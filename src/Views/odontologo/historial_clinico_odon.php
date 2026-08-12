<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/LOGIN_ORIGINAL/">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Odonto Estética - Historia Clínica</title>
    <link rel="stylesheet" href="public/css/odontologo/menu.css">
    <link rel="stylesheet" href="public/css/odontologo/historial_clinico_odon.css?v=1.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>
        
        <div class="main-content">
            <?php 
                $pacienteIdReal = isset($_GET['paciente_id']) ? intval($_GET['paciente_id']) : 0; 
                $citaIdReal = isset($_GET['cita_id']) ? intval($_GET['cita_id']) : 0;
            ?>
            
            <?php if ($pacienteIdReal === 0): ?>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 70vh; text-align: center;">
                    <i class="fa-solid fa-user-doctor" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h2 style="color: #475569; font-size: 1.8rem; margin: 0 0 10px 0;">No se ha seleccionado ningún paciente</h2>
                    <p style="color: #94a3b8; font-size: 1.1rem; max-width: 400px;">Por favor, seleccione un paciente desde la agenda o desde el módulo de tratamientos para ver su historia clínica.</p>
                    <a href="index.php?action=odontologo/tratamientos" style="margin-top: 20px; background-color: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.3s;">Ir a Tratamientos</a>
                </div>
            <?php else: ?>

            <input type="hidden" id="pacienteIdHidden" value="<?php echo $pacienteIdReal; ?>">
            
            <!-- ENCABEZADO OCULTO (Solo visible en PDF e Impresión) -->
            <div id="print-header" style="display: none; text-align: center; margin-bottom: 30px; border-bottom: 3px solid #3b82f6; padding-bottom: 15px;">
                <img src="public/img/logo_odontologia.png" alt="Logo Odonto Estética" style="max-width: 200px; margin-bottom: 10px;">
                <h1 style="color: #1e293b; margin: 0; font-size: 1.8rem;">Historia Clínica Odontológica</h1>
                <p style="color: #64748b; margin: 5px 0 0 0;">Odonto Estética - Salud y Bienestar</p>
            </div>

            <!-- TARJETA DEL PACIENTE Y BOTONES -->
            <section class="patient-card" style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
                <div class="patient-info-block" style="display: flex; gap: 15px; align-items: center;">
                    <div id="avatarInitials" class="patient-avatar-initials" style="width: 65px; height: 65px; background-color: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.4rem; text-transform: uppercase; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        --
                    </div>
                    <div class="patient-details">
                        <h2 id="historial-nombre-paciente" style="margin: 0; color: #1e293b; font-size: 1.5rem;">Cargando datos del paciente...</h2>
                        <p id="historial-meta-paciente" style="margin: 5px 0 0 0; color: #64748b; font-size: 0.95rem;">Identificación: -- • Doctor Asignado: --</p>
                    </div>
                </div>
                
                <!-- PANEL DE BOTONES -->
                <div class="patient-actions" id="botones-acciones" style="display: flex; gap: 10px;">
                    <button id="btn-imprimir" style="background-color: #64748b; color: white; padding: 12px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.3s;">
                        Imprimir
                    </button>
                    
                    <button id="btn-exportar-pdf" style="background-color: #ef4444; color: white; padding: 12px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.3s;">
                        Exportar PDF
                    </button>

                    <button id="btn-guardar-enviar-historial" data-cita-id="<?php echo $citaIdReal; ?>" data-paciente-id="<?php echo $pacienteIdReal; ?>" 
                       style="background-color: #3b82f6; color: white; padding: 12px 24px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(59,130,246,0.3); transition: background 0.3s;">
                        Guardar historial clínico 
                    </button>
                </div>
            </section>

            <div class="tabs-container">
                <button class="tab-button active">Historia Clínica</button>
            </div>

            <section class="dashboard-block odontogram-section">
                <div class="block-header">
                    <h3>2D odontograma adulto</h3>
                    <a href="index.php?action=dashboard&modulo=agenda" class="link-view-all">Ver agenda completa</a>
                </div>

                <div class="odontogram-graphic">
                    <div class="teeth-row superior">
                        <?php foreach([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16] as $num) { ?>
                            <div class="tooth-item" data-tooth-number="<?= $num ?>">
                                <span class="tooth-number"><?= $num ?></span>
                                <div class="tooth-shape premolar" id="shape-<?= $num ?>"></div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="teeth-row inferior">
                        <?php foreach([17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32] as $num) { ?>
                            <div class="tooth-item" data-tooth-number="<?= $num ?>">
                                <div class="tooth-shape premolar" id="shape-<?= $num ?>"></div>
                                <span class="tooth-number"><?= $num ?></span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>

            <section class="dashboard-block timeline-section" style="margin-top: 25px; width: 100%;">
                <div class="block-header">
                    <h3>Historial de Evoluciones Clínicas</h3>
                </div>
                <div class="timeline-container" id="timeline-container">
                    <p style="color: #6b7280; text-align: center;">Cargando registros...</p>
                </div>
            </section>

            <div id="print-tooth-details" style="display: none; margin-top: 20px; width: 100%;">
                <h3 style="color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;">Detalle Clínico por Pieza Dental</h3>
                <div id="print-tooth-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                </div>
            </div>

            <div class="right-summary-panel" style="display: flex; flex-direction: row; gap: 20px; width: 100%; margin-bottom: 20px;">
                <div class="summary-card alert-card" style="flex: 1; background: #fff; padding: 20px; border-radius: 12px; border-left: 5px solid #ef4444; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <h3 style="margin-top:0; color: #ef4444; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                         Alertas y Antecedentes
                    </h3>
                    <ul style="list-style: none; padding: 0; margin: 10px 0 0 0; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem;">
                        <li><strong>Alergias:</strong> <span id="resumen-alergias" style="color: #ef4444; font-weight: 600;">Cargando...</span></li>
                        <li><strong>Enfermedades:</strong> <span id="resumen-enfermedades">Cargando...</span></li>
                        <li><strong>Medicamentos:</strong> <span id="resumen-medicamentos">Cargando...</span></li>
                    </ul>
                </div>

                <div class="summary-card appointments-card" style="flex: 1; background: #fff; padding: 20px; border-radius: 12px; border-left: 5px solid #3b82f6; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <h3 style="margin-top:0; color: #1e293b; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-calendar-days" style="color: #3b82f6;"></i> Control de Citas
                    </h3>
                    <ul style="list-style: none; padding: 0; margin: 10px 0 0 0; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem;">
                        <li><strong>Motivo:</strong> <span id="resumen-cita-hoy">Cargando...</span></li>
                        <li><strong>Próxima Cita:</strong> <span id="resumen-proxima-cita" style="font-weight: 600; color: #3b82f6;">Cargando...</span></li>
                    </ul>
                </div>
            </div>

            <!-- MODAL DETAILS -->
            <div class="tooth-modal" id="toothModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 id="modalToothTitle">Diente</h2>
                            <p>Información clínica detallada</p>
                        </div>
                        <button class="close-modal" id="closeModal"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="info-card"><span class="label">Nombre</span><h3 id="toothName"></h3></div>
                        <div class="info-grid">
                            <div class="info-box"><span class="label">Estado</span><p id="toothStatus"></p></div>
                            <div class="info-box"><span class="label">Tratamiento</span><p id="toothTreatment"></p></div>
                            <div class="info-box"><span class="label">Doctor</span><p id="toothDoctor"></p></div>
                            <div class="info-box"><span class="label">Última revisión</span><p id="toothDate"></p></div>
                        </div>
                        <div class="info-card" style="margin-top: 15px;"><span class="label">Notas / Diagnóstico</span><p id="toothNotes" style="font-size: 0.9rem; color: #475569; margin: 5px 0 0 0;"></p></div>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" defer></script>
    <script src="public/js/odontologo/historial_clinico_odon.js?v=<?= time() ?>" defer></script>
</body>
</html>     