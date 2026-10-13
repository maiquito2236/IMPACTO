@extends('layouts.paciente')

@section('titulo', 'Mis Tratamientos - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/historial_clinico.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-3 rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-light text-primary" style="width: 56px; height: 56px;">
                    <i class="fa-solid fa-stethoscope fs-4"></i>
                </div>
                <div>
                    <h2 class="h5 fw-bold text-dark-blue m-0">Mis Tratamientos</h2>
                    <p class="text-muted small m-0 mt-1">Consulta el historial de todas tus atenciones y procedimientos.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card-custom p-3 rounded-4 bg-white border shadow-sm h-100 d-flex gap-3 align-items-center">
                <div class="p-3 rounded-3 bg-primary-light text-primary fs-4"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <span class="text-muted font-xs d-block fw-semibold">Consultas</span>
                    <strong class="h4 fw-bold text-dark-blue m-0">{{ $estadisticas['consultas'] }}</strong>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card-custom p-3 rounded-4 bg-white border shadow-sm h-100 d-flex gap-3 align-items-center">
                <div class="p-3 rounded-3 bg-purple-light text-purple fs-4"><i class="fa-solid fa-tooth"></i></div>
                <div>
                    <span class="text-muted font-xs d-block fw-semibold">Tratamientos</span>
                    <strong class="h4 fw-bold text-dark-blue m-0">{{ $estadisticas['tratamientos'] }}</strong>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card-custom p-3 rounded-4 bg-white border shadow-sm h-100 d-flex gap-3 align-items-center">
                <div class="p-3 rounded-3 bg-success-subtle text-success fs-4"><i class="fa-solid fa-heart-pulse"></i></div>
                <div>
                    <span class="text-muted font-xs d-block fw-semibold">Diagnósticos</span>
                    <strong class="h4 fw-bold text-dark-blue m-0">{{ $estadisticas['diagnosticos'] }}</strong>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card-custom p-3 rounded-4 bg-white border shadow-sm h-100 d-flex gap-3 align-items-center">
                <div class="p-3 rounded-3 bg-warning-light text-warning fs-4"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <span class="text-muted font-xs d-block fw-semibold">Última consulta</span>
                    <strong class="h5 fw-bold text-warning m-0">{{ $estadisticas['ultima_fecha'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Línea de tiempo -->
    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
        <h4 class="h6 fw-bold text-dark-blue mb-4">Línea de Tiempo de Tratamientos</h4>
        <div class="d-flex flex-column gap-3">
            @forelse ($citas as $cita)
                <div class="p-3 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary-light text-primary font-xs mb-1">{{ date('d/m/Y', strtotime($cita['FECHA_HORA'])) }}</span>
                        <h5 class="fs-6 fw-bold text-dark-blue m-0">{{ $cita['MOTIVO'] }}</h5>
                        <p class="text-muted font-xs m-0">Dr. {{ $cita['DOCTOR_NOMBRE'] }}</p>
                    </div>
                    <span class="badge bg-white border text-dark font-xs px-3 py-2">{{ $cita['NOMBRE_ESTADO'] }}</span>
                </div>
            @empty
                <p class="text-muted font-sm text-center py-4 m-0">No tienes tratamientos registrados aún.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/paciente/tratamientos.js') }}"></script>
@endpush