<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Odonto Estética</title>
    <meta name="description" content="Panel de reportes y estadísticas financieras de Odonto Estética">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/reportes.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>
 
<div class="menu-layout">
 
    <?php require_once __DIR__ . '/layouts/menu.php'; ?>
 
    <main class="menu-main-content">

        <?php require_once __DIR__ . '/layouts/header.php'; ?>
 
        <!-- HEADER -->
        <div class="rep-header">
            <div class="rep-header-title">
                <h1>Reportes y estadísticas</h1>
                <p>Análisis financiero del consultorio</p>
            </div>
            <div class="rep-header-actions">
                <button class="btn-outline" id="btn-periodo"><i class="fa-regular fa-calendar"></i> Este mes</button>
                <button class="btn-outline btn-export" id="btn-exportar"><i class="fa-solid fa-arrow-up-from-bracket"></i> Exportar</button>
            </div>
        </div>
 
        <!-- LOADING OVERLAY -->
        <div id="loading-overlay" class="loading-overlay" style="display:none;">
            <div class="spinner"></div>
            <p>Cargando datos...</p>
        </div>

        <!-- KPI GRID -->
        <section class="kpi-grid" id="kpi-grid">
            <div class="kpi-card kpi-card-1" id="kpi-ingresos">
                <div class="kpi-icon icon-green"><i class="fa-solid fa-dollar-sign"></i></div>
                <div class="kpi-info">
                    <h3>Ingresos Totales</h3>
                    <div class="number" id="kpi-val-ingresos">—</div>
                    <div class="trend green" id="kpi-trend-ingresos"></div>
                    <a href="#" class="kpi-link" data-kpi="ingresos">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card kpi-card-2" id="kpi-ganancia">
                <div class="kpi-icon icon-blue"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div class="kpi-info">
                    <h3>Ganancia Neta</h3>
                    <div class="number" id="kpi-val-ganancia">—</div>
                    <div class="trend green" id="kpi-trend-ganancia"></div>
                    <a href="#" class="kpi-link" data-kpi="ganancia">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card kpi-card-3" id="kpi-margen">
                <div class="kpi-icon icon-orange"><i class="fa-solid fa-percent"></i></div>
                <div class="kpi-info">
                    <h3>Margen de Ganancia</h3>
                    <div class="number" id="kpi-val-margen">—</div>
                    <div class="trend green" id="kpi-trend-margen"></div>
                    <a href="#" class="kpi-link" data-kpi="margen">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card kpi-card-4" id="kpi-facturas">
                <div class="kpi-icon icon-purple"><i class="fa-regular fa-file-lines"></i></div>
                <div class="kpi-info">
                    <h3>Total de Facturas</h3>
                    <div class="number" id="kpi-val-facturas">—</div>
                    <div class="trend text-muted" id="kpi-trend-facturas"></div>
                    <a href="#" class="kpi-link" data-kpi="facturas">Ver detalle</a>
                </div>
            </div>
            <div class="kpi-card kpi-card-5" id="kpi-pacientes">
                <div class="kpi-icon icon-teal"><i class="fa-solid fa-user-check"></i></div>
                <div class="kpi-info">
                    <h3>Pacientes Atendidos</h3>
                    <div class="number" id="kpi-val-pacientes">—</div>
                    <div class="trend text-muted" id="kpi-trend-pacientes"></div>
                    <a href="#" class="kpi-link" data-kpi="pacientes">Ver detalle</a>
                </div>
            </div>
        </section>
 
        <!-- PANELS GRID (full width, sin columna derecha) -->
        <div class="panels-grid">

            <!-- RESULTADOS FINANCIEROS -->
            <div class="panel panel-chart">
                <div class="panel-header-flex">
                    <h2 class="panel-title">Resultados Financieros</h2>
                    <select class="select-sm" id="select-chart-periodo">
                        <option value="este-mes">Este mes</option>
                        <option value="mes-anterior">Mes anterior</option>
                        <option value="trimestre">Último trimestre</option>
                    </select>
                </div>
 
                <div class="chart-summary" id="chart-summary">
                    <div class="chart-stat">
                        <span>Ingresos</span>
                        <strong style="color:var(--c-green);" id="summary-ingresos">—</strong>
                    </div>
                    <div class="chart-stat">
                        <span>Egresos</span>
                        <strong style="color:var(--c-red);" id="summary-egresos">—</strong>
                    </div>
                    <div class="chart-stat">
                        <span>Ganancia Neta</span>
                        <strong style="color:var(--c-blue);" id="summary-ganancia">—</strong>
                    </div>
                    <div class="chart-stat">
                        <span>Margen</span>
                        <strong id="summary-margen">—</strong>
                    </div>
                </div>
 
                <div class="chart-legend">
                    <span><span class="dot c-green"></span> Ingresos</span>
                    <span><span class="dot c-red"></span> Egresos</span>
                    <span><span class="dot c-blue"></span> Ganancia Neta</span>
                </div>
 
                <div class="chart-wrapper" id="line-chart-wrapper">
                    <svg class="line-chart-svg" viewBox="0 0 600 200" preserveAspectRatio="none"></svg>
                    <div class="chart-xaxis"></div>
                </div>
 
                <div class="chart-bottom-stats" id="chart-bottom-stats">
                    <div class="stat-box">
                        <span>Promedio diario de ingresos</span>
                        <strong id="stat-avg-ing">—</strong>
                    </div>
                    <div class="stat-box">
                        <span>Promedio diario de egresos</span>
                        <strong id="stat-avg-egr">—</strong>
                    </div>
                    <div class="stat-box">
                        <span>Día con mayor ingreso</span>
                        <strong id="stat-max-ing">—</strong>
                    </div>
                    <div class="stat-box">
                        <span>Día con mayor egreso</span>
                        <strong id="stat-max-egr">—</strong>
                    </div>
                </div>
            </div>

            <!-- INGRESOS POR TRATAMIENTO -->
            <div class="panel panel-bars">
                <div class="panel-header-flex">
                    <h2 class="panel-title">Ingresos por Tratamiento</h2>
                    <select class="select-sm" id="select-bar-periodo">
                        <option value="este-mes">Este mes</option>
                        <option value="mes-anterior">Mes anterior</option>
                        <option value="trimestre">Último trimestre</option>
                    </select>
                </div>
                <div id="bar-chart-container" class="bar-chart-container"></div>
                <div class="bar-empty-state" id="bar-empty" style="display:none;">
                    <i class="fa-solid fa-chart-simple"></i>
                    <p>No hay datos de tratamientos para este periodo</p>
                </div>
            </div>
 
            <!-- ESTADO DE RESULTADOS -->
            <div class="panel panel-table-full">
                <div class="panel-header-flex">
                    <h2 class="panel-title">Estado de Resultados</h2>
                    <a href="#" class="link-sm" id="link-ver-reporte">Ver reporte completo</a>
                </div>
                <table class="report-table" id="tabla-resultados">
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
                <div class="table-empty-state" id="table-empty" style="display:none;">
                    <i class="fa-solid fa-table"></i>
                    <p>No hay datos para este periodo</p>
                </div>
            </div>
        </div>
 
        <!-- FOOTER -->
        <div class="footer-note">
            <i class="fa-solid fa-circle-info"></i>
            Los reportes se obtienen en tiempo real desde la base de datos. Exporta los datos para análisis más detallados.
        </div>
        <!-- Modal de Exportación de Reportes -->
        <div id="modalExportReportes" class="modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
            <div class="modal-content modal-export" style="background:#fff; border-radius:20px; padding:32px; min-width:360px; max-width:460px; box-shadow:0 24px 60px rgba(0,0,0,.2); position:relative;">
                <span class="close-modal" id="closeModalExportReportes" style="position:absolute; top:14px; right:18px; background:none; border:none; font-size:22px; cursor:pointer; color:#64748b; transition:color 0.2s;">&times;</span>
                <div class="modal-export-header" style="text-align:center; margin-bottom:24px;">
                    <div class="modal-export-icon" style="background:#f1f5f9; color:#3b82f6; width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; margin:0 auto 16px;">
                        <i class="fa-solid fa-file-export"></i>
                    </div>
                    <h2 style="font-size:20px; font-weight:700; color:#0f172a; margin-bottom:8px;">Exportar Reporte</h2>
                    <p class="modal-export-subtitle" id="exportReporteInfo" style="color:#64748b; font-size:14px;">Selecciona el formato de descarga</p>
                </div>
                <div class="export-options" style="display:flex; flex-direction:column; gap:12px;">
                    <button class="export-option-btn" id="btn-export-pdf" style="display:flex; align-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; cursor:pointer; text-align:left; transition:all 0.2s; width:100%;">
                        <div class="export-option-icon pdf-icon" style="background:#fef2f2; color:#ef4444; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-right:16px;">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div class="export-option-info" style="flex:1;">
                            <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Documento PDF</strong>
                            <span style="font-size:13px; color:#64748b;">Descargar como archivo .pdf</span>
                        </div>
                        <i class="fa-solid fa-chevron-right export-arrow" style="color:#cbd5e1; font-size:14px;"></i>
                    </button>
                    <button class="export-option-btn" id="btn-export-excel" style="display:flex; align-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; cursor:pointer; text-align:left; transition:all 0.2s; width:100%;">
                        <div class="export-option-icon excel-icon" style="background:#f0fdf4; color:#10b981; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-right:16px;">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                        <div class="export-option-info" style="flex:1;">
                            <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Hoja de Excel</strong>
                            <span style="font-size:13px; color:#64748b;">Descargar como archivo .csv</span>
                        </div>
                        <i class="fa-solid fa-chevron-right export-arrow" style="color:#cbd5e1; font-size:14px;"></i>
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="/LOGIN_ORIGINAL/public/js/administrador/reportes.js"></script>
</body>
</html>