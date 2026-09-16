<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sonríe Dental - Dashboard')</title>
    
    <!-- Fuentes e íconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS de tu proyecto usando asset() de Laravel -->
    <link rel="stylesheet" href="{{ asset('css/administrador/global.css') }}">
    
    <!-- Por si alguna vista necesita CSS extra -->
    @stack('css')
</head>
<body>

    <div class="menu-layout">
 
        <!-- INICIO DEL MENÚ LATERAL (SIDEBAR) -->
        <aside class="menu-sidebar">
            <div class="menu-logo-area" style="text-align: center; padding: 45px 20px; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                <div class="menu-logo-icon" style="width: 100%;">
                    <!-- Imagen con asset() -->
                    <img src="{{ asset('img/logo_odontologia.png') }}" alt="Logo Odonto Estética" 
                         style="width: 100%; max-width: 190px; height: auto; object-fit: contain;">
                </div>
            </div>

                        <nav class="menu-nav">
                <a href="/admin/inicio" class="menu-nav-item {{ request()->is('admin/inicio') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                
                <a href="/admin/agenda" class="menu-nav-item {{ request()->is('admin/agenda') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Agenda y citas
                </a>
                
                <a href="/admin/usuarios" class="menu-nav-item {{ request()->is('admin/usuarios') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-group"></i> Gestión de Usuarios
                </a>
                
                <a href="/admin/consultorios" class="menu-nav-item {{ request()->is('admin/consultorios') ? 'active' : '' }}">
                    <i class="fa-solid fa-clinic-medical"></i> Gestión de Consultorios
                </a>

                <a href="/admin/horarios" class="menu-nav-item {{ request()->is('admin/horarios') ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar-days"></i> Gestión de horarios
                </a>

                <a href="/admin/facturacion" class="menu-nav-item {{ request()->is('admin/facturacion') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Facturación
                </a>

                <a href="/admin/reportes" class="menu-nav-item {{ request()->is('admin/reportes') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Reportes y estadísticas
                </a>

                <a href="/admin/otros" class="menu-nav-item {{ request()->is('admin/otros') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i> Otros
                </a>
            </nav>

            <div class="menu-sidebar-footer">
                <a href="/admin/perfil" class="menu-nav-item {{ request()->is('admin/perfil*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-tie"></i> Mi Perfil
                </a>
                <a href="/admin/notificaciones" class="menu-nav-item {{ request()->is('admin/notificaciones') ? 'active' : '' }}">
                    <i class="fa-solid fa-bell"></i> Notificaciones
                </a>
                        <!-- Mostrar botón de carga masiva solo si estamos en Usuarios, Agenda, Facturas u Horarios -->
                @if(request()->is('admin/usuarios', 'admin/agenda', 'admin/facturacion', 'admin/horarios'))
                    <button class="menu-nav-item" style="background-color: #10b981; color: white; border: none; width: calc(100% - 40px); margin: 10px 20px; border-radius: 8px; text-align: left; cursor: pointer;">
                        <i class="fa-solid fa-file-csv" style="color: white;"></i> Carga Masiva
                    </button>
                @endif

                <a href="/salir" class="menu-nav-item">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </a>
            </div>
        </aside>
        <!-- FIN DEL MENÚ LATERAL -->

    
        <!-- INICIO DEL CONTENIDO PRINCIPAL -->
        <main class="menu-main-content">

                        <!-- HEADER SUPERIOR -->
            <header class="menu-main-header">
                <div class="menu-welcome-text">
                    <!-- Si hay usuario logueado mostramos su nombre, si no, ponemos un texto temporal -->
                    <h1>¡Hola, {{ Auth::check() ? Auth::user()->NOMBRES : 'Admin Sistema' }}!</h1>
                    
                    <!-- Fecha automática idéntica a la original -->
                    <p class="menu-current-date">Hoy es {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D \d\e MMMM \d\e Y') }}</p>
                </div>

                <div class="menu-header-actions">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; border: 2px solid #dbeafe;">
                        <!-- Inicial del nombre -->
                        {{ Auth::check() ? substr(Auth::user()->NOMBRES, 0, 1) : 'A' }}
                    </div>
                </div>
            </header>

            @yield('contenido')

        </main>
        <!-- FIN DEL CONTENIDO PRINCIPAL -->

    </div>

    <!-- Por si alguna vista necesita JS extra -->
    @stack('scripts')
</body>
</html>