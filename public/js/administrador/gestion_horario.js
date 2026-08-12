window.odontologosDB = [];
window.listaOdontologos = [];
window.filtroJornada = 'todos';
window.busquedaTexto = '';
let currentViewDate = new Date();
let fechaCalendarioPrincipal = new Date();
let fechaSeleccionada = new Date();

// ── INIT ÚNICO Y CONFIGURACIÓN ───────────
document.addEventListener('DOMContentLoaded', () => {
    cargarDatosIniciales();
});

// ── CARGA DE DATOS ───────────
async function cargarDatosIniciales() {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/horarios/listar');
        const json = await response.json();
        
        window.odontologosDB = json.data || []; 
        window.listaOdontologos = json.listaOdontologos || [];
        
        poblarSelectOdontologos(); 
        configurarEventosModal();
        
        renderCalendarioPrincipal(); 

        if (document.getElementById('calendar-selector-container')) {
            renderCalendarSelector(); 
        }

        renderTabla();
    } catch (error) {
        console.error("Error cargando datos:", error);
        window.odontologosDB = [];
    }
}

// ── POBLAR SELECTS ────
window.tsOdontologo = null;

function poblarSelectOdontologos() {
    const selectNuevo = document.getElementById('odontologo_id');
    const plantillaHtml = '<option value="">Seleccionar odontólogo...</option>';
    
    if (selectNuevo) {
        if (window.tsOdontologo) {
            window.tsOdontologo.destroy();
        }
        selectNuevo.innerHTML = plantillaHtml;
        window.listaOdontologos.forEach(doc => {
            const option = document.createElement('option');
            option.value = doc.ID_ODONTOLOGO;
            option.textContent = `${doc.NOMBRES} ${doc.APELLIDOS}`;
            selectNuevo.appendChild(option);
        });

        window.tsOdontologo = new TomSelect(selectNuevo, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    }

    if(document.getElementById('kpi-odontologos-activos')) {
        document.getElementById('kpi-odontologos-activos').textContent = window.listaOdontologos.length;
    }
}

// ── FILTROS Y BÚSQUEDA ────────────────────
function filtrarTabla(jornada, boton) {
    document.querySelectorAll('.filter-tab').forEach(btn => btn.classList.remove('filter-tab--active'));
    if (boton) boton.classList.add('filter-tab--active');
    window.filtroJornada = jornada.toLowerCase();
    renderTabla();
}

function buscarOdontologo(texto) {
    window.busquedaTexto = texto.toLowerCase();
    renderTabla();
}

// ── RENDERIZAR TABLA ──────────
function renderTabla() {
    const tbody = document.getElementById('tabla-body');
    if (!tbody) return; 
    tbody.innerHTML = ''; 
    
    const datosFiltrados = obtenerDatosMesActual().filter(o => {
        const estaActivo = o.ESTADO !== 'Inactivo';
        const nombreCompleto = `${o.NOMBRES} ${o.APELLIDOS}`.toLowerCase();
        const coincideTexto = nombreCompleto.includes(window.busquedaTexto);

        let coincideJornada = true;
        if (window.filtroJornada !== 'todos') {
            const jornadaDb = (o.JORNADA || '').toLowerCase();
            coincideJornada = jornadaDb.includes(window.filtroJornada);
        }
        return estaActivo && coincideTexto && coincideJornada;
    });

    if (datosFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 20px;">No se encontraron resultados.</td></tr>';
        return;
    }
    
    datosFiltrados.forEach(o => {
        const tr = document.createElement('tr');
        const rangoHorario = `${o.HORA_INICIO.substring(0, 5)} - ${o.HORA_FIN.substring(0, 5)}`;
        const consultorio = o.CONSULTORIO || 'Sin asignar';
        
        tr.innerHTML = `
            <td><div class="doc-cell"><span class="doc-name"><strong>${o.NOMBRES} ${o.APELLIDOS}</strong></span></div></td>
            <td>${o.NOMBRE_PROCEDIMIENTO || 'Sin procedimiento'}</td>
            <td>${consultorio}</td> 
            <td>${rangoHorario}</td>
            <td>${o.FECHA || 'No definida'}</td> 
            <td>
                <span class="badge-status ${o.ESTADO === 'Ocupado' ? 'badge-status--busy' : 'badge-status--active'}">
                    ${o.ESTADO || 'Disponible'}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <button class="action-btn action-btn--edit" onclick="editarHorario(${o.ID_HORARIO})"><i class="fa-solid fa-pen"></i></button>
                    <button class="action-btn action-btn--del" onclick="eliminarHorario(${o.ID_HORARIO})"><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ── CALENDARIO PRINCIPAL ──────────────────
function renderCalendarioPrincipal() {
    const container = document.getElementById('calendario-mensual-container');
    const textoMes = document.getElementById('mes-actual-texto');
    if (!container) return;

    const year = fechaCalendarioPrincipal.getFullYear();
    const month = fechaCalendarioPrincipal.getMonth();
    const today = new Date();

    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    if(textoMes) textoMes.textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    let html = '';
    const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    diasSemana.forEach(d => html += `<div class="cal-mes-header">${d}</div>`);

    for (let i = 0; i < firstDay; i++) {
        html += `<div class="cal-mes-celda cal-mes-celda--vacia"></div>`;
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) ? 'cal-mes-hoy' : '';

        const turnosDelDia = window.odontologosDB.filter(h => h.FECHA === dateStr && h.ESTADO !== 'Inactivo');
        turnosDelDia.sort((a, b) => a.HORA_INICIO.localeCompare(b.HORA_INICIO));

        let turnosHtml = '';
        turnosDelDia.forEach(t => {
            const hora = t.HORA_INICIO.substring(0, 5);
            turnosHtml += `<div class="turno-mensual" title="${t.NOMBRES} ${t.APELLIDOS} (${hora})"><strong>${hora}</strong> ${t.NOMBRES}</div>`;
        });

        html += `<div class="cal-mes-celda ${isToday}" onclick="mostrarDetalleDia('${dateStr}')"><div class="cal-mes-fecha">${day}</div>${turnosHtml}</div>`;
    }

    const totalCeldas = firstDay + daysInMonth;
    const celdasFaltantes = (7 - (totalCeldas % 7)) % 7;
    for (let i = 0; i < celdasFaltantes; i++) {
        html += `<div class="cal-mes-celda cal-mes-celda--vacia"></div>`;
    }
    
    container.innerHTML = html;
    actualizarKPIsMes();
}

function cambiarMesPrincipal(offset) {
    fechaCalendarioPrincipal.setMonth(fechaCalendarioPrincipal.getMonth() + offset);
    renderCalendarioPrincipal();
    renderTabla();
}

function irAHoyPrincipal() {
    fechaCalendarioPrincipal = new Date();
    renderCalendarioPrincipal();
}

// ── AUXILIARES DE FORMULARIO Y LIMPIEZA ────
function limpiarFormularioHorario() {
    const form = document.getElementById('formHorario');
    if (form) form.reset();
    if (window.tsOdontologo) window.tsOdontologo.clear(true);
    
    const inputId = document.getElementById('id_horario');
    const inputFecha = document.getElementById('fecha_horario');
    const titulo = document.getElementById('modal-horario-titulo');
    const btnGuardar = document.getElementById('btn-guardar-horario');

    if (inputId) inputId.value = '';
    if (inputFecha) inputFecha.value = '';
    if (titulo) titulo.textContent = 'Nuevo Horario';
    if (btnGuardar) btnGuardar.textContent = 'Guardar Horario';
    
    const grupoDescanso = document.getElementById('grupo-descanso');
    if (grupoDescanso) {
        grupoDescanso.style.display = 'none';
    }
    const dInicio = document.getElementById('descanso_inicio');
    const dFin = document.getElementById('descanso_fin');
    if (dInicio) dInicio.value = '';
    if (dFin) dFin.value = '';
}

// ── MODALS Y UTILIDADES ───────────────────
function abrirModal(key) {
    if (key === 'nuevo-horario' && (!document.getElementById('id_horario') || document.getElementById('id_horario').value === '')) {
        limpiarFormularioHorario();
    }
    if (key === 'nuevo-horario') {
        fechaSeleccionada = new Date();
        currentViewDate = new Date();
        document.getElementById("fecha_horario").value = fechaSeleccionada.toISOString().split("T")[0];
        renderCalendarSelector();
    }
    const modal = document.getElementById(`modal-${key}`);
    if (modal) modal.classList.add('active');
}

function cerrarModal(key) {
    const modal = document.getElementById(`modal-${key}`);
    if (modal) modal.classList.remove('active');
    if (key === 'nuevo-horario') limpiarFormularioHorario();
}

function cerrarModalOverlay(e, key) {
    if (e.target === e.currentTarget) cerrarModal(key);
}

// ── ACCIONES: HORARIOS ────────────────────
function editarHorario(idHorario) {
    const o = window.odontologosDB.find(x => x.ID_HORARIO == idHorario);
    if (!o) return;

    const titulo = document.getElementById('modal-horario-titulo'); 
    const btnGuardar = document.getElementById('btn-guardar-horario');
    if (titulo) titulo.textContent = 'Editar Horario';
    if (btnGuardar) btnGuardar.textContent = 'Actualizar Horario';

    document.getElementById('id_horario').value = o.ID_HORARIO;
    document.getElementById('odontologo_id').value = o.ODONTOLOGO_ID_ODONTOLOGO;
    if (window.tsOdontologo) window.tsOdontologo.setValue(o.ODONTOLOGO_ID_ODONTOLOGO, true);
    document.getElementById('hora_inicio').value = o.HORA_INICIO.substring(0, 5);
    document.getElementById('hora_fin').value = o.HORA_FIN.substring(0, 5);
    document.getElementById('jornada').value = o.JORNADA;
    document.getElementById('consultorio').value = o.CONSULTORIO || '';

    const grupoDescanso = document.getElementById('grupo-descanso');
    const dInicio = document.getElementById('descanso_inicio');
    const dFin = document.getElementById('descanso_fin');
    if (o.JORNADA === 'Jornada Completa') {
        if (grupoDescanso) grupoDescanso.style.display = 'block';
        if (dInicio) dInicio.value = o.descanso_inicio ? o.descanso_inicio.substring(0, 5) : '';
        if (dFin) dFin.value = o.descanso_fin ? o.descanso_fin.substring(0, 5) : '';
    } else {
        if (grupoDescanso) grupoDescanso.style.display = 'none';
        if (dInicio) dInicio.value = '';
        if (dFin) dFin.value = '';
    }

    if (o.FECHA) {
        const [anio, mes, dia] = o.FECHA.split('-').map(Number);
        fechaSeleccionada = new Date(anio, mes - 1, dia);
        currentViewDate = new Date(anio, mes - 1, dia);
        document.getElementById("fecha_horario").value = o.FECHA;
        renderCalendarSelector(); 
    }

    document.getElementById('modal-nuevo-horario').classList.add('active');
}

async function guardarHorario() {
    const formData = new FormData();
    formData.append('id_horario', document.getElementById('id_horario').value);
    formData.append('odontologo_id', document.getElementById('odontologo_id').value);
    formData.append('fecha', document.getElementById('fecha_horario').value); 
    formData.append('jornada', document.getElementById('jornada').value);
    formData.append('consultorio', document.getElementById('consultorio').value);
    formData.append('hora_inicio', document.getElementById('hora_inicio').value);
    formData.append('hora_fin', document.getElementById('hora_fin').value);
    formData.append('estado', document.getElementById('estado').value);
    formData.append('descanso_inicio', document.getElementById('descanso_inicio').value);
    formData.append('descanso_fin', document.getElementById('descanso_fin').value);

    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/horarios/guardar', { method: 'POST', body: formData });
        const data = await response.json(); 

        if (data.status === "success") {
            cerrarModal('nuevo-horario');
            await cargarDatosIniciales();
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'Horario guardado correctamente',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || "Ocurrió un error al guardar.",
                confirmButtonColor: '#2563eb'
            });
        }
    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Error de comunicación',
            text: e.message,
            confirmButtonColor: '#2563eb'
        });
    }
}

async function eliminarHorario(idHorario) {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se eliminará este horario permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('id_horario', idHorario);
        const url = window.location.origin + '/LOGIN_ORIGINAL/admin/horarios/eliminar';

        const response = await fetch(url, { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'success') {
            await cargarDatosIniciales();
            
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'Horario eliminado correctamente.',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message,
                confirmButtonColor: '#2563eb'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al intentar eliminar.',
            confirmButtonColor: '#2563eb'
        });
    }
}

// ── EXPORTACIÓN ───────────────────────────
function exportarDatos() {
    const datosMes = obtenerDatosMesActual();
    if (datosMes.length === 0) {
        showToast('No hay datos para exportar en el mes seleccionado');
        return;
    }
        
    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    const mesTexto = monthNames[fechaCalendarioPrincipal.getMonth()];
    const anio = fechaCalendarioPrincipal.getFullYear();

    let tablaHTML = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <style>
                table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
                th { background-color: #2563EB; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #D1D5DB; }
                td { padding: 8px; border: 1px solid #D1D5DB; text-align: left; }
                .disponible { color: #16A34A; font-weight: bold; }
                .ocupado { color: #DC2626; font-weight: bold; }
            </style>
        </head>
        <body>
            <h2>Reporte de Horarios - Odonto Estética (${mesTexto} ${anio})</h2>
            <table>
                <thead>
                    <tr><th>Odontólogo</th><th>Procedimiento</th><th>Consultorio</th><th>Jornada</th><th>Horario</th><th>Fecha</th><th>Estado</th></tr>
                </thead>
                <tbody>
    `;

    datosMes.forEach(o => {
        const rangoHorario = `${o.HORA_INICIO.substring(0, 5)} - ${o.HORA_FIN.substring(0, 5)}`;
        const claseEstado = (o.ESTADO.toLowerCase() === 'disponible') ? 'disponible' : 'ocupado';
        tablaHTML += `<tr>
                <td>${o.NOMBRES} ${o.APELLIDOS}</td>
                <td>${o.NOMBRE_PROCEDIMIENTO || 'Sin procedimiento'}</td>
                <td>${o.CONSULTORIO || 'Sin asignar'}</td>
                <td>${o.JORNADA}</td>
                <td>${rangoHorario}</td>
                <td>${o.FECHA || 'N/A'}</td>
                <td class="${claseEstado}">${o.ESTADO}</td>
            </tr>`;
    });

    tablaHTML += `</tbody></table></body></html>`;

    const blob = new Blob([tablaHTML], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Reporte_Horarios_${mesTexto}_${anio}.xls`;
    a.click();
    
    showToast('✅ Exportando reporte en Excel...');
}

// ── TOAST Y TECLADO ───────────────────────
function showToast(msg) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
});

// ── CALENDARIO DEL MODAL (SELECTOR) ───────
function renderCalendarSelector() {
    const container = document.getElementById('calendar-selector-container');
    if (!container) return;
    const year = currentViewDate.getFullYear();
    const month = currentViewDate.getMonth();
    const monthNames = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const hoy = new Date();
    
    let html = `<div class="selector-header"><button type="button" onclick="cambiarMesSelector(-1)">◀</button><span>${monthNames[month]} ${year}</span><button type="button" onclick="cambiarMesSelector(1)">▶</button></div><div class="selector-grid"><div class="day-name">Do</div><div class="day-name">Lu</div><div class="day-name">Ma</div><div class="day-name">Mi</div><div class="day-name">Ju</div><div class="day-name">Vi</div><div class="day-name">Sa</div>`;
    
    for(let i=0; i<firstDay; i++) html += `<div class="day empty"></div>`;
    
    for(let day=1; day<=daysInMonth; day++){
        let clases="day";
        if(day===hoy.getDate() && month===hoy.getMonth() && year===hoy.getFullYear()) clases+=" today";
        if(fechaSeleccionada && fechaSeleccionada.getDate()===day && fechaSeleccionada.getMonth()===month && fechaSeleccionada.getFullYear()===year) clases+=" selected";
        html += `<div class="${clases}" onclick="seleccionarFecha(${day})">${day}</div>`;
    }
    
    html += `</div>`;
    container.innerHTML = html;
}

function seleccionarFecha(day){
    const year = currentViewDate.getFullYear();
    const month = currentViewDate.getMonth();
    fechaSeleccionada = new Date(year,month,day);
    document.getElementById("fecha_horario").value = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
    renderCalendarSelector();
}

function cambiarMesSelector(offset) {
    currentViewDate.setMonth(currentViewDate.getMonth() + offset);
    renderCalendarSelector();
}

// ── LÓGICA DE MODALS ──────────────────────
function configurarEventosModal() {
    const selectOdontologo = document.getElementById('odontologo_id');
    const inputConsultorio = document.getElementById('consultorio');
    if (!selectOdontologo || !inputConsultorio) return;

    selectOdontologo.addEventListener('change', (e) => {
        const idSeleccionado = e.target.value;
        const doctor = window.listaOdontologos.find(o => o.ID_ODONTOLOGO == idSeleccionado);
        if (doctor) {
            let textoConsultorio = doctor.CONSULTORIO_NOMBRE;
            if (doctor.PISO && doctor.PISO.trim() !== '') textoConsultorio += ` - ${doctor.PISO}`;
            inputConsultorio.value = textoConsultorio;
        } else {
            inputConsultorio.value = '';
        }
    });

    const selectJornada = document.getElementById('jornada');
    const grupoDescanso = document.getElementById('grupo-descanso');
    if (selectJornada && grupoDescanso) {
        selectJornada.addEventListener('change', () => {
            if (selectJornada.value === 'Jornada Completa') {
                grupoDescanso.style.display = 'block';
            } else {
                grupoDescanso.style.display = 'none';
                document.getElementById('descanso_inicio').value = '';
                document.getElementById('descanso_fin').value = '';
            }
        });
    }
}

function mostrarDetalleDia(fecha) {
    const horarios = window.odontologosDB.filter(h => h.FECHA === fecha && h.ESTADO !== "Inactivo");
    document.getElementById("detalle-dia-fecha").textContent = "Detalles para horarios del dia " + fecha;
    const body = document.getElementById("detalle-dia-body");
    body.innerHTML = ""; 

    if (horarios.length === 0) {
        body.innerHTML = `<div class="sin-registros"><i class="fa-solid fa-calendar-xmark"></i><p>No existen horarios registrados para este día.</p></div>`;
    } else {
        horarios.forEach(h => {
            const nombrePaciente = (h.NOMBRE_PACIENTE) ? `${h.NOMBRE_PACIENTE} ${h.APELLIDO_PACIENTE || ''}` : 'Sin paciente asignado';
            body.innerHTML += `
            <div class="detalle-card">
                <div class="detalle-header">
                    <div class="doctor"><i class="fa-solid fa-user-doctor"></i><strong>${h.NOMBRES} ${h.APELLIDOS}</strong></div>
                    <span class="${h.ESTADO === "Disponible" ? "estado-disponible" : "estado-ocupado"}">${h.ESTADO}</span>
                </div>
                <div class="detalle-grid">
                    <div style="grid-column: 1 / -1; background-color: #f8fafc; padding: 10px; border-radius: 6px; border: 1px dashed #cbd5e1; margin-bottom: 5px;">
                        <b><i class="fa-solid fa-user"></i> Paciente:</b> <span style="color: #334155;">${nombrePaciente}</span>
                    </div>
                    <div style="grid-column: 1 / -1; background-color: #f1f5f9; padding: 10px; border-radius: 6px;">
                        <b><i class="fa-solid fa-tooth"></i> Procedimiento:</b><br><span style="color: #0f172a;">${h.NOMBRE_PROCEDIMIENTO || 'No asignado'}</span>
                    </div>
                    <div><b>Consultorio</b><br>${h.CONSULTORIO || "Sin asignar"}</div>
                    <div><b>Jornada</b><br>${h.JORNADA}</div>
                    <div><b>Horario</b><br>${h.HORA_INICIO.substring(0,5)} - ${h.HORA_FIN.substring(0,5)}</div>
                </div>
            </div>`;
        });
    }
    document.getElementById("modal-detalle-dia").classList.add("active");
}

function obtenerDatosMesActual() {
    const anio = fechaCalendarioPrincipal.getFullYear();
    const mes = fechaCalendarioPrincipal.getMonth() + 1;
    return window.odontologosDB.filter(h => {
        if (!h.FECHA) return false;
        const [a, m] = h.FECHA.split('-').map(Number);
        return a === anio && m === mes;
    });
}

// ── ACTUALIZAR KPIs POR MES ──
function actualizarKPIsMes() {
    const datosMes = obtenerDatosMesActual();

    if (document.getElementById('kpi-horarios-total')) {
        document.getElementById('kpi-horarios-total').textContent = datosMes.length;
        if(document.getElementById('kpi-turnos-disponibles')) {
            document.getElementById('kpi-turnos-disponibles').textContent = datosMes.filter(h => h.ESTADO.toLowerCase() === 'disponible').length;
        }
        if (document.getElementById('kpi-turnos-ocupados')) {
            document.getElementById('kpi-turnos-ocupados').textContent = datosMes.filter(h => h.ESTADO.toLowerCase() === 'ocupado').length;
        }
    }
}