<?php
// --- Función utilitaria para obtener iniciales en tarjetas ---
function obtenerIniciales($nombreCompleto) {
    $nombreCompleto = strtoupper(trim($nombreCompleto));
    if (empty($nombreCompleto)) return "??";
    $partes = explode(" ", $nombreCompleto);
    if (count($partes) < 2) return substr($partes[0], 0, 2);
    return substr($partes[0], 0, 1) . substr($partes[1], 0, 1);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Dashboard</title>
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/menu.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/dashboard_odon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>

        <section class="main-content">
            <section class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon blue"><i class="fa-regular fa-calendar"></i></div>
                    <div class="metric-info">
                        <p class="metric-label">Citas de Hoy</p>
                        <h3 class="metric-value"><?= $citasHoy; ?></h3>
                        <p class="metric-sub">Próxima: <?= $proximaCita; ?></p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon green"><i class="fa-regular fa-user"></i></div>
                    <div class="metric-info">
                        <p class="metric-label">Pacientes Tratados</p>
                        <h3 class="metric-value"><?= $pacientesActivos; ?></h3>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon purple"><i class="fa-solid fa-bezier-curve"></i></div>
                    <div class="metric-info">
                        <p class="metric-label">Tratamientos Registrados</p>
                        <h3 class="metric-value"><?= $tratamientosActivos; ?></h3>
                    </div>
                </div>
            </section>

            <div class="two-columns-layout">
                <div class="left-column">
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h3>Agenda de Hoy</h3>
                            <a href="/LOGIN_ORIGINAL/odontologo/agenda" class="link-view-all">Ver agenda completa</a>
                        </div>
                        
                        <div class="agenda-list">
                            <?php if(empty($agendaHoy)): ?>
                                <p style="padding: 15px; color: #777;">No hay citas programadas para hoy.</p>
                            <?php else: ?>
                                <?php foreach(array_slice($agendaHoy, 0, 3) as $cita): ?>
                                    <div class="agenda-item">
                                        <div class="agenda-time">
                                            <span class="time"><?= $cita['hora']; ?></span>
                                            <span class="duration">-- min</span>
                                        </div>
                                        <div class="patient-brief">
                                            <div class="avatar-iniciales"><?= obtenerIniciales($cita['paciente_nombre']); ?></div>
                                            <div>
                                                <h4><?= htmlspecialchars($cita['paciente_nombre']); ?></h4>
                                                <p><?= htmlspecialchars($cita['MOTIVO']); ?></p>
                                            </div>
                                        </div>
                                        <span class="status-badge <?= strtolower($cita['ESTADO']) == 'programada' ? 'pending' : 'confirmed'; ?>">
                                            <?= htmlspecialchars($cita['ESTADO']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="block-footer">
                            <a href="#" class="view-more-center" id="openCitas">Ver todas las citas</a>
                        </div>
                    </section>

                    <section class="dashboard-block">
                        <div class="block-header">
                            <h3>Pacientes Recientes</h3>
                        </div>
                        <table class="dashboard-table">
                            <tbody>
                                <?php if(empty($pacientesRecientes)): ?>
                                    <tr><td colspan="4" style="color: #777; text-align: center;">No hay pacientes recientes.</td></tr>
                                <?php else: ?>
                                    <?php foreach($pacientesRecientes as $pr): ?>
                                        <?php 
                                            $cumpleanos = new DateTime($pr['FECHA_NACIMIENTO']);
                                            $hoyDate = new DateTime(date('Y-m-d'));
                                            $edad = $hoyDate->diff($cumpleanos)->y . " años";
                                            $iniciales = obtenerIniciales($pr['paciente_nombre']);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="table-patient">
                                                    <div class="avatar-iniciales"><?= $iniciales; ?></div>
                                                    <span><?= htmlspecialchars($pr['paciente_nombre']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-muted"><?= htmlspecialchars(substr($pr['MOTIVO_CONSULTA'], 0, 25)) . '...'; ?></td>
                                            <td class="text-muted"><?= $pr['fecha']; ?></td>
                                            <td>
                                                <button class="btn-table-action btn-ver-paciente" 
                                                        data-nombre="<?= htmlspecialchars($pr['paciente_nombre']); ?>"
                                                        data-edad="<?= $edad; ?>"
                                                        data-telefono="<?= htmlspecialchars($pr['TELEFONO']); ?>"
                                                        data-tratamiento="<?= htmlspecialchars($pr['MOTIVO_CONSULTA']); ?>"
                                                        data-visita="<?= $pr['fecha']; ?>"
                                                        data-iniciales="<?= $iniciales; ?>">
                                                    Ver
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                </div>

                <div class="right-column">
                    <section class="dashboard-block">
                        <h3>Resumen de Tratamientos</h3>
                        <div class="chart-container-placeholder"></div>
                        
                        <?php if (empty($tratamientosDinamicos)): ?>
                            <p style="padding: 15px; color: #777; text-align: center;">No hay procedimientos aplicados activos.</p>
                        <?php else: ?>
                            <?php 
                            $coloresBarras = ['orto-progress', 'endo-progress', 'limp-progress'];
                            $indexColor = 0;
                            
                            foreach ($tratamientosDinamicos as $tratamiento): 
                                $claseColor = $coloresBarras[$indexColor % count($coloresBarras)];
                                $indexColor++;
                            ?>
                                <div class="progress-item">
                                    <div class="progress-info">
                                        <span><?= htmlspecialchars($tratamiento['nombre']); ?></span>
                                        <span><?= $tratamiento['porcentaje']; ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress <?= $claseColor; ?>" style="width: <?= $tratamiento['porcentaje']; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>
                    

                    <section class="dashboard-block notifications-block">
                        <div class="block-header">
                            <h3>Alertas y Recordatorios</h3>
                            <span class="notification-badge-count">
                                <?php 
                                $sinLeer = array_filter($alertas, function($a) { return $a['ESTADO'] === 'NO_LEIDA'; });
                                echo count($sinLeer);
                                ?> Nuevas
                            </span>
                        </div>

                        <div class="notifications-list">
                            <?php if (empty($alertas)): ?>
                                <div class="no-notifications">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No tienes alertas pendientes.</p>
                                </div>
                            <?php else: ?>
                               <?php foreach ($alertas as $alerta): 
                                    $icon = "fa-bell";
                                    $colorClass = "info-alert";
                                    $redireccion = "#"; 
                                    $mensajeUpper = mb_strtoupper($alerta['MENSAJE']);
                                    
                                    if (strpos($mensajeUpper, 'PAGO') !== false || strpos($mensajeUpper, 'VALOR') !== false) {
                                        $icon = "fa-dollar-sign";
                                        $colorClass = "success-alert";
                                    } elseif (strpos($mensajeUpper, 'CITA') !== false || strpos($mensajeUpper, 'PACIENTE') !== false) {
                                        $icon = "fa-calendar-check";
                                        $colorClass = "calendar-alert";
                                    }
                                    $horaAmigable = date('h:i A', strtotime($alerta['FECHA_ENVIO']));
                                ?>
                                    <a href="#" 
                                       class="notification-item <?= $alerta['ESTADO'] === 'NO_LEIDA' ? 'unread' : ''; ?> interactiva"
                                       data-id="<?= $alerta['ID_NOTIFICACIONES']; ?>">
                                        <div class="notification-icon <?= $colorClass; ?>"><i class="fas <?= $icon; ?>"></i></div>
                                        <div class="notification-content">
                                            <p class="notification-text"><?= htmlspecialchars($alerta['MENSAJE']); ?></p>
                                            <span class="notification-time"><?= $horaAmigable; ?> • <?= htmlspecialchars($alerta['TIPO_NOMBRE']); ?></span>
                                        </div>
                                        <?php if ($alerta['ESTADO'] === 'NO_LEIDA'): ?>
                                            <span class="unread-dot"></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div>

    <div class="modal" id="modalCitas">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Todas las citas del día</h2>
                <span class="close-modal" id="closeCitas">&times;</span>
            </div>
            <div class="modal-body">
                <?php foreach($agendaHoy as $cita): ?>
                    <div class="modal-agenda-item" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                        <div class="avatar-iniciales" style="width: 35px; height: 35px; font-size: 13px; flex-shrink: 0;"><?= obtenerIniciales($cita['paciente_nombre']); ?></div>
                        <div>
                            <strong><?= $cita['hora']; ?></strong>
                            <p style="margin: 0; color: #555;"><?= htmlspecialchars($cita['paciente_nombre']); ?> - <?= htmlspecialchars($cita['MOTIVO']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="modal" id="modalInfoPaciente">
        <div class="modal-content info-modal">
            <div class="modal-header">
                <h2>Información del paciente</h2>
                <span class="close-modal" id="closeInfo">&times;</span>
            </div>
            <div class="modal-body patient-info">
                <div id="patientAvatar" class="avatar-iniciales modal-big-avatar"></div>
                <h3 id="patientName" style="margin-top: 10px;"></h3>
                <p><strong>Edad:</strong> <span id="patientEdad"></span></p>
                <p><strong>Teléfono:</strong> <span id="patientTelefono"></span></p>
                <p><strong>Tratamiento:</strong> <span id="patientTratamiento"></span></p>
                <p><strong>Última visita:</strong> <span id="patientVisita"></span></p>
            </div>
        </div>
    </div>

    <script src="/LOGIN_ORIGINAL/public/js/odontologo/dashboard_odon.js" defer></script>
</body>
</html>