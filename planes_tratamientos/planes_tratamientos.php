<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Planes de Tratamiento</title>

    <link rel="stylesheet" href="../menu/menu.css">
    <link rel="stylesheet" href="styles.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="menu-layout">

    <?php require_once "../menu/menu.php"; ?>

    <main class="main-content-area">

        

        <h2 class="section-dashboard-title">
            Planes de Tratamiento
        </h2>

        <!-- GRID -->
        <div class="workspace-grid">

            <!-- ODONTOGRAMA -->
            <section class="ui-panel panel-odontogram">

                <h3>Paciente María Fernanda López</h3>

                <div class="odontogram-visualizer-box">

                    <div class="dental-arch arch-upper">

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">
                            1
                            <i class="fa-solid fa-circle-check check-overlay"></i>
                        </a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">2</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">3</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">4</a>
                       <a href="#modal-registrar-plan"
class="tooth-item tooth-select">5</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">6</a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">
                            7
                            <i class="fa-solid fa-crown crown-overlay"></i>
                        </a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">8</a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">9</a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">10</a>

                    </div>

                    <div class="jaw-divider-line"></div>

                    <div class="dental-arch arch-lower">

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">37</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">38</a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">35
                            <i class="fa-solid fa-screwdriver implant-overlay"></i>
                        </a>

                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">36</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">25</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">23</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">20</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">21</a>
                        <a href="#modal-registrar-plan"
class="tooth-item tooth-select">22</a>

                    </div>

                </div>

                <div class="odontogram-utility-bar">

                    <button class="btn-utility" id="resetTeeth">
                        <i class="fa-solid fa-rotate"></i>
                    </button>

                </div>

            </section>

            <!-- COLUMNA DERECHA -->
            <div class="metrics-and-phases-column">

                <!-- PLAN -->
                <section class="ui-panel phase-tracking-panel">

                    <div class="panel-inner-header">

                        <div class="title-with-status">
                            <h4>Plan de Ortodoncia - Fase 1</h4>
                            <p>Control y seguimiento</p>
                        </div>

                        <span class="badge-status-active">
                            Activo
                        </span>

                    </div>

                    <div class="sub-tab-bar">
                        <span class="tab-link active">
                            Plan de Fase
                        </span>
                    </div>

                    <div class="phases-table-list">

                        <div class="table-row-item">
                            <span class="row-cell-name">
                                Fase 1: Limpieza Dental
                            </span>

                            <span class="tag-phase-state status-done">
                                Hecho
                            </span>

                            <span class="row-cell-price">
                                $15.00
                            </span>
                        </div>

                        <div class="table-row-item">

                            <span class="row-cell-name">
                                Fase 2: Montaje Inferior
                            </span>

                            <span class="tag-phase-state status-pending">
                                Pendiente
                            </span>

                            <span class="row-cell-price">
                                $350.00
                            </span>

                        </div>

                        <div class="table-row-item">

                            <span class="row-cell-name">
                                Fase 3: Ajustes
                            </span>

                            <span class="tag-phase-state status-pending">
                                Pendiente
                            </span>

                            <span class="row-cell-price">
                                $100.00
                            </span>

                        </div>

                    </div>

                </section>

                <!-- PRESUPUESTO -->
                <section class="ui-panel budget-summary-panel">

                    <h5>Presupuesto y Pago</h5>

                    <div class="total-amount-lbl">
                        Total: $350.00
                    </div>

                    <div class="acceptance-pill">
                        <i class="fa-solid fa-circle-check"></i>
                        Aceptado por María F.
                    </div>

                    <div class="mini-timeline-chart">

                        <div class="chart-row">
                            <span>Mayo</span>
                            <div class="bar-fill blue-fill"></div>
                        </div>

                        <div class="chart-row">
                            <span>Marzo</span>
                            <div class="bar-fill green-fill"></div>
                        </div>

                        <div class="chart-row">
                            <span>Abril</span>
                            <div class="bar-fill blue-fill"></div>
                        </div>

                    </div>

                </section>

                <!-- PACIENTES -->
                <div class="pending-plans-row-wrapper">

                    <h6>Planes de Tratamiento Pendientes</h6>

                    <div class="pending-cards-container">

                        <div class="mini-pending-card clear-blue patient-selector">

                            <img
                            src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=60&q=80"
                            alt="Paciente">

                            <div class="p-card-info">
                                <strong>María Fernanda López</strong>
                                <span>Endodoncia - Juan G.</span>
                            </div>

                        </div>

                        <div class="mini-pending-card clear-yellow patient-selector">

                            <img
                            src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=60&q=80"
                            alt="Paciente">

                            <div class="p-card-info">
                                <strong>Diego Ruiz</strong>
                                <span>Ortodoncia - Juan G.</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

<!-- MODAL -->
<div id="modal-registrar-plan" class="modal-overlay-backdrop">

    <div class="modal-dialog-box">

        <div class="modal-header-bar">

            <h3>Registrar Nuevo Plan</h3>

            <a href="#" class="btn-close-modal">
                &times;
            </a>

        </div>

        <div class="modal-form-content-split">
            <div class="selected-tooth-preview">

    <img
    src="https://cdn-icons-png.flaticon.com/512/2966/2966486.png"
    alt="Diente"
    id="toothImage">

    <h4 id="selectedTooth">
        Selecciona una pieza dental
    </h4>

</div>
            <div class="modal-right-inputs-pane">

                <div class="form-group-field">

                    <label>Paciente</label>

                    <input
type="text"
class="form-control"
id="patientInput"
value="María Fernanda López">

                </div>
                <label>Tratamiento</label>
                <div class="fast-treatments-selector-box">
                        <h5>Añadir Tratamientos Rápido</h5>
                        <div class="checkbox-list-container">
                            <label class="custom-checkbox-row">
                                <input type="checkbox"> <span class="checkmark"></span> Limpieza Dental
                            </label>
                            <label class="custom-checkbox-row">
                                <input type="checkbox"> <span class="checkmark"></span> Endodoncia Pieza [ ]
                            </label>
                            <label class="custom-checkbox-row">
                                <input type="checkbox"> <span class="checkmark"></span> Corona de Porcelana Pieza [ ]
                            </label>
                            <label class="custom-checkbox-row">
                                <input type="checkbox"> <span class="checkmark"></span> Implante Dental Pieza [ ]
                            </label>
                        </div>
                    </div>

                <div class="form-grid-2-col">

                    <div class="form-group-field">

                        <label>Fecha</label>

                        <input
type="date"
class="form-control"
id="dateInput">

                    </div>

                    <div class="form-group-field">

                        <label>Especialista</label>

                        <input
                        type="text"
                        class="form-control"
                        placeholder="Nombre especialista">

                    </div>

                </div>

                <div class="form-group-field">

                    <label>Notas</label>

                    <textarea
                    class="form-control textarea-custom"
                    rows="3"></textarea>

                </div>

            </div>

        </div>

        <div class="modal-footer-bar">

            <a href="#" class="btn-secondary">
                Cancelar
            </a>

            <button
            type="button"
            class="btn-primary-submit"
            id="saveTreatment">

                Guardar

            </button>

        </div>

    </div>

</div>

<!-- MENSAJE -->
<div class="success-message" id="successMessage">

    <i class="fa-solid fa-circle-check"></i>

    Tratamiento guardado exitosamente

</div>

<script src="js.js"></script>

</body>
</html>