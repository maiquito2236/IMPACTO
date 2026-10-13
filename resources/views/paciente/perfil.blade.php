@extends('layouts.paciente')

@section('titulo', 'Mi Perfil - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/perfil.css') }}">
@endpush

@section('contenido')
<div class="container-fluid p-0">
    @php
        $modoEdicion = request()->query('mode') === 'edit';
    @endphp

    @if (session('EXITO_PERFIL'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 small mb-4" role="alert">
            <span>{{ session('EXITO_PERFIL') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('ERROR_PERFIL'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 small mb-4" role="alert">
            <span>{{ session('ERROR_PERFIL') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (!$modoEdicion)
        {{-- VISTA MODO LECTURA --}}
        <section class="profile-banner p-4 shadow-sm text-center mb-4 border border-light-subtle rounded-4 bg-white">
            <h2 class="fs-6 fw-bold text-dark-blue mb-3">Mi Perfil - {{ $usuario['NOMBRES'] ?? 'Paciente' }}</h2>
            <div class="d-inline-flex align-items-center text-start profile-card-badge shadow-sm p-3 rounded-pill bg-light">
                <div class="bg-primary-light text-primary d-flex align-items-center justify-content-center rounded-circle me-3" style="width:50px; height:50px;">
                    <i class="fa-solid fa-user fs-5"></i>
                </div>
                <div class="me-4">
                    <h3 class="fs-6 fw-bold m-0 text-dark-blue">{{ $usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS'] }}</h3>
                    <p class="m-0 text-muted font-xs">{{ $usuario['TIPO_DOCUMENTO'] ?? 'C.C' }} {{ $usuario['NUMERO_DOCUMENTO'] ?? '' }}</p>
                </div>
                <a href="/paciente/perfil?mode=edit" class="btn btn-primary btn-sm fw-bold px-3 rounded-pill font-xs">Actualizar Información</a>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <!-- Datos Personales -->
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 p-4 h-100 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="fa-regular fa-user fs-5 text-dark-blue"></i>
                        <h3 class="fs-6 fw-bold text-dark-blue m-0">Datos Personales</h3>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="font-xs text-muted d-block">Nombre completo</span>
                            <strong class="font-sm text-dark-blue">{{ $usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS'] }}</strong>
                        </div>
                        <hr class="m-0 opacity-25">
                        <div>
                            <span class="font-xs text-muted d-block">Documento de identidad</span>
                            <strong class="font-sm text-dark-blue">{{ $usuario['TIPO_DOCUMENTO'] }} {{ $usuario['NUMERO_DOCUMENTO'] }}</strong>
                        </div>
                        <hr class="m-0 opacity-25">
                        <div>
                            <span class="font-xs text-muted d-block">Género</span>
                            <strong class="font-sm text-dark-blue">{{ ucfirst(strtolower($usuario['GENERO'] ?? 'No definido')) }}</strong>
                        </div>
                        <hr class="m-0 opacity-25">
                        <div>
                            <span class="font-xs text-muted d-block">Grupo Sanguíneo (RH)</span>
                            <strong class="font-sm text-dark-blue">{{ $usuario['RH'] ?? 'No registrado' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos de Contacto y EPS -->
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 p-4 h-100 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="fa-regular fa-address-book fs-5 text-dark-blue"></i>
                        <h3 class="fs-6 fw-bold text-dark-blue m-0">Contacto y EPS</h3>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="font-xs text-muted d-block">Correo electrónico</span>
                            <strong class="font-sm text-dark-blue">{{ $usuario['CORREO'] }}</strong>
                        </div>
                        <hr class="m-0 opacity-25">
                        <div>
                            <span class="font-xs text-muted d-block">Teléfono móvil</span>
                            <strong class="font-sm text-dark-blue">{{ $usuario['TELEFONO'] }}</strong>
                        </div>
                        <hr class="m-0 opacity-25">
                        <div>
                            <span class="font-xs text-muted d-block">Dirección de residencia</span>
                            <strong class="font-sm text-dark-blue">{{ $usuario['DIRECCION'] ?? 'No registrada' }}</strong>
                        </div>
                        <hr class="m-0 opacity-25">
                        <div>
                            <span class="font-xs text-muted d-block">Contacto de Emergencia</span>
                            <strong class="font-sm text-dark-blue">{{ $paciente['NOMBRE_CONTACTO_EMERGENCIA'] ?? 'Sin contacto' }} ({{ $paciente['NUMERO_CONTACTO_EMERGENCIA'] ?? '--' }})</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exportar Perfil -->
            <div class="col-xl-4 col-md-12">
                <div class="card border-0 p-4 h-100 shadow-sm rounded-4 bg-white d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="fa-solid fa-file-export fs-5 text-dark-blue"></i>
                            <h3 class="fs-6 fw-bold text-dark-blue m-0">Exportar Ficha</h3>
                        </div>
                        <p class="text-muted font-xs">Puedes descargar una copia oficial de tus datos personales registrados en el sistema.</p>
                    </div>
                    <div class="d-flex flex-column gap-2 mt-4">
                        <a href="/paciente/perfil/exportar?formato=excel" class="btn btn-outline-success font-sm fw-semibold py-2 rounded-3">
                            <i class="fa-solid fa-file-excel me-2"></i> Exportar a Excel
                        </a>
                        <a href="/paciente/perfil/exportar?formato=imprimir" target="_blank" class="btn btn-outline-secondary font-sm fw-semibold py-2 rounded-3">
                            <i class="fa-solid fa-print me-2"></i> Imprimir Ficha
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- VISTA MODO EDICIÓN --}}
        <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="h5 fw-bold text-dark-blue m-0">Editar Datos Personales</h3>
                    <p class="text-muted small m-0">Actualiza tu información de contacto y antecedentes.</p>
                </div>
                <a href="/paciente/perfil" class="btn btn-light font-sm fw-semibold">Cancelar</a>
            </div>

            <form action="/paciente/perfil/actualizar" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Nombres *</label>
                        <input type="text" name="nombres" class="form-control font-sm" value="{{ $usuario['NOMBRES'] }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control font-sm" value="{{ $usuario['APELLIDOS'] }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label font-xs fw-bold">Tipo Documento *</label>
                        <select name="tipo_documento" class="form-select font-sm" required>
                            <option value="C.C" {{ $usuario['TIPO_DOCUMENTO'] == 'C.C' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                            <option value="T.I" {{ $usuario['TIPO_DOCUMENTO'] == 'T.I' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                            <option value="C.E" {{ $usuario['TIPO_DOCUMENTO'] == 'C.E' ? 'selected' : '' }}>Cédula de Extranjería</option>
                            <option value="PPT" {{ $usuario['TIPO_DOCUMENTO'] == 'PPT' ? 'selected' : '' }}>PPT</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-xs fw-bold">Número Documento *</label>
                        <input type="text" name="numero_documento" class="form-control font-sm" value="{{ $usuario['NUMERO_DOCUMENTO'] }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-xs fw-bold">Teléfono *</label>
                        <input type="text" name="telefono" class="form-control font-sm" value="{{ $usuario['TELEFONO'] }}" required pattern="[0-9]{10}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Correo Electrónico *</label>
                        <input type="email" name="correo" class="form-control font-sm" value="{{ $usuario['CORREO'] }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">EPS *</label>
                        <select name="eps" class="form-select font-sm" required>
                            <option value="" disabled>Selecciona una EPS</option>
                            @foreach ($epsLista as $eps)
                                <option value="{{ $eps->ID_EPS }}" {{ ($paciente['EPS_ID_EPS'] ?? 1) == $eps->ID_EPS ? 'selected' : '' }}>
                                    {{ $eps->NOMBRE_EPS }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Dirección</label>
                        <input type="text" name="direccion" class="form-control font-sm" value="{{ $usuario['DIRECCION'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Grupo Sanguíneo (RH)</label>
                        <select name="rh" class="form-select font-sm">
                            <option value="">Selecciona RH</option>
                            @foreach (['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'AB-', 'O-'] as $rh)
                                <option value="{{ $rh }}" {{ ($usuario['RH'] ?? '') == $rh ? 'selected' : '' }}>{{ $rh }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Nombre Contacto Emergencia</label>
                        <input type="text" name="nombre_contacto_emergencia" class="form-control font-sm" value="{{ $paciente['NOMBRE_CONTACTO_EMERGENCIA'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Teléfono Contacto Emergencia</label>
                        <input type="text" name="numero_contacto_emergencia" class="form-control font-sm" value="{{ $paciente['NUMERO_CONTACTO_EMERGENCIA'] ?? '' }}">
                    </div>
                </div>

                <div class="border-top pt-4 mb-4">
                    <h5 class="fs-6 fw-bold text-dark-blue mb-3">Cambio de Contraseña (Opcional)</h5>
                    <div class="col-md-6">
                        <label class="form-label font-xs fw-bold">Nueva Contraseña</label>
                        <input type="password" name="password" class="form-control font-sm" placeholder="Dejar en blanco para mantener la actual">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary font-sm fw-bold px-4 py-2 rounded-3">Guardar Cambios</button>
            </form>
        </div>
    @endif
</div>
@endsection