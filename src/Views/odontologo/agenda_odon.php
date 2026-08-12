<!DOCTYPE html>
<html lang="es">

<head>
    <base href="/LOGIN_ORIGINAL/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Gestión de Agenda</title>

    <link rel="stylesheet" href="public/css/odontologo/menu.css">
    <link rel="stylesheet" href="public/css/odontologo/agenda_odon.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>

        <main class="main-content">
            <section class="agenda-wrapper">

                <div class="agenda-main-container">
                    <div class="agenda-top-bar">
                        <h2>Gestión de Agenda</h2>
                        <div class="view-switcher">
                            <button class="btn-toggle">Ver Hoy</button>
                            <button class="btn-toggle active">Semana</button>
                            <button class="btn-toggle">Mes</button>
                        </div>
                    </div>

                    <div class="calendar-grid">
                        <div class="grid-header-cell empty-corner">Hora</div>

                        <?php
                        // Configuración de fechas para la cabecera semanal en el Front
                        $fechaBase = new DateTime(); 
                        $fechaHoySql = $fechaBase->format('Y-m-d');
                        $dias_es = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];

                        for ($i = 0; $i < 5; $i++) {
                            $dia = clone $fechaBase;
                            $dia->modify("+$i days");
                            $nombre_dia = $dias_es[$dia->format('l')];
                            $fecha_texto = $dia->format('d M Y');
                        ?>
                            <div class="grid-header-cell">
                                <strong><?php echo $nombre_dia; ?></strong>
                                <span><?php echo $fecha_texto; ?></span>
                            </div>
                        <?php } ?>

                        <?php
                        // Generar grilla de tiempo estructurada
                        for($hora = 5; $hora <= 20; $hora++){
                            echo '<div class="time-cell">'.$hora.':00</div>';

                            for($i = 0; $i < 5; $i++){
                                $fechaCol = clone $fechaBase;
                                $fechaCol->modify("+$i days");
                                $fechaSql = $fechaCol->format('Y-m-d');
                                $diaNum = $fechaCol->format('j');

                                $claseExtra = ($fechaSql == $fechaHoySql) ? 'col-hoy' : '';

                                echo '<div class="calendar-day-column '.$claseExtra.'" 
                                           data-dia="'.$fechaSql.'" 
                                           data-col-day="'.$diaNum.'" 
                                           data-hora="'.$hora.'"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="patients-today-sidebar">
                    <h3>Pacientes de Hoy</h3>
                    <div class="patient-list contenedor-vista-hoy">
                        </div>
                </div>

            </section>
        </main>
    </div>

    <div id="modal-reprogramar" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Reprogramar Cita</h3>
                <a href="#" class="btn-close-modal">&times;</a>
            </div>

            <div class="modal-body">
                <h4 class="section-title">Datos de la Nueva Cita</h4>

                <div class="original-appointment-box" style="position: relative; overflow: hidden;">
                    <div class="accent-line-blue"></div>
                    <div class="orig-details">
                        <h4 id="modal-paciente-nombre">-- Paciente --</h4>
                        <p class="treatment-type" id="modal-paciente-tratamiento">-- Tratamiento --</p>
                        <p class="time-tag" id="modal-paciente-horario">Seleccione una cita en el calendario</p>
                    </div>
                    <span class="lbl-original">Cita Original</span>
                </div>

                <div class="new-appointment-form-grid">
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
                            <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sa</span><span>Do</span>
                        </div>
                        <div class="calendar-days-numbers" id="calendar-days"></div>
                    </div>

                    <div id="time-slots-container">
                        <p class="text-center text-muted py-3">Selecciona un día para ver las horas disponibles.</p>
                    </div>
                </div>

                <div class="form-group-block">
                    <label>Motivo del cambio <span class="optional-lbl">(opcional)</span></label>
                    <input type="text" class="form-control-input" value="El paciente solicitó cambiar el horario de atención médica.">
                </div>

                <div class="form-checkbox-block">
                    <input type="checkbox" id="notify-patient" checked>
                    <label for="notify-patient">Notificar al paciente automáticamente (vía Alertas del Sistema).</label>
                </div>
            </div>

            <div class="modal-footer">
                <a href="#" class="btn-cancel-modal">Cancelar</a>
                <a href="#" class="btn-confirm-reprogram" id="btn-confirm-reprogramar">Confirmar Reprogramación</a>
            </div>
        </div>
        
    </div>
    <!-- MODAL CALENDARIO VER HOY-->
<!-- EL CONTENEDOR PADRE TIENE QUE SER EL MODAL-OVERLAY Y LLEVAR EL ID -->
<div id="modal-nuevo-procedimiento" class="modal-overlay">
    
    <!-- LA TARJETA INTERNA QUE CONTIENE TODO EL DISEÑO -->
    <div class="modal-card">
        
        <!-- CABECERA DEL MODAL (Título e Icono de Cerrar) -->
        <div class="modal-header">
            <h3>Iniciar Nuevo Tratamiento</h3>
            <!-- Al pulsar la X, vuelve a href="#" para limpiar el hash de la URL y ocultarlo -->
            <a href="#" class="btn-close-modal">&times;</a>
        </div>
        
        <!-- CUERPO DEL MODAL (Formulario igual a la imagen) -->
        <div class="modal-body">
            
            <div class="search-section-title">Búsqueda de Paciente</div>
            <div class="search-section-desc">Ingresa el documento para vincular el procedimiento a su historia clínica.</div>

            <div class="search-inline-container">
                <div class="form-group-block">
                    <label>Nombre del paciente</label>
                    <input type="text" id="input-nombre-paciente" class="form-control-input" placeholder="Ej: Carlos Gonzales" readonly>
                    
                    <label>Documento del Paciente</label>
                    <input type="text" id="input-doc-paciente" class="form-control-input" placeholder="Ej: 10203040" readonly>
                    
                    <input type="hidden" id="idPacienteHidden">
                </div>
            </div>

            <div class="treatment-fields-grid">
                <div class="form-group-block">
                    <label>Procedimiento</label>
                        <select id="select-procedimiento" class="form-control-input">
                            <option value="">Seleccionar...</option>
                        </select>
                </div>
                
                <div class="form-group-block">
                    <label>Fecha</label>
                    <input type="text" class="form-control-input" id="dateInput" readonly>
                </div>
                
                <div class="form-group-block">
                    <label>Costo Oficial</label>
                    <input type="text" id="input-costo" class="form-control-input" placeholder="Automático" readonly>
                </div>
                
                <div class="form-group-block">
                    <label>Duración Estimada</label>
                    <input type="text" id="input-duracion" class="form-control-input" placeholder="Automático" readonly>
                </div>
            </div>

            <div class="modal-footer-seamless">
                <button type="button" class="btn-action-submit" id="btn-iniciar-tratamiento">Iniciar y Ver Odontograma</button>

<input type="hidden" id="idCitaOculto" value="">

                <a href="#" class="btn-action-dismiss">Cancelar</a>
            </div>
            
        </div>
    </div>
</div>

    <script src="public/js/odontologo/agenda_odon.js?v=<?= time() ?>" defer></script>
</body>
</html>