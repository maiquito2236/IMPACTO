<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Odonto Estética - Historia Clínica</title>
    <link rel="stylesheet" href="../menu/menu.css">

    <link rel="stylesheet" href="styles.css" />

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

</head>

<body>
        <div class="menu-layout">
        <?php require_once "../menu/menu.php"; ?>
            <div class="main-content">
            <!-- PACIENTE -->
            <section class="patient-card">

                <div class="patient-info-block">

                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80"
                        alt="Paciente" class="patient-avatar" />

                    <div class="patient-details">
                        <h2>Maria Fernanda López, ID: 12345678</h2>
                        <p>Perfil: 16 años • Datos: Dr. Andrés Díaz</p>
                    </div>

                </div>

                <button class="btn-more-options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>

            </section>

            <!-- TABS -->
            <div class="tabs-container">

                <button class="tab-button active">
                    Historia Clínica
                </button>

            </div>

            <!-- ODONTOGRAMA -->
            <section class="dashboard-block odontogram-section">

                <div class="block-header">

                    <h3>2D odontograma adulto</h3>

                    <a href="../agenda/agenda.php" class="link-view-all">
                        Ver agenda completa
                    </a>

                </div>

                <div class="odontogram-graphic">

                    <!-- SUPERIOR -->
                    <div class="teeth-row superior">

                        <div class="tooth-item">
                            <span class="tooth-number">18</span>
                            <div class="tooth-shape molar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">17</span>
                            <div class="tooth-shape molar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">16</span>
                            <div class="tooth-shape molar red-status">
                                <i class="fa-solid fa-star"></i>
                            </div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">15</span>
                            <div class="tooth-shape premolar blue-status">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">14</span>
                            <div class="tooth-shape premolar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">13</span>
                            <div class="tooth-shape canino"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">12</span>
                            <div class="tooth-shape incisivo"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">11</span>
                            <div class="tooth-shape incisivo"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">21</span>
                            <div class="tooth-shape incisivo"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">22</span>
                            <div class="tooth-shape incisivo"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">23</span>
                            <div class="tooth-shape canino"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">24</span>
                            <div class="tooth-shape premolar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">25</span>
                            <div class="tooth-shape premolar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">26</span>
                            <div class="tooth-shape molar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">27</span>
                            <div class="tooth-shape molar"></div>
                        </div>

                        <div class="tooth-item">
                            <span class="tooth-number">28</span>
                            <div class="tooth-shape molar"></div>
                        </div>

                    </div>

                    <!-- INFERIOR -->
                    <div class="teeth-row inferior">

                        <div class="tooth-item">
                            <div class="tooth-shape molar"></div>
                            <span class="tooth-number">48</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape molar"></div>
                            <span class="tooth-number">47</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape molar crown-status">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                            <span class="tooth-number">46</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape premolar"></div>
                            <span class="tooth-number">45</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape premolar"></div>
                            <span class="tooth-number">44</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape canino"></div>
                            <span class="tooth-number">43</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape incisivo"></div>
                            <span class="tooth-number">42</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape incisivo"></div>
                            <span class="tooth-number">41</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape incisivo"></div>
                            <span class="tooth-number">31</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape incisivo"></div>
                            <span class="tooth-number">32</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape canino"></div>
                            <span class="tooth-number">33</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape premolar"></div>
                            <span class="tooth-number">34</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape premolar"></div>
                            <span class="tooth-number">35</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape molar"></div>
                            <span class="tooth-number">36</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape molar"></div>
                            <span class="tooth-number">37</span>
                        </div>

                        <div class="tooth-item">
                            <div class="tooth-shape molar"></div>
                            <span class="tooth-number">38</span>
                        </div>

                    </div>

                </div>

            </section>

            <!-- GRID -->
            <div class="two-columns-grid">

                <!-- TIMELINE -->
                <section class="dashboard-block evolution-section">

                    <div class="block-header">

                        <h3>Línea de Tiempo de Evolución</h3>

                        <div class="timeline-filters">

                            <button class="timeline-filter active" data-filter="day">
                                Día
                            </button>

                            <button class="timeline-filter" data-filter="month">
                                Mes
                            </button>

                            <button class="timeline-filter" data-filter="year">
                                Año
                            </button>

                        </div>

                    </div>

                    <div class="timeline" id="timelineContainer"></div>

                </section>

                <!-- RIGHT -->
                <div class="right-sub-column">

                    <!-- ARCHIVOS -->
                    <section class="dashboard-block files-section">

                        <div class="block-header">

                            <h3>Archivos y Pruebas</h3>

                            <select class="select-filter">
                                <option>Este mes</option>
                            </select>

                        </div>

                        <div class="files-grid">

                            <div class="file-card">

                                <div class="img-thumb">
                                    <img src="https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=150&q=80" />
                                </div>

                                <span class="file-name">
                                    Panorámica
                                </span>

                                <span class="file-date">
                                    15/05/2026
                                </span>

                            </div>

                            <div class="file-card">

                                <div class="img-thumb double">

                                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=80&q=80" />

                                    <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=80&q=80" />

                                </div>

                                <span class="file-name">
                                    Fotos clínicas
                                </span>

                            </div>

                            <div class="file-card">

                                <div class="pdf-thumb">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>

                                <span class="file-name">
                                    PDFs
                                </span>

                            </div>

                        </div>

                    </section>

                    <!-- ALERTAS -->
                    <section class="dashboard-block alerts-section">

                        <h3>Alertas Clínicas</h3>

                        <div class="alert-box">

                            <div class="alert-icon-wrapper">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>

                            <div class="alert-text">
                                <p>Alergia a medicamentos</p>
                            </div>

                            <a href="../agenda/agenda.php" class="link-action">
                                Revisar agenda
                            </a>

                        </div>

                    </section>

                </div>

            </div>
            </div>
        </main>
    </div>

    <!-- MODAL -->
    <div class="tooth-modal" id="toothModal">

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h2 id="modalToothTitle">Diente</h2>
                    <p>Información clínica detallada</p>
                </div>

                <button class="close-modal" id="closeModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            <div class="modal-body">

                <div class="info-card">
                    <span class="label">Nombre</span>
                    <h3 id="toothName"></h3>
                </div>

                <div class="info-grid">

                    <div class="info-box">
                        <span class="label">Estado</span>
                        <p id="toothStatus"></p>
                    </div>

                    <div class="info-box">
                        <span class="label">Tratamiento</span>
                        <p id="toothTreatment"></p>
                    </div>

                    <div class="info-box">
                        <span class="label">Doctor</span>
                        <p id="toothDoctor"></p>
                    </div>

                    <div class="info-box">
                        <span class="label">Última revisión</span>
                        <p id="toothDate"></p>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="js.js" ></script>
</body>

</html>