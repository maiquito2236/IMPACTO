<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../menu/global.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
<div class="menu-layout">
 
    <?php require_once "../menu/menu.php"; ?>
 
    <main class="menu-main-content">
 
        <!-- HEADER -->
        <header class="gu-header">
            <div class="gu-header-title">
                <h1>Gestión de Usuarios</h1>
                <p>Administración de pacientes, odontólogos y personal</p>
            </div>
            <div class="gu-header-actions">
                <button class="btn-primary" id="btnNuevoUsuario">
                    <i class="fa-solid fa-plus"></i> Nuevo Usuario
                </button>
            </div>
        </header>
 
        <!-- KPI -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon icon-blue"><i class="fa-regular fa-user"></i></div>
                <div class="kpi-info">
                    <h3>Usuarios Totales</h3>
                    <div class="number" id="kpi-totales">3</div>
                    <div class="trend green">+ 5 nuevos este mes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-green"><i class="fa-solid fa-hospital-user"></i></div>
                <div class="kpi-info">
                    <h3>Pacientes</h3>
                    <div class="number" id="kpi-pacientes">2</div>
                    <div class="trend green">+ 8 nuevos este mes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-purple"><i class="fa-solid fa-stethoscope"></i></div>
                <div class="kpi-info">
                    <h3>Odontólogos</h3>
                    <div class="number" id="kpi-odontologos">1</div>
                    <div class="trend purple">+ 1 nuevo este mes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-yellow"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="kpi-info">
                    <h3>Roles Configurados</h3>
                    <div class="number">6</div>
                    <div class="trend yellow">+ 1 nuevo este mes</div>
                </div>
            </div>
        </section>
 
        <!-- DASHBOARD -->
        <div class="dashboard-grid">
 
            <div class="left-col">
 
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Usuarios</h2>
                    </div>
                    <div class="table-controls">
                        <div class="tabs" id="tableTabs">
                            <span class="tab-btn active" data-filter="Todos">Todos</span>
                            <span class="tab-btn" data-filter="Paciente">Pacientes</span>
                            <span class="tab-btn" data-filter="Odontólogo">Odontólogos</span>
                            <span class="tab-btn" data-filter="Personal">Personal</span>
                        </div>
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" placeholder="Buscar usuario...">
                        </div>
                    </div>
                    <table id="usuariosTable">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Tipo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
 
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Estadísticas de Usuarios</h2>
                    </div>
                    <div class="stats-container">
                        <div class="donut-chart" id="donutChart">
                            <div class="donut-inner">
                                <h2 id="chartTotal">3</h2>
                                <span>Total</span>
                            </div>
                        </div>
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-label"><span class="dot green"></span> Pacientes</div>
                                <div class="legend-values"><span id="legPacientes">2</span></div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-label"><span class="dot purple"></span> Odontólogos</div>
                                <div class="legend-values"><span id="legOdontologos">1</span></div>
                            </div>
                        </div>
                    </div>
                </div>
 
            </div>
 
            <div class="right-col">
 
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Roles y Permisos</h2>
                    </div>
                    <div class="role-list">
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-icon bg-blue"><i class="fa-solid fa-shield"></i></span>
                                Administrador
                            </div>
                            <strong>1</strong>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-icon bg-purple"><i class="fa-solid fa-tooth"></i></span>
                                Odontólogo
                            </div>
                            <strong id="roleCountOdon">1</strong>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-icon bg-green"><i class="fa-solid fa-hospital-user"></i></span>
                                Paciente
                            </div>
                            <strong id="roleCountPac">2</strong>
                        </div>
                    </div>
                </div>
 
            </div>
        </div>
 
    </main>
</div>
 
<!-- MODAL NUEVO USUARIO -->
<div id="modalUsuario" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeModal">&times;</span>
        <h2>Nuevo Usuario</h2>
        <form id="formUsuario">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" id="formNombre" placeholder="Ej. María González" required>
            </div>
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="formEmail" placeholder="correo@ejemplo.com" required>
            </div>
            <div class="form-group">
                <label>Tipo de Usuario</label>
                <select id="formTipo">
                    <option value="Paciente">Paciente</option>
                    <option value="Odontólogo">Odontólogo</option>
                    <option value="Personal">Personal</option>
                </select>
            </div>
            <button type="submit" class="btn-primary btn-block">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Usuario
            </button>
        </form>
    </div>
</div>
 
<!-- MENÚ CONTEXTUAL -->
<div id="contextMenu" class="context-menu">
    <button onclick="eliminarUsuarioActual()">
        <i class="fa-solid fa-trash"></i> Eliminar usuario
    </button>
</div>
 
<script src="script.js"></script>
</body>
</html>
 