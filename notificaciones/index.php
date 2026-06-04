<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../menu/global.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
<div class="menu-layout">
 
    <?php require_once "../menu/menu.php" ?>
 
    <main class="menu-main-content">
 
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
                        <span class="bell-badge" id="bell-badge">3</span>
                    </button>
                </div>
                <div class="avatar-wrap">
                    <img src="https://i.pravatar.cc/42?img=12" alt="Avatar" class="noti-avatar">
                    <div class="avatar-status"></div>
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
                        <option value="Cita próxima">Cita próxima</option>
                        <option value="Tratamiento completado">Tratamiento completado</option>
                        <option value="Pago recibido">Pago recibido</option>
                        <option value="Recordatorio">Recordatorio</option>
                        <option value="Nuevo paciente">Nuevo paciente</option>
                        <option value="Plan de tratamiento creado">Plan de tratamiento</option>
                        <option value="Documento subido">Documento subido</option>
                        <option value="Actualización del sistema">Sistema</option>
                    </select>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
            <div class="filter-group">
                <label>Categoría</label>
                <div class="select-wrap">
                    <select id="filtro-categoria">
                        <option value="">Todas</option>
                        <option value="Agenda">Agenda</option>
                        <option value="Tratamientos">Tratamientos</option>
                        <option value="Facturación">Facturación</option>
                        <option value="Recordatorios">Recordatorios</option>
                        <option value="Pacientes">Pacientes</option>
                        <option value="Planes">Planes</option>
                        <option value="Historia Clínica">Historia Clínica</option>
                        <option value="Sistema">Sistema</option>
                    </select>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
            <div class="filter-group">
                <label>Estado</label>
                <div class="select-wrap">
                    <select id="filtro-estado">
                        <option value="">Todos</option>
                        <option value="Sin leer">Sin leer</option>
                        <option value="Leída">Leída</option>
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
                    <button class="tab active" data-tab="todas">Todas <span class="tab-count" id="count-todas">24</span></button>
                    <button class="tab" data-tab="sin-leer">Sin leer <span class="tab-count sin-leer-count" id="count-sin-leer">3</span></button>
                    <button class="tab" data-tab="archivadas">Archivadas <span class="tab-count" id="count-archivadas">8</span></button>
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
                            <th>CATEGORÍA</th>
                            <th>MENSAJE</th>
                            <th>PACIENTE / ORIGEN</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="noti-tbody"></tbody>
                </table>
                <div id="empty-state" class="empty-state" style="display:none;">
                    <i class="fa-regular fa-bell-slash"></i>
                    <p>No hay notificaciones que coincidan con los filtros.</p>
                    <button class="btn-limpiar" id="btn-limpiar2"><i class="fa-solid fa-rotate-left"></i> Restablecer filtros</button>
                </div>
            </div>
 
            <!-- PAGINACIÓN -->
            <div class="pagination-bar">
                <span id="pag-info">Mostrando 1 a 8 de 24 resultados</span>
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
                <button class="bulk-btn" id="bulk-leer"><i class="fa-regular fa-circle-check"></i> Marcar como leídas</button>
                <button class="bulk-btn" id="bulk-archivar"><i class="fa-regular fa-folder"></i> Archivar</button>
                <button class="bulk-btn danger" id="bulk-eliminar"><i class="fa-regular fa-trash-can"></i> Eliminar</button>
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
            <button class="modal-btn-sec" id="modal-archivar">Archivar</button>
            <button class="modal-btn-pri" id="modal-accion">Ver detalle</button>
        </div>
    </div>
</div>
 
<!-- TOAST -->
<div class="toast" id="toast"></div>
 
<script src="script.js"></script>
</body>
</html>
 