@extends('layouts.admin')

@section('titulo', 'Mi Perfil - Panel Admin')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/administrador/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/administrador/perfilAdmin.css') }}">
@endpush

@section('contenido')
    @if (!$modo_edicion)
        @if (session('exito_perfil'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('exito_perfil') }}
            </div>
        @endif

        <!-- BANNER SUPERIOR -->
        <div class="profile-banner">
            <div class="banner-info">
                <div class="icon-42 bg-blue-soft text-blue rounded-circle">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <h3>{{ ($usuario->NOMBRES ?? 'Admin') . ' ' . ($usuario->APELLIDOS ?? 'Sistema') }}</h3>
                    <p>{{ ($usuario->TIPO_DOCUMENTO ?? 'C.C') . ' ' . ($usuario->NUMERO_DOCUMENTO ?? '00000000') }}</p>
                </div>
            </div>
            <a href="/admin/perfil?mode=edit" class="btn-primary">
                <i class="fa-solid fa-pen"></i> Actualizar Información
            </a>
        </div>

        <!-- GRILLA 3 COLUMNAS -->
        <div class="profile-grid">
            <!-- Tarjeta 1: Datos Personales -->
            <div class="profile-card">
                <div class="card-header-title">
                    <i class="fa-regular fa-user text-blue"></i>
                    <h4>Datos Personales</h4>
                </div>
                <div class="card-body-list">
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-regular fa-id-card"></i></div>
                        <div class="info-text">
                            <strong>Tipo y Número de Documento</strong>
                            <span>{{ ($usuario->TIPO_DOCUMENTO ?? 'C.C') }} - {{ ($usuario->NUMERO_DOCUMENTO ?? 'No registrado') }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-solid fa-venus-mars"></i></div>
                        <div class="info-text">
                            <strong>Género</strong>
                            <span>{{ ucfirst(strtolower($usuario->GENERO ?? 'No definido')) }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-regular fa-calendar"></i></div>
                        <div class="info-text">
                            <strong>Fecha de Nacimiento</strong>
                            <span>{{ !empty($usuario->FECHA_NACIMIENTO) ? date('d/m/Y', strtotime($usuario->FECHA_NACIMIENTO)) : 'No registrada' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Datos de Contacto -->
            <div class="profile-card">
                <div class="card-header-title">
                    <i class="fa-regular fa-envelope text-blue"></i>
                    <h4>Datos de Contacto</h4>
                </div>
                <div class="card-body-list">
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-solid fa-phone"></i></div>
                        <div class="info-text">
                            <strong>Teléfono Celular</strong>
                            <span>{{ $usuario->TELEFONO ?? 'No registrado' }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-regular fa-envelope"></i></div>
                        <div class="info-text">
                            <strong>Correo Electrónico</strong>
                            <span class="text-underline text-blue">{{ $usuario->CORREO ?? 'No registrado' }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="info-text">
                            <strong>Dirección de Residencia</strong>
                            <span>{{ $usuario->DIRECCION ?? 'No registrada' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Seguridad y Ajustes -->
            <div class="profile-card">
                <div class="card-header-title">
                    <i class="fa-solid fa-shield-halved text-blue"></i>
                    <h4>Seguridad y Ajustes</h4>
                </div>
                <div class="card-body-list">
                    <a href="/admin/perfil?mode=edit#password-section" class="btn-outline-full">
                        <i class="fa-solid fa-lock-open text-blue"></i> Cambiar Contraseña
                    </a>
                    <div class="toggle-box mt-3">
                        <div class="toggle-left">
                            <i class="fa-regular fa-bell"></i>
                            <span>Notificaciones</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    @else

        @if (session('error_perfil'))
            <div class="alert-danger">
                <div class="alert-danger-content">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error_perfil') }}
                </div>
                <i class="fa-solid fa-xmark alert-close" onclick="this.parentElement.style.display='none';"></i>
            </div>
        @endif

        <!-- MODO EDICIÓN -->
        <div class="form-panel">
            <div class="form-header-center">
                <div class="icon-42 bg-blue text-white mx-auto mb-2 rounded-circle"><i class="fa-solid fa-user-tie"></i></div>
                <h2>Actualizar datos de Administrador</h2>
                <p>Modifica tus datos de acceso y contacto institucional.</p>
            </div>

            <form action="/admin/perfil/actualizar" method="POST">
                @csrf
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Nombres:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nombres" value="{{ $usuario->NOMBRES ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Apellidos:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="apellidos" value="{{ $usuario->APELLIDOS ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipo de documento:</label>
                        <div class="input-wrap select-wrap-icon">
                            <i class="fa-regular fa-id-card"></i>
                            <select name="tipo_documento" required>
                                <option value="C.C" {{ (($usuario->TIPO_DOCUMENTO ?? '') == 'C.C') ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="C.E" {{ (($usuario->TIPO_DOCUMENTO ?? '') == 'C.E') ? 'selected' : '' }}>Cédula de Extranjería</option>
                                <option value="PPT" {{ (($usuario->TIPO_DOCUMENTO ?? '') == 'PPT') ? 'selected' : '' }}>Permiso por Protección</option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Número de documento:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="number" name="numero_documento" value="{{ $usuario->NUMERO_DOCUMENTO ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Teléfono Celular:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <input type="tel" name="telefono" value="{{ $usuario->TELEFONO ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Género:</label>
                        <div class="input-wrap select-wrap-icon">
                            <i class="fa-solid fa-venus-mars"></i>
                            <select name="genero" required>
                                <option value="MASCULINO" {{ (($usuario->GENERO ?? '') == 'MASCULINO') ? 'selected' : '' }}>Masculino</option>
                                <option value="FEMENINO" {{ (($usuario->GENERO ?? '') == 'FEMENINO') ? 'selected' : '' }}>Femenino</option>
                                <option value="OTRO" {{ (($usuario->GENERO ?? '') == 'OTRO') ? 'selected' : '' }}>Otro</option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha de nacimiento:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" name="fecha_nacimiento" value="{{ $usuario->FECHA_NACIMIENTO ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dirección de Residencia:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <input type="text" name="direccion" value="{{ $usuario->DIRECCION ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-16">
                    <label>Correo electrónico institucional:</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="correo" value="{{ $usuario->CORREO ?? '' }}" required>
                    </div>
                </div>

                <div class="form-group mt-16" id="password-section">
                    <label>Contraseña:</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="passInput" name="password" placeholder="Nueva contraseña (deja en blanco si no deseas cambiarla)">
                        <i class="fa-regular fa-eye eye-btn" onclick="togglePasswordVisibility()"></i>
                    </div>
                </div>

                <div class="form-actions mt-32">
                    <a href="/admin/perfil" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </div>

    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/administrador/perfilAdmin.js') }}"></script>
@endpush
