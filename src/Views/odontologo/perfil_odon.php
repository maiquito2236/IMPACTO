<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Mi Perfil</title>

    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/menu.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/odontologo/perfil_odon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="menu-layout">
        
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>

        <main class="main-content">
            
            <?php 
            // Validamos el modo de vista actual
            $modo_edicion = isset($_GET['mode']) && $_GET['mode'] === 'edit'; 
            ?>
        <div class="po-container">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">
            <i class="fa-solid fa-circle-check"></i> Información actualizada correctamente.
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert-danger">
            <div class="alert-danger-content">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?>
            </div>
            <i class="fa-solid fa-xmark alert-close" onclick="this.parentElement.style.display='none';"></i>
        </div>
    <?php endif; ?>

    <?php if (!isset($_GET['mode']) || $_GET['mode'] !== 'edit'): ?>
        
        <div class="profile-banner">
            <div class="banner-info">
                <div class="icon-42 bg-blue-soft text-blue rounded-circle">
                    <?= strtoupper(substr($perfil['NOMBRES'] ?? 'O', 0, 1) . substr($perfil['APELLIDOS'] ?? 'D', 0, 1)) ?>
                </div>
                <div>
                    <h3><?= htmlspecialchars(($perfil['NOMBRES'] ?? '') . ' ' . ($perfil['APELLIDOS'] ?? '')) ?></h3>
                    <p><?= htmlspecialchars($perfil['TIPO_DOCUMENTO'] ?? 'C.C') ?> <?= htmlspecialchars($perfil['NUMERO_DOCUMENTO'] ?? '00000000') ?></p>
                </div>
            </div>
            <a href="/LOGIN_ORIGINAL/odontologo/perfil?mode=edit" class="btn-primary">
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
                            <span><?= htmlspecialchars($perfil['TIPO_DOCUMENTO'] ?? 'C.C') ?> - <?= htmlspecialchars($perfil['NUMERO_DOCUMENTO'] ?? 'No registrado') ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-solid fa-venus-mars"></i></div>
                        <div class="info-text">
                            <strong>Género</strong>
                            <span><?= htmlspecialchars(ucfirst(strtolower($perfil['GENERO'] ?? 'No definido'))) ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-regular fa-calendar"></i></div>
                        <div class="info-text">
                            <strong>Fecha de Nacimiento</strong>
                            <span><?= !empty($perfil['FECHA_NACIMIENTO']) ? date('d/m/Y', strtotime($perfil['FECHA_NACIMIENTO'])) : 'No registrada'; ?></span>
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
                            <span><?= htmlspecialchars($perfil['TELEFONO'] ?? 'No registrado') ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-regular fa-envelope"></i></div>
                        <div class="info-text">
                            <strong>Correo Electrónico</strong>
                            <span class="text-underline text-blue"><?= htmlspecialchars($perfil['CORREO'] ?? 'No registrado') ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row">
                        <div class="icon-42 bg-light-gray"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="info-text">
                            <strong>Dirección de Residencia</strong>
                            <span><?= htmlspecialchars($perfil['DIRECCION'] ?? 'No registrada') ?></span>
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
                            <span><?= htmlspecialchars($perfil['especialidad_nombre'] ?? 'Odontólogo General') ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="info-row" style="margin-bottom: 16px;">
                        <div class="icon-42 bg-light-gray"><i class="fa-solid fa-door-open"></i></div>
                        <div class="info-text">
                            <strong>Consultorio Asignado</strong>
                            <span>Consultorio <?= htmlspecialchars($perfil['CONSULTORIO'] ?? '1') ?></span>
                        </div>
                    </div>
                    
                    <a href="/LOGIN_ORIGINAL/odontologo/perfil?mode=edit#password-section" class="btn-outline-full">
                        <i class="fa-solid fa-lock-open text-blue"></i> Cambiar Contraseña
                    </a>
                </div>
            </div>
        </div>

    <?php else: ?>
        
        <div class="form-panel" style="margin-top: 20px;">
            <div class="form-header-center">
                <div class="icon-42 bg-blue text-white mx-auto mb-2 rounded-circle">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h2>Actualizar datos del Odontólogo</h2>
                <p>Modifica tu información personal, de contacto y especialidad.</p>
            </div>

            <form id="formPerfilOdon" action="/LOGIN_ORIGINAL/odontologo/perfil/actualizar" method="POST">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Nombres:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nombres" value="<?= htmlspecialchars($perfil['NOMBRES'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Apellidos:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($perfil['APELLIDOS'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipo de documento:</label>
                        <div class="input-wrap select-wrap-icon">
                            <i class="fa-regular fa-id-card"></i>
                            <select name="tipo_documento" required>
                                <option value="C.C" <?= (($perfil['TIPO_DOCUMENTO'] ?? '') == 'C.C') ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                <option value="C.E" <?= (($perfil['TIPO_DOCUMENTO'] ?? '') == 'C.E') ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                <option value="PPT" <?= (($perfil['TIPO_DOCUMENTO'] ?? '') == 'PPT') ? 'selected' : '' ?>>Permiso por Protección</option>
                                <option value="T.I" <?= (($perfil['TIPO_DOCUMENTO'] ?? '') == 'T.I') ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Número de documento:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="number" name="numero_documento" value="<?= htmlspecialchars($perfil['NUMERO_DOCUMENTO'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Teléfono Celular:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <input type="tel" name="telefono" id="celular_odon" 
                                   value="<?= htmlspecialchars($perfil['TELEFONO'] ?? '') ?>" 
                                   required maxlength="10" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Género:</label>
                        <div class="input-wrap select-wrap-icon">
                            <i class="fa-solid fa-venus-mars"></i>
                            <select name="genero" required>
                                <option value="MASCULINO" <?= (($perfil['GENERO'] ?? '') == 'MASCULINO') ? 'selected' : '' ?>>Masculino</option>
                                <option value="FEMENINO" <?= (($perfil['GENERO'] ?? '') == 'FEMENINO') ? 'selected' : '' ?>>Femenino</option>
                                <option value="OTRO" <?= (($perfil['GENERO'] ?? '') == 'OTRO') ? 'selected' : '' ?>>Otro</option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha de nacimiento:</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($perfil['FECHA_NACIMIENTO'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dirección de Residencia:</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <input type="text" name="direccion" value="<?= htmlspecialchars($perfil['DIRECCION'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-16">
                    <label>Especialidades Clínicas:</label>
                    <div class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 8px; padding: 15px; background: #fff; width: 100%;">
                        <?php 
                        $especialidadesArr = explode(',', $perfil['especialidad_id'] ?? '');
                        foreach ($especialidades as $esp): 
                            $checked = in_array($esp['ID_ESPECIALIDAD'], $especialidadesArr) ? 'checked' : '';
                        ?>
                            <label style="display: flex; align-items: center; margin-bottom: 8px; cursor: pointer; font-size: 14px;">
                                <input type="checkbox" name="especialidad_id[]" value="<?= $esp['ID_ESPECIALIDAD'] ?>" <?= $checked ?> style="margin-right: 10px; width: auto; box-shadow: none;">
                                <?= htmlspecialchars($esp['NOMBRE_ESPECIALIDAD']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group mt-16">
                    <label>Correo electrónico institucional:</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="correo" value="<?= htmlspecialchars($perfil['CORREO'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group mt-16" id="password-section">
                    <label>Contraseña:</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="passInput" name="pass_nueva" 
                               placeholder="Nueva contraseña (deja en blanco si no deseas cambiarla)"
                               minlength="8" pattern="(?=.*\d)(?=.*[A-Z])(?=.*[\W_]).{8,}">
                        <i class="fa-regular fa-eye eye-btn pass-toggle-eye" onclick="togglePasswordVisibility()"></i>
                    </div>
                    <span class="help-text text-muted font-xs mt-1">Mínimo 8 caracteres, con mayúscula, número y carácter especial.</span>
                </div>

                <div class="form-actions mt-32">
                    <a href="/LOGIN_ORIGINAL/odontologo/perfil" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </div>

    <?php endif; ?>

    <script src="/LOGIN_ORIGINAL/public/js/odontologo/perfil_odon.js"></script>
</div>