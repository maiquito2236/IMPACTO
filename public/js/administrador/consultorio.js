// =====================================================
// SISTEMA DE GESTIÓN DE CONSULTORIOS
// =====================================================

let dbConsultorios = [];
let activeTabConsultorio = "consulta";

// =====================================================
// 1. UTILIDADES
// =====================================================

function mostrarToast(mensaje, tipo = "success") {
    Swal.fire({
        toast: true, position: 'top',
        icon: tipo === 'error' ? 'error' : (tipo === 'info' ? 'info' : 'success'),
        title: mensaje, showConfirmButton: false, timer: 3000, timerProgressBar: true,
        width: 'auto', padding: '10px 20px'
    });
}

// =====================================================
// 2. CARGA DE KPIs
// =====================================================

async function cargarKPIs() {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/kpis?t=' + Date.now());
        const res = await response.json();
        if (res.status === 'success') {
            const d = res.data;
            const cards = document.querySelectorAll(".kpi-card .kpi-number");
            if (cards.length >= 4) {
                cards[0].textContent = d.total_consultorios || 0;
                cards[1].textContent = d.asignados || 0;
                cards[2].textContent = d.disponibles || 0;
                cards[3].textContent = d.odontologos_con_consultorio || 0;
            }
        }
    } catch (e) { console.error("Error cargando KPIs:", e); }
}

// =====================================================
// 3. TAB: CONSULTA DE CONSULTORIOS
// =====================================================

async function cargarConsultorios() {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/listar?t=' + Date.now());
        const res = await response.json();
        if (res.status === 'success') {
            dbConsultorios = res.data;
            renderTablaConsultorios(dbConsultorios);
        }
    } catch (e) { console.error("Error:", e); }
}

function renderTablaConsultorios(data) {
    const tbody = document.getElementById("tbodyConsultorios");
    if (!tbody) return;

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No se encontraron consultorios</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map((c, i) => {
        const estadoClass = c.ESTADO === 'ASIGNADO' ? 'badge-asignado' : (c.ESTADO === 'DISPONIBLE' ? 'badge-disponible' : 'badge-mantenimiento');
        const estadoTexto = c.ESTADO === 'ASIGNADO' ? 'Asignado' : (c.ESTADO === 'DISPONIBLE' ? 'Disponible' : 'Mantenimiento');
        const doctorHTML = c.doctor_nombre
            ? `<div class="doctor-cell">
                    <div class="doctor-avatar-sm">${c.doctor_nombre.charAt(0)}</div>
                    <div>
                        <div class="doctor-name-sm">${c.doctor_nombre}</div>
                        <div class="doctor-esp-sm">${c.especialidad || ''}</div>
                    </div>
                </div>`
            : `<span style="color:var(--text-muted);">—</span>`;

        return `<tr>
            <td>${i + 1}</td>
            <td style="font-weight:600;">${c.NOMBRE}</td>
            <td>${c.UBICACION || '—'}</td>
            <td><span class="badge ${estadoClass}">${estadoTexto}</span></td>
            <td>${doctorHTML}</td>
            <td>
                <div class="action-icons-wrap">
                    <button class="btn-action-icon" title="Ver detalle" onclick="verDetalle(${c.ID_CONSULTORIO})"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-action-icon" title="Editar" onclick="abrirModalEditar(${c.ID_CONSULTORIO})"><i class="fa-solid fa-pen"></i></button>
                    ${c.ESTADO === 'DISPONIBLE' ? `<button class="btn-action-icon btn-action-danger" title="Eliminar" onclick="eliminarConsultorio(${c.ID_CONSULTORIO}, '${c.NOMBRE}')"><i class="fa-solid fa-trash"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
}

// Búsqueda en tabla
function filtrarConsultorios() {
    const query = document.getElementById("buscadorConsultorio").value.toLowerCase();
    const filtrados = dbConsultorios.filter(c =>
        c.NOMBRE.toLowerCase().includes(query) ||
        (c.UBICACION && c.UBICACION.toLowerCase().includes(query)) ||
        (c.doctor_nombre && c.doctor_nombre.toLowerCase().includes(query))
    );
    renderTablaConsultorios(filtrados);
}

// =====================================================
// 4. PANEL DE DETALLE
// =====================================================

async function verDetalle(id) {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/detalle?id=' + id);
        const res = await response.json();
        if (res.status === 'success') {
            const c = res.data;
            const panel = document.getElementById("modalDetalle");
            const estadoClass = c.ESTADO === 'ASIGNADO' ? 'badge-asignado' : (c.ESTADO === 'DISPONIBLE' ? 'badge-disponible' : 'badge-mantenimiento');
            const estadoTexto = c.ESTADO === 'ASIGNADO' ? 'Asignado' : (c.ESTADO === 'DISPONIBLE' ? 'Disponible' : 'Mantenimiento');

            document.getElementById("detalleNombre").textContent = c.NOMBRE;
            document.getElementById("detalleUbicacion").textContent = c.UBICACION || '—';
            document.getElementById("detalleEstadoBadge").className = 'badge ' + estadoClass;
            document.getElementById("detalleEstadoBadge").textContent = estadoTexto;

            // Info del doctor
            const doctorSection = document.getElementById("detalleDoctorInfo");
            if (c.doctor_nombre) {
                doctorSection.innerHTML = `
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-stethoscope"></i> Estado:</span>
                        <span class="badge ${estadoClass}">${estadoTexto}</span>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-user-doctor"></i> Odontólogo:</span>
                        <div class="detalle-doctor-chip">
                            <div class="doctor-avatar-sm">${c.doctor_nombre.charAt(0)}</div>
                            <span>${c.doctor_nombre}</span>
                        </div>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-tooth"></i> Especialidad:</span>
                        <span>${c.especialidad || '—'}</span>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-align-left"></i> Descripción:</span>
                        <span>${c.DESCRIPCION || 'Sin descripción'}</span>
                    </div>`;
            } else {
                doctorSection.innerHTML = `
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-stethoscope"></i> Estado:</span>
                        <span class="badge ${estadoClass}">${estadoTexto}</span>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-user-doctor"></i> Odontólogo:</span>
                        <span style="color:var(--text-muted);">No asignado</span>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label"><i class="fa-solid fa-align-left"></i> Descripción:</span>
                        <span>${c.DESCRIPCION || 'Sin descripción'}</span>
                    </div>`;
            }

            // Botones de acción
            const btnsDetalle = document.getElementById("detalleBotones");
            btnsDetalle.innerHTML = `
                <button class="btn-detalle-primary" onclick="abrirModalEditar(${c.ID_CONSULTORIO})">
                    <i class="fa-solid fa-pen-to-square"></i> Modificar
                </button>
                ${c.ESTADO === 'ASIGNADO'
                    ? `<button class="btn-detalle-danger" onclick="desasignarOdontologo(${c.ID_CONSULTORIO}, '${c.NOMBRE}')">
                            <i class="fa-solid fa-user-minus"></i> Desasignar
                        </button>`
                    : (c.ESTADO === 'MANTENIMIENTO' 
                        ? `<button class="btn-detalle-success" onclick="cambiarEstadoConsultorio(${c.ID_CONSULTORIO}, 'DISPONIBLE', '${c.NOMBRE}')">
                                <i class="fa-solid fa-check"></i> Habilitar
                           </button>`
                        : `<button class="btn-detalle-success" onclick="abrirModalAsignarDesdeDetalle(${c.ID_CONSULTORIO}, '${c.NOMBRE}')">
                                <i class="fa-solid fa-user-plus"></i> Asignar
                           </button>
                           <button class="btn-detalle-danger" style="border-color: #fcd34d; color: #b45309;" onclick="cambiarEstadoConsultorio(${c.ID_CONSULTORIO}, 'MANTENIMIENTO', '${c.NOMBRE}')">
                                <i class="fa-solid fa-wrench"></i> Mantenimiento
                           </button>`
                    )
                }`;

            panel.style.display = "flex";
        }
    } catch (e) { console.error(e); }
}

// =====================================================
// 5. TAB: ASIGNAR CONSULTORIO
// =====================================================

async function cargarTabAsignar() {
    try {
        // Cargar consultorios disponibles
        const resC = await fetch('/LOGIN_ORIGINAL/admin/consultorio/listar?t=' + Date.now());
        const dataC = await resC.json();

        // Cargar odontólogos sin consultorio
        const resO = await fetch('/LOGIN_ORIGINAL/admin/consultorio/odontologos-disponibles?t=' + Date.now());
        const dataO = await resO.json();

        if (dataC.status === 'success') {
            const select = document.getElementById("selectConsultorioAsignar");
            const disponibles = dataC.data.filter(c => c.ESTADO === 'DISPONIBLE');
            select.innerHTML = '<option value="">Seleccione un consultorio...</option>';
            disponibles.forEach(c => {
                select.innerHTML += `<option value="${c.ID_CONSULTORIO}">${c.NOMBRE} - ${c.UBICACION || 'Sin ubicación'}</option>`;
            });
        }

        if (dataO.status === 'success') {
            const select = document.getElementById("selectOdontologoAsignar");
            select.innerHTML = '<option value="">Seleccione un odontólogo...</option>';
            dataO.data.forEach(o => {
                select.innerHTML += `<option value="${o.ID_ODONTOLOGO}">${o.doctor_nombre} — ${o.especialidad}</option>`;
            });
        }
    } catch (e) { console.error(e); }
}

async function ejecutarAsignacion() {
    const consultorioId = document.getElementById("selectConsultorioAsignar").value;
    const odontologoId = document.getElementById("selectOdontologoAsignar").value;

    if (!consultorioId || !odontologoId) {
        return mostrarToast("Selecciona un consultorio y un odontólogo", "error");
    }

    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/asignar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ consultorio_id: parseInt(consultorioId), odontologo_id: parseInt(odontologoId) })
        });
        const res = await response.json();

        if (res.status === 'success') {
            mostrarToast("✅ Odontólogo asignado correctamente", "success");
            document.getElementById("selectConsultorioAsignar").value = "";
            document.getElementById("selectOdontologoAsignar").value = "";
            cargarConsultorios();
            cargarKPIs();
            cargarTabAsignar();
        } else {
            mostrarToast(res.message, "error");
        }
    } catch (e) { mostrarToast("Error de conexión", "error"); }
}

// =====================================================
// 6. TAB: HISTORIAL DE ASIGNACIONES
// =====================================================

async function cargarHistorial() {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/historial?t=' + Date.now());
        const res = await response.json();
        if (res.status === 'success') {
            const tbody = document.getElementById("tbodyHistorial");
            if (res.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">No hay registros de asignaciones</td></tr>`;
                return;
            }
            tbody.innerHTML = res.data.map(h => {
                const fechaDesasig = h.FECHA_DESASIGNACION
                    ? (h.FECHA_DESASIGNACION.includes('0000') ? '—' : h.FECHA_DESASIGNACION)
                    : '<span class="badge badge-asignado">Activa</span>';
                return `<tr>
                    <td style="font-weight:600;">${h.consultorio_nombre || '—'}</td>
                    <td>
                        <div class="doctor-cell">
                            <div class="doctor-avatar-sm">${(h.doctor_nombre || '?').charAt(0)}</div>
                            <span>${h.doctor_nombre || 'Desconocido'}</span>
                        </div>
                    </td>
                    <td>${h.FECHA_ASIGNACION || '—'}</td>
                    <td>${fechaDesasig}</td>
                    <td>${h.MOTIVO_CAMBIO || '—'}</td>
                </tr>`;
            }).join('');
        }
    } catch (e) { console.error(e); }
}

// =====================================================
// 7. MODAL: CREAR CONSULTORIO
// =====================================================

function abrirModalCrear() {
    document.getElementById("modalCrearTitulo").textContent = "Nuevo Consultorio";
    document.getElementById("formCrear").reset();
    document.getElementById("crearConsultorioId").value = "";
    document.getElementById("modalCrear").style.display = "flex";
}

async function guardarConsultorio(e) {
    e.preventDefault();

    const id = document.getElementById("crearConsultorioId").value;
    const nombre = document.getElementById("crearNombre").value.trim();
    const ubicacion = document.getElementById("crearUbicacion").value.trim();
    const descripcion = document.getElementById("crearDescripcion").value.trim();

    if (!nombre) return mostrarToast("El nombre es obligatorio", "error");

    const endpoint = id
        ? '/LOGIN_ORIGINAL/admin/consultorio/actualizar'
        : '/LOGIN_ORIGINAL/admin/consultorio/crear';

    const payload = id
        ? { id: parseInt(id), nombre, ubicacion, descripcion }
        : { nombre, ubicacion, descripcion };

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const res = await response.json();

        if (res.status === 'success') {
            mostrarToast(id ? "✅ Consultorio actualizado" : "✅ Consultorio creado", "success");
            document.getElementById("modalCrear").style.display = "none";
            cargarConsultorios();
            cargarKPIs();
        } else {
            mostrarToast(res.message, "error");
        }
    } catch (e) { mostrarToast("Error de conexión", "error"); }
}

// =====================================================
// 8. MODAL: EDITAR CONSULTORIO
// =====================================================

async function abrirModalEditar(id) {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/detalle?id=' + id);
        const res = await response.json();
        if (res.status === 'success') {
            const c = res.data;
            document.getElementById("modalCrearTitulo").textContent = "Editar Consultorio";
            document.getElementById("crearConsultorioId").value = c.ID_CONSULTORIO;
            document.getElementById("crearNombre").value = c.NOMBRE;
            document.getElementById("crearUbicacion").value = c.UBICACION || '';
            document.getElementById("crearDescripcion").value = c.DESCRIPCION || '';
            document.getElementById("modalCrear").style.display = "flex";
        }
    } catch (e) { mostrarToast("Error cargando datos", "error"); }
}

// =====================================================
// 9. ELIMINAR CONSULTORIO
// =====================================================

async function eliminarConsultorio(id, nombre) {
    const result = await Swal.fire({
        title: '¿Eliminar consultorio?',
        text: `Se eliminará "${nombre}" permanentemente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const res = await response.json();
            if (res.status === 'success') {
                mostrarToast("✅ Consultorio eliminado", "success");
                cargarConsultorios();
                cargarKPIs();
                document.getElementById("modalDetalle").style.display = "none";
            } else {
                mostrarToast(res.message, "error");
            }
        } catch (e) { mostrarToast("Error de conexión", "error"); }
    }
}

// =====================================================
// 10. DESASIGNAR ODONTÓLOGO
// =====================================================

async function desasignarOdontologo(consultorioId, nombre) {
    const result = await Swal.fire({
        title: '¿Desasignar odontólogo?',
        text: `Ingrese el motivo para liberar el consultorio "${nombre}":`,
        icon: 'question',
        input: 'text',
        inputPlaceholder: 'Ej. Cambio de turno, Mantenimiento...',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, desasignar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/desasignar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ consultorio_id: consultorioId, motivo: result.value })
            });
            const res = await response.json();
            if (res.status === 'success') {
                mostrarToast("✅ Odontólogo desasignado", "success");
                cargarConsultorios();
                cargarKPIs();
                verDetalle(consultorioId); // Refrescar panel
            } else {
                mostrarToast(res.message, "error");
            }
        } catch (e) { mostrarToast("Error de conexión", "error"); }
    }
}

function abrirModalAsignarDesdeDetalle(consultorioId, nombre) {
    document.getElementById("modalDetalle").style.display = "none";
    
    // Cambiar a la pestaña de asignar
    document.querySelectorAll(".tab-btn-c").forEach(t => t.classList.remove("active"));
    document.querySelectorAll(".tab-content-c").forEach(c => c.style.display = "none");
    
    const tabAsignar = document.querySelector('[data-tab-c="asignar"]');
    if (tabAsignar) {
        tabAsignar.classList.add("active");
        document.getElementById("content-asignar").style.display = "block";
        activeTabConsultorio = "asignar";
        cargarTabAsignar().then(() => {
            // Preseleccionar el consultorio
            document.getElementById("selectConsultorioAsignar").value = consultorioId;
        });
    }
}

// =====================================================
// 11. CAMBIAR ESTADO A MANTENIMIENTO
// =====================================================

async function cambiarEstadoConsultorio(id, estado, nombre) {
    const isMantenimiento = estado === 'MANTENIMIENTO';
    const textInfo = isMantenimiento 
        ? `"${nombre}" pasará a estar inhabilitado temporalmente.`
        : `"${nombre}" volverá a estar disponible para asignaciones.`;

    const result = await Swal.fire({
        title: `¿Cambiar a ${estado}?`,
        text: textInfo,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isMantenimiento ? '#d97706' : '#16a34a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('/LOGIN_ORIGINAL/admin/consultorio/estado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, estado: estado })
            });
            const res = await response.json();
            if (res.status === 'success') {
                mostrarToast(`✅ Estado actualizado`, "success");
                cargarConsultorios();
                cargarKPIs();
                verDetalle(id); // Refrescar panel
            } else {
                mostrarToast(res.message, "error");
            }
        } catch (e) { mostrarToast("Error de conexión", "error"); }
    }
}

// =====================================================
// 12. EVENTOS Y TABS
// =====================================================

document.addEventListener("DOMContentLoaded", () => {
    // Tabs
    document.querySelectorAll(".tab-btn-c").forEach(btn => {
        btn.addEventListener("click", (e) => {
            document.querySelectorAll(".tab-btn-c").forEach(t => t.classList.remove("active"));
            e.target.classList.add("active");
            document.querySelectorAll(".tab-content-c").forEach(c => c.style.display = "none");
            activeTabConsultorio = e.target.getAttribute("data-tab-c");
            document.getElementById("content-" + activeTabConsultorio).style.display = "block";

            if (activeTabConsultorio === "consulta") cargarConsultorios();
            else if (activeTabConsultorio === "asignar") cargarTabAsignar();
            else if (activeTabConsultorio === "historial") cargarHistorial();
        });
    });

    // Modal crear - cerrar
    const closeCrear = document.getElementById("closeModalCrear");
    const modalCrear = document.getElementById("modalCrear");
    if (closeCrear && modalCrear) {
        closeCrear.addEventListener("click", () => modalCrear.style.display = "none");
        modalCrear.addEventListener("click", (e) => {
            if (e.target === modalCrear) modalCrear.style.display = "none";
        });
    }

    // Modal detalle - cerrar
    const closeDetalle = document.getElementById("closeModalDetalle");
    const modalDetalle = document.getElementById("modalDetalle");
    if (closeDetalle && modalDetalle) {
        closeDetalle.addEventListener("click", () => modalDetalle.style.display = "none");
        modalDetalle.addEventListener("click", (e) => {
            if (e.target === modalDetalle) modalDetalle.style.display = "none";
        });
    }

    // Form crear
    const formCrear = document.getElementById("formCrear");
    if (formCrear) {
        formCrear.addEventListener("submit", guardarConsultorio);
    }

    // Búsqueda
    const buscador = document.getElementById("buscadorConsultorio");
    if (buscador) {
        buscador.addEventListener("input", filtrarConsultorios);
    }

    // Botón asignar
    const btnAsignar = document.getElementById("btnEjecutarAsignacion");
    if (btnAsignar) {
        btnAsignar.addEventListener("click", ejecutarAsignacion);
    }

    // Carga inicial
    cargarConsultorios();
    cargarKPIs();
});
