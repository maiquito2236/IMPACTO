<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sonríe Dental - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/dashboard.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>

    <div class="menu-layout">
 
            <?php require_once __DIR__ . '/layouts/menu.php'; ?>
    
        <main class="menu-main-content">

            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            <!-- KPIs -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon icon-blue">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="kpi-info">
                        <p>Citas programadas</p>
                        <h3><?php echo $citasHoy ?? 0; ?> <span class="sub-label">para hoy</span></h3>
                        <a href="/LOGIN_ORIGINAL/admin/agenda">Ver calendario →</a>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon icon-green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div class="kpi-info">
                        <p>Pacientes atendidos</p>
                        <h3><?php echo $pacientesAtendidos ?? 0; ?> <span class="sub-label">hoy</span></h3>
                        <a href="/LOGIN_ORIGINAL/admin/gestion_usuario">Ver pacientes →</a>
                    </div>
                </div>



                <div class="kpi-card">
                    <div class="kpi-icon icon-amber">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div class="kpi-info">
                        <p>Ingresos del día</p>
                        <h3>$<?php echo number_format($ingresosHoy ?? 0, 0, ',', '.'); ?> <span class="sub-label">hoy</span></h3>
                        <a href="/LOGIN_ORIGINAL/admin/facturacion">Ver facturación →</a>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Actividad de la clínica</h3>
                        <div class="chart-legends-top">
                            <span class="legend-indicator blue">Citas</span>
                            <span class="legend-indicator green">Pacientes</span>
                            <span class="legend-indicator amber">Ingresos</span>
                            
                            <!-- CORREGIDO: Estructura interactiva del Dropdown de filtrado -->
                            <div class="chart-filter-dropdown">
                                <button class="date-dropdown dropdown-small" id="btn-chart-filter">
                                    Últimos 7 días <i class="fas fa-chevron-down" style="margin-left: 6px; font-size: 10px;"></i>
                                </button>
                                <div class="chart-dropdown-menu is-hidden" id="menu-chart-filter">
                                    <a href="#" data-value="7">Últimos 7 días</a>
                                    <a href="#" data-value="30">Últimos 30 días</a>
                                    <a href="#" data-value="mes">Este mes</a>
                                    <a href="#" data-value="año">Este año</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="line-chart-area">
                        <svg viewBox="0 0 600 160" class="svg-chart">
                            <g stroke="#f1f5f9" stroke-width="1">
                                <line x1="0" y1="30" x2="600" y2="30" stroke-dasharray="4"/>
                                <line x1="0" y1="65" x2="600" y2="65" stroke-dasharray="4"/>
                                <line x1="0" y1="100" x2="600" y2="100" stroke-dasharray="4"/>
                                <line x1="0" y1="135" x2="600" y2="135" stroke-dasharray="4"/>
                            </g>
                            
                            <path d="<?php echo $pathCitas; ?>" fill="none" stroke="#0061ff" stroke-width="2.5"/>
                            <path d="<?php echo $pathPacientes; ?>" fill="none" stroke="#22c55e" stroke-width="2.5"/>
                            <path d="<?php echo $pathIngresos; ?>" fill="none" stroke="#f59e0b" stroke-width="2.5"/>
                            
                            <?php echo $puntosExtraCitas; ?>
                        </svg>
                        <div class="x-axis">
                            <?php foreach($diasStr as $d): ?>
                                <span><?php echo $d; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Estado de citas de hoy</h3>
                    </div>
                    <div class="donut-chart-box">
                        <?php 
                            $totalCitasE = 0;
                            $estadosHTML = '';
                            $clases = ['Confirmada' => 'primary', 'Pendiente' => 'warning', 'Completada' => 'success', 'Cancelada' => 'danger'];
                            if(!empty($estadoCitasHoy)) {
                                foreach($estadoCitasHoy as $est) {
                                    $totalCitasE += $est['cantidad'];
                                }
                                foreach($estadoCitasHoy as $est) {
                                    $porcentaje = $totalCitasE > 0 ? round(($est['cantidad'] / $totalCitasE) * 100) : 0;
                                    $clase = $clases[$est['NOMBRE_ESTADO']] ?? 'purple';
                                    $estadosHTML .= '<li class="legend-item"><span class="dot dot-'.$clase.'"></span> '.htmlspecialchars($est['NOMBRE_ESTADO']).' <span class="legend-count">'.$est['cantidad'].' ('.$porcentaje.'%)</span></li>';
                                }
                            } else {
                                $estadosHTML = '<li class="legend-item text-muted">No hay citas para hoy</li>';
                            }
                        ?>
                        <div class="donut-chart">
                            <div class="donut-center-text">
                                <span class="donut-title">Total</span>
                                <span class="donut-num"><?php echo $totalCitasE; ?></span>
                            </div>
                        </div>
                        <ul class="chart-legend">
                            <?php echo $estadosHTML; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Listas Inferiores -->
            <div class="bottom-grid">
                
                <!-- CARD: RECORDATORIOS IMPORTANTES -->
                <div class="list-container">
                    <div class="list-header">
                        <h3>Recordatorios importantes</h3>
                    </div>
                    <div class="list-items">
                        <?php if(!empty($recordatorios)): foreach($recordatorios as $index => $rec): ?>
                        <div class="item-row clickable-row reminder-extra <?php echo $index >= 3 ? 'is-hidden' : ''; ?>">
                            <div class="item-left">
                                <div class="list-icon icon-blue-soft">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0061ff" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
                                </div>
                                <div class="item-text">
                                    <h4><?php echo htmlspecialchars($rec['MENSAJE']); ?></h4>
                                    <p><?php echo date('d M, Y', strtotime($rec['FECHA_ENVIO'])); ?></p>
                                </div>
                            </div>
                            <span class="chevron">›</span>
                        </div>
                        <?php endforeach; else: ?>
                            <p style="padding:15px; color:#64748b; font-size:14px; text-align:center;">No hay recordatorios pendientes</p>
                        <?php endif; ?>
                    </div>
                    <a href="#" id="btn-toggle-reminders" class="bottom-list-link">Ver todos los recordatorios →</a>
                </div>

                <!-- CARD: PRÓXIMAS CITAS -->
                <div class="list-container">
                    <div class="list-header">
                        <h3>Próximas citas</h3>
                    </div>
                    <div class="list-items">
                        <?php if(!empty($proximasCitas)): foreach($proximasCitas as $index => $cita): ?>
                        <div class="item-row clickable-row <?php echo $index >= 2 ? 'is-hidden' : ''; ?>">
                            <div class="item-left">
                                <span class="time-indicator"><?php echo htmlspecialchars($cita['fecha_corta']); ?></span>
                                <div class="avatar av-<?php echo ($index % 7) + 1; ?>"></div>
                                <div class="item-text">
                                    <h4><?php echo htmlspecialchars($cita['paciente_nombre']); ?></h4>
                                    <p><?php echo htmlspecialchars($cita['MOTIVO'] ?? 'Consulta general'); ?></p>
                                </div>
                            </div>
                            <span class="chevron">›</span>
                        </div>
                        <?php endforeach; else: ?>
                            <p style="padding:15px; color:#64748b; font-size:14px; text-align:center;">No hay citas próximas</p>
                        <?php endif; ?>
                    </div>
                    <a href="#" id="btn-toggle-citas" class="bottom-list-link">Ver todas las citas →</a>
                </div>


            </div>
        </main> 
    </div> 

    <script src="/LOGIN_ORIGINAL/public/js/administrador/dashboard.js"></script>
</body>
</html>