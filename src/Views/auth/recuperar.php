<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/auth/recuperar.css">
    
</head>
<body class="d-flex justify-content-center align-items-center">

    <div class="bg-star star-1">✦</div><div class="bg-star star-2">✦</div>
    <div class="bg-star star-3">✦</div><div class="bg-star star-4">✦</div>

    <div class="card card-custom p-4 p-md-5 m-3">
        
        <?php if (isset($_ERROR)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_ERROR); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SUCCESS)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_SUCCESS); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php 
        $fase = $_SESSION['recuperar_fase'] ?? 'solicitar';
        
        // INTERFAZ FASE 1: Solicitar Código
        if ($fase === 'solicitar'): 
        ?>
            <div class="text-center">
                <div class="avatar-circle"><i class="bi bi-envelope-fill"></i></div>
                <h2 class="h3 fw-bold text-dark mb-1">¿Olvidaste tu contraseña?</h2>
                <p class="text-muted small mb-4">Ingresa tu correo para enviarte un código de verificación de 6 dígitos</p>
            </div>
            <form action="/LOGIN_ORIGINAL/procesarRecuperar" method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-1">Correo electrónico:</label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="correo" class="form-control" placeholder="Ingresa tu correo registrado" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-action w-100 mb-3 shadow-sm">Enviar Código</button>
                <div class="text-center">
                    <a href="/LOGIN_ORIGINAL/login" class="text-decoration-none fw-bold small" style="color: #0F62FE;">Volver al Inicio</a>
                </div>
            </form>

        <?php 
        // INTERFAZ FASE 2: Validar Código OTP
        elseif ($fase === 'verificar'): 
        ?>
            <div class="text-center">
                <div class="avatar-circle"><i class="bi bi-shield-lock-fill"></i></div>
                <h2 class="h3 fw-bold text-dark mb-1">Verificar Código</h2>
                <p class="text-muted small mb-4">Ingresa el código numérico de 6 dígitos que enviamos a tu Gmail</p>
            </div>
            <form action="/LOGIN_ORIGINAL/procesarRecuperar" method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-1">Código de verificación:</label>
                    <div class="input-group-custom">
                        <i class="bi bi-hash"></i>
                        <input type="text" name="codigo" class="form-control" placeholder="Ej: 123456" maxlength="6" pattern="[0-9]{6}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-action w-100 mb-4 shadow-sm">Validar Código</button>
            </form>

            <form id="formReenviar" action="/LOGIN_ORIGINAL/procesarRecuperar" method="POST" class="text-center">
                <input type="hidden" name="correo" value="<?= htmlspecialchars($_SESSION['recuperar_correo'] ?? ''); ?>">
                <p class="small text-muted mb-0">¿No te llegó el código?</p>
                <button type="submit" id="btnReenviarCode" class="btn-link-custom">Reenviar código</button>
            </form>

        <?php   
        // INTERFAZ FASE 3: Cambiar Contraseña Coincidente
        elseif ($fase === 'cambiar'): 
        ?>
            <div class="text-center">
                <div class="avatar-circle"><i class="bi bi-person-fill"></i></div>
                <h2 class="h3 fw-bold text-dark mb-1">Crear nueva contraseña</h2>
                <p class="text-muted small mb-4">Completa los campos para realizar el proceso de cambio de contraseña</p>
            </div>
            <form id="formFinal" action="/LOGIN_ORIGINAL/procesarRecuperar" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small mb-1">Contraseña:</label>
                    <div class="input-group-custom">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Ingresa tu nueva contraseña" required minlength="8" pattern="(?=.*\d)(?=.*[A-Z])(?=.*[\W_]).{8,}">
                        <i class="bi bi-eye eye-icon" onclick="togglePassword('contrasena', this)"></i>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold text-dark small mb-1">Confirmar contraseña:</label>
                    <div class="input-group-custom">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="form-control" placeholder="Confirma tu nueva contraseña" required>
                        <i class="bi bi-eye eye-icon" onclick="togglePassword('confirmar_contrasena', this)"></i>
                    </div>
                </div>
                <div class="text-muted mb-4" style="font-size: 0.78rem;">Mínimo 8 caracteres, con mayúscula, número y carácter especial.</div>
                
                <div id="jsErrorAlert" class="alert alert-danger p-2 small text-center rounded-3 mb-3 d-none">Las contraseñas no coinciden.</div>
                
                <button type="submit" class="btn btn-action w-100 shadow-sm">Guardar</button>
            </form>
        <?php endif; ?>

    </div>

    <div class="waves-container">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,42.4V120H0Z" class="wave-bg"></path>
        </svg>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/LOGIN_ORIGINAL/public/js/auth/recuperar.js"></script>

</body>
</html>