<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
    (function(){
        const m = document.querySelector('meta[name="csrf-token"]');
        if (!m) return;
        const t = m.getAttribute('content');
        const sameOrigin = u => typeof u !== 'string' || !/^https?:\/\//i.test(u) || u.startsWith(window.location.origin);
        const of = window.fetch;
        window.fetch = function(input, init) {
            init = init || {};
            const url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
            const method = (init.method || (input && input.method) || 'GET').toUpperCase();
            if (sameOrigin(url) && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                const h = new Headers(init.headers || {});
                if (!h.has('X-CSRF-TOKEN')) h.set('X-CSRF-TOKEN', t);
                init.headers = h;
            }
            return of.call(this, input, init);
        };
        const oo = XMLHttpRequest.prototype.open, os = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function(method, url) { this._cm = (method||'GET').toUpperCase(); this._cu = url; return oo.apply(this, arguments); };
        XMLHttpRequest.prototype.send = function() {
            if (sameOrigin(this._cu) && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(this._cm)) {
                try { this.setRequestHeader('X-CSRF-TOKEN', t); } catch (e) {}
            }
            return os.apply(this, arguments);
        };
    })();
    </script>
    <title>@yield('titulo', 'Odonto Estética - Portal Paciente')</title>

    <!-- Fuentes e Íconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS del paciente -->
    <link rel="stylesheet" href="{{ asset('css/paciente/global.css') }}">
    @stack('css')
</head>
<body>

    <div class="container-fluid h-100 p-0 d-flex overflow-hidden">

        <!-- MENÚ LATERAL (SIDEBAR) -->
        <aside class="sidebar d-flex flex-column p-4 justify-content-between">
            <div>
                <div class="d-flex align-items-center mb-4 border-bottom pb-3 justify-content-center">
                    <a href="/paciente/dashboard" class="d-block text-center w-100">
                        <img src="{{ asset('img/logo_odontologia.png') }}" alt="Odonto Estética" 
                             style="width: 95%; max-width: 200px; height: auto; object-fit: contain; display: inline-block;">
                    </a>
                </div>

                <nav class="nav flex-column gap-1">
                    <a href="/paciente/dashboard" 
                       class="nav-link custom-nav-link {{ request()->is('paciente/dashboard*') ? 'active' : '' }}">
                        <i class="fa-solid fa-house me-2"></i> Inicio
                    </a>
                    <a href="/paciente/perfil" 
                       class="nav-link custom-nav-link {{ request()->is('paciente/perfil*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user me-2"></i> Perfil
                    </a>
                    <a href="/paciente/citas" 
                       class="nav-link custom-nav-link {{ (request()->is('paciente/citas*') || request()->is('paciente/mis_citas*')) ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days me-2"></i> Mis citas
                    </a>
                    <a href="/paciente/historial" 
                       class="nav-link custom-nav-link {{ (request()->is('paciente/historial*') || request()->is('paciente/historial_clinico*')) ? 'active' : '' }}">
                        <i class="fa-solid fa-file-medical me-2"></i> Historial clínico
                    </a>
                    <a href="/paciente/tratamientos" 
                       class="nav-link custom-nav-link {{ request()->is('paciente/tratamientos*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bars-staggered me-2"></i> Tratamientos
                    </a>
                    <a href="/paciente/pagos" 
                       class="nav-link custom-nav-link {{ request()->is('paciente/pagos*') ? 'active' : '' }}">
                        <i class="fa-solid fa-wallet me-2"></i> Facturación y pagos
                    </a>
                    <a href="/paciente/notificaciones" 
                       class="nav-link custom-nav-link {{ request()->is('paciente/notificaciones*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell me-2"></i> Notificaciones
                    </a>
                </nav>
            </div>
            
            <a href="/salir" class="nav-link custom-nav-link logout-btn">
                <i class="fa-solid fa-door-open me-2"></i> Salir
            </a>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-grow-1 p-4 overflow-auto">

            <!-- HEADER -->
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 m-0 fw-bold text-dark-blue">
                        ¡Hola, {{ Auth::check() ? (Auth::user()->NOMBRES . ' ' . Auth::user()->APELLIDOS) : (session('usuario_nome') ?? 'Paciente') }}!
                    </h1>
                    <p class="m-0 text-muted small">
                        Hoy es {{ session('fecha_completa_es') ?? \Carbon\Carbon::now()->locale('es')->isoFormat('D \d\e MMMM \d\e Y') }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="/paciente/pedir_cita" class="btn btn-primary fw-bold px-3 d-flex align-items-center gap-2 btn-pedir-cita">
                        <i class="fa-regular fa-calendar-plus"></i>
                        Pedir Cita
                    </a>
                    <a href="/paciente/perfil" title="Mi Perfil" style="text-decoration: none;">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; border: 2px solid #dbeafe; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(37,99,235,0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                            {{ Auth::check() ? substr(Auth::user()->NOMBRES, 0, 1) : 'P' }}
                        </div>
                    </a>
                </div>
            </header>

            @yield('contenido')

        </main>
    </div>

    <!-- Scripts Base -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>