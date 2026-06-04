<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Odonto Estética - Gestión de Agenda</title>

    <link rel="stylesheet" href="../menu/menu.css">

    <!-- CSS -->
    <link rel="stylesheet" href="styles.css">
                                                                                                            
    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

        <div class="menu-layout">
        <?php require_once "../menu/menu.php" ?>

            <!-- =========================================
                        AGENDA
            ========================================== -->
            <main class="main-content">
            <section class="agenda-wrapper">

                <!-- =========================================
                            CALENDARIO
                ========================================== -->

                <div class="agenda-main-container">

                    <!-- TOP BAR -->
                    <div class="agenda-top-bar">

                        <h2>Gestión de Agenda</h2>

                        <div class="view-switcher">

                            <button class="btn-toggle">
                                Ver Hoy
                            </button>

                            <button class="btn-toggle active">
                                Semana
                            </button>

                            <button class="btn-toggle">
                                Mes
                            </button>

                        </div>

                        <a href="#modal-reprogramar" class="btn-reprogramar-top">

                            <i class="fa-solid fa-calendar-days"></i>

                            Reprogramar

                            <i class="fa-solid fa-chevron-right icon-right"></i>

                        </a>

                    </div>

                    <!-- GRID -->
                    <div class="calendar-grid">

                        <!-- HEADER -->
                        <div class="grid-header-cell empty-corner">
                            Hoy
                        </div>

                        <div class="grid-header-cell">
                            <strong>Lunes</strong>
                            <span>14 mayo 2026</span>
                        </div>

                        <div class="grid-header-cell">
                            <strong>Martes</strong>
                            <span>14 mayo 2026</span>
                        </div>

                        <div class="grid-header-cell">
                            <strong>Miércoles</strong>
                            <span>14 mayo 2026</span>
                        </div>

                        <div class="grid-header-cell">
                            <strong>Jueves</strong>
                            <span>14 mayo 2026</span>
                        </div>

                        <div class="grid-header-cell">
                            <strong>Viernes</strong>
                            <span>15 mayo 2026</span>
                        </div>

                        <!-- =========================================
                                    8:00 AM
                        ========================================== -->

                        <div class="time-cell">8:00 AM</div>

                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>

                        <!-- =========================================
                                    9:00 AM
                        ========================================== -->

                        <div class="time-cell">9:00 AM</div>

                        <!-- CARD -->
                        <div
                            class="event-card green-card patient-card"
                            data-name="Alejandra Torres"
                            data-treatment="Ajuste Ortodoncia"
                            data-time="09:00 AM"
                            data-id="12345678"
                            data-status="Confirmada">

                            <h5>Alejandra Torres</h5>
                            <p>Ajuste Ortodoncia</p>
                            <span class="ev-time">09:00 AM</span>

                        </div>

                        <!-- CARD -->
                        <div
                            class="event-card green-card patient-card"
                            data-name="Alejandra Torres"
                            data-treatment="Ajuste Ortodoncia"
                            data-time="09:00 AM"
                            data-id="12345679"
                            data-status="Confirmada">

                            <h5>Alejandra Torres</h5>
                            <p>Ajuste Ortodoncia</p>
                            <span class="ev-time">09:00 AM</span>

                        </div>

                        <!-- CARD -->
                        <div
                            class="event-card green-card patient-card"
                            data-name="Alejandra Torres"
                            data-treatment="Ajuste Ortodoncia"
                            data-time="09:00 AM"
                            data-id="12345680"
                            data-status="Pendiente">

                            <h5>Alejandra Torres</h5>
                            <p>Ajuste Ortodoncia</p>
                            <span class="ev-time">09:00 AM</span>

                        </div>

                        <!-- CARD -->
                        <div
                            class="event-card blue-card patient-card"
                            data-name="Juan Guarnizo"
                            data-treatment="Limpieza"
                            data-time="11:30 AM"
                            data-id="22334455"
                            data-status="Confirmada">

                            <h5>Juan Guarnizo</h5>
                            <p>Limpieza</p>
                            <span class="ev-time">11:30 AM</span>

                        </div>

                        <div class="calendar-day-column"></div>

                        <!-- =========================================
                                    10:30 AM
                        ========================================== -->

                        <div class="time-cell">10:30 AM</div>

                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>

                        <!-- =========================================
                                    11:30 AM
                        ========================================== -->

                        <div class="time-cell">11:30 AM</div>

                        <!-- CARD -->
                        <div
                            class="event-card blue-card patient-card"
                            data-name="Maria Fernanda López"
                            data-treatment="Limpieza"
                            data-time="11:30 AM"
                            data-id="99887766"
                            data-status="Confirmada">

                            <h5>Maria Fernanda López</h5>
                            <p>Limpieza</p>
                            <span class="ev-time">11:30 AM</span>

                        </div>

                        <!-- CARD -->
                        <div
                            class="event-card blue-card patient-card"
                            data-name="María Fernanda López"
                            data-treatment="Limpieza"
                            data-time="11:30 AM"
                            data-id="99887767"
                            data-status="Pendiente">

                            <h5>María Fernanda López</h5>
                            <p>Limpieza 11:30 AM</p>

                        </div>

                        <!-- REPROGRAMAR -->
                        <a
                            href="#modal-reprogramar"
                            class="event-card clickable-reprogram-card patient-card"
                            data-name="Luis Eduardo Pérez"
                            data-treatment="Consulta General"
                            data-time="11:30 AM"
                            data-id="12345678"
                            data-status="Pendiente">

                            <h5>Luis Eduardo Pérez</h5>

                            <p>ID: 12345678</p>

                            <p class="sub">
                                Consulta General 11:30 AM
                            </p>

                            <div class="floating-tooltip">

                                <i class="fa-solid fa-calendar-days"></i>

                                Reprogramar Cita

                            </div>

                        </a>

                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>

                        <!-- =========================================
                                    1:00 PM
                        ========================================== -->

                        <div class="time-cell">1:00 PM</div>

                        <!-- CARD -->
                        <div
                            class="event-card green-card patient-card"
                            data-name="Carlos Ramirez"
                            data-treatment="Limpieza"
                            data-time="03:00 PM"
                            data-id="44556677"
                            data-status="Confirmada">

                            <h5>Carlos Ramirez</h5>
                            <p>Limpieza 03:00 PM</p>

                        </div>

                        <!-- CARD -->
                        <div
                            class="event-card purple-card patient-card"
                            data-name="Ana Sofia Martinez"
                            data-treatment="Consulta General"
                            data-time="02:00 PM"
                            data-id="66778899"
                            data-status="Confirmada">

                            <h5>Ana Sofia Martinez</h5>
                            <p>Consulta General 02:00 PM</p>

                        </div>

                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>
                        <div class="calendar-day-column"></div>

                    </div>

                </div>

                <!-- =========================================
                        SIDEBAR PACIENTES
                ========================================== -->

                <div class="patients-today-sidebar">

                    <h3>Pacientes de Hoy</h3>

                    <div class="patient-list">

                        <!-- PACIENTE -->
                        <div class="patient-sidebar-item">

                            <img
                                src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=80&q=80"
                                alt="">

                            <div class="p-meta">
                                <h4>Alejandra Torres</h4>
                                <p>ID: 12345678</p>
                            </div>

                        </div>

                        <!-- PACIENTE -->
                        <div class="patient-sidebar-item">

                            <img
                                src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=80&q=80"
                                alt="">

                            <div class="p-meta">
                                <h4>Luis Eduardo Pérez</h4>
                                <p>ID: 12345678</p>
                            </div>

                        </div>

                        <!-- PACIENTE -->
                        <div class="patient-sidebar-item">

                            <img
                                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=80&q=80"
                                alt="">

                            <div class="p-meta">
                                <h4>Juan Guarnizo</h4>
                                <p>ID: 12345678</p>
                            </div>

                        </div>

                        <!-- PACIENTE -->
                        <div class="patient-sidebar-item">

                            <img
                                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=80&q=80"
                                alt="">

                            <div class="p-meta">
                                <h4>Maria Fernanda</h4>
                                <p>ID: 12345678</p>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>

    <!-- =========================================
                MODAL REPROGRAMAR
    ========================================== -->

    <div id="modal-reprogramar" class="modal-overlay">

        <div class="modal-card">

            <!-- HEADER -->
            <div class="modal-header">

                <h3>Reprogramar Cita</h3>

                <a href="#" class="btn-close-modal">
                    &times;
                </a>

            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- CITA ORIGINAL -->
                <div class="original-appointment-box">

                    <div class="accent-line-blue"></div>

                    <div class="orig-details">

                        <h4></h4>

                        <p class="treatment-type">---</p>

                        <p class="time-tag">---</p>

                    </div>

                    <span class="lbl-original">
                        Cita Original
                    </span>

                </div>

                <!-- NUEVA CITA -->
                <h4 class="section-title">
                    Datos de la Nueva Cita
                </h4>

                <!-- SELECT -->
                <div class="form-group-block">

                    <label>
                        Seleccionar Paciente
                    </label>

                    <select id="patient-selector" class="form-control-input">

                        <option value="N.N"></option>

                        <option
                            data-name="Alejandra Torres"
                            data-treatment="Ortodoncia"
                            data-time="09:00 AM"
                            data-id="12345678"
                            data-status="Confirmada">

                            Alejandra Torres

                        </option>

                        <option
                            data-name="Maria Fernanda"
                            data-treatment="Limpieza"
                            data-time="11:30 AM"
                            data-id="87654321"
                            data-status="Pendiente">

                            Maria Fernanda

                        </option>

                        <option
                            data-name="Carlos Ramirez"
                            data-treatment="Consulta General"
                            data-time="01:00 PM"
                            data-id="99887766"
                            data-status="Confirmada">

                            Carlos Ramirez

                        </option>

                    </select>

                </div>

                <!-- FORM GRID -->
                <div class="new-appointment-form-grid">

                    <!-- MINI CALENDAR -->
                    <div class="mini-calendar-wrapper">

                        <div class="calendar-month-header">

                        <button class="cal-nav-btn prev-month">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <span id="calendar-month-label"></span>

                         <button class="cal-nav-btn next-month">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>

                    </div>

                        <div class="calendar-weekdays">

                            <span>Lu</span>
                            <span>Ma</span>
                            <span>Mi</span>
                            <span>Ju</span>
                            <span>Vi</span>
                            <span>Sa</span>
                            <span>Do</span>

                        </div>

                        <div class="calendar-days-numbers" id="calendar-days"> </div>
                    </div>

                    <!-- HORARIOS -->
                    <div class="time-slots-grid">

                        <button class="time-slot-btn">09:00 AM</button>
                        <button class="time-slot-btn">10:30 AM</button>
                        <button class="time-slot-btn">10:30 AM</button>
                        <button class="time-slot-btn">11:30 AM</button>
                        <button class="time-slot-btn">01:00 PM</button>
                        <button class="time-slot-btn">02:30 PM</button>
                        <button class="time-slot-btn">02:30 PM</button>
                        <button class="time-slot-btn">04:30 PM</button>
                        <button class="time-slot-btn">04:00 PM</button>

                    </div>

                </div>

                <!-- MOTIVO -->
                <div class="form-group-block">

                    <label>
                        Motivo del cambio
                        <span class="optional-lbl">(opcional)</span>
                    </label>

                    <input
                        type="text"
                        class="form-control-input"
                        value="El paciente solicitó adelantar la cita por viaje.">

                </div>

                <!-- CHECK -->
                <div class="form-checkbox-block">

                    <input
                        type="checkbox"
                        id="notify-patient"
                        checked>

                    <label for="notify-patient">

                        Notificar al paciente automáticamente
                        (vía WhatsApp/SMS).

                    </label>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">

                <a href="#" class="btn-cancel-modal">
                    Cancelar
                </a>

                <a href="#" class="btn-confirm-reprogram">
                    Confirmar Reprogramación
                </a>

            </div>

        </div>

    </div>
    <!-- JS -->
    <script src="js.js" defer></script>
</body>

</html>