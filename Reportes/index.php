<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../menu/global.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
<div class="menu-layout">
 
    <?php require_once "../menu/menu.php" ?>
 
    <main class="menu-main-content">
 
        <!-- HEADER -->
        <div class="rep-header">
            <div class="rep-header-title">
                <h1>Reportes y estadísticas</h1>
                <p>Análisis financiero y estado del sistema</p>
            </div>
            <div class="rep-header-actions">
                <button class="btn-outline"><i class="fa-regular fa-calendar"></i> Este periodo</button>
                <button class="btn-outline btn-export"><i class="fa-solid fa-arrow-up-from-bracket"></i> Exportar</button>
            </div>
        </div>
 
        <!-- KPI GRID -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon icon-green"><i class="fa-solid fa-dollar-sign"></i></div>
                <div class="kpi-info">
                    <h3>Ingresos Totales</h3>
                    <div class="number">$58,750.00</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-up"></i> +18% vs periodo anterior</div>
                    <a href="#" class="kpi-link">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-blue"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div class="kpi-info">
                    <h3>Ganancia Neta</h3>
                    <div class="number">$46,270.00</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-up"></i> +26% vs periodo anterior</div>
                    <a href="#" class="kpi-link">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-orange"><i class="fa-solid fa-percent"></i></div>
                <div class="kpi-info">
                    <h3>Margen de Ganancia</h3>
                    <div class="number">78.7%</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-up"></i> +5.4% vs periodo anterior</div>
                    <a href="#" class="kpi-link">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-purple"><i class="fa-regular fa-file-lines"></i></div>
                <div class="kpi-info">
                    <h3>Total de Facturas</h3>
                    <div class="number">45</div>
                    <div class="trend text-muted">Documentos procesados</div>
                    <a href="#" class="kpi-link">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-teal"><i class="fa-solid fa-user-check"></i></div>
                <div class="kpi-info">
                    <h3>Pacientes Atendidos</h3>
                    <div class="number">158</div>
                    <div class="trend text-muted">En el periodo actual</div>
                    <a href="#" class="kpi-link">Ver detalle</a>
                </div>
            </div>
        </section>
 
        <!-- DASHBOARD GRID -->
        <div class="dashboard-grid">
 
            <!-- COLUMNA IZQUIERDA -->
            <div class="left-col">
 
                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Resultados Financieros</h2>
                        <select class="select-sm"><option>Este periodo</option></select>
                    </div>
 
                    <div class="chart-summary">
                        <div class="chart-stat">
                            <span>Ingresos</span>
                            <strong style="color:var(--c-green);">$58,750.00</strong>
                        </div>
                        <div class="chart-stat">
                            <span>Ganancia Neta</span>
                            <strong style="color:var(--c-blue);">$46,270.00</strong>
                        </div>
                        <div class="chart-stat">
                            <span>Margen</span>
                            <strong>78.7%</strong>
                        </div>
                    </div>
 
                    <div class="chart-legend">
                        <span><span class="dot c-green"></span> Ingresos</span>
                        <span><span class="dot c-red"></span> Egresos</span>
                        <span><span class="dot c-blue"></span> Ganancia Neta</span>
                    </div>
 
                    <div class="chart-wrapper">
                        <svg class="line-chart-svg" viewBox="0 0 600 200" preserveAspectRatio="none">
                            <line x1="0" y1="20"  x2="600" y2="20"  stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="0" y1="60"  x2="600" y2="60"  stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="0" y1="100" x2="600" y2="100" stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="0" y1="140" x2="600" y2="140" stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="0" y1="180" x2="600" y2="180" stroke="#e2e8f0" stroke-width="1"/>
                            <path d="M 0 160 L 100 150 L 200 165 L 300 145 L 400 160 L 500 155 L 600 150" fill="none" stroke="#ef4444" stroke-width="2"/>
                            <path d="M 0 60  L 100 40  L 200 80  L 300 30  L 400 50  L 500 20  L 600 45"  fill="none" stroke="#22c55e" stroke-width="2"/>
                            <path d="M 0 100 L 100 80  L 200 110 L 300 70  L 400 90  L 500 60  L 600 85"  fill="none" stroke="#3b82f6" stroke-width="2"/>
                            <circle cx="500" cy="20"  r="4" fill="#22c55e"/>
                            <circle cx="500" cy="60"  r="4" fill="#3b82f6"/>
                            <circle cx="500" cy="155" r="4" fill="#ef4444"/>
                            <line x1="500" y1="0" x2="500" y2="180" stroke="#cbd5e1" stroke-dasharray="4" stroke-width="1"/>
                        </svg>
                        <div class="chart-xaxis">
                            <span>1 May</span><span>4 May</span><span>7 May</span>
                            <span>10 May</span><span>13 May</span><span>16 May</span>
                        </div>
                    </div>
 
                    <div class="chart-bottom-stats">
                        <div class="stat-box">
                            <span>Promedio diario de ingresos</span>
                            <strong>$3,671.88</strong>
                        </div>
                        <div class="stat-box">
                            <span>Promedio diario de egresos</span>
                            <strong>$1,820.00</strong>
                        </div>
                        <div class="stat-box">
                            <span>Día con mayor ingreso</span>
                            <strong>16/05/2026 <small>($8,750.00)</small></strong>
                        </div>
                        <div class="stat-box">
                            <span>Día con mayor egreso</span>
                            <strong>15/05/2026 <small>($2,480.00)</small></strong>
                        </div>
                    </div>
                </div>
 
                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Estado de Resultados</h2>
                        <a href="#" class="link-sm">Ver reporte completo</a>
                    </div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Este Periodo</th>
                                <th>Periodo Anterior</th>
                                <th>Variación</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosBody"></tbody>
                    </table>
                </div>
 
            </div>
 
            <!-- COLUMNA DERECHA -->
            <div class="right-col">
 
                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Estado del Sistema</h2>
                        <a href="#" class="link-sm">Ver bitácora completa</a>
                    </div>
 
                    <div class="system-status-banner">
                        <div class="status-left">
                            <div class="icon-circle bg-green"><i class="fa-solid fa-check"></i></div>
                            <div>
                                <h4>Óptimo</h4>
                                <p>Todos los sistemas funcionan correctamente.</p>
                            </div>
                        </div>
                        <div class="status-right">
                            <span>Tiempo de actividad</span>
                            <strong>99.9%</strong>
                            <p>Últimos 30 días</p>
                        </div>
                    </div>
 
                    <div class="system-modules-grid">
                        <div class="module-card">
                            <i class="fa-solid fa-server"></i>
                            <div><span>Servidor Web</span><strong>Óptimo</strong></div>
                        </div>
                        <div class="module-card">
                            <i class="fa-solid fa-database"></i>
                            <div><span>Base de Datos</span><strong>Óptimo</strong></div>
                        </div>
                        <div class="module-card">
                            <i class="fa-solid fa-cloud"></i>
                            <div><span>Almacenamiento</span><strong>Óptimo</strong></div>
                        </div>
                        <div class="module-card">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <div><span>Respaldos</span><strong>Óptimo</strong></div>
                        </div>
                    </div>
 
                    <div class="panel-header-flex mt-20">
                        <h3 class="sub-title">Métricas del Sistema</h3>
                        <select class="select-sm"><option>Este periodo</option></select>
                    </div>
 
                    <div class="metrics-grid">
                        <div class="metric-box">
                            <i class="fa-regular fa-clock" style="color:#3b82f6;"></i>
                            <div class="m-info">
                                <span>Tiempo de respuesta</span>
                                <strong>245ms</strong>
                                <small>Promedio</small>
                            </div>
                        </div>
                        <div class="metric-box">
                            <i class="fa-solid fa-user-astronaut" style="color:#a855f7;"></i>
                            <div class="m-info">
                                <span>Usuarios activos</span>
                                <strong>28</strong>
                                <small>Actualmente</small>
                            </div>
                        </div>
                        <div class="metric-box">
                            <i class="fa-regular fa-calendar-check" style="color:#f59e0b;"></i>
                            <div class="m-info">
                                <span>Citas procesadas</span>
                                <strong>156</strong>
                                <small>Este periodo</small>
                            </div>
                        </div>
                        <div class="metric-box alert-box">
                            <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i>
                            <div class="m-info">
                                <span>Errores del sistema</span>
                                <strong style="color:#ef4444;">2</strong>
                                <small>Este periodo</small>
                            </div>
                        </div>
                    </div>
                </div>
 
                <div class="panel panel-alerts">
                    <div class="panel-header-flex">
                        <h3 class="panel-title">Alertas del Sistema</h3>
                        <a href="#" class="link-sm">Ver todas</a>
                    </div>
 
                    <div class="alert-card success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <h4>No hay alertas críticas</h4>
                            <p>El sistema está funcionando correctamente.</p>
                        </div>
                    </div>
 
                    <div class="alert-card info">
                        <i class="fa-solid fa-circle-info"></i>
                        <div>
                            <h4>Mantenimiento programado</h4>
                            <p>Domingo 18/05/2026 de 02:00 AM a 04:00 AM</p>
                            <small>Se realizarán tareas de mantenimiento preventivo.</small>
                        </div>
                    </div>
                </div>
 
                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Actividad del Sistema</h2>
                        <a href="#" class="link-sm">Ver todas</a>
                    </div>
                    <table class="report-table table-activity">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Evento</th>
                                <th>Detalle</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="actividadBody"></tbody>
                    </table>
                </div>
 
            </div>
        </div>
 
        <!-- FOOTER -->
        <div class="footer-note">
            <i class="fa-solid fa-circle-info"></i>
            Los reportes se actualizan en tiempo real. Exporta los datos para análisis más detallados.
        </div>
 
    </main>
</div>
 
<script src="script.js"></script>
</body>
</html>
  