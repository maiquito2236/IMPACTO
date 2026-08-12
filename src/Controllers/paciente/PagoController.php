<?php
namespace App\Controllers\paciente;

use App\Models\paciente\Pago;
use Dompdf\Dompdf;

class PagoController {

    // Muestra el panel financiero principal del paciente (Facturas y Tarjetas de Resumen)
    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        $modelo = new Pago();
        $idUsuario = $_SESSION['usuario_id'];

        // Traemos las facturas del paciente
        $facturasRaw = $modelo->obtenerFacturas($idUsuario);
        $resumen = $modelo->obtenerResumenPagos($idUsuario);

        $facturas = [];
        foreach ($facturasRaw as $factura) {
            // Inyectamos dinamicamente el historial de cuotas/abonos para cada factura de la lista
            $factura['HISTORIAL_CUOTAS'] = $modelo->obtenerHistorialPagos($factura['ID_FACTURA']);
            $facturas[] = $factura;
        }

        require_once __DIR__ . '/../../Views/paciente/pagos.php';
    }
        // =====================================================
        // EXPORTAR DETALLE FINANCIERO A PDF (DOMPDF DINAMICO)
        // =====================================================
        public function exportarPdf() {
            $idFactura = $_GET['id'] ?? null;

            if (!$idFactura) {
                die("ID de factura invalido o inexistente.");
            }

            $modelo = new Pago();
            $factura = $modelo->obtenerFacturaPorId($idFactura);

            if (!$factura) {
                die("La factura solicitada no existe en el sistema.");
            }
            $factura['HISTORIAL_CUOTAS'] = $modelo->obtenerHistorialPagos($idFactura);

            // Requisitos de incrustación de imagenes binarias para DOMPDF
            $rutaLogo = __DIR__ . '/../../../public/img/logo_odontologia.png';
            $logo = '';
            if (file_exists($rutaLogo)) {
                $logoData = base64_encode(file_get_contents($rutaLogo));
                $logo = 'data:image/png;base64,' . $logoData;
            }

            // Mapeo de variables trayendo datos de la BD limpia
            $total = $factura['TOTAL'];
            $abonado = $factura['MONTO_PAGADO'];
            $pendiente = $total - $abonado;
            
            // Usamos el estado calculado directamente de la consulta SQL
            $estadoTexto = strtoupper($factura['ESTADO_CALCULADO'] ?? 'PENDIENTE');

            $isPagada = ($estadoTexto === 'PAGADA');
            $isAbonada = ($estadoTexto === 'ABONADA');
            $isPendiente = ($estadoTexto === 'PENDIENTE');

            if ($estadoTexto === 'PENDIENTE') {
                $badgeBg = '#dc3545';
            } elseif ($estadoTexto === 'ABONADA') {
                $badgeBg = '#0d6efd';
            } else {
                $badgeBg = '#198754';
            }

            $colorAzulOscuro = '#0f4c81';
            $colorAzulClaro  = '#e8f4fd';
            ob_start();
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <style>
                    @page { margin: 40px 50px; }
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; font-size: 13px; line-height: 1.4; }
                    
                    /* Layout del Encabezado */
                    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    .header-left { width: 55%; vertical-align: top; }
                    .header-right { width: 45%; vertical-align: top; border-left: 2px solid #cbd5e1; padding-left: 20px; }
                    
                    .clinica-title { font-size: 24px; font-weight: bold; color: <?= $colorAzulOscuro ?>; margin: 0; letter-spacing: -0.5px; }
                    .clinica-subtitle { font-size: 13px; color: #475569; margin: 2px 0 10px 0; }
                    .clinica-info { font-size: 11px; color: #64748b; margin: 2px 0; }
                    
                    .doc-type { font-size: 14px; font-weight: bold; color: #334155; margin: 0 0 12px 0; text-transform: uppercase; }
                    .meta-row { margin: 6px 0; font-size: 13px; }
                    .meta-label { color: #475569; display: inline-block; width: 120px; }
                    
                    /* Badges de Estado */
                    .badge-factura { background-color: <?= $colorAzulOscuro ?>; color: white; padding: 4px 12px; font-weight: bold; border-radius: 4px; }
                    .badge-estado { background-color: <?= $badgeBg ?>; color: white; padding: 5px 16px; font-weight: bold; border-radius: 4px; display: inline-block; text-align: center; min-width: 90px; }

                    /* Contenedores de Bloques / Tarjetas */
                    .section-container { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
                    .card { border: 1px solid #cbd5e1; border-radius: 6px; vertical-align: top; }
                    .card-header { background-color: <?= $colorAzulOscuro ?>; color: white; padding: 8px 12px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
                    .card-body { padding: 12px; }
                    
                    .field-row { margin: 6px 0; }
                    .field-label { color: #64748b; font-size: 12px; margin-bottom: 2px; }
                    .field-value { font-weight: bold; color: #0f172a; font-size: 13px; }

                    /* Tablas de Contenido y Datos */
                    .table-data { width: 100%; border-collapse: collapse; margin-top: 5px; }
                    .table-data th { background-color: #e2e8f0; color: #334155; font-weight: bold; padding: 10px; border: 1px solid #cbd5e1; text-align: left; }
                    .table-data td { padding: 10px; border: 1px solid #cbd5e1; color: #334155; }
                    
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    
                    .row-total-cancelado { background-color: <?= $colorAzulOscuro ?>; color: white; font-weight: bold; font-size: 14px; }
                    .row-total-cancelado td { color: white !important; border: 1px solid <?= $colorAzulOscuro ?>; }

                    /* Observaciones y Footer */
                    .observaciones-box { border: 1px solid #cbd5e1; border-radius: 4px; padding: 12px; background-color: #f8fafc; color: #334155; }
                    .footer { border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 35px; text-align: center; }
                    .footer-brand { font-weight: bold; color: <?= $colorAzulOscuro ?>; font-size: 13px; margin-bottom: 4px; }
                    .footer-thanks { font-style: italic; color: #475569; font-size: 12px; }
                    .footer-auto { color: #94a3b8; font-size: 10px; margin-top: 8px; }
                </style>
            </head>
            <body>

                <table class="header-table">
                    <tr>
                        <td class="header-left">
                            <?php if (!empty($logo)): ?>
                                <img src="<?= $logo ?>" width="180" alt="Logo">
                            <?php endif; ?>
                            <div class="clinica-subtitle">Cli­nica Odontológica</div>
                            <div class="clinica-info">Calle 10 # 10 - 35, Fuentedeoro Meta</div>
                            <div class="clinica-info">3115204752</div>
                            <div class="clinica-info">NIT: 51936980</div>
                        </td>
                        <td class="header-right">
                            <div class="doc-type">
                                <?= $estadoTexto === 'PAGADA'
                                    ? 'Factura de Servicios Odontológicos'
                                    : 'Recibo de Caja Individual' ?>
                                </div>                           
                                 <div class="meta-row">
                                <span class="meta-label">Factura No:</span>
                                <span class="badge-factura">FAC-<?= str_pad($factura['ID_FACTURA'], 4, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Fecha de emisión:</span>
                                <strong><?= date('d/m/Y', strtotime($factura['FECHA_EMISION'])) ?></strong>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Estado:</span>
                                <span class="badge-estado"><?= $estadoTexto ?></span>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="section-container" style="margin-top: 10px;">
                    <tr>
                        <td class="card" width="49%">
                            <div class="card-header">Paciente</div>
                            <div class="card-body">
                                <div class="field-row">
                                    <div class="field-label">Nombre:</div>
                                    <div class="field-value"><?= htmlspecialchars($factura['PACIENTE_NOMBRE'] ?? 'Sin Nombre Registrado') ?></div>
                                </div>
                                <div class="field-row" style="margin-top: 8px;">
                                    <div class="field-label">Documento:</div>
                                    <div class="field-value"><?= htmlspecialchars($factura['PACIENTE_DOCUMENTO'] ?? 'Sin Documento') ?></div>
                                </div>
                            </div>
                        </td>
                        <td width="2%"></td> <td class="card" width="49%">
                            <div class="card-header">Odontólogo Responsable</div>
                            <div class="card-body">
                                <div class="field-row">
                                    <div class="field-label">Nombre:</div>
                                    <div class="field-value"><?= htmlspecialchars($factura['ODONTOLOGO'] ?? 'No asignado') ?></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="section-container">
                    <tr>
                        <td class="card">
                            <div class="card-header">Tratamiento</div>
                            <div class="card-body">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="55%" style="vertical-align:top;">
                                            <div class="field-row">
                                                <div class="field-label">Tratamiento:</div>
                                                <div class="field-value"><?= htmlspecialchars($factura['PROCEDIMIENTO'] ?? 'No especificado') ?></div>
                                            </div>
                                            <div class="field-row" style="margin-top: 8px;">
                                                <div class="field-label">Fecha de inicio:</div>
                                                <div class="field-value"><?= date('d/m/Y', strtotime($factura['FECHA_EMISION'])) ?></div>
                                            </div>
                                        </td>
                                        <td width="5%" style="border-right: 1px solid #e2e8f0;"></td>
                                        <td width="5%"></td>
                                        <td width="35%" style="vertical-align:middle;">
                                            <div class="field-label" style="font-size:13px;">Valor del tratamiento:</div>
                                            <div style="font-size:24px; font-weight:bold; color:<?= $colorAzulOscuro ?>; margin-top:4px;">
                                                $<?= number_format($total, 0, ',', '.') ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                <div style="margin-bottom: 6px; font-weight: bold; color: <?= $colorAzulOscuro ?>; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                    <?= $estadoTexto === 'PAGADA' ? ' Historial de Pagos ' : ' Información de Pago ' ?>
                </div>

                <?php if ($estadoTexto !== 'PAGADA'): ?>
                    <table class="table-data" style="margin-bottom: 20px;">
                        <thead>
                            <tr>
                                <th width="60%">Concepto</th>
                                <th width="40%" class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span style="color:#0d6efd; font-weight:bold; margin-right:5px;">$</span> Valor abonado</td>
                                <td class="text-right" style="font-weight:bold; color:#198754;">$<?= number_format($abonado, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td><span style="color:#dc3545; font-weight:bold; margin-right:5px;">$</span> Saldo pendiente</td>
                                <td class="text-right" style="font-weight:bold; color:#dc3545;">$<?= number_format($pendiente, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="table-data" style="margin-bottom: 20px;">
                        <thead>
                            <tr>
                                <th width="25%" class="text-center">Fecha</th>
                                <th width="45%">Concepto</th>
                                <th width="30%" class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        <?php if (!empty($factura['HISTORIAL_CUOTAS'])): ?>
                                <?php foreach ($factura['HISTORIAL_CUOTAS'] as $cuota): ?>
                                <tr>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($cuota['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($cuota['concepto']) ?></td>
                                    <td class="text-right">$<?= number_format($cuota['monto'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($factura['FECHA_EMISION'])) ?></td>
                                    <td>Abono Inicial Registrado</td>
                                    <td class="text-right">$<?= number_format($abonado, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="row-total-cancelado">
                                <td colspan="2" class="text-right" style="padding:12px;">TOTAL CANCELADO:</td>
                                <td class="text-right" style="padding:12px;">$<?= number_format($total, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div style="margin-bottom: 6px; font-weight: bold; color: <?= $colorAzulOscuro ?>; text-transform: uppercase; font-size: 12px;">Observaciones</div>
                <div class="observaciones-box">
                   <?= $estadoTexto === 'PAGADA' ? 'Tratamiento finalizado satisfactoriamente.' : 'Pago registrado correctamente en el sistema corporativo.' ?>
                </div>

                <div class="footer">
                    <div class="footer-brand">ODONTOESTETICA</div>
                    <div class="footer-thanks">Gracias por confiar en nuestros servicios.</div>
                    <div class="footer-auto">Documento generado automÃ¡ticamente por el sistema corporativo.</div>
                </div>

            </body>
            </html>
            <?php
            $html = ob_get_clean();

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("factura_" . $idFactura . ".pdf", ["Attachment" => true]);
        }

        // =====================================================
        // ENVIAR COLA DE IMPRESIÓN LOCAL
        // =====================================================
       public function imprimir() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $idFactura = $_GET['id'] ?? null;

        if (!$idFactura) {
            die("Error: El ID de la factura es invÃ¡lido.");
        }

        $modelo = new Pago();

        $factura = $modelo->obtenerFacturaPorId($idFactura);

        if (!$factura) {
            die("Factura no encontrada");
        }

        // historial separado (si lo necesitas en la vista)
        $factura['HISTORIAL_CUOTAS'] = $modelo->obtenerHistorialPagos($idFactura);

        require __DIR__ . '/../../Views/paciente/exports/pago_exportar.php';
        exit;
    }
    // =====================================================
    // VISTA PREVIA DE FACTURA
    // =====================================================
    public function verFactura()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idFactura = $_GET['id'] ?? null;

        if (!$idFactura) {
            die("Factura invÃ¡lida.");
        }

        $modelo = new Pago();

        $factura = $modelo->obtenerFacturaPorId($idFactura);

        if (!$factura) {
            die("Factura no encontrada.");
        }

        $factura['HISTORIAL_CUOTAS'] =
            $modelo->obtenerHistorialPagos($idFactura);

        require __DIR__ . '/../../Views/paciente/exports/pago_exportar.php';
        exit;
    }


    // =====================================================
    // EXPORTAR EXTRACTO A EXCEL (CSV COMPATIBLE)
    // =====================================================
    public function exportarExcel()
    {
        $idFactura = $_GET["id"] ?? null;

        if (!$idFactura) {
            die("ID inválido");
        }

        $modelo = new Pago();
        $factura = $modelo->obtenerFacturaPorId($idFactura);

        if (!$factura) {
            die("Factura no encontrada");
        }

        $montoPagado = $factura["MONTO_PAGADO"] ?? 0;
        $estado      = $factura["ESTADO_CALCULADO"] ?? "Pendiente";
        $deuda       = $factura["TOTAL"] - $montoPagado;

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=factura_" . $idFactura . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Agregar BOM (Byte Order Mark) para que Excel reconozzca UTF-8
        echo chr(0xEF).chr(0xBB).chr(0xBF);

        echo "<table border=\"1\">";
        echo "
        <tr style=\"background:#00205b;color:white;font-weight:bold;\">
            <th>Factura</th>
            <th>Paciente</th>
            <th>Documento</th>
            <th>Tratamiento</th>
            <th>Odontólogo</th>
            <th>Fecha</th>
            <th>Abonado</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Deuda</th>
        </tr>";

        echo "
        <tr>
            <td>FAC-" . str_pad($factura["ID_FACTURA"],4,"0",STR_PAD_LEFT) . "</td>
            <td>" . htmlspecialchars($factura["PACIENTE_NOMBRE"]) . "</td>
            <td>" . htmlspecialchars($factura["PACIENTE_DOCUMENTO"]) . "</td>
            <td>" . htmlspecialchars($factura["PROCEDIMIENTO"]) . "</td>
            <td>" . htmlspecialchars($factura["ODONTOLOGO"]) . "</td>
            <td>" . date("d/m/Y", strtotime($factura["FECHA_EMISION"])) . "</td>
            <td>$" . number_format($montoPagado,0,",",".") . "</td>
            <td>$" . number_format($factura["TOTAL"],0,",",".") . "</td>
            <td>" . $estado . "</td>
            <td>$" . number_format($deuda,0,",",".") . "</td>
        </tr>";

        echo "</table>";
        exit;
    }

    // =====================================================
    // GUARDAR PAGO (PACIENTE)
    // =====================================================
    public function guardarPago() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data["factura_id"]) && isset($data["monto"]) && isset($data["metodo"])) {
            $modelo = new Pago();
            $resultado = $modelo->registrarPago($data["factura_id"], $data["monto"], $data["metodo"]);
            
            if ($resultado) {
                $db = \App\Config\Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT 
                        p.ID_PACIENTE AS PACIENTE_ID_PACIENTE, 
                        CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente_nombre,
                        f.TOTAL,
                        (SELECT SUM(MONTO) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA AND ESTADO = 'PAGADO') as total_pagado
                    FROM factura f 
                    LEFT JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA 
                    JOIN paciente p ON (hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE OR f.PACIENTE_ID_PACIENTE = p.ID_PACIENTE) 
                    JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                    WHERE f.ID_FACTURA = ?
                ");
                $stmt->execute([$data['factura_id']]);
                $refs = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($refs) {
                    $monto_fmt = number_format($data['monto'], 2, ',', '.');
                    $esPagoTotal = ($refs['total_pagado'] >= $refs['TOTAL']);
                    if ($esPagoTotal) {
                        \App\Helpers\Notificador::enviarAAdmin(23, "El paciente {$refs['paciente_nombre']} ha cancelado la totalidad de la factura #{$data['factura_id']} por $$monto_fmt.");
                        \App\Helpers\Notificador::enviarAPaciente($refs['PACIENTE_ID_PACIENTE'], 5, "Hemos recibido tu pago total de $$monto_fmt a la factura #{$data['factura_id']}. Gracias!");
                    } else {
                        \App\Helpers\Notificador::enviarAAdmin(20, "El paciente {$refs['paciente_nombre']} realizo un abono por $$monto_fmt a la factura #{$data['factura_id']}.");
                        \App\Helpers\Notificador::enviarAPaciente($refs['PACIENTE_ID_PACIENTE'], 19, "Hemos recibido tu abono de $$monto_fmt a la factura #{$data['factura_id']}. Gracias!");
                    }
                }

                echo json_encode(["status" => "success", "message" => "Pago registrado correctamente"]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo registrar el pago en la base de datos."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        }
        exit;
    }
}