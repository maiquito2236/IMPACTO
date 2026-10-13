@extends('layouts.paciente')

@section('titulo', 'Inicio - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/dashboard.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    <div class="row g-4 mb-4">
        <!-- Tarjeta 1: Próxima Cita -->
        <div class="col-12 col-md-4">
            <div class="dashboard-card p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="card-icon-box bg-primary-light text-primary">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                            <h3 class="fs-6 m-0 fw-bold text-dark-blue">Próxima cita</h3>
                        </div>
                    </div>
                    @if ($proximaCita)
                        @php
                            $fechaTimestamp = strtotime($proximaCita['FECHA_HORA']);
                            $dia = date('d', $fechaTimestamp);
                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $mes = $meses[(int)date('m', $fechaTimestamp) - 1];
                            $hora = date('h:i A', $fechaTimestamp);
                        @endphp
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="date-badge text-center p-2">
                                <span class="d-block fw-bold fs-3 text-primary lh-1">{{ $dia }}</span>
                                <span class="d-block font-xs fw-bold text-uppercase tracking-wider">{{ $mes }}</span>
                                <span class="d-block text-primary font-xs fw-bold mt-1">{{ $hora }}</span>
                            </div>
                            <div>
                                <h4 class="fs-6 m-0 fw-bold text-dark-blue">{{ $proximaCita['odontologo'] }}</h4>
                                <p class="m-0 text-muted font-xs">{{ $proximaCita['especialidad'] ?? 'Odontología General' }}</p>
                                <span class="badge bg-primary-light text-primary font-xs mt-1">
                                    {{ $proximaCita['procedimiento'] ?? $proximaCita['MOTIVO'] }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa-regular fa-calendar-xmark text-muted fs-3 mb-2 d-block"></i>
                            <p class="text-muted font-sm m-0">No tienes citas programadas</p>
                        </div>
                    @endif
                </div>
                <a href="/paciente/citas" class="btn btn-outline-primary btn-sm w-100 fw-semibold">Ver todas las citas</a>
            </div>
        </div>

        <!-- Tarjeta 2: Resumen Tratamientos -->
        <div class="col-12 col-md-4">
            <div class="dashboard-card p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="card-icon-box bg-purple-light text-purple">
                            <i class="fa-solid fa-tooth"></i>
                        </div>
                        <h3 class="fs-6 m-0 fw-bold text-dark-blue">Tratamientos</h3>
                    </div>
                    <div class="d-flex flex-column gap-2 mb-3">
                        @forelse ($tratamientos as $tratamiento)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                <span class="font-sm text-dark-blue fw-medium">{{ $tratamiento['nombre'] }}</span>
                                <span class="badge {{ strtolower($tratamiento['estado']) === 'completada' ? 'bg-success text-white' : 'bg-warning text-dark' }} font-xs">
                                    {{ $tratamiento['estado'] }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted font-xs text-center py-3 m-0">Sin tratamientos recientes</p>
                        @endforelse
                    </div>
                </div>
                <a href="/paciente/tratamientos" class="btn btn-outline-purple btn-sm w-100 fw-semibold">Ver tratamientos</a>
            </div>
        </div>

        <!-- Tarjeta 3: Pagos Pendientes -->
        <div class="col-12 col-md-4">
            <div class="dashboard-card p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="card-icon-box bg-warning-light text-warning">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <h3 class="fs-6 m-0 fw-bold text-dark-blue">Pagos pendientes</h3>
                    </div>
                    <div class="text-center my-3">
                        <span class="fs-4 fw-bold {{ $totalPendiente > 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($totalPendiente, 0, ',', '.') }}
                        </span>
                        <p class="text-muted font-xs m-0 mt-1">Saldo total pendiente</p>
                    </div>
                </div>
                <a href="/paciente/pagos" class="btn btn-outline-warning btn-sm w-100 fw-semibold">Gestionar pagos</a>
            </div>
        </div>
    </div>

    <!-- Citas Recientes -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="dashboard-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fs-6 m-0 fw-bold text-dark-blue">Citas recientes</h3>
                    <a href="/paciente/citas" class="font-xs text-primary text-decoration-none fw-semibold">Ver historial</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th class="font-xs text-muted border-0">Fecha</th>
                                <th class="font-xs text-muted border-0">Doctor</th>
                                <th class="font-xs text-muted border-0">Motivo</th>
                                <th class="font-xs text-muted border-0">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($citasRecientes as $c)
                                <tr>
                                    <td class="font-xs fw-semibold">{{ date('d/m/Y h:i A', strtotime($c['FECHA_HORA'])) }}</td>
                                    <td class="font-xs">{{ $c['odontologo'] }}</td>
                                    <td class="font-xs text-muted">{{ $c['MOTIVO'] }}</td>
                                    <td>
                                        <span class="badge {{ strtolower($c['estado']) === 'completada' ? 'bg-success' : (strtolower($c['estado']) === 'cancelada' ? 'bg-danger' : 'bg-warning text-dark') }} font-xs">
                                            {{ $c['estado'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted font-xs py-3">No hay citas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Últimos Pagos -->
        <div class="col-12 col-lg-4">
            <div class="dashboard-card p-4">
                <h3 class="fs-6 m-0 fw-bold text-dark-blue mb-3">Últimos pagos</h3>
                <div class="d-flex flex-column gap-3">
                    @forelse ($ultimosPagos as $p)
                        <div class="p-3 rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <span class="d-block font-xs text-muted">{{ date('d/m/Y', strtotime($p['FECHA_PAGO'])) }}</span>
                                <strong class="font-sm text-dark-blue">{{ $p['concepto'] }}</strong>
                                <span class="d-block font-xs text-muted">{{ $p['METODO_PAGO'] }}</span>
                            </div>
                            <span class="fw-bold text-success font-sm">${{ number_format($p['MONTO'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-muted font-xs text-center py-3 m-0">No hay pagos registrados</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection