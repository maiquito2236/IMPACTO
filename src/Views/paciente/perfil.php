<?php
// Capturamos el modo actual de la pantalla (lectura por defecto, o edición si se solicita)
$modo_edicion = isset($_GET['mode']) && $_GET['mode'] === 'edit';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Odonto Estética</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/global.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/paciente/perfil.css">
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">
        
        <?php require_once __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-grow-1 p-4 overflow-auto">
            
            <?php require_once __DIR__ . '/layouts/header.php'; ?>

            <?php if (!$modo_edicion): ?>

                <?php if (isset($_SESSION['EXITO_PERFIL'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <?= $_SESSION['EXITO_PERFIL']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['EXITO_PERFIL']); ?>
                <?php endif; ?>
                
                <section class="profile-banner p-4 shadow-sm text-center mb-4 border border-light-subtle">
                    <h2 class="fs-6 fw-bold text-dark-blue mb-3">Mi Perfil - <?= htmlspecialchars($usuario['NOMBRES'] ?? 'Paciente') ?></h2>
                    
                    <div class="d-inline-flex align-items-center text-start profile-card-badge shadow-sm">
                        <div class="bg-primary-light text-primary d-flex align-items-center justify-content-center rounded-circle me-3 border border-2 border-white font-sm fw-bold" style="width:60px; height:60px; min-width:60px; font-size: 20px;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="me-4">
                            <h3 class="fs-6 fw-bold m-0 text-dark-blue"><?= htmlspecialchars($usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS']) ?></h3>
                            <p class="m-0 text-muted font-xs"><?= htmlspecialchars($usuario['TIPO_DOCUMENTO'] ?? 'C.C') ?>  <?= htmlspecialchars($usuario['NUMERO_DOCUMENTO'] ?? '') ?></p>
                        </div>
                        <a href="/LOGIN_ORIGINAL/perfil?mode=edit" class="btn btn-primary btn-sm fw-bold px-3 d-flex align-items-center" style="border-radius: 20px; font-size: 11px; height: 32px;">Actualizar Información</a>
                    </div>
                </section>

                <div class="row g-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card border-0 p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-regular fa-user fs-5 text-dark-blue"></i>
                                <h3 class="fs-6 fw-bold text-dark-blue m-0">Datos Personales</h3>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-regular fa-user"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">Nombre</strong>
                                        <span class="text-muted font-xs"><?= htmlspecialchars($usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS']) ?></span>
                                    </div>
                                </div>
                                <hr class="m-0 text-black-50 opacity-25">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-solid fa-snowflake"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">Alergias</strong>
                                        <span class="text-muted font-xs">
                                            <?php
                                            $alergiaNombre = 'Ninguna';
                                            foreach ($alergiasLista as $alergia) {
                                                if (!empty($condicionesPaciente['alergia']) && $condicionesPaciente['alergia'] == $alergia['ID_CONDICION_MEDICA']) {
                                                    $alergiaNombre = $alergia['NOMBRE_CONDICION'];
                                                    break;
                                                }
                                            }
                                            echo htmlspecialchars($alergiaNombre);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <hr class="m-0 text-black-50 opacity-25">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-solid fa-mars"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">Género</strong>
                                        <span class="text-muted font-xs"><?= htmlspecialchars(ucfirst(strtolower($usuario['GENERO'] ?? 'No definido'))) ?></span>
                                    </div>
                                </div>
                                <hr class="m-0 text-black-50 opacity-25">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-solid fa-plus"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">EPS</strong>
                                        <span class="text-muted font-xs">
                                            <?php
                                            $epsNombre = 'No asignada';
                                            foreach ($epsLista as $eps) {
                                                if (!empty($paciente['EPS_ID_EPS']) && $paciente['EPS_ID_EPS'] == $eps['ID_EPS']) {
                                                    $epsNombre = $eps['NOMBRE_EPS'];
                                                    break;
                                                }
                                            }
                                            echo htmlspecialchars($epsNombre);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card border-0 p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-regular fa-envelope fs-5 text-dark-blue"></i>
                                <h3 class="fs-6 fw-bold text-dark-blue m-0">Datos de Contacto</h3>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-solid fa-phone"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">Teléfono</strong>
                                        <span class="text-muted font-xs"><?= htmlspecialchars($usuario['TELEFONO'] ?? 'No registrado') ?></span>
                                    </div>
                                </div>
                                <hr class="m-0 text-black-50 opacity-25">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-regular fa-envelope"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">Correo Electrónico</strong>
                                        <span class="text-primary font-xs text-decoration-underline"><?= htmlspecialchars($usuario['CORREO'] ?? '') ?></span>
                                    </div>
                                </div>
                                <hr class="m-0 text-black-50 opacity-25">
                                <div class="d-flex align-items-center gap-3 info-row">
                                    <div class="bg-primary-light data-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                                    <div>
                                        <strong class="font-sm text-dark-blue d-block">Dirección</strong>
                                        <span class="text-muted font-xs"><?= htmlspecialchars($usuario['DIRECCION'] ?? 'No registrada') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-12">
                        <div class="card border-0 p-4 h-100 shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-solid fa-shield-halved fs-5 text-dark-blue"></i>
                                <h3 class="fs-6 fw-bold text-dark-blue m-0">Seguridad y Ajustes</h3>
                            </div>
                            <div class="d-flex flex-column gap-4">
                                <a href="/LOGIN_ORIGINAL/perfil?mode=edit#password-section" class="btn btn-action-outline w-100 p-3 rounded-3 d-flex align-items-center justify-content-start gap-3 text-decoration-none text-dark-blue">
                                    <i class="fa-solid fa-lock-open text-primary"></i>
                                    <span class="font-sm fw-semibold">Cambiar Contraseña</span>
                                </a>
                                <div class="p-2 border border-primary rounded-3 d-flex align-items-center justify-content-between px-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="fa-regular fa-bell text-primary fs-5"></i>
                                        <span class="font-sm text-dark-blue fw-medium">Notificaciones</span>
                                    </div>
                                    <div class="form-check form-switch m-0 fs-5">
                                        <input class="form-check-input shadow-none" type="checkbox" role="switch" id="notifSwitch" checked>
                                    </div>
                                </div>
                                <div class="border border-primary rounded-3 p-3 mt-2">
                                    <strong class="text-dark-blue font-sm d-block mb-3"><i class="fa-solid fa-download text-primary me-2"></i>Exportar mis Datos Personales</strong>
                                    <div class="d-flex flex-column gap-2">
                                        
                                        <a href="/LOGIN_ORIGINAL/exportarPerfil?formato=excel" class="btn btn-outline-success text-start font-xs py-2 w-100 shadow-none">
                                            <i class="fa-regular fa-file-excel me-2"></i> Descargar reporte en Excel
                                        </a>
                                        
                                        <a href="/LOGIN_ORIGINAL/exportarPerfil?formato=pdf" class="btn btn-outline-danger text-start font-xs py-2 w-100 shadow-none" target="_blank">
                                            <i class="fa-regular fa-file-pdf me-2"></i> Descargar reporte en PDF
                                        </a>
                                        
                                        <a href="/LOGIN_ORIGINAL/exportarPerfil?formato=imprimir" class="btn btn-outline-primary text-start font-xs py-2 w-100 shadow-none" target="_blank">
                                            <i class="fa-solid fa-print me-2"></i> Imprimir
                                        </a>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>

                <?php if (isset($_SESSION['ERROR_PERFIL'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <?= $_SESSION['ERROR_PERFIL']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['ERROR_PERFIL']); ?>
                <?php endif; ?>

                <div class="form-update-container p-4 p-md-5">
                    <div class="form-profile-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    
                    <h2 class="text-center">Actualizar datos</h2>
                    <p class="subtitle text-center mb-4">Completa los datos que deseas modificar en tu historial</p>

                    <form action="/LOGIN_ORIGINAL/procesarActualizarPerfil" method="POST">
                        <div class="form-grid-custom">
                            
                            <div class="form-group-custom">
                                <label>Nombres:</label>
                                <div class="input-icon-container">
                                    <i class="fa-regular fa-user icon-left"></i>
                                    <input type="text" name="nombres" placeholder="Ingresa tus nombres" value="<?= htmlspecialchars($usuario['NOMBRES'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Apellidos:</label>
                                <div class="input-icon-container">
                                    <i class="fa-regular fa-user icon-left"></i>
                                    <input type="text" name="apellidos" placeholder="Ingresa tus apellidos" value="<?= htmlspecialchars($usuario['APELLIDOS'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Tipo de documento:</label>
                                <div class="input-icon-container select-icon-box">
                                    <i class="fa-regular fa-file-lines icon-left"></i>
                                    <select name="tipo_documento" required>
                                        <option value="C.C" <?= (($usuario['TIPO_DOCUMENTO'] ?? '') == 'C.C') ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                        <option value="T.I" <?= (($usuario['TIPO_DOCUMENTO'] ?? '') == 'T.I') ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                        <option value="C.E" <?= (($usuario['TIPO_DOCUMENTO'] ?? '') == 'C.E') ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                        <option value="PPT" <?= (($usuario['TIPO_DOCUMENTO'] ?? '') == 'PPT') ? 'selected' : '' ?>>Permiso por Protección Temporal</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Número de documento:</label>
                                <div class="input-icon-container">
                                    <i class="fa-regular fa-file-lines icon-left"></i>
                                    <input type="text" name="numero_documento" placeholder="Ingresa tu número" value="<?= htmlspecialchars($usuario['NUMERO_DOCUMENTO'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Teléfono:</label>
                                <div class="input-icon-container">
                                    <i class="fa-solid fa-phone icon-left"></i>
                                    <input type="tel" name="telefono" placeholder="Ingresa tu número de teléfono" value="<?= htmlspecialchars($usuario['TELEFONO'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Género:</label>
                                <div class="input-icon-container select-icon-box">
                                    <i class="fa-regular fa-user icon-left"></i>
                                    <select name="genero" required>
                                        <option value="">Selecciona tu género</option>
                                        <option value="MASCULINO" <?= (($usuario['GENERO'] ?? '') == 'MASCULINO') ? 'selected' : '' ?>>Masculino</option>
                                        <option value="FEMENINO" <?= (($usuario['GENERO'] ?? '') == 'FEMENINO') ? 'selected' : '' ?>>Femenino</option>
                                        <option value="OTRO" <?= (($usuario['GENERO'] ?? '') == 'OTRO') ? 'selected' : '' ?>>Otro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Fecha de nacimiento:</label>
                                <div class="input-icon-container">
                                    <i class="fa-regular fa-calendar icon-left"></i>
                                    <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['FECHA_NACIMIENTO'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Dirección:</label>
                                <div class="input-icon-container">
                                    <i class="fa-solid fa-map icon-left"></i>
                                    <input type="text" name="direccion" placeholder="Ingresa tu dirección" value="<?= htmlspecialchars($usuario['DIRECCION'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>EPS:</label>
                                <div class="input-icon-container select-icon-box">
                                    <i class="fa-solid fa-plus icon-left"></i>
                                    <select name="eps" required>
                                        <option value="">Selecciona tu EPS</option>
                                        <?php foreach ($epsLista as $eps): ?>
                                            <option value="<?= $eps['ID_EPS'] ?>" <?= (!empty($paciente['EPS_ID_EPS']) && $paciente['EPS_ID_EPS'] == $eps['ID_EPS']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($eps['NOMBRE_EPS']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Enfermedad:</label>
                                <div class="input-icon-container select-icon-box">
                                    <i class="fa-solid fa-user-doctor icon-left"></i>
                                    <select name="enfermedad">
                                        <option value="">Selecciona una enfermedad</option>
                                        <?php foreach ($enfermedadesLista as $enfermedad): ?>
                                            <option value="<?= $enfermedad['ID_CONDICION_MEDICA'] ?>" <?= (!empty($condicionesPaciente['enfermedad']) && $condicionesPaciente['enfermedad'] == $enfermedad['ID_CONDICION_MEDICA']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($enfermedad['NOMBRE_CONDICION']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>RH:</label>
                                <div class="input-icon-container select-icon-box">
                                    <i class="fa-solid fa-droplet icon-left"></i>
                                    <select name="rh">
                                        <option value="A+" <?= (($usuario['RH'] ?? '') == 'A+') ? 'selected' : '' ?>>A+</option>
                                        <option value="B+" <?= (($usuario['RH'] ?? '') == 'B+') ? 'selected' : '' ?>>B+</option>
                                        <option value="AB+" <?= (($usuario['RH'] ?? '') == 'AB+') ? 'selected' : '' ?>>AB+</option>
                                        <option value="O+" <?= (($usuario['RH'] ?? '') == 'O+') ? 'selected' : '' ?>>O+</option>
                                        <option value="A-" <?= (($usuario['RH'] ?? '') == 'A-') ? 'selected' : '' ?>>A-</option>
                                        <option value="B-" <?= (($usuario['RH'] ?? '') == 'B-') ? 'selected' : '' ?>>B-</option>
                                        <option value="AB-" <?= (($usuario['RH'] ?? '') == 'AB-') ? 'selected' : '' ?>>AB-</option>
                                        <option value="O-" <?= (($usuario['RH'] ?? '') == 'O-') ? 'selected' : '' ?>>O-</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group-custom">
                                <label>Alergias:</label>
                                <div class="input-icon-container select-icon-box">
                                    <i class="fa-solid fa-snowflake icon-left"></i>
                                    <select name="alergia">
                                        <option value="">Selecciona una alergia</option>
                                        <?php foreach ($alergiasLista as $alergia): ?>
                                            <option value="<?= $alergia['ID_CONDICION_MEDICA'] ?>" <?= (!empty($condicionesPaciente['alergia']) && $condicionesPaciente['alergia'] == $alergia['ID_CONDICION_MEDICA']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($alergia['NOMBRE_CONDICION']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Nombre contacto de emergencia:</label>
                                <div class="input-icon-container">
                                    <i class="fa-solid fa-user-group icon-left"></i>
                                    <input type="text" name="nombre_contacto_emergencia" placeholder="Ingresa el nombre del contacto" value="<?= htmlspecialchars($paciente['NOMBRE_CONTACTO_EMERGENCIA'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>Número contacto de emergencia:</label>
                                <div class="input-icon-container">
                                    <i class="fa-solid fa-phone icon-left"></i>
                                    <input type="text" name="numero_contacto_emergencia" placeholder="Ingresa el número del contacto" value="<?= htmlspecialchars($paciente['NUMERO_CONTACTO_EMERGENCIA'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group-custom mt-3">
                            <label>Correo electrónico:</label>
                            <div class="input-icon-container">
                                <i class="fa-regular fa-envelope icon-left"></i>
                                <input type="email" name="correo" placeholder="Ingresa tu correo electrónico" value="<?= htmlspecialchars($usuario['CORREO'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group-custom mt-3" id="password-section">
                            <label>Contraseña:</label>
                            <div class="input-icon-container">
                                <i class="fa-solid fa-lock icon-left"></i>
                                <input type="password" id="passInput" name="password" placeholder="Nueva contraseña (deja en blanco si no deseas cambiarla)">
                                <i class="fa-regular fa-eye pass-toggle-eye" onclick="togglePasswordVisibility()" style="cursor: pointer;"></i>
                            </div>
                            <span class="help-text text-muted font-xs mt-1">Mínimo 8 caracteres, con mayúscula, número y carácter especial.</span>
                        </div>

                        <div class="d-flex gap-2 form-actions-group mt-4">
                            <a href="/LOGIN_ORIGINAL/perfil" class="btn-cancel-danger w-50 py-2 fw-bold text-center text-decoration-none">Cancelar</a>
                            <button type="submit" class="btn-submit-update w-50 py-2 fw-bold">Actualizar</button>
                        </div>
                    </form>
                </div>

            <?php endif; ?>
            
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/LOGIN_ORIGINAL/public/js/paciente/perfil.js"></script>

</body>
</html>