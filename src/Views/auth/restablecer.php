<?php
if (!isset($token)) {
    $token = $_GET['token'] ?? ($_POST['token'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Crear Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/auth/restablecer.css">
    
</head>
<body class="d-flex justify-content-center align-items-center">

    <div class="bg-star star-1">✦</div><div class="bg-star star-2">✦</div>
    <div class="bg-star star-3">✦</div><div class="bg-star star-4">✦</div>

    <div class="card card-custom p-4 p-md-5 m-3">
        <div class="text-center">
            <div class="avatar-circle">
                <i class="bi bi-person-fill"></i>
            </div>
            <h2 class="h3 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Crear nueva contraseña</h2>
            <p class="text-muted small mb-4">Completa los campos para realizar el proceso de cambio de contraseña</p>
        </div>

        <?php if (isset($_ERROR)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 small text-center mb-4" role="alert">
                <span><?= htmlspecialchars($_ERROR); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="/LOGIN_ORIGINAL/procesarRestablecer" method="POST">
            
            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

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

            <button type="submit" class="btn btn-guardar w-100 shadow-sm">Guardar</button>

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