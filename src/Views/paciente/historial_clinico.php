<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Historia Clínica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Estilos compartidos del paciente -->
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/tratamientos.css">
    
    <!-- Cargamos los estilos del odontograma adaptados para el paciente (sin romper layout global) -->
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/odontograma_paciente.css?v=1.2">

    <style>
        /* GRID PARA DETALLES DE DIENTES (Visible en el modal y PDF) */
        .tooth-detail-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0b57d0;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .tooth-detail-card:hover {
            transform: translateX(4px);
        }
        .tooth-detail-card.hecho { border-left-color: #16a34a; }
        .tooth-detail-card.proceso { border-left-color: #ea580c; }
    </style>
</head>
<body>
    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">
        
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>
        
        <main class="flex-grow-1 p-4 overflow-auto">
            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            <?php 
                $pacienteIdReal = $idPaciente ?? 0;
            ?>
            
            <?php if ($pacienteIdReal === 0 || $pacienteIdReal === null): ?>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 70vh; text-align: center;">
                    <i class="fa-solid fa-notes-medical" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h2 style="color: #475569; font-size: 1.8rem; margin: 0 0 10px 0;">No se encontró tu registro clínico</h2>
                    <p style="color: #94a3b8; font-size: 1.1rem; max-width: 400px;">Comunícate con la administración para que validen tu perfil médico.</p>
                </div>
            <?php else: ?>

            <input type="hidden" id="pacienteIdHidden" value="<?php echo $pacienteIdReal; ?>">

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-3" style="border-radius: 16px; background-color: #ffffff;">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 56px; height: 56px; background-color: #e8f0fe; color: #0b57d0; flex-shrink: 0;">
                            <i class="fa-solid fa-book-medical fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h4 fw-bold text-dark-blue m-0">Historial Clínico Integral</h2>
                            <p class="text-muted small m-0 mt-1">Explora tu registro detallado de atenciones, diagnósticos y procedimientos odontológicos oficiales.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px; background-color: #ffffff;">
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-12 col-sm-4">
                        <h4 class="h5 fw-bold text-dark-blue m-0">Registro de Evoluciones</h4>
                    </div>
                    <div class="col-12 col-sm-5 col-md-4 col-xl-3 ms-sm-auto">
                        <div class="input-group align-items-center px-2 bg-light rounded-3 border" style="height: 38px;">
                            <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
                            <input type="text" id="inputBuscarHistorial" class="form-control border-0 bg-transparent font-sm p-0 shadow-none" placeholder="Buscar por diagnóstico o doctor...">
                        </div>
                    </div>
                    <div class="col-12 col-sm-3 col-xl-2 d-flex justify-content-sm-end">
                        <select id="selectOrdenHistorial" class="form-select border shadow-none font-sm rounded-3" style="height: 38px; background-color: white;">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguas">Más antiguas</option>
                        </select>
                    </div>
                </div>

                <div id="tbodyHistorial" class="d-flex flex-column gap-3">
                    <?php if (!empty($citas)): ?>
                        <?php foreach ($citas as $cita): 
                            $timestamp = strtotime($cita['FECHA_HORA']);
                            $dia = date('d', $timestamp);
                            $anio = date('Y', $timestamp);
                            $mesesEspanol = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $mes = $mesesEspanol[date('n', $timestamp) - 1];
                            $horaFmt = date('h:i A', $timestamp);
                            
                            $motivo = !empty($cita['TRATAMIENTO']) ? $cita['TRATAMIENTO'] : 'Valoración / Control General';
                            $diagnostico = !empty($cita['RECOMENDACIONES']) ? $cita['RECOMENDACIONES'] : 'Sin diagnóstico registrado en sistema';
                            
                            $busquedaStr = strtolower($dia . ' ' . $mes . ' ' . $anio . ' ' . $cita['NOMBRE_DOCTOR'] . ' ' . $motivo . ' ' . $diagnostico);
                            $diagnosticoResumen = mb_strimwidth($diagnostico, 0, 100, '...');
                        ?>
                        <div class="historial-row historia-item d-flex align-items-center justify-content-between p-3 bg-white rounded-4 border border-light-subtle shadow-sm flex-wrap gap-3" 
                             data-fecha="<?= $timestamp ?>" 
                             data-search="<?= htmlspecialchars($busquedaStr) ?>">
                            
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <div class="date-badge-timeline d-flex flex-column align-items-center justify-content-center border rounded-3 bg-light" style="width: 70px; height: 75px; flex-shrink: 0;">
                                    <span class="h4 m-0 fw-bold text-dark-blue"><?= $dia ?></span>
                                    <span class="font-xs fw-bold text-primary text-uppercase" style="font-size:10px;"><?= $mes ?></span>
                                    <span class="font-xs text-muted" style="font-size:10px;"><?= $anio ?></span>
                                </div>
                                <div class="icon-box-metric bg-blue-light border d-flex align-items-center justify-content-center rounded-3" style="width: 52px; height: 52px; flex-shrink: 0;">
                                    <i class="fa-solid fa-tooth"></i>
                                </div>
                                <div class="ms-1">
                                    <h5 class="font-sm fw-bold text-dark-blue m-0"><?= htmlspecialchars($motivo) ?></h5>
                                    <p class="text-muted font-xs m-0 mt-1" style="max-width: 500px;"><?= htmlspecialchars($diagnosticoResumen) ?></p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-4 justify-content-end flex-wrap">
                                <div class="text-md-end">
                                    <span class="badge-type badge-treatment">Dr(a). <?= htmlspecialchars($cita['NOMBRE_DOCTOR'] ?? '') ?></span>
                                    <span class="d-block font-xs fw-bold text-muted mt-1"><i class="fa-regular fa-clock me-1"></i> <?= $horaFmt ?></span>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <button class="btn btn-outline-primary rounded-pill font-sm fw-bold d-flex align-items-center px-3 btn-ver-detalle" data-id="<?= $cita['ID_CITA'] ?>" data-doc="<?= htmlspecialchars($cita['NOMBRE_DOCTOR'] ?? '') ?>" data-fecha-iso="<?= date('Y-m-d', strtotime($cita['FECHA_HORA'])) ?>">
                                        <i class="fa-solid fa-file-medical me-2"></i> Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="historial-row p-4 text-center text-muted font-sm border rounded-4 bg-light">
                            <i class="fa-regular fa-folder-open fs-2 mb-3 d-block text-black-50"></i>
                            Aún no hay registros de historias clínicas.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-light flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="txtContadorPaginacion" class="text-muted font-xs m-0">Mostrando 0 a 0 de 0 registros</span>
                        <select id="registrosPorPagina" class="form-select form-select-sm border rounded-3 bg-white shadow-none font-xs" style="width: auto; height: 32px; padding-top: 2px; padding-bottom: 2px;">
                            <option value="5"> 5 </option>
                            <option value="10" selected> 10 </option>
                            <option value="25"> 25 </option>
                        </select>
                    </div>
                    <nav>
                        <ul id="ulPaginacion" class="pagination pagination-sm m-0 gap-1"></ul>
                    </nav>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL PRINCIPAL: DETALLE DE LA HISTORIA CLINICA Y ODONTOGRAMA -->
    <div class="modal fade" id="modalOdontogramaHistoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background-color: #f8fafc;">
                
                <div class="modal-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-blue-light text-primary d-flex align-items-center justify-content-center rounded-3 shadow-sm" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-clipboard-list fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark-blue m-0 fs-4">Detalle de Evolución Clínica</h5>
                            <p class="text-muted font-xs m-0" id="modal-subtitle">Registro médico oficial de la atención prestada</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-circle shadow-sm" data-bs-dismiss="modal" style="width: 40px; height: 40px; color: #64748b;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-4 main-content-modal" id="printable-modal-content">
                    
                    <!-- ENCABEZADO OCULTO (Solo visible en PDF e Impresión) -->
                    <div id="print-header" style="display: none; text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0b57d0; padding-bottom: 15px;">
                        <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Logo Odonto Estética" style="max-width: 200px; margin-bottom: 10px;">
                        <h1 style="color: #1e293b; margin: 0; font-size: 1.8rem;">Historia Clínica Odontológica</h1>
                        <p style="color: #64748b; margin: 5px 0 0 0;">Odonto Estética - Salud y Bienestar</p>
                    </div>

                    <!-- TARJETA DEL PACIENTE Y BOTONES -->
                    <section class="patient-card" style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div class="patient-info-block" style="display: flex; gap: 15px; align-items: center;">
                            <div id="avatarInitials" style="width: 60px; height: 60px; background-color: #0b57d0; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.4rem; text-transform: uppercase;">--</div>
                            <div class="patient-details">
                                <h2 id="historial-nombre-paciente" style="margin: 0; color: #0f172a; font-size: 1.4rem; font-weight: bold;">Cargando paciente...</h2>
                                <p id="historial-meta-paciente" style="margin: 4px 0 0 0; color: #475569; font-size: 0.95rem;">Identificación: -- • Doctor Tratante: --</p>
                            </div>
                        </div>
                        
                        <!-- PANEL DE BOTONES -->
                        <div class="patient-actions no-print" id="botones-acciones" style="display: flex; gap: 10px;">
                            <button id="btn-imprimir" class="btn btn-outline-secondary font-sm fw-bold shadow-sm rounded-3">
                                 <i class="fa-solid fa-print"></i> Imprimir
                            </button>
                            <button id="btn-exportar-pdf" class="btn btn-outline-danger font-sm fw-bold shadow-sm rounded-3 px-3">
                                 <i class="fa-solid fa-file-pdf me-1"></i> Descargar PDF
                            </button>
                        </div>
                    </section>

                    <!-- SECCIÓN 1: ODONTOGRAMA -->
                    <section class="odontogram-section" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: white; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div style="border-bottom: 2px solid #f8fafc; padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: #e8f0fe; color: #0b57d0; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-teeth-open"></i></div>
                            <h3 style="margin: 0; color: #0f172a; font-size: 1.15rem; font-weight: bold;">Odontograma Interactivo (Adulto)</h3>
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

                            <div class="teeth-row inferior mt-4">
                                <?php foreach([17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32] as $num) { ?>
                                    <div class="tooth-item" data-tooth-number="<?= $num ?>">
                                        <div class="tooth-shape premolar" id="shape-<?= $num ?>"></div>
                                        <span class="tooth-number"><?= $num ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 2: DETALLES POR PIEZA DENTAL (NUEVO VISIBLE) -->
                    <section id="print-tooth-details" style="display: block; margin-bottom: 24px; background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div style="border-bottom: 2px solid #f8fafc; padding-bottom: 10px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: #e6f4ea; color: #137333; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-list-check"></i></div>
                            <h3 style="margin: 0; color: #0f172a; font-size: 1.15rem; font-weight: bold;">Procedimientos y Detalles por Pieza Dental</h3>
                        </div>
                        <!-- Aquí se inyectan las tarjetas desde JS -->
                        <div id="print-tooth-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                            <!-- Placeholder -->
                            <div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #64748b;">
                                <i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando detalles de los procedimientos...
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 3: RESUMEN MÉDICO Y EVOLUCIÓN (AL FINAL) -->
                    <div class="right-summary-panel" style="display: flex; flex-direction: row; gap: 20px; width: 100%;">
                        <div class="summary-card" style="flex: 1; background: #fff; padding: 20px; border-radius: 12px; border-left: 5px solid #dc3545; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <h3 style="margin-top:0; color: #dc3545; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; font-weight: bold; border-bottom: 1px solid #fdf5f6; padding-bottom: 10px;">
                                <div style="width: 28px; height: 28px; background: #fce8e6; border-radius: 6px; display: flex; justify-content: center; align-items: center;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                Alertas y Antecedentes
                            </h3>
                            <ul style="list-style: none; padding: 0; margin: 15px 0 0 0; display: flex; flex-direction: column; gap: 12px; font-size: 0.95rem; color: #334155;">
                                <li><strong style="color: #0f172a;">Alergias:</strong> <span id="resumen-alergias" style="color: #dc3545; font-weight: 600;">Cargando...</span></li>
                                <li><strong style="color: #0f172a;">Enfermedades:</strong> <span id="resumen-enfermedades">Cargando...</span></li>
                                <li><strong style="color: #0f172a;">Medicamentos:</strong> <span id="resumen-medicamentos">Cargando...</span></li>
                            </ul>
                        </div>

                        <div class="summary-card" style="flex: 1; background: #fff; padding: 20px; border-radius: 12px; border-left: 5px solid #0b57d0; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <h3 style="margin-top:0; color: #0b57d0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; font-weight: bold; border-bottom: 1px solid #eff6ff; padding-bottom: 10px;">
                                <div style="width: 28px; height: 28px; background: #e8f0fe; border-radius: 6px; display: flex; justify-content: center; align-items: center;"><i class="fa-solid fa-file-signature"></i></div>
                                Evolución Clínica y Notas
                            </h3>
                            <ul style="list-style: none; padding: 0; margin: 15px 0 0 0; display: flex; flex-direction: column; gap: 12px; font-size: 0.95rem; color: #334155;">
                                <li><strong style="color: #0f172a; display: block; margin-bottom: 4px;">Motivo Principal:</strong> <span id="resumen-cita-hoy" style="display: block; background: #f8fafc; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0;">Cargando...</span></li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" defer></script>
    <script src="/LOGIN_ORIGINAL/public/js/paciente/historial_clinico.js?v=<?= time() ?>" defer></script>
</body>
</html>