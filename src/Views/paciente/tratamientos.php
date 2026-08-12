<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tratamientos - Odonto Estética</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link class="no-print" rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <link class="no-print" rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/historial_clinico.css">
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">
        
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-grow-1 p-4 overflow-auto">
            
            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-3" style="border-radius: 16px; background-color: #ffffff;">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 56px; height: 56px; background-color: #e8f0fe; color: #0b57d0; flex-shrink: 0;">
                            <i class="fa-solid fa-stethoscope fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h4 fw-bold text-dark-blue m-0">Mis Tratamientos</h2>
                            <p class="text-muted small m-0 mt-1">Consulta el historial de todas tus citas y tratamientos completados, en curso o pendientes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card-custom h-100">
                        <div class="icon-box-metric bg-blue-light">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <span class="text-muted font-xs d-block fw-semibold">Consultas</span>
                            <span class="h3 fw-bold text-dark-blue m-0 d-block mt-1"><?= $estadisticas['consultas'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card-custom h-100">
                        <div class="icon-box-metric bg-purple-light"><i class="fa-solid fa-tooth"></i></div>
                        <div>
                            <span class="text-muted font-xs d-block fw-semibold">Tratamientos</span>
                            <span class="h3 fw-bold text-dark-blue m-0 d-block mt-1"><?= $estadisticas['tratamientos'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card-custom h-100">
                        <div class="icon-box-metric bg-green-light"><i class="fa-solid fa-heart-pulse"></i></div>
                        <div>
                            <span class="text-muted font-xs d-block fw-semibold">Diagnósticos</span>
                            <span class="h3 fw-bold text-dark-blue m-0 d-block mt-1"><?= $estadisticas['diagnosticos'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card-custom h-100">
                        <div class="icon-box-metric bg-orange-light"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div>
                            <span class="text-muted font-xs d-block fw-semibold">Última consulta</span>
                            <span class="h4 fw-bold m-0 d-block mt-1" style="color: #ea580c !important;"><?= $estadisticas['ultima_fecha'] ?? '--' ?></span>
                            <span class="text-muted font-xs d-block mt-1" style="font-size: 11px; line-height: 1;"><?= htmlspecialchars($estadisticas['ultima_especialidad'] ?? '--') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px; background-color: #ffffff;">
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-12 col-sm-4">
                        <h4 class="h5 fw-bold text-dark-blue m-0">Línea de Tiempo de Tratamientos</h4>
                    </div>
                    <div class="col-12 col-sm-5 col-md-4 col-xl-3 ms-sm-auto">
                        <div class="input-group align-items-center px-2 bg-light rounded-3 border" style="height: 38px;">
                            <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
                            <input type="text" id="buscarHistoria" class="form-control border-0 bg-transparent font-sm p-0 shadow-none" placeholder="Buscar por tratamiento o doctor...">
                        </div>
                    </div>
                    <div class="col-12 col-sm-3 col-xl-2 d-flex justify-content-sm-end">
                        <button type="button" id="btnAlternarOrden" class="btn btn-light border rounded-3 font-sm d-flex align-items-center gap-2 shadow-none w-100 justify-content-center" data-orden="desc" style="height: 38px;">
                            <i class="fa-solid fa-sort-amount-down"></i> <span id="txtOrden">Descendente</span>
                        </button>
                    </div>
                </div>

                <div id="contenedorHistorias" class="d-flex flex-column gap-3">
                    <?php 
                    // Mostramos las citas en general
                    if (empty($citas)): ?>
                        <div class="p-4 text-center text-muted font-sm">No registras ninguna atención clínica completada en el historial.</div>
                    <?php else: ?>
                        <?php foreach ($citas as $registro): 
                            $timestamp = strtotime($registro['FECHA_HORA']);
                            $dia = date('d', $timestamp);
                            $anio = date('Y', $timestamp);
                            
                            $mesesEspanol = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $mes = $mesesEspanol[date('n', $timestamp) - 1];

                            $nombreProcedimiento = !empty($registro['TRATAMIENTO']) ? $registro['TRATAMIENTO'] : 'Valoración / Control General';

                            if (strpos(strtoupper($nombreProcedimiento), 'VALORACION') !== false || strpos(strtoupper($nombreProcedimiento), 'DIAGNOSTICO') !== false || strpos(strtoupper($nombreProcedimiento), 'CONTROL') !== false) {
                                $claseIconoBox = 'bg-green-light';
                                $iconoInterno = 'fa-solid fa-shield-halved';
                                $badgeClase = 'badge-consult';
                                $tipoTexto = 'Consulta';
                            } else {
                                $claseIconoBox = 'bg-blue-light';
                                $iconoInterno = 'fa-solid fa-tooth';
                                $badgeClase = 'badge-treatment';
                                $tipoTexto = 'Tratamiento';
                            }

                            // Subtítulo: Recomendación en la tarjeta externa (procedimiento si está vacío)
                            $textoDescripcion = !empty(trim($registro['RECOMENDACIONES'] ?? '')) ? $registro['RECOMENDACIONES'] : $nombreProcedimiento;
                            $textoDescripcion = mb_strimwidth($textoDescripcion, 0, 120, '...');
                        ?>
                            <div class="historia-item d-flex align-items-center justify-content-between p-3 bg-white rounded-4 border border-light-subtle shadow-sm flex-wrap gap-3" 
                                 data-fecha="<?= $timestamp ?>" 
                                 data-search="<?= strtolower(htmlspecialchars($nombreProcedimiento . ' ' . $registro['NOMBRE_DOCTOR'] . ' ' . $textoDescripcion)) ?>">
                                
                                <div class="d-flex align-items-center gap-3 flex-grow-1">
                                    <div class="date-badge-timeline d-flex flex-column align-items-center justify-content-center border rounded-3 bg-light" style="width: 70px; height: 75px; flex-shrink: 0;">
                                        <span class="h4 m-0 fw-bold text-dark-blue"><?= $dia ?></span>
                                        <span class="font-xs fw-bold text-primary text-uppercase" style="font-size:10px;"><?= $mes ?></span>
                                        <span class="font-xs text-muted" style="font-size:10px;"><?= $anio ?></span>
                                    </div>
                                    <div class="icon-box-metric <?= $claseIconoBox ?> border d-flex align-items-center justify-content-center rounded-3" style="width: 52px; height: 52px; flex-shrink: 0;">
                                        <i class="<?= $iconoInterno ?>"></i>
                                    </div>
                                    <div class="ms-1">
                                        <h5 class="font-sm fw-bold text-dark-blue m-0"><?= htmlspecialchars($nombreProcedimiento) ?></h5>
                                        <p class="text-muted font-xs m-0 mt-1" style="max-width: 600px;"><?= htmlspecialchars($textoDescripcion) ?></p>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-4 justify-content-end flex-wrap">
                                    <div class="text-md-end">
                                        <span class="badge-type <?= $badgeClase ?>"><?= $tipoTexto ?></span>
                                        <span class="d-block font-xs fw-bold text-dark-blue mt-1">Dr(a). <?= htmlspecialchars($registro['NOMBRE_DOCTOR'] ?? '') ?></span>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <a href="/LOGIN_ORIGINAL/paciente/historial/exportar?formato=pdf&id=<?= $registro['ID_CITA'] ?>" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0" title="Descargar PDF" style="width: 36px; height: 36px;">
                                            <i class="fa-solid fa-download font-xs"></i>
                                        </a>
                                        
                                        <a href="/LOGIN_ORIGINAL/paciente/historial/exportar?formato=imprimir&id=<?= $registro['ID_CITA'] ?>" target="_blank" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0" title="Imprimir" style="width: 36px; height: 36px;">
                                            <i class="fa-solid fa-print font-xs"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 btn-view-hc" data-id="<?= $registro['ID_CITA'] ?>" title="Ver detalles" style="width: 36px; height: 36px;">
                                            <i class="fa-regular fa-eye font-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-light flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="txtContadorPaginacion" class="text-muted font-xs m-0">Mostrando 0 a 0 de 0 registros</span>
                        <select id="registrosPorPagina" class="form-select form-select-sm border rounded-3 bg-white shadow-none font-xs" style="width: auto; height: 32px; padding-top: 2px; padding-bottom: 2px;">
                            <option value="5"> 5 </option>
                            <option value="10"> 10 </option>
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

    <!-- Modal Detalles del Historial Clínico Pulido -->
    <div class="modal fade" id="modalDetalleCita" tabindex="-1" aria-labelledby="modalDetalleCitaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content style-modal-custom" style="border-radius: 20px; border: none; box-shadow: 0 12px 40px rgba(0,0,0,0.12); background-color: #ffffff;">
                
                <div class="modal-header border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-medical text-primary fs-4"></i>
                        <h5 class="modal-title font-weight-bold text-dark-blue m-0" id="modalDetalleCitaLabel" style="font-size: 20px; font-weight: 700;">Resumen del Registro Médico</h5>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4">
                    <!-- Bloque 1: Información Administrativa Basada en Filas Limpias -->
                    <div class="container-fields-detail bg-light p-3 rounded-4 mb-4 border border-light-subtle">
                        <div class="d-flex align-items-center py-2 border-bottom-soft">
                            <div class="box-icon-detail text-primary" style="width: 30px;"><i class="far fa-user"></i></div>
                            <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Paciente:</span></div>
                            <div class="text-end"><span id="txtDetallePaciente" class="text-dark-blue fw-bold font-sm"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Paciente Registrado') ?></span></div>
                        </div>
                        <div class="d-flex align-items-center py-2 border-bottom-soft">
                            <div class="box-icon-detail text-primary" style="width: 30px;"><i class="fa-solid fa-id-card"></i></div>
                            <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Documento:</span></div>
                            <div class="text-end"><span id="txtDetalleDocumento" class="text-dark-blue fw-bold font-sm"><?= htmlspecialchars($_SESSION['usuario_documento'] ?? 'No Registrado') ?></span></div>
                        </div>
                        <div class="d-flex align-items-center py-2 border-bottom-soft">
                            <div class="box-icon-detail text-primary" style="width: 30px;"><i class="fa-solid fa-user-doctor"></i></div>
                            <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Odontólogo Tratante:</span></div>
                            <div class="text-end"><span id="txtDetalleDoctor" class="text-dark-blue fw-bold font-sm">Dr(a). --</span></div>
                        </div>
                        <div class="d-flex align-items-center py-2 border-bottom-soft">
                            <div class="box-icon-detail text-primary" style="width: 30px;"><i class="fa-solid fa-id-card-clip"></i></div>
                            <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Especialidad:</span></div>
                            <div class="text-end"><span id="txtDetalleEspecialidad" class="text-dark-blue fw-bold font-sm">--</span></div>
                        </div>
                        <div class="d-flex align-items-center py-2 border-bottom-soft">
                            <div class="box-icon-detail text-primary" style="width: 30px;"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Consultorio:</span></div>
                            <div class="text-end"><span id="txtDetalleConsultorio" class="badge bg-primary text-white px-2 py-1 rounded-2 font-xs fw-bold">Consultorio --</span></div>
                        </div>
                        <div class="d-flex align-items-center py-2 mb-0">
                            <div class="box-icon-detail text-success" style="width: 30px;"><i class="far fa-calendar-check"></i></div>
                            <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Fecha y Hora de Cierre:</span></div>
                            <div class="text-end"><span id="txtDetalleFechaAtencion" class="text-dark-blue fw-bold font-sm">--/--/---- --:-- --</span></div>
                        </div>
                    </div>

                    <!-- Bloque 2: Evolución Clínica Dinámica con Bloques de Color -->
                    <div class="row g-3">
                        <!-- Tarjeta Tratamiento -->
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border" style="border-left: 4px solid #0b57d0 !important;">
                                <span class="text-primary font-xs d-block fw-bold text-uppercase tracking-wider mb-1"><i class="fas fa-tooth me-2"></i>Procedimiento Clínico</span>
                                <h6 id="txtDetalleTratamiento" class="fw-bold text-dark-blue m-0 font-sm">Procedimiento</h6>
                            </div>
                        </div>

                        <!-- Tarjeta Observaciones -->
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border" style="border-left: 4px solid #16a34a !important;">
                                <span class="text-success font-xs d-block fw-bold text-uppercase tracking-wider mb-1"><i class="far fa-file-alt me-2"></i>Observaciones del Diagnóstico</span>
                                <p id="txtDetalleObservaciones" class="text-muted-gray font-sm m-0 line-height-sm text-justify">--</p>
                            </div>
                        </div>

                        <!-- Tarjeta Recomendaciones -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border" style="background-color: #fff7ed !important; border-color: #ffedd5 !important; border-left: 4px solid #ea580c !important;">
                                <span class="font-xs d-block fw-bold text-uppercase tracking-wider mb-1" style="color: #ea580c !important;"><i class="fas fa-notes-medical me-2"></i>Recomendaciones Médicas</span>
                                <p id="txtDetalleRecomendaciones" class="text-muted-gray font-sm m-0 line-height-sm text-justify">--</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-secondary w-100 border-0" data-bs-dismiss="modal" style="border-radius: 12px; font-weight: 600; font-size: 14px; padding: 10px; background-color: #64748b;">Cerrar Resumen</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/LOGIN_ORIGINAL/public/js/paciente/tratamientos.js?v=<?= time() ?>"></script>
</body>
</html>