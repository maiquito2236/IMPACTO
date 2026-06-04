<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Dashboard Pacientes</title>
    <link rel="stylesheet" href="../menu/menu.css">

    <!-- CSS -->
    <link rel="stylesheet" href="styles.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <div class="menu-layout">
    <?php require_once "../menu/menu.php"; ?>
        <!-- FILTROS -->
            <main class="main-content">
        <div class="filters-bar">

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="buscar"
                    placeholder="Buscar paciente..."
                >
            </div>

            <select id="filtroEstado">
                <option value="Todos">Todos</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>

            <button class="btn-nuevo-paciente" id="nuevo">
                <i class="fa-solid fa-plus"></i>
                Nuevo Paciente
            </button>

        </div>

        <!-- GRID -->

        <div class="grid-layout">

            <!-- TABLA -->

            <div class="table-section">

                <table class="data-table">

                    <thead>

                        <tr>
                            <th>Paciente</th>
                            <th>Documento</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>

                    </thead>

                    <tbody id="tabla"></tbody>

                </table>

            </div>

            <!-- RESUMEN -->

            <aside class="summary-section">

                <h3>Resumen de Pacientes</h3>

                <div class="summary-card">

                    <div class="icon-box blue">
                        <i class="fa-solid fa-user-group"></i>
                    </div>

                    <div class="summary-data">
                        <span>Total Pacientes</span>
                        <h2 id="total">0</h2>
                    </div>

                </div>

                <div class="summary-card">

                    <div class="icon-box green">
                        <i class="fa-solid fa-user-check"></i>
                    </div>

                    <div class="summary-data">
                        <span>Pacientes Activos</span>
                        <h2 id="activos">0</h2>
                    </div>

                </div>

                <div class="summary-card">

                    <div class="icon-box purple">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>

                    <div class="summary-data">
                        <span>Pacientes Inactivos</span>
                        <h2 id="inactivos">0</h2>
                    </div>

                </div>

            </aside>

        </div>

    </main>
    </div>
    <script src="script.js"></script>

</body>
</html>