@extends('layouts.admin')

@section('titulo', 'Gestión de Consultorios')

<!-- Agregamos el CSS específico de esta página -->
@push('css')
    <link rel="stylesheet" href="{{ asset('css/administrador/consultorio.css') }}">
@endpush

@section('contenido')
    <header class="page-header">
        <div class="page-header-text">
            <h1>Gestión de Consultorios</h1>
            <p>Administra, asigna y consulta los consultorios y su relación con los odontólogos.</p>
        </div>
        <button class="btn-primary-action" onclick="abrirModalCrear()">
            <i class="fa-solid fa-plus"></i> Nuevo Consultorio
        </button>
    </header>

    <!-- KPIs -->
    <section class="kpi-grid-c">
        <!-- Tarjetas de colores... -->
        <div class="kpi-card kpi-card-blue">
            <div class="kpi-icon-box kpi-icon-blue"><i class="fa-solid fa-building"></i></div>
            <div class="kpi-text">
                <span class="kpi-label">Total Consultorios</span>
                <span class="kpi-number">{{ count($consultorios) }}</span>
                <span class="kpi-sub">Consultorios registrados</span>
            </div>
        </div>
        <!-- (Aquí puedes agregar las otras tarjetas verde, naranja, morada de tu original) -->
    </section>

    <!-- TABS -->
    <div class="tabs-bar-c">
        <span class="tab-btn-c active" data-tab-c="consulta">Consulta de Consultorios</span>
        <span class="tab-btn-c" data-tab-c="asignar">Asignar Consultorio</span>
        <span class="tab-btn-c" data-tab-c="historial">Historial de Asignaciones</span>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-grid-c">
        <!-- TAB: CONSULTA -->
        <div id="content-consulta" class="tab-content-c">
            <div class="panel-c">
                <div class="panel-header-c">
                    <h2 class="panel-title-c">Lista de Consultorios</h2>
                    <div class="panel-search-c">
                        <input type="text" id="buscadorConsultorio" placeholder="Buscar consultorio..." class="input-search-c">
                        <i class="fa-solid fa-magnifying-glass search-icon-c"></i>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table-c">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AQUÍ USAMOS BLADE PARA LLENAR LA TABLA -->
                            @foreach ($consultorios as $consul)
                                <tr>
                                    <td>{{ $consul->ID_CONSULTORIO }}</td>
                                    <td>{{ $consul->NOMBRE }}</td>
                                    <td>{{ $consul->UBICACION }}</td>
                                    <td>
                                        <span class="badge {{ $consul->ESTADO == 'DISPONIBLE' ? 'badge-disponible' : 'badge-asignado' }}">
                                            {{ $consul->ESTADO }}
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Botones de acción iguales a los de tu JS -->
                                        <button class="btn-icon-c" onclick="verDetalle({{ $consul->ID_CONSULTORIO }})" title="Ver Detalles">
                                            <i class="fa-solid fa-eye" style="color: #3b82f6;"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CREAR / EDITAR CONSULTORIO -->
    <div id="modalCrear" class="modal-c">
        <div class="modal-content-c">
            <span class="close-modal-c" id="closeModalCrear">&times;</span>
            <h2 id="modalCrearTitulo">Nuevo Consultorio</h2>
            
            <!-- IMPORTANTE: El formulario ahora envía datos a Laravel usando POST -->
            <form action="/admin/consultorios/guardar" method="POST">
                @csrf <!-- Esta etiqueta de seguridad es obligatoria en Laravel -->
                
                <div class="form-group-c">
                    <label>Nombre del Consultorio *</label>
                    <input type="text" name="NOMBRE" class="input-c" placeholder="Ej. Consultorio 7" required>
                </div>
                <div class="form-group-c">
                    <label>Ubicación</label>
                    <input type="text" name="UBICACION" class="input-c" placeholder="Ej. Piso 3">
                </div>
                <div class="form-group-c">
                    <label>Descripción</label>
                    <textarea name="DESCRIPCION" class="input-c" rows="3" placeholder="Descripción del consultorio..."></textarea>
                </div>
                <button type="submit" class="btn-primary-action" style="width:100%; justify-content:center; margin-top:10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar en Laravel
                </button>
            </form>
        </div>
    </div>
@endsection

<!-- Agregamos el JS al final de la página -->
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/administrador/consultorio.css') }}"></script>
    <script>
        // Funciones básicas para abrir y cerrar el modal
        function abrirModalCrear() {
            document.getElementById('modalCrear').style.display = 'flex';
        }
        document.getElementById('closeModalCrear').onclick = function() {
            document.getElementById('modalCrear').style.display = 'none';
        }
    </script>
@endpush