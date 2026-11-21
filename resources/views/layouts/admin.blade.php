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
    <title>@yield('titulo', 'Odonto Estética - Panel Administrador')</title>
    
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
                    <button id="btnAbrirModalCargaMasiva" class="menu-nav-item" style="background-color: #10b981; color: white; border: none; width: calc(100% - 40px); margin: 10px 20px; border-radius: 8px; text-align: left; cursor: pointer;">
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
                    <a href="/admin/perfil" title="Mi Perfil" style="text-decoration: none;">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; border: 2px solid #dbeafe; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(37,99,235,0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                            <!-- Inicial del nombre -->
                            {{ Auth::check() ? substr(Auth::user()->NOMBRES, 0, 1) : 'A' }}
                        </div>
                    </a>
                </div>
            </header>

            @yield('contenido')

        </main>
        <!-- FIN DEL CONTENIDO PRINCIPAL -->

    </div>

    <!-- Por si alguna vista necesita JS extra -->
    @stack('scripts')

    {{-- =================== MODAL DE CARGA MASIVA =================== --}}
    @if(request()->is('admin/usuarios', 'admin/agenda', 'admin/facturacion', 'admin/horarios'))
    <div id="modalCargaMasiva" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:16px; padding:32px; width:95%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative; animation:fadeInModal .25s ease;">
            <!-- Cerrar -->
            <button id="btnCerrarModalCM" style="position:absolute; top:14px; right:14px; background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Título -->
            <div style="text-align:center; margin-bottom:24px;">
                <div style="width:56px; height:56px; border-radius:50%; background:#ecfdf5; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                    <i class="fa-solid fa-file-csv" style="font-size:24px; color:#10b981;"></i>
                </div>
                <h2 id="tituloCargaMasiva" style="margin:0; font-size:20px; color:#1e293b; font-weight:700;">Carga Masiva</h2>
                <p style="margin:6px 0 0; font-size:13px; color:#64748b;">Sube un archivo CSV con los datos para importar registros automáticamente</p>
            </div>

            <!-- Botón descargar plantilla -->
            <div style="margin-bottom:20px; text-align:center;">
                <a id="linkDescargarPlantilla" href="#" style="display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#eff6ff; color:#2563eb; border-radius:8px; text-decoration:none; font-size:13px; font-weight:600; border:1px solid #bfdbfe; transition:all .2s;">
                    <i class="fa-solid fa-download"></i> Descargar Datos del Mes (CSV)
                </a>
            </div>

            <!-- Zona de carga -->
            <div id="zonaCargaCsv" style="border:2px dashed #cbd5e1; border-radius:12px; padding:30px 20px; text-align:center; cursor:pointer; transition:all .2s; margin-bottom:20px; background:#f8fafc;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size:36px; color:#94a3b8; margin-bottom:8px;"></i>
                <p style="margin:0; font-size:14px; color:#64748b;">Arrastra tu archivo CSV aquí o <span style="color:#2563eb; font-weight:600; text-decoration:underline;">haz clic para seleccionar</span></p>
                <p id="nombreArchivoCsv" style="margin:8px 0 0; font-size:12px; color:#10b981; font-weight:600; display:none;"></p>
                <input type="file" id="inputArchivoCsv" accept=".csv" style="display:none;">
            </div>

            <!-- Botón subir -->
            <button id="btnSubirCargaMasiva" disabled style="width:100%; padding:12px; border:none; border-radius:10px; background:#10b981; color:white; font-size:15px; font-weight:600; cursor:pointer; opacity:0.5; transition:all .2s;">
                <i class="fa-solid fa-upload"></i> Subir e Importar Datos
            </button>

            <!-- Resultados -->
            <div id="resultadosCargaMasiva" style="display:none; margin-top:20px; padding:16px; border-radius:10px; background:#f8fafc; border:1px solid #e2e8f0;">
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeInModal {
            from { opacity:0; transform:scale(0.95); }
            to { opacity:1; transform:scale(1); }
        }
        #zonaCargaCsv.dragover { border-color:#10b981; background:#ecfdf5; }
        #linkDescargarPlantilla:hover { background:#dbeafe; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Detectar módulo actual
        const path = window.location.pathname;
        let modulo = '', tituloModulo = '', rutaUpload = '', rutaPlantilla = '';

        if (path.includes('/admin/usuarios')) {
            modulo = 'usuarios'; tituloModulo = 'Carga Masiva de Usuarios';
            rutaUpload = '/admin/usuarios/carga_masiva'; rutaPlantilla = '/admin/usuarios/plantilla';
        } else if (path.includes('/admin/agenda')) {
            modulo = 'agenda'; tituloModulo = 'Carga Masiva de Citas';
            rutaUpload = '/admin/agenda/carga_masiva'; rutaPlantilla = '/admin/agenda/plantilla';
        } else if (path.includes('/admin/horarios')) {
            modulo = 'horarios'; tituloModulo = 'Carga Masiva de Horarios';
            rutaUpload = '/admin/horarios/carga_masiva'; rutaPlantilla = '/admin/horarios/plantilla';
        } else if (path.includes('/admin/facturacion')) {
            modulo = 'facturacion'; tituloModulo = 'Carga Masiva de Facturación';
            rutaUpload = '/admin/facturacion/carga_masiva'; rutaPlantilla = '/admin/facturacion/plantilla';
        }

        const modal = document.getElementById('modalCargaMasiva');
        const btnAbrir = document.getElementById('btnAbrirModalCargaMasiva');
        const btnCerrar = document.getElementById('btnCerrarModalCM');
        const titulo = document.getElementById('tituloCargaMasiva');
        const linkPlantilla = document.getElementById('linkDescargarPlantilla');
        const zonaCarga = document.getElementById('zonaCargaCsv');
        const inputFile = document.getElementById('inputArchivoCsv');
        const nombreArchivo = document.getElementById('nombreArchivoCsv');
        const btnSubir = document.getElementById('btnSubirCargaMasiva');
        const resultados = document.getElementById('resultadosCargaMasiva');

        if (!btnAbrir || !modal) return;

        titulo.textContent = tituloModulo;
        linkPlantilla.href = rutaPlantilla;

        // Abrir modal
        btnAbrir.addEventListener('click', function() {
            modal.style.display = 'flex';
            inputFile.value = '';
            nombreArchivo.style.display = 'none';
            btnSubir.disabled = true;
            btnSubir.style.opacity = '0.5';
            resultados.style.display = 'none';
        });

        // Cerrar modal
        btnCerrar.addEventListener('click', function() { modal.style.display = 'none'; });
        modal.addEventListener('click', function(e) { if (e.target === modal) modal.style.display = 'none'; });

        // Zona de carga - click
        zonaCarga.addEventListener('click', function() { inputFile.click(); });

        // Drag & drop
        zonaCarga.addEventListener('dragover', function(e) { e.preventDefault(); zonaCarga.classList.add('dragover'); });
        zonaCarga.addEventListener('dragleave', function() { zonaCarga.classList.remove('dragover'); });
        zonaCarga.addEventListener('drop', function(e) {
            e.preventDefault();
            zonaCarga.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                inputFile.files = e.dataTransfer.files;
                mostrarArchivo(e.dataTransfer.files[0]);
            }
        });

        // Seleccionar archivo
        inputFile.addEventListener('change', function() {
            if (inputFile.files.length > 0) mostrarArchivo(inputFile.files[0]);
        });

        function mostrarArchivo(file) {
            if (!file.name.endsWith('.csv')) {
                Swal.fire({ icon: 'error', title: 'Formato inválido', text: 'Solo se permiten archivos .csv', confirmButtonColor: '#2563eb' });
                inputFile.value = '';
                return;
            }
            nombreArchivo.textContent = '📄 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            nombreArchivo.style.display = 'block';
            btnSubir.disabled = false;
            btnSubir.style.opacity = '1';
        }

        // Subir archivo
        btnSubir.addEventListener('click', function() {
            if (!inputFile.files.length) return;

            const formData = new FormData();
            formData.append('archivo_csv', inputFile.files[0]);

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const headers = {};
            if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken.content;

            btnSubir.disabled = true;
            btnSubir.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
            btnSubir.style.opacity = '0.7';

            fetch(rutaUpload, {
                method: 'POST',
                headers: headers,
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btnSubir.innerHTML = '<i class="fa-solid fa-upload"></i> Subir e Importar Datos';
                btnSubir.disabled = false;
                btnSubir.style.opacity = '1';

                if (data.success) {
                    let html = '<div style="text-align:center; margin-bottom:10px;"><i class="fa-solid fa-circle-check" style="font-size:28px; color:#10b981;"></i></div>';
                    html += '<p style="text-align:center; font-weight:600; color:#1e293b; margin:0 0 12px;">Importación completada</p>';
                    html += '<div style="display:flex; gap:12px; justify-content:center;">';
                    html += '<div style="background:#ecfdf5; padding:10px 18px; border-radius:8px; text-align:center;"><span style="font-size:20px; font-weight:700; color:#10b981;">' + data.successCount + '</span><br><span style="font-size:11px; color:#64748b;">Exitosos</span></div>';
                    html += '<div style="background:#fef2f2; padding:10px 18px; border-radius:8px; text-align:center;"><span style="font-size:20px; font-weight:700; color:#ef4444;">' + data.errorCount + '</span><br><span style="font-size:11px; color:#64748b;">Errores</span></div>';
                    html += '</div>';

                    if (data.erroresDetalle && data.erroresDetalle.length > 0) {
                        html += '<div style="margin-top:12px; max-height:120px; overflow-y:auto; font-size:12px; color:#ef4444; background:#fff5f5; padding:10px; border-radius:6px; border:1px solid #fecaca;">';
                        data.erroresDetalle.forEach(function(err) { html += '<div style="margin-bottom:4px;">⚠️ ' + err + '</div>'; });
                        html += '</div>';
                    }

                    resultados.innerHTML = html;
                    resultados.style.display = 'block';

                    if (data.successCount > 0) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Importación exitosa!',
                            text: data.successCount + ' registro(s) importados correctamente' + (data.errorCount > 0 ? ', ' + data.errorCount + ' con errores' : ''),
                            confirmButtonColor: '#10b981'
                        }).then(function() {
                            if (typeof cargarDatos === 'function') cargarDatos();
                            else location.reload();
                        });
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al procesar el archivo', confirmButtonColor: '#ef4444' });
                }
            })
            .catch(function(err) {
                btnSubir.innerHTML = '<i class="fa-solid fa-upload"></i> Subir e Importar Datos';
                btnSubir.disabled = false;
                btnSubir.style.opacity = '1';
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor. Intenta de nuevo.', confirmButtonColor: '#ef4444' });
            });
        });
    });
    </script>
    @endif
    {{-- ============== FIN MODAL CARGA MASIVA ============== --}}
</body>
</html>