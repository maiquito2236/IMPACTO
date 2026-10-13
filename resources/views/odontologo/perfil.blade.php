@extends('layouts.odontologo')

@section('titulo', 'Odonto Estética - Mi Perfil')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/odontologo/perfil_odon.css') }}">
@endpush

@section('contenido')
<main class="main-content">
    @php
        $modo_edicion = request('mode') === 'edit';
    @endphp

    <div class="po-container">

        @if(request('success'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i> Información actualizada correctamente.
            </div>
        @endif

        @if(request('error'))
            <div class="alert-danger">
                <div class="alert-danger-content">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ request('error') }}
                </div>
                <i class="fa-solid fa-xmark alert-close" onclick="this.parentElement.style.display='none';"></i>
            </div>
        @endif

        @if(!$modo_edicion)
            {{-- MODO VISTA (DETALLE) --}}
            <div class="profile-banner">
                <div class="banner-info">
                    <div class="icon-42 bg-blue-soft text-blue rounded-circle">
                        {{ strtoupper(substr($perfil['NOMBRES'] ?? 'O', 0, 1) . substr($perfil['APELLIDOS'] ?? 'D', 0, 1)) }}
                    </div>
                    <div>
                        <h3>{{ ($perfil['NOMBRES'] ?? '') . ' ' . ($perfil['APELLIDOS'] ?? '') }}</h3>
                        <p>{{ $perfil['TIPO_DOCUMENTO'] ?? 'C.C' }} {{ $perfil['NUMERO_DOCUMENTO'] ?? '00000000' }}</p>
                    </div>
                </div>
                <a href="{{ url('/odontologo/perfil?mode=edit') }}" class="btn-primary">
                    <i class="fa-solid fa-pen"></i> Actualizar Información
                </a>
            </div>

            <div class="profile-grid">
                
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
                                <span>{{ $perfil['TIPO_DOCUMENTO'] ?? 'C.C' }} - {{ $perfil['NUMERO_DOCUMENTO'] ?? 'No registrado' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="info-row">
                            <div class="icon-42 bg-light-gray"><i class="fa-solid fa-venus-mars"></i></div>
                            <div class="info-text">
                                <strong>Género</strong>
                                <span>{{ ucfirst(strtolower($perfil['GENERO'] ?? 'No definido')) }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="info-row">
                            <div class="icon-42 bg-light-gray"><i class="fa-regular fa-calendar"></i></div>
                            <div class="info-text">
                                <strong>Fecha de Nacimiento</strong>
                                <span>{{ !empty($perfil['FECHA_NACIMIENTO']) ? date('d/m/Y', strtotime($perfil['FECHA_NACIMIENTO'])) : 'No registrada' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <span>{{ $perfil['TELEFONO'] ?? 'No registrado' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="info-row">
                            <div class="icon-42 bg-light-gray"><i class="fa-regular fa-envelope"></i></div>
                            <div class="info-text">
                                <strong>Correo Electrónico</strong>
                                <span class="text-underline text-blue">{{ $perfil['CORREO'] ?? 'No registrado' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="info-row">
                            <div class="icon-42 bg-light-gray"><i class="fa-solid fa-location-dot"></i></div>
                            <div class="info-text">
                                <strong>Dirección de Residencia</strong>
                                <span>{{ $perfil['DIRECCION'] ?? 'No registrada' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="card-header-title">
                        <i class="fa-solid fa-shield-halved text-blue"></i>
                        <h4>Perfil Clínico y Seguridad</h4>
                    </div>
                    <div class="card-body-list">
                        <div class="info-row">
                            <div class="icon-42 bg-light-gray"><i class="fa-solid fa-tooth"></i></div>
                            <div class="info-text">
                                <span class="label">Especialidad Clínica:</span>
                                <span>{{ $perfil['especialidad_nombre'] ?? 'Odontólogo General' }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="info-row" style="margin-bottom: 16px;">
                            <div class="icon-42 bg-light-gray"><i class="fa-solid fa-door-open"></i></div>
                            <div class="info-text">
                                <strong>Consultorio Asignado</strong>
                                <span>Consultorio {{ $perfil['CONSULTORIO'] ?? '1' }}</span>
                            </div>
                        </div>
                        
                        <a href="{{ url('/odontologo/perfil?mode=edit#password-section') }}" class="btn-outline-full">
                            <i class="fa-solid fa-lock-open text-blue"></i> Cambiar Contraseña
                        </a>
                    </div>
                </div>
            </div>

        @else
            {{-- MODO EDICIÓN --}}
            <div class="form-panel" style="margin-top: 20px;">
                <div class="form-header-center">
                    <div class="icon-42 bg-blue text-white mx-auto mb-2 rounded-circle" style="background:#0f62fe; display:flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:50%; margin:0 auto 8px auto;">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <h2>Actualizar datos del Odontólogo</h2>
                    <p>Modifica tu información personal, de contacto y especialidad.</p>
                </div>

                <form id="formPerfilOdon" action="{{ url('/odontologo/perfil/actualizar') }}" method="POST">
                    @csrf
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Nombres:</label>
                            <div class="input-wrap">
                                <i class="fa-regular fa-user"></i>
                                <input type="text" name="nombres" value="{{ $perfil['NOMBRES'] ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Apellidos:</label>
                            <div class="input-wrap">
                                <i class="fa-regular fa-user"></i>
                                <input type="text" name="apellidos" value="{{ $perfil['APELLIDOS'] ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Tipo de documento:</label>
                            <div class="input-wrap select-wrap-icon">
                                <i class="fa-regular fa-id-card"></i>
                                <select name="tipo_documento" required>
                                    <option value="C.C" {{ (($perfil['TIPO_DOCUMENTO'] ?? '') == 'C.C') ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                    <option value="C.E" {{ (($perfil['TIPO_DOCUMENTO'] ?? '') == 'C.E') ? 'selected' : '' }}>Cédula de Extranjería</option>
                                    <option value="PPT" {{ (($perfil['TIPO_DOCUMENTO'] ?? '') == 'PPT') ? 'selected' : '' }}>Permiso por Protección</option>
                                    <option value="T.I" {{ (($perfil['TIPO_DOCUMENTO'] ?? '') == 'T.I') ? 'selected' : '' }}>Tarjeta de Identidad</option>
                                </select>
                                <i class="fa-solid fa-chevron-down arrow-down"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Número de documento:</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hashtag"></i>
                                <input type="number" name="numero_documento" value="{{ $perfil['NUMERO_DOCUMENTO'] ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Teléfono Celular:</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-mobile-screen"></i>
                                <input type="tel" name="telefono" id="celular_odon" 
                                       value="{{ $perfil['TELEFONO'] ?? '' }}" 
                                       required maxlength="10" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Género:</label>
                            <div class="input-wrap select-wrap-icon">
                                <i class="fa-solid fa-venus-mars"></i>
                                <select name="genero" required>
                                    <option value="MASCULINO" {{ (($perfil['GENERO'] ?? '') == 'MASCULINO') ? 'selected' : '' }}>Masculino</option>
                                    <option value="FEMENINO" {{ (($perfil['GENERO'] ?? '') == 'FEMENINO') ? 'selected' : '' }}>Femenino</option>
                                    <option value="OTRO" {{ (($perfil['GENERO'] ?? '') == 'OTRO') ? 'selected' : '' }}>Otro</option>
                                </select>
                                <i class="fa-solid fa-chevron-down arrow-down"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha de nacimiento:</label>
                            <div class="input-wrap">
                                <i class="fa-regular fa-calendar"></i>
                                <input type="date" name="fecha_nacimiento" value="{{ $perfil['FECHA_NACIMIENTO'] ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Dirección de Residencia:</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-map-location-dot"></i>
                                <input type="text" name="direccion" value="{{ $perfil['DIRECCION'] ?? '' }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-16" style="margin-top: 16px;">
                        <label>Especialidades Clínicas:</label>
                        <div class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 8px; padding: 15px; background: #fff; width: 100%;">
                            @php
                                $especialidadesArr = explode(',', $perfil['especialidad_id'] ?? '');
                            @endphp
                            @foreach ($especialidades as $esp)
                                @php
                                    $checked = in_array($esp['ID_ESPECIALIDAD'], $especialidadesArr) ? 'checked' : '';
                                @endphp
                                <label style="display: flex; align-items: center; margin-bottom: 8px; cursor: pointer; font-size: 14px;">
                                    <input type="checkbox" name="especialidad_id[]" value="{{ $esp['ID_ESPECIALIDAD'] }}" {{ $checked }} style="margin-right: 10px; width: auto; box-shadow: none;">
                                    {{ $esp['NOMBRE_ESPECIALIDAD'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mt-16" style="margin-top: 16px;">
                        <label>Correo electrónico institucional:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="correo" value="{{ $perfil['CORREO'] ?? '' }}" required>
                        </div>
                    </div>

                    <div class="form-group mt-16" id="password-section" style="margin-top: 16px;">
                        <label>Contraseña:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="passInput" name="pass_nueva" 
                                   placeholder="Nueva contraseña (deja en blanco si no deseas cambiarla)"
                                   minlength="8" pattern="(?=.*\d)(?=.*[A-Z])(?=.*[\W_]).{8,}">
                            <i class="fa-regular fa-eye eye-btn pass-toggle-eye" onclick="togglePasswordVisibility()"></i>
                        </div>
                        <span class="help-text text-muted font-xs mt-1" style="font-size: 12px; color: #6b7280; display: block; margin-top: 4px;">Mínimo 8 caracteres, con mayúscula, número y carácter especial.</span>
                    </div>

                    <div class="form-actions mt-32" style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px;">
                        <a href="{{ url('/odontologo/perfil') }}" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/odontologo/perfil_odon.js') }}" defer></script>
@endpush
