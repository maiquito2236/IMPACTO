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
            var tab = phpTab || (tabParam && ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'].includes(tabParam.toLowerCase()) ? tabParam.toLowerCase() : (savedTab || 'procedimientos'));
            if (!['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'].includes(tab)) tab = 'procedimientos';

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
                                <td><span class="badge-otros {{ $eps['css_estado'] }}">{{ $eps['estado'] }}</span></td>
                                <td>
                                    <div class="actions-group-otros">
                                        <button class="btn-action edit" title="Editar" onclick="abrirModalEditarEps('{{ $eps['id'] }}', '{{ addslashes($eps['nombre']) }}', '{{ strtoupper($eps['estado']) }}')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action delete" title="Eliminar" onclick="eliminarEps('{{ $eps['id'] }}')"><i class="fa-regular fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">No hay EPS registradas.</td>
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
        <div class="modal-content" style="max-width: 400px;">
            <span class="close-modal" id="closeModalEps">&times;</span>
            <h2 id="modalEpsTitle" style="margin-top:0;font-size:18px;display:flex;align-items:center;color:#1e293b;margin-bottom:20px;">
                <i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva EPS</span>
            </h2>
            <form id="formEps">
                <input type="hidden" id="formEpsId" value="">
                <div class="form-group">
                    <label>Nombre de la EPS</label>
                    <input type="text" id="formEpsNombre" required>
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/administrador/otros.js') }}"></script>
@endpush
