<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Gestión de Tratamientos</title>

    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="styles.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS DEL MENÚ -->
    <link rel="stylesheet" href="../menu/menu.css">
</head>

<body>
    <div class="menu-layout">
    <!-- MENÚ -->
    <?php require_once "../menu/menu.php"; ?>

        <div class="main-content">
        <section class="content-layout">

            <!-- TABLA -->
            <div class="data-card table-section">

                <div class="table-header-actions">

                    <h3>Catálogo de Tratamientos</h3>

                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            id="searchTreatment"
                            type="text"
                            placeholder="Buscar por tratamiento...">
                    </div>
            <button
            id="openModalBtn"
            class="btn-new-treatment">

            <i class="fa-solid fa-plus"></i>
            Nuevo

        </button>
                </div>
        

                <table class="treatment-table">

                    <thead>
                        <tr>
                            <th>Tratamiento</th>
                            <th>Categoría</th>
                            <th>Tiempo Estándar</th>
                            <th>Costo Base ($)</th>
                            <th>Visibilidad</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>
                                <i class="fa-solid fa-circle-plus icon-row-add"></i>
                                Limpieza Dental
                            </td>

                            <td class="cat-tag">Preventivo</td>
                            <td>15m</td>
                            <td class="price-lbl">$15.00</td>

                            <td>
                                <label class="switch-toggle">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </td>

                            <td>
                                <i class="fa-solid fa-ellipsis-vertical action-dots"></i>
                            </td>
                        </tr>

                        <tr class="row-highlighted">

                            <td>
                                <i class="fa-solid fa-circle-plus icon-row-add"></i>
                                Ajuste de Ortodoncia
                            </td>

                            <td class="cat-tag">Restaurador</td>
                            <td>17m</td>
                            <td class="price-lbl">$10.00</td>

                            <td>
                                <label class="switch-toggle">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </td>

                            <td>
                                <i class="fa-solid fa-ellipsis-vertical action-dots"></i>
                            </td>

                        </tr>

                        <tr>

                            <td>
                                <i class="fa-solid fa-circle-plus icon-row-add"></i>
                                Obturación Resina
                            </td>

                            <td class="cat-tag">Ortodoncia</td>
                            <td>30m</td>
                            <td class="price-lbl">$10.00</td>

                            <td>
                                <label class="switch-toggle">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </td>

                            <td>
                                <i class="fa-solid fa-ellipsis-vertical action-dots"></i>
                            </td>

                        </tr>

                        <tr>

                            <td>
                                <i class="fa-solid fa-circle-plus icon-row-add"></i>
                                Endodoncia
                            </td>

                            <td class="cat-tag">Preventivo</td>
                            <td>15m</td>
                            <td class="price-lbl">$30.00</td>

                            <td>
                                <label class="switch-toggle">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </td>

                            <td>
                                <i class="fa-solid fa-ellipsis-vertical action-dots"></i>
                            </td>

                        </tr>

                    </tbody>

                </table>

                <!-- PAGINACIÓN -->
                <div class="pagination">

                    <button class="pag-btn">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <span class="pag-num active">1</span>
                    <span class="pag-num">2</span>

                    <button class="pag-btn">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                </div>

            </div>

            <!-- SIDEBAR -->
            <div class="sidebar-metrics">

                <!-- MÉTRICAS -->
                <div class="data-card metric-summary">

                    <div class="metric-item">
                        <span class="m-title">Total Tratamientos</span>
                        <span class="m-val">45</span>
                    </div>

                    <div class="metric-item">
                        <span class="m-title">Más Populares:</span>
                        <span class="m-val-sub">Limpieza</span>
                    </div>

                    <div class="metric-item">
                        <span class="m-title">
                            Tratamientos Pendientes de Actualización
                        </span>

                        <span class="m-val-alert">(3)</span>
                    </div>

                </div>

                <!-- GRÁFICA -->
                <div class="data-card chart-box">

                    <h4>Resumen de Uso de Tratamientos</h4>

                    <div class="donut-chart-simulation">

                        <div class="donut-hole">
                            <strong>45</strong>
                            <span>Activos</span>
                        </div>

                    </div>

                    <ul class="chart-legend-list">

                        <li>
                            <span class="dot orto"></span>
                            Ortodoncia
                            <span class="pct">20%</span>
                        </li>

                        <li>
                            <span class="dot endo"></span>
                            Endodoncia
                            <span class="pct">15%</span>
                        </li>

                        <li>
                            <span class="dot limp"></span>
                            Limpieza
                            <span class="pct">40%</span>
                        </li>

                        <li>
                            <span class="dot imp"></span>
                            Implantes
                            <span class="pct">15%</span>
                        </li>

                        <li>
                            <span class="dot otr"></span>
                            Otros
                            <span class="pct">5%</span>
                        </li>

                    </ul>

                </div>

            </div>

        </section>
    </div>

<!-- MODAL -->
<div id="modal-nuevo-procedimiento" class="modal-backdrop">

    <div class="modal-window">

        <div class="modal-top-bar">

            <h3>Registrar Nuevo Procedimiento del Día</h3>

            <a href="#" class="close-modal-x">
                &times;
            </a>

        </div>

        <div class="modal-body-form">

            <div class="form-title">

                <h4>Información del procedimiento</h4>

                <p>
                    Registra los datos del tratamiento realizado al paciente
                </p>

            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>Paciente</label>

                    <input
                        id="patientInput"
                        type="text"
                        placeholder="Buscar paciente..."
                        class="input-field">

                </div>

                <div class="form-group">

                    <label>Tipo de Tratamiento</label>

                    <select id="treatmentType" class="input-field">

                        <option value="">Seleccionar</option>
                        <option value="Limpieza Dental">Limpieza Dental</option>
                        <option value="Endodoncia">Endodoncia</option>
                        <option value="Ortodoncia">Ortodoncia</option>
                        <option value="Exodoncia">Exodoncia</option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Fecha</label>

                    <input
                        id="dateInput"
                        type="date"
                        class="input-field">

                </div>

                <div class="form-group">

                    <label>Costo</label>

                    <input
                        id="costInput"
                        type="number"
                        placeholder="Ej: 15000"
                        class="input-field">

                </div>

                <div class="form-group full-width">

                    <label>Duración</label>

                    <input
                        id="timeInput"
                        type="text"
                        placeholder="Ej: 1h 30m"
                        class="input-field">

                </div>

            </div>
            <div class="modal-actions">

    <button
        id="saveTreatmentBtn"
        class="btn-save">

        Guardar

    </button>

    <button
        id="closeModalBtn"
        class="btn-cancel">

        Cancelar

    </button>

</div>
        </div>

    </div>

</div>
</div>
<!-- JS -->
<script src="js.js"></script>

</body>
</html>