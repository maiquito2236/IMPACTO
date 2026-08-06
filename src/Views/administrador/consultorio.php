<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda y Citas — Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/consultorio.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>
 
<div class="menu-layout">
 
    <?php require_once __DIR__ . '/layouts/menu.php'; ?>
 
    <main class="menu-main-content">

        <?php require_once __DIR__ . '/layouts/header.php'; ?>

        <header class="page-header">
            <div class="page-header-text">
                <h1>Gestión de Consultorios</h1>
                <p>Administra, asigna y consulta los consultorios y su relación con los odontólogos.</p>
            </div>
            <button class="btn-primary-action" onclick="abrirModalCrear()">
                <i class="fa-solid fa-plus"></i> Nuevo Consultorio
            </button>
        </header>

        <!-- KPIs -->
        <section class="kpi-grid-c">
            <div class="kpi-card kpi-card-blue">
                <div class="kpi-icon-box kpi-icon-blue"><i class="fa-solid fa-building"></i></div>
                <div class="kpi-text">
                    <span class="kpi-label">Total Consultorios</span>
                    <span class="kpi-number">0</span>
                    <span class="kpi-sub">Consultorios registrados</span>
                </div>
            </div>
            <div class="kpi-card kpi-card-green">
                <div class="kpi-icon-box kpi-icon-green"><i class="fa-solid fa-user-check"></i></div>
                <div class="kpi-text">
                    <span class="kpi-label">Asignados</span>
                    <span class="kpi-number">0</span>
                    <span class="kpi-sub">Consultorios en uso</span>
                </div>
            </div>
            <div class="kpi-card kpi-card-orange">
                <div class="kpi-icon-box kpi-icon-orange"><i class="fa-solid fa-door-open"></i></div>
                <div class="kpi-text">
                    <span class="kpi-label">Disponibles</span>
                    <span class="kpi-number">0</span>
                    <span class="kpi-sub">Consultorios libres</span>
                </div>
            </div>
            <div class="kpi-card kpi-card-purple">
                <div class="kpi-icon-box kpi-icon-purple"><i class="fa-solid fa-user-doctor"></i></div>
                <div class="kpi-text">
                    <span class="kpi-label">Odontólogos</span>
                    <span class="kpi-number">0</span>
                    <span class="kpi-sub">Con consultorio asignado</span>
                </div>
            </div>
        </section>

        <!-- TABS -->
        <div class="tabs-bar-c">
            <span class="tab-btn-c active" data-tab-c="consulta">Consulta de Consultorios</span>
            <span class="tab-btn-c" data-tab-c="asignar">Asignar Consultorio</span>
            <span class="tab-btn-c" data-tab-c="historial">Historial de Asignaciones</span>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-grid-c">
            <!-- TAB: CONSULTA -->
            <div id="content-consulta" class="tab-content-c">
                <div class="panel-c">
                    <div class="panel-header-c">
                        <h2 class="panel-title-c">Lista de Consultorios</h2>
                        <div class="panel-search-c">
                            <input type="text" id="buscadorConsultorio" placeholder="Buscar consultorio..." class="input-search-c">
                            <i class="fa-solid fa-magnifying-glass search-icon-c"></i>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="table-c">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nombre</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Odontólogo Asignado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyConsultorios"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: ASIGNAR -->
            <div id="content-asignar" class="tab-content-c" style="display:none;">
                <div class="panel-c" style="max-width: 600px;">
                    <h2 class="panel-title-c" style="margin-bottom:20px;">Asignar Odontólogo a Consultorio</h2>
                    <div class="form-group-c">
                        <label>Seleccionar Consultorio Disponible</label>
                        <select id="selectConsultorioAsignar" class="input-c"></select>
                    </div>
                    <div class="form-group-c">
                        <label>Seleccionar Odontólogo (sin consultorio)</label>
                        <select id="selectOdontologoAsignar" class="input-c"></select>
                    </div>
                    <button id="btnEjecutarAsignacion" class="btn-primary-action" style="width:100%; justify-content:center; margin-top:10px;">
                        <i class="fa-solid fa-link"></i> Realizar Asignación
                    </button>
                </div>
            </div>

            <!-- TAB: HISTORIAL -->
            <div id="content-historial" class="tab-content-c" style="display:none;">
                <div class="panel-c">
                    <h2 class="panel-title-c" style="margin-bottom:16px;">Historial de Asignaciones</h2>
                    <div style="overflow-x: auto;">
                        <table class="table-c">
                            <thead>
                                <tr>
                                    <th>Consultorio</th>
                                    <th>Odontólogo</th>
                                    <th>Fecha Asignación</th>
                                    <th>Fecha Desasignación</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistorial"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    <!-- MODAL: DETALLE CONSULTORIO -->
    <div id="modalDetalle" class="modal-c">
        <div class="modal-content-c" style="width: 450px;">
            <span class="close-modal-c" id="closeModalDetalle">&times;</span>
            <div class="detalle-header-c" style="margin-top:10px;">
                <div class="detalle-icon-c"><i class="fa-solid fa-building"></i></div>
                <div>
                    <h3 id="detalleNombre">—</h3>
                    <p id="detalleUbicacion">—</p>
                </div>
                <span id="detalleEstadoBadge" class="badge badge-disponible" style="margin-left:auto;">—</span>
            </div>
            <div id="detalleDoctorInfo" class="detalle-body-c"></div>
            <div id="detalleBotones" class="detalle-actions-c"></div>
        </div>
    </div>

    <!-- MODAL: CREAR / EDITAR CONSULTORIO -->
    <div id="modalCrear" class="modal-c">
        <div class="modal-content-c">
            <span class="close-modal-c" id="closeModalCrear">&times;</span>
            <h2 id="modalCrearTitulo">Nuevo Consultorio</h2>
            <form id="formCrear">
                <input type="hidden" id="crearConsultorioId">
                <div class="form-group-c">
                    <label>Nombre del Consultorio *</label>
                    <input type="text" id="crearNombre" class="input-c" placeholder="Ej. Consultorio 7" required>
                </div>
                <div class="form-group-c">
                    <label>Ubicación</label>
                    <input type="text" id="crearUbicacion" class="input-c" placeholder="Ej. Piso 3">
                </div>
                <div class="form-group-c">
                    <label>Descripción</label>
                    <textarea id="crearDescripcion" class="input-c" rows="3" placeholder="Descripción del consultorio..."></textarea>
                </div>
                <button type="submit" class="btn-primary-action" style="width:100%; justify-content:center; margin-top:10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/LOGIN_ORIGINAL/public/js/administrador/consultorio.js"></script>
</body>
</html>