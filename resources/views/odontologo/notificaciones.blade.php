@extends('layouts.odontologo')

@section('titulo', 'Odonto Estética - Notificaciones')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/odontologo/notificaciones_odon.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('contenido')
<main class="main-content">
    <section class="filters-section">
        <div class="filter-group">
            <label for="filter-tipo">Tipo</label>
            <select id="filter-tipo" class="filter-input">
                <option value="todos">Todos</option>
                @foreach ($tiposNotificacion as $tipo)
                    <option value="{{ mb_strtoupper($tipo) }}">{{ mb_strtoupper($tipo) }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Fecha</label>
            <input type="date" id="filter-fecha" class="filter-input">
        </div>

        <button class="btn-clear" id="btn-reset-filters">
            <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar filtros
        </button>
    </section>

    <section class="notifications-card">
        <div class="tabs-header">
            <div class="tabs-list">
                <div class="tab-item active" data-tab="todas">Todas ({{ $totalAlertas }})</div>
                <div class="tab-item" data-tab="sin-leer">Sin leer ({{ $sinLeer }})</div>
                <div class="tab-item" data-tab="leidas">Leídas ({{ $totalAlertas - $sinLeer }})</div>
            </div>

            @if ($sinLeer > 0)
                <button class="mark-read" id="btn-mark-all">
                    <i class="fa-solid fa-check-double"></i> Marcar todas como leídas
                </button>
            @endif
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>FECHA Y HORA</th>
                        <th>TIPO</th>
                        <th>MENSAJE DEL SISTEMA</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="notifications-tbody">
                    @if(empty($notificaciones))
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:#777;">No tienes notificaciones en tu historial clínico.</td></tr>
                    @else
                        @foreach($notificaciones as $n)
                            @php
                                $isUnread = ($n['ESTADO'] === 'NO_LEIDA');
                                $tipoNombre = mb_strtoupper($n['TIPO_NOMBRE']);
                                
                                $icon = "fa-bell"; $catClass = "badge-gray"; $tipoText = "Sistema";
                                
                                if (str_contains($tipoNombre, 'CANCELADA')) {
                                    $icon = "fa-solid fa-calendar-xmark"; $catClass = "badge-alerta"; $tipoText = "Cita";
                                } elseif (str_contains($tipoNombre, 'CITA') || str_contains($tipoNombre, 'AGENDA')) {
                                    $icon = "fa-regular fa-calendar"; $catClass = "badge-agenda"; $tipoText = "Cita";
                                } elseif (str_contains($tipoNombre, 'ABONO')) {
                                    $icon = "fa-solid fa-money-bill-transfer"; $catClass = "badge-teal"; $tipoText = "Pago";
                                } elseif (str_contains($tipoNombre, 'PAGO') || str_contains($tipoNombre, 'COMISION')) {
                                    $icon = "fa-solid fa-dollar-sign"; $catClass = "badge-facturacion"; $tipoText = "Pago";
                                } elseif (str_contains($tipoNombre, 'HISTORIA CLINICA')) {
                                    $icon = "fa-solid fa-notes-medical"; $catClass = "badge-yellow"; $tipoText = "Historia Clínica";
                                } elseif (str_contains($tipoNombre, 'ACTUALIZACION') || str_contains($tipoNombre, 'DATOS')) {
                                    $icon = "fa-solid fa-user-pen"; $catClass = "badge-purple"; $tipoText = "Sistema";
                                }
                            @endphp
                            <tr class="{{ $isUnread ? 'row-unread' : '' }}" 
                                data-tipo="{{ $tipoNombre }}" 
                                data-estado="{{ $isUnread ? 'sin-leer' : 'leida' }}"
                                data-fecha="{{ date('Y-m-d', strtotime($n['FECHA_ENVIO'])) }}"
                                data-id="{{ $n['ID_NOTIFICACIONES'] }}">
                                
                                <td class="cell-fecha" style="white-space: nowrap;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div class="dot"></div>
                                        {{ date('d M, h:i A', strtotime($n['FECHA_ENVIO'])) }}
                                    </div>
                                </td>
                                <td class="cell-tipo">
                                    <span class="badge {{ $catClass }}" style="display:inline-flex; align-items:center; gap:6px;">
                                        <i class="{{ $icon }}"></i> {{ $tipoNombre }}
                                    </span>
                                </td>
                                <td><div class="msg-text" style="max-width: 100%; min-width: 250px;">{{ $n['MENSAJE'] }}</div></td>
                                <td>
                                    <span class="badge {{ $isUnread ? 'badge-status-unread' : 'badge-status-read' }}">
                                        {{ $isUnread ? 'Sin leer' : 'Leída' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    @if($isUnread)
                                        <button class="btn-action-read btn-marcar-una" title="Marcar como leída" style="background:none; border:none; color:#0f62fe; cursor:pointer; font-size: 18px;">
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
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/odontologo/notificaciones_odon.js') }}" defer></script>
@endpush
