<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Reportes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/menu.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/reportes_odon.css">
</head>
<body>
    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>

        <main class="main-content dashboard-content">
            <section class="kpi-metrics-row">
                <div class="metric-card-kpi blue-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Citas de Hoy</span>
                        <span class="kpi-number"><?= $citasHoy; ?></span>
                        <span class="kpi-anchor">Próxima: <?= $proximaCita; ?></span>
                    </div>
                </div>
                <div class="metric-card-kpi green-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Pacientes Tratados</span>
                        <span class="kpi-number"><?= $pacientesActivos; ?></span>
                        <span class="kpi-anchor text-green">Atendidos por usted</span>
                    </div>
                </div>
                <div class="metric-card-kpi purple-kpi">
                    <div class="kpi-icon-container"><i class="fa-solid fa-kit-medical"></i></div>
                    <div class="kpi-data">
                        <span class="kpi-title">Tratamientos Totales</span>
                        <span class="kpi-number"><?= $tratamientosActivos; ?></span>
                        <span class="kpi-anchor text-purple">Evoluciones registradas</span>
                    </div>
                </div>
            </section>

            <div class="dashboard-grid-layout">
                <div class="left-operational-column">
                    <div class="panel-card margin-bottom-space">
                        <div class="table-header-toolbar">
                            <h2>Agenda de Hoy</h2>
                            <a href="/LOGIN_ORIGINAL/odontologo/agenda" class="kpi-anchor text-size-sm">Ver agenda completa</a>
                        </div>
                        
                        <div class="agenda-items-list">
                            <?php if(empty($agendaHoy)): ?>
                                <p class="empty-agenda-msg">No hay compromisos médicos programados para el día de hoy.</p>
                            <?php else: ?>
                                <?php foreach($agendaHoy as $cita): ?>
                                    <div class="agenda-item border-green">
                                        <div class="agenda-time-meta">
                                            <strong><?= $cita['hora']; ?></strong>
                                            <span>60 min</span>
                                        </div>
                                        <div class="pacient-avatar-info">
                                            <div class="avatar-iniciales avatar-md">
                                                <?= substr($cita['paciente_nombre'],0,2); ?>
                                            </div>
                                            <div class="meta-txt">
                                                <strong><?= htmlspecialchars($cita['paciente_nombre']); ?></strong>
                                                <span><?= htmlspecialchars($cita['MOTIVO']); ?></span>
                                            </div>
                                        </div>
                                        <span class="pill-status bg-green"><?= htmlspecialchars($cita['ESTADO']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="panel-inner-footer">
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="table-header-toolbar">
                            <h2>Pacientes Recientes</h2>
                        </div>
                        <div class="table-scroll-container">
                            <table class="data-dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Tratamiento Evaluado</th>
                                        <th>Fecha Registro</th>
                                        <th class="text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pacientesRecientes as $pr): ?>
                                        <tr>
                                            <td>
                                                <div class="pacient-table-cell">
                                                    <div class="avatar-iniciales avatar-sm">
                                                        <?= substr($pr['paciente_nombre'],0,2); ?>
                                                    </div>
                                                    <div class="meta-txt">
                                                        <strong class="dark-bold-text"><?= htmlspecialchars($pr['paciente_nombre']); ?></strong>
                                                        <span class="id-sublabel">Doc: <?= $pr['NUMERO_DOCUMENTO']; ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><strong class="dark-medium-text"><?= htmlspecialchars(substr($pr['MOTIVO_CONSULTA'], 0, 25)) . '...'; ?></strong></td>
                                            <td><?= $pr['fecha']; ?></td>
                                            <td class="text-right">
                                                <a href="#" class="btn-action-table ver-paciente" 
                                                   data-nombre="<?= htmlspecialchars($pr['paciente_nombre']); ?>"
                                                   data-documento="<?= $pr['NUMERO_DOCUMENTO']; ?>" 
                                                   data-tratamiento="<?= htmlspecialchars($pr['MOTIVO_CONSULTA']); ?>"
                                                   data-fecha="<?= $pr['fecha']; ?>"
                                                   data-telefono="<?= htmlspecialchars($pr['TELEFONO']); ?>">Ver</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
                            <?php 
                            $coloresHex = ['#1a56db', '#10b981', '#f59e0b', '#8b5cf6', '#6b7280'];
                            $conicGradient = [];
                            $acumulado = 0;
                            $idxColor = 0;
                            
                            if (!empty($distribucion)) {
                                foreach($distribucion as $dist) {
                                    $color = $coloresHex[$idxColor % count($coloresHex)];
                                    $porcentaje = $dist['porcentaje'];
                                    $inicio = $acumulado;
                                    $acumulado += $porcentaje;
                                    $conicGradient[] = "$color $inicio% $acumulado%";
                                    $idxColor++;
                                }
                                $gradientString = implode(', ', $conicGradient);
                            } else {
                                $gradientString = "#e5e7eb 0% 100%"; 
                            }
                            ?>

                            <div class="donut-chart-graphic dynamic-donut" style="background: conic-gradient(<?= $gradientString ?>);">
                                <div class="donut-hole inner-hole">
                                    <strong><?= count($distribucion); ?></strong>
                                    <span>Tipos</span>
                                </div>
                            </div>
                            
                            <ul class="chart-legend-list">
                                <?php 
                                $clasesDot = ['orto', 'endo', 'limp', 'imp', 'otr'];
                                $idx = 0;
                                foreach($distribucion as $dist): 
                                    $dot = $clasesDot[$idx % count($clasesDot)]; $idx++;
                                ?>
                                    <li>
                                        <span class="dot <?= $dot; ?>"></span> 
                                        <?= htmlspecialchars($dist['nombre']); ?> 
                                        <span class="pct"><?= $dist['porcentaje']; ?>% <small>(<?= $dist['cantidad']; ?>)</small></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>


                </div>
            </div>
        </main>
    </div>

    <div id="modalPaciente" class="modal-paciente">
        <div class="contenido-modal-paciente">
            <span class="cerrar-modal-paciente">&times;</span>
            <h2>Información de Auditoría del Paciente</h2>
            <div class="info-paciente">
                <p><strong>Nombre completo:</strong> <span id="infoNombre"></span></p>
                <p><strong>Número de Documento:</strong> <span id="infoDocumento"></span></p> 
                <p><strong>Último Tratamiento:</strong> <span id="infoTratamiento"></span></p>
                <p><strong>Fecha Registro:</strong> <span id="infoFecha"></span></p>
                <p><strong>Teléfono de Contacto:</strong> <span id="infoTelefono"></span></p>
            </div>
        </div>
    </div>

    <div id="toastInfo" class="toast-info"></div>
    <script src="/LOGIN_ORIGINAL/public/js/odontologo/reportes_odon.js" defer></script>
</body>
</html>