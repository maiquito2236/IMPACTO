<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Notificaciones</title>
    
    <!-- FUENTE INTER ELIMINADA PARA NO ENCOGER EL MENÚ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/menu.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/notificaciones_odon.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>

        <!-- ESTRUCTURA IDÉNTICA A PACIENTES -->
        <main class="main-content">
            <section class="filters-section">
                <div class="filter-group">
                    <label for="filter-tipo">Tipo</label>
                    <select id="filter-tipo" class="filter-input">
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
                    <input type="date" id="filter-fecha" class="filter-input">
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
                    </div>

                    <?php if ($sinLeer > 0): ?>
                        <button class="mark-read" id="btn-mark-all">
                            <i class="fa-solid fa-check-double"></i> Marcar todas como leídas
                        </button>
                    <?php endif; ?>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>FECHA Y HORA</th>
                                <th>TIPO</th>
                                <th>MENSAJE DEL SISTEMA</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="notifications-tbody">
                            <?php if(empty($notificaciones)): ?>
                                <tr><td colspan="5" style="text-align:center; padding:30px; color:#777;">No tienes notificaciones en tu historial clínico.</td></tr>
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
                                    
                                    <td class="cell-fecha" style="white-space: nowrap;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <div class="dot"></div>
                                            <?= date('d M, h:i A', strtotime($n['FECHA_ENVIO'])); ?>
                                        </div>
                                    </td>
                                    <td class="cell-tipo">
                                        <span class="badge <?= $catClass; ?>" style="display:inline-flex; align-items:center; gap:6px;">
                                            <i class="<?= $icon; ?>"></i> <?= htmlspecialchars($tipoNombre); ?>
                                        </span>
                                    </td>
                                    <td><div class="msg-text" style="max-width: 100%; min-width: 250px;"><?= htmlspecialchars($n['MENSAJE']); ?></div></td>
                                    <td>
                                        <span class="badge <?= $isUnread ? 'badge-status-unread' : 'badge-status-read'; ?>">
                                            <?= $isUnread ? 'Sin leer' : 'Leída'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if($isUnread): ?>
                                            <button class="btn-action-read btn-marcar-una" title="Marcar como leída" style="background:none; border:none; color:#0f62fe; cursor:pointer; font-size: 18px;">
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
            </section>
        </main>
    </div>
    <script src="/LOGIN_ORIGINAL/public/js/odontologo/notificaciones_odon.js?v=<?= time() ?>" defer></script>
</body>
</html>