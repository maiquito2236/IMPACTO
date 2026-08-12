<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - Odonto Estética</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/pedir_cita.css">
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">
        
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-grow-1 p-4 overflow-auto">
            
            <?php require_once __DIR__ . '/layouts/header.php'; ?>
            
            <div class="card border-0 p-3 mb-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between px-md-4">
                    <div class="d-flex align-items-center gap-2" id="step-indicator-1">
                        <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                        <span class="text-primary fw-bold small">Seleccionar servicio</span>
                    </div>
                    <div class="flex-grow-1 mx-3 border-bottom border-light-subtle"></div>
                    <div class="d-flex align-items-center gap-2" id="step-indicator-2">
                        <span class="badge bg-secondary-subtle text-muted rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
                        <span class="text-muted small">Elegir fecha y hora</span>
                    </div>
                    <div class="flex-grow-1 mx-3 border-bottom border-light-subtle"></div>
                    <div class="d-flex align-items-center gap-2" id="step-indicator-3">
                        <span class="badge bg-secondary-subtle text-muted rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
                        <span class="text-muted small">Confirmar cita</span>
                    </div>
                </div>
            </div>

            <form id="form-agendar-cita" action="/LOGIN_ORIGINAL/guardar_cita" method="POST">
                <input type="hidden" id="input-procedimiento-id" name="procedimiento_id" value="">
                <input type="hidden" id="input-horario-id" name="horario_id" value="">
                <input type="hidden" id="input-fecha-seleccionada" name="fecha_cita" value="">
                <input type="hidden" id="input-hora-seleccionada" name="hora_cita" value="">

                <div class="row g-4">
                    
                    <div class="col-1024-4 col-xl-4 col-lg-6">
                        <div class="card border-0 p-4 h-100 shadow-sm">
                            <h3 class="fs-6 fw-bold text-dark-blue mb-4">Selecciona el servicio</h3>
                            
                            <div class="d-flex flex-column gap-3">
                            <?php foreach ($procedimientos as $procedimiento): ?>
                                <label class="service-card p-3 rounded-3 d-flex align-items-center position-relative" 
                                    data-id="<?= $procedimiento['ID_PROCEDIMIENTO'] ?>" 
                                    data-nombre="<?= htmlspecialchars($procedimiento['NOMBRE_PROCEDIMIENTO']) ?>" 
                                    data-precio="$<?= number_format($procedimiento['COSTO'], 0, ',', '.') ?>"
                                    data-duracion="<?= $procedimiento['TIEMPO_ESTIMADO'] ?> minutos"
                                    data-especialidad="<?= $procedimiento['ESPECIALIDAD_ID_ESPECIALIDAD'] ?>">
                                    
                                    <input type="radio" name="servicio_radio" class="d-none" value="<?= $procedimiento['ID_PROCEDIMIENTO'] ?>">
                                    <span class="custom-radio-circle me-3"></span>

                                    <div class="bg-primary-light text-primary p-2 rounded-2 me-3">
                                        <i class="fa-solid fa-tooth"></i>
                                    </div>

                                    <div class="flex-grow-1">
                                        <h4 class="fs-6 fw-bold m-0 text-dark-blue"><?= htmlspecialchars($procedimiento['NOMBRE_PROCEDIMIENTO']) ?></h4>
                                        <p class="m-0 text-muted font-xs line-height-sm"><?= htmlspecialchars($procedimiento['DESCRIPCION']) ?></p>
                                    </div>

                                    <span class="fw-bold text-primary ms-2 font-sm">
                                        $<?= number_format($procedimiento['COSTO'], 0, ',', '.') ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                            </div>
                            <button type="button" id="btn-continuar-servicio" class="btn btn-primary w-100 py-2-5 mt-4 fw-bold">Continuar</button>
                        </div>
                    </div>

                    <div class="col-1024-4 col-xl-4 col-lg-6">
                        <div class="card border-0 p-4 h-100 shadow-sm">
                            <h3 class="fs-6 fw-bold text-dark-blue mb-4">Selecciona la fecha y hora</h3>
                            
                            <div class="border rounded-3 p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <button type="button" id="prev-month" class="btn btn-link btn-sm p-0 text-muted">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </button>
                                    <span id="current-month" class="fw-bold text-dark-blue small"></span>
                                    <button type="button" id="next-month" class="btn btn-link btn-sm p-0 text-muted">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </button>
                                </div>
                                
                                <div class="calendar-weekdays text-center fw-bold text-muted mb-2 font-xs">
                                    <div>Do</div><div>Lu</div><div>Ma</div><div>Mi</div><div>Ju</div><div>Vi</div><div>Sá</div>
                                </div>
                                
                                <div id="calendar-days-container" class="calendar-days text-center font-xs"></div>
                            </div>

                            <div class="mb-4">
                                <h4 id="selected-date-label" class="font-sm text-dark-blue fw-normal mb-3">
                                    Selecciona un día del calendario para ver las horas disponibles.
                                </h4>
                                <div id="contenedor-horarios" class="row row-cols-3 g-2" data-horarios='<?= json_encode($horarios); ?>'></div>
                            </div>

                            <div class="mb-4" id="contenedor-odontologos-wrapper" style="display: none;">
                                <h4 class="font-sm text-dark-blue fw-normal mb-3">
                                    Selecciona un odontólogo
                                </h4>
                                <div id="contenedor-odontologos" class="d-flex flex-column gap-2"></div>
                            </div>

                            <button type="button" id="btn-continuar-fecha" class="btn btn-primary w-100 py-2-5 fw-bold">Continuar</button>
                            <button type="button" id="btn-volver-fecha" class="btn btn-outline-primary w-100 py-2-5 mt-2 fw-bold">Volver</button>
                        </div>
                    </div>

                    <div class="col-1024-4 col-xl-4 col-lg-12">
                        <div class="card border-0 p-4 h-100 shadow-sm justify-content-between">
                            <div>
                                <h3 class="fs-6 fw-bold text-dark-blue mb-4">Resumen de tu cita</h3>
                                
                                <div class="d-flex flex-column gap-3 mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-primary fs-5 text-center" style="width: 24px;"><i class="fa-solid fa-tooth"></i></div>
                                        <div>
                                            <small class="text-muted d-block font-xs">Servicio</small>
                                            <strong id="summary-service" class="text-dark-blue font-sm">Selecciona un servicio</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-primary fs-5 text-center" style="width: 24px;"><i class="fa-solid fa-calendar-days"></i></div>
                                        <div>
                                            <small class="text-muted d-block font-xs">Fecha</small>
                                            <strong id="summary-date" class="text-dark-blue font-sm">Selecciona una fecha</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-primary fs-5 text-center" style="width: 24px;"><i class="fa-regular fa-clock"></i></div>
                                        <div>
                                            <small class="text-muted d-block font-xs">Hora</small>
                                            <strong id="summary-time" class="text-dark-blue font-sm">Selecciona una hora</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-primary fs-5 text-center" style="width: 24px;"><i class="fa-solid fa-user-doctor"></i></div>
                                        <div>
                                            <small class="text-muted d-block font-xs">Odontólogo</small>
                                            <strong id="summary-doctor" class="text-dark-blue font-sm">--</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-primary fs-5 text-center" style="width: 24px;"><i class="fa-regular fa-circle-dot"></i></div>
                                        <div>
                                            <small class="text-muted d-block font-xs">Tiempo estimado</small>
                                            <strong id="summary-duration" class="text-dark-blue font-sm">--</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <small class="text-muted font-xs">Precio</small>
                                    <div id="summary-price" class="fs-3 fw-bold text-success">$0</div>
                                </div>

                                <div class="bg-primary-light p-3 rounded-3 d-flex gap-2 mb-4">
                                    <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                                    <div>
                                        <strong class="text-primary font-xs d-block">Importante</strong>
                                        <p class="m-0 font-xs text-dark-blue line-height-sm">Por favor llega 10 minutos antes de tu cita.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="button" id="btn-confirmar-final" class="btn btn-primary w-100 py-2-5 fw-bold">Confirmar Cita</button>
                                <button type="button" id="btn-volver-resumen" class="btn btn-outline-primary w-100 py-2-5 mt-2 fw-bold">Volver</button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="/LOGIN_ORIGINAL/public/js/paciente/pedir_cita.js"></script>
</body>
</html>