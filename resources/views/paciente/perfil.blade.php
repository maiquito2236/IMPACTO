@extends('layouts.paciente')

@section('titulo', 'Mi Perfil - Odonto Estética')

@push('css')
<link rel="stylesheet" href="{{ asset('css/paciente/perfil.css') }}?v={{ filemtime(public_path('css/paciente/perfil.css')) }}">
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
            <div class="d-inline-flex align-items-center text-start profile-card-badge shadow-sm p-3 rounded-pill" style="background-color: #d3e3fd;">
                <div class="bg-white text-primary d-flex align-items-center justify-content-center rounded-circle me-3 shadow-xs" style="width:48px; height:48px;">
                    <i class="fa-solid fa-user fs-5" style="color: #0b57d0;"></i>
                </div>
                <div class="me-4 pe-2">
                    <h3 class="fs-6 fw-bold m-0 text-dark-blue">{{ $usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS'] }}</h3>
                    <p class="m-0 text-muted font-xs">{{ $usuario['TIPO_DOCUMENTO'] ?? 'C.C' }} {{ $usuario['NUMERO_DOCUMENTO'] ?? '' }}</p>
                </div>
                <a href="/paciente/perfil?mode=edit" class="btn btn-primary btn-sm fw-bold px-3 rounded-pill font-xs" style="background-color: #0b57d0; border-color: #0b57d0;">Actualizar Información</a>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <!-- 1. Datos Personales -->
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card border-0 p-4 h-100 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="fa-regular fa-user fs-5 text-dark-blue"></i>
                        <h3 class="fs-6 fw-bold text-dark-blue m-0">Datos Personales</h3>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <!-- Nombre -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-regular fa-user" style="color: #0b57d0; font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">Nombre</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ $usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS'] }}</strong>
                            </div>
                        </div>

                        <!-- Alergias -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-snowflake" style="color: #0b57d0; font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">Alergias</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ $textoAlergias ?? 'Ninguna' }}</strong>
                            </div>
                        </div>

                        <!-- Género -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                @if (strtoupper($usuario['GENERO'] ?? '') === 'FEMENINO')
                                    <i class="fa-solid fa-venus" style="color: #0b57d0; font-size: 1.15rem;"></i>
                                @else
                                    <i class="fa-solid fa-mars" style="color: #0b57d0; font-size: 1.15rem;"></i>
                                @endif
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">Género</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ ucfirst(strtolower($usuario['GENERO'] ?? 'Masculino')) }}</strong>
                            </div>
                        </div>

                        <!-- EPS -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-plus" style="color: #0b57d0; font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">EPS</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ $paciente['NOMBRE_EPS'] ?? 'SURA' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Datos de Contacto -->
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card border-0 p-4 h-100 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="fa-regular fa-envelope fs-5 text-dark-blue"></i>
                        <h3 class="fs-6 fw-bold text-dark-blue m-0">Datos de Contacto</h3>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <!-- Teléfono -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-phone" style="color: #0b57d0; font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">Teléfono</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ $usuario['TELEFONO'] }}</strong>
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-regular fa-envelope" style="color: #0b57d0; font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">Correo Electrónico</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ $usuario['CORREO'] }}</strong>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div class="d-flex align-items-center gap-3 info-row">
                            <div class="data-icon-box" style="background-color: #e8f0fe; min-width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-location-dot" style="color: #0b57d0; font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <span class="font-xs text-muted d-block" style="font-size: 0.76rem;">Dirección</span>
                                <strong class="text-dark-blue" style="font-size: 0.88rem;">{{ $usuario['DIRECCION'] ?? 'No registrada' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Seguridad y Ajustes -->
            <div class="col-xl-4 col-md-12 col-12">
                <div class="card border-0 p-4 h-100 shadow-sm rounded-4 bg-white d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="fa-solid fa-shield-halved fs-5 text-dark-blue"></i>
                            <h3 class="fs-6 fw-bold text-dark-blue m-0">Seguridad y Ajustes</h3>
                        </div>

                        <!-- Botón Cambiar Contraseña -->
                        <button type="button" class="btn btn-outline-light text-start w-100 py-3 px-3 rounded-3 d-flex align-items-center mb-3 border bg-white shadow-none info-row" style="border-color: #cbd5e1 !important;" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                            <i class="fa-solid fa-key me-3" style="color: #0b57d0; font-size: 1.1rem;"></i>
                            <span class="text-dark-blue fw-semibold" style="font-size: 0.88rem;">Cambiar Contraseña</span>
                        </button>

                        <!-- Notificaciones Switch -->
                        <div class="border rounded-3 p-3 mb-3 d-flex justify-content-between align-items-center bg-white" style="border-color: #cbd5e1 !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-regular fa-bell fs-5" style="color: #0b57d0;"></i>
                                <span class="text-dark-blue fw-semibold" style="font-size: 0.88rem;">Notificaciones</span>
                            </div>
                            <div class="form-check form-switch m-0 p-0" style="min-height: auto;">
                                <input class="form-check-input ms-0" type="checkbox" role="switch" id="toggleNotificaciones" checked style="cursor: pointer; width: 2.8em; height: 1.4em;">
                            </div>
                        </div>

                        <!-- Subsección: Exportar mis Datos Personales -->
                        <div class="border rounded-3 p-3 bg-white" style="border-color: #cbd5e1 !important;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fa-solid fa-download" style="color: #0b57d0;"></i>
                                <span class="fw-bold text-dark-blue" style="font-size: 0.82rem;">Exportar mis Datos Personales</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="/paciente/perfil/exportar?formato=excel" class="btn btn-export-excel w-100 py-2 rounded-2 text-start d-flex align-items-center px-3" onclick="this.blur()">
                                    <i class="fa-regular fa-file-excel me-2 fs-6"></i> Descargar reporte en Excel
                                </a>
                                <a href="/paciente/perfil/exportar?formato=pdf" target="_blank" class="btn btn-export-pdf w-100 py-2 rounded-2 text-start d-flex align-items-center px-3" onclick="this.blur()">
                                    <i class="fa-regular fa-file-pdf me-2 fs-6"></i> Descargar reporte en PDF
                                </a>
                                <a href="/paciente/perfil/exportar?formato=imprimir" target="_blank" class="btn btn-export-print w-100 py-2 rounded-2 text-start d-flex align-items-center px-3" onclick="this.blur()">
                                    <i class="fa-solid fa-print me-2 fs-6"></i> Imprimir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN: MI NÚCLEO FAMILIAR (HIJOS Y MENORES A CARGO) --}}
        <div class="card border-0 p-4 shadow-sm rounded-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:48px; height:48px;">
                        <i class="fa-solid fa-people-roof fs-5"></i>
                    </div>
                    <div>
                        <h3 class="fs-6 fw-bold text-dark-blue m-0">Mi Núcleo Familiar (Hijos y Menores a Cargo)</h3>
                        <p class="text-muted font-xs m-0">Menores de edad vinculados a tu cuenta para gestión de citas e historial clínico.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-primary rounded-pill font-xs fw-bold px-3 py-2 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalRegistrarHijo">
                    <i class="fa-solid fa-user-plus"></i> Registrar Hijo / Menor
                </button>
            </div>

            @if(isset($hijos) && count($hijos) > 0)
                <div class="row g-3">
                    @foreach ($hijos as $hijo)
                        @php
                            $edadCalculada = $hijo->FECHA_NACIMIENTO ? \Carbon\Carbon::parse($hijo->FECHA_NACIMIENTO)->age : null;
                        @endphp
                        <div class="col-lg-6 col-md-12">
                            <div class="border rounded-4 p-3 bg-light-subtle h-100 position-relative shadow-none transition-hover">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 44px; height: 44px; font-size: 1rem;">
                                            {{ strtoupper(substr($hijo->NOMBRES, 0, 1) . substr($hijo->APELLIDOS, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h4 class="fs-6 fw-bold text-dark-blue m-0">{{ $hijo->NOMBRES }} {{ $hijo->APELLIDOS }}</h4>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                <span class="badge bg-primary text-white font-xs px-2 py-1">{{ $hijo->PARENTESCO }}</span>
                                                @if($edadCalculada !== null)
                                                    <span class="badge bg-info-subtle text-info-emphasis font-xs px-2 py-1">
                                                        <i class="fa-solid fa-cake-candles me-1"></i>{{ $edadCalculada }} años
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <form action="{{ route('paciente.hijos.desvincular', $hijo->ID_PACIENTE_HAS_ACUDIENTE) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas desvincular a este menor de tu cuenta de acudiente?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" title="Desvincular menor" style="width: 32px; height: 32px;">
                                            <i class="fa-solid fa-trash-can font-xs"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="row g-2 pt-2 border-top font-xs text-muted mb-3">
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Documento:</span>
                                        <strong class="text-dark-blue">{{ $hijo->TIPO_DOCUMENTO }} {{ $hijo->NUMERO_DOCUMENTO }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">EPS:</span>
                                        <strong class="text-dark-blue">{{ $hijo->NOMBRE_EPS ?? 'Sin asignar' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Contacto Principal:</span>
                                        <span class="badge {{ $hijo->CONTACTO_PRINCIPAL === 'Si' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted' }}">
                                            {{ $hijo->CONTACTO_PRINCIPAL }}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-secondary d-block">Autoriza Tratamientos:</span>
                                        <span class="badge {{ $hijo->AUTORIZA_TRATAMIENTOS === 'Si' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-muted' }}">
                                            {{ $hijo->AUTORIZA_TRATAMIENTOS }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="/paciente/pedir_cita?paciente_id={{ $hijo->ID_PACIENTE }}" class="btn btn-primary btn-sm rounded-pill font-xs fw-bold px-3 w-100 text-center py-2">
                                        <i class="fa-regular fa-calendar-plus me-1"></i> Agendar Cita para {{ $hijo->NOMBRES }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 px-3 border border-dashed rounded-4 bg-light">
                    <div class="text-primary mb-3" style="font-size: 2.5rem;">
                        <i class="fa-solid fa-children"></i>
                    </div>
                    <h4 class="fs-6 fw-bold text-dark-blue mb-1">Aún no tienes menores registrados a tu cargo</h4>
                    <p class="text-muted font-xs mb-3 mx-auto" style="max-width: 460px;">
                        Si tienes hijos o estás a cargo de menores de 18 años, puedes registrarlos aquí. Podrás pedir citas a su nombre y consultar su evolución odontológica sin que ellos necesiten correo o contraseña.
                    </p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill font-xs fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalRegistrarHijo">
                        <i class="fa-solid fa-plus me-1"></i> Registrar Primer Menor
                    </button>
                </div>
            @endif
        </div>
    @else
        {{-- VISTA MODO EDICIÓN --}}
        <div class="form-update-container p-4 p-md-5 mb-4">
            <div class="form-profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2 class="text-center fw-bold text-dark-blue mb-1" style="font-size: 1.5rem;">Actualizar datos</h2>
            <p class="text-center subtitle">Completa los datos que deseas modificar en tu historial</p>

            <form action="/paciente/perfil/actualizar" method="POST">
                @csrf
                <div class="form-grid-custom">
                    <!-- Nombres -->
                    <div class="form-group-custom">
                        <label>Nombres:</label>
                        <div class="input-icon-container">
                            <i class="fa-regular fa-user icon-left"></i>
                            <input type="text" name="nombres" value="{{ $usuario['NOMBRES'] }}" required>
                        </div>
                    </div>

                    <!-- Apellidos -->
                    <div class="form-group-custom">
                        <label>Apellidos:</label>
                        <div class="input-icon-container">
                            <i class="fa-regular fa-user icon-left"></i>
                            <input type="text" name="apellidos" value="{{ $usuario['APELLIDOS'] }}" required>
                        </div>
                    </div>

                    <!-- Tipo de documento -->
                    <div class="form-group-custom">
                        <label>Tipo de documento:</label>
                        <div class="input-icon-container select-icon-box">
                            <i class="fa-regular fa-id-card icon-left"></i>
                            <select name="tipo_documento" required>
                                <option value="C.C" {{ ($usuario['TIPO_DOCUMENTO'] ?? '') == 'C.C' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="T.I" {{ ($usuario['TIPO_DOCUMENTO'] ?? '') == 'T.I' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                                <option value="C.E" {{ ($usuario['TIPO_DOCUMENTO'] ?? '') == 'C.E' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                <option value="PPT" {{ ($usuario['TIPO_DOCUMENTO'] ?? '') == 'PPT' ? 'selected' : '' }}>PPT</option>
                                <option value="R.C" {{ ($usuario['TIPO_DOCUMENTO'] ?? '') == 'R.C' ? 'selected' : '' }}>Registro Civil</option>
                            </select>
                        </div>
                    </div>

                    <!-- Número de documento -->
                    <div class="form-group-custom">
                        <label>Número de documento:</label>
                        <div class="input-icon-container">
                            <i class="fa-regular fa-address-card icon-left"></i>
                            <input type="text" name="numero_documento" value="{{ $usuario['NUMERO_DOCUMENTO'] }}" required>
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="form-group-custom">
                        <label>Teléfono:</label>
                        <div class="input-icon-container">
                            <i class="fa-solid fa-phone icon-left"></i>
                            <input type="text" name="telefono" value="{{ $usuario['TELEFONO'] }}" required pattern="[0-9]{10}">
                        </div>
                    </div>

                    <!-- Género -->
                    <div class="form-group-custom">
                        <label>Género:</label>
                        <div class="input-icon-container select-icon-box">
                            <i class="fa-regular fa-user icon-left"></i>
                            <select name="genero" required>
                                <option value="MASCULINO" {{ strtoupper($usuario['GENERO'] ?? '') == 'MASCULINO' ? 'selected' : '' }}>Masculino</option>
                                <option value="FEMENINO" {{ strtoupper($usuario['GENERO'] ?? '') == 'FEMENINO' ? 'selected' : '' }}>Femenino</option>
                                <option value="OTRO" {{ strtoupper($usuario['GENERO'] ?? '') == 'OTRO' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fecha de nacimiento -->
                    <div class="form-group-custom">
                        <label>Fecha de nacimiento:</label>
                        <div class="input-icon-container">
                            <i class="fa-regular fa-calendar icon-left"></i>
                            <input type="date" name="fecha_nacimiento" value="{{ $usuario['FECHA_NACIMIENTO'] ?? '' }}" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="form-group-custom">
                        <label>Dirección:</label>
                        <div class="input-icon-container">
                            <i class="fa-solid fa-map icon-left"></i>
                            <input type="text" name="direccion" value="{{ $usuario['DIRECCION'] ?? '' }}">
                        </div>
                    </div>

                    <!-- EPS -->
                    <div class="form-group-custom">
                        <label>EPS:</label>
                        <div class="input-icon-container select-icon-box">
                            <i class="fa-solid fa-plus icon-left"></i>
                            <select name="eps" required>
                                <option value="" disabled>Selecciona tu EPS</option>
                                @foreach ($epsLista as $eps)
                                    <option value="{{ $eps->ID_EPS }}" {{ ($paciente['EPS_ID_EPS'] ?? 1) == $eps->ID_EPS ? 'selected' : '' }}>
                                        {{ $eps->NOMBRE_EPS }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Enfermedad -->
                    <div class="form-group-custom">
                        <label>Enfermedad:</label>
                        <div class="input-icon-container select-icon-box">
                            <i class="fa-solid fa-user-doctor icon-left"></i>
                            <select name="enfermedad">
                                <option value="">Selecciona una enfermedad</option>
                                @foreach ($enfermedadesLista as $enf)
                                    <option value="{{ $enf->ID_CONDICION_MEDICA }}" {{ ($idEnfermedadPaciente ?? null) == $enf->ID_CONDICION_MEDICA ? 'selected' : '' }}>
                                        {{ $enf->NOMBRE_CONDICION }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- RH -->
                    <div class="form-group-custom">
                        <label>RH:</label>
                        <div class="input-icon-container select-icon-box">
                            <i class="fa-solid fa-droplet icon-left"></i>
                            <select name="rh">
                                <option value="">Selecciona tu RH</option>
                                @foreach (['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'AB-', 'O-'] as $rh)
                                    <option value="{{ $rh }}" {{ ($usuario['RH'] ?? '') == $rh ? 'selected' : '' }}>{{ $rh }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Alergias -->
                    <div class="form-group-custom">
                        <label>Alergias:</label>
                        <div class="input-icon-container select-icon-box">
                            <i class="fa-solid fa-snowflake icon-left"></i>
                            <select name="alergia">
                                <option value="">Selecciona una alergia</option>
                                @foreach ($alergiasLista as $ale)
                                    <option value="{{ $ale->ID_CONDICION_MEDICA }}" {{ ($idAlergiaPaciente ?? null) == $ale->ID_CONDICION_MEDICA ? 'selected' : '' }}>
                                        {{ $ale->NOMBRE_CONDICION }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Nombre contacto de emergencia -->
                    <div class="form-group-custom">
                        <label>Nombre contacto de emergencia:</label>
                        <div class="input-icon-container">
                            <i class="fa-solid fa-users icon-left"></i>
                            <input type="text" name="nombre_contacto_emergencia" value="{{ $paciente['NOMBRE_CONTACTO_EMERGENCIA'] ?? '' }}">
                        </div>
                    </div>

                    <!-- Número contacto de emergencia -->
                    <div class="form-group-custom">
                        <label>Número contacto de emergencia:</label>
                        <div class="input-icon-container">
                            <i class="fa-solid fa-phone icon-left"></i>
                            <input type="text" name="numero_contacto_emergencia" value="{{ $paciente['NUMERO_CONTACTO_EMERGENCIA'] ?? '' }}" pattern="[0-9]{10}">
                        </div>
                    </div>

                    <!-- Correo electrónico (ancho completo) -->
                    <div class="form-group-custom" style="grid-column: 1 / -1;">
                        <label>Correo electrónico:</label>
                        <div class="input-icon-container">
                            <i class="fa-regular fa-envelope icon-left"></i>
                            <input type="email" name="correo" value="{{ $usuario['CORREO'] }}" required>
                        </div>
                    </div>

                    <!-- Contraseña (ancho completo) -->
                    <div class="form-group-custom" style="grid-column: 1 / -1;">
                        <label>Contraseña:</label>
                        <div class="input-icon-container">
                            <i class="fa-solid fa-lock icon-left"></i>
                            <input type="password" id="passInput" name="password" placeholder="Nueva contraseña (deja en blanco si no deseas cambiarla)">
                            <i class="fa-regular fa-eye pass-toggle-eye" onclick="togglePasswordVisibility()"></i>
                        </div>
                        <small class="text-muted mt-1" style="font-size: 0.78rem;">Mínimo 8 caracteres, con mayúscula, número y carácter especial.</small>
                    </div>

                    <!-- Botones de Acción (ancho completo) -->
                    <div class="form-actions-group d-flex gap-3" style="grid-column: 1 / -1;">
                        <a href="/paciente/perfil" class="btn-cancel-danger flex-fill text-center">Cancelar</a>
                        <button type="submit" class="btn-submit-update flex-fill text-center">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

{{-- MODAL: REGISTRAR HIJO / MENOR DE EDAD --}}
<div class="modal fade" id="modalRegistrarHijo" tabindex="-1" aria-labelledby="modalRegistrarHijoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-child fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="modalRegistrarHijoLabel">Registrar Menor de Edad</h5>
                        <p class="m-0 text-white-50 font-xs">Ingresa los datos del menor para vincularlo a tu cuenta de acudiente</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('paciente.hijos.registrar') }}" method="POST" id="formRegistroHijo">
                @csrf
                <div class="modal-body p-4 bg-light-subtle">
                    <div class="alert alert-info py-2 px-3 rounded-3 font-xs d-flex align-items-center gap-2 mb-4">
                        <i class="fa-solid fa-shield-halved fs-6 text-primary"></i>
                        <span>Los menores de edad quedan protegidos bajo tu cuenta. No requieren correo electrónico ni contraseña propios.</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">Nombres del Menor *</label>
                            <input type="text" name="nombres" class="form-control font-sm" placeholder="Ej: Samuel David" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">Apellidos del Menor *</label>
                            <input type="text" name="apellidos" class="form-control font-sm" placeholder="Ej: Gómez Pérez" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">Tipo de Documento *</label>
                            <select name="tipo_documento" class="form-select font-sm" required id="selectTipoDocMenor">
                                <option value="R.C" selected>R.C - Registro Civil de Nacimiento (0 - 7 años)</option>
                                <option value="T.I">T.I - Tarjeta de Identidad (7 - 17 años)</option>
                                <option value="C.E">C.E - Cédula de Extranjería</option>
                                <option value="PPT">PPT - Permiso por Protección Temporal</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">Número de Documento *</label>
                            <input type="number" name="numero_documento" class="form-control font-sm" placeholder="Número de documento sin puntos" required min="10000">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label font-xs fw-bold text-dark">Fecha de Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="fechaNacMenor" class="form-control font-sm" required max="{{ date('Y-m-d') }}">
                            <div id="edadFeedback" class="font-xs mt-1 text-muted"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-xs fw-bold text-dark">Género *</label>
                            <select name="genero" class="form-select font-sm" required>
                                <option value="MASCULINO">Masculino</option>
                                <option value="FEMENINO">Femenino</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-xs fw-bold text-dark">Grupo Sanguíneo (RH)</label>
                            <select name="rh" class="form-select font-sm">
                                <option value="">Selecciona RH</option>
                                @foreach (['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'] as $rh)
                                    <option value="{{ $rh }}">{{ $rh }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">EPS del Menor *</label>
                            <select name="eps_id" class="form-select font-sm" required>
                                @foreach ($epsLista as $eps)
                                    <option value="{{ $eps->ID_EPS }}">{{ $eps->NOMBRE_EPS }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">Parentesco contigo *</label>
                            <select name="parentesco" class="form-select font-sm" required>
                                <option value="Madre" selected>Madre</option>
                                <option value="Padre">Padre</option>
                                <option value="Abuelo/a">Abuelo / Abuela</option>
                                <option value="Tío/a">Tío / Tía</option>
                                <option value="Tutor Legal">Tutor Legal</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">¿Eres el contacto principal?</label>
                            <select name="contacto_principal" class="form-select font-sm">
                                <option value="Si" selected>Sí (Avisos de citas y facturas principales)</option>
                                <option value="No">No (Contacto secundario)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-xs fw-bold text-dark">¿Autorizas procedimientos médicos?</label>
                            <select name="autoriza_tratamientos" class="form-select font-sm">
                                <option value="Si" selected>Sí (Autorizo consentimientos informados)</option>
                                <option value="No">No (Solo acompañamiento)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white p-3 border-top">
                    <button type="button" class="btn btn-light font-sm fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGuardarMenor" class="btn btn-primary font-sm fw-bold px-4 rounded-pill">
                        <i class="fa-solid fa-check me-1"></i> Guardar y Vincular Menor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: CAMBIAR CONTRASEÑA --}}
<div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-key fs-5" style="color: #0b57d0;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="modalCambiarPasswordLabel">Cambiar Contraseña</h5>
                        <p class="m-0 text-white-50 font-xs">Actualiza tu clave de acceso al portal de pacientes</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('paciente.perfil.cambiarPassword') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light-subtle">
                    <div class="alert alert-info py-2 px-3 rounded-3 font-xs d-flex align-items-center gap-2 mb-4">
                        <i class="fa-solid fa-shield-halved fs-6 text-primary"></i>
                        <span>La contraseña debe tener mínimo 8 caracteres, al menos 1 mayúscula, 1 número y 1 carácter especial (@, $, !, %, *, #, ?, &).</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-xs fw-bold text-dark">Nueva Contraseña *</label>
                        <div class="input-group">
                            <input type="password" name="password" id="inputModalPassword" class="form-control font-sm" required minlength="8" placeholder="Ingresa tu nueva contraseña">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleModalPass('inputModalPassword', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-xs fw-bold text-dark">Confirmar Nueva Contraseña *</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="inputModalPasswordConf" class="form-control font-sm" required minlength="8" placeholder="Repite tu nueva contraseña">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleModalPass('inputModalPasswordConf', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white p-3 border-top">
                    <button type="button" class="btn btn-light font-sm fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary font-sm fw-bold px-4 rounded-pill">
                        <i class="fa-solid fa-lock me-1"></i> Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/paciente/perfil.js') }}"></script>
<script>
    function toggleModalPass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fechaInput = document.getElementById('fechaNacMenor');
        const feedback = document.getElementById('edadFeedback');
        const btnGuardar = document.getElementById('btnGuardarMenor');
        const tipoDocSelect = document.getElementById('selectTipoDocMenor');

        if (fechaInput) {
            fechaInput.addEventListener('change', function () {
                if (!this.value) {
                    feedback.innerHTML = '';
                    btnGuardar.disabled = false;
                    return;
                }

                const fechaNac = new Date(this.value);
                const hoy = new Date();
                let edad = hoy.getFullYear() - fechaNac.getFullYear();
                const m = hoy.getMonth() - fechaNac.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
                    edad--;
                }

                if (edad < 0) {
                    feedback.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> La fecha no puede ser futura.</span>';
                    btnGuardar.disabled = true;
                } else if (edad >= 18) {
                    feedback.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Debe ser menor de 18 años (' + edad + ' años detectados). Los adultos deben registrarse por su cuenta.</span>';
                    btnGuardar.disabled = true;
                } else {
                    feedback.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Edad calculada: <strong>' + edad + ' años</strong>.</span>';
                    btnGuardar.disabled = false;

                    // Sugerir tipo de documento automáticamente según la edad en Colombia
                    if (edad >= 7 && tipoDocSelect.value === 'R.C') {
                        tipoDocSelect.value = 'T.I';
                    } else if (edad < 7 && tipoDocSelect.value === 'T.I') {
                        tipoDocSelect.value = 'R.C';
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection