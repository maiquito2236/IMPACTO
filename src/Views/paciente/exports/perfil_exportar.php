<?php
/**
 * CONTROLADOR MAESTRO DE EXPORTACIONES - ODONTO ESTÉTICA
 * Estructura centralizada y altamente escalable para reportes.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 🌟 Ubicados correctamente en la cima del archivo
use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Filtro estricto de seguridad de sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

$idUsuario = $_SESSION['usuario_id'];
// 🌟 Limpieza de espacios invisibles garantizada
$formato = isset($_GET['formato']) ? strtolower(trim($_GET['formato'])) : '';

// 2. Conexión única y consulta unificada a la base de datos
try {
    $db = new PDO("mysql:host=localhost;dbname=sigco_esteban_alone;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta extendida compartida para todos los formatos
    $sql = "SELECT u.*, p.*, e.NOMBRE_EPS 
            FROM usuarios u
            LEFT JOIN paciente p ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
            LEFT JOIN eps e ON e.ID_EPS = p.EPS_ID_EPS
            WHERE u.ID_USUARIOS = :id LIMIT 1";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $idUsuario]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
        die("Usuario no encontrado en el sistema.");
    }

} catch (PDOException $e) {
    die("Error de Base de Datos: " . $e->getMessage());
}

// 3. Enrutador maestro (Switch)
switch ($formato) {

    case 'excel':
        // ==========================================
        // MÓDULO EXCEL: ESTRUCTURA NATIVA CON METADATOS
        // ==========================================
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Mis_Datos_Completos.xls"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                .header-title { background-color: #0b57d0; color: #ffffff; font-weight: bold; font-size: 14px; text-align: center; padding: 12px; }
                .label-cell { background-color: #f1f5f9; font-weight: bold; text-align: left; padding: 6px; }
                .data-cell { text-align: left; padding: 6px; }
            </style>
        </head>
        <body>
        <table border="1">
            <thead>
                <tr><th colspan="2" class="header-title">ODONTO ESTÉTICA - DATOS PERSONALES</th></tr>
            </thead>
            <tbody>
                <tr><td class="label-cell">Tipo de Documento:</td><td class="data-cell">' . htmlspecialchars($u['TIPO_DOCUMENTO'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Número de Documento:</td><td class="data-cell">' . htmlspecialchars(" " . ($u['NUMERO_DOCUMENTO'] ?? '')) . '</td></tr>
                <tr><td class="label-cell">Nombres:</td><td class="data-cell">' . htmlspecialchars($u['NOMBRES'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Apellidos:</td><td class="data-cell">' . htmlspecialchars($u['APELLIDOS'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Teléfono celular:</td><td class="data-cell">' . htmlspecialchars(" " . ($u['TELEFONO'] ?? '')) . '</td></tr>
                <tr><td class="label-cell">Correo electrónico:</td><td class="data-cell">' . htmlspecialchars($u['CORREO'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Género:</td><td class="data-cell">' . htmlspecialchars($u['GENERO'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Fecha de Nacimiento:</td><td class="data-cell">' . htmlspecialchars($u['FECHA_NACIMIENTO'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Dirección de residencia:</td><td class="data-cell">' . htmlspecialchars($u['DIRECCION'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Factor de sangre (RH):</td><td class="data-cell">' . htmlspecialchars($u['RH'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Entidad de salud (EPS):</td><td class="data-cell">' . htmlspecialchars($u['NOMBRE_EPS'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Contacto de Emergencia:</td><td class="data-cell">' . htmlspecialchars($u['NOMBRE_CONTACTO_EMERGENCIA'] ?? '') . '</td></tr>
                <tr><td class="label-cell">Teléfono de Emergencia:</td><td class="data-cell">' . htmlspecialchars(" " . ($u['NUMERO_CONTACTO_EMERGENCIA'] ?? '')) . '</td></tr>
            </tbody>
        </table>
        </body>
        </html>';
        exit;


    case 'pdf':
        // ==========================================
        // MÓDULO PDF: RENDERIZACIÓN MEDIANTE DOMPDF
        // ==========================================

        // 🌟 CONFIGURACIÓN CRÍTICA: Le damos permiso a DOMPDF para leer tus rutas locales de imágenes
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Permite rutas HTTP si usas localhost
        $options->set('chroot', __DIR__ . '/../../../../'); // Permite subir a la raíz pública de tu XAMPP

        $dompdf = new Dompdf($options);

        // 🌟 Tu HTML idéntico, limpio y sin el texto duplicado de "ODONTO ESTÉTICA"
        $html = '
        <html>
        <head>
            <style>
                body { font-family: "Helvetica", sans-serif; color: #1e293b; padding: 30px; }
                .header { text-align: center; border-bottom: 3px solid #0b57d0; padding-bottom: 15px; margin-bottom: 30px; }
                .logo { width: 180px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
                .info-box { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .info-box td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
                .label { font-weight: bold; color: #64748b; width: 35%; }
                .footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 60px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" class="logo" alt="Logo">
                
                <div style="color: #64748b; font-size: 12px; margin-top: 8px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Resumen Clínico Informativo de Perfil</div>
            </div>

            <h2 style="color: #1e293b; font-size: 18px; margin-bottom: 20px;">Información del Paciente</h2>

            <table class="info-box">
                <tr><td class="label">Documento de Identidad:</td><td>' . htmlspecialchars(($u['TIPO_DOCUMENTO'] ?? '') . ' ' . ($u['NUMERO_DOCUMENTO'] ?? '')) . '</td></tr>
                <tr><td class="label">Nombres Completos:</td><td>' . htmlspecialchars(($u['NOMBRES'] ?? '') . ' ' . ($u['APELLIDOS'] ?? '')) . '</td></tr>
                <tr><td class="label">Teléfono de Contacto:</td><td>' . htmlspecialchars($u['TELEFONO'] ?? '') . '</td></tr>
                <tr><td class="label">Correo Electrónico:</td><td>' . htmlspecialchars($u['CORREO'] ?? '') . '</td></tr>
                <tr><td class="label">Género:</td><td>' . htmlspecialchars($u['GENERO'] ?? '') . '</td></tr>
                <tr><td class="label">Fecha de Nacimiento:</td><td>' . htmlspecialchars($u['FECHA_NACIMIENTO'] ?? '') . '</td></tr>
                <tr><td class="label">Dirección Registrada:</td><td>' . htmlspecialchars($u['DIRECCION'] ?? '') . '</td></tr>
                <tr><td class="label">Grupo Sanguíneo (RH):</td><td>' . htmlspecialchars($u['RH'] ?? '') . '</td></tr>
                <tr><td class="label">Entidad de Salud (EPS):</td><td>' . htmlspecialchars($u['NOMBRE_EPS'] ?? '') . '</td></tr>
                <tr><td class="label">Contacto de Emergencia:</td><td>' . htmlspecialchars($u['NOMBRE_CONTACTO_EMERGENCIA'] ?? '') . '</td></tr>
                <tr><td class="label">Teléfono de Emergencia:</td><td>' . htmlspecialchars($u['NUMERO_CONTACTO_EMERGENCIA'] ?? '') . '</td></tr>
            </table>

            <div class="footer">
                Este documento es un extracto informativo generado automáticamente por el usuario autenticado. &copy; 2026.
            </div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Mis_Datos_Personales.pdf", array("Attachment" => true));
        exit;


    case 'imprimir':
        // ==========================================
        // MÓDULO IMPRIMIR: INTERFAZ DE HARDWARE LOCAL
        // ==========================================
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Imprimir Datos Personales</title>
            <style>
                /* 🌟 TRUCO MAESTRO: Margen de página a cero elimina la URL y la fecha por completo */
                @page { 
                    size: auto;   
                    margin: 0px;  
                }
                
                /* Trasladamos el margen al body para que el diseño respire y no se corte */
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 40px; 
                    color: #333; 
                    line-height: 1.6; 
                    background: #ffffff;
                }
                
                .print-card { 
                    border: 1px solid #cbd5e1; 
                    padding: 30px; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    border-radius: 8px; 
                }
                img { display: block; margin-left: auto; margin-right: auto; width: 40%; height: auto; }
                .text-center { text-align: center; }
                .item-row { display: flex; border-bottom: 1px solid #f1f5f9; padding: 8px 0; }
                .item-label { font-weight: bold; width: 40%; color: #475569; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="print-card">
                <div class="text-center">
                    <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Logo">
                    <p style="margin: 10px; font-size: 12px; color: #64748b; font-weight: bold; text-transform: uppercase;">Comprobante de datos de paciente</p>
                </div>
                
                <div class="item-row"><div class="item-label">Tipo de Documento:</div><div><?= htmlspecialchars($u['TIPO_DOCUMENTO'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Número de Documento:</div><div><?= htmlspecialchars($u['NUMERO_DOCUMENTO'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Nombres:</div><div><?= htmlspecialchars($u['NOMBRES'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Apellidos:</div><div><?= htmlspecialchars($u['APELLIDOS'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Teléfono:</div><div><?= htmlspecialchars($u['TELEFONO'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Correo Electrónico:</div><div><?= htmlspecialchars($u['CORREO'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Género:</div><div><?= htmlspecialchars($u['GENERO'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Fecha de Nacimiento:</div><div><?= htmlspecialchars($u['FECHA_NACIMIENTO'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Dirección:</div><div><?= htmlspecialchars($u['DIRECCION'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Factor de sangre (RH):</div><div><?= htmlspecialchars($u['RH'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">EPS:</div><div><?= htmlspecialchars($u['NOMBRE_EPS'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Contacto de Emergencia:</div><div><?= htmlspecialchars($u['NOMBRE_CONTACTO_EMERGENCIA'] ?? '') ?></div></div>
                <div class="item-row"><div class="item-label">Teléfono de Emergencia:</div><div><?= htmlspecialchars($u['NUMERO_CONTACTO_EMERGENCIA'] ?? '') ?></div></div>
            </div>

            <div class="text-center no-print" style="margin-top: 25px;">
                <button onclick="window.close()" style="padding: 8px 20px; font-weight: bold; cursor: pointer; border-radius: 4px; border: 1px solid #ccc; background: #fff;">Cerrar Ventana</button>
            </div>
        </body>
        </html>
        <?php
        exit;

    default:
        die("Formato solicitado no válido o no soportado en esta versión.");
}