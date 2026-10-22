@extends('layouts.paciente')

@section('titulo', 'Agendar Cita - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/pedir_cita.css') }}?v={{ filemtime(public_path('css/paciente/pedir_cita.css')) }}">
@endpush

@section('contenido')
<div class="card border-0 p-3 mb-4 shadow-sm rounded-4">
    <div class="d-flex align-items-center justify-content-between px-md-4">
        <div class="d-flex align-items-center gap-2" id="step-indicator-1">
            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
            <span class="text-primary fw-bold small">Seleccionar servicio</span>
        </div>
        <div class="flex-grow-1 mx-3 border-bottom border-light-subtle"></div>
        <div class="d-flex align-items-center gap-2" id="step-indicator-2">
            <span class="badge bg-secondary-subtle text-muted rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
            <span class="text-muted small">Elegir fecha y hora</span>
        </div>
        <div class="flex-grow-1 mx-3 border-bottom border-light-subtle"></div>
        <div class="d-flex align-items-center gap-2" id="step-indicator-3">
            <span class="badge bg-secondary-subtle text-muted rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
            <span class="text-muted small">Confirmar cita</span>
        </div>
    </div>
</div>

{{-- SELECTOR DE PACIENTE: ¿Para quién es la cita? --}}
<div class="card border-0 p-3 mb-4 shadow-sm rounded-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-md-2">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-hospital-user fs-5"></i>
            </div>
            <div>
                <span class="font-xs text-muted d-block">¿Para quién es la cita?</span>
                <label for="select-paciente-id" class="fw-bold text-dark-blue fs-6 m-0">Seleccionar Paciente</label>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0" style="min-width: 320px;">
            <select name="paciente_id" id="select-paciente-id" form="form-agendar-cita" class="form-select font-sm fw-semibold rounded-pill border-primary-subtle shadow-sm py-2 px-3">
                @if(isset($pacientesDisponibles))
                    @foreach ($pacientesDisponibles as $p)
                        <option value="{{ $p['id'] }}" 
                                data-nombre="{{ $p['nombre'] }}"
                                data-tipo="{{ $p['tipo'] }}"
                                {{ $p['id'] == ($pacienteSeleccionadoId ?? 0) ? 'selected' : '' }}>
                            {{ $p['label'] }}
                        </option>
                    @endforeach
                @endif
            </select>
            <a href="/paciente/perfil" class="btn btn-outline-primary btn-sm rounded-pill font-xs text-nowrap px-3 py-2" title="Gestionar familiares en el perfil">
                <i class="fa-solid fa-plus me-1"></i> Familiar
            </a>
        </div>
    </div>
</div>

<form id="form-agendar-cita" action="/paciente/citas/guardar" method="POST">
    @csrf
    <input type="hidden" id="input-procedimiento-id" name="procedimiento_id" value="">
    <input type="hidden" id="input-horario-id" name="horario_id" value="">
    <input type="hidden" id="input-fecha-seleccionada" name="fecha_cita" value="">
    <input type="hidden" id="input-hora-seleccionada" name="hora_cita" value="">

    <div class="row g-4">
        {{-- PASO 1: Servicio --}}
        <div class="col-1024-4 col-xl-4 col-lg-6">
            <div class="card border-0 p-4 h-100 shadow-sm rounded-4">
                <h3 class="fs-6 fw-bold text-dark-blue mb-4">Selecciona el servicio</h3>
                
                <div class="d-flex flex-column gap-3">
                    @foreach ($procedimientos as $proc)
                        <label class="service-card p-3 rounded-3 d-flex align-items-center position-relative"
                            data-id="{{ $proc['ID_PROCEDIMIENTO'] }}"
                            data-nombre="{{ $proc['NOMBRE_PROCEDIMIENTO'] }}"
                            data-precio="${{ number_format($proc['COSTO'], 0, ',', '.') }}"
                            data-duracion="{{ $proc['TIEMPO_ESTIMADO'] }} minutos"
                            data-especialidad="{{ $proc['ESPECIALIDAD_ID_ESPECIALIDAD'] }}">
                            <input type="radio" name="servicio_radio" class="d-none" value="{{ $proc['ID_PROCEDIMIENTO'] }}">
                            <span class="custom-radio-circle me-3"></span>
                            <div class="bg-primary-light text-primary p-2 rounded-2 me-3">
                                <i class="fa-solid fa-tooth"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-6 fw-bold m-0 text-dark-blue">{{ $proc['NOMBRE_PROCEDIMIENTO'] }}</h4>
                                <p class="m-0 text-muted font-xs">{{ $proc['DESCRIPCION'] }}</p>
                            </div>
                            <span class="fw-bold text-primary ms-2 font-sm">
                                ${{ number_format($proc['COSTO'], 0, ',', '.') }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <button type="button" id="btn-continuar-servicio" class="btn btn-primary w-100 py-2-5 mt-4 fw-bold">Continuar</button>
            </div>
        </div>

        {{-- PASO 2: Fecha y Hora --}}
        <div class="col-1024-4 col-xl-4 col-lg-6">
            <div class="card border-0 p-4 h-100 shadow-sm rounded-4">
                <h3 class="fs-6 fw-bold text-dark-blue mb-4">Selecciona la fecha y hora</h3>
                
                <div class="border rounded-3 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" id="prev-month" class="btn btn-link btn-sm p-0 text-muted"><i class="fa-solid fa-chevron-left"></i></button>
                        <span id="current-month" class="fw-bold text-dark-blue small"></span>
                        <button type="button" id="next-month" class="btn btn-link btn-sm p-0 text-muted"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <div class="calendar-days-names d-grid text-center mb-2 font-xs text-muted fw-semibold" style="grid-template-columns: repeat(7, 1fr);">
                        <span>Do</span><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sa</span>
                    </div>
                    <div class="calendar-dates-grid d-grid text-center gap-1 font-xs" id="calendar-days-container" style="grid-template-columns: repeat(7, 1fr);"></div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark-blue font-xs">Horas disponibles</span>
                        <span class="font-xs text-muted" id="selected-date-label">Selecciona una fecha</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2" id="contenedor-horarios" data-horarios="{{ json_encode($horarios) }}">
                        <p class="text-muted font-xs m-0 py-2">Selecciona un día en el calendario.</p>
                    </div>
                </div>

                <div class="mb-4" id="contenedor-odontologos-wrapper" style="display: none;">
                    <span class="fw-bold text-dark-blue font-xs d-block mb-2">Odontólogo asignado</span>
                    <div class="d-flex flex-column gap-2" id="contenedor-odontologos"></div>
                </div>

                <div class="d-flex gap-2 mt-auto">
                    <button type="button" id="btn-volver-fecha" class="btn btn-light w-50 py-2-5 fw-semibold font-sm">Atrás</button>
                    <button type="button" id="btn-continuar-fecha" class="btn btn-primary w-50 py-2-5 fw-bold font-sm">Continuar</button>
                </div>
            </div>
        </div>

        {{-- PASO 3: Resumen y Confirmación --}}
        <div class="col-1024-4 col-xl-4 col-lg-12">
            <div class="card border-0 p-4 h-100 shadow-sm rounded-4 d-flex flex-column justify-content-between">
                <div>
                    <h3 class="fs-6 fw-bold text-dark-blue mb-4">Resumen de la cita</h3>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="summary-item p-3 rounded-3 bg-light">
                            <span class="text-muted font-xs d-block">Paciente</span>
                            <strong class="text-dark-blue font-sm" id="summary-patient">
                                {{ collect($pacientesDisponibles ?? [])->firstWhere('id', $pacienteSeleccionadoId ?? 0)['nombre'] ?? 'Titular' }}
                            </strong>
                        </div>
                        <div class="summary-item p-3 rounded-3 bg-light">
                            <span class="text-muted font-xs d-block">Tratamiento</span>
                            <strong class="text-dark-blue font-sm" id="summary-service">--</strong>
                        </div>
                        <div class="summary-item p-3 rounded-3 bg-light">
                            <span class="text-muted font-xs d-block">Fecha y Hora</span>
                            <strong class="text-dark-blue font-sm" id="summary-date">--</strong>
                            <span class="font-xs text-primary d-block mt-1 fw-bold" id="summary-time">--</span>
                        </div>
                        <div class="summary-item p-3 rounded-3 bg-light">
                            <span class="text-muted font-xs d-block">Odontólogo</span>
                            <strong class="text-dark-blue font-sm" id="summary-doctor">--</strong>
                        </div>
                        <div class="summary-item p-3 rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted font-xs d-block">Valor del servicio</span>
                                <strong class="fs-5 text-primary" id="summary-price">$0</strong>
                            </div>
                            <span class="badge bg-primary-subtle text-primary font-xs" id="summary-duration">0 min</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" id="btn-volver-resumen" class="btn btn-light w-50 py-2-5 fw-semibold font-sm">Atrás</button>
                    <button type="submit" id="btn-confirmar-final" class="btn btn-primary w-50 py-2-5 fw-bold font-sm">Confirmar Cita</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/paciente/pedir_cita.js') }}?v={{ filemtime(public_path('js/paciente/pedir_cita.js')) }}"></script>
@endpush