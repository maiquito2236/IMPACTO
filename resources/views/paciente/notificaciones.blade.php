@extends('layouts.paciente')

@section('titulo', 'Notificaciones - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/notificaciones.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="h5 fw-bold text-dark-blue m-0">Notificaciones</h2>
        <p class="text-muted small m-0">Mantente al día con recordatorios de citas, pagos y avisos clínicos.</p>
    </div>

    <!-- Tabs y Acciones -->
    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-tab-notif active" data-tab="todas">Todas ({{ $totalAlertas }})</button>
                <button type="button" class="btn btn-sm btn-tab-notif" data-tab="sin-leer">Sin leer ({{ $sinLeer }})</button>
                <button type="button" class="btn btn-sm btn-tab-notif" data-tab="leidas">Leídas ({{ $leidas }})</button>
            </div>
            @if ($sinLeer > 0)
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-mark-all">
                    <i class="fa-solid fa-check-double me-1"></i> Marcar todas como leídas
                </button>
            @endif
        </div>

        <div class="d-flex flex-column gap-3" id="contenedor-notificaciones">
            @forelse ($notificaciones as $n)
                <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center notif-item {{ $n['ESTADO'] === 'NO_LEIDA' ? 'bg-primary-subtle border-primary-subtle' : 'bg-white' }}"
                     data-id="{{ $n['ID_NOTIFICACIONES'] }}"
                     data-estado="{{ $n['ESTADO'] }}">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-circle {{ $n['ESTADO'] === 'NO_LEIDA' ? 'bg-primary text-white' : 'bg-light text-muted' }}" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa-solid fa-bell font-xs"></i>
                        </div>
                        <div>
                            <span class="badge bg-secondary-subtle text-dark font-xs mb-1">{{ $n['TIPO_NOMBRE'] }}</span>
                            <p class="m-0 font-sm text-dark-blue fw-medium">{{ $n['MENSAJE'] }}</p>
                            <span class="font-xs text-muted">{{ date('d/m/Y h:i A', strtotime($n['FECHA_ENVIO'])) }}</span>
                        </div>
                    </div>
                    @if ($n['ESTADO'] === 'NO_LEIDA')
                        <button type="button" class="btn btn-sm btn-light border btn-marcar-individual" data-id="{{ $n['ID_NOTIFICACIONES'] }}" title="Marcar como leída">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-bell-slash fs-2 mb-2"></i>
                    <p class="font-sm m-0">No tienes notificaciones por el momento.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/paciente/notificaciones.js') }}"></script>
@endpush