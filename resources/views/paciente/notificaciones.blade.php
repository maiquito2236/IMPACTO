@extends('layouts.paciente')

@section('titulo', 'Notificaciones - Odonto Estética')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="{{ asset('css/paciente/global.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/odontologo/notificaciones_odon.css') }}?v={{ time() }}">
<style>
    .main-content-table {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .filters-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        align-items: end;
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="h5 fw-bold text-dark-blue m-0">Notificaciones</h2>
        <p class="text-muted small m-0">Mantente al día con toda la información importante.</p>
    </div>

    <div class="main-content-table mt-4">
        <!-- Filtros superiores -->
        <section class="filters-section">
            <div class="filter-group">
                <label for="filter-tipo" class="form-label font-xs fw-bold text-muted mb-1">Tipo</label>
                <select id="filter-tipo" class="filter-input form-select shadow-none">
                    <option value="todos">Todos</option>
                    @foreach ($tiposNotificacion as $tipo)
                        <option value="{{ mb_strtoupper($tipo) }}">{{ mb_strtoupper($tipo) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-fecha" class="form-label font-xs fw-bold text-muted mb-1">Fecha</label>
                <input type="date" id="filter-fecha" class="filter-input form-control shadow-none">
            </div>

            <button class="btn-clear" id="btn-reset-filters">
                <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar filtros
            </button>
        </section>

        <!-- Tarjeta de Notificaciones con Pestañas -->
        <section class="notifications-card">
            <div class="tabs-header">
                <div class="tabs-list">
                    <div class="tab-item active" data-tab="todas">Todas ({{ $totalAlertas }})</div>
                    <div class="tab-item" data-tab="sin-leer">Sin leer ({{ $sinLeer }})</div>
                    <div class="tab-item" data-tab="leidas">Leídas ({{ $leidas }})</div>
                </div>

                @if ($sinLeer > 0)
                    <button class="mark-read shadow-sm" id="btn-mark-all">
                        <i class="fa-solid fa-check-double text-primary"></i> Marcar todas como leídas
                    </button>
                @endif
            </div>

            <!-- Tabla de Notificaciones -->
            <div class="table-container" id="notifications-table-container">
                <table class="table mb-0 table-hover align-middle">
                    <thead class="table-light text-muted">
                        <tr>
                            <th class="border-0 font-xs fw-bold px-4 py-3">FECHA Y HORA</th>
                            <th class="border-0 font-xs fw-bold px-4 py-3">TIPO</th>
                            <th class="border-0 font-xs fw-bold px-4 py-3">MENSAJE</th>
                            <th class="border-0 font-xs fw-bold px-4 py-3">ESTADO</th>
                            <th class="border-0 font-xs fw-bold px-4 py-3 text-center">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody id="notifications-tbody">
                        @if (empty($notificaciones))
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-bell-slash fs-2 mb-2 d-block"></i>
                                    No tienes notificaciones registradas.
                                </td>
                            </tr>
                        @else
                            @foreach ($notificaciones as $n)
                                @php
                                    $isUnread = ($n['ESTADO'] === 'NO_LEIDA');
                                    $tipoNombre = mb_strtoupper($n['TIPO_NOMBRE'] ?? 'SISTEMA');

                                    $icon = "fa-solid fa-bell";
                                    $catClass = "badge-gray";
                                    $tipoText = "General";
                                    
                                    if (str_contains($tipoNombre, 'CANCELADA')) {
                                        $icon = "fa-solid fa-calendar-xmark"; $catClass = "badge-alerta"; $tipoText = "Cita";
                                    } elseif (str_contains($tipoNombre, 'RECORDATORIO') || str_contains($tipoNombre, 'CITA') || str_contains($tipoNombre, 'AGENDA')) {
                                        $icon = "fa-regular fa-calendar-check"; $catClass = "badge-agenda"; $tipoText = "Cita";
                                    } elseif (str_contains($tipoNombre, 'PRESUPUESTO')) {
                                        $icon = "fa-solid fa-file-medical"; $catClass = "badge-agenda"; $tipoText = "Presupuesto";
                                    } elseif (str_contains($tipoNombre, 'SALDO') || str_contains($tipoNombre, 'DEUDA')) {
                                        $icon = "fa-solid fa-file-invoice-dollar"; $catClass = "badge-yellow"; $tipoText = "Facturación";
                                    } elseif (str_contains($tipoNombre, 'ABONO')) {
                                        $icon = "fa-solid fa-money-bill-transfer"; $catClass = "badge-teal"; $tipoText = "Pago";
                                    } elseif (str_contains($tipoNombre, 'PAGO') || str_contains($tipoNombre, 'COMISION')) {
                                        $icon = "fa-solid fa-dollar-sign"; $catClass = "badge-facturacion"; $tipoText = "Pago";
                                    } elseif (str_contains($tipoNombre, 'HISTORIA CLINICA') || str_contains($tipoNombre, 'HISTORIAL')) {
                                        $icon = "fa-solid fa-notes-medical"; $catClass = "badge-yellow"; $tipoText = "Historia Clínica";
                                    } elseif (str_contains($tipoNombre, 'ACTUALIZACION') || str_contains($tipoNombre, 'DATOS')) {
                                        $icon = "fa-solid fa-user-pen"; $catClass = "badge-purple"; $tipoText = "Sistema";
                                    }
                                @endphp
                                <tr class="{{ $isUnread ? 'row-unread' : '' }}" 
                                    data-tipo="{{ htmlspecialchars($tipoNombre) }}" 
                                    data-estado="{{ $isUnread ? 'sin-leer' : 'leida' }}"
                                    data-fecha="{{ date('Y-m-d', strtotime($n['FECHA_ENVIO'])) }}"
                                    data-id="{{ $n['ID_NOTIFICACIONES'] }}"
                                    style="cursor: pointer;">
                                    
                                    <td class="cell-fecha px-4 py-3" style="white-space: nowrap;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <div class="dot"></div>
                                            {{ date('d M, h:i A', strtotime($n['FECHA_ENVIO'])) }}
                                        </div>
                                    </td>
                                    <td class="cell-tipo px-4 py-3">
                                        <span class="badge {{ $catClass }}" style="display:inline-flex; align-items:center; gap:6px;">
                                            <i class="{{ $icon }}"></i> {{ htmlspecialchars($tipoNombre) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="msg-text font-sm" style="max-width: 100%; min-width: 250px;">{{ htmlspecialchars($n['MENSAJE']) }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $isUnread ? 'badge-status-unread' : 'badge-status-read' }} font-xxs">
                                            {{ $isUnread ? 'Sin leer' : 'Leída' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3" style="text-align: center;">
                                        @if ($isUnread)
                                            <button class="btn-action-read btn-marcar-una p-0 m-0" title="Marcar como leída" style="background:none; border:none; color:#0f62fe; cursor:pointer; font-size: 18px;">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @else
                                            <span style="color:#198754; font-size: 18px;"><i class="fa-solid fa-circle-check"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.IS_PACIENTE_MODULE = true;
</script>
<script src="{{ asset('js/odontologo/notificaciones_odon.js') }}?v={{ time() }}"></script>
@endpush