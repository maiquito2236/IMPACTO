<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Registrar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/auth/registro.css">

</head>
<body class="d-flex justify-content-center align-items-center">

    <div class="bg-star star-1">✦</div><div class="bg-star star-2">✦</div>
    <div class="bg-star star-3">✦</div><div class="bg-star star-4">✦</div>

    <div class="card card-register p-4 p-md-5 m-3">
        <div class="text-center">
            <div class="avatar-circle">
                <i class="bi bi-person-fill"></i>
            </div>
            <h2 class="h3 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Registro</h2>
            <p class="text-muted small">Completa tus datos para crear tu cuenta en el sistema</p>
        </div>

        <?php if (isset($_SUCCESS)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_SUCCESS); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_ERROR)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_ERROR); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="/LOGIN_ORIGINAL/procesarRegistro" method="POST">
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark small mb-1">Nombres *</label>
                    <input type="text" name="nombres" class="form-control" placeholder="Ingresa tus nombres" required
                        value="<?= isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark small mb-1">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control" placeholder="Ingresa tus apellidos" required
                        value="<?= isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark small mb-1">Tipo de documento *</label>
                    <select name="tipo_documento" class="form-select" required>
                        <option value="" disabled selected>Selecciona un tipo</option>

                        <option value="C.C" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'C.C') ? 'selected' : ''; ?>>
                            Cédula de Ciudadanía
                        </option>
                        <option value="T.I" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'T.I') ? 'selected' : ''; ?>>
                            Tarjeta de Identidad
                        </option>
                        <option value="C.E" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'C.E') ? 'selected' : ''; ?>>
                            Cédula de Extranjería
                        </option>
                        <option value="PPT" <?= (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'PPT') ? 'selected' : ''; ?>>
                            Permiso por Protección Temporal
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark small mb-1">Número de documento *</label>
                    <input type="text" name="numero_documento" class="form-control" placeholder="Ingresa tu número" required
                        value="<?= isset($_POST['numero_documento']) ? htmlspecialchars($_POST['numero_documento']) : ''; ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark small mb-1">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" placeholder="Ingresa tu número de teléfono"
                        value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark small mb-1">Género *</label>
                    <select name="genero" class="form-select" required>
                        <option value="" disabled selected>Selecciona tu género</option>

                        <option value="MASCULINO" <?= (isset($_POST['genero']) && $_POST['genero'] == 'MASCULINO') ? 'selected' : ''; ?>>
                            Masculino
                        </option>
                        <option value="FEMENINO" <?= (isset($_POST['genero']) && $_POST['genero'] == 'FEMENINO') ? 'selected' : ''; ?>>
                            Femenino
                        </option>
                        <option value="OTRO" <?= (isset($_POST['genero']) && $_POST['genero'] == 'OTRO') ? 'selected' : ''; ?>>
                            Otro
                        </option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Correo electrónico *</label>
                <input type="email" name="correo" class="form-control" placeholder="Ingresa tu correo electrónico" required
                    value="<?= isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Contraseña *</label>
                <div class="position-relative">
                    <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Crea una contraseña" required minlength="8" pattern="(?=.*\d)(?=.*[A-Z])(?=.*[\W_]).{8,}">
                    <i class="bi bi-eye eye-icon" id="toggleIcon" style="cursor:pointer; position:absolute; right:15px; top:50%; transform:translateY(-50%); color: #A0AEC0;" onclick="togglePassword()"></i>
                </div>
            </div>

            <div class="progress mt-2" style="height: 5px;">
                    <div id="passwordStrength" class="progress-bar transition-all" role="progressbar" style="width: 0%; transition: width 0.3s ease, background-color 0.3s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="strengthText" class="small mt-1 fw-bold" style="min-height: 20px;"></div>

            <div class="text-muted mb-4" style="font-size: 0.78rem;">Mínimo 8 caracteres, con mayúscula, número y un carácter especial (ej. !@#$%).</div>
            
            <button type="submit" class="btn btn-registrar w-100 mb-4 shadow-sm">Crear cuenta</button>

            <div class="text-center">
                <p class="small text-muted mb-0">¿Ya tienes una cuenta? <a href="/LOGIN_ORIGINAL/login" class="text-decoration-none fw-bold" style="color: #0F62FE;">Inicia sesión</a></p>
            </div>
        </form>
    </div>

    <div class="waves-container">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,42.4V120H0Z" class="wave-bg"></path>
        </svg>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/LOGIN_ORIGINAL/public/js/auth/registro.js"></script>

</body>
</html>