@extends('layouts.paciente')

@section('titulo', 'Facturación y Pagos - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/pagos.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h2 class="h5 fw-bold text-dark-blue m-0">Facturación y Pagos</h2>
        <p class="text-muted small m-0">Consulta tus comprobantes, saldos y registra abonos en línea.</p>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm p-3 h-100 rounded-4">
                <span class="text-muted fw-semibold font-sm">Precio Total</span>
                <h3 class="fw-bold text-primary mt-2">${{ number_format($resumen['total_tratamientos'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm p-3 h-100 rounded-4">
                <span class="text-muted fw-semibold font-sm">Abono Total</span>
                <h3 class="fw-bold text-success mt-2">${{ number_format($resumen['total_pagado'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm p-3 h-100 rounded-4">
                <span class="text-muted fw-semibold font-sm">Deuda Total</span>
                <h3 class="fw-bold text-danger mt-2">${{ number_format($resumen['saldo_pendiente'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Tabla de Facturas -->
    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
        <h3 class="fs-6 fw-bold text-dark-blue mb-4">Historial de Facturación</h3>
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaPagos">
                <thead class="table-light">
                    <tr>
                        <th class="font-xs text-muted border-0">Factura</th>
                        <th class="font-xs text-muted border-0">Fecha</th>
                        <th class="font-xs text-muted border-0">Tratamiento</th>
                        <th class="font-xs text-muted border-0">Total</th>
                        <th class="font-xs text-muted border-0">Abonado</th>
                        <th class="font-xs text-muted border-0">Pendiente</th>
                        <th class="font-xs text-muted border-0">Estado</th>
                        <th class="font-xs text-muted border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facturas as $f)
                        @php
                            $total = floatval($f['TOTAL']);
                            $abonado = floatval($f['MONTO_PAGADO']);
                            $deuda = max(0, $total - $abonado);
                            $estado = $f['ESTADO_CALCULADO'];
                            $badge = match($estado) {
                                'PAGADA'  => 'bg-success-subtle text-success',
                                'ABONADA' => 'bg-primary-subtle text-primary',
                                default   => 'bg-danger-subtle text-danger'
                            };
                        @endphp
                        <tr>
                            <td class="font-sm fw-bold">FAC-{{ str_pad($f['ID_FACTURA'], 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="font-xs text-muted">{{ date('d/m/Y', strtotime($f['FECHA_EMISION'])) }}</td>
                            <td class="font-sm">{{ $f['PROCEDIMIENTO'] }}</td>
                            <td class="font-sm fw-bold">${{ number_format($total, 0, ',', '.') }}</td>
                            <td class="font-sm text-success">${{ number_format($abonado, 0, ',', '.') }}</td>
                            <td class="font-sm text-danger">${{ number_format($deuda, 0, ',', '.') }}</td>
                            <td><span class="badge font-xs px-2 py-1 {{ $badge }}">{{ $estado }}</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-ver-factura" 
                                            data-id="{{ $f['ID_FACTURA'] }}"
                                            data-total="{{ $total }}"
                                            data-abonado="{{ $abonado }}"
                                            data-deuda="{{ $deuda }}"
                                            data-procedimiento="{{ $f['PROCEDIMIENTO'] }}"
                                            data-doctor="{{ $f['DOCTOR'] }}"
                                            data-fecha="{{ date('d/m/Y', strtotime($f['FECHA_EMISION'])) }}">
                                        <i class="fa-solid fa-receipt me-1"></i> Ver
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted font-sm">No tienes facturas generadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle Factura y Pago -->
<div class="modal fade" id="modalVerFactura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark-blue">Comprobante de Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <span class="text-muted font-xs d-block">Número de Factura</span>
                        <strong class="font-sm text-dark-blue" id="modal-fac-numero">--</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted font-xs d-block">Procedimiento</span>
                        <strong class="font-sm text-dark-blue" id="modal-fac-procedimiento">--</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted font-xs d-block">Total Facturado</span>
                        <strong class="font-sm text-primary" id="modal-fac-total">$0</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted font-xs d-block">Total Abonado</span>
                        <strong class="font-sm text-success" id="modal-fac-abonado">$0</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted font-xs d-block">Saldo Pendiente</span>
                        <strong class="font-sm text-danger" id="modal-fac-deuda">$0</strong>
                    </div>
                </div>

                <!-- Opciones de descarga -->
                <div class="d-flex gap-2 pt-3 border-top">
                    <a href="#" id="btn-modal-excel" class="btn btn-outline-success btn-sm font-sm"><i class="fa-solid fa-file-excel me-1"></i> Exportar Excel</a>
                    <a href="#" id="btn-modal-print" target="_blank" class="btn btn-outline-secondary btn-sm font-sm"><i class="fa-solid fa-print me-1"></i> Imprimir Recibo</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/paciente/pagos.js') }}"></script>
@endpush