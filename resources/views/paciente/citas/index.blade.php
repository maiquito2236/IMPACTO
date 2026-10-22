@extends('layouts.paciente')

@section('titulo', 'Mis Citas - Odonto Estética')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="{{ asset('css/paciente/mis_citas.css') }}?v={{ filemtime(public_path('css/paciente/mis_citas.css')) }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="row g-4">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm p-4 mb-4 d-flex flex-row align-items-center gap-3" style="border-radius: 16px; background-color: #ffffff;">
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 56px; height: 56px; background-color: #e8f0fe; color: #0b57d0; flex-shrink: 0;">
                    <i class="fa-solid fa-calendar-check fs-4"></i>
                </div>
                <div>
                    <h2 class="h4 fw-bold text-dark-blue m-0">Mis citas</h2>
                    <p class="text-muted small m-0 mt-1">Gestiona y consulta todas tus citas programadas.</p>
                </div>
            </div>

            @if (session('mensaje_success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 small mb-4" role="alert">
                    <span>{{ session('mensaje_success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('mensaje_error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 small mb-4" role="alert">
                    <span>{{ session('mensaje_error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filtros y Búsqueda -->
            <div class="row g-3 align-items-center mb-4">
                <div class="col-xl-7 col-lg-12">
                    <div class="d-flex flex-wrap gap-2 appointment-tabs-container">
                        <button type="button" class="btn-tab-filter active" data-filter="Todas">
                            <i class="fa-regular fa-calendar text-primary-icon"></i> Todas
                        </button>
                        <button type="button" class="btn-tab-filter" data-filter="Pendiente">
                            <i class="fa-regular fa-calendar-check text-blue-icon"></i> Programadas
                        </button>
                        <button type="button" class="btn-tab-filter" data-filter="Completada">
                            <i class="fa-regular fa-circle-check text-green-icon"></i> Completadas
                        </button>
                        <button type="button" class="btn-tab-filter" data-filter="Cancelada">
                            <i class="fa-regular fa-circle-xmark text-red-icon"></i> Canceladas
                        </button>
                        <button type="button" class="btn-tab-filter" data-filter="No asistió">
                            <i class="fa-regular fa-circle-dot text-gray-icon"></i> No asistió
                        </button>
                    </div>
                </div>
                
                <div class="col-xl-5 col-lg-12">
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-custom flex-grow-1 align-items-center px-2 bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted me-1"></i>
                            <input type="text" id="search-appointment" class="form-control border-0 bg-transparent font-sm ps-1 shadow-none" placeholder="Buscar por tratamiento, odontólogo...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Citas -->
            <div class="card card-appointments overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-appointments m-0 align-middle" id="tablaCitas">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Doctor</th>
                                <th>Tratamiento</th>
                                <th>Consultorio</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="citas-container">
                            @if (!empty($citas))
                                @foreach ($citas as $get_cita)
                                    @php
                                        $timestamp = strtotime($get_cita['FECHA_HORA']);
                                        $dia = date('d', $timestamp);
                                        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                        $mes = $meses[(int)date('n', $timestamp) - 1];
                                        $anio = date('Y', $timestamp);
                                        $hora = date('g:i A', $timestamp);

                                        $badgeClass = 'status-pending';
                                        $estadoTexto = $get_cita['ESTADO_CITA'] ?? ($get_cita['ESTADO'] ?? 'Pendiente');
                                        $badgeClass = 'status-pending';
                                        
                                        if ($estadoTexto === 'Completada') { 
                                            $badgeClass = 'status-completed'; 
                                        } elseif ($estadoTexto === 'Cancelada') { 
                                            $badgeClass = 'status-cancelled'; 
                                        } elseif ($estadoTexto === 'No asistió') {
                                            $badgeClass = 'status-no-asistio';
                                        }

                                        $esMenor = !empty($get_cita['NOMBRE_PACIENTE']) && $get_cita['NOMBRE_PACIENTE'] !== (Auth::check() ? (Auth::user()->NOMBRES . ' ' . Auth::user()->APELLIDOS) : '');
                                        $doctorNombre = $get_cita['NOMBRE_ODONTOLOGO'] ?? ($get_cita['NOMBRE_DOCTOR'] ?? 'Odontólogo');
                                        $especialidadNombre = !empty($get_cita['NOMBRE_ESPECIALIDAD']) ? $get_cita['NOMBRE_ESPECIALIDAD'] : 'General';
                                        $tratamientoTexto = !empty($get_cita['NOMBRE_PROCEDIMIENTO']) ? $get_cita['NOMBRE_PROCEDIMIENTO'] : (!empty($get_cita['MOTIVO']) ? $get_cita['MOTIVO'] : 'Consulta Odontológica');
                                        $consultorioTexto = !empty($get_cita['NOMBRE_CONSULTORIO']) ? $get_cita['NOMBRE_CONSULTORIO'] : '--';
                                        $consultorioFormato = str_starts_with(strtolower($consultorioTexto), 'consultorio') ? $consultorioTexto : 'Consultorio ' . $consultorioTexto;
                                    @endphp
                                    <tr class="dashboard-card" data-status="{{ $estadoTexto }}">
                                        <td data-order="{{ date('YmdHis', $timestamp) }}">
                                            <div class="date-badge-box">
                                                <div class="date-badge-day fw-bold">{{ $dia }}</div>
                                                <div class="date-badge-month font-xs fw-bold">{{ $mes }}</div>
                                                <div class="date-badge-year font-xs opacity-75">{{ $anio }}</div>
                                            </div>
                                        </td>
                                        
                                        <td class="fw-semibold font-sm text-dark-blue">{{ $hora }}</td>
                                        
                                        <td class="col-doctor">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-light text-primary d-flex align-items-center justify-content-center rounded-circle font-sm fw-bold border" style="width:32px; height:32px; min-width:32px;">
                                                    <i class="fa-solid fa-user-doctor"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold font-sm card-doctor-name">Dr(a). {{ $doctorNombre }}</div>
                                                    <div class="text-muted font-xs card-specialty">{{ $especialidadNombre }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="col-tratamiento fw-bold text-dark-blue font-sm">
                                            <div>{{ $tratamientoTexto }}</div>
                                            @if($esMenor)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-xs fw-semibold mt-1">
                                                    <i class="fa-solid fa-child me-1"></i> Paciente: {{ $get_cita['NOMBRE_PACIENTE'] }}
                                                </span>
                                            @endif
                                        </td>
                                        
                                        <td class="text-muted font-sm col-consultorio">
                                            <i class="fa-solid fa-location-dot me-1"></i>{{ $consultorioFormato }}
                                        </td>
                                        
                                        <td><span class="status-badge-pill status-badge {{ $badgeClass }} font-xs">{{ $estadoTexto }}</span></td>

                                        <td class="text-center">
                                            <div class="action-buttons-flex d-flex gap-2 justify-content-center">
                                                <button type="button" class="btn-action-circle btn-view" data-id="{{ $get_cita['ID_CITA'] }}" data-id-odontologo="{{ $get_cita['ODONTOLOGO_ID_ODONTOLOGO'] ?? 1 }}">
                                                    <i class="fa-regular fa-eye"></i>
                                                    <span class="action-tooltip">Ver detalle</span>
                                                </button>

                                                @if ($estadoTexto === 'Pendiente')
                                                    <button type="button" class="btn-action-circle btn-reschedule" data-bs-toggle="modal" data-bs-target="#modalReprogramar" data-id="{{ $get_cita['ID_CITA'] }}" data-id-odontologo="{{ $get_cita['ODONTOLOGO_ID_ODONTOLOGO'] ?? 1 }}">
                                                        <i class="fa-regular fa-calendar-minus"></i>
                                                        <span class="action-tooltip">Reprogramar</span>
                                                    </button>
                                                    <button type="button" class="btn-action-circle btn-cancel text-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar" data-id="{{ $get_cita['ID_CITA'] }}">
                                                        <i class="fa-regular fa-circle-xmark"></i>
                                                        <span class="action-tooltip">Cancelar</span>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn-action-circle opacity-25 pe-none" tabindex="-1"><i class="fa-regular fa-calendar-minus"></i></button>
                                                    <button type="button" class="btn-action-circle opacity-25 pe-none" tabindex="-1"><i class="fa-regular fa-circle-xmark"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reprogramar Cita -->
<div class="modal fade" id="modalReprogramar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-0 mb-3 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-dark-blue" style="font-size: 20px;">Reprogramar cita</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 mb-4 d-flex align-items-center gap-3" style="background-color: #eff6ff; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center justify-content-center text-primary bg-white rounded-circle shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="fa-regular fa-calendar-check fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="d-block font-weight-bold text-dark-blue font-sm" style="margin-bottom: 2px;">Cita actual</span>
                        <span id="rep-card-fecha-hora" class="d-block fw-bold text-dark font-sm">--/--/---- - --:-- --</span>
                        <span id="rep-card-doctor-info" class="d-block text-muted-gray font-xs mt-1">Dra. -- --</span>
                        <span id="rep-card-tratamiento-info" class="d-block text-muted-gray font-xs">Procedimiento - Consultorio --</span>
                    </div>
                </div>

                <form id="formReprogramar" action="{{ route('paciente.citas.reprogramar') }}" method="POST">
                    @csrf
                    <input type="hidden" id="rep-id-cita" name="id_cita">
                    <input type="hidden" id="rep-id-odontologo" name="id_odontologo">
                    
                    <h6 class="fw-bold text-dark-blue mb-3 font-sm">Nueva fecha y hora</h6>
                    
                    <div class="mb-3">
                        <label for="rep-fecha" class="form-label font-xs text-muted-gray fw-bold mb-1">Nueva fecha *</label>
                        <input type="date" id="rep-fecha" name="nueva_fecha" class="form-control font-sm shadow-none" style="border-radius: 10px; border: 1px solid #e2e8f0; background-color: #f8fafc;" min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="row gx-3">
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="rep-hora" class="form-label font-xs text-muted-gray fw-bold mb-1">Nueva hora *</label>
                            <select id="rep-hora" class="form-select form-select-sm py-2 shadow-none font-sm" style="border-radius: 10px; border: 1px solid #e2e8f0; background-color: #f8fafc;" required disabled>
                                <option value="" disabled selected>Primero elige fecha</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="rep-odontologo" class="form-label font-xs text-muted-gray fw-bold mb-1">Odontólogo *</label>
                            <select id="rep-odontologo" name="nuevo_id_horario" class="form-select form-select-sm py-2 shadow-none font-sm" style="border-radius: 10px; border: 1px solid #e2e8f0; background-color: #f8fafc;" required disabled>
                                <option value="" disabled selected>Primero elige hora</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="rep-motivo" class="form-label font-xs text-muted-gray fw-bold mb-1">Motivo de la reprogramación (opcional)</label>
                        <textarea id="rep-motivo" name="motivo" class="form-control shadow-none font-sm text-dark-blue" style="border-radius: 12px; border: 1px solid #e2e8f0;" rows="3" placeholder="Cuéntanos brevemente el motivo de la reprogramación..."></textarea>
                    </div>

                    <div class="p-3 mb-4 d-flex align-items-start gap-3" style="border-radius: 12px; font-size: 13px; background-color: #eff6ff; color: #1e40af;">
                        <i class="fas fa-info-circle mt-1" style="color: #0b57d0;"></i>
                        <div class="flex-grow-1">
                            <span class="d-block font-xs text-primary-icon">Recibirás una notificación de confirmación con la nueva fecha y hora.</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light fw-semibold px-4 py-2 border font-sm" data-bs-dismiss="modal" style="border-radius: 10px;">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2 font-sm" style="background-color: #0b57d0; border: none; border-radius: 10px;">Confirmar reprogramación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar Cita -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-0 mb-3 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-dark-blue" style="font-size: 20px;">Cancelar cita</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 mb-4 d-flex align-items-center gap-3" style="background-color: #fce8e6; border-radius: 12px; border: 1px solid #f9d5d1;">
                    <div class="d-flex align-items-center justify-content-center text-danger bg-white rounded-circle shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="fa-regular fa-calendar-xmark fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="d-block font-weight-bold text-dark-blue font-sm" style="margin-bottom: 2px;">Detalle de la cita</span>
                        <span id="canc-card-fecha-hora" class="d-block fw-bold text-dark font-sm">--/--/---- - --:-- --</span>
                        <span id="canc-card-doctor-info" class="d-block text-muted-gray font-xs mt-1">Dra. -- --</span>
                        <span id="canc-card-tratamiento-info" class="d-block text-muted-gray font-xs">Procedimiento - Consultorio --</span>
                    </div>
                </div>

                <form id="formCancelar" action="{{ route('paciente.citas.cancelar') }}" method="POST">
                    @csrf
                    <input type="hidden" id="canc-id-cita" name="id_cita">
                    
                    <div class="mb-3">
                        <label class="form-label font-xs text-muted-gray fw-bold mb-2">Motivo de cancelación *</label>
                        <div class="d-flex flex-column gap-2 ps-1">
                            <div class="form-check">
                                <input class="form-check-input shadow-none" type="radio" name="motivo_cancelacion" id="motivo1" value="No puedo asistir" checked required>
                                <label class="form-check-label font-sm text-dark-blue" for="motivo1">No puedo asistir</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input shadow-none" type="radio" name="motivo_cancelacion" id="motivo2" value="Problemas de horario">
                                <label class="form-check-label font-sm text-dark-blue" for="motivo2">Problemas de horario</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input shadow-none" type="radio" name="motivo_cancelacion" id="motivo3" value="Problemas personales">
                                <label class="form-check-label font-sm text-dark-blue" for="motivo3">Problemas personales</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input shadow-none" type="radio" name="motivo_cancelacion" id="motivo4" value="Otra razón">
                                <label class="form-check-label font-sm text-dark-blue" for="motivo4">Otra razón</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 mt-3">
                        <label for="canc-observaciones" class="form-label font-xs text-muted-gray fw-bold mb-1">Observaciones <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea id="canc-observaciones" name="observaciones_paciente" class="form-control shadow-none font-sm text-dark-blue" style="border-radius: 12px; border: 1px solid #e2e8f0;" rows="3" placeholder="Si deseas, puedes agregar más detalles..."></textarea>
                    </div>

                    <div class="p-3 mb-4 d-flex align-items-start gap-3" style="border-radius: 12px; font-size: 13px; background-color: #fce8e6; color: #c5221f; border: 1px solid #f9d5d1;">
                        <i class="fas fa-exclamation-triangle mt-1" style="color: #c5221f;"></i>
                        <div class="flex-grow-1">
                            <span class="d-block fw-semibold font-xs" style="color: #c5221f;">Al cancelar tu cita, esta se liberará para que otro paciente pueda tomarla.</span>
                            <span class="d-block font-xs mt-1" style="color: #c5221f; opacity: 0.85;">Si deseas reprogramar, puedes hacerlo más adelante.</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light fw-semibold px-4 py-2 border font-sm" data-bs-dismiss="modal" style="border-radius: 10px;">No cancelar</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4 py-2 font-sm" style="border-radius: 10px;">Sí, cancelar cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalle de Cita -->
<div class="modal fade" id="modalDetalleCita" tabindex="-1" aria-labelledby="modalDetalleCitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 px-4 pt-4 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="modal-title font-weight-bold text-dark-blue" id="modalDetalleCitaLabel" style="font-size: 20px;">Detalles de la cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body px-4 py-2">
                <!-- Tarjeta Principal con Ícono -->
                <div id="wrapperEstadoHeader" class="p-3 mb-4 d-flex align-items-center justify-content-between" style="border-radius: 12px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="style-icon-box text-primary">
                            <i class="fas fa-tooth" style="font-size: 20px;"></i>
                        </div>
                        <div>
                            <span class="text-dark-blue font-weight-bold d-block" style="font-size: 15px;">Consulta Odontológica</span>
                            <span class="text-muted-gray font-xs">Odonto Estética</span>
                        </div>
                    </div>
                    <div>
                        <span id="badgeEstadoCita" class="status-badge font-xs">--</span>
                    </div>
                </div>

                <!-- Lista de Datos Detallada -->
                <div class="mt-2">
                    <div class="d-flex align-items-center py-2 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="far fa-calendar-alt"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Fecha:</span></div>
                        <div class="text-right"><span id="txtDetalleFecha" class="text-dark-blue font-weight-bold font-sm">--/--/----</span></div>
                    </div>
                    <div class="d-flex align-items-center py-2 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="far fa-clock"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Hora:</span></div>
                        <div class="text-right"><span id="txtDetalleHora" class="text-dark-blue font-weight-bold font-sm">--:-- --</span></div>
                    </div>
                    <div class="d-flex align-items-center py-2 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="far fa-user"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Odontólogo:</span></div>
                        <div class="text-right"><span id="txtDetalleDoctor" class="text-dark-blue font-weight-bold font-sm">Dr(a). --</span></div>
                    </div>
                    <div class="d-flex align-items-center py-2 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="fa-solid fa-id-card-clip"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Especialidad:</span></div>
                        <div class="text-right"><span id="txtDetalleEspecialidad" class="text-dark-blue font-weight-bold font-sm">--</span></div>
                    </div>
                    
                    <div class="d-flex align-items-center py-3 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="fas fa-tooth"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Tratamiento:</span></div>
                        <div class="text-right" style="max-width: 65%;"><span id="txtDetalleTratamiento" class="text-dark-blue font-weight-bold font-sm d-block text-end line-height-sm">Procedimiento</span></div>
                    </div>

                    <div class="d-flex align-items-center py-2 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="fas fa-teeth-open"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Dientes Tratados:</span></div>
                        <div class="text-right"><span id="txtDetalleDientes" class="text-dark-blue font-weight-bold font-sm">--</span></div>
                    </div>

                    <div class="d-flex align-items-center py-2 border-bottom-soft">
                        <div class="box-icon-detail text-primary"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Consultorio:</span></div>
                        <div class="text-right"><span id="txtDetalleConsultorio" class="text-dark-blue font-weight-bold font-sm">Consultorio --</span></div>
                    </div>

                    <div id="rowFechaAtencion" class="d-flex align-items-center py-2 border-bottom-soft d-none">
                        <div class="box-icon-detail text-success"><i class="far fa-calendar-check"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Fecha de atención:</span></div>
                        <div class="text-right"><span id="txtDetalleFechaAtencion" class="text-dark-blue font-weight-bold font-sm">--/--/----</span></div>
                    </div>
                    <div id="rowObservacionesDoc" class="d-flex align-items-start py-3 border-bottom-soft d-none">
                        <div class="box-icon-detail text-primary mt-1"><i class="far fa-file-alt"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm fw-bold">Observaciones del Médico:</span></div>
                        <div class="text-right" style="max-width: 55%;"><span id="txtDetalleObservaciones" class="text-muted-gray font-sm text-end d-block">--</span></div>
                    </div>
                    <div id="rowRecomendaciones" class="d-flex align-items-start py-3 border-bottom-soft d-none">
                        <div class="box-icon-detail text-primary mt-1"><i class="fas fa-notes-medical"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm fw-bold">Recomendaciones:</span></div>
                        <div class="text-right" style="max-width: 55%;"><span id="txtDetalleRecomendaciones" class="text-muted-gray font-sm text-end d-block">--</span></div>
                    </div>
                    <div id="rowFechaCancelacion" class="d-flex align-items-center py-2 border-bottom-soft d-none">
                        <div class="box-icon-detail text-danger"><i class="far fa-calendar-times"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Fecha de cancelación:</span></div>
                        <div class="text-right"><span id="txtDetalleFechaCancelacion" class="text-dark-blue font-weight-bold font-sm">--/--/----</span></div>
                    </div>
                    <div id="rowMotivoCancelacion" class="d-flex align-items-center py-2 border-bottom-soft d-none">
                        <div class="box-icon-detail text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Motivo de cancelación:</span></div>
                        <div class="text-right"><span id="txtDetalleMotivoCancelacion" class="text-dark-blue font-weight-bold font-sm">--</span></div>
                    </div>
                    <div id="rowCreadaEl" class="d-flex align-items-center py-2 border-bottom-soft d-none">
                        <div class="box-icon-detail text-primary"><i class="far fa-clock"></i></div>
                        <div class="ml-3 flex-grow-1"><span class="text-muted-gray font-sm">Creada el:</span></div>
                        <div class="text-right"><span id="txtDetalleCreadaEl" class="text-dark-blue font-weight-bold font-sm">--/--/----</span></div>
                    </div>
                </div>

                <div id="bannerInfoModal" class="mt-4 p-3 d-flex align-items-start gap-3" style="border-radius: 12px; font-size: 13px;">
                    <i id="bannerIcono" class="fas fa-info-circle style-icon-banner" style="font-size: 16px; margin-top: 2px;"></i>
                    <div class="flex-grow-1">
                        <span id="bannerTextoTitle" class="d-block font-weight-bold line-height-1">Título Banner</span>
                        <span id="bannerTextoDesc" class="text-muted-gray d-block mt-1">Descripción contextual.</span>
                    </div>
                </div>
                <div id="wrapperHistorialReprogramaciones" class="mt-4 d-none">
                    <h6 class="fw-bold text-dark-blue mb-3 font-sm"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Historial de Cambios</h6>
                    <div id="timelineHistorial" class="ps-2" style="border-left: 2px solid #e2e8f0; position: relative;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 500; font-size: 14px; padding: 8px 20px; border: 1px solid #e2e8f0;">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>
<script src="{{ asset('js/paciente/mis_citas.js') }}?v={{ filemtime(public_path('js/paciente/mis_citas.js')) }}"></script>
@endpush