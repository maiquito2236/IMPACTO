<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Facturación - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../menu/global.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="menu-layout">

    <?php require_once "../menu/menu.php"; ?>

    <main class="menu-main-content">
        
        <header class="header">
            <div class="header-title">
                <h1>Facturación y Caja</h1>
                <p>Gestión de ingresos y cobros</p>
            </div>
            <div class="header-actions">
                <button class="btn-primary" onclick="document.getElementById('modalFactura').style.display='flex'">
                    <i class="fa-solid fa-plus"></i> Nueva Factura
                </button>
            </div>
        </header>

        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon icon-green"><i class="fa-solid fa-cash-register"></i></div>
                <div class="kpi-info">
                    <h3>Ingresos del Día</h3>
                    <div class="number">$ 2,850.00</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-trend-up"></i> +18% vs ayer</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-blue"><i class="fa-solid fa-wallet"></i></div>
                <div class="kpi-info">
                    <h3>Ingresos del Mes</h3>
                    <div class="number">$ 38,650.00</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-trend-up"></i> +12% este mes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-purple"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div class="kpi-info">
                    <h3>Abonos del Mes</h3>
                    <div class="number">$ 15,320.00</div>
                    <div class="trend purple">Cobros parciales registrados</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="kpi-info">
                    <h3>Deudas Pendientes</h3>
                    <div class="number">$ 22,180.00</div>
                    <div class="trend orange">Por gestionar cobranza</div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            
            <div class="left-col">
                <div class="panel">
                    <div class="panel-navbar">
                        <div class="tabs">
                            <span class="tab-btn active" data-tab="facturacion">Facturación</span>
                            <span class="tab-btn" data-tab="abonos">Abonos</span>
                            <span class="tab-btn" data-tab="deudas">Deudas Pendientes</span>
                            <span class="tab-btn" data-tab="emitidas">Facturas Emitidas</span>
                        </div>
                    </div>

                    <div class="table-filters">
                        <div class="filter-group">
                            <button class="btn-filter-dropdown"><i class="fa-regular fa-calendar"></i> Filtrar Fecha <i class="fa-solid fa-chevron-down"></i></button>
                            <button class="btn-filter-dropdown">Todos los Doctores <i class="fa-solid fa-chevron-down"></i></button>
                        </div>
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="mainSearchInput" placeholder="Buscar por paciente o documento...">
                        </div>
                    </div>

                    <div style="overflow-x: auto; min-height: 280px;">
                        <table>
                            <thead id="mainTableHead">
                                </thead>
                            <tbody id="mainTableBody">
                                </tbody>
                        </table>
                    </div>

                    <div class="pagination-container">
                        <span class="pagination-info">Mostrando <span id="rowsCounter">0</span> registros encontrados</span>
                        <div class="pagination-buttons">
                            <button class="btn-page"><i class="fa-solid fa-chevron-left"></i></button>
                            <button class="btn-page active">1</button>
                            <button class="btn-page">2</button>
                            <button class="btn-page"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <div class="charts-subgrid">
                    <div class="panel">
                        <div class="panel-header-flex">
                            <h2 class="panel-title">Evolución de Ingresos</h2>
                        </div>
                        <div class="chart-wrapper">
                            <svg class="line-chart-svg" viewBox="0 0 500 120">
                                <defs>
                                    <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#2563eb" stop-opacity="0.15"/>
                                        <stop offset="100%" stop-color="#2563eb" stop-opacity="0.0"/>
                                    </linearGradient>
                                </defs>
                                <path d="M 0 100 Q 100 80, 200 50 T 400 30 T 500 20" fill="none" stroke="#2563eb" stroke-width="3"></path>
                                <path d="M 0 100 Q 100 80, 200 50 T 400 30 T 500 20 L 500 120 L 0 120 Z" fill="url(#chartGradient)"></path>
                            </svg>
                            <div class="chart-xaxis">
                                <span>Semana 1</span><span>Semana 2</span><span>Semana 3</span><span>Semana 4</span>
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header-flex">
                            <h2 class="panel-title">Métodos de Pago Utilizados</h2>
                        </div>
                        <div class="donut-chart-container">
                            <div class="donut-display">
                                <div class="donut-display-inner">
                                    <h3>47%</h3>
                                    <span>Efectivo</span>
                                </div>
                            </div>
                            <div class="donut-legend-list">
                                <div class="legend-row">
                                    <span class="legend-lbl"><span class="dot-color c-limpieza"></span> Efectivo</span>
                                    <span class="legend-val">$ 18,200</span>
                                </div>
                                <div class="legend-row">
                                    <span class="legend-lbl"><span class="dot-color c-ortodoncia"></span> Tarjeta</span>
                                    <span class="legend-val">$ 12,850</span>
                                </div>
                                <div class="legend-row">
                                    <span class="legend-lbl"><span class="dot-color c-resinas"></span> Transferencia</span>
                                    <span class="legend-val">$ 7,600</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="right-col">
                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Cierre de Caja (Hoy)</h2>
                    </div>
                    <div class="cash-control-list">
                        <div class="cash-row">
                            <span><i class="fa-solid fa-money-bill-wave" style="color:#22c55e;"></i> Efectivo</span>
                            <strong>$ 1,850.00</strong>
                        </div>
                        <div class="cash-row">
                            <span><i class="fa-solid fa-credit-card" style="color:#3b82f6;"></i> Tarjeta</span>
                            <strong>$ 800.00</strong>
                        </div>
                        <div class="cash-row">
                            <span><i class="fa-solid fa-money-bill-transfer" style="color:#a855f7;"></i> Transferencia</span>
                            <strong>$ 200.00</strong>
                        </div>
                        <div class="cash-total-row">
                            <span>Total en Caja</span>
                            <strong>$ 2,850.00</strong>
                        </div>
                    </div>
                    <button class="btn-outline-action w-100"><i class="fa-solid fa-print"></i> Imprimir Reporte Diario</button>
                </div>

                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Exportar Datos</h2>
                    </div>
                    <div class="form-group">
                        <label>Formato de descarga</label>
                        <select><option>Documento PDF (.pdf)</option><option>Hoja de Excel (.xlsx)</option></select>
                    </div>
                    <button id="btnDescargarDatos" class="btn-primary w-100" style="justify-content: center; margin-top: 10px;"><i class="fa-solid fa-file-export"></i> Descargar Datos</button>
                </div>
            </div>

        </div> 
        </main> 
        
    </div>
        <div id="modalFactura" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModal">&times;</span>
            <h2>Generar Nueva Factura</h2>
            <form id="formFactura">
                <div class="form-group">
                    <label>Paciente</label>
                    <input type="text" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label>Concepto Médico</label>
                    <input type="text" placeholder="Ej. Resina de Premolar" required>
                </div>
                <div class="form-group">
                    <label>Monto Total ($)</label>
                    <input type="number" placeholder="0.00" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">Crear Factura</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>