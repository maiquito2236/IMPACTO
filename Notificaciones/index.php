<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Notificaciones</title>

    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../menu/menu.css">

    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="menu-layout">
    <?php require_once "../menu/menu.php"; ?>

        <!-- WRAPPER -->

        <div class="content-wrapper">

            <!-- =========================================
                 FILTROS
            ========================================== -->
            <section class="main-content">
            <section class="filters-section">

                <div class="filter-group">
                    <label for="filter-tipo">Tipo</label>

                    <select id="filter-tipo" class="filter-input">
                        <option value="todos">Todos</option>
                        <option value="cita">Cita próxima</option>
                        <option value="tratamiento">Tratamiento completado</option>
                        <option value="pago">Pago recibido</option>
                        <option value="recordatorio">Recordatorio</option>
                    </select>
                </div>

                <div class="filter-group">

                    <label for="filter-categoria">Categoría</label>

                    <select id="filter-categoria" class="filter-input">
                        <option value="todas">Todas</option>
                        <option value="agenda">Agenda</option>
                        <option value="tratamientos">Tratamientos</option>
                        <option value="facturacion">Facturación</option>
                        <option value="recordatorios">Recordatorios</option>
                    </select>

                </div>

                <div class="filter-group">

                    <label for="filter-estado">Estado</label>

                    <select id="filter-estado" class="filter-input">
                        <option value="todos">Todos</option>
                        <option value="sin-leer">Sin leer</option>
                        <option value="leida">Leída</option>
                    </select>

                </div>

                <div class="filter-group">

                    <label>Fecha</label>

                    <input
                        type="date"
                        class="filter-input"
                    >

                </div>

                <button class="btn-clear" id="btn-reset-filters">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    Limpiar filtros
                </button>

            </section>

            <!-- =========================================
                 TARJETA TABLA
            ========================================== -->

            <section class="notifications-card">

                <!-- TABS -->

                <div class="tabs-header">

                    <div class="tabs-list">

                        <div class="tab-item active" data-tab="todas">
                            Todas (8)
                        </div>

                        <div class="tab-item" data-tab="sin-leer">
                            Sin leer (3)
                        </div>

                        <div class="tab-item" data-tab="archivadas">
                            Archivadas (0)
                        </div>

                    </div>

                    <button class="mark-read" id="btn-mark-all">

                        <i class="fa-solid fa-check-double"></i>

                        Marcar todas como leídas

                    </button>

                </div>

                <!-- TABLA -->

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>
                                <th>FECHA Y HORA</th>
                                <th>TIPO</th>
                                <th>CATEGORÍA</th>
                                <th>MENSAJE</th>
                                <th>PACIENTE / ORIGEN</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>

                        </thead>

                        <tbody id="notifications-tbody">

                            <!-- NOTIFICACIÓN 1 -->

                            <tr
                                class="row-unread"
                                data-tipo="cita"
                                data-categoria="agenda"
                                data-estado="sin-leer"
                            >

                                <td class="cell-fecha">
                                    <div class="dot"></div>
                                    16 May, 10:00 AM
                                </td>

                                <td class="cell-tipo">

                                    <div class="icon-box icon-agenda">
                                        <i class="fa-regular fa-calendar"></i>
                                    </div>

                                    Cita próxima

                                </td>

                                <td>
                                    <span class="badge badge-agenda">
                                        Agenda
                                    </span>
                                </td>

                                <td>
                                    <div class="msg-text">
                                        Tienes una cita con María Fernanda López mañana a las 10:30 AM.
                                    </div>
                                </td>

                                <td>María F. López</td>

                                <td>
                                    <span class="badge badge-status-unread">
                                        Sin leer
                                    </span>
                                </td>

                                <td>
                                    <button class="btn-more">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                </td>

                            </tr>

                            <!-- NOTIFICACIÓN 2 -->

                            <tr
                                class="row-unread"
                                data-tipo="tratamiento"
                                data-categoria="tratamientos"
                                data-estado="sin-leer"
                            >

                                <td class="cell-fecha">
                                    <div class="dot"></div>
                                    15 May, 3:45 PM
                                </td>

                                <td class="cell-tipo">

                                    <div class="icon-box icon-tratamientos">
                                        <i class="fa-solid fa-tooth"></i>
                                    </div>

                                    Tratamiento completado

                                </td>

                                <td>
                                    <span class="badge badge-tratamientos">
                                        Tratamientos
                                    </span>
                                </td>

                                <td>
                                    <div class="msg-text">
                                        Has completado el tratamiento de limpieza dental para Ana Sofía Martínez.
                                    </div>
                                </td>

                                <td>Ana Sofía Martínez</td>

                                <td>
                                    <span class="badge badge-status-unread">
                                        Sin leer
                                    </span>
                                </td>

                                <td>
                                    <button class="btn-more">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                </td>

                            </tr>

                            <!-- NOTIFICACIÓN 3 -->

                            <tr
                                data-tipo="pago"
                                data-categoria="facturacion"
                                data-estado="leida"
                            >

                                <td class="cell-fecha">
                                    <div class="dot"></div>
                                    15 May, 11:20 AM
                                </td>

                                <td class="cell-tipo">

                                    <div class="icon-box icon-facturacion">
                                        <i class="fa-solid fa-dollar-sign"></i>
                                    </div>

                                    Pago recibido

                                </td>

                                <td>
                                    <span class="badge badge-facturacion">
                                        Facturación
                                    </span>
                                </td>

                                <td>
                                    <div class="msg-text">
                                        Se ha registrado un pago de $120.000 de Juan Camilo Ramírez.
                                    </div>
                                </td>

                                <td>Juan Camilo Ramírez</td>

                                <td>
                                    <span class="badge badge-status-read">
                                        Leída
                                    </span>
                                </td>

                                <td>
                                    <button class="btn-more">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- PAGINACIÓN -->

                <div class="pagination">

                    <span class="pagination-info">
                        Mostrando 1 a 3 de 8 resultados
                    </span>

                    <div class="pagination-controls">

                        <button class="page-btn">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <button class="page-btn active">
                            1
                        </button>

                        <button class="page-btn">
                            2
                        </button>

                        <button class="page-btn">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>

                        <select class="per-page-select">
                            <option>10 por página</option>
                            <option>20 por página</option>
                        </select>

                    </div>

                </div>

            </section>
        </section>
        </div>

    </main>
    </div>
    <!-- JS -->
    <script src="script.js"></script>

</body>
</html>