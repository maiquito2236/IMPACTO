<header class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <!-- Mantiene dinámicamente el nombre del usuario que ingresó al sistema -->
        <h1 class="h4 m-0 fw-bold text-dark-blue">¡Hola, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Paciente'); ?>!</h1>
        
        <!-- CORRECCIÓN LIMPIA: Se eliminó el punto y coma del tag de cierre -->
        <p class="m-0 text-muted small">Hoy es <?= htmlspecialchars($_SESSION['fecha_completa_es'] ?? date('d / m / Y')); ?></p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="/LOGIN_ORIGINAL/pedir_cita" class="btn btn-primary fw-bold px-3 d-flex align-items-center gap-2 btn-pedir-cita">
            <i class="fa-regular fa-calendar-plus"></i>
            Pedir Cita
        </a>
        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=100" alt="Usuario" class="rounded-circle border border-2 border-white object-fit-cover header-profile-img">
    </div>
</header>