<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Dashboard</title>
    <link rel="stylesheet" href="../menu/menu.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="menu-layout">
    <?php require_once "../menu/menu.php"; ?>

    <section class="main-content">
            <section class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon blue"><i class="fa-regular fa-calendar"></i></div>
                    <div class="metric-info">
                        <p class="metric-label">Citas de Hoy</p>
                        <h3 class="metric-value">12</h3>
                        <p class="metric-sub">Próxima: 09:00 AM &bull; <a href="/agenda/agenda.html">Ver agenda</a></p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon green"><i class="fa-regular fa-user"></i></div>
                    <div class="metric-info">
                        <p class="metric-label">Pacientes Activos</p>
                        <h3 class="metric-value">158</h3>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon purple"><i class="fa-solid fa-bezier-curve"></i></div>
                    <div class="metric-info">
                        <p class="metric-label">Tratamientos Activos</p>
                        <h3 class="metric-value">45</h3>
                    </div>
                </div>
            </section>

            <div class="two-columns-layout">
                <div class="left-column">
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h3>Agenda de Hoy</h3>
                            <a href="../agenda/agenda.php" class="link-view-all">Ver agenda completa</a>
                        </div>
                        
                        <div class="agenda-list">
                            <div class="agenda-item">
                                <div class="agenda-time">
                                    <span class="time">09:00 AM</span>
                                    <span class="duration">60 min</span>
                                </div>
                                <div class="patient-brief">
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=80&q=80" alt="Juan Guarnizo">
                                    <div>
                                        <h4>Juan Guarnizo</h4>
                                        <p>Ortodoncia - Ajuste de brackets</p>
                                    </div>
                                </div>
                                <span class="status-badge pending">Pendiente</span>
                                <button class="btn-action"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>

                            <div class="agenda-item">
                                <div class="agenda-time">
                                    <span class="time">10:30 AM</span>
                                    <span class="duration">45 min</span>
                                </div>
                                <div class="patient-brief">
                                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=80&q=80" alt="María Fernanda López">
                                    <div>
                                        <h4>María Fernanda López</h4>
                                        <p>Limpieza dental</p>
                                    </div>
                                </div>
                                <span class="status-badge confirmed">Confirmada</span>
                                <button class="btn-action"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>

                            <div class="agenda-item">
                                <div class="agenda-time">
                                    <span class="time">11:30 AM</span>
                                    <span class="duration">60 min</span>
                                </div>
                                <div class="patient-brief">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=80&q=80" alt="Carlos Ramírez">
                                    <div>
                                        <h4>Carlos Ramírez</h4>
                                        <p>Endodoncia - Conducto</p>
                                    </div>
                                </div>
                                <span class="status-badge confirmed">Confirmada</span>
                                <button class="btn-action"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </div>
                        </div>
                        <div class="block-footer">
                            <a href="#" class="view-more-center" id="openCitas">Ver todas las citas</a>
                        </div>
                    </section>
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h3>Pacientes Recientes</h3>
                            <a href="#" class="link-view-all" id="openPacientes">Ver todos</a>
                        </div>
                        <table class="dashboard-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="table-patient">
                                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=80&q=80" alt="">
                                            <span>Alejandra Torres</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">Nuevo paciente</td>
                                    <td class="text-muted">15 Mayo, 2026</td>
                                    <td><button class="btn-table-action btn-ver-paciente" id="btnAlejandra">Ver</button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-patient">
                                            <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=80&q=80" alt="">
                                            <span>Luis Eduardo Pérez</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">Evaluación</td>
                                    <td class="text-muted">15 Mayo, 2026</td>
                                    <td><button class="btn-table-action btn-ver-paciente" id="btnLuis">Ver</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </div>

                <div class="right-column">
                    <section class="dashboard-block">
                        <h3>Resumen de Tratamientos</h3>
                        <div class="chart-container-placeholder">
                        </div>

                        <div class="progress-item">
        <div class="progress-info">
            <span>Ortodoncia</span>
            <span>40%</span>
        </div>

        <div class="progress-bar">
            <div class="progress orto-progress"></div>
        </div>
    </div>

    <div class="progress-item">
        <div class="progress-info">
            <span>Endodoncia</span>
            <span>22%</span>
        </div>

        <div class="progress-bar">
            <div class="progress endo-progress"></div>
        </div>
    </div>

    <div class="progress-item">
        <div class="progress-info">
            <span>Limpieza</span>
            <span>20%</span>
        </div>
<div class="progress-bar">
            <div class="progress limp-progress"></div>
        </div>
    </div>

</div>

    </section>
    
        

                    <section class="dashboard-block">
                        <div class="block-header">
                            <h3>Ingresos</h3>
                            <select class="select-dropdown" id="incomeSelector">
                                <option value="mes">Este mes</option>
                                <option value="semana">Esta semana</option>
                            </select>
                        </div>
                        <div class="income-value-display">
                            <span class="income-title">Ingresos del mes</span>
                            <span class="income-main-amount">$8,750.00</span>
                        </div>
                        <div class="income-stats">
                            <div><p class="lbl">Mes anterior</p><p class="val">$7,420.00</p></div>
                            <div><p class="lbl">Crecimiento</p><p class="val green-text">+18%</p></div>
                        </div>
                    </section>

                    <section class="dashboard-block">
                        <h3>Alertas y Recordatorios</h3>
                        <div class="alert-item amber">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <div class="alert-content">
                                <p>Tienes 3 citas sin confirmar para mañana</p>
                                <a href="../agenda/agenda.php">Revisar agenda</a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
    <!-- MODAL CITAS -->
<div class="modal" id="modalCitas">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Todas las citas del día</h2>
            <span class="close-modal" id="closeCitas">&times;</span>
        </div>

        <div class="modal-body">

            <div class="modal-agenda-item">
                <strong>09:00 AM</strong>
                <p>Juan Guarnizo - Ortodoncia</p>
            </div>

            <div class="modal-agenda-item">
                <strong>10:30 AM</strong>
                <p>María Fernanda López - Limpieza dental</p>
            </div>

            <div class="modal-agenda-item">
                <strong>11:30 AM</strong>
                <p>Carlos Ramírez - Endodoncia</p>
            </div>

            <div class="modal-agenda-item">
                <strong>02:00 PM</strong>
                <p>Alejandra Torres - Valoración</p>
            </div>

        </div>
    </div>
</div>

<!-- MODAL PACIENTES -->
<div class="modal" id="modalPacientes">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Pacientes recientes</h2>
            <span class="close-modal" id="closePacientes">&times;</span>
        </div>

        <div class="modal-body">

            <div class="patient-card">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=80&q=80">
                <div>
                    <h4>Alejandra Torres</h4>
                    <p>Nuevo paciente</p>
                </div>
            </div>

            <div class="patient-card">
                <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=80&q=80">
                <div>
                    <h4>Luis Eduardo Pérez</h4>
                    <p>Evaluación</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL INFORMACION -->
<div class="modal" id="modalInfoPaciente">
    <div class="modal-content info-modal">
        <div class="modal-header">
            <h2>Información del paciente</h2>
            <span class="close-modal" id="closeInfo">&times;</span>
        </div>

        <div class="modal-body patient-info">

            <img id="patientImg" src="">

<h3 id="patientName"></h3>

<p><strong>Edad:</strong> <span id="patientEdad"></span></p>

<p><strong>Teléfono:</strong> <span id="patientTelefono"></span></p>

<p><strong>Tratamiento:</strong> <span id="patientTratamiento"></span></p>

<p><strong>Última visita:</strong> <span id="patientVisita"></span></p>

        </div>
    </div>
</div>
</section>
</div>
</main>
<script src="js.js"></script>
</body>
</html>