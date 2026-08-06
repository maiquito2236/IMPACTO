<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda y Citas — Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/agenda.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>
 
<div class="menu-layout">
 
    <?php require_once __DIR__ . '/layouts/menu.php'; ?>
 
    <main class="menu-main-content">

        <?php require_once __DIR__ . '/layouts/header.php'; ?>

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
            <div id="calendarContainer">
                </div>
 
            <aside class="panel">
                <h3 style="margin-bottom:16px;font-size:15px;font-weight:700;">
                    <i class="fa-regular fa-calendar-check" style="color:#2563eb;margin-right:6px;"></i>
                    Pacientes de Hoy
                </h3>
                <ul class="patient-list" id="listaPacientesHoy">
                    </ul>
                <button class="btn-primary" id="btnNuevaCita2" style="width:100%;justify-content:center;margin-top:16px;"
                    onclick="document.getElementById('btnNuevaCita').click()">
                    <i class="fa-solid fa-plus"></i> Agendar Cita
                </button>
            </aside>
        </div>
 
    </main>
</div>
 
<div id="modalCita" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeModalCita">&times;</span>
        <h2 style="margin-bottom:20px;font-size:18px;"><i class="fa-solid fa-calendar-plus" style="color:#2563eb;margin-right:8px;"></i>Nueva Cita</h2>
        <form id="formCita">
            <div class="form-group">
                <label>Paciente *</label>
                <select id="fCitaPaciente" required>
                    <option value="">— Seleccionar paciente —</option>
                </select>
            </div>
            <div class="form-group">
                <label>Odontólogo *</label>
                <select id="fCitaOdontologo" required>
                    <option value="">— Seleccionar odontólogo —</option>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" id="fCitaFecha" required>
                </div>
                <div class="form-group">
                    <label>Hora *</label>
                    <select id="fCitaHora" required>
                        <option value="">— Seleccionar hora —</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Tratamiento *</label>
                <select id="fCitaTratamiento" required>
                    <option value="">— Seleccionar tratamiento —</option>
                </select>
            </div>
            <div class="form-group">
                <label>Color de la cita</label>
                <div class="color-picker-row">
                    <label class="color-opt"><input type="radio" name="citaColor" id="fCitaColor" value="apt-blue" checked> <span class="dot apt-blue-dot"></span> Azul</label>
                    <label class="color-opt"><input type="radio" name="citaColor" value="apt-green"> <span class="dot apt-green-dot"></span> Verde</label>
                    <label class="color-opt"><input type="radio" name="citaColor" value="apt-purple"> <span class="dot apt-purple-dot"></span> Morado</label>
                    <label class="color-opt"><input type="radio" name="citaColor" value="apt-orange"> <span class="dot apt-orange-dot"></span> Naranja</label>
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Confirmar Cita
            </button>
        </form>
    </div>
</div>
 
<div id="modalReprogramar" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeModalReprogramar">&times;</span>
        <h2 style="margin-bottom:20px;font-size:18px;"><i class="fa-regular fa-calendar" style="color:#f59e0b;margin-right:8px;"></i>Reprogramar Cita</h2>
        <form id="formReprogramar">
            <div class="form-group">
                <label>Seleccionar Cita</label>
                <select id="rSeleccionCita"></select>
            </div>
            <div class="form-group">
                <label>Cita seleccionada</label>
                <div id="rCitaInfo" style="background:#f8fafc;padding:10px;border-radius:6px;font-size:13px;color:#475569;border:1px solid #e2e8f0;"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Nueva Fecha *</label>
                    <input type="date" id="rCitaFecha" required>
                </div>
                <div class="form-group">
                    <label>Nueva Hora *</label>
                    <select id="rCitaHora" required>
                        <option value="">— Seleccionar hora —</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Motivo (opcional)</label>
                <input type="text" id="rMotivo" placeholder="Ej: Paciente solicitó cambio">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Guardar Cambios
            </button>
        </form>
    </div>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/LOGIN_ORIGINAL/public/js/administrador/agenda.js"></script>
</body>
</html>