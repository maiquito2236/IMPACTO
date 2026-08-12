<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Dashboard Pacientes</title>
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/menu.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/pacientes_odon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    
    <div class="menu-layout">
        
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>
        
        <main class="main-content">
            
            <!-- BARRA DE FILTROS -->
            <section class="filters-bar">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="buscar" placeholder="Buscar paciente por nombre o documento...">
                </div>

                <select id="filtroEstado">
                    <option value="todos">Todos los estados</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </section>

            <!-- CONTENIDO PRINCIPAL: TABLA Y RESUMEN -->
            <section class="grid-layout">
                
                <div class="table-section">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th> 
                                <th>Documento</th>
                                <th>Correo Electrónico</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla">
                            <tr>
                                <td colspan="6" style="text-align:center; padding:20px; color:#777;">
                                    Cargando registros médicos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- KPIs DE RESUMEN -->
                <aside class="summary-section">
                    <h3>Resumen de Pacientes</h3>
                    
                    <div class="summary-card">
                        <div class="icon-box blue"><i class="fa-solid fa-user-group"></i></div>
                        <div class="summary-data">
                            <span>Total Pacientes</span>
                            <h2 id="total">0</h2>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="icon-box green"><i class="fa-solid fa-user-check"></i></div>
                        <div class="summary-data">
                            <span>Pacientes Activos</span>
                            <h2 id="activos">0</h2>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="icon-box purple"><i class="fa-solid fa-user-xmark"></i></div>
                        <div class="summary-data">
                            <span>Pacientes Inactivos</span>
                            <h2 id="inactivos">0</h2>
                        </div>
                    </div>
                </aside>
            </section>
            
        </main>
    </div>

    <!-- MODAL DE DETALLES DEL PACIENTE -->
    <div id="ventanaModal" class="modal-overlay" style="display:none;">
        <div class="modal-card">
            <button class="close-btn" onclick="document.getElementById('ventanaModal').style.display='none'">×</button>
            <div class="modal-header">
                <h3>Información del Paciente</h3>
            </div>
            <div class="modal-body">
                <div class="avatar-big" id="modalIniciales"></div>
                <h2 id="modalNombre"></h2>
                <p id="modalDocumento" class="text-muted"></p>
                <hr>
                <div class="info-grid">
                    <div><span>Correo:</span> <p id="modalEmail"></p></div>
                    <div><span>Teléfono:</span> <p id="modalTelefono"></p></div>
                </div>
            </div>
        </div>
    </div>

    <script src="/LOGIN_ORIGINAL/public/js/odontologo/pacientes_odon.js" defer></script>
</body>
</html>