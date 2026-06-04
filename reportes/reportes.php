<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Dashboard Principal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../menu/menu.css">
</head>
<body>
        <div class="menu-layout">
        <?php require_once "../menu/menu.php"; ?>

        <section class="dashboard-content">
            <section class="kpi-metrics-row">
                <div class="metric-card-kpi blue-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Citas de Hoy</span>
                        <span class="kpi-number">12</span>
                        <span class="kpi-anchor">Próxima: 09:00 AM</span>
                    </div>
                </div>
                <div class="metric-card-kpi green-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Pacientes Activos</span>
                        <span class="kpi-number">158</span>
                        <span class="kpi-anchor text-green">+ 8 nuevos este mes</span>
                    </div>
                </div>
                <div class="metric-card-kpi purple-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-kit-medical"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Tratamientos Activos</span>
                        <span class="kpi-number">45</span>
                        <span class="kpi-anchor text-purple">+ 5 nuevos este mes</span>
                    </div>
                </div>
            </section>

            <div class="dashboard-grid-layout">
                
                <div class="left-operational-column">
                    
                    <div class="panel-card margin-bottom-space">
                        <div class="table-header-toolbar">
                            <h2>Agenda de Hoy</h2>
                            <a href="/agenda/agenda.html" class="kpi-anchor text-size-sm">Ver agenda completa</a>
                        </div>
                        
                        <div class="agenda-items-list">
                            <div class="agenda-item border-orange">
                                <div class="agenda-time-meta">
                                    <strong>09:00 AM</strong>
                                    <span>60 min</span>
                                </div>
                                <div class="pacient-avatar-info">
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt="Juan">
                                    <div class="meta-txt">
                                        <strong>Juan Guarnizo</strong>
                                        <span>Ortodoncia - Ajuste de brackets</span>
                                    </div>
                                </div>
                                <span class="pill-status bg-orange">Pendiente</span>
                                <button class="btn-more-options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>

                            <div class="agenda-item border-green">
                                <div class="agenda-time-meta">
                                    <strong>10:30 AM</strong>
                                    <span>45 min</span>
                                </div>
                                <div class="pacient-avatar-info">
                                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="María">
                                    <div class="meta-txt">
                                        <strong>María Fernanda López</strong>
                                        <span>Limpieza dental</span>
                                    </div>
                                </div>
                                <span class="pill-status bg-green">Confirmada</span>
                                <button class="btn-more-options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>

                            <div class="agenda-item border-green">
                                <div class="agenda-time-meta">
                                    <strong>11:30 AM</strong>
                                    <span>60 min</span>
                                </div>
                                <div class="pacient-avatar-info">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Carlos">
                                    <div class="meta-txt">
                                        <strong>Carlos Ramírez</strong>
                                        <span>Endodoncia - Conducto</span>
                                    </div>
                                </div>
                                <span class="pill-status bg-green">Confirmada</span>
                                <button class="btn-more-options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>
                        </div>

                        <div class="panel-inner-footer">
                            <a href="#" id="verTodasCitas" class="kpi-anchor text-size-md">Ver todas las citas</a>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="table-header-toolbar">
                            <h2>Pacientes Recientes</h2>
                            <a href="#" id="verTodosPacientes" class="kpi-anchor text-size-sm">Ver todos</a>
                        </div>

                        <div class="table-scroll-container">
                            <table class="data-dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Tratamiento</th>
                                        <th>Fecha</th>
                                        <th style="text-align: right;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="pacient-table-cell">
                                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=100&q=80" alt="Alejandra">
                                                <div class="meta-txt">
                                                    <strong class="dark-bold-text">Alejandra Torres</strong>
                                                    <span class="id-sublabel">ID: 12345678</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong class="dark-medium-text">Nuevo paciente</strong></td>
                                        <td>15 Mayo, 2026</td>
                                        <td style="text-align: right;"><a href="#" class="btn-action-table ver-paciente" data-id="1">Ver</a></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="pacient-table-cell">
                                                <img src="https://images.unsplash.com/photo-1600486913747-55e5470d6f40?auto=format&fit=crop&w=100&q=80" alt="Luis">
                                                <div class="meta-txt">
                                                    <strong class="dark-bold-text">Luis Eduardo Pérez</strong>
                                                    <span class="id-sublabel">ID: 12345678</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong class="dark-medium-text">Evaluación</strong></td>
                                        <td>15 Mayo, 2026</td>
                                        <td style="text-align: right;"><a href="#" class="btn-action-table ver-paciente" data-id="2">Ver</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="right-analytics-column">
                    
                    <div class="panel-card">
                        <div class="table-header-toolbar">
                            <h2>Resumen de Tratamientos</h2>
                        </div>
                        <div class="donut-chart-wrapper">
                            <div class="donut-chart-graphic">
                                <div class="donut-hole">
                                    <strong>45</strong>
                                    <span>Activos</span>
                                </div>
                            </div>
                            <ul class="chart-legend-list">
                                <li><span class="dot orto"></span> Ortodoncia <span class="pct">40% <small>(18)</small></span></li>
                                <li><span class="dot endo"></span> Endodoncia <span class="pct">22% <small>(10)</small></span></li>
                                <li><span class="dot limp"></span> Limpieza <span class="pct">20% <small>(9)</small></span></li>
                                <li><span class="dot imp"></span> Implantes <span class="pct">11% <small>(5)</small></span></li>
                                <li><span class="dot otr"></span> Otros <span class="pct">7% <small>(3)</small></span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="table-header-toolbar">
                            <h2>Ingresos</h2>
                            <select id="filtroIngresos">
    <option value="mes">Este mes</option>
    <option value="semana">Esta semana</option>
</select>
                        </div>
                        
                        <div class="sparkline-simulation-box">
                            <div class="line-graph-canvas">
                                <svg class="svg-curve" viewBox="0 0 300 80" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="blue-gradient-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="rgba(26,86,219,0.18)" />
                                            <stop offset="100%" stop-color="rgba(26,86,219,0)" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M 0 60 Q 40 50, 70 45 T 140 30 T 210 20 T 280 10 L 300 8 L 300 80 L 0 80 Z" fill="url(#blue-gradient-fill)" />
                                    <path d="M 0 60 Q 40 50, 70 45 T 140 30 T 210 20 T 280 10 L 300 8" fill="none" stroke="#1a56db" stroke-width="2.5" />
                                    <circle cx="210" cy="20" r="4.5" fill="#1a56db" stroke="#fff" stroke-width="1.5" />
                                </svg>
                                <div class="floating-tooltip-value">$8,750</div>
                                <div class="x-axis-dates">
                                    <span>1 May</span><span>11 May</span><span>16 May</span><span>31 May</span>
                                </div>
                            </div>
                        </div>

                        <div class="financial-briefcase-row">
                            <div class="f-brief-item">
                                <small>Ingresos del mes</small>
                                <strong>$8,750.00</strong>
                            </div>
                            <div class="f-brief-item">
                                <small>Mes anterior</small>
                                <strong class="color-muted-txt">$7,420.00</strong>
                            </div>
                            <div class="f-brief-item">
                                <small>Crecimiento</small>
                                <strong class="color-green-txt">+18%</strong>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="table-header-toolbar">
                            <h2>Alertas y Recordatorios</h2>
                        </div>
                        <div class="dashboard-mini-alerts">
                            <div class="alert-banner-item bg-red-light">
                                <i class="fa-solid fa-circle-exclamation text-red"></i>
                                <p>Tienes 3 citas sin confirmar para mañana</p>
                                <a href="#">Revisar</a>
                            </div>
                            <div class="alert-banner-item bg-blue-light">
                                <i class="fa-solid fa-circle-info text-blue"></i>
                                <p>Capacitación en nuevas técnicas de ortodoncia (03:00 PM)</p>
                                <a href="#">Ver</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        </main>
</div>
    <div id="modal-nueva-cita" class="modal-backdrop">
        <div class="modal-window">
            <div class="modal-top-bar">
                <h3>Agendar Nueva Cita</h3>
                <a href="#" class="close-modal-x">&times;</a>
            </div>
            <form class="modal-body-form" onsubmit="return false;">
                <div class="form-group-field">
                    <label>Buscar Paciente</label>
                    <div class="relative-input-wrapper">
                        <input type="text" class="input-field" placeholder="Escribe el nombre o ID del paciente...">
                        <i class="fa-solid fa-magnifying-glass inline-search-icon"></i>
                    </div>
                </div>
                <div class="form-grid-2-col">
                    <div class="form-group-field">
                        <label>Especialidad / Tratamiento</label>
                        <select class="input-field">
                            <option>Ortodoncia - Control</option>
                            <option>Endodoncia - Fase Inicial</option>
                            <option>Limpieza Dental Profiláctica</option>
                            <option>Implantología Diagnóstico</option>
                        </select>
                    </div>
                    <div class="form-group-field">
                        <label>Hora Programada</label>
                        <input type="time" class="input-field" value="09:00">
                    </div>
                </div>
                <div class="form-group-field">
                    <label>Notas Clínicas / Observaciones Previa</label>
                    <textarea class="input-field textarea-custom" rows="3" placeholder="Añadir indicaciones especiales para la recepción o el doctor..."></textarea>
                </div>
            </form>
            <div class="modal-footer-actions">
                <a href="#" class="btn-secondary-action">Cancelar</a>
                <a href="#" class="btn-primary-action">Agendar Cita</a>
            </div>
        </div>
    </div>
<div id="modalPaciente" class="modal-paciente">
    <div class="contenido-modal-paciente">
        <span class="cerrar-modal-paciente">&times;</span>

        <h2>Información del Paciente</h2>

        <div class="info-paciente">
            <p><strong>Nombre:</strong> <span id="infoNombre"></span></p>
            <p><strong>Documento:</strong> <span id="infoDocumento"></span></p>
            <p><strong>Tratamiento:</strong> <span id="infoTratamiento"></span></p>
            <p><strong>Fecha:</strong> <span id="infoFecha"></span></p>
            <p><strong>Teléfono:</strong> <span id="infoTelefono"></span></p>
            <p><strong>Email:</strong> <span id="infoEmail"></span></p>
        </div>
    </div>
</div>
<div id="toastInfo" class="toast-info"></div>
<script src="js.js"></script>
</body>
</html>