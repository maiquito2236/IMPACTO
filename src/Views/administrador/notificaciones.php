<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/notificaciones.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>

<div class="menu-layout">

    <?php require_once __DIR__ . '/layouts/menu.php'; ?>

    <main class="menu-main-content">

        <?php require_once __DIR__ . '/layouts/header.php'; ?>

        <!-- HEADER -->
        <div class="noti-header">
            <div class="noti-header-title">
                <h1>Notificaciones</h1>
                <p>Mantente al día con lo más importante de tu consulta.</p>
            </div>
            <div class="noti-header-actions">
                <button class="btn-primary" id="btn-nueva-cita">
                    <i class="fa-solid fa-plus"></i> Nueva Cita
                </button>
                <div class="bell-wrap">
                    <button class="bell-btn" id="bell-btn">
                        <i class="fa-regular fa-bell"></i>
                        <span class="bell-badge" id="bell-badge"
                              style="<?= ($sinLeer ?? 0) === 0 ? 'display:none;' : '' ?>">
                            <?= $sinLeer ?? 0 ?>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="filters-bar">
            <div class="filter-group">
                <label>Tipo</label>
                <div class="select-wrap">
                    <select id="filtro-tipo">
                        <option value="">Todos</option>
                        <?php
                        $tiposUnicos = $tiposNotificacion ?? [];
                        foreach ($tiposUnicos as $tipo): ?>
                            <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
            <div class="filter-group">
                <label>Fecha</label>
                <div class="date-wrap">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="date" id="filtro-fecha" placeholder="Rango de fechas">
                </div>
            </div>
            <button class="btn-limpiar" id="btn-limpiar">
                <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar filtros
            </button>
        </div>

        <!-- PANEL PRINCIPAL -->
        <div class="noti-panel">

            <!-- TABS + ACCIÓN -->
            <div class="tabs-row">
                <div class="tabs">
                    <button class="tab active" data-tab="todas">
                        Todas <span class="tab-count" id="count-todas"><?= $totalAlertas ?? 0 ?></span>
                    </button>
                    <button class="tab" data-tab="sin-leer">
                        Sin leer <span class="tab-count sin-leer-count" id="count-sin-leer"><?= $sinLeer ?? 0 ?></span>
                    </button>
                    <button class="tab" data-tab="leidas">
                        Leídas <span class="tab-count leidas-count" id="count-leidas"><?= ($totalAlertas ?? 0) - ($sinLeer ?? 0) ?></span>
                    </button>
                </div>
                <button class="btn-marcar-todas" id="btn-marcar-todas">
                    <i class="fa-regular fa-circle-check"></i> Marcar todas como leídas
                </button>
            </div>

            <!-- TABLA -->
            <div class="table-wrap">
                <table class="noti-table">
                    <thead>
                        <tr>
                            <th class="th-check"><input type="checkbox" id="check-all" title="Seleccionar todo"></th>
                            <th class="sortable" data-sort="fecha">FECHA Y HORA <i class="fa-solid fa-sort-down"></i></th>
                            <th>TIPO</th>
                            <th>MENSAJE</th>
                            <th>USUARIO</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="noti-tbody"></tbody>
                </table>
                <div id="empty-state" class="empty-state" style="display:none;">
                    <i class="fa-regular fa-bell-slash"></i>
                    <p>No hay notificaciones que coincidan con los filtros.</p>
                    <button class="btn-limpiar" id="btn-limpiar2">
                        <i class="fa-solid fa-rotate-left"></i> Restablecer filtros
                    </button>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            <div class="pagination-bar">
                <span id="pag-info">Mostrando 1 a 10 de <?= $totalAlertas ?? 0 ?> resultados</span>
                <div class="pag-controls">
                    <button class="pag-btn" id="pag-prev"><i class="fa-solid fa-chevron-left"></i></button>
                    <div class="pag-pages" id="pag-pages"></div>
                    <button class="pag-btn" id="pag-next"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <div class="select-wrap per-page-wrap">
                    <select id="per-page">
                        <option value="8">8 por página</option>
                        <option value="10" selected>10 por página</option>
                        <option value="20">20 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- BARRA DE SELECCIÓN MASIVA -->
        <div class="bulk-bar" id="bulk-bar">
            <span id="bulk-count">0 seleccionadas</span>
            <div class="bulk-actions">
                <button class="bulk-btn" id="bulk-leer">
                    <i class="fa-regular fa-circle-check"></i> Marcar como leídas
                </button>
            </div>
            <button class="bulk-close" id="bulk-close"><i class="fa-solid fa-xmark"></i></button>
        </div>

    </main>
</div>

<!-- MODAL DETALLE -->
<div class="modal-overlay" id="modal-overlay">
    <div class="modal-box" id="modal-box">
        <button class="modal-close" id="modal-close"><i class="fa-solid fa-xmark"></i></button>
        <div class="modal-icon-wrap" id="modal-icon-wrap"></div>
        <h3 id="modal-title"></h3>
        <p id="modal-fecha" class="modal-fecha"></p>
        <p id="modal-msg" class="modal-msg"></p>
        <div class="modal-meta" id="modal-meta"></div>
        <div class="modal-footer">
            <button class="modal-btn-pri" id="modal-cerrar">Cerrar</button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<?php
// Convertir los datos de BD a formato que espera el JS
$notificacionesJS = array_map(function($n) {
    return [
        'id'       => (int) $n['ID_NOTIFICACIONES'],
        'fecha'    => date('Y-m-d', strtotime($n['FECHA_ENVIO'])),
        'hora'     => date('h:i A', strtotime($n['FECHA_ENVIO'])),
        'timestamp'=> strtotime($n['FECHA_ENVIO']),
        'tipo'     => $n['TIPO_NOMBRE'],
        'mensaje'  => $n['MENSAJE'],
        'paciente' => $n['USUARIO_NOMBRES'] . ' ' . $n['USUARIO_APELLIDOS'],
        'estado'   => $n['ESTADO'] === 'NO_LEIDA' ? 'Sin leer' : 'Leída',
    ];
}, $notificaciones ?? []);
?>

<script>
    // Datos reales desde la BD, inyectados por PHP
    const NOTIFICACIONES_BD = <?= json_encode($notificacionesJS, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/LOGIN_ORIGINAL/public/js/administrador/notificaciones.js?v=<?= time(); ?>"></script>
</body>
</html>