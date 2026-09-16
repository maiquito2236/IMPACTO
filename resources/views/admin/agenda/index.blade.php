@extends('layouts.admin')

@section('titulo', 'Agenda y Citas - Panel Admin')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/administrador/agenda.css') }}">
    <link rel="stylesheet" href="{{ asset('css/administrador/global.css') }}">
@endpush

@section('contenido')
    <div class="agenda-toolbar">
        <div class="toolbar-left">
            <h2>Gestión de Agenda</h2>
            <div class="nav-fecha">
                <button class="btn-nav" id="btnAnterior"><i class="fa-solid fa-chevron-left"></i></button>
                <span id="tituloRango">—</span>
                <button class="btn-nav" id="btnSiguiente"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="view-controls">
            <button class="btn-outline" id="btnVerHoy">Hoy</button>
            <div class="btn-group-container">
                <button class="btn-group" id="btnVistaDia"><i class="fa-regular fa-calendar-day"></i> Día</button>
                <button class="btn-group active" id="btnVistaSemana"><i class="fa-solid fa-border-all"></i> Semana</button>
                <button class="btn-group" id="btnVistaMes"><i class="fa-regular fa-calendar"></i> Mes</button>
            </div>
            <button class="btn-outline" id="btnReprogramarMain">
                <i class="fa-regular fa-calendar-check"></i> Reprogramar
            </button>
            <button class="btn-primary" id="btnNuevaCita">
                <i class="fa-solid fa-plus"></i> Nueva Cita
            </button>
        </div>
    </div>

    <div class="dashboard-grid">
        <div id="calendarContainer"></div>

        <aside class="panel">
            <h3 style="margin-bottom:16px;font-size:15px;font-weight:700;">
                <i class="fa-regular fa-calendar-check" style="color:#2563eb;margin-right:6px;"></i>
                Pacientes de Hoy
            </h3>
            <ul class="patient-list" id="listaPacientesHoy"></ul>
            <button class="btn-primary" id="btnNuevaCita2" style="width:100%;justify-content:center;margin-top:16px;" onclick="document.getElementById('btnNuevaCita').click()">
                <i class="fa-solid fa-plus"></i> Agendar Cita
            </button>
        </aside>
    </div>

    <!-- MODAL NUEVA CITA -->
    <div id="modalCita" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModalCita">&times;</span>
            <h2 style="margin-bottom:20px;font-size:18px;"><i class="fa-solid fa-calendar-plus" style="color:#2563eb;margin-right:8px;"></i>Nueva Cita</h2>
            <form id="formCita">
                <div class="form-group">
                    <label>Paciente *</label>
                    <select id="fCitaPaciente" required><option value="">— Seleccionar paciente —</option></select>
                </div>
                <div class="form-group">
                    <label>Odontólogo *</label>
                    <select id="fCitaOdontologo" required><option value="">— Seleccionar odontólogo —</option></select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label>Fecha *</label>
                        <input type="date" id="fCitaFecha" required>
                    </div>
                    <div class="form-group">
                        <label>Hora *</label>
                        <select id="fCitaHora" required><option value="">— Seleccionar hora —</option></select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tratamiento *</label>
                    <select id="fCitaTratamiento" required><option value="">— Seleccionar tratamiento —</option></select>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Confirmar Cita
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL REPROGRAMAR CITA -->
    <div id="modalReprogramar" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModalReprogramar">&times;</span>
            <h2 style="margin-bottom:20px;font-size:18px;"><i class="fa-regular fa-calendar" style="color:#f59e0b;margin-right:8px;"></i>Reprogramar Cita</h2>
            <form id="formReprogramar">
                <div class="form-group">
                    <label>Seleccionar Cita</label>
                    <select id="rSeleccionCita"></select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label>Nueva Fecha *</label>
                        <input type="date" id="rCitaFecha" required>
                    </div>
                    <div class="form-group">
                        <label>Nueva Hora *</label>
                        <select id="rCitaHora" required><option value="">— Seleccionar hora —</option></select>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;">
                    <i class="fa-solid fa-check"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/administrador/agenda.js') }}"></script>
@endpush