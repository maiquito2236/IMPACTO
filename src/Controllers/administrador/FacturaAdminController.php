<?php
namespace App\Controllers\administrador;

use App\Models\administrador\FacturaAdminModel; // Ajusta según tu estructura de carpetas
use Exception;

class FacturaAdminController {

    private $modelo;

    public function __construct() {
        $this->modelo = new FacturaAdminModel();
    }

    /**
     * Formatea una fecha de la BD. Si es inválida (0000-00-00), devuelve un texto amigable.
     */
    private function formatearFecha($fecha) {
        if (empty($fecha) || strpos($fecha, '0000-00-00') !== false) {
            return 'Sin fecha registrada';
        }
        return $fecha;
    }

    public function mostrarFacturacion() {
        // Obtener las citas que sí se pueden facturar
        $citas = $this->modelo->obtenerCitasParaFacturar();
        
        // Pasar $citas a la vista (Corregimos la ruta subiendo solo 2 niveles: Controllers -> src -> Views)
        require_once __DIR__ . '/../../Views/administrador/facturacion.php';
    }

    /**
     * Endpoint para guardar factura vía JSON (como en tu ejemplo de agenda)
     * POST /LOGIN_ORIGINAL/admin/facturacion/guardar
     */
    public function guardarFactura() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validación de campos según tu BD (historia_clinica_id, cita_id, total)
            if (empty($data['historia_clinica_id']) || empty($data['total'])) {
                throw new Exception("Datos incompletos para crear la factura.");
            }

            $id_factura = $this->modelo->crearFactura([
                'historia_clinica_id' => (int)$data['historia_clinica_id'],
                'cita_id'             => (int)$data['cita_id'],
                'total'               => (float)$data['total']
            ]);

            if ((float)$data['total'] > 0) {
                $db = \App\Config\Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT hc.PACIENTE_ID_PACIENTE, hc.ODONTOLOGO_ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente_nombre FROM historia_clinica hc INNER JOIN paciente p ON hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE hc.ID_HISTORIA_CLINICA = ?");
                $stmt->execute([(int)$data['historia_clinica_id']]);
                $refs = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($refs) {
                    $monto_fmt = number_format((float)$data['total'], 2, ',', '.');
                    \App\Helpers\Notificador::enviarAAdmin(26, "Se generó la factura #{$id_factura} para {$refs['paciente_nombre']} con saldo pendiente de $$monto_fmt.");
                    \App\Helpers\Notificador::enviarAPaciente($refs['PACIENTE_ID_PACIENTE'], 27, "Tu factura #{$id_factura} ha sido generada. Saldo pendiente: $$monto_fmt.");
                    \App\Helpers\Notificador::enviarAOdontologo($refs['ODONTOLOGO_ID_ODONTOLOGO'], 28, "El paciente {$refs['paciente_nombre']} quedó con saldo pendiente de $$monto_fmt.");
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Factura creada.', 'id' => $id_factura]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
    public function obtenerFacturas() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $facturas = $this->modelo->listarFacturas();
            echo json_encode(['status' => 'success', 'facturas' => $facturas]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ==========================================
    // ENDPOINTS DE CONFIGURACIÓN
    // ==========================================
    public function obtenerConfiguracion() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $config = $this->modelo->obtenerConfiguracionPorcentajes();
            echo json_encode(['status' => 'success', 'data' => $config]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function actualizarConfiguracion() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id_odontologo']) || !isset($data['porcentaje'])) {
                throw new Exception("Datos incompletos para actualizar.");
            }

            $this->modelo->actualizarPorcentaje($data['id_odontologo'], $data['porcentaje']);
            echo json_encode(['status' => 'success', 'message' => 'Porcentaje actualizado correctamente.']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ==========================================
    // ENDPOINTS DE LIQUIDACIÓN
    // ==========================================
    public function calcularLiquidacion() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $resultado = $this->modelo->calcularLiquidacion($data['id_odontologo'], $data['mes']);
            
            // Calculamos cuánto dinero exacto le toca al doctor
            $totalPagar = ($resultado['produccion_total'] * $resultado['porcentaje']) / 100;
            $resultado['total_pagar'] = $totalPagar;

            echo json_encode(['status' => 'success', 'data' => $resultado]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function guardarLiquidacion() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $this->modelo->registrarEgreso(
                $data['id_odontologo'], 
                $data['mes'], 
                $data['produccion'], 
                $data['porcentaje'], 
                $data['total_pagado']
            );

            $db = \App\Config\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS odontologo_nombre FROM odontologo o JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE o.ID_ODONTOLOGO = ?");
            $stmt->execute([$data['id_odontologo']]);
            $odontologo = $stmt->fetch(\PDO::FETCH_ASSOC);
            $nombreOdontologo = $odontologo ? $odontologo['odontologo_nombre'] : 'Odontólogo';

            $montoFmt = number_format($data['total_pagado'], 2, ',', '.');
            \App\Helpers\Notificador::enviarAOdontologo($data['id_odontologo'], 24, "El administrador te ha realizado un pago por $$montoFmt correspondiente al mes de {$data['mes']}.");
            \App\Helpers\Notificador::enviarAAdmin(20, "Has registrado un pago por $$montoFmt al odontólogo(a) $nombreOdontologo correspondiente al mes de {$data['mes']}.");

            echo json_encode(['status' => 'success', 'message' => 'Pago registrado con éxito.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function obtenerResumenKPIs() {
        header('Content-Type: application/json');
        $resumen = $this->modelo->obtenerResumenFinanciero();
        $caja = $this->modelo->obtenerCierreCajaHoy();
        
        echo json_encode([
            'status' => 'success',
            'data' => $resumen,
            'caja' => $caja
        ]);
        exit;
    }
    
    // Guarda el pago en la base de datos
    public function guardarPago() {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['factura_id']) && isset($data['monto'])) {
        $id_factura = $data['factura_id'];
        $monto = $data['monto'];
        $metodo = $data['metodo'];

        $resultado = $this->modelo->registrarPago($id_factura, $monto, $metodo);
        
        if ($resultado) {
            // Notificaciones
            $infoFactura = $this->modelo->obtenerFacturaIndividual($id_factura);
            if ($infoFactura && isset($infoFactura['factura'])) {
                $f = $infoFactura['factura'];
                $monto_fmt = number_format($monto, 2, ',', '.');
                
                $db = \App\Config\Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT 
                        p.ID_PACIENTE AS PACIENTE_ID_PACIENTE, 
                        CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente_nombre 
                    FROM factura f 
                    LEFT JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA 
                    JOIN paciente p ON (hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE OR f.PACIENTE_ID_PACIENTE = p.ID_PACIENTE) 
                    JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                    WHERE f.ID_FACTURA = ?
                ");
                $stmt->execute([$id_factura]);
                $refs = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($refs) {
                    $esPagoTotal = ($f['pagado'] >= $f['TOTAL']);
                    if ($esPagoTotal) {
                        \App\Helpers\Notificador::enviarAAdmin(23, "El paciente {$refs['paciente_nombre']} ha cancelado la totalidad de la factura #{$id_factura} por $$monto_fmt.");
                        \App\Helpers\Notificador::enviarAPaciente($refs['PACIENTE_ID_PACIENTE'], 5, "Hemos recibido tu pago total de $$monto_fmt por concepto de {$f['tratamiento']}. Gracias!");
                    } else {
                        \App\Helpers\Notificador::enviarAAdmin(20, "El paciente {$refs['paciente_nombre']} hizo un abono de $$monto_fmt a la factura #{$id_factura}.");
                        \App\Helpers\Notificador::enviarAPaciente($refs['PACIENTE_ID_PACIENTE'], 19, "Hemos recibido tu abono de $$monto_fmt por concepto de {$f['tratamiento']}. Gracias!");
                    }
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Pago registrado correctamente']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos']);
        }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos para el pago']);
        }
        exit;
    }

    public function exportarReporte() {
        $tipo = $_GET['tipo'] ?? 'ingresos';
        $formato = $_GET['formato'] ?? 'imprimir';
        $mes = $_GET['mes'] ?? date('Y-m');

        $datos = $this->modelo->obtenerReportePorMes($tipo, $mes);

        // ==========================================
        // 1. SI ES EXCEL
        // ==========================================
        if ($formato === 'excel') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=Reporte_{$tipo}_{$mes}.xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            // BOM UTF-8 para que Excel reconozca los caracteres especiales
            echo "\xEF\xBB\xBF";
            echo "<html><head><meta charset='utf-8'></head><body>";
            echo "<table border='1'>";
            echo "<tr><th colspan='4' style='background-color:#2563eb; color:white; font-size:18px;'>Reporte de " . strtoupper($tipo) . " - $mes</th></tr>";
            
            if ($tipo === 'ingresos') {
                echo "<tr style='background-color:#f1f5f9;'><th>ID Pago</th><th>Factura</th><th>Fecha</th><th>Total Ingresado</th></tr>";
                foreach ($datos as $d) {
                    $fechaPago = $this->formatearFecha($d['FECHA_PAGO']);
                    echo "<tr><td>{$d['ID_PAGO']}</td><td>FAC-" . str_pad($d['FACTURA_ID_FACTURA'], 4, "0", STR_PAD_LEFT) . "</td><td>{$fechaPago}</td><td>$" . number_format($d['MONTO'], 2) . "</td></tr>";
                }
            } else {
                echo "<tr style='background-color:#f1f5f9;'><th>Mes</th><th>Odontólogo</th><th>Producción</th><th>Comisión Pagada</th></tr>";
                foreach ($datos as $d) {
                    $prod = $d['produccion'] ?? 0;
                    $pagado = $d['total_pagado'] ?? 0;
                    $doctor = $d['doctor'] ?? 'Desconocido';
                    echo "<tr><td>{$mes}</td><td>{$doctor}</td><td>$" . number_format($prod, 2) . "</td><td>$" . number_format($pagado, 2) . "</td></tr>";
                }
            }
            echo "</table>";
            echo "</body></html>";
            exit;
        }

        // ==========================================
        // 2. SI ES PDF O IMPRIMIR
        // ==========================================
        $titulo = $tipo === 'ingresos' ? "Reporte de Ingresos" : "Reporte de Egresos";
        
        // Magia para descargar el PDF o Imprimir
        if ($formato === 'pdf') {
            $scriptAccion = "
                <script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'></script>
                <script>
                    window.onload = function() {
                        var element = document.getElementById('reporte-contenido');
                        var opt = {
                            margin:       15,
                            filename:     'Reporte_{$tipo}_{$mes}.pdf',
                            image:        { type: 'jpeg', quality: 0.98 },
                            html2canvas:  { scale: 2 },
                            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                        };
                        html2pdf().set(opt).from(element).save().then(() => {
                            setTimeout(() => { window.close(); }, 1500); // Cierra la pestaña solita tras descargar
                        });
                    };
                </script>
            ";
        } else {
            $scriptAccion = "<script>window.onload = function() { window.print(); };</script>";
        }
        
        $html = "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>{$titulo}</title>
            <style>
                body { font-family: 'Arial', sans-serif; padding: 20px; color: #333; }
                #reporte-contenido { padding: 20px; }
                .header { border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;}
                .logo img { max-height: 80px; }
                .info-reporte { text-align: right; }
                .info-reporte h1 { margin: 0; font-size: 22px; color: #1e293b; }
                .info-reporte p { margin: 5px 0 0 0; color: #64748b; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                th, td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; }
                th { background-color: #f8fafc; color: #0f172a; }
                .total-box { float: right; background: #f8fafc; padding: 15px 30px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 18px; }
                .total-box strong { color: #2563eb; font-size: 22px; }
            </style>
        </head>
        <body>
            <div id='reporte-contenido'>
                <div class='header'>
                    <div class='logo'>
                        <img src='http://localhost/LOGIN_ORIGINAL/public/img/logo_odontologia.png' alt='Odonto Estética'>
                    </div>
                    <div class='info-reporte'>
                        <p>Período: <strong>{$mes}</strong></p>
                    </div>
                </div>
                <table>";

        $suma = 0;
        if ($tipo === 'ingresos') {
            $html .= "<tr><th>N° Factura</th><th>Fecha y Hora del Pago</th><th>Método</th><th>Monto Ingresado</th></tr>";
            foreach ($datos as $d) {
                $fechaPago = $this->formatearFecha($d['FECHA_PAGO']);
                $html .= "<tr>
                            <td>FAC-" . str_pad($d['FACTURA_ID_FACTURA'], 4, "0", STR_PAD_LEFT) . "</td>
                            <td>{$fechaPago}</td>
                            <td>{$d['METODO_PAGO']}</td>
                            <td>$ " . number_format($d['MONTO'], 2, ',', '.') . "</td>
                          </tr>";
                $suma += $d['MONTO'];
            }
        } else {
            $html .= "<tr><th>Mes Liquidado</th><th>Odontólogo</th><th>Producción</th><th>Comisión Pagada</th></tr>";
            foreach ($datos as $d) {
                $prod = $d['produccion'] ?? 0;
                $pagado = $d['total_pagado'] ?? 0;
                $doctor = $d['doctor'] ?? 'Desconocido';
                $html .= "<tr>
                            <td>{$mes}</td>
                            <td>{$doctor}</td>
                            <td>$ " . number_format($prod, 2, ',', '.') . "</td>
                            <td>$ " . number_format($pagado, 2, ',', '.') . "</td>
                          </tr>";
                $suma += $pagado;
            }
        }

        $html .= "</table>
                <div class='total-box'>Total Consolidado: <strong>$ " . number_format($suma, 2, ',', '.') . "</strong></div>
            </div>
            {$scriptAccion}
        </body>
        </html>";

        echo $html;
        exit;
    }

    /**
     * Exporta una factura individual en formato PDF, Excel o Impresión.
     * GET: /LOGIN_ORIGINAL/admin/facturacion/exportar-individual?id=123&formato=pdf
     */
    public function exportarFacturaIndividual() {
        $idFactura = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $formato = $_GET['formato'] ?? 'imprimir';

        if ($idFactura <= 0) {
            echo "<h2>Error: ID de factura no válido.</h2>";
            exit;
        }

        $resultado = $this->modelo->obtenerFacturaIndividual($idFactura);
        $f = $resultado['factura'];
        $pagos = $resultado['pagos'];

        if (!$f) {
            echo "<h2>Error: Factura no encontrada.</h2>";
            exit;
        }

        $numFactura = "FAC-" . str_pad($f['ID_FACTURA'], 4, "0", STR_PAD_LEFT);
        $pendiente = $f['TOTAL'] - $f['pagado'];

        // ==========================================
        // EXCEL INDIVIDUAL
        // ==========================================
        if ($formato === 'excel') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=Factura_{$numFactura}.xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            // BOM UTF-8 para que Excel reconozca los caracteres especiales
            echo "\xEF\xBB\xBF";
            echo "<html><head><meta charset='utf-8'></head><body>";
            echo "<table border='1'>";
            echo "<tr><th colspan='4' style='background-color:#2563eb; color:white; font-size:18px;'>Factura {$numFactura} - Odonto Estética</th></tr>";
            echo "<tr><td colspan='4'>&nbsp;</td></tr>";
            echo "<tr><th style='background-color:#f1f5f9;'>Paciente</th><td colspan='3'>{$f['paciente']}</td></tr>";
            echo "<tr><th style='background-color:#f1f5f9;'>Documento</th><td colspan='3'>{$f['documento']}</td></tr>";
            echo "<tr><th style='background-color:#f1f5f9;'>Fecha de Emisión</th><td colspan='3'>{$f['FECHA_EMISION']}</td></tr>";
            echo "<tr><th style='background-color:#f1f5f9;'>Concepto</th><td colspan='3'>{$f['tratamiento']}</td></tr>";
            echo "<tr><th style='background-color:#f1f5f9;'>Estado</th><td colspan='3'>{$f['estado']}</td></tr>";
            echo "<tr><td colspan='4'>&nbsp;</td></tr>";
            echo "<tr><th style='background-color:#f1f5f9;'>Total</th><th style='background-color:#f1f5f9;'>Pagado</th><th style='background-color:#f1f5f9;'>Pendiente</th><th style='background-color:#f1f5f9;'>Estado</th></tr>";
            echo "<tr><td>$ " . number_format($f['TOTAL'], 2, ',', '.') . "</td><td>$ " . number_format($f['pagado'], 2, ',', '.') . "</td><td>$ " . number_format($pendiente, 2, ',', '.') . "</td><td>{$f['estado']}</td></tr>";

            if (!empty($pagos)) {
                echo "<tr><td colspan='4'>&nbsp;</td></tr>";
                echo "<tr><th colspan='4' style='background-color:#e0f2fe; color:#1e40af;'>Historial de Pagos</th></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>N° Pago</th><th style='background-color:#f1f5f9;'>Fecha</th><th style='background-color:#f1f5f9;'>Método</th><th style='background-color:#f1f5f9;'>Monto</th></tr>";
                foreach ($pagos as $p) {
                    $fechaPago = $this->formatearFecha($p['FECHA_PAGO']);
                    echo "<tr><td>{$p['ID_PAGO']}</td><td>{$fechaPago}</td><td>{$p['METODO_PAGO']}</td><td>$ " . number_format($p['MONTO'], 2, ',', '.') . "</td></tr>";
                }
            }
            echo "</table>";
            echo "</body></html>";
            exit;
        }

        // ==========================================
        // PDF O IMPRESIÓN INDIVIDUAL
        // ==========================================
        if ($formato === 'pdf') {
            $scriptAccion = "
                <script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'></script>
                <script>
                    window.onload = function() {
                        var element = document.getElementById('factura-contenido');
                        var opt = {
                            margin:       15,
                            filename:     'Factura_{$numFactura}.pdf',
                            image:        { type: 'jpeg', quality: 0.98 },
                            html2canvas:  { scale: 2 },
                            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                        };
                        html2pdf().set(opt).from(element).save().then(() => {
                            setTimeout(() => { window.close(); }, 1500);
                        });
                    };
                </script>
            ";
        } else {
            $scriptAccion = "<script>window.onload = function() { window.print(); };</script>";
        }

        // Color del badge según estado
        $estadoColor = $f['estado'] === 'Pagada' ? '#dcfce7' : ($f['estado'] === 'Abonada' ? '#fef9c3' : '#dbeafe');
        $estadoTextoColor = $f['estado'] === 'Pagada' ? '#166534' : ($f['estado'] === 'Abonada' ? '#854d0e' : '#1e40af');

        $html = "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Factura {$numFactura}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Arial', sans-serif; padding: 30px; color: #333; background: #f8fafc; }
                #factura-contenido { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
                
                .factura-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #2563eb; padding-bottom: 25px; margin-bottom: 30px; }
                .factura-header .logo img { max-height: 110px; }
                .factura-header .logo h2 { color: #2563eb; font-size: 16px; margin-top: 5px; }
                .factura-info { text-align: right; }
                .factura-info h1 { font-size: 28px; color: #1e293b; margin-bottom: 5px; letter-spacing: -0.5px; }
                .factura-info .num-factura { font-size: 18px; color: #2563eb; font-weight: 700; }
                .factura-info .fecha { color: #64748b; font-size: 13px; margin-top: 4px; }
                
                .estado-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; background: {$estadoColor}; color: {$estadoTextoColor}; margin-top: 8px; }
                
                .datos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
                .datos-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; }
                .datos-box h3 { font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 10px; }
                .datos-box p { font-size: 13px; color: #1e293b; margin-bottom: 4px; }
                .datos-box p strong { color: #0f172a; }
                
                .concepto-section { margin-bottom: 25px; }
                .concepto-section h3 { font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 10px; }
                .concepto-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 15px; font-size: 14px; color: #1e40af; font-weight: 500; }
                
                table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                th { background: #f1f5f9; color: #0f172a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 15px; text-align: left; border-bottom: 2px solid #e2e8f0; }
                td { padding: 12px 15px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
                tr:last-child td { border-bottom: none; }
                
                .resumen-financiero { display: flex; justify-content: flex-end; margin-bottom: 30px; }
                .resumen-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 30px; min-width: 280px; }
                .resumen-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
                .resumen-row.total { border-top: 2px solid #2563eb; padding-top: 12px; margin-top: 8px; font-size: 16px; font-weight: 700; color: #2563eb; }
                .resumen-row .label { color: #64748b; }
                .resumen-row .valor { font-weight: 600; color: #1e293b; }
                .resumen-row .valor.verde { color: #22c55e; }
                .resumen-row .valor.rojo { color: #ef4444; }
                
                .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; color: #94a3b8; font-size: 11px; }
                .footer p { margin-bottom: 3px; }
                
                @media print {
                    body { padding: 0; background: white; }
                    #factura-contenido { box-shadow: none; padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div id='factura-contenido'>
                <div class='factura-header'>
                    <div class='logo'>
                        <img src='http://localhost/LOGIN_ORIGINAL/public/img/logo_odontologia.png' alt='Odonto Estética'>
                    </div>
                    <div class='factura-info'>
                        <h1>FACTURA</h1>
                        <div class='num-factura'>{$numFactura}</div>
                        <div class='fecha'>Fecha: {$f['FECHA_EMISION']}</div>
                        <div class='estado-badge'>{$f['estado']}</div>
                    </div>
                </div>

                <div class='datos-grid'>
                    <div class='datos-box'>
                        <h3>Datos del Paciente</h3>
                        <p><strong>{$f['paciente']}</strong></p>
                        <p>Doc: {$f['documento']}</p>
                        <p>Tel: {$f['telefono']}</p>
                        <p>Email: {$f['correo']}</p>
                    </div>
                    <div class='datos-box'>
                        <h3>Datos de la Factura</h3>
                        <p><strong>N°:</strong> {$numFactura}</p>
                        <p><strong>Emisión:</strong> {$f['FECHA_EMISION']}</p>
                        <p><strong>Estado:</strong> {$f['estado']}</p>
                    </div>
                </div>

                <div class='concepto-section'>
                    <h3>Concepto / Procedimientos</h3>
                    <div class='concepto-box'>{$f['tratamiento']}</div>
                </div>";

        // Historial de pagos si existen
        if (!empty($pagos)) {
            $html .= "
                <h3 style='font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 10px;'>Historial de Pagos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>N° Pago</th>
                            <th>Fecha y Hora</th>
                            <th>Método</th>
                            <th style='text-align: right;'>Monto</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($pagos as $p) {
                $fechaPago = $this->formatearFecha($p['FECHA_PAGO']);
                $html .= "<tr>
                            <td>{$p['ID_PAGO']}</td>
                            <td>{$fechaPago}</td>
                            <td>{$p['METODO_PAGO']}</td>
                            <td style='text-align: right; font-weight: 600; color: #22c55e;'>$ " . number_format($p['MONTO'], 2, ',', '.') . "</td>
                          </tr>";
            }
            $html .= "</tbody></table>";
        }

        $html .= "
                <div class='resumen-financiero'>
                    <div class='resumen-box'>
                        <div class='resumen-row'>
                            <span class='label'>Total Factura</span>
                            <span class='valor'>$ " . number_format($f['TOTAL'], 2, ',', '.') . "</span>
                        </div>
                        <div class='resumen-row'>
                            <span class='label'>Total Pagado</span>
                            <span class='valor verde'>$ " . number_format($f['pagado'], 2, ',', '.') . "</span>
                        </div>
                        <div class='resumen-row'>
                            <span class='label'>Saldo Pendiente</span>
                            <span class='valor rojo'>$ " . number_format($pendiente, 2, ',', '.') . "</span>
                        </div>
                        <div class='resumen-row total'>
                            <span class='label'>Estado</span>
                            <span class='valor'>{$f['estado']}</span>
                        </div>
                    </div>
                </div>

                <div class='footer'>
                    <p><strong>Odonto Estética - Salud y Bienestar</strong></p>
                    <p>Generado el: " . date('d/m/Y H:i:s') . "</p>
                </div>
            </div>
            {$scriptAccion}
        </body>
        </html>";

        echo $html;
        exit;
    }

    public function cargaMasiva() {
        if (isset($_GET['descargar_plantilla'])) {
            return $this->descargarPlantillaCsv();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
            header('Content-Type: application/json; charset=utf-8');
            $file = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);
            $successCount = 0; $errorCount = 0; $erroresDetalle = [];
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 6) { $errorCount++; continue; }
                $docPaciente = trim($row[0]);
                if ($docPaciente === 'DOCUMENTO_PACIENTE') continue;

                $fecha = trim($row[1]); $metodo = trim($row[2]);
                $monto = trim($row[3]); $total = trim($row[4]); $estado = trim($row[5]);

                try {
                    $this->modelo->registrarFacturaMasiva($docPaciente, $fecha, $metodo, $monto, $total, $estado);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Fila con doc $docPaciente: " . $e->getMessage();
                }
            }
            fclose($file);
            echo json_encode(['success' => true, 'successCount' => $successCount, 'errorCount' => $errorCount, 'erroresDetalle' => $erroresDetalle]);
            exit;
        }
    }

    public function descargarPlantillaCsv() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_facturacion.csv');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['DOCUMENTO_PACIENTE', 'FECHA_EMISION', 'METODO_PAGO', 'MONTO_ABONADO', 'TOTAL_FACTURA', 'ESTADO_FACTURA']);
        fputcsv($output, ['1001001001', date('Y-m-d H:i:s'), 'Efectivo', '50000', '150000', 'PAGADA']);
        fclose($output);
        exit;
    }
}