<?php 
$action_actual = $_GET['action'] ?? 'dashboard'; 
?>

<aside class="sidebar d-flex flex-column p-4 justify-content-between">
    <div>
        <div class="d-flex align-items-center mb-4 border-bottom pb-3 justify-content-center">
            <a href="/LOGIN_ORIGINAL/dashboard" class="d-block text-center w-100">
                <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Odonto Estética" style="width: 95%; max-width: 200px; height: auto; object-fit: contain; display: inline-block;">
            </a>
        </div>

        <nav class="nav flex-column gap-1">
            <a href="/LOGIN_ORIGINAL/dashboard" class="nav-link custom-nav-link <?= $action_actual === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-house me-2"></i> Inicio
            </a>
            <a href="/LOGIN_ORIGINAL/perfil" class="nav-link custom-nav-link <?= $action_actual === 'perfil' ? 'active' : '' ?>">
                <i class="fa-solid fa-user me-2"></i> Perfil
            </a>
            <a href="/LOGIN_ORIGINAL/mis_citas" class="nav-link custom-nav-link <?= $action_actual === 'mis_citas' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days me-2"></i> Mis citas
            </a>
            <a href="/LOGIN_ORIGINAL/historial_clinico" class="nav-link custom-nav-link <?= $action_actual === 'historial_clinico' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-medical me-2"></i> Historial clínico
            </a>
            <a href="/LOGIN_ORIGINAL/tratamientos" class="nav-link custom-nav-link <?= $action_actual === 'tratamientos' ? 'active' : '' ?>">
                <i class="fa-solid fa-bars-staggered me-2"></i> Tratamientos
            </a>
            <a href="/LOGIN_ORIGINAL/pagos" class="nav-link custom-nav-link <?= $action_actual === 'pagos' ? 'active' : '' ?>">
                <i class="fa-solid fa-wallet me-2"></i> Facturación y pagos
            </a>
            <a href="/LOGIN_ORIGINAL/notificaciones" class="nav-link custom-nav-link <?= $action_actual === 'notificaciones' ? 'active' : '' ?>">
                <i class="fa-solid fa-bell me-2"></i> Notificaciones
            </a>
        </nav>
    </div>
    
    <a href="/LOGIN_ORIGINAL/salir" class="nav-link custom-nav-link logout-btn">
        <i class="fa-solid fa-door-open me-2"></i> Salir
    </a>
</aside>