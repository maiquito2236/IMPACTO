<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($factura)) { die("Error de carga de datos."); }

// Forzar lectura de datos del paciente para que no se pierdan al imprimir
$nombreP = $factura['PACIENTE_NOMBRE'] ?? 'N/A';
$docP    = $factura['PACIENTE_DOCUMENTO'] ?? 'N/A';
// Lógica de estado (se mantiene igual)
$total     = floatval($factura['TOTAL'] ?? 0);
$abonado = floatval($factura['MONTO_PAGADO'] ?? 0);
$pendiente = $total - $abonado;
$estadoBD = strtoupper($factura['ESTADO_CALCULADO'] ?? 'PENDIENTE');

if ($estadoBD === 'PAGADA') { $estadoTexto = 'PAGADA'; $badgeBg = '#198754'; }
elseif ($estadoBD === 'ANULADA') { $estadoTexto = 'ANULADA'; $badgeBg = '#6c757d'; }
elseif ($estadoBD === 'ABONANDO') { $estadoTexto = 'ABONANDO'; $badgeBg = '#d97706'; }
else { $estadoTexto = ($abonado > 0) ? 'ABONANDO' : 'PENDIENTE'; $badgeBg = ($abonado > 0) ? '#d97706' : '#dc3545'; }

$esPagada = ($estadoTexto === 'PAGADA');
$esAbonada = ($estadoTexto === 'ABONANDO');
$colorAzulOscuro = '#00205b';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Imprimir Recibo - Odonto Estética</title>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; color: #1e293b; background-color: #fff; font-size: 14px; }
    
    /* Encabezado Estilo Maqueta */
    .header-container { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 25px; }
    .header-left { display: flex; align-items: center; gap: 15px; }
    .logo { width: 180px; height: auto; }
    .clinica-title { font-size: 26px; font-weight: bold; color: <?= $colorAzulOscuro ?>; margin: 0; line-height: 1.1; }
    .clinica-subtitle { font-size: 14px; color: #475569; margin: 3px 0 8px 0; font-weight: 500; }
    .clinica-info { font-size: 12px; color: #64748b; margin: 2px 0; }
    
    .header-right { border-left: 2px solid #cbd5e1; padding-left: 25px; text-align: left; min-width: 280px; }
    .doc-type { font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .meta-row { margin: 8px 0; font-size: 14px; }
    .meta-label { color: #475569; display: inline-block; width: 130px; }
    
    /* Badges */
    .badge-factura { background-color: <?= $colorAzulOscuro ?>; color: white; padding: 4px 12px; font-weight: bold; border-radius: 4px; }
    .badge-estado { background-color: <?= $badgeBg ?>; color: white; padding: 5px 16px; font-weight: bold; border-radius: 4px; display: inline-block; text-align: center; }

    /* Estructuras de Bloques de Datos (Grid de Dos Columnas) */
    .columns-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .card { border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; }
    .card-header { background-color: <?= $colorAzulOscuro ?>; color: white; padding: 10px 14px; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
    .card-body { padding: 15px; }
    
    .field-row { margin: 8px 0; }
    .field-label { color: #64748b; font-size: 12px; margin-bottom: 2px; }
    .field-value { font-weight: bold; color: #0f172a; font-size: 14px; }

    /* Bloque Unificado de Tratamiento */
    .tratamiento-flex { display: flex; justify-content: space-between; align-items: center; }
    .valor-tag { font-size: 26px; font-weight: bold; color: <?= $colorAzulOscuro ?>; margin-top: 4px; }

    /* Tablas Financieras */
    .seccion-titulo-tabla { margin: 25px 0 8px 0; font-weight: bold; color: <?= $colorAzulOscuro ?>; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
    .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .table-data th { background-color: #f1f5f9; color: #334155; font-weight: bold; padding: 12px; border: 1px solid #cbd5e1; text-align: left; }
    .table-data td { padding: 12px; border: 1px solid #cbd5e1; color: #334155; }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    
    .row-total-cancelado { background-color: <?= $colorAzulOscuro ?>; color: white; font-weight: bold; font-size: 15px; }
    .row-total-cancelado td { color: white !important; border: 1px solid <?= $colorAzulOscuro ?>; }

    /* Caja de Observaciones */
    .observaciones-box { border: 1px solid #cbd5e1; border-radius: 4px; padding: 15px; background-color: #f8fafc; color: #334155; margin-top: 5px; }
    
    /* Pie de página e Impresión */
    .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 40px; text-align: center; }
    .footer-brand { font-weight: bold; color: <?= $colorAzulOscuro ?>; font-size: 14px; margin-bottom: 4px; }
    .footer-thanks { font-style: italic; color: #475569; font-size: 13px; }
    
    .no-print { text-align: center; margin-top: 35px; }
    .btn-imprimir { background: <?= $colorAzulOscuro ?>; color: white; border: none; padding: 14px 30px; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: bold; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: all 0.2s; }
    .btn-imprimir:hover { background: #071635; transform: translateY(-1px); }
    
    @media print { 
        @page { margin: 0; } /* ESTA ES LA LÍNEA MÁGICA QUE QUITA LA URL Y LA FECHA */
        .no-print { display: none; } 
        body { margin: 1.5cm; padding: 0; font-size: 13px; } /* Le damos margen a la hoja para compensar */
        .card { border: 1px solid #94a3b8; }
        .table-data th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .row-total-cancelado { background-color: <?= $colorAzulOscuro ?> !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>

<div class="header-container">
    <div class="header-left">
        <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" class="logo" alt="Logo" onerror="this.style.display='none'">
        <div>
            <div class="clinica-subtitle">Clínica Odontológica</div>
            <div class="clinica-info">Calle 10 #10-35, Fuentedeoro Meta</div>
            <div class="clinica-info">3115204752</div>
            <div class="empresa-info">
                <strong>NIT:</strong> 51936980
            </div>
        </div>
    </div>
    <div class="header-right">
        <div class="doc-type">
        <?php
        if($esPagada){
            echo 'Factura de Servicios Odontológicos';
        }elseif($esAbonada){
            echo 'Recibo de Abono';
        }else{
            echo 'Factura Pendiente de Pago';
        }
        ?>
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
    </div>
</div>

<div class="columns-container">
        <div class="card">
            <div class="card-header">Paciente</div>
            <div class="card-body">
                <div class="field-row">
                    <div class="field-label">Nombre:</div>
                    <div class="field-value">
                        <?= htmlspecialchars($factura['PACIENTE_NOMBRE'] ?? '') ?>
                    </div>
                </div>
                <div class="field-row" style="margin-top: 10px;">
                    <div class="field-label">Documento:</div>
                    <div class="field-value">
                    <?= htmlspecialchars($factura['PACIENTE_DOCUMENTO'] ?? '') ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Odontólogo Responsable</div>
        <div class="card-body">
            <div class="field-row">
                <div class="field-label">Nombre:</div>
                <div class="field-value"><?= htmlspecialchars($factura['ODONTOLOGO'] ?? '') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">Tratamiento</div>
    <div class="card-body tratamiento-flex">
        <div style="flex: 1;">
            <div class="field-row">
                <div class="field-label">Tratamiento:</div>
                <div class="field-value"><?= htmlspecialchars($factura['PROCEDIMIENTO']) ?></div>
            </div>
            <div class="field-row" style="margin-top: 10px;">
                <div class="field-label">Fecha de inicio:</div>
                <div class="field-value"><?= date('d/m/Y', strtotime($factura['FECHA_EMISION'])) ?></div>
            </div>
            <?php if ($esPagada): ?>
                <div class="field-row" style="margin-top: 10px;">
                    <div class="field-label">Fecha de finalización:</div>
                    <div class="field-value"><?= !empty($factura['FECHA_FIN']) ? date('d/m/Y', strtotime($factura['FECHA_FIN'])) : '' ?></div>
                </div>
                <div class="field-row" style="margin-top: 10px;">
                    <div class="field-label">Observaciones del tratamiento:</div>
                    <div class="field-value" style="font-weight:normal; font-size:13px; color:#475569;">Instalación y seguimiento de brackets continuos.</div>
                </div>
            <?php endif; ?>
        </div>
        <div style="border-left: 1px solid #cbd5e1; padding-left: 30px; min-width: 200px;">
            <div class="field-label">Valor del tratamiento:</div>
            <div class="valor-tag">$<?= number_format($total, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div class="seccion-titulo-tabla">
    <?= $esPagada ? ' Historial de Pagos ' : ' Información de Pago ' ?>
</div>

<?php if (!$esPagada): ?>
    <table class="table-data">
        <thead>
            <tr>
                <th width="60%">Concepto</th>
                <th width="40%" class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>$</strong> Valor abonado</td>
                <td class="text-right" style="font-weight:bold; color:#198754;">$<?= number_format($abonado, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td><strong>§</strong> Saldo pendiente</td>
                <td class="text-right" style="font-weight:bold; color:#dc3545;">$<?= number_format($pendiente, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
<?php else: ?>
    <table class="table-data">
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
                <?php if($pendiente > 0): ?>
                    <tr>
                        <td class="text-center"><?= date('d/m/Y') ?></td>
                        <td>Pago Final de Saldo</td>
                        <td class="text-right">$<?= number_format($pendiente, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>
            <tr class="row-total-cancelado">
                <td colspan="2" class="text-right" style="padding:12px;">TOTAL CANCELADO:</td>
                <td class="text-right" style="padding:12px;">$<?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<div style="font-weight: bold; color: <?= $colorAzulOscuro ?>; text-transform: uppercase; font-size: 12px; margin-top: 15px;">Observaciones</div>
<div class="observaciones-box">
    <?= $esPagada ? 'Tratamiento finalizado satisfactoriamente.' : 'Pago registrado correctamente en el sistema de caja corporativo.' ?>
</div>

<div class="footer">
    <div class="footer-brand">ODONTOESTÉTICA</div>
    <div class="footer-thanks">Gracias por confiar en nuestros servicios.</div>
</div>


<script>
window.onload = function() {
    window.print();
};
</script>
</body>
</html>