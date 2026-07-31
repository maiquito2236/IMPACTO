<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/gestion_usuario.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>
 
    <div class="menu-layout">
    
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>
    
        <main class="menu-main-content">

            <?php require_once __DIR__ . '/layouts/header.php'; ?>
    
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
    
            <section class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon icon-blue"><i class="fa-regular fa-user"></i></div>
                    <div class="kpi-info">
                        <h3>Usuarios Totales</h3>
                        <div class="number" id="kpi-totales">0</div>
                        <div class="trend green">+ 5 nuevos este mes</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon icon-green"><i class="fa-solid fa-hospital-user"></i></div>
                    <div class="kpi-info">
                        <h3>Pacientes</h3>
                        <div class="number" id="kpi-pacientes">0</div>
                        <div class="trend green">+ 8 nuevos este mes</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon icon-purple"><i class="fa-solid fa-stethoscope"></i></div>
                    <div class="kpi-info">
                        <h3>Odontólogos</h3>
                        <div class="number" id="kpi-odontologos">0</div>
                        <div class="trend purple">+ 1 nuevo este mes</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon icon-yellow"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="kpi-info">
                        <h3>Roles Configurados</h3>
                        <div class="number">4</div>
                        <div class="trend yellow">Sistema actualizado</div>
                    </div>
                </div>
            </section>
    
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
                                <span class="tab-btn" data-filter="Personal">Personal / Admin</span>
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
                                    <th>Tipo / Rol</th>
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
                                    <h2 id="chartTotal">0</h2>
                                    <span>Total</span>
                                </div>
                            </div>
                            <div class="legend">
                                <div class="legend-item">
                                    <div class="legend-label"><span class="dot green"></span> Pacientes</div>
                                    <div class="legend-values"><span id="legPacientes">0</span></div>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-label"><span class="dot purple"></span> Odontólogos</div>
                                    <div class="legend-values"><span id="legOdontologos">0</span></div>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-label"><span class="dot blue"></span> Administradores</div>
                                    <div class="legend-values"><span id="legAdmins">0</span></div>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-label"><span class="dot yellow"></span> Administrador Jefe</div>
                                    <div class="legend-values"><span id="legJefes">0</span></div>
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
                                    <span class="role-icon bg-blue"><i class="fa-solid fa-crown"></i></span>
                                    Administrador Jefe
                                </div>
                                <strong id="roleCountJefe">0</strong>
                            </div>
                            <div class="role-item">
                                <div class="role-info">
                                    <span class="role-icon bg-blue"><i class="fa-solid fa-shield"></i></span>
                                    Administrador
                                </div>
                                <strong id="roleCountAdmin">0</strong>
                            </div>
                            <div class="role-item">
                                <div class="role-info">
                                    <span class="role-icon bg-purple"><i class="fa-solid fa-tooth"></i></span>
                                    Odontólogo
                                </div>
                                <strong id="roleCountOdon">0</strong>
                            </div>
                            <div class="role-item">
                                <div class="role-info">
                                    <span class="role-icon bg-green"><i class="fa-solid fa-hospital-user"></i></span>
                                    Paciente
                                </div>
                                <strong id="roleCountPac">0</strong>
                            </div>
                        </div>
                    </div>
    
                </div>
            </div>
    
        </main>
    </div>
    
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
                    <label>Teléfono (10 dígitos)</label>
                    <input type="text" id="formTelefono" placeholder="Ej. 3001234567" minlength="10" maxlength="10" required>
                </div>

                <div class="form-group">
                    <label>Rol en el Sistema *</label>
                    <select id="formRol" required>
                        <option value="">Seleccione un rol...</option>
                        <option value="1">Administrador</option>
                        <option value="2">Odontólogo</option>
                        <option value="3">Paciente</option>
                        <option value="4" disabled hidden>Administrador Jefe</option>
                    </select>
                </div>
                <div id="camposOdontologoCrear" style="display: none;">
                    <div class="form-group">
                        <label>Especialidades</label>
                        <div id="containerEspecialidadesCrear" class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px;">
                            <!-- Checkboxes se cargarán por JS -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Consultorio</label>
                        <select id="formConsultorioCrear">
                            <option value="">Seleccione un consultorio...</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-primary btn-block">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Usuario
                </button>
            </form>
        </div>
    </div>

    <div id="modalEditarRol" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModalEditar">&times;</span>
            <h2>Modificar Información de Usuario</h2>
            <form id="formEditarRol">
                <input type="hidden" id="editUsuarioId">
                
                <div class="form-group">
                    <label>Nombre del Usuario</label>
                    <input type="text" id="editNombreUsuario" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                </div>
                
                <div class="form-group">
                    <label>Asignar Rol en el Sistema</label>
                    <select id="formNuevoRol">
                        <option value="1">Administrador</option>
                        <option value="2">Odontólogo</option>
                        <option value="3">Paciente</option>
                        
                        <option value="4" disabled hidden>Administrador Jefe</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado de la Cuenta</label>
                    <select id="formNuevoEstado">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>

                <div id="camposOdontologo" style="display: none;">
                    <div class="form-group">
                        <label>Especialidades</label>
                        <div id="containerEspecialidades" class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px;">
                            <!-- Checkboxes se cargarán por JS -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Consultorio</label>
                        <select id="formConsultorio">
                            <option value="">Seleccione un consultorio...</option>
                        </select>
                    </div>
                </div>

                <div id="alertaAdminJefe" style="display: none; margin-bottom: 15px; padding: 12px; background-color: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 4px; font-size: 14px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Protección de Seguridad:</strong> Este usuario posee el cargo de <strong>Administrador Jefe</strong>. Sus privilegios, accesos y estado activo no pueden ser alterados.
                </div>

                <div id="alertaAdminNormal" style="display: none; margin-bottom: 15px; padding: 12px; background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #b45309; border-radius: 4px; font-size: 14px;">
                    <i class="fa-solid fa-lock"></i> <strong>Acción Restringida:</strong> No tienes permisos para modificar a otros administradores. Solo el Administrador Jefe puede hacerlo.
                </div>

                <button type="submit" class="btn-primary btn-block" id="btnGuardarRol">
                    <i class="fa-solid fa-rotate"></i> Actualizar Usuario
                </button>
            </form>
        </div>
    </div>
    
    <div id="contextMenu" class="context-menu"></div>
    <input type="hidden" id="rolSesionActual" value="<?= $_SESSION['usuario_rol'] ?? 1; ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/LOGIN_ORIGINAL/public/js/administrador/gestion_usuario.js?v=<?= time(); ?>"></script>
</body>
</html>