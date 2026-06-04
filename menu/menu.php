<?php $currentPage = $_SERVER['REQUEST_URI']; ?>

<aside class="menu-sidebar">

    <div class="menu-logo-area">
        <div class="menu-logo-icon">
            <i class="fa-solid fa-tooth"></i>
        </div>

        <div class="menu-logo-text">
            <span class="menu-brand-name">ODONTO ESTÉTICA</span>
            <span class="menu-brand-sub">SALUD Y BIENESTAR</span>
        </div>
    </div>

    <nav class="menu-nav">

        <a href="../Agenda/index.php"
           class="menu-nav-item <?= (strpos($currentPage,'/Agenda/') !== false) ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            Agenda y citas
        </a>

        <a href="../Facturacion/index.php"
           class="menu-nav-item <?= (strpos($currentPage,'/Facturacion/') !== false) ? 'active' : '' ?>">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            Facturación
        </a>

        <a href="../Gestion de usuarios/index.php"
           class="menu-nav-item <?= (strpos($currentPage,'/Gestion de usuarios/') !== false) ? 'active' : '' ?>">
            <i class="fa-solid fa-user-group"></i>
            Gestión de Usuarios
        </a>

        <a href="../Reportes/index.php"
           class="menu-nav-item <?= (strpos($currentPage,'/Reportes/') !== false) ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i>
            Reportes y estadísticas
        </a>

    </nav>

    <div class="menu-sidebar-footer">

        <a href="../notificaciones/index.php"
           class="menu-nav-item <?= (strpos($currentPage,'/notificaciones/') !== false) ? 'active' : '' ?>">
            <i class="fa-solid fa-bell"></i>
            Notificaciones
        </a>

        <a href="#" class="menu-logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Cerrar sesión
        </a>

    </div>

</aside>