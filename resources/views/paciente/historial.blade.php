@extends('layouts.paciente')

@section('titulo', 'Historia Clínica - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/tratamientos.css') }}">
<link rel="stylesheet" href="{{ asset('css/paciente/odontograma_paciente.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <input type="hidden" id="pacienteIdHidden" value="{{ $idPaciente ?? 0 }}">

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-3 rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-light text-primary" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-book-medical fs-4"></i>
                </div>
                <div>
                    <h2 class="h5 fw-bold text-dark-blue m-0">Historial Clínico Integral</h2>
                    <p class="text-muted small m-0 mt-1">Explora tu registro de atenciones, diagnósticos y evolución odontológica.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white">
        <div class="row g-3 align-items-center mb-4">
            <div class="col-12 col-sm-4">
                <h4 class="h6 fw-bold text-dark-blue m-0">Registro de Evoluciones</h4>
            </div>
            <div class="col-12 col-sm-5 col-md-4 col-xl-3 ms-sm-auto">
                <div class="input-group align-items-center px-2 bg-light rounded-3 border" style="height: 38px;">
                    <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
                    <input type="text" id="inputBuscarHistorial" class="form-control border-0 bg-transparent font-sm p-0 shadow-none" placeholder="Buscar por diagnóstico...">
                </div>
            </div>
            <div class="col-12 col-sm-3 col-xl-2 d-flex justify-content-sm-end">
                <select id="selectOrdenHistorial" class="form-select border shadow-none font-sm rounded-3" style="height: 38px;">
                    <option value="recientes">Más recientes</option>
                    <option value="antiguas">Más antiguas</option>
                </select>
            </div>
        </div>

        <div id="tbodyHistorial" class="d-flex flex-column gap-3">
            @forelse ($citas as $cita)
                @php
                    $ts = strtotime($cita['FECHA_HORA']);
                    $dia = date('d', $ts);
                    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    $mes = $meses[(int)date('m', $ts) - 1];
                    $anio = date('Y', $ts);
                    $horaFmt = date('h:i A', $ts);
                @endphp
                <div class="card border p-3 rounded-3 shadow-none item-historial">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="text-center p-2 rounded-3 bg-primary-light text-primary" style="min-width: 55px;">
                                <strong class="fs-5 d-block lh-1">{{ $dia }}</strong>
                                <span class="font-xs fw-bold text-uppercase">{{ $mes }}</span>
                            </div>
                            <div>
                                <h5 class="fs-6 fw-bold text-dark-blue m-0">{{ $cita['MOTIVO'] }}</h5>
                                <p class="text-muted font-xs m-0">Dr. {{ $cita['DOCTOR_NOMBRE'] }} &bull; {{ $cita['NOMBRE_ESPECIALIDAD'] ?? 'Odontología' }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border font-xs">{{ $horaFmt }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalle-historial" data-id="{{ $cita['ID_CITA'] }}">
                                <i class="fa-solid fa-eye me-1"></i> Detalles
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-file-medical fs-2 mb-2"></i>
                    <p class="font-sm m-0">No hay registros de atenciones clínicas.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Detalle Historial -->
<div class="modal fade" id="modalDetalleHistorial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark-blue">Detalle de Atención Clínica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalHistorialBody">
                <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin fs-4 text-primary"></i></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/paciente/historial_clinico.js') }}"></script>
@endpush