@extends('layouts.odontologo')

@section('titulo', 'Odonto Estética - Mis Finanzas')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/odontologo/facturacion_odon.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endpush

@section('contenido')
<main class="facturacion-content">
    <section class="kpi-metrics-row">
        <div class="metric-card-kpi green-kpi">
            <div class="kpi-icon-container"><i class="fa-solid fa-money-bill-wave"></i></div>
            <div class="kpi-data">
                <span class="kpi-title">Total Pagado</span>
                <span class="kpi-number">${{ number_format($kpis['total_pagado'], 0, ',', '.') }}</span>
                <span class="kpi-anchor">Dinero ya liquidado</span>
            </div>
        </div>
        
        <div class="metric-card-kpi blue-kpi">
            <div class="kpi-icon-container"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <div class="kpi-data">
                <span class="kpi-title">Saldo Pendiente</span>
                <span class="kpi-number">${{ number_format($kpis['saldo_pendiente'], 0, ',', '.') }}</span>
                <span class="kpi-anchor">Por cobrar a la clínica</span>
            </div>
        </div>
        
        <div class="metric-card-kpi red-kpi">
            <div class="kpi-icon-container"><i class="fa-solid fa-tooth"></i></div>
            <div class="kpi-data">
                <span class="kpi-title">Tratamientos</span>
                <span class="kpi-number">{{ $kpis['total_tratamientos'] }}</span>
                <span class="kpi-anchor">Procedimientos realizados</span>
            </div>
        </div>
    </section>

    <section class="billing-grid-layout">
        <div class="panel-card">
            
            <div class="tabs-container">
                <button class="tab-btn active" data-tab="comisiones">
                    <i class="fa-solid fa-list-check"></i> Tratamientos & Comisiones
                </button>
                <button class="tab-btn" data-tab="pagos">
                    <i class="fa-solid fa-wallet"></i> Historial de Pagos
                </button>
            </div>

            <div id="tab-comisiones" class="tab-content active">
                <table id="tablaComisiones" class="display responsive nowrap data-invoice-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Procedimiento</th>
                            <th>Precio Tratamiento</th>
                            <th>% Comisión</th>
                            <th>Ganancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($comisiones))
                            @foreach($comisiones as $c)
                                <tr>
                                    <td>{{ $c['fecha'] }}</td>
                                    <td>
                                        <div class="pacient-meta-cell">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($c['paciente']) }}&background=0d6efd&color=fff&rounded=true" alt="Avatar">
                                            <div class="meta-txt"><span>{{ $c['paciente'] }}</span></div>
                                        </div>
                                    </td>
                                    <td>{{ $c['procedimiento'] }}</td>
                                    <td>${{ number_format($c['monto'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($c['porcentaje'], 2) }}%</td>
                                    <td class="bold-cell text-success" data-ganancia="{{ $c['ganancia'] }}">
                                        ${{ number_format($c['ganancia'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="footer-summary-row">
                            <td colspan="4"></td>
                            <td class="bold-cell" style="text-align: right;">Total Ganancia Filtrada:</td>
                            <td class="total-final-sum" id="totalGananciaFooter">$0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="tab-pagos" class="tab-content" style="display: none;">
                <table id="tablaPagos" class="display responsive nowrap data-invoice-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Mes Liquidado</th>
                            <th>Fecha de Pago</th>
                            <th>Producción Base</th>
                            <th>% Promedio Aplicado</th>
                            <th>Monto Pagado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($pagos))
                            @foreach($pagos as $p)
                                <tr>
                                    <td class="bold-cell">{{ $p['mes'] }}</td>
                                    <td>{{ $p['fecha_pago'] }}</td>
                                    <td>${{ number_format($p['produccion'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($p['porcentaje'], 2) }}%</td>
                                    <td class="bold-cell text-success">
                                        ${{ number_format($p['pagado'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </section>
</main>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="{{ asset('js/odontologo/facturacion.js') }}"></script>
@endpush
