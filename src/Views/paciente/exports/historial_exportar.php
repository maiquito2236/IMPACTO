<?php
/**
 * CONTROLADOR/VISTA MAESTRA DE EXPORTACIONES DE HISTORIAL - ODONTO ESTÉTICA
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

$id_cita = $_GET['id'] ?? null;
$formato = isset($_GET['formato']) ? strtolower(trim($_GET['formato'])) : '';

if (!$id_cita || empty($formato)) { 
    die("Parámetros incompletos para la exportación."); 
}

// 1. Conexión única a la base de datos utilizando el Singleton global
try {
    $db = \App\Config\Database::getInstance()->getConnection();

    $sql = "SELECT 
                c.ID_CITA, c.FECHA_HORA, c.MOTIVO,
                CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_DOCTOR,
                cons.NOMBRE AS CONSULTORIO, 
                COALESCE(c.OBSERVACIONES_DOCTOR, hc.OBSERVACIONES, hc.DIAGNOSTICO) AS OBSERVACIONES_DOCTOR, 
                c.RECOMENDACIONES, c.FECHA_ATENCION,
                CONCAT(upac.NOMBRES, ' ', upac.APELLIDOS) AS NOMBRE_PACIENTE,
                upac.NUMERO_DOCUMENTO AS DOC_PACIENTE, upac.TIPO_DOCUMENTO AS TIPO_DOC_PACIENTE,
                COALESCE(p.NOMBRE_PROCEDIMIENTO, hc_p.NOMBRE_PROCEDIMIENTO, hc.TRATAMIENTO) AS TRATAMIENTO
            FROM cita c
            INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
            INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
            INNER JOIN paciente pac ON c.PACIENTE_ID_PACIENTE = pac.ID_PACIENTE
            INNER JOIN usuarios upac ON pac.USUARIOS_ID_USUARIOS = upac.ID_USUARIOS
            LEFT JOIN consultorio cons ON o.CONSULTORIO_ID_CONSULTORIO = cons.ID_CONSULTORIO
            LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
            LEFT JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
            
            LEFT JOIN factura f ON c.ID_CITA = f.CITA_ID_CITA
            LEFT JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
            LEFT JOIN historia_clinica_has_procedimientos hcp ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
            LEFT JOIN procedimientos hc_p ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = hc_p.ID_PROCEDIMIENTO
            
            WHERE c.ID_CITA = :id LIMIT 1";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id_cita]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        die("Registro clínico no encontrado.");
    }
} catch (Exception $e) {
    die("Error de Base de Datos: " . $e->getMessage());
}

// 2. Formateo de variables
$fecha_atencion = !empty($datos['FECHA_ATENCION']) ? date('d/m/Y \a \l\a\s h:i A', strtotime($datos['FECHA_ATENCION'])) : date('d/m/Y \a \l\a\s h:i A', strtotime($datos['FECHA_HORA']));
$procedimiento = !empty($datos['TRATAMIENTO']) ? $datos['TRATAMIENTO'] : $datos['MOTIVO'];
$observaciones = !empty($datos['OBSERVACIONES_DOCTOR']) ? $datos['OBSERVACIONES_DOCTOR'] : 'Sin observaciones registradas.';
$recomendaciones = !empty($datos['RECOMENDACIONES']) ? $datos['RECOMENDACIONES'] : 'Sin recomendaciones médicas especificadas.';
$fecha_emision = date('d/m/Y \a \l\a\s h:i A');

// 🌟 FIX MILIMÉTRICO: Ajustamos la altura de línea dependiendo de si es PDF o Impresión
$cssAjusteCirculo = ($formato === 'pdf') 
    ? 'line-height: 25px;' /* Dompdf empuja el texto hacia abajo, esto lo sube al centro exacto */
    : 'line-height: 32px;'; /* El navegador web lo centra perfecto con el tamaño original */

// 3. Estilos CSS Compartidos
$cssBase = '
    body { font-family: "Helvetica", "Arial", sans-serif; color: #1e293b; margin: 0; padding: 20px; }
    .header-table { width: 100%; border-bottom: 3px solid #1a56db; padding-bottom: 15px; margin-bottom: 20px; }
    .logo-cell { width: 40%; }
    .logo-title { color: #1a56db; margin: 0; font-size: 24px; font-weight: bold; }
    .info-cell { width: 60%; text-align: right; font-size: 11px; color: #0f172a; line-height: 1.6; }
    .text-primary { color: #1a56db; }
    .title-container { text-align: center; margin-bottom: 20px; }
    .main-title { font-size: 22px; color: #1a56db; margin: 0; text-transform: uppercase; }
    .sub-title { font-size: 14px; color: #64748b; margin: 5px 0 0 0; }
    .info-box { width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; background-color: #f8fafc; margin-bottom: 25px; border-collapse: collapse; }
    .info-box td { padding: 15px; vertical-align: top; width: 50%; }
    .border-right { border-right: 1px solid #cbd5e1; }
    .info-label { font-size: 11px; color: #64748b; margin-bottom: 3px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 12px; display: block; }
    .badge-consultorio { background-color: #1a56db; color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; }
    .content-block { width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; margin-bottom: 15px; border-collapse: collapse; }
    .content-block td { padding: 15px; vertical-align: middle; }
    .icon-col { width: 50px; text-align: center; border-right: 1px dashed #cbd5e1; }
    
    /* Inyección del fix de centrado */
    .icon-circle { width: 32px; height: 32px; border-radius: 50%; display: inline-block; text-align: center; font-weight: bold; font-size: 16px; font-family: "Helvetica", sans-serif; box-sizing: border-box; ' . $cssAjusteCirculo . ' }
    
    .block-title { font-size: 13px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
    .block-text { font-size: 13px; color: #334155; }
    .block-blue { border-color: #93c5fd; border-left: 4px solid #3b82f6; }
    .block-blue .block-title { color: #1d4ed8; }
    .block-blue .icon-circle { background-color: #eff6ff; border: 1px solid #bfdbfe; color: #3b82f6; }
    .block-green { border-color: #86efac; border-left: 4px solid #22c55e; }
    .block-green .block-title { color: #15803d; }
    .block-green .icon-circle { background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #22c55e; }
    .block-orange { border-color: #fdba74; border-left: 4px solid #f97316; }
    .block-orange .block-title { color: #c2410c; }
    .block-orange .icon-circle { background-color: #fff7ed; border: 1px solid #fed7aa; color: #f97316; }
    .footer { text-align: center; font-size: 11px; color: #64748b; margin-top: 40px; padding-top: 15px; border-top: 1px dashed #cbd5e1; }
    .footer img { display: block; margin: 0 auto 10px auto; max-width: 150px; }
    .footer .footer-text { display: block; font-weight: bold; }
';

// 4. HTML Base Compartido
$htmlBase = '
    <table class="header-table">
        <tr>
            <td class="logo-cell">{{LOGO}}</td>
            <td class="info-cell">
                <strong class="text-primary">NIT:</strong> 51936980 <br>
                <strong class="text-primary">Dirección:</strong> Calle 10 #10-35<br>
                <strong class="text-primary">Teléfono:</strong> 3115204752<br>
                <strong class="text-primary">Correo:</strong> odontoestetica@gmail.com
            </td>
        </tr>
    </table>

    <div class="title-container">
        <h1 class="main-title">Historia Clínica N° ' . htmlspecialchars($datos['ID_CITA']) . '</h1>
        <p class="sub-title">Resumen del Registro Médico</p>
    </div>

    <table class="info-box">
        <tr>
            <td class="border-right">
                <span class="info-label">Paciente</span>
                <span class="info-value">' . htmlspecialchars($datos['NOMBRE_PACIENTE']) . '</span>
                <span class="info-label">Documento de Identidad</span>
                <span class="info-value">' . htmlspecialchars($datos['TIPO_DOC_PACIENTE'] . ' ' . $datos['DOC_PACIENTE']) . '</span>
                <span class="info-label">Fecha y Hora de Cierre</span>
                <span class="info-value">' . htmlspecialchars($fecha_atencion) . '</span>
            </td>
            <td>
                <span class="info-label">Odontólogo Tratante</span>
                <span class="info-value">Dr(a). ' . htmlspecialchars($datos['NOMBRE_DOCTOR']) . '</span>
                <span class="info-label">Consultorio</span>
                <span class="info-value">Consultorio ' . htmlspecialchars($datos['CONSULTORIO']) . '</span>
            </td>
        </tr>
    </table>

    <table class="content-block block-blue">
        <tr>
            <td class="icon-col"><div class="icon-circle">1</div></td>
            <td><div class="block-title">Procedimiento Clínico</div><div class="block-text">' . htmlspecialchars($procedimiento) . '</div></td>
        </tr>
    </table>

    <table class="content-block block-green">
        <tr>
            <td class="icon-col"><div class="icon-circle">2</div></td>
            <td><div class="block-title">Observaciones del Diagnóstico</div><div class="block-text">' . htmlspecialchars($observaciones) . '</div></td>
        </tr>
    </table>

    <table class="content-block block-orange">
        <tr>
            <td class="icon-col"><div class="icon-circle">3</div></td>
            <td><div class="block-title">Recomendaciones Médicas</div><div class="block-text">' . htmlspecialchars($recomendaciones) . '</div></td>
        </tr>
    </table>

    <div class="footer">
        {{LOGO_FOOTER}}
        <span class="footer-text text-primary">Fecha de emisión: ' . $fecha_emision . '</span>
    </div>
';

// 5. Enrutador Maestro
switch ($formato) {

    case 'pdf':
        $ruta_logo = $_SERVER['DOCUMENT_ROOT'] . '/LOGIN_ORIGINAL/public/img/logo_odontologia.png';
        $logo_src = '';
        if (file_exists($ruta_logo)) {
            $tipo_imagen = pathinfo($ruta_logo, PATHINFO_EXTENSION);
            $datos_imagen = file_get_contents($ruta_logo);
            $logo_src = 'data:image/' . $tipo_imagen . ';base64,' . base64_encode($datos_imagen);
        }
        
        $logoTop = !empty($logo_src) ? '<img src="'.$logo_src.'" alt="Logo" style="height: 80px; width: auto;">' : '<h2 class="logo-title">ODONTO ESTÉTICA</h2>';
        $logoFooter = !empty($logo_src) ? '<img src="'.$logo_src.'" alt="Logo" style="height: 60px; width: auto; display: block; margin: 0 auto 10px auto;">' : '';

        $htmlFinal = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>' . $cssBase . '</style></head><body>';
        $htmlFinal .= str_replace(['{{LOGO}}', '{{LOGO_FOOTER}}'], [$logoTop, $logoFooter], $htmlBase);
        $htmlFinal .= '</body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlFinal);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Ficha_Evolucion_N{$id_cita}.pdf", ["Attachment" => 1]);
        break;

    case 'imprimir':
        $logoTop = '<img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Logo Odonto Estética" style="height: 80px; object-fit: contain;">';
        $logoFooter = '<img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Logo Odonto Estética" style="height: 60px; object-fit: contain;">';
        
        $htmlFinal = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Imprimir Historial</title><style>';
        $htmlFinal .= '@media print { @page { margin: 0; } body { margin: 1.5cm; } } ';
        $htmlFinal .= $cssBase . '</style></head><body>';
        $htmlFinal .= str_replace(['{{LOGO}}', '{{LOGO_FOOTER}}'], [$logoTop, $logoFooter], $htmlBase);
        $htmlFinal .= '<script> window.onload = function() { window.print(); }; window.onafterprint = function() { window.close(); }; </script>';
        $htmlFinal .= '</body></html>';
        
        echo $htmlFinal;
        break;

    default:
        die("Formato solicitado no válido.");
}