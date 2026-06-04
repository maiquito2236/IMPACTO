<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Facturación e Ingresos</title>
    <link rel="stylesheet" href="../menu/menu.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
        <div class="menu-layout">
        <?php require_once "../menu/menu.php"; ?>
            <section class="facturacion-content">
            <section class="kpi-metrics-row">
                <div class="metric-card-kpi blue-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-cash-register"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Caja del Día</span>
                        <span class="kpi-number">12</span>
                        <a href="../reportes/reportes.php"><span class="kpi-anchor"> Ver ingresos hoy</span></a>
                    </div>
                </div>
                <div class="metric-card-kpi green-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-file-invoice"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Cuentas por Cobrar</span>
                        <span class="kpi-number">72</span>
                        <span class="kpi-anchor">Vuestros pendientes</span>
                    </div>
                </div>
                <div class="metric-card-kpi red-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-wallet"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Gastos del Mes</span>
                        <span class="kpi-number">18</span>
                        <span class="kpi-anchor">Ver gastos del mes</span>
                    </div>
                </div>
            </section>

            <div class="billing-grid-layout">
                
                <div class="panel-card invoices-table-section">
                    <div class="table-header-toolbar">
                        <h2>Registro de Facturas Recientes</h2>
                        <div class="toolbar-buttons">
                            <button class="btn-toolbar-pdf"><i class="fa-solid fa-file-pdf"></i> Exportar PDF</button>
                            <span class="btn-toolbar-label">RESUMEN FACTURAS</span>
                        </div>
                    </div>

                    <div class="table-scroll-container">
                        <table class="data-invoice-table">
                            <thead>
                                <tr>
                                    <th>N° Factura</th>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Tratamiento</th>
                                    <th>Monto ($)</th>
                                    <th>Método</th>
                                    <th>Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="bold-cell">712001</td>
                                    <td>16/9/2026</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=60&q=80" alt="">
                                            <div class="meta-txt"><span>Alejandra Torres</span></div>
                                        </div>
                                    </td>
                                    <td>Endodoncia - Fase 1</td>
                                    <td class="bold-cell">$750.00</td>
                                    <td>Tarjeta</td>
                                    <td>
                                        <div class="doctor-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1594824813573-246434de83fb?auto=format&fit=crop&w=60&q=80" alt="">
                                            <span>Dra. Laura Morales</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold-cell">712002</td>
                                    <td>14/9/2026</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=60&q=80" alt="">
                                            <div class="meta-txt"><span>Luis Eduardo Pérez</span></div>
                                        </div>
                                    </td>
                                    <td>Limpieza</td>
                                    <td class="bold-cell">$740.00</td>
                                    <td>Tarjeta</td>
                                    <td>
                                        <div class="doctor-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=60&q=80" alt="">
                                            <span>Dr. Andrés Díaz</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold-cell">712003</td>
                                    <td>14/9/2026</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=60&q=80" alt="">
                                            <div class="meta-txt"><span>Valentina Gómez</span></div>
                                        </div>
                                    </td>
                                    <td>Ortodoncia</td>
                                    <td class="bold-cell">$220.00</td>
                                    <td>Efectivo</td>
                                    <td>
                                        <div class="doctor-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=60&q=80" alt="">
                                            <span>Dr. Carlos Ruiz</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold-cell">712015</td>
                                    <td>14/9/2026</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=60&q=80" alt="">
                                            <div class="meta-txt"><span>Ana Sofía Martínez</span></div>
                                        </div>
                                    </td>
                                    <td>Blanqueamiento</td>
                                    <td class="bold-cell">$150.00</td>
                                    <td>Efectivo</td>
                                    <td>
                                        <div class="doctor-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=60&q=80" alt="">
                                            <span>Dr. Javier Gómez</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold-cell">712106</td>
                                    <td>14/9/2026</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=60&q=80" alt="">
                                            <div class="meta-txt"><span>Luis Eduardo Pérez</span></div>
                                        </div>
                                    </td>
                                    <td>Limpieza</td>
                                    <td class="bold-cell">$740.00</td>
                                    <td>Tarjeta</td>
                                    <td>
                                        <div class="doctor-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=60&q=80" alt="">
                                            <span>Dra. Ana López</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold-cell">712107</td>
                                    <td>14/9/2026</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=60&q=80" alt="">
                                            <div class="meta-txt"><span>Pedro Herrera</span></div>
                                        </div>
                                    </td>
                                    <td>Ortodoncia</td>
                                    <td class="bold-cell">$250.00</td>
                                    <td>Tarjeta</td>
                                    <td>
                                        <div class="doctor-meta-cell">
                                            <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=60&q=80" alt="">
                                            <span>Dr. Carlos Gómez</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="footer-summary-row">
                                    <td class="bold-cell">Total</td>
                                    <td>16/9/2026</td>
                                    <td colspan="2"></td>
                                    <td class="total-final-sum">$8,720.00</td>
                                    <td>Efectivo</td>
                                    <td>
                                        <div class="table-inline-pagination">
                                            <button class="pag-arrow"><i class="fa-solid fa-chevron-left"></i></button>
                                            <span class="pag-indicator">1</span>
                                            <button class="pag-arrow"><i class="fa-solid fa-chevron-right"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="charts-and-alerts-column">
                    
                    <div class="panel-card chart-analytics-card">
                        <h3>Resumen de Ingresos y Gastos (Año)</h3>
                        <div class="chart-legends">
                            <div class="leg-item"><span class="leg-dot color-blue-dot"></span> Ingresos mensual</div>
                        </div>
                        
                        <div class="pure-css-bar-chart">
                            <div class="y-axis-labels"><span>$10K</span><span>$8K</span><span>$6K</span><span>$4K</span><span>$2K</span><span>$0</span></div>
                            <div class="bars-container-wrapper">
                                <div class="bar-group-month">
                                    <div class="double-bars"><div class="bar-fill-unit b-blue" style="height: 65%;"></div></div>
                                    <span class="m-lbl">May</span>
                                </div>
                                <div class="bar-group-month">
                                    <div class="double-bars"><div class="bar-fill-unit b-blue" style="height: 50%;"></div></div>
                                    <span class="m-lbl">Apr</span>
                                </div>
                                <div class="bar-group-month">
                                    <div class="double-bars"><div class="bar-fill-unit b-blue" style="height: 70%;"></div></div>
                                    <span class="m-lbl">May</span>
                                </div>
                                <div class="bar-group-month">
                                    <div class="double-bars"><div class="bar-fill-unit b-blue" style="height: 55%;"></div></div>
                                    <span class="m-lbl">July</span>
                                </div>
                                <div class="bar-group-month">
                                    <div class="double-bars">
                                        <div class="bar-fill-unit b-blue" style="height:72%;"></div>
                                    </div>
                                    <span class="m-lbl">Aug</span>
                                </div>
                                <div class="bar-group-month">
                                    <div class="double-bars"><div class="bar-fill-unit b-blue" style="height: 68%;"></div></div>
                                    <span class="m-lbl">Sep</span>
                                </div>
                                <div class="bar-group-month">
                                    <div class="double-bars"><div class="bar-fill-unit b-blue" style="height: 80%;"></div></div>
                                    <span class="m-lbl">May</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card chart-analytics-card">
                        <h3>Flujo de Efectivo</h3>
                        <div class="sparkline-simulation-box">
                            <div class="y-axis-labels"><span>$10K</span><span>$6K</span><span>$4K</span><span>$2K</span><span>$0</span></div>
                            <div class="line-graph-canvas">
                                <svg viewBox="0 0 300 80" class="svg-curve">
                                    <path d="M0,70 Q30,55 60,65 T120,40 T180,50 T240,30 T300,20" fill="none" stroke="#1a56db" stroke-width="2.5"/>
                                    <path d="M0,70 Q30,55 60,65 T120,40 T180,50 T240,30 T300,20 L300,80 L0,80 Z" fill="url(#blue-gradient)" opacity="0.08"/>
                                    <defs>
                                        <linearGradient id="blue-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#1a56db"/>
                                            <stop offset="100%" stop-color="#ffffff"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="x-axis-dates"><span>1 May</span><span>6 May</span><span>11 May</span><span>21 May</span><span>26 May</span><span>31 May</span></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <section class="bottom-alerts-layout-row">
                <div class="panel-card flex-alert-card">
                    <div class="alert-icon-accent red-accent"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="alert-content-data">
                        <h4>Alertas y Recordatorios</h4>
                        <p class="alert-text-paragraph">Recordatorio: 3 facturas de ortodoncia vencen mañana<br><span class="alert-timestamp">20 mayo, 2026 - 03:00 PM</span></p>
                        <p class="alert-text-paragraph">Alerta: 1 gasto de laboratorio pendiente de aprobación<br><span class="alert-timestamp">20 mayo, 2026 - 03:00 PM</span></p>
                    </div>
                </div>

                <div class="panel-card flex-alert-card">
                    <div class="alert-icon-accent gray-accent"><i class="fa-solid fa-circle-info"></i></div>
                    <div class="alert-content-data">
                        <h4>Alertas y Recordatorios</h4>
                        <p class="alert-text-paragraph">Recordatorio: 3 facturas de ortodoncia vencen mañana</p>
                        <p class="alert-text-paragraph mt-space">Alerta: 1 gasto de laboratorio pendiente de aprobación</p>
                    </div>
                </div>
            </section>

            <footer class="exclusive-view-notice">
                <span>Vista de Consulta Exclusiva - No se permiten modificaciones</span>
            </footer>
            </section>
        </main>
    </div>
</div>
</body>
</html>