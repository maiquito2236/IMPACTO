@extends('layouts.admin')

@section('titulo', 'Inicio - Panel Admin')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/administrador/dashboard.css') }}">
@endpush

@section('contenido')
    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-info">
                <p>Citas programadas</p>
                <h3>{{ $citasHoy ?? 0 }} <span class="sub-label">para hoy</span></h3>
                <a href="/admin/agenda">Ver calendario →</a>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon icon-green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-info">
                <p>Pacientes atendidos</p>
                <h3>{{ $pacientesAtendidos ?? 0 }} <span class="sub-label">hoy</span></h3>
                <a href="/admin/gestion_usuario">Ver pacientes →</a>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon icon-amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-info">
                <p>Ingresos del día</p>
                <h3>${{ number_format($ingresosHoy ?? 0, 0, ',', '.') }} <span class="sub-label">hoy</span></h3>
                <a href="/admin/facturacion">Ver facturación →</a>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="charts-grid">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Actividad de la clínica</h3>
                <div class="chart-legends-top">
                    <span class="legend-indicator blue">Citas</span>
                    <span class="legend-indicator green">Pacientes</span>
                    <span class="legend-indicator amber">Ingresos</span>
                    
                    <div class="chart-filter-dropdown">
                        <button class="date-dropdown dropdown-small" id="btn-chart-filter">
                            Últimos 7 días <i class="fas fa-chevron-down" style="margin-left: 6px; font-size: 10px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="line-chart-area">
                <svg viewBox="0 0 600 160" class="svg-chart">
                    <g stroke="#f1f5f9" stroke-width="1">
                        <line x1="0" y1="30" x2="600" y2="30" stroke-dasharray="4"/>
                        <line x1="0" y1="65" x2="600" y2="65" stroke-dasharray="4"/>
                        <line x1="0" y1="100" x2="600" y2="100" stroke-dasharray="4"/>
                        <line x1="0" y1="135" x2="600" y2="135" stroke-dasharray="4"/>
                    </g>
                    
                    <path d="{{ $pathCitas }}" fill="none" stroke="#0061ff" stroke-width="2.5"/>
                    <path d="{{ $pathPacientes }}" fill="none" stroke="#22c55e" stroke-width="2.5"/>
                    <path d="{{ $pathIngresos }}" fill="none" stroke="#f59e0b" stroke-width="2.5"/>
                    
                    {!! $puntosExtraCitas !!}
                </svg>
                <div class="x-axis">
                    @foreach($diasStr as $d)
                        <span>{{ $d }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3>Estado de citas de hoy</h3>
            </div>
            <div class="donut-chart-box">
                @php
                    $totalCitasE = 0;
                    $clases = ['Confirmada' => 'primary', 'Pendiente' => 'warning', 'Completada' => 'success', 'Cancelada' => 'danger'];
                    foreach($estadoCitasHoy as $est) {
                        $totalCitasE += $est->cantidad;
                    }
                @endphp

                <div class="donut-chart">
                    <div class="donut-center-text">
                        <span class="donut-title">Total</span>
                        <span class="donut-num">{{ $totalCitasE }}</span>
                    </div>
                </div>
                <ul class="chart-legend">
                    @if(count($estadoCitasHoy) > 0)
                        @foreach($estadoCitasHoy as $est)
                            @php
                                $porcentaje = $totalCitasE > 0 ? round(($est->cantidad / $totalCitasE) * 100) : 0;
                                $clase = $clases[$est->NOMBRE_ESTADO] ?? 'purple';
                            @endphp
                            <li class="legend-item"><span class="dot dot-{{ $clase }}"></span> {{ $est->NOMBRE_ESTADO }} <span class="legend-count">{{ $est->cantidad }} ({{ $porcentaje }}%)</span></li>
                        @endforeach
                    @else
                        <li class="legend-item text-muted">No hay citas para hoy</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Listas Inferiores -->
    <div class="bottom-grid">
        
        <!-- CARD: RECORDATORIOS IMPORTANTES -->
        <div class="list-container">
            <div class="list-header">
                <h3>Recordatorios importantes</h3>
            </div>
            <div class="list-items">
                @if(count($recordatorios) > 0)
                    @foreach($recordatorios as $index => $rec)
                    <div class="item-row clickable-row reminder-extra {{ $index >= 3 ? 'is-hidden' : '' }}">
                        <div class="item-left">
                            <div class="list-icon icon-blue-soft">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0061ff" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
                            </div>
                            <div class="item-text">
                                <h4>{{ $rec->MENSAJE }}</h4>
                                <p>{{ \Carbon\Carbon::parse($rec->FECHA_ENVIO)->format('d M, Y') }}</p>
                            </div>
                        </div>
                        <span class="chevron">›</span>
                    </div>
                    @endforeach
                @else
                    <p style="padding:15px; color:#64748b; font-size:14px; text-align:center;">No hay recordatorios pendientes</p>
                @endif
            </div>
            <a href="#" id="btn-toggle-reminders" class="bottom-list-link">Ver todos los recordatorios →</a>
        </div>

        <!-- CARD: PRÓXIMAS CITAS -->
        <div class="list-container">
            <div class="list-header">
                <h3>Próximas citas</h3>
            </div>
            <div class="list-items">
                @if(count($proximasCitas) > 0)
                    @foreach($proximasCitas as $index => $cita)
                    <div class="item-row clickable-row {{ $index >= 2 ? 'is-hidden' : '' }}">
                        <div class="item-left">
                            <span class="time-indicator">{{ $cita->fecha_corta }}</span>
                            <div class="avatar av-{{ ($index % 7) + 1 }}"></div>
                            <div class="item-text">
                                <h4>{{ $cita->paciente_nombre }}</h4>
                                <p>{{ $cita->MOTIVO ?? 'Consulta general' }}</p>
                            </div>
                        </div>
                        <span class="chevron">›</span>
                    </div>
                    @endforeach
                @else
                    <p style="padding:15px; color:#64748b; font-size:14px; text-align:center;">No hay citas próximas</p>
                @endif
            </div>
            <a href="#" id="btn-toggle-citas" class="bottom-list-link">Ver todas las citas →</a>
        </div>

    </div>
@endsection