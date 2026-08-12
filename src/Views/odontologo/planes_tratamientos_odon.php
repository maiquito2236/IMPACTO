<?php
// RESPALDO DE SEGURIDAD: Si por alguna razón no se pasó por el controlador, inicializa variables vacías
if (!isset($paciente_nombre)) $paciente_nombre = "Seleccione Paciente";
if (!isset($id_paciente)) $id_paciente = "";
if (!isset($documento)) $documento = "";
if (!isset($tratamiento_motivo)) $tratamiento_motivo = "Ninguno asignado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/LOGIN_ORIGINAL/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Planes de Tratamiento</title>

    <link rel="stylesheet" href="public/css/odontologo/menu.css">
    <link rel="stylesheet" href="public/css/odontologo/planes_tratamientos_odon.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>

<div class="menu-layout">
    <?php require_once __DIR__ . '/layouts/menu.php'; ?>

    <main class="main-content-area">
        <h2 class="section-dashboard-title">
            Planes de Tratamiento
        </h2>

<div class="patient-banner-container">
    <div class="patient-banner-left">
        <div class="patient-avatar-wrapper">
            <div class="patient-avatar-initials" id="avatarInitials">
                <?php 
                    if (!empty($paciente_nombre) && $paciente_nombre !== 'Seleccione Paciente') {
                        $partes = explode(' ', trim($paciente_nombre));
                        echo htmlspecialchars(strtoupper(isset($partes[1]) ? $partes[0][0].$partes[1][0] : substr($partes[0], 0, 2)));
                    } else {
                        echo "--";
                    }
                ?>
            </div>
        </div>
        
        <div class="patient-info-text">
            <h3 class="patient-name-title" id="bannerPatientName">
                <?php echo htmlspecialchars($paciente_nombre ?? 'Seleccione Paciente'); ?>
            </h3>
            <p class="patient-meta-sub" id="bannerPatientMeta">
                <?php if (!empty($id_paciente)): ?>
                    <strong>Doc:</strong> <?php echo htmlspecialchars($documento ?? 'N/A'); ?> &bull; 
                    <strong>Motivo:</strong> <?php echo htmlspecialchars($tratamiento_motivo ?? 'Consulta'); ?>
                <?php else: ?>
                    Seleccione un paciente de la lista lateral o use la agenda.
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div class="actions-container" style="margin-top: 20px; text-align: right;">
<button type="button" class="btn-success-action" id="btn-guardar-enviar-historial"style="background-color: #2bc48a; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
     Guardar y enviar a historial clínico
</button>
            </div>
</div>

        <div class="workspace-grid">
            <section class="ui-panel panel-odontogram">
                <h3>Odontograma Clínico</h3>

                <div class="odontogram-visualizer-box">
                    <div class="dental-arch arch-upper">
                        <?php for($i = 1; $i <= 16; $i++): ?>
                            <a href="#modal-registrar-plan" class="tooth-item tooth-select">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <div class="jaw-divider-line"></div>

                    <div class="dental-arch arch-lower">
                        <?php for($i = 17; $i <= 32; $i++): ?>
                            <a href="#modal-registrar-plan" class="tooth-item tooth-select">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="odontogram-utility-bar">
                    <button class="btn-utility" id="resetTeeth" title="Limpiar Selección">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
            </section>

            <div class="metrics-and-phases-column">
                <section class="ui-panel phase-tracking-panel">
    <div class="panel-inner-header">
        <div class="title-with-status">
            <h4>Historial de Fases / Tratamientos</h4>
            <p>Procedimientos registrados en Historia Clínica</p>
        </div>
        <span class="badge-status-active" style="background-color: <?= !empty($procedimientos_paciente) ? '#2bc48a' : '#6b7280'; ?>;">
            <?= !empty($procedimientos_paciente) ? 'En Curso' : 'Sin Plan'; ?>
        </span>
    </div>

    <div class="sub-tab-bar">
        <span class="tab-link active">Plan de Fase</span>
    </div>

    <div class="phases-table-list" id="contenedor-fases-dinamicas">
        <?php if (!empty($procedimientos_paciente)): ?>
            <?php foreach ($procedimientos_paciente as $index => $fase): ?>
                <?php if ($fase['TIPO_SEGUIMIENTO'] === 'EVOLUCION_FASES'): ?>
                <div class="table-row-item" 
                     style="display: flex; justify-content: space-between; padding: 12px 10px; border-bottom: 1px solid #eee; align-items: center; 
                            border-left: 4px solid <?= $fase['TIPO_SEGUIMIENTO'] === 'EVOLUCION_FASES' ? '#a855f7' : '#2bc48a'; ?>; margin-bottom: 5px; background: #fff;">
                    
                    <span class="row-cell-name" style="display: flex; align-items: center; gap: 10px;">
                        <strong>Fase <?= $index + 1; ?>:</strong> 
                        
                        <div>
                            <span style="font-weight: 600; color: #1f2937;"><?= htmlspecialchars($fase['NOMBRE_PROCEDIMIENTO']); ?></span>
                            
                            <?php if ($fase['TIPO_SEGUIMIENTO'] === 'EVOLUCION_FASES'): ?>
                                <small style="color: #a855f7; display: block; font-size: 0.75rem;">⏳ Control Continuo / Evolución</small>
                            <?php else: ?>
                                <small style="color: #2bc48a; display: block; font-size: 0.75rem;">✓ Sesión Inmediata</small>
                            <?php endif; ?>

                            <?php if (!empty($fase['NOTAS'])): ?>
                                <small style="color: #6b7280; font-size: 0.7rem; display: block; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($fase['NOTAS']); ?>">
                                    Obs: <?= htmlspecialchars($fase['NOTAS']); ?>
                                </small>
                            <?php endif; ?>
                            
                            <small style="color: #6b7280; display: block; font-size: 0.75rem; margin-top: 2px;">
                                <i class="fa-regular fa-calendar" style="color: #2bc48a; margin-right: 3px;"></i> <?= !empty($fase['FECHA_REGISTRO']) ? date('d/m/Y', strtotime($fase['FECHA_REGISTRO'])) : 'N/A'; ?>
                            </small>
                        </div>

                        <span style="background: <?= $fase['PIEZA_DENTAL'] !== 'Gral' ? '#e0f2fe' : '#f3e8ff'; ?>; 
                                     color: <?= $fase['PIEZA_DENTAL'] !== 'Gral' ? '#0369a1' : '#6b21a8'; ?>; 
                                     padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; white-space: nowrap;">
                            <?= $fase['PIEZA_DENTAL'] !== 'Gral' ? 'Pieza ' . htmlspecialchars($fase['PIEZA_DENTAL']) : 'Boca Completa'; ?>
                        </span>
                    </span>
                    
                    <span class="tag-phase-state" style="color: <?= strtolower($fase['ESTADO_FASE']) === 'hecho' ? '#2bc48a' : '#f59e0b'; ?>; font-weight: 600; font-size: 0.85rem;">
                        <?= htmlspecialchars($fase['ESTADO_FASE']); ?>
                    </span>
                    
                    <span class="row-cell-price" style="font-weight: bold; color: #374151;">
                        $<?= number_format($fase['PRECIO_APLICADO'] * $fase['CANTIDAD'], 2); ?>
                    </span>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 35px 15px; color: #6b7280;">
                <div style="font-size: 2.5rem; margin-bottom: 10px; color: #d1d5db;">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h5 style="margin-bottom: 5px; color: #4b5563;">Sin tratamientos</h5>
                <p style="font-size: 0.85rem; max-width: 220px; margin: 0 auto;">
                    Selecciona piezas en el odontograma para armar el plan de fases de este paciente.
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

                <section class="ui-panel budget-summary-panel">
                    <h5>Presupuesto y Balance Financiero</h5>
                    <div class="total-amount-lbl" id="monto-total-fase" data-base-total="<?= $total_presupuesto; ?>">
    Total Estimado: $<?= number_format($total_presupuesto, 2); ?>
</div>
                    <div class="acceptance-pill" style="background-color: <?= $total_presupuesto > 0 ? '#e0f2fe' : '#f3f4f6'; ?>; color: <?= $total_presupuesto > 0 ? '#0369a1' : '#6b7280'; ?>;">
                        <i class="fa-solid fa-circle-check"></i> <?= $total_presupuesto > 0 ? 'Registro Vinculado' : 'Esperando Diagnóstico'; ?>
                    </div>
                </section>
            </div>
            
        </div>
    </main>
</div>

<div id="modal-registrar-plan" class="modal-overlay-backdrop">
    <div class="modal-dialog-box">
        <div class="modal-header-bar">
            <h3>Registrar Evolución en Odontograma</h3>
            <button type="button" class="btn-close-modal">&times;</button>
        </div>

        <div class="modal-form-content-split">
            <div class="selected-tooth-preview">
                <img src="https://cdn-icons-png.flaticon.com/512/2966/2966486.png" alt="Diente" id="toothImage">
                <h4 id="selectedTooth">Selecciona una pieza dental</h4>
            </div>

            <div class="modal-right-inputs-pane">
                <div class="form-group-field">
                    <label>Paciente Asignado</label>
                        <input type="text" class="form-control" id="patientInput" value="<?php echo htmlspecialchars($paciente_nombre ?? ''); ?>" readonly>
                    <input type="hidden" id="patientIdInput" value="<?php echo htmlspecialchars($id_paciente ?? ''); ?>">
                    <input type="hidden" id="citaIdInput" value="<?php echo isset($_GET['id_cita']) ? intval($_GET['id_cita']) : ''; ?>">
                </div>

                <div class="form-group-field">
                    <label>Fecha de Registro</label>
                    <input type="date" class="form-control" id="dateInput">
                </div>

                <div class="form-group-field">
                    <label>Procedimientos Clínicos</label>
                    <div id="selectProcedimientos" class="checkbox-group" style="max-height: 120px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px; background: #fff;"></div>
                </div>

                <div class="form-group-field">
                    <label>Notas de Evolución / Diagnóstico</label>
                    <textarea class="form-control textarea-custom" id="notesInput" rows="3" placeholder="Escribe las observaciones de la pieza..."></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer-bar">
            <button type="button" class="btn-secondary btn-close-modal">Cancelar</button>
            <button type="button" class="btn-primary-submit" id="saveTreatment">Guardar Plan</button>
        </div>
    </div>
</div>

<div class="success-message" id="successMessage">
    <i class="fa-solid fa-circle-check"></i> Tratamiento guardado exitosamente en el Odontograma
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="public/js/odontologo/planes_tratamientos_odon.js?v=<?= time() ?>" defer></script>
</body>
</html>