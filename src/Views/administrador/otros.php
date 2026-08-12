<?php
$tabActiva = $_GET['tab'] ?? '';
if (!in_array($tabActiva, ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'])) {
    $tabActiva = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otros Catálogos — Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/otros.css">
    <script>
        (function() {
            var phpTab = "<?= htmlspecialchars($tabActiva, ENT_QUOTES) ?>";
            var urlParams = new URLSearchParams(window.location.search);
            var tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
            var savedTab = localStorage.getItem('activeOtrosTab');
            var tab = phpTab || (tabParam && ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'].includes(tabParam.toLowerCase()) ? tabParam.toLowerCase() : (savedTab || 'procedimientos'));
            if (!['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'].includes(tab)) tab = 'procedimientos';

            document.write('<style>.tab-content-otros { display: none !important; } #' + tab + 'Content { display: block !important; }</style>');
        })();
    </script>
</head>
<body>

<div class="menu-layout">

    <?php require_once __DIR__ . '/layouts/menu.php'; ?>

    <main class="menu-main-content">

        <?php require_once __DIR__ . '/layouts/header.php'; ?>

        <!-- CONTENEDOR PRINCIPAL BLANCO -->
        <div class="panel-otros">
            
            <!-- Encabezado interno -->
            <div class="header-otros">
                <div class="icon-circle-otros">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div class="titles-otros">
                    <h2>Otros</h2>
                    <p>Administración de catálogos del sistema</p>
                </div>
            </div>

            <!-- Pestañas (Tabs) -->
            <div class="tabs-otros" id="mainTabs">
                <span class="tab-btn-otros <?= ($tabActiva === 'procedimientos' || empty($tabActiva)) ? 'active' : '' ?>" data-tab="procedimientos">
                    <i class="fa-solid fa-tooth"></i> Procedimientos
                </span>
                <span class="tab-btn-otros <?= ($tabActiva === 'especialidades') ? 'active' : '' ?>" data-tab="especialidades">
                    <i class="fa-solid fa-star"></i> Especialidades
                </span>
                <span class="tab-btn-otros <?= ($tabActiva === 'eps') ? 'active' : '' ?>" data-tab="eps">
                    <i class="fa-solid fa-hospital"></i> EPS
                </span>
                <span class="tab-btn-otros <?= ($tabActiva === 'alergias') ? 'active' : '' ?>" data-tab="alergias">
                    <i class="fa-solid fa-shield-virus"></i> Alergias
                </span>
                <span class="tab-btn-otros <?= ($tabActiva === 'enfermedades') ? 'active' : '' ?>" data-tab="enfermedades">
                    <i class="fa-solid fa-notes-medical"></i> Enfermedades
                </span>
            </div>

            <!-- Contenido de Procedimientos -->
            <div class="tab-content-otros" id="procedimientosContent" style="<?= ($tabActiva === 'procedimientos' || empty($tabActiva)) ? 'display: block;' : 'display: none;' ?>">
                
                <!-- Barra de Acciones y Filtros -->
                <div class="toolbar-otros">
                    <button class="btn-primary-otros" id="btnNuevoProcedimiento">
                        <i class="fa-solid fa-plus"></i> Nuevo procedimiento
                    </button>
                    
                    <div class="toolbar-right-otros">
                        <div class="search-box-otros">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" placeholder="Buscar procedimiento...">
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="table-container-otros">
                    <table class="table-otros" id="procedimientosTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>DESCRIPCIÓN</th>
                                <th>DURACIÓN</th>
                                <th>PRECIO BASE</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($procedimientos)): ?>
                                <?php foreach ($procedimientos as $proc): ?>
                                <tr>
                                    <td class="col-id-otros"><?= htmlspecialchars($proc['id']) ?></td>
                                    <td>
                                        <div class="proc-name-otros"><?= htmlspecialchars($proc['nombre']) ?></div>
                                    </td>
                                    <td style="font-size: 13px; color: #475569;">
                                        <?= htmlspecialchars($proc['descripcion']) ?>
                                    </td>
                                    <td class="col-duracion-otros">
                                        <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($proc['duracion']) ?>
                                    </td>
                                    <td class="col-precio-otros"><?= htmlspecialchars($proc['precio']) ?></td>
                                    <td>
                                        <span class="badge-otros <?= htmlspecialchars($proc['css_estado']) ?>">
                                            <?= htmlspecialchars($proc['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions-group-otros">
                                            <button class="btn-action edit" onclick="abrirModalEditar(<?= $proc['id'] ?>, '<?= htmlspecialchars($proc['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($proc['descripcion'], ENT_QUOTES) ?>', <?= $proc['precio_num'] ?>, <?= $proc['duracion_num'] ?>, '<?= strtoupper($proc['estado']) ?>')"><i class="fa-solid fa-pencil"></i></button>
                                            <button class="btn-action delete" onclick="eliminarProcedimiento(<?= $proc['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">No hay procedimientos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- ==============================================
                 PESTAÑA EPS
            =============================================== -->
            <div class="tab-content-otros" id="epsContent" style="display: none;">
                <!-- Toolbar EPS -->
                <div class="toolbar-otros">
                    <button class="btn-primary-otros" id="btnNuevaEps">
                        <i class="fa-solid fa-plus"></i> Nueva EPS
                    </button>
                    
                    <div class="toolbar-right-otros">
                        <div class="search-box-otros">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Buscar EPS..." id="searchInputEps">
                        </div>
                    </div>
                </div>

                <!-- Tabla EPS -->
                <div class="table-container-otros">
                    <table class="table-otros" id="epsTable">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>NOMBRE</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($listaEps)): ?>
                                <?php foreach ($listaEps as $eps): ?>
                                <tr>
                                    <td class="col-id-otros">EPS-<?= str_pad(htmlspecialchars($eps['id']), 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><div class="proc-name-otros"><?= htmlspecialchars($eps['nombre']) ?></div></td>
                                    <td><span class="badge-otros <?= htmlspecialchars($eps['css_estado']) ?>"><?= htmlspecialchars($eps['estado']) ?></span></td>
                                    <td>
                                        <div class="actions-group-otros">
                                            <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEps('<?= htmlspecialchars($eps['id']) ?>', '<?= htmlspecialchars($eps['nombre'], ENT_QUOTES) ?>', '<?= strtoupper($eps['estado']) ?>')"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn-action delete" title="Eliminar" onclick="eliminarEps('<?= htmlspecialchars($eps['id']) ?>')"><i class="fa-regular fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">No hay EPS registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

            <!-- ==============================================
                 PESTAÑA ESPECIALIDADES
            =============================================== -->
            <div class="tab-content-otros" id="especialidadesContent" style="display: none;">
                <!-- Toolbar Especialidades -->
                <div class="toolbar-otros">
                    <button class="btn-primary-otros" id="btnNuevaEspecialidad">
                        <i class="fa-solid fa-plus"></i> Nueva especialidad
                    </button>
                    
                    <div class="toolbar-right-otros">
                        <div class="search-box-otros">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Buscar especialidad..." id="searchInputEspecialidad">
                        </div>
                    </div>
                </div>

                <!-- Tabla Especialidades -->
                <div class="table-container-otros">
                    <table class="table-otros" id="especialidadesTable">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>NOMBRE</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($especialidades)): ?>
                                <?php foreach ($especialidades as $esp): ?>
                                <tr>
                                    <td class="col-id-otros">ESP-<?= str_pad(htmlspecialchars($esp['id']), 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><div class="proc-name-otros"><?= htmlspecialchars($esp['nombre']) ?></div></td>
                                    <td><span class="badge-otros <?= htmlspecialchars($esp['css_estado']) ?>"><?= htmlspecialchars($esp['estado']) ?></span></td>
                                    <td>
                                        <div class="actions-group-otros">
                                            <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEspecialidad('<?= htmlspecialchars($esp['id']) ?>', '<?= htmlspecialchars($esp['nombre'], ENT_QUOTES) ?>', '<?= strtoupper($esp['estado']) ?>')"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn-action delete" title="Eliminar" onclick="eliminarEspecialidad('<?= htmlspecialchars($esp['id']) ?>')"><i class="fa-regular fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">No hay especialidades registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ==============================================
                 PESTAÑA ALERGIAS
            =============================================== -->
            <div class="tab-content-otros" id="alergiasContent" style="<?= ($tabActiva === 'alergias') ? 'display: block;' : 'display: none;' ?>">
                <!-- Toolbar Alergias -->
                <div class="toolbar-otros">
                    <button class="btn-primary-otros" id="btnNuevaAlergia">
                        <i class="fa-solid fa-plus"></i> Nueva alergia
                    </button>
                    
                    <div class="toolbar-right-otros">
                        <div class="search-box-otros">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Buscar alergia..." id="searchInputAlergia">
                        </div>
                    </div>
                </div>

                <!-- Tabla Alergias -->
                <div class="table-container-otros">
                    <table class="table-otros" id="alergiasTable">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>NOMBRE</th>
                                <th>DESCRIPCIÓN</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($alergias)): ?>
                                <?php foreach ($alergias as $alg): ?>
                                <tr>
                                    <td class="col-id-otros">ALG-<?= str_pad(htmlspecialchars($alg['id']), 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><div class="proc-name-otros"><?= htmlspecialchars($alg['nombre']) ?></div></td>
                                    <td style="font-size: 13px; color: #475569;"><?= htmlspecialchars($alg['descripcion'] ?? '—') ?></td>
                                    <td><span class="badge-otros <?= htmlspecialchars($alg['css_estado']) ?>"><?= htmlspecialchars($alg['estado']) ?></span></td>
                                    <td>
                                        <div class="actions-group-otros">
                                            <button class="btn-action edit" title="Editar" onclick="abrirModalEditarAlergia('<?= htmlspecialchars($alg['id']) ?>', '<?= htmlspecialchars($alg['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($alg['descripcion'] ?? '', ENT_QUOTES) ?>', '<?= strtoupper($alg['estado']) ?>')"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn-action delete" title="Eliminar" onclick="eliminarAlergia('<?= htmlspecialchars($alg['id']) ?>')"><i class="fa-regular fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">No hay alergias registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

            <!-- ==============================================
                 PESTAÑA ENFERMEDADES
            =============================================== -->
            <div class="tab-content-otros" id="enfermedadesContent" style="<?= ($tabActiva === 'enfermedades') ? 'display: block;' : 'display: none;' ?>">
                <!-- Toolbar Enfermedades -->
                <div class="toolbar-otros">
                    <button class="btn-primary-otros" id="btnNuevaEnfermedad">
                        <i class="fa-solid fa-plus"></i> Nueva enfermedad
                    </button>
                    
                    <div class="toolbar-right-otros">
                        <div class="search-box-otros">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Buscar enfermedad..." id="searchInputEnfermedad">
                        </div>
                    </div>
                </div>

                <!-- Tabla Enfermedades -->
                <div class="table-container-otros">
                    <table class="table-otros" id="enfermedadesTable">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>NOMBRE</th>
                                <th>DESCRIPCIÓN</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($enfermedades)): ?>
                                <?php foreach ($enfermedades as $enf): ?>
                                <tr>
                                    <td class="col-id-otros">ENF-<?= str_pad(htmlspecialchars($enf['id']), 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><div class="proc-name-otros"><?= htmlspecialchars($enf['nombre']) ?></div></td>
                                    <td style="font-size: 13px; color: #475569;"><?= htmlspecialchars($enf['descripcion'] ?? '—') ?></td>
                                    <td><span class="badge-otros <?= htmlspecialchars($enf['css_estado']) ?>"><?= htmlspecialchars($enf['estado']) ?></span></td>
                                    <td>
                                        <div class="actions-group-otros">
                                            <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEnfermedad('<?= htmlspecialchars($enf['id']) ?>', '<?= htmlspecialchars($enf['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($enf['descripcion'] ?? '', ENT_QUOTES) ?>', '<?= strtoupper($enf['estado']) ?>')"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn-action delete" title="Eliminar" onclick="eliminarEnfermedad('<?= htmlspecialchars($enf['id']) ?>')"><i class="fa-regular fa-trash-can"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">No hay enfermedades registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>

    </main>
</div>

<!-- Modal Nuevo Procedimiento (AHORA CON LOS CAMPOS COMPLETOS) -->
<div id="modalProcedimiento" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeModalProcedimiento">&times;</span>
        <h2 id="modalProcTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
            <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nuevo Procedimiento</span>
        </h2>
        <form id="formProcedimiento">
            <input type="hidden" id="formProcId" value="">
            <div class="form-group">
                <label>Nombre del Procedimiento</label>
                <input type="text" id="formProcNombre" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" id="formProcDesc" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Precio Base ($)</label>
                    <input type="number" id="formProcCosto" required>
                </div>
                <div class="form-group">
                    <label>Duración (Minutos)</label>
                    <input type="number" id="formProcTiempo" required>
                </div>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="formProcEstado" required>
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>
            <button type="submit" id="modalProcSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Guardar Procedimiento
            </button>
        </form>
    </div>
</div>

<!-- ==============================================
     MODAL NUEVA/EDITAR ESPECIALIDAD
=============================================== -->
<div id="modalEspecialidad" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <span class="close-modal" id="closeModalEspecialidad">&times;</span>
        <h2 id="modalEspTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
            <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Especialidad</span>
        </h2>
        <form id="formEspecialidad">
            <input type="hidden" id="formEspId" value="">
            <div class="form-group">
                <label>Nombre de la Especialidad</label>
                <input type="text" id="formEspNombre" required>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="formEspEstado" required>
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>
            <button type="submit" id="modalEspSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Guardar Especialidad
            </button>
        </form>
    </div>
</div>

<!-- ==============================================
     MODAL NUEVA/EDITAR EPS
=============================================== -->
<div id="modalEps" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <span class="close-modal" id="closeModalEps">&times;</span>
        <h2 id="modalEpsTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
            <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva EPS</span>
        </h2>
        <form id="formEps">
            <input type="hidden" id="formEpsId" value="">
            <div class="form-group">
                <label>Nombre de la EPS</label>
                <input type="text" id="formEpsNombre" required>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="formEpsEstado" required>
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>
            <button type="submit" id="modalEpsSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Guardar EPS
            </button>
        </form>
    </div>
</div>

<!-- ==============================================
     MODAL NUEVA/EDITAR ALERGIA
=============================================== -->
<div id="modalAlergia" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close-modal" id="closeModalAlergia">&times;</span>
        <h2 id="modalAlgTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
            <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Alergia</span>
        </h2>
        <form id="formAlergia">
            <input type="hidden" id="formAlgId" value="">
            <div class="form-group">
                <label>Nombre de la Alergia</label>
                <input type="text" id="formAlgNombre" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" id="formAlgDesc">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="formAlgEstado" required>
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>
            <button type="submit" id="modalAlgSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Guardar Alergia
            </button>
        </form>
    </div>
</div>

<!-- ==============================================
     MODAL NUEVA/EDITAR ENFERMEDAD
=============================================== -->
<div id="modalEnfermedad" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close-modal" id="closeModalEnfermedad">&times;</span>
        <h2 id="modalEnfTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
            <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Enfermedad</span>
        </h2>
        <form id="formEnfermedad">
            <input type="hidden" id="formEnfId" value="">
            <div class="form-group">
                <label>Nombre de la Enfermedad</label>
                <input type="text" id="formEnfNombre" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" id="formEnfDesc">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="formEnfEstado" required>
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>
            <button type="submit" id="modalEnfSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Guardar Enfermedad
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="/LOGIN_ORIGINAL/public/js/administrador/otros.js"></script>
</body>
</html>