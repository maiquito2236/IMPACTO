<?php
$notificaciones = $notificaciones ?? [];
$totalAlertas   = $totalAlertas ?? 0;
$sinLeer        = $sinLeer ?? 0;
$leidas         = $leidas ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Odonto Estética</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <!-- Usamos el CSS de Odontologo para heredar el estilo de la tabla y filtros -->
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/notificaciones_odon.css?v=<?= time() ?>">
    
    <style>
        /* Ajustes menores para convivir con bootstrap en el layout del paciente */
        body {
            background-color: var(--bg-main) !important;
        }
        .main-content-table {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .filters-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            align-items: end;
        }
    </style>
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">

        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-grow-1 p-4 overflow-auto">

            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            <div class="mb-4">
                <h2 class="h5 fw-bold text-dark-blue m-0">Notificaciones</h2>
                <p class="text-muted small m-0">Mantente al día con toda la información importante.</p>
            </div>

            <div class="main-content-table mt-4">
                <section class="filters-section">
                    <div class="filter-group">
                        <label for="filter-tipo">Tipo</label>
                        <select id="filter-tipo" class="filter-input form-select shadow-none">
                            <option value="todos">Todos</option>
                            <?php
                            $tiposUnicos = $tiposNotificacion ?? [];
                            foreach ($tiposUnicos as $tipo): ?>
                                <option value="<?= htmlspecialchars(mb_strtoupper($tipo)) ?>"><?= htmlspecialchars(mb_strtoupper($tipo)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Fecha</label>
                        <input type="date" id="filter-fecha" class="filter-input form-control shadow-none">
                    </div>

                    <button class="btn-clear" id="btn-reset-filters">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar filtros
                    </button>
                </section>

                <section class="notifications-card">
                    <div class="tabs-header">
                        <div class="tabs-list">
                            <div class="tab-item active" data-tab="todas">Todas (<?= $totalAlertas; ?>)</div>
                            <div class="tab-item" data-tab="sin-leer">Sin leer (<?= $sinLeer; ?>)</div>
                            <div class="tab-item" data-tab="leidas">Leídas (<?= $totalAlertas - $sinLeer; ?>)</div>
                            <div class="tab-item" data-tab="preferencias">Preferencias</div>
                        </div>

                        <?php if ($sinLeer > 0): ?>
                            <button class="mark-read shadow-sm" id="btn-mark-all">
                                <i class="fa-solid fa-check-double text-primary"></i> Marcar todas como leídas
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-container" id="notifications-table-container">
                        <table class="table mb-0 table-hover align-middle">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="border-0 font-xs fw-bold px-4 py-3">FECHA Y HORA</th>
                                    <th class="border-0 font-xs fw-bold px-4 py-3">TIPO</th>
                                    <th class="border-0 font-xs fw-bold px-4 py-3">MENSAJE DEL SISTEMA</th>
                                    <th class="border-0 font-xs fw-bold px-4 py-3">ESTADO</th>
                                    <th class="border-0 font-xs fw-bold px-4 py-3 text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody id="notifications-tbody" class="border-top-0">
                                <?php if(empty($notificaciones)): ?>
                                    <tr><td colspan="5" style="text-align:center; padding:30px; color:#777;">No tienes notificaciones por el momento.</td></tr>
                                <?php else: ?>
                                <?php foreach($notificaciones as $n): 
                                    $isUnread = ($n['ESTADO'] === 'NO_LEIDA');
                                    $tipoNombre = mb_strtoupper($n['TIPO_NOMBRE']);
                                    
                                    $icon = "fa-bell"; $catClass = "badge-gray"; $tipoText = "Sistema";
                                    
                                    if (strpos($tipoNombre, 'CANCELADA') !== false) {
                                        $icon = "fa-solid fa-calendar-xmark"; $catClass = "badge-alerta"; $tipoText = "Cita";
                                    } elseif (strpos($tipoNombre, 'CITA') !== false || strpos($tipoNombre, 'AGENDA') !== false) {
                                        $icon = "fa-regular fa-calendar"; $catClass = "badge-agenda"; $tipoText = "Cita";
                                    } elseif (strpos($tipoNombre, 'ABONO') !== false) {
                                        $icon = "fa-solid fa-money-bill-transfer"; $catClass = "badge-teal"; $tipoText = "Pago";
                                    } elseif (strpos($tipoNombre, 'PAGO') !== false || strpos($tipoNombre, 'COMISION') !== false) {
                                        $icon = "fa-solid fa-dollar-sign"; $catClass = "badge-facturacion"; $tipoText = "Pago";
                                    } elseif (strpos($tipoNombre, 'HISTORIA CLINICA') !== false) {
                                        $icon = "fa-solid fa-notes-medical"; $catClass = "badge-yellow"; $tipoText = "Historia Clínica";
                                    } elseif (strpos($tipoNombre, 'ACTUALIZACION') !== false || strpos($tipoNombre, 'DATOS') !== false) {
                                        $icon = "fa-solid fa-user-pen"; $catClass = "badge-purple"; $tipoText = "Sistema";
                                    }
                                ?>
                                <tr class="<?= $isUnread ? 'row-unread' : ''; ?>" 
                                        data-tipo="<?= htmlspecialchars($tipoNombre); ?>" 
                                        data-estado="<?= $isUnread ? 'sin-leer' : 'leida'; ?>"
                                        data-fecha="<?= date('Y-m-d', strtotime($n['FECHA_ENVIO'])); ?>"
                                        data-id="<?= $n['ID_NOTIFICACIONES']; ?>">
                                        
                                        <td class="cell-fecha px-4 py-3" style="white-space: nowrap;">
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <div class="dot"></div>
                                                <?= date('d M, h:i A', strtotime($n['FECHA_ENVIO'])); ?>
                                            </div>
                                        </td>
                                        <td class="cell-tipo px-4 py-3">
                                            <span class="badge <?= $catClass; ?>" style="display:inline-flex; align-items:center; gap:6px;">
                                                <i class="<?= $icon; ?>"></i> <?= htmlspecialchars($tipoNombre); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3"><div class="msg-text font-sm" style="max-width: 100%; min-width: 250px;"><?= htmlspecialchars($n['MENSAJE']); ?></div></td>
                                        <td class="px-4 py-3">
                                            <span class="badge <?= $isUnread ? 'badge-status-unread' : 'badge-status-read'; ?> font-xxs">
                                                <?= $isUnread ? 'Sin leer' : 'Leída'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3" style="text-align: center;">
                                            <?php if($isUnread): ?>
                                                <button class="btn-action-read btn-marcar-una p-0 m-0" title="Marcar como leída" style="background:none; border:none; color:#0f62fe; cursor:pointer; font-size: 18px;">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <span style="color:#198754; font-size: 18px;"><i class="fa-solid fa-circle-check"></i></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="preferences-container" style="display: none; padding: 20px;">
                        <h4 class="mb-4" style="color: var(--text-dark);">Preferencias de Notificación</h4>
                        <p class="text-muted mb-4">Elige por qué canales deseas recibir notificaciones externas de tus citas, pagos y actualizaciones.</p>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded" style="background: #fff;">
                                <div>
                                    <h6 class="mb-1"><i class="fa-solid fa-comment-sms text-primary"></i> SMS</h6>
                                    <small class="text-muted">Recibe alertas directamente en tu teléfono por mensaje de texto.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pref-sms" style="transform: scale(1.3); cursor: pointer;" checked>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded" style="background: #fff;">
                                <div>
                                    <h6 class="mb-1"><i class="fa-brands fa-whatsapp text-success"></i> WhatsApp</h6>
                                    <small class="text-muted">Recibe notificaciones inmediatas a través de WhatsApp.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pref-whatsapp" style="transform: scale(1.3); cursor: pointer;" checked>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded" style="background: #fff;">
                                <div>
                                    <h6 class="mb-1"><i class="fa-regular fa-envelope text-danger"></i> Correo Electrónico</h6>
                                    <small class="text-muted">Recibe un resumen y notificaciones en tu bandeja de entrada.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pref-correo" style="transform: scale(1.3); cursor: pointer;" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Usamos el JS del odontologo ya que la logica de filtros y modal de alerta es idéntica -->
    <script>
        // Hacemos que el JS del odontologo funcione con la ruta del paciente para marcar como leida
        window.IS_PACIENTE_MODULE = true;
    </script>
    <script src="/LOGIN_ORIGINAL/public/js/odontologo/notificaciones_odon.js?v=<?= time() ?>"></script>
</body>
</html>