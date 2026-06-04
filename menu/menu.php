<?php

$currentPage = basename($_SERVER['PHP_SELF']);

?>

<!-- SIDEBAR -->
<aside class="menu-sidebar">

    <!-- LOGO -->
    <div class="menu-logo-area">

        <div class="menu-logo-icon">
            <i class="fa-solid fa-tooth"></i>
        </div>

        <div class="menu-logo-text">

            <span class="menu-brand-name">
                ODONTO ESTÉTICA
            </span>

            <span class="menu-brand-sub">
                SALUD Y BIENESTAR
            </span>

        </div>

    </div>

    <!-- NAV -->
    <nav class="menu-nav">

        <!-- DASHBOARD -->
        <a href="/Odontologo/dashboard/dashboard.php"
           class="menu-nav-item <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-house"></i>
            Dashboard

        </a>

        <!-- AGENDA -->
        <a href="/Odontologo/agenda/agenda.php"
           class="menu-nav-item <?= ($currentPage == 'agenda.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-calendar-days"></i>
            Agenda

        </a>

        <!-- PACIENTES -->
        <a href="/Odontologo/pacientes/index.php"
           class="menu-nav-item <?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], '/pacientes/') !== false) ? 'active' : '' ?>">

            <i class="fa-solid fa-user-group"></i>
            Pacientes

        </a>

        <!-- TRATAMIENTOS -->
        <a href="/Odontologo/tratamiento/index.php"
           class="menu-nav-item <?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], '/tratamiento/') !== false) ? 'active' : '' ?>">

            <i class="fa-solid fa-kit-medical"></i>
            Tratamientos

        </a>

        <!-- HISTORIA -->
        <a href="/Odontologo/historial_clinico/index.php"
           class="menu-nav-item <?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], '/historial_clinico/') !== false) ? 'active' : '' ?>">

            <i class="fa-solid fa-id-card-clip"></i>
            Historia Clínica

        </a>

        <!-- PLANES -->
        <a href="/Odontologo/planes_tratamientos/planes_tratamientos.php"
           class="menu-nav-item <?= ($currentPage == 'planes_tratamientos.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-file-invoice-dollar"></i>
            Planes de Tratamiento

        </a>

        <!-- FACTURACIÓN -->
        <a href="/Odontologo/facturacion/index.php"
           class="menu-nav-item <?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], '/facturacion/') !== false) ? 'active' : '' ?>">

            <i class="fa-solid fa-receipt"></i>
            Facturación

        </a>

        <!-- REPORTES -->
        <a href="/Odontologo/reportes/reportes.php"
           class="menu-nav-item <?= ($currentPage == 'reportes.php') ? 'active' : '' ?>">

            <i class="fa-solid fa-chart-bar"></i>
            Reportes

        </a>

    </nav>

    <!-- FOOTER -->
    <div class="menu-sidebar-footer">

        <a href="/Odontologo/notificaciones/index.php"
           class="menu-nav-item <?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], '/notificaciones/') !== false) ? 'active' : '' ?>">

            <i class="fa-solid fa-comment-dots"></i>
            Notificaciones

        </a>

        <a href="#"
           class="menu-logout-btn">

            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Cerrar sesión

        </a>

    </div>

</aside>

<!-- MAIN -->
<main class="main-workspace">

    <!-- HEADER -->
    <header class="workspace-header">

        <div class="greeting-box">

            <h1>
                ¡Hola, Dr. Andres Diaz!
            </h1>

            <span class="date-lbl">
                Jueves, 16 de mayo de 2026
            </span>

        </div>

        <div class="profile-header-actions">

            <div class="notification-wrapper">

                <a href="/Odontologo/notificaciones/index.php"
                   class="logout-btn">

                    <i class="fa-regular fa-bell"></i>

                    <span class="count-badge">
                        1
                    </span>

                </a>

            </div>

            <img
                src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=100&q=80"
                alt="Dr. Andres"
                class="doctor-avatar">

        </div>

    </header>