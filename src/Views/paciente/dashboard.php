<?php
// Validar que las variables existan para evitar warnings si se accede directamente
$proximaCita = $proximaCita ?? null;
$tratamientos = $tratamientos ?? [];
$totalPendiente = $totalPendiente ?? 0;
$citasRecientes = $citasRecientes ?? [];
$ultimosPagos = $ultimosPagos ?? [];

function formatFechaEspanol($fechaString) {
    if (!$fechaString) return '';
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $timestamp = strtotime($fechaString);
    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('m', $timestamp) - 1];
    return $dia . ' ' . $mes;
}

function getEstadoBadgeClass($estado) {
    switch (strtolower($estado)) {
        case 'completada': return 'text-success bg-success-light';
        case 'pendiente': return 'text-warning bg-warning-light';
        case 'cancelada': return 'text-danger bg-danger-light';
        case 'no asistió': return 'text-purple bg-purple-light';
        default: return 'text-secondary bg-secondary-light';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Odonto Estética</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/dashboard.css">
    
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">
    
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>
        
        <main class="flex-grow-1 p-4 overflow-auto">
        
            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            
            <div class="container-fluid p-0">
                
                <div class="row g-4 mb-4">
                    <!-- Tarjeta 1: Próxima Cita -->
                    <div class="col-12 col-md-4">
                        <div class="dashboard-card p-3 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="card-icon-box bg-primary-light text-primary">
                                            <i class="fa-regular fa-calendar-check"></i>
                                        </div>
                                        <h3 class="fs-6 m-0 fw-bold text-dark-blue">Próxima cita</h3>
                                    </div>
                                </div>
                            <?php if ($proximaCita): 
                                $fechaTimestamp = strtotime($proximaCita['FECHA_HORA']);
                                $dia = date('d', $fechaTimestamp);
                                $mes = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][(int)date('m', $fechaTimestamp) - 1];
                                $hora = date('h:i A', $fechaTimestamp);
                            ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="date-badge text-center p-2">
                                    <span class="d-block fw-bold fs-3 text-primary lh-1"><?= $dia ?></span>
                                    <span class="d-block font-xs fw-bold text-uppercase tracking-wider"><?= $mes ?></span>
                                    <span class="d-block text-primary font-xs fw-bold mt-1"><?= $hora ?></span>
                                </div>
                                <div>
                                    <h4 class="fs-6 m-0 fw-bold text-dark-blue"><?= htmlspecialchars($proximaCita['odontologo']) ?></h4>
                                    <p class="m-0 text-muted font-sm"><?= htmlspecialchars($proximaCita['procedimiento'] ?? $proximaCita['especialidad'] ?? 'Consulta general') ?></p>
                                    <span class="text-muted font-xs d-block mt-1"><i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($proximaCita['consultorio'] ?? 'Consultorio') ?></span>
                                </div>
                            </div>
                            </div>
                            <a href="/LOGIN_ORIGINAL/mis_citas" class="btn btn-action-outline bg-primary-light text-primary w-100 py-2-5 font-sm border-0 fw-bold mt-auto">Ver detalle de cita</a>
                            <?php else: ?>
                            </div>
                            <div class="d-flex flex-column align-items-center justify-content-center text-center flex-grow-1 gap-2 py-3">
                                <i class="fa-regular fa-calendar-xmark fs-2 text-muted"></i>
                                <p class="text-muted m-0 font-sm">No tienes citas programadas próximamente.</p>
                            </div>
                            <a href="/LOGIN_ORIGINAL/pedir_cita" class="btn btn-action-outline bg-primary-light text-primary w-100 py-2-5 font-sm border-0 fw-bold mt-auto">Agendar una cita</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Mis Tratamientos -->
                    <div class="col-12 col-md-4">
                        <div class="dashboard-card p-3 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="card-icon-box bg-success-light text-success">
                                        <i class="fa-solid fa-tooth"></i>
                                    </div>
                                    <h3 class="fs-6 m-0 fw-bold text-dark-blue">Mis tratamientos</h3>
                                </div>
                                <div class="d-flex flex-column gap-2 mb-3">
                                    <?php if (!empty($tratamientos)): ?>
                                        <?php foreach ($tratamientos as $trat): ?>
                                        <div class="d-flex justify-content-between align-items-center p-2 rounded-3 border border-light">
                                            <span class="font-sm fw-semibold text-dark-blue"><?= htmlspecialchars($trat['nombre']) ?></span>
                                            <span class="badge <?= getEstadoBadgeClass($trat['estado']) ?> font-xs px-2 py-1"><?= htmlspecialchars($trat['estado']) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center font-sm mt-4">Aún no has iniciado ningún tratamiento.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="/LOGIN_ORIGINAL/tratamientos" class="btn btn-action-outline w-100 py-2-5 font-sm border-0 bg-success-light text-success fw-bold d-flex align-items-center justify-content-center gap-1">Ver todos mis tratamientos</a>
                        </div>
                    </div>

                    <!-- Tarjeta 3: Pagos Pendientes -->
                    <div class="col-12 col-md-4">
                        <div class="dashboard-card p-3 h-100 d-flex flex-column justify-content-between section-payments-summary">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="card-icon-box bg-warning-light text-warning">
                                            <i class="fa-solid fa-credit-card"></i>
                                        </div>
                                        <h3 class="fs-6 m-0 fw-bold text-dark-blue">Pagos pendientes</h3>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-end my-2">
                                    <div>
                                        <span class="d-block fs-3 fw-bold text-dark-blue">$<?= number_format($totalPendiente, 2) ?></span>
                                        <?php if ($totalPendiente > 0): ?>
                                            <span class="text-warning font-sm fw-bold d-flex align-items-center gap-1">Pagos requeridos <i class="fa-solid fa-triangle-exclamation"></i></span>
                                        <?php else: ?>
                                            <span class="text-success font-sm fw-bold d-flex align-items-center gap-1">Al día <i class="fa-regular fa-circle-check"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wallet-icon-container">
                                        <i class="fa-solid fa-wallet text-primary fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            <a href="/LOGIN_ORIGINAL/pagos" class="btn btn-action-outline w-100 py-2-5 font-sm border-0 bg-warning-light text-warning fw-bold d-flex align-items-center justify-content-center gap-1">Ver historial de pagos</a>
                        </div>
                    </div>
                </div>

                <!-- Bloque Inferior: Tabla y Recomendaciones -->
                <div class="row g-4">
                    <!-- Historial de Citas Recientes -->
                    <div class="col-12 col-lg-8">
                        <div class="dashboard-card p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="card-icon-box bg-primary-light text-primary">
                                        <i class="fa-regular fa-calendar text-primary"></i>
                                    </div>
                                    <h3 class="fs-6 m-0 fw-bold text-dark-blue">Mis citas recientes</h3>
                                </div>
                                <a href="/LOGIN_ORIGINAL/mis_citas" class="font-sm fw-bold text-decoration-none text-primary">Ver todas <i class="fa-solid fa-arrow-right font-xs ms-1"></i></a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle m-0 custom-dashboard-table">
                                    <thead>
                                        <tr class="table-light-header">
                                            <th class="font-xs text-muted fw-bold text-uppercase">Fecha</th>
                                            <th class="font-xs text-muted fw-bold text-uppercase">Doctor</th>
                                            <th class="font-xs text-muted fw-bold text-uppercase">Especialidad</th>
                                            <th class="font-xs text-muted fw-bold text-uppercase">Motivo</th>
                                            <th class="font-xs text-muted fw-bold text-uppercase">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($citasRecientes)): ?>
                                            <?php foreach ($citasRecientes as $cita): ?>
                                            <tr>
                                                <td class="font-sm fw-semibold text-dark-blue">
                                                    <span class="table-dot-indicator bg-primary"></span> 
                                                    <?= formatFechaEspanol($cita['FECHA_HORA']) ?>
                                                </td>
                                                <td class="font-sm text-muted"><?= htmlspecialchars($cita['odontologo']) ?></td>
                                                <td class="font-sm text-muted"><?= htmlspecialchars($cita['especialidad'] ?? 'General') ?></td>
                                                <td class="font-sm text-muted"><?= htmlspecialchars($cita['procedimiento'] ?? $cita['MOTIVO']) ?></td>
                                                <td><span class="badge <?= getEstadoBadgeClass($cita['estado']) ?> font-xs px-2 py-1 w-100"><?= htmlspecialchars($cita['estado']) ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted font-sm py-4">No hay citas recientes registradas.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recomendaciones e Historial de Facturas Breve -->
                    <div class="col-12 col-lg-4 d-flex flex-column gap-4">
                        <div class="dashboard-card p-3 d-flex flex-column justify-content-between flex-grow-1">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="card-icon-box bg-primary-light text-primary">
                                        <i class="fa-solid fa-notes-medical"></i>
                                    </div>
                                    <h3 class="fs-6 m-0 fw-bold text-dark-blue">Recomendaciones de cuidado</h3>
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex gap-2 align-items-start info-row p-0 m-0">
                                        <div class="card-icon-box bg-primary-light text-primary font-sm flex-shrink-0" style="width: 28px; height: 28px;">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-sm fw-bold text-dark-blue m-0">Cuida tu ortodoncia</h5>
                                            <p class="font-xs text-muted m-0">Técnicas de cepillado</p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 align-items-start info-row p-0 m-0">
                                        <div class="card-icon-box bg-primary-light text-primary font-sm flex-shrink-0" style="width: 28px; height: 28px;">
                                            <i class="fa-solid fa-tooth"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-sm fw-bold text-dark-blue m-0">Mantenimiento de brackets</h5>
                                            <p class="font-xs text-muted m-0">Consejos y cuidados</p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 align-items-start info-row p-0 m-0">
                                        <div class="card-icon-box bg-primary-light text-primary font-sm flex-shrink-0" style="width: 28px; height: 28px;">
                                            <i class="fa-solid fa-glass-water"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-sm fw-bold text-dark-blue m-0">Limpieza con regularidad</h5>
                                            <p class="font-xs text-muted m-0">Hábitos recomendados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-3">
                                <a href="https://www.onsalus.com/como-cuidar-los-dientes-22122.html" target="_blank" class="btn btn-action-outline bg-primary-light text-primary w-100 py-2-5 font-sm border-0 fw-bold">Ver más recomendaciones</a>
                            </div>
                        </div>

                        <div class="dashboard-card p-3 d-flex flex-column justify-content-between flex-grow-1">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="card-icon-box bg-primary-light text-primary">
                                        <i class="fa-regular fa-file-lines"></i>
                                    </div>
                                    <h3 class="fs-6 m-0 fw-bold text-dark-blue">Historial de pagos y facturas</h3>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <?php if (!empty($ultimosPagos)): ?>
                                        <?php foreach ($ultimosPagos as $pago): 
                                            $esPagada = strtolower($pago['ESTADO']) === 'pagada';
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center info-row p-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-regular fa-file-pdf text-primary fs-5"></i>
                                                <div>
                                                    <h5 class="font-sm fw-bold text-dark-blue m-0">Factura #<?= htmlspecialchars($pago['ID_FACTURA']) ?></h5>
                                                    <p class="font-xs text-muted m-0"><?= formatFechaEspanol($pago['FECHA_EMISION']) ?> • <?= ucfirst(strtolower($pago['ESTADO'])) ?></p>
                                                </div>
                                            </div>
                                            <span class="font-sm fw-bold <?= $esPagada ? 'text-success' : 'text-warning' ?>">
                                                $<?= number_format($pago['TOTAL'], 2) ?>
                                            </span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center font-sm m-0">No hay facturas registradas.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div> 
            </div>
            
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>