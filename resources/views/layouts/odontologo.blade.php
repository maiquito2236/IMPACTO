<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Odonto Estética - Panel Odontólogo')</title>
    
    <!-- Fuentes e Íconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Maestro del Menú de Odontólogo -->
    <link rel="stylesheet" href="{{ asset('css/odontologo/menu.css') }}">
    
    <!-- CSS extra de vistas secundarias -->
    @stack('css')
</head>
<body>

    <div class="menu-layout">

        <!-- MENÚ LATERAL (SIDEBAR) -->
        <aside class="menu-sidebar">

            <div class="menu-logo-area" style="text-align: center; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                <img src="{{ asset('img/logo_odontologia.png') }}" alt="Logo Odonto Estética" 
                     style="width: 100%; max-width: 190px; height: auto; object-fit: contain;">
            </div>

            <nav class="menu-nav">

                <a href="/odontologo/inicio"
                   class="menu-nav-item {{ (request()->is('odontologo/inicio*') || request()->is('odontologo/dashboard*')) ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>

                <a href="/odontologo/agenda"
                   class="menu-nav-item {{ request()->is('odontologo/agenda*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Agenda
                </a>

                <a href="/odontologo/pacientes"
                   class="menu-nav-item {{ request()->is('odontologo/pacientes*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-group"></i> Pacientes
                </a>

                <a href="/odontologo/tratamientos"
                   class="menu-nav-item {{ request()->is('odontologo/tratamientos*') ? 'active' : '' }}">
                    <i class="fa-solid fa-kit-medical"></i> Tratamientos
                </a>

                <a href="/odontologo/planes"
                   class="menu-nav-item {{ (request()->is('odontologo/planes*') || request()->is('odontologo/historial*')) ? 'active' : '' }}" >
                    <i class="fa-solid fa-file-invoice-dollar"></i> Planes de Tratamiento
                </a>

                <a href="/odontologo/facturacion"
                   class="menu-nav-item {{ request()->is('odontologo/facturacion*') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i> Facturación
                </a>

                <a href="/odontologo/reportes"
                   class="menu-nav-item {{ request()->is('odontologo/reportes*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-bar"></i> Reportes
                </a>

            </nav>

            <div class="menu-sidebar-footer">
                
                <a href="/odontologo/perfil"
                    class="menu-nav-item {{ request()->is('odontologo/perfil*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-doctor"></i> Mi Perfil
                </a>

                <a href="/odontologo/notificaciones"
                   class="menu-nav-item {{ request()->is('odontologo/notificaciones*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comment-dots"></i> Notificaciones
                </a>

                <a href="/salir" class="menu-logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
                </a>

            </div>

        </aside>
        <!-- FIN SIDEBAR -->

        <!-- CONTENEDOR PRINCIPAL -->
        <main class="main-workspace">

            <header class="workspace-header">
                <div class="greeting-box">
                    <h1>¡Hola, Dr(a). {{ Auth::check() ? (Auth::user()->NOMBRES . ' ' . Auth::user()->APELLIDOS) : ($doctorNombre ?? 'Odontólogo') }}!</h1>
                    <span class="date-lbl">
                        Hoy es {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D \d\e MMMM \d\e Y') }}
                    </span>
                </div>

                <div class="profile-header-actions">
                    <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=100&q=80"
                         alt="Doctor"
                         class="doctor-avatar">
                </div>
            </header>

            @yield('contenido')

        </main>
        <!-- FIN CONTENEDOR PRINCIPAL -->

    </div>

    <!-- Scripts adicionales -->
    @stack('scripts')
</body>
</html>
