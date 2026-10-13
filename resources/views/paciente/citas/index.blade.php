@extends('layouts.paciente')

@section('titulo', 'Mis Citas - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/mis_citas.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="h5 fw-bold text-dark-blue m-0">Gestión de Citas</h2>
        <p class="text-muted small m-0">Revisa tus citas programadas, historial y cancelaciones.</p>
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
    <div class="row g-3 mb-4 align-items-center">
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
            </div>
        </div>
        
        <div class="col-xl-5 col-lg-12">
            <div class="input-group input-group-custom align-items-center px-2 bg-white rounded-3 border">
                <i class="fa-solid fa-magnifying-glass text-muted me-1"></i>
                <input type="text" id="search-appointment" class="form-control border-0 bg-transparent font-sm ps-1 shadow-none" placeholder="Buscar por motivo, odontólogo...">
            </div>
        </div>
    </div>

    <!-- Tabla de Citas -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover m-0 align-middle" id="tablaCitas">
                <thead class="table-light">
                    <tr>
                        <th class="font-xs text-muted border-0 px-3 py-3">Fecha</th>
                        <th class="font-xs text-muted border-0 py-3">Hora</th>
                        <th class="font-xs text-muted border-0 py-3">Doctor</th>
                        <th class="font-xs text-muted border-0 py-3">Motivo</th>
                        <th class="font-xs text-muted border-0 py-3">Estado</th>
                        <th class="font-xs text-muted border-0 text-center py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="citas-container">
                    @forelse ($citas as $cita)
                        @php
                            $ts = strtotime($cita['FECHA_HORA']);
                            $dia = date('d', $ts);
                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $mes = $meses[(int)date('m', $ts) - 1];
                            $anio = date('Y', $ts);
                            $hora = date('g:i A', $ts);

                            $estado = $cita['NOMBRE_ESTADO'] ?? 'Pendiente';
                            $badgeClass = match($estado) {
                                'Completada' => 'bg-success-subtle text-success border border-success-subtle',
                                'Cancelada'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'No asistió' => 'bg-secondary-subtle text-secondary border',
                                default      => 'bg-primary-subtle text-primary border border-primary-subtle'
                            };
                        @endphp
                        <tr class="cita-row" data-status="{{ $estado }}">
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-center p-2 rounded-3 bg-light" style="min-width: 50px;">
                                        <span class="d-block fw-bold text-dark-blue lh-1">{{ $dia }}</span>
                                        <span class="d-block font-xs text-muted text-uppercase">{{ $mes }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold font-sm text-dark-blue">{{ $hora }}</td>
                            <td>
                                <span class="d-block font-sm fw-medium text-dark-blue">{{ $cita['DOCTOR_NOMBRE'] }}</span>
                                <span class="font-xs text-muted">{{ $cita['NOMBRE_ESPECIALIDAD'] ?? 'General' }}</span>
                            </td>
                            <td class="font-sm text-muted">{{ $cita['MOTIVO'] }}</td>
                            <td>
                                <span class="badge rounded-pill font-xs px-3 py-1 {{ $badgeClass }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalle" data-id="{{ $cita['ID_CITA'] }}" title="Ver Detalles">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    @if ($cita['ESTADO_CITA_ID'] == 1 && strtotime($cita['FECHA_HORA']) >= time())
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-reprogramar" data-id="{{ $cita['ID_CITA'] }}" title="Reprogramar">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-cancelar" data-id="{{ $cita['ID_CITA'] }}" title="Cancelar">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted font-sm">No tienes citas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalDetalleCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark-blue">Detalle de la Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalDetalleBody">
                <div class="text-center py-3"><i class="fa-solid fa-spinner fa-spin fs-4 text-primary"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar Cita -->
<div class="modal fade" id="modalCancelarCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="/paciente/cita/cancelar" method="POST">
                @csrf
                <input type="hidden" name="id_cita" id="cancelar_id_cita">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">Cancelar Cita Médica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted font-sm">¿Estás seguro de cancelar esta cita? Por favor indicanos el motivo:</p>
                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold">Motivo principal *</label>
                        <select name="motivo_cancelacion" class="form-select font-sm" required>
                            <option value="" disabled selected>Selecciona un motivo</option>
                            <option value="Inconveniente personal / fuerza mayor">Inconveniente personal / fuerza mayor</option>
                            <option value="Motivos laborales">Motivos laborales</option>
                            <option value="Problemas de salud">Problemas de salud</option>
                            <option value="Deseo agendar otra fecha">Deseo agendar otra fecha</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold">Observaciones adicionales</label>
                        <textarea name="observaciones_paciente" class="form-control font-sm" rows="2" placeholder="Detalles adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light font-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger font-sm fw-bold">Confirmar Cancelación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reprogramar Cita -->
<div class="modal fade" id="modalReprogramarCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="/paciente/cita/reprogramar" method="POST">
                @csrf
                <input type="hidden" name="id_cita" id="reprogramar_id_cita">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark-blue">Reprogramar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold">Nueva Fecha *</label>
                        <input type="date" id="reprogramar_fecha" name="nueva_fecha" class="form-control font-sm" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold">Horarios Disponibles *</label>
                        <select id="reprogramar_horario" name="nuevo_id_horario" class="form-select font-sm" required disabled>
                            <option value="" disabled selected>Primero selecciona una fecha</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold">Motivo del cambio</label>
                        <input type="text" name="motivo" class="form-control font-sm" placeholder="Ej: Cambio de turno laboral">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light font-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary font-sm fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/paciente/mis_citas.js') }}"></script>
@endpush