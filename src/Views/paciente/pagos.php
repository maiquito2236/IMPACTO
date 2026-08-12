<?php
/**
 * VISTA: HISTORIAL DE PAGOS - PACIENTE (COMPATIBLE W3C Y BD REAL)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos - Odonto Estética</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/pagos.css">
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">
        
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-grow-1 p-4 overflow-auto">
        
            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <div class="card text-center border-0 shadow-sm p-3 h-100 card-summary">
                        <span class="text-muted fw-semibold font-sm">Precio Total</span>
                        <h3 class="fw-bold text-primary mt-2">
                            $<?= number_format($resumen['precio_total'] ?? 0, 0, ',', '.') ?>
                        </h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-0 shadow-sm p-3 h-100 card-summary">
                        <span class="text-muted fw-semibold font-sm">Abono Total</span>
                        <h3 class="fw-bold text-success mt-2">
                            $<?= number_format($resumen['abono_total'] ?? 0, 0, ',', '.') ?>
                        </h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-0 shadow-sm p-3 h-100 card-summary">
                        <span class="text-muted fw-semibold font-sm">Deuda Total</span>
                        <h3 class="fw-bold text-warning-dark mt-2">
                            $<?= number_format($resumen['deuda_total'] ?? 0, 0, ',', '.') ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 mt-4 main-data-card">
                <div class="mb-4">
                    <h2 class="h5 m-0 fw-bold text-dark-blue">
                        Historial de Actividad Financiera
                    </h2>
                </div>
            
                <div class="table-responsive">
                    <table id="tablaPagos" class="table table-bordered dataTable">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Fecha Emisión</th>
                                <th>Tratamiento</th>
                                <th>Valor Total</th>
                                <th>Abonado</th>
                                <th>Pendiente</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($facturas as $factura): ?>
                           <?php
                               $total   = floatval($factura['TOTAL'] ?? 0);
                                $abonado = floatval($factura['MONTO_PAGADO'] ?? 0);
                                $deuda   = max(0, $total - $abonado);

                                $estadoTexto = $factura['ESTADO_CALCULADO'] ?? 'Pendiente';
                                   $badge = match($estadoTexto) {
                                        'Pagada' => 'badge-pagada',
                                        'Abonada' => 'badge-abonando',
                                        default => 'badge-pendiente'
                                    };
                                ?>
                            <tr>
                                
                                <td>
                                    FAC-<?= str_pad($factura['ID_FACTURA'], 4, '0', STR_PAD_LEFT) ?>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($factura['FECHA_EMISION'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($factura['PROCEDIMIENTO']) ?>
                                </td>

                                <td class="fw-semibold">
                                    $<?= number_format($factura['TOTAL'],0,',','.') ?>
                                </td>

                                <td class="text-success fw-semibold">
                                    $<?= number_format($abonado,0,',','.') ?>
                                </td>

                                <td class="text-danger fw-semibold">
                                    $<?= number_format($deuda,0,',','.') ?>
                                </td>

                                <td data-estado="<?= $estadoTexto ?>" class="estado-col">
                                    <span class="badge <?= $badge ?>">
                                        <?= $estadoTexto ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-icons">
                                        <?php if ($deuda > 0): ?>
                                        <a href="#" class="text-muted btn-pagar-paciente" style="color:var(--c-green) !important;" data-id="<?= $factura['ID_FACTURA'] ?>" data-pendiente="<?= $deuda ?>" title="Pagar ahora">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                        </a>
                                        <?php endif; ?>

                                        <a href="#" class="text-muted btn-ver-detalle" data-especialista="<?= htmlspecialchars($factura['ODONTOLOGO'] ?? 'No asignado') ?>" title="Ver detalles">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>

                                        <a href="#"
                                        class="text-muted btn-opciones-exportar"
                                        data-id="<?= $factura['ID_FACTURA'] ?>" title="Descargar PDF">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="overlay"></div>

    <div id="panel-detalle">
        <div class="card border-0 p-4 sidebar-detail-card">
            <h2 class="h5 fw-bold text-dark-blue mb-4 text-center">Actividad Financiera</h2>
            
            <div class="d-flex flex-column gap-3">
                <div class="detail-item-box p-2 border rounded">
                    <span class="d-block text-muted font-xs fw-bold text-uppercase">Concepto y Descripción</span>
                    <span id="det-concepto" class="text-dark-blue font-sm fw-medium d-block mt-1">Selecciona un registro...</span>
                </div>

                <div class="detail-item-box p-2 border rounded">
                    <span class="d-block text-muted font-xs fw-bold text-uppercase">Fecha de cita Asociada</span>
                    <span id="det-fecha" class="text-dark-blue font-sm fw-medium d-block mt-1">--</span>
                </div>

                <div class="detail-item-box p-2 border rounded">
                    <span class="d-block text-muted font-xs fw-bold text-uppercase">Especialista Responsable</span>
                    <span id="det-especialista" class="text-dark-blue font-sm fw-medium d-block mt-1">--</span>
                </div>

                <div class="detail-item-box p-2 border rounded">
                    <span class="d-block text-muted font-xs fw-bold text-uppercase">Monto abonado</span>
                    <span id="det-abonado" class="text-dark-blue font-sm fw-semibold d-block mt-1">--</span>
                </div>

                <div class="detail-item-box p-2 border rounded">
                    <span class="d-block text-muted font-xs fw-bold text-uppercase">Costo Total del Procedimiento</span>
                    <span id="det-total" class="text-dark-blue font-sm fw-semibold d-block mt-1">--</span>
                </div>

                <div class="detail-item-box p-2 border rounded d-flex justify-content-between align-items-center">
                    <span class="text-muted font-xs fw-bold text-uppercase">Estado Actual</span>
                    <span id="det-estado" class="badge bg-secondary px-3 py-1 fs-7">--</span>
                </div>

                <div class="detail-item-box p-2 border rounded d-flex justify-content-between align-items-center mt-2 bg-light-gray">
                    <span class="text-muted font-xs fw-bold text-uppercase">Deuda Total</span>
                    <span id="det-deuda" class="text-dark-blue fw-bold font-sm">--</span>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button id="cerrar-detalle" class="btn">Volver</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-exportar" class="p-4">
        <div class="text-center mb-3">
            <div class="bg-light d-inline-block p-3 rounded-circle mb-2">
                <i class="fa-solid fa-print fs-3 text-primary"></i>
            </div>
            <h5 class="fw-bold text-dark-blue mb-1">Opciones de Exportación</h5>
            <p class="text-muted small">¿Cómo deseas procesar este documento financiero?</p>
        </div>
        
        <div class="d-grid gap-2">
            <a id="btn-modal-pdf" href="#" class="btn btn-outline-danger d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                <i class="fa-solid fa-file-pdf fs-5"></i> Descargar PDF
            </a>
            <a id="btn-modal-print"
            href="#"
            target="_blank"
            class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                <i class="fa-solid fa-print fs-5"></i>
                Imprimir Recibo
            </a>
            <a id="btn-modal-excel" href="#" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                <i class="fa-solid fa-file-excel fs-5"></i> Descargar Excel
            </a>
            <button id="cerrar-modal-exportar" class="btn btn-cerrar-panel mt-2">Cancelar</button>
        </div>
    </div>

    <div id="modal-pago" class="p-4" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000; background: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); width: 90%; max-width: 400px;">
        <div class="text-center mb-3">
            <div class="bg-light d-inline-block p-3 rounded-circle mb-2">
                <i class="fa-solid fa-hand-holding-dollar fs-3 text-success"></i>
            </div>
            <h5 class="fw-bold text-dark-blue mb-1">Registrar Pago / Abono</h5>
            <p class="text-muted small">Ingresa los detalles del pago de tu factura.</p>
        </div>
        
        <form id="formPagoPaciente">
            <input type="hidden" id="pagoFacturaId">
            <input type="hidden" id="pagoPendienteMonto">
            
            <div class="mb-3">
                <label class="form-label font-xs fw-bold text-muted">Deuda Pendiente</label>
                <input type="text" id="pagoPendienteDisplay" class="form-control bg-light text-danger fw-bold" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label font-xs fw-bold text-muted">Método de Pago</label>
                <select id="pagoMetodo" class="form-select" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label font-xs fw-bold text-muted">Monto a Pagar ($)</label>
                <input type="number" id="pagoMonto" class="form-control fw-bold" required min="1">
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success fw-bold py-2">Confirmar Pago</button>
                <button type="button" id="cerrar-modal-pago" class="btn btn-outline-secondary py-2 mt-1">Cancelar</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="/LOGIN_ORIGINAL/public/js/paciente/pagos.js"></script>
</body>
</html>
