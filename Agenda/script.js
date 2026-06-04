// =====================================================
// AGENDA ODONTO ESTÉTICA — Script completamente funcional
// Vistas: Día, Semana, Mes | Agregar, Eliminar, Reprogramar citas
// =====================================================
 
// ── 1. BASE DE DATOS ──────────────────────────────
let dbCitas = [
    { id: 1, paciente: "Alejandra Torres",   doc: "12345678", fecha: "2026-06-02", hora: "09:00", tratamiento: "Ajuste Ortodoncia",  color: "apt-green",  estado: "Confirmada" },
    { id: 2, paciente: "Maria Fernanda López", doc: "30012345", fecha: "2026-06-02", hora: "11:30", tratamiento: "Limpieza Dental",    color: "apt-blue",   estado: "Confirmada" },
    { id: 3, paciente: "Luis Eduardo Pérez",  doc: "98765432", fecha: "2026-06-03", hora: "10:00", tratamiento: "Consulta General",   color: "apt-purple",  estado: "Pendiente"  },
    { id: 4, paciente: "Luan Guarnizo",       doc: "55512345", fecha: "2026-06-04", hora: "11:30", tratamiento: "Limpieza Dental",    color: "apt-blue",   estado: "Confirmada" },
    { id: 5, paciente: "Miguel Ángel Rojas",  doc: "31577722", fecha: "2026-06-05", hora: "14:00", tratamiento: "Ortodoncia Inicial", color: "apt-green",  estado: "Pendiente"  },
    { id: 6, paciente: "Ana Sofia Martínez",  doc: "30165498", fecha: "2026-06-02", hora: "15:00", tratamiento: "Blanqueamiento",     color: "apt-purple",  estado: "Confirmada" },
];
 
let nextId = 10;
let vistaActual = "semana"; // "dia" | "semana" | "mes"
let fechaBase = new Date("2026-06-02"); // Fecha de referencia de la semana/día/mes actual
 
// ── 2. UTILIDADES DE FECHA ─────────────────────────
const DIAS_ES   = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
const MESES_ES  = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
const HORAS     = ["08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00"];
 
function fmtFecha(d) {
    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
}
function isoFecha(d) {
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}
function getLunesDe(d) {
    const copy = new Date(d);
    const day  = copy.getDay() || 7;
    copy.setDate(copy.getDate() - day + 1);
    return copy;
}
function addDays(d, n) { const c = new Date(d); c.setDate(c.getDate()+n); return c; }
 
// ── 3. RENDER PRINCIPAL ────────────────────────────
function render() {
    actualizarTituloToolbar();
    if (vistaActual === "dia")    renderDia();
    else if (vistaActual === "semana") renderSemana();
    else                          renderMes();
    renderPacientesHoy();
}
 
// ── TÍTULO TOOLBAR ─────────────────────────────────
function actualizarTituloToolbar() {
    const el = document.getElementById("tituloRango");
    if (!el) return;
    if (vistaActual === "dia") {
        el.textContent = `${DIAS_ES[fechaBase.getDay()]} ${fechaBase.getDate()} de ${MESES_ES[fechaBase.getMonth()]} ${fechaBase.getFullYear()}`;
    } else if (vistaActual === "semana") {
        const lunes  = getLunesDe(fechaBase);
        const viernes = addDays(lunes, 4);
        el.textContent = `${lunes.getDate()} – ${viernes.getDate()} de ${MESES_ES[lunes.getMonth()]} ${lunes.getFullYear()}`;
    } else {
        el.textContent = `${MESES_ES[fechaBase.getMonth()]} ${fechaBase.getFullYear()}`;
    }
}
 
// ── VISTA DÍA ──────────────────────────────────────
function renderDia() {
    const cont = document.getElementById("calendarContainer");
    cont.innerHTML = "";
 
    const isoHoy = isoFecha(fechaBase);
    const citasHoy = dbCitas.filter(c => c.fecha === isoHoy);
 
    let html = `<div class="vista-dia">`;
    html += `<div class="dia-header-col"></div>`;
    html += `<div class="dia-header-col dia-header-main">
        <strong>${DIAS_ES[fechaBase.getDay()]}</strong>
        <small>${fechaBase.getDate()} de ${MESES_ES[fechaBase.getMonth()]}</small>
    </div>`;
 
    HORAS.forEach(hora => {
        const citas = citasHoy.filter(c => c.hora === hora);
        html += `<div class="time-cell">${hora}</div>`;
        html += `<div class="day-cell" data-fecha="${isoHoy}" data-hora="${hora}">`;
        citas.forEach(c => { html += tarjetaCita(c); });
        html += `</div>`;
    });
 
    html += `</div>`;
    cont.innerHTML = html;
    bindCeldas(cont);
}
 
// ── VISTA SEMANA ───────────────────────────────────
function renderSemana() {
    const cont = document.getElementById("calendarContainer");
    cont.innerHTML = "";
 
    const lunes = getLunesDe(fechaBase);
    const dias  = Array.from({length:5}, (_,i) => addDays(lunes,i));
    const hoy   = isoFecha(new Date("2026-06-02"));
 
    let html = `<div class="calendar-grid-week">`;
    // Cabecera
    html += `<div class="grid-header-cell"></div>`;
    dias.forEach(d => {
        const iso    = isoFecha(d);
        const esHoy  = iso === hoy ? " es-hoy" : "";
        html += `<div class="grid-header-cell${esHoy}">
            <strong>${DIAS_ES[d.getDay()]}</strong><br>
            <small>${d.getDate()} de ${MESES_ES[d.getMonth()]}</small>
        </div>`;
    });
 
    // Filas de horas
    HORAS.forEach(hora => {
        html += `<div class="time-cell">${hora}</div>`;
        dias.forEach(d => {
            const iso   = isoFecha(d);
            const citas = dbCitas.filter(c => c.fecha === iso && c.hora === hora);
            html += `<div class="day-cell" data-fecha="${iso}" data-hora="${hora}">`;
            citas.forEach(c => { html += tarjetaCita(c); });
            html += `</div>`;
        });
    });
 
    html += `</div>`;
    cont.innerHTML = html;
    bindCeldas(cont);
}
 
// ── VISTA MES ──────────────────────────────────────
function renderMes() {
    const cont = document.getElementById("calendarContainer");
    cont.innerHTML = "";
 
    const año  = fechaBase.getFullYear();
    const mes  = fechaBase.getMonth();
    const hoy  = isoFecha(new Date("2026-06-02"));
 
    const primerDia = new Date(año, mes, 1);
    const ultimoDia = new Date(año, mes+1, 0);
    let startDow    = primerDia.getDay() || 7; // lunes=1 … domingo=7
 
    let html = `<div class="calendar-grid-mes">`;
    // Cabecera días semana
    ["Lun","Mar","Mié","Jue","Vie","Sáb","Dom"].forEach(d => {
        html += `<div class="mes-header">${d}</div>`;
    });
 
    // Celdas vacías antes del 1
    for (let i=1; i < startDow; i++) html += `<div class="mes-cell vacio"></div>`;
 
    // Días del mes
    for (let dia=1; dia <= ultimoDia.getDate(); dia++) {
        const d     = new Date(año, mes, dia);
        const iso   = isoFecha(d);
        const esHoy = iso === hoy ? " es-hoy" : "";
        const citas = dbCitas.filter(c => c.fecha === iso);
 
        html += `<div class="mes-cell${esHoy}" data-fecha="${iso}" data-hora="09:00">`;
        html += `<span class="mes-numero">${dia}</span>`;
        citas.slice(0,3).forEach(c => {
            html += `<div class="mes-apt ${c.color}" data-id="${c.id}">
                <span>${c.hora} ${c.paciente.split(" ")[0]}</span>
                <button class="mes-del-btn" onclick="eliminarCita(${c.id},event)" title="Eliminar">×</button>
            </div>`;
        });
        if (citas.length > 3) html += `<div class="mes-mas">+${citas.length-3} más</div>`;
        html += `</div>`;
    }
 
    html += `</div>`;
    cont.innerHTML = html;
    bindCeldas(cont);
}
 
// ── TARJETA DE CITA (Día / Semana) ─────────────────
function tarjetaCita(c) {
    const estadoClass = c.estado === "Confirmada" ? "estado-confirmada" : "estado-pendiente";
    return `<div class="appointment ${c.color}" data-id="${c.id}">
        <div class="apt-top">
            <strong>${c.paciente}</strong>
            <div class="apt-btns">
                <button class="apt-icon-btn" onclick="reprogramarCita(${c.id},event)" title="Reprogramar"><i class="fa-regular fa-calendar"></i></button>
                <button class="apt-icon-btn red" onclick="eliminarCita(${c.id},event)" title="Eliminar"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <span>${c.tratamiento}</span>
        <span>${c.hora}</span>
        <span class="apt-estado ${estadoClass}">${c.estado}</span>
    </div>`;
}
 
// ── BIND: clic en celda vacía = nueva cita ─────────
function bindCeldas(cont) {
    cont.querySelectorAll(".day-cell, .mes-cell:not(.vacio)").forEach(cell => {
        cell.addEventListener("click", function(e) {
            // Solo si hizo clic en la celda misma (no en un botón interno)
            if (e.target !== this && !e.target.classList.contains("mes-numero")) return;
            const fecha = this.dataset.fecha;
            const hora  = this.dataset.hora || "09:00";
            abrirModalNuevaCita(fecha, hora);
        });
    });
}
 
// ── PANEL PACIENTES HOY ────────────────────────────
function renderPacientesHoy() {
    const hoy   = isoFecha(new Date("2026-06-02"));
    const citas = dbCitas.filter(c => c.fecha === hoy).sort((a,b) => a.hora.localeCompare(b.hora));
    const lista = document.getElementById("listaPacientesHoy");
    if (!lista) return;
 
    if (citas.length === 0) {
        lista.innerHTML = `<li style="color:var(--text-muted);font-size:13px;padding:12px 0;text-align:center;">No hay citas para hoy</li>`;
        return;
    }
 
    lista.innerHTML = citas.map(c => `
        <li class="patient-item">
            <div class="patient-item-info">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.paciente)}&background=random" alt="">
                <div class="details">
                    <strong>${c.paciente}</strong>
                    <span>${c.hora} · ${c.tratamiento}</span>
                </div>
            </div>
            <button class="apt-icon-btn red" onclick="eliminarCita(${c.id},event)" title="Eliminar cita"><i class="fa-solid fa-xmark"></i></button>
        </li>
    `).join("");
}
 
// ── 4. NUEVA CITA ──────────────────────────────────
function abrirModalNuevaCita(fechaPre, horaPre) {
    if (fechaPre) document.getElementById("fCitaFecha").value = fechaPre;
    if (horaPre)  document.getElementById("fCitaHora").value  = horaPre;
    document.getElementById("modalCita").style.display = "flex";
}
 
document.getElementById("closeModalCita").addEventListener("click", () => {
    document.getElementById("modalCita").style.display = "none";
});
window.addEventListener("click", e => {
    if (e.target === document.getElementById("modalCita"))
        document.getElementById("modalCita").style.display = "none";
    if (e.target === document.getElementById("modalReprogramar"))
        document.getElementById("modalReprogramar").style.display = "none";
});
 
document.getElementById("formCita").addEventListener("submit", e => {
    e.preventDefault();
    const paciente    = document.getElementById("fCitaPaciente").value.trim();
    const doc         = document.getElementById("fCitaDoc").value.trim();
    const fecha       = document.getElementById("fCitaFecha").value;
    const hora        = document.getElementById("fCitaHora").value;
    const tratamiento = document.getElementById("fCitaTratamiento").value;
    const color = document.querySelector('input[name="citaColor"]:checked')?.value || 'apt-blue';
 
    if (!paciente || !fecha || !hora || !tratamiento) {
        mostrarToast("Completa todos los campos obligatorios.", "error");
        return;
    }
 
    dbCitas.push({ id: nextId++, paciente, doc, fecha, hora, tratamiento, color, estado: "Pendiente" });
    document.getElementById("modalCita").style.display = "none";
    document.getElementById("formCita").reset();
    render();
    mostrarToast(`✅ Cita de ${paciente} agendada para el ${fmtFecha(new Date(fecha+"T00:00"))} a las ${hora}.`, "success");
});
 
// ── 5. ELIMINAR CITA ───────────────────────────────
function eliminarCita(id, e) {
    if (e) e.stopPropagation();
    const c = dbCitas.find(x => x.id === id);
    if (!c) return;
    if (!confirm(`¿Eliminar la cita de ${c.paciente} (${c.fecha} ${c.hora})?`)) return;
    dbCitas = dbCitas.filter(x => x.id !== id);
    render();
    mostrarToast(`Cita de ${c.paciente} eliminada.`, "error");
}
 
// ── 6. REPROGRAMAR CITA ────────────────────────────
let citaReprogramandoId = null;
 
function reprogramarCita(id, e) {
    if (e) e.stopPropagation();
    const c = dbCitas.find(x => x.id === id);
    if (!c) return;
    citaReprogramandoId = id;
    document.getElementById("rCitaInfo").textContent = `${c.paciente} — ${c.tratamiento} (${c.fecha} ${c.hora})`;
    document.getElementById("rCitaFecha").value = c.fecha;
    document.getElementById("rCitaHora").value  = c.hora;
    document.getElementById("modalReprogramar").style.display = "flex";
}
 
document.getElementById("closeModalReprogramar").addEventListener("click", () => {
    document.getElementById("modalReprogramar").style.display = "none";
});
 
document.getElementById("formReprogramar").addEventListener("submit", e => {
    e.preventDefault();
    const nuevaFecha = document.getElementById("rCitaFecha").value;
    const nuevaHora  = document.getElementById("rCitaHora").value;
    const motivo     = document.getElementById("rMotivo").value;
 
    const c = dbCitas.find(x => x.id === citaReprogramandoId);
    if (!c) return;
 
    c.fecha  = nuevaFecha;
    c.hora   = nuevaHora;
    c.estado = "Pendiente";
 
    document.getElementById("modalReprogramar").style.display = "none";
    document.getElementById("formReprogramar").reset();
    render();
    mostrarToast(`📅 Cita de ${c.paciente} reprogramada al ${fmtFecha(new Date(nuevaFecha+"T00:00"))} a las ${nuevaHora}.`, "success");
});
 
// ── 7. BOTÓN "NUEVA CITA" TOOLBAR ─────────────────
document.getElementById("btnNuevaCita").addEventListener("click", () => {
    abrirModalNuevaCita(isoFecha(fechaBase), "09:00");
});
 
// ── 8. BOTÓN REPROGRAMAR TOOLBAR ──────────────────
document.getElementById("btnReprogramarMain").addEventListener("click", () => {
    if (dbCitas.length === 0) { mostrarToast("No hay citas para reprogramar.", "info"); return; }
    // Abrir modal con selector de cita
    const sel = document.getElementById("rSeleccionCita");
    sel.innerHTML = dbCitas.map(c =>
        `<option value="${c.id}">${c.paciente} — ${c.fecha} ${c.hora}</option>`
    ).join("");
    sel.addEventListener("change", () => {
        const c = dbCitas.find(x => x.id == sel.value);
        if (c) {
            document.getElementById("rCitaInfo").textContent = `${c.paciente} — ${c.tratamiento}`;
            document.getElementById("rCitaFecha").value = c.fecha;
            document.getElementById("rCitaHora").value  = c.hora;
            citaReprogramandoId = c.id;
        }
    });
    const first = dbCitas[0];
    citaReprogramandoId = first.id;
    document.getElementById("rCitaInfo").textContent = `${first.paciente} — ${first.tratamiento}`;
    document.getElementById("rCitaFecha").value = first.fecha;
    document.getElementById("rCitaHora").value  = first.hora;
    document.getElementById("modalReprogramar").style.display = "flex";
});
 
// ── 9. CONTROLES DE VISTA ─────────────────────────
document.getElementById("btnVistaDia").addEventListener("click", () => {
    vistaActual = "dia";
    setActiveVista("btnVistaDia");
    render();
});
document.getElementById("btnVistaSemana").addEventListener("click", () => {
    vistaActual = "semana";
    setActiveVista("btnVistaSemana");
    render();
});
document.getElementById("btnVistaMes").addEventListener("click", () => {
    vistaActual = "mes";
    setActiveVista("btnVistaMes");
    render();
});
document.getElementById("btnVerHoy").addEventListener("click", () => {
    fechaBase = new Date("2026-06-02");
    render();
    mostrarToast("Mostrando fecha actual.", "info");
});
document.getElementById("btnAnterior").addEventListener("click", () => {
    if (vistaActual === "dia")    fechaBase = addDays(fechaBase, -1);
    else if (vistaActual === "semana") fechaBase = addDays(fechaBase, -7);
    else { fechaBase.setMonth(fechaBase.getMonth()-1); fechaBase = new Date(fechaBase); }
    render();
});
document.getElementById("btnSiguiente").addEventListener("click", () => {
    if (vistaActual === "dia")    fechaBase = addDays(fechaBase, 1);
    else if (vistaActual === "semana") fechaBase = addDays(fechaBase, 7);
    else { fechaBase.setMonth(fechaBase.getMonth()+1); fechaBase = new Date(fechaBase); }
    render();
});
 
function setActiveVista(activeId) {
    ["btnVistaDia","btnVistaSemana","btnVistaMes"].forEach(id => {
        document.getElementById(id).classList.toggle("active", id === activeId);
    });
}
 
// ── 10. TOASTS ─────────────────────────────────────
function mostrarToast(mensaje, tipo) {
    let cont = document.getElementById("toastCont");
    if (!cont) {
        cont = document.createElement("div");
        cont.id = "toastCont";
        cont.style.cssText = "position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;";
        document.body.appendChild(cont);
    }
    const colors = { success:"#22c55e", error:"#ef4444", info:"#3b82f6" };
    const t = document.createElement("div");
    t.style.cssText = `background:white;border-left:4px solid ${colors[tipo]||colors.info};padding:12px 18px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);font-size:13.5px;color:#1e293b;max-width:340px;animation:slideIn .3s ease;`;
    t.textContent = mensaje;
    if (!document.getElementById("toastSty")) {
        const s = document.createElement("style"); s.id="toastSty";
        s.textContent = "@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}";
        document.head.appendChild(s);
    }
    cont.appendChild(t);
    setTimeout(() => { t.style.opacity="0"; t.style.transition="opacity .3s"; setTimeout(()=>t.remove(),300); }, 3500);
}
 
// ── ARRANQUE ────────────────────────────────────────
render();
 