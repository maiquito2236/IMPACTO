<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Mis Finanzas</title>
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/menu.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/facturacion_odon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
</head>
<body>
    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>
        
        <main class="facturacion-content">
            <section class="kpi-metrics-row">
                <div class="metric-card-kpi green-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Total Pagado</span>
                        <span class="kpi-number">$<?= number_format($kpis['total_pagado'], 0, ',', '.'); ?></span>
                        <span class="kpi-anchor">Dinero ya liquidado</span>
                    </div>
                </div>
                
                <div class="metric-card-kpi blue-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Saldo Pendiente</span>
                        <span class="kpi-number">$<?= number_format($kpis['saldo_pendiente'], 0, ',', '.'); ?></span>
                        <span class="kpi-anchor">Por cobrar a la clínica</span>
                    </div>
                </div>
                
                <div class="metric-card-kpi red-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-tooth"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Tratamientos</span>
                        <span class="kpi-number"><?= $kpis['total_tratamientos']; ?></span>
                        <span class="kpi-anchor">Procedimientos realizados</span>
                    </div>
                </div>
            </section>

            <section class="billing-grid-layout">
                <div class="panel-card">
                    
                    <div class="tabs-container">
                        <button class="tab-btn active" data-tab="comisiones">
                            <i class="fa-solid fa-list-check"></i> Tratamientos & Comisiones
                        </button>
                        <button class="tab-btn" data-tab="pagos">
                            <i class="fa-solid fa-wallet"></i> Historial de Pagos
                        </button>
                    </div>

                    <div id="tab-comisiones" class="tab-content active">
                        <table id="tablaComisiones" class="display responsive nowrap data-invoice-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Procedimiento</th>
                                    <th>Precio Tratamiento</th>
                                    <th>% Comisión</th>
                                    <th>Ganancia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($comisiones)): ?>
                                    <?php foreach($comisiones as $c): ?>
                                        <tr>
                                            <td><?= $c['fecha']; ?></td>
                                            <td>
                                                <div class="pacient-meta-cell">
                                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['paciente']) ?>&background=0d6efd&color=fff&rounded=true" alt="Avatar">
                                                    <div class="meta-txt"><span><?= htmlspecialchars($c['paciente']); ?></span></div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($c['procedimiento']); ?></td>
                                            <td>$<?= number_format($c['monto'], 0, ',', '.'); ?></td>
                                            <td><?= number_format($c['porcentaje'], 2); ?>%</td>
                                            <td class="bold-cell text-success" data-ganancia="<?= $c['ganancia'] ?>">
                                                $<?= number_format($c['ganancia'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="footer-summary-row">
                                    <td colspan="4"></td>
                                    <td class="bold-cell" style="text-align: right;">Total Ganancia Filtrada:</td>
                                    <td class="total-final-sum" id="totalGananciaFooter">$0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div id="tab-pagos" class="tab-content" style="display: none;">
                        <table id="tablaPagos" class="display responsive nowrap data-invoice-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Mes Liquidado</th>
                                    <th>Fecha de Pago</th>
                                    <th>Producción Base</th>
                                    <th>% Promedio Aplicado</th>
                                    <th>Monto Pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($pagos)): ?>
                                    <?php foreach($pagos as $p): ?>
                                        <tr>
                                            <td class="bold-cell"><?= htmlspecialchars($p['mes']); ?></td>
                                            <td><?= $p['fecha_pago']; ?></td>
                                            <td>$<?= number_format($p['produccion'], 0, ',', '.'); ?></td>
                                            <td><?= number_format($p['porcentaje'], 2); ?>%</td>
                                            <td class="bold-cell text-success">
                                                $<?= number_format($p['pagado'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="/LOGIN_ORIGINAL/public/js/odontologo/facturacion.js"></script>
</body>
</html>