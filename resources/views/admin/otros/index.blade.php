@extends('layouts.admin')

@section('titulo', 'Otros Catálogos - Panel Admin')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/administrador/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/administrador/otros.css') }}">
    <script>
        (function() {
            var phpTab = "{{ $tabActiva ?? '' }}";
            var urlParams = new URLSearchParams(window.location.search);
            var tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
            var savedTab = localStorage.getItem('activeOtrosTab');
            var tab = phpTab || (tabParam && ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades', 'contactos'].includes(tabParam.toLowerCase()) ? tabParam.toLowerCase() : (savedTab || 'procedimientos'));
            if (!['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades', 'contactos'].includes(tab)) tab = 'procedimientos';

            document.write('<style>.tab-content-otros { display: none !important; } #' + tab + 'Content { display: block !important; }</style>');
        })();
    </script>
@endpush

@section('contenido')
    <!-- CONTENEDOR PRINCIPAL BLANCO -->
    <div class="panel-otros">
        
        <!-- Encabezado interno -->
        <div class="header-otros">
            <div class="icon-circle-otros">
                <i class="fa-solid fa-gear"></i>
            </div>
            <div class="titles-otros">
                <h2>Otros</h2>
                <p>Administración de catálogos del sistema</p>
            </div>
        </div>

        <!-- Pestañas (Tabs) -->
        <div class="tabs-otros" id="mainTabs">
            <span class="tab-btn-otros {{ ($tabActiva === 'procedimientos' || empty($tabActiva)) ? 'active' : '' }}" data-tab="procedimientos">
                <i class="fa-solid fa-tooth"></i> Procedimientos
            </span>
            <span class="tab-btn-otros {{ ($tabActiva === 'especialidades') ? 'active' : '' }}" data-tab="especialidades">
                <i class="fa-solid fa-star"></i> Especialidades
            </span>
            <span class="tab-btn-otros {{ ($tabActiva === 'eps') ? 'active' : '' }}" data-tab="eps">
                <i class="fa-solid fa-hospital"></i> EPS
            </span>
            <span class="tab-btn-otros {{ ($tabActiva === 'alergias') ? 'active' : '' }}" data-tab="alergias">
                <i class="fa-solid fa-shield-virus"></i> Alergias
            </span>
            <span class="tab-btn-otros {{ ($tabActiva === 'enfermedades') ? 'active' : '' }}" data-tab="enfermedades">
                <i class="fa-solid fa-notes-medical"></i> Enfermedades
            </span>
            <span class="tab-btn-otros {{ ($tabActiva === 'contactos') ? 'active' : '' }}" data-tab="contactos">
                <i class="fa-solid fa-headset"></i> Contactos Web
                @if(!empty($statsContactos['pendientes']) && $statsContactos['pendientes'] > 0)
                    <span class="badge-tab-alerta">{{ $statsContactos['pendientes'] }}</span>
                @endif
            </span>
        </div>

        <!-- ==============================================
             PESTAÑA PROCEDIMIENTOS
        =============================================== -->
        <div class="tab-content-otros" id="procedimientosContent" style="{{ ($tabActiva === 'procedimientos' || empty($tabActiva)) ? 'display: block;' : 'display: none;' }}">
            <div class="toolbar-otros">
                <button class="btn-primary-otros" id="btnNuevoProcedimiento">
                    <i class="fa-solid fa-plus"></i> Nuevo procedimiento
                </button>
                <div class="toolbar-right-otros">
                    <div class="search-box-otros">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar procedimiento...">
                    </div>
                </div>
            </div>

            <div class="table-container-otros">
                <table class="table-otros" id="procedimientosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>DESCRIPCIÓN</th>
                            <th>DURACIÓN</th>
                            <th>PRECIO BASE</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($procedimientos))
                            @foreach($procedimientos as $proc)
                            <tr>
                                <td class="col-id-otros">{{ $proc['id'] }}</td>
                                <td>
                                    <div class="proc-name-otros">{{ $proc['nombre'] }}</div>
                                </td>
                                <td style="font-size: 13px; color: #475569;">
                                    {{ $proc['descripcion'] }}
                                </td>
                                <td class="col-duracion-otros">
                                    <i class="fa-regular fa-clock"></i> {{ $proc['duracion'] }}
                                </td>
                                <td class="col-precio-otros">{{ $proc['precio'] }}</td>
                                <td>
                                    <span class="badge-otros {{ $proc['css_estado'] }}">
                                        {{ $proc['estado'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" onclick="abrirModalEditar({{ $proc['id'] }}, '{{ addslashes($proc['nombre']) }}', '{{ addslashes($proc['descripcion']) }}', {{ $proc['precio_num'] }}, {{ $proc['duracion_num'] }}, '{{ strtoupper($proc['estado']) }}')"><i class="fa-solid fa-pencil"></i></button>
                                        <button class="btn-action delete" onclick="eliminarProcedimiento({{ $proc['id'] }})"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">No hay procedimientos registrados.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================
             PESTAÑA EPS
        =============================================== -->
        <div class="tab-content-otros" id="epsContent" style="display: none;">
            <div class="toolbar-otros">
                <button class="btn-primary-otros" id="btnNuevaEps">
                    <i class="fa-solid fa-plus"></i> Nueva EPS
                </button>
                <div class="toolbar-right-otros">
                    <div class="search-box-otros">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar EPS..." id="searchInputEps">
                    </div>
                </div>
            </div>

            <div class="table-container-otros">
                <table class="table-otros" id="epsTable">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>NOMBRE</th>
                            <th>DIRECCIÓN</th>
                            <th>TELÉFONO</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($listaEps))
                            @foreach($listaEps as $eps)
                            <tr>
                                <td class="col-id-otros">EPS-{{ str_pad($eps['id'], 3, '0', STR_PAD_LEFT) }}</td>
                                <td><div class="proc-name-otros">{{ $eps['nombre'] }}</div></td>
                                <td><span style="font-size: 13px; color: #475569;">{{ !empty($eps['direccion']) ? $eps['direccion'] : '—' }}</span></td>
                                <td><span style="font-size: 13px; color: #475569;">{{ !empty($eps['telefono']) ? $eps['telefono'] : '—' }}</span></td>
                                <td><span class="badge-otros {{ $eps['css_estado'] }}">{{ $eps['estado'] }}</span></td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEps('{{ $eps['id'] }}', '{{ addslashes($eps['nombre']) }}', '{{ addslashes($eps['direccion'] ?? '') }}', '{{ addslashes($eps['telefono'] ?? '') }}', '{{ strtoupper($eps['estado']) }}')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action delete" title="Eliminar" onclick="eliminarEps('{{ $eps['id'] }}')"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">No hay EPS registradas.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================
             PESTAÑA ESPECIALIDADES
        =============================================== -->
        <div class="tab-content-otros" id="especialidadesContent" style="display: none;">
            <div class="toolbar-otros">
                <button class="btn-primary-otros" id="btnNuevaEspecialidad">
                    <i class="fa-solid fa-plus"></i> Nueva especialidad
                </button>
                <div class="toolbar-right-otros">
                    <div class="search-box-otros">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar especialidad..." id="searchInputEspecialidad">
                    </div>
                </div>
            </div>

            <div class="table-container-otros">
                <table class="table-otros" id="especialidadesTable">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>NOMBRE</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($especialidades))
                            @foreach($especialidades as $esp)
                            <tr>
                                <td class="col-id-otros">ESP-{{ str_pad($esp['id'], 3, '0', STR_PAD_LEFT) }}</td>
                                <td><div class="proc-name-otros">{{ $esp['nombre'] }}</div></td>
                                <td><span class="badge-otros {{ $esp['css_estado'] }}">{{ $esp['estado'] }}</span></td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEspecialidad('{{ $esp['id'] }}', '{{ addslashes($esp['nombre']) }}', '{{ strtoupper($esp['estado']) }}')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action delete" title="Eliminar" onclick="eliminarEspecialidad('{{ $esp['id'] }}')"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">No hay especialidades registradas.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================
             PESTAÑA ALERGIAS
        =============================================== -->
        <div class="tab-content-otros" id="alergiasContent" style="{{ ($tabActiva === 'alergias') ? 'display: block;' : 'display: none;' }}">
            <div class="toolbar-otros">
                <button class="btn-primary-otros" id="btnNuevaAlergia">
                    <i class="fa-solid fa-plus"></i> Nueva alergia
                </button>
                <div class="toolbar-right-otros">
                    <div class="search-box-otros">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar alergia..." id="searchInputAlergia">
                    </div>
                </div>
            </div>

            <div class="table-container-otros">
                <table class="table-otros" id="alergiasTable">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>NOMBRE</th>
                            <th>DESCRIPCIÓN</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($alergias))
                            @foreach($alergias as $alg)
                            <tr>
                                <td class="col-id-otros">ALG-{{ str_pad($alg['id'], 3, '0', STR_PAD_LEFT) }}</td>
                                <td><div class="proc-name-otros">{{ $alg['nombre'] }}</div></td>
                                <td style="font-size: 13px; color: #475569;">{{ $alg['descripcion'] ?? '—' }}</td>
                                <td><span class="badge-otros {{ $alg['css_estado'] }}">{{ $alg['estado'] }}</span></td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" title="Editar" onclick="abrirModalEditarAlergia('{{ $alg['id'] }}', '{{ addslashes($alg['nombre']) }}', '{{ addslashes($alg['descripcion'] ?? '') }}', '{{ strtoupper($alg['estado']) }}')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action delete" title="Eliminar" onclick="eliminarAlergia('{{ $alg['id'] }}')"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">No hay alergias registradas.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================
             PESTAÑA ENFERMEDADES
        =============================================== -->
        <div class="tab-content-otros" id="enfermedadesContent" style="{{ ($tabActiva === 'enfermedades') ? 'display: block;' : 'display: none;' }}">
            <div class="toolbar-otros">
                <button class="btn-primary-otros" id="btnNuevaEnfermedad">
                    <i class="fa-solid fa-plus"></i> Nueva enfermedad
                </button>
                <div class="toolbar-right-otros">
                    <div class="search-box-otros">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar enfermedad..." id="searchInputEnfermedad">
                    </div>
                </div>
            </div>

            <div class="table-container-otros">
                <table class="table-otros" id="enfermedadesTable">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>NOMBRE</th>
                            <th>DESCRIPCIÓN</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($enfermedades))
                            @foreach($enfermedades as $enf)
                            <tr>
                                <td class="col-id-otros">ENF-{{ str_pad($enf['id'], 3, '0', STR_PAD_LEFT) }}</td>
                                <td><div class="proc-name-otros">{{ $enf['nombre'] }}</div></td>
                                <td style="font-size: 13px; color: #475569;">{{ $enf['descripcion'] ?? '—' }}</td>
                                <td><span class="badge-otros {{ $enf['css_estado'] }}">{{ $enf['estado'] }}</span></td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEnfermedad('{{ $enf['id'] }}', '{{ addslashes($enf['nombre']) }}', '{{ addslashes($enf['descripcion'] ?? '') }}', '{{ strtoupper($enf['estado']) }}')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action delete" title="Eliminar" onclick="eliminarEnfermedad('{{ $enf['id'] }}')"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">No hay enfermedades registradas.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==============================================
             PESTAÑA CONTACTOS WEB (LANDING)
        =============================================== -->
        <div class="tab-content-otros" id="contactosContent" style="{{ ($tabActiva === 'contactos') ? 'display: block;' : 'display: none;' }}">
            
            <!-- Mini KPIs de Contactos -->
            <div class="contactos-kpi-grid">
                <div class="contactos-kpi-card">
                    <div class="contactos-kpi-icon total">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <div class="contactos-kpi-info">
                        <span class="kpi-num">{{ $statsContactos['total'] ?? 0 }}</span>
                        <span class="kpi-label">Total Solicitudes</span>
                    </div>
                </div>
                <div class="contactos-kpi-card">
                    <div class="contactos-kpi-icon pendientes">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="contactos-kpi-info">
                        <span class="kpi-num">{{ $statsContactos['pendientes'] ?? 0 }}</span>
                        <span class="kpi-label">Pendientes por Atender</span>
                    </div>
                </div>
                <div class="contactos-kpi-card">
                    <div class="contactos-kpi-icon contactados">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="contactos-kpi-info">
                        <span class="kpi-num">{{ $statsContactos['contactados'] ?? 0 }}</span>
                        <span class="kpi-label">Gestionados / Atendidos</span>
                    </div>
                </div>
            </div>

            <div class="toolbar-otros">
                <div class="toolbar-left-otros" style="display:flex;gap:12px;align-items:center;">
                    <select id="filterEstadoContactos" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; background: white; outline: none;">
                        <option value="">Todos los Estados</option>
                        <option value="Pendiente">Solo Pendientes</option>
                        <option value="Contactado">Solo Contactados</option>
                        <option value="Descartado">Solo Descartados</option>
                    </select>
                </div>
                <div class="toolbar-right-otros">
                    <div class="search-box-otros">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Buscar por nombre, correo o mensaje..." id="searchInputContactos">
                    </div>
                </div>
            </div>

            <div class="table-container-otros">
                <table class="table-otros" id="contactosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FECHA</th>
                            <th>PROSPECTO</th>
                            <th>TELÉFONO</th>
                            <th>INTERÉS</th>
                            <th>MENSAJE</th>
                            <th>ESTADO</th>
                            <th>GESTIONADO POR</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($contactos))
                            @foreach($contactos as $c)
                            <tr>
                                <td class="col-id-otros">#{{ $c['id'] }}</td>
                                <td style="font-size: 12px; color: #64748b; white-space: nowrap;">
                                    <i class="fa-regular fa-clock" style="margin-right: 3px;"></i> {{ $c['fecha'] }}
                                </td>
                                <td>
                                    <div class="proc-name-otros">{{ $c['nombre'] }}</div>
                                    <div class="proc-desc-otros"><i class="fa-regular fa-envelope"></i> {{ $c['correo'] }}</div>
                                </td>
                                <td>
                                    @if(!empty($c['telefono']))
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span style="font-size: 13px; color: #334155;">{{ $c['telefono'] }}</span>
                                            @if(!empty($c['whatsapp_url']))
                                                <a href="{{ $c['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-action" title="Abrir chat en WhatsApp">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-size: 12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 12.5px; font-weight: 600; color: #0284c7; background: #e0f2fe; padding: 3px 8px; border-radius: 4px;">
                                        {{ $c['servicio'] ?? 'Consulta General' }}
                                    </span>
                                </td>
                                <td style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12.5px; color: #475569;" title="{{ $c['mensaje'] }}">
                                    {{ $c['mensaje'] }}
                                </td>
                                <td>
                                    <span class="badge-otros {{ $c['css_estado'] }}">
                                        {{ $c['estado'] }}
                                    </span>
                                </td>
                                <td style="font-size: 12px; color: #64748b;">
                                    {{ $c['admin_gestion'] ?? 'Sin asignar' }}
                                </td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" title="Ver detalle y gestionar" 
                                            onclick='abrirModalGestionContacto(@json($c))'>
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="btn-action delete" title="Eliminar registro" 
                                            onclick="eliminarContacto({{ $c['id'] }})">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #64748b;">No hay mensajes de contacto registrados.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Procedimiento -->
    <div id="modalProcedimiento" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModalProcedimiento">&times;</span>
            <h2 id="modalProcTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
                <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nuevo Procedimiento</span>
            </h2>
            <form id="formProcedimiento">
                <input type="hidden" id="formProcId" value="">
                <div class="form-group">
                    <label>Nombre del Procedimiento</label>
                    <input type="text" id="formProcNombre" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" id="formProcDesc" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label>Precio Base ($)</label>
                        <input type="number" id="formProcCosto" required>
                    </div>
                    <div class="form-group">
                        <label>Duración (Minutos)</label>
                        <input type="number" id="formProcTiempo" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="formProcEstado" required>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <button type="submit" id="modalProcSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Guardar Procedimiento
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Especialidad -->
    <div id="modalEspecialidad" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close-modal" id="closeModalEspecialidad">&times;</span>
            <h2 id="modalEspTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
                <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Especialidad</span>
            </h2>
            <form id="formEspecialidad">
                <input type="hidden" id="formEspId" value="">
                <div class="form-group">
                    <label>Nombre de la Especialidad</label>
                    <input type="text" id="formEspNombre" required>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="formEspEstado" required>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <button type="submit" id="modalEspSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Guardar Especialidad
                </button>
            </form>
        </div>
    </div>

    <!-- Modal EPS -->
    <div id="modalEps" class="modal">
        <div class="modal-content" style="max-width: 440px;">
            <span class="close-modal" id="closeModalEps">&times;</span>
            <h2 id="modalEpsTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
                <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva EPS</span>
            </h2>
            <form id="formEps">
                <input type="hidden" id="formEpsId" value="">
                <div class="form-group">
                    <label>Nombre de la EPS</label>
                    <input type="text" id="formEpsNombre" placeholder="Ej: SURA, Sanitas..." required>
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" id="formEpsDireccion" placeholder="Ej: Carrera 57 # 98-30">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="formEpsTelefono" placeholder="Ej: 3128567456">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="formEpsEstado" required>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <button type="submit" id="modalEpsSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Guardar EPS
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Alergia -->
    <div id="modalAlergia" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <span class="close-modal" id="closeModalAlergia">&times;</span>
            <h2 id="modalAlgTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
                <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Alergia</span>
            </h2>
            <form id="formAlergia">
                <input type="hidden" id="formAlgId" value="">
                <div class="form-group">
                    <label>Nombre de la Alergia</label>
                    <input type="text" id="formAlgNombre" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" id="formAlgDesc">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="formAlgEstado" required>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <button type="submit" id="modalAlgSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Guardar Alergia
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Enfermedad -->
    <div id="modalEnfermedad" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <span class="close-modal" id="closeModalEnfermedad">&times;</span>
            <h2 id="modalEnfTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
                <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Enfermedad</span>
            </h2>
            <form id="formEnfermedad">
                <input type="hidden" id="formEnfId" value="">
                <div class="form-group">
                    <label>Nombre de la Enfermedad</label>
                    <input type="text" id="formEnfNombre" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" id="formEnfDesc">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="formEnfEstado" required>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <button type="submit" id="modalEnfSubmitBtn" class="btn-primary-otros" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Guardar Enfermedad
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Gestionar Contacto Web -->
    <div id="modalGestionarContacto" class="modal">
        <div class="modal-content" style="max-width: 560px;">
            <span class="close-modal" id="closeModalGestionarContacto">&times;</span>
            <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:18px;">
                <i class="fa-solid fa-headset" style="color:#0d6efd;margin-right:8px;"></i>
                <span>Gestión de Prospecto Web</span>
            </h2>
            
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                    <div>
                        <strong id="modalDetalleNombre" style="font-size:15px;color:#0f172a;"></strong>
                        <div id="modalDetalleFecha" style="font-size:12px;color:#64748b;margin-top:2px;"></div>
                    </div>
                    <span id="modalDetalleBadgeEstado" class="badge-otros"></span>
                </div>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px;color:#334155;margin-bottom:10px;">
                    <div>
                        <i class="fa-regular fa-envelope" style="color:#64748b;margin-right:4px;"></i>
                        <a id="modalDetalleEmailLink" href="" style="color:#0d6efd;text-decoration:none;word-break:break-all;"></a>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fa-solid fa-phone" style="color:#64748b;"></i>
                        <span id="modalDetalleTelefono"></span>
                        <a id="modalDetalleWaBtn" href="#" target="_blank" class="btn-whatsapp-action" style="display:none;" title="Abrir WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div style="font-size:13px;color:#334155;margin-bottom:8px;">
                    <strong>Interés:</strong> <span id="modalDetalleServicio" style="color:#0284c7;font-weight:500;"></span>
                </div>

                <div style="font-size:13px;">
                    <strong style="display:block;margin-bottom:4px;color:#475569;">Mensaje enviado:</strong>
                    <div id="modalDetalleMensaje" style="background:#ffffff;border:1px solid #cbd5e1;padding:10px;border-radius:6px;font-size:13px;color:#1e293b;max-height:120px;overflow-y:auto;white-space:pre-wrap;line-height:1.4;"></div>
                </div>
            </div>

            <form id="formGestionarContacto">
                <input type="hidden" id="formContactoId" value="">
                <div class="form-group">
                    <label>Estado de la Gestión</label>
                    <select id="formContactoEstado" required>
                        <option value="PENDIENTE">PENDIENTE (Pendiente por contactar)</option>
                        <option value="CONTACTADO">CONTACTADO (Ya se atendió / en seguimiento)</option>
                        <option value="DESCARTADO">DESCARTADO (No interesado / Número erróneo)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Observaciones del Administrador</label>
                    <textarea id="formContactoObservaciones" rows="3" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;color:#1e293b;font-family:inherit;outline:none;" placeholder="Ej: Se llamó al prospecto y agendó cita de valoración para el jueves..."></textarea>
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button type="button" class="btn-outline-otros" id="btnCerrarModalContacto" style="flex:1;justify-content:center;">Cerrar</button>
                    <button type="submit" id="modalContactoSubmitBtn" class="btn-primary-otros" style="flex:2;justify-content:center;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Gestión
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/administrador/otros.js') }}?v={{ time() }}"></script>
@endpush
