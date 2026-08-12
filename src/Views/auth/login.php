<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Iniciar Sesión</title>
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/auth/login.css">
    
</head>
<body class="d-flex justify-content-center align-items-center">

    <div class="bg-star star-1">✦</div><div class="bg-star star-2">✦</div>
    <div class="bg-star star-3">✦</div><div class="bg-star star-4">✦</div>

    <div class="card card-login p-4 p-md-5 m-3">
        <div class="text-center">
            <div class="avatar-circle">
                <i class="bi bi-person-fill"></i>
            </div>
            <h2 class="h3 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Iniciar Sesión</h2>
            <p class="text-muted small mb-4">Completa los datos para ingresar</p>
        </div>

        <?php if (isset($_SESSION['ALERTA_EXITO'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_SESSION['ALERTA_EXITO']); ?></span>
                <?php unset($_SESSION['ALERTA_EXITO']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['LOGIN_ERROR'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_SESSION['LOGIN_ERROR']); ?></span>
                <?php unset($_SESSION['LOGIN_ERROR']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="/LOGIN_ORIGINAL/procesarLogin" method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Correo electrónico:</label>
                <div class="input-group-custom">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="correo" class="form-control" placeholder="Ingresa tu correo electrónico" required autocomplete="off">
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Contraseña:</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Contraseña" required autocomplete="new-password">
                    <i class="bi bi-eye eye-icon" onclick="togglePassword()"></i>
                </div>
            </div>
            <div class="text-muted mb-4" style="font-size: 0.78rem;">Mínimo 8 caracteres, con mayúscula, número y carácter especial.</div>

            <button type="submit" class="btn btn-ingresar w-100 mb-4 shadow-sm">Ingresar</button>

            <div class="text-center">
                <a href="/LOGIN_ORIGINAL/recuperar" class="text-decoration-none fw-bold d-block mb-3" style="color: #0F62FE; font-size: 1.05rem;">¿Olvidaste tu contraseña?</a>
                <p class="small text-muted mb-0">¿No tienes cuenta? <a href="/LOGIN_ORIGINAL/registro" class="text-decoration-none fw-bold" style="color: #0F62FE;">Regístrate</a></p>
            </div>
        </form>
    </div>

    <div class="waves-container">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,42.4V120H0Z" class="wave-bg"></path>
        </svg>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/LOGIN_ORIGINAL/public/js/auth/login.js"></script>
    
</body>
</html>