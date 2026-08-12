<?php
$currentAction = $_GET['action'] ?? 'inicio';

// 1. GENERADOR AUTÓNOMO DE FECHA EN ESPAÑOL
$meses_es = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecha_actual_es = date('d') . ' de ' . $meses_es[date('n') - 1] . ' de ' . date('Y');

// 2. LÓGICA INTELIGENTE PARA OBTENER EL NOMBRE
$nombreEspecialista = 'Odontólogo';
if (isset($usuario) && !empty($usuario['NOMBRES'])) {
    $nombreEspecialista = trim($usuario['NOMBRES'] . ' ' . ($usuario['APELLIDOS'] ?? ''));
} elseif (!empty($_SESSION['usuario_nome'])) {
    $nombreEspecialista = $_SESSION['usuario_nome'];
}
?>

<aside class="menu-sidebar">

    <div class="menu-logo-area" style="text-align: center; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
        <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Logo Odonto Estética" 
             style="width: 100%; max-width: 190px; height: auto; object-fit: contain;">
    </div>

    <nav class="menu-nav">

        <a href="/LOGIN_ORIGINAL/dashboard"
           class="menu-nav-item <?= ($currentAction == 'dashboard') ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/agenda"
           class="menu-nav-item <?= ($currentAction == 'odontologo/agenda') ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i> Agenda
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/pacientes"
           class="menu-nav-item <?= ($currentAction == 'odontologo/pacientes') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-group"></i> Pacientes
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/tratamientos"
           class="menu-nav-item <?= ($currentAction == 'odontologo/tratamientos') ? 'active' : '' ?>">
            <i class="fa-solid fa-kit-medical"></i> Tratamientos
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/planes"
           class="menu-nav-item <?= ($currentAction == 'odontologo/planes') ? 'active' : '' ?>" >
            <i class="fa-solid fa-file-invoice-dollar"></i> Planes de Tratamiento
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/facturacion"
           class="menu-nav-item <?= ($currentAction == 'odontologo/facturacion') ? 'active' : '' ?>">
            <i class="fa-solid fa-receipt"></i> Facturación
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/reportes"
           class="menu-nav-item <?= ($currentAction == 'odontologo/reportes') ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-bar"></i> Reportes
        </a>

    </nav>

    <div class="menu-sidebar-footer">
        
        <a href="/LOGIN_ORIGINAL/odontologo/perfil"
            class="menu-nav-item <?= ($currentAction === 'odontologo/perfil') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-doctor"></i> Mi Perfil
        </a>

        <a href="/LOGIN_ORIGINAL/odontologo/notificaciones"
           class="menu-nav-item <?= ($currentAction == 'odontologo/notificaciones') ? 'active' : '' ?>">
            <i class="fa-solid fa-comment-dots"></i> Notificaciones
        </a>

        <a href="/LOGIN_ORIGINAL/salir" class="menu-logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
        </a>

    </div>

</aside>

<main class="main-workspace">

    <header class="workspace-header">
        <div class="greeting-box">
            <h1>¡Hola, Dr(a). <?= htmlspecialchars($nombreEspecialista); ?>!</h1>
            <span class="date-lbl">
                Hoy es <?= $fecha_actual_es; ?>
            </span>
        </div>

        <div class="profile-header-actions">
            <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=100&q=80"
                 alt="Doctor"
                 class="doctor-avatar">
        </div>
    </header>