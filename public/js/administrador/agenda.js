// =====================================================
// AGENDA ODONTO ESTÉTICA — Admin
// Vistas: Día, Semana, Mes | Nueva, Cancelar, Reprogramar citas
// =====================================================

// ── 1. ESTADO GLOBAL ──────────────────────────────
let dbCitas       = [];
let pacientesDB   = [];
let odontologosDB = [];
let tratamientosDB = [];
let vistaActual   = "semana";
let fechaBase     = new Date();

// ── 2. CARGA DESDE LA BASE DE DATOS ───────────────

function cargarCitasDesdeBD() {
    fetch('/LOGIN_ORIGINAL/admin/agenda/obtener_citas')
        .then(r => r.text())
        .then(text => {
            try { return JSON.parse(text); }
            catch(e) {
                console.error("❌ Respuesta PHP no es JSON:", text);
                mostrarToast("Error en el formato del servidor", "error");
                return [];
            }
        })
        .then(data => {
            if (!Array.isArray(data)) {
                console.error("Error del servidor:", data);
                return;
            }
            // Normalizar: el modelo devuelve id_cita, fecha_cita, hora_cita
            dbCitas = data.map(c => {
                let hStr = c.hora_cita ? c.hora_cita.substring(0, 5) : (c.hora ? c.hora.substring(0, 5) : "00:00");
                let parts = hStr.split(':');
                let min = parseInt(parts[1] || "0", 10);
                let gridMin = min < 30 ? "00" : "30";
                let hGrid = (parts[0] || "00") + ":" + gridMin;

                return {
                    id:          c.id_cita,
                    fecha:       c.fecha_cita,   
                    hora:        hStr,
                    hora_grid:   hGrid,
                    paciente:    c.paciente    || "Paciente",
                    odontologo:  c.odontologo  || "",
                    id_odontologo: c.id_odontologo,
                    tratamiento: c.tratamiento || "Consulta",
                    estado:      c.estado      || "Pendiente",
                    color:       colorPorEstado(c.estado),
                    consultorio: c.consultorio || "Sin asignar",
                };
            });
            console.log("✅ Citas cargadas:", dbCitas);
            render();
        })
        .catch(err => {
            console.error("Fetch error:", err);
            mostrarToast("No se pudo conectar con el servidor", "error");
        });
}

function colorPorEstado(estado) {
    const mapa = {
        "Pendiente":   "apt-blue",
        "Completada":  "apt-green",
        "Cancelada":   "apt-red",
        "No asistió":  "apt-gray",
    };
    return mapa[estado] || "apt-purple";
}

function cargarListasDesdeBD() {
    fetch('/LOGIN_ORIGINAL/admin/agenda/obtener_citas?listas=1')
        .then(r => r.json())
        .then(data => {
            pacientesDB    = data.pacientes    || [];
            odontologosDB  = data.odontologos  || [];
            tratamientosDB = data.tratamientos || [];
            llenarSelect("fCitaPaciente",    pacientesDB,   "Seleccionar paciente");
            llenarSelect("fCitaOdontologo",  odontologosDB, "Seleccionar odontólogo");
            llenarSelectTratamientos();
        })
        .catch(err => {
            console.error("Error cargando listas:", err);
            mostrarToast("No se pudieron cargar pacientes/odontólogos", "error");
        });
}

function llenarSelect(idSelect, datos, placeholder) {
    const sel = document.getElementById(idSelect);
    if (!sel) return;
    sel.innerHTML = `<option value="">— ${placeholder} —</option>` +
        datos.map(d => `<option value="${d.id}">${d.nombre}</option>`).join("");
}

function llenarSelectTratamientos() {
    const sel = document.getElementById("fCitaTratamiento");
    if (!sel) return;
    sel.innerHTML = `<option value="">— Seleccionar tratamiento —</option>` +
        tratamientosDB.map(t =>
            `<option value="${t.nombre}">${t.nombre}</option>`
        ).join("");
}

document.addEventListener("DOMContentLoaded", () => {
    cargarListasDesdeBD();
    cargarCitasDesdeBD();

    // Listeners para horas dinámicas (Nueva Cita)
    const fCitaFecha = document.getElementById("fCitaFecha");
    const fCitaOdontologo = document.getElementById("fCitaOdontologo");
    if (fCitaFecha) fCitaFecha.addEventListener("change", () => actualizarHorasDisponibles("fCitaFecha", "fCitaOdontologo", "fCitaHora"));
    if (fCitaOdontologo) fCitaOdontologo.addEventListener("change", () => actualizarHorasDisponibles("fCitaFecha", "fCitaOdontologo", "fCitaHora"));

    // Listeners para horas dinámicas (Reprogramar)
    const rCitaFecha = document.getElementById("rCitaFecha");
    if (rCitaFecha) rCitaFecha.addEventListener("change", () => {
        if (!citaReprogramandoId) return;
        const c = dbCitas.find(x => x.id == citaReprogramandoId);
        if (c) actualizarHorasDisponiblesReprogramar(rCitaFecha.value, c.id_odontologo, "rCitaHora");
    });
});

async function actualizarHorasDisponibles(idFecha, idOdontologo, idHora, horaSeleccionada = "") {
    const fecha = document.getElementById(idFecha)?.value;
    const odon = document.getElementById(idOdontologo)?.value;
    const selHora = document.getElementById(idHora);
    if (!selHora) return;
    selHora.innerHTML = '<option value="">— Seleccionar hora —</option>';
    if (!fecha || !odon) return;

    try {
        const res = await fetch(`/LOGIN_ORIGINAL/admin/agenda/obtener_horas_ocupadas?fecha=${fecha}&id_odontologo=${odon}`);
        const ocupadas = await res.json();
        if (ocupadas.status === "error") throw new Error(ocupadas.message);

        for (let i = 5; i <= 20; i++) {
            ['00', '30'].forEach(min => {
                if (i === 20 && min === '30') return;
                const h = String(i).padStart(2, '0');
                const time = `${h}:${min}`;
                if (time === horaSeleccionada || !ocupadas.includes(time)) {
                    let ampm = i < 12 ? 'AM' : 'PM';
                    let h12 = i % 12 === 0 ? 12 : i % 12;
                    selHora.innerHTML += `<option value="${time}">${h12}:${min} ${ampm}</option>`;
                }
            });
        }
        if (horaSeleccionada) selHora.value = horaSeleccionada;
    } catch (e) {
        console.error(e);
    }
}

async function actualizarHorasDisponiblesReprogramar(fecha, idOdontologo, idHora, horaSeleccionada = "") {
    const selHora = document.getElementById(idHora);
    if (!selHora) return;
    selHora.innerHTML = '<option value="">— Seleccionar hora —</option>';
    if (!fecha || !idOdontologo) return;

    try {
        const res = await fetch(`/LOGIN_ORIGINAL/admin/agenda/obtener_horas_ocupadas?fecha=${fecha}&id_odontologo=${idOdontologo}`);
        const ocupadas = await res.json();
        if (ocupadas.status === "error") throw new Error(ocupadas.message);

        for (let i = 5; i <= 20; i++) {
            ['00', '30'].forEach(min => {
                if (i === 20 && min === '30') return;
                const h = String(i).padStart(2, '0');
                const time = `${h}:${min}`;
                if (time === horaSeleccionada || !ocupadas.includes(time)) {
                    let ampm = i < 12 ? 'AM' : 'PM';
                    let h12 = i % 12 === 0 ? 12 : i % 12;
                    selHora.innerHTML += `<option value="${time}">${h12}:${min} ${ampm}</option>`;
                }
            });
        }
        if (horaSeleccionada) selHora.value = horaSeleccionada;
    } catch (e) {
        console.error(e);
    }
}

// ── 3. UTILIDADES DE FECHA ────────────────────────
const DIAS_ES  = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
const MESES_ES = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
const HORAS = [];
for (let i = 5; i <= 20; i++) {
    const h = i.toString().padStart(2, '0');
    HORAS.push(`${h}:00`);
    if (i < 20) HORAS.push(`${h}:30`);
}

function fmtFecha(d) {
    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
}
function isoFecha(d) {
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}
function getLunesDe(d) {
    const c = new Date(d); const day = c.getDay() || 7;
    c.setDate(c.getDate() - day + 1); return c;
}
function addDays(d, n) { const c = new Date(d); c.setDate(c.getDate()+n); return c; }

// ── 4. RENDER PRINCIPAL ───────────────────────────
function render() {
    actualizarTituloToolbar();
    if (vistaActual === "dia")         renderDia();
    else if (vistaActual === "semana") renderSemana();
    else                               renderMes();
    renderPacientesHoy();
}

function actualizarTituloToolbar() {
    const el = document.getElementById("tituloRango");
    if (!el) return;
    if (vistaActual === "dia") {
        el.textContent = `${DIAS_ES[fechaBase.getDay()]} ${fechaBase.getDate()} de ${MESES_ES[fechaBase.getMonth()]} ${fechaBase.getFullYear()}`;
    } else if (vistaActual === "semana") {
        const lunes   = getLunesDe(fechaBase);
        const viernes = addDays(lunes, 4);
        el.textContent = `${lunes.getDate()} – ${viernes.getDate()} de ${MESES_ES[lunes.getMonth()]} ${lunes.getFullYear()}`;
    } else {
        el.textContent = `${MESES_ES[fechaBase.getMonth()]} ${fechaBase.getFullYear()}`;
    }
}

// ── VISTA DÍA ────────────────────────────────────
function renderDia() {
    const cont   = document.getElementById("calendarContainer");
    const isoHoy = isoFecha(fechaBase);

    let html = `<div class="calendar-grid-week">`;
    html += `<div class="grid-header-cell"></div>`;
    html += `<div class="grid-header-cell es-hoy">
        <strong>${DIAS_ES[fechaBase.getDay()]}</strong><br>
        <small>${fechaBase.getDate()} de ${MESES_ES[fechaBase.getMonth()]}</small>
    </div>`;

    HORAS.forEach(hora => {
        const citas = dbCitas.filter(c => c.fecha === isoHoy && c.hora_grid === hora);
        html += `<div class="time-cell">${hora}</div>`;
        html += `<div class="day-cell" data-fecha="${isoHoy}" data-hora="${hora}">`;
        citas.forEach(c => { html += tarjetaCita(c); });
        html += `</div>`;
    });

    html += `</div>`;
    cont.innerHTML = html;
    bindCeldas(cont);
}

// ── VISTA SEMANA ─────────────────────────────────
function renderSemana() {
    const cont  = document.getElementById("calendarContainer");
    const lunes = getLunesDe(fechaBase);
    const dias  = Array.from({length:5}, (_,i) => addDays(lunes,i));
    const hoy   = isoFecha(new Date());

    let html = `<div class="calendar-grid-week">`;
    html += `<div class="grid-header-cell"></div>`;
    dias.forEach(d => {
        const iso   = isoFecha(d);
        const esHoy = iso === hoy ? " es-hoy" : "";
        html += `<div class="grid-header-cell${esHoy}">
            <strong>${DIAS_ES[d.getDay()]}</strong><br>
            <small>${d.getDate()} de ${MESES_ES[d.getMonth()]}</small>
        </div>`;
    });

    HORAS.forEach(hora => {
        html += `<div class="time-cell">${hora}</div>`;
        dias.forEach(d => {
            const iso   = isoFecha(d);
            const citas = dbCitas.filter(c => c.fecha === iso && c.hora_grid === hora);
            html += `<div class="day-cell" data-fecha="${iso}" data-hora="${hora}">`;
            citas.forEach(c => { html += tarjetaCita(c); });
            html += `</div>`;
        });
    });

    html += `</div>`;
    cont.innerHTML = html;
    bindCeldas(cont);
}

// ── VISTA MES ────────────────────────────────────
function renderMes() {
    const cont      = document.getElementById("calendarContainer");
    const año       = fechaBase.getFullYear();
    const mes       = fechaBase.getMonth();
    const hoy       = isoFecha(new Date());
    const primerDia = new Date(año, mes, 1);
    const ultimoDia = new Date(año, mes+1, 0);
    let   startDow  = primerDia.getDay() || 7;

    let html = `<div class="calendar-grid-mes">`;
    ["Lun","Mar","Mié","Jue","Vie","Sáb","Dom"].forEach(d => {
        html += `<div class="mes-header">${d}</div>`;
    });
    for (let i=1; i < startDow; i++) html += `<div class="mes-cell vacio"></div>`;

    for (let dia=1; dia <= ultimoDia.getDate(); dia++) {
        const d     = new Date(año, mes, dia);
        const iso   = isoFecha(d);
        const esHoy = iso === hoy ? " es-hoy" : "";
        const citas = dbCitas.filter(c => c.fecha === iso);

        html += `<div class="mes-cell${esHoy}" data-fecha="${iso}" data-hora="09:00">`;
        html += `<span class="mes-numero">${dia}</span>`;
        citas.slice(0,3).forEach(c => {
            html += `<div class="mes-apt ${c.color}" data-id="${c.id}" onclick="verDetalleCita(${c.id}, event)" style="cursor: pointer;">
                <span>${c.hora} ${c.paciente.split(" ")[0]}</span>
                <button class="mes-del-btn" onclick="abrirModalCancelar(${c.id},event)" title="Cancelar">×</button>
            </div>`;
        });
        if (citas.length > 3) html += `<div class="mes-mas">+${citas.length-3} más</div>`;
        html += `</div>`;
    }
    html += `</div>`;
    cont.innerHTML = html;
    bindCeldas(cont);
}

// ── TARJETA DE CITA ──────────────────────────────
function tarjetaCita(c) {
    const estadoClass = c.estado === "Pendiente" ? "estado-pendiente" : "estado-confirmada";
    return `<div class="appointment ${c.color}" data-id="${c.id}" onclick="verDetalleCita(${c.id}, event)" style="cursor: pointer;">
        <div class="apt-top">
            <strong>${c.paciente}</strong>
            <div class="apt-btns">
                <button class="apt-icon-btn" onclick="abrirModalReprogramar(${c.id},event)" title="Reprogramar"><i class="fa-regular fa-calendar"></i></button>
                <button class="apt-icon-btn red" onclick="abrirModalCancelar(${c.id},event)" title="Cancelar"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <span>${c.tratamiento}</span>
        <span>${c.hora}${c.odontologo ? ' · Dr(a). '+c.odontologo : ''}</span>
        <span class="apt-estado ${estadoClass}">${c.estado}</span>
    </div>`;
}

// ── BIND CELDAS ──────────────────────────────────
function bindCeldas(cont) {
    cont.querySelectorAll(".day-cell, .mes-cell:not(.vacio)").forEach(cell => {
        cell.addEventListener("click", function(e) {
            if (e.target !== this && !e.target.classList.contains("mes-numero")) return;
            abrirModalNuevaCita(this.dataset.fecha, this.dataset.hora || "09:00");
        });
    });
}

// ── PANEL PACIENTES HOY ──────────────────────────
function renderPacientesHoy() {
    const hoy   = isoFecha(new Date());
    const ahora = new Date();
    const horaActual = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');
    
    const citas = dbCitas.filter(c => 
        c.fecha === hoy && 
        c.estado === "Pendiente" && 
        c.hora >= horaActual
    ).sort((a,b) => a.hora.localeCompare(b.hora));
    
    const lista = document.getElementById("listaPacientesHoy");
    if (!lista) return;

    if (citas.length === 0) {
        lista.innerHTML = `<li style="color:var(--text-muted);font-size:13px;padding:12px 0;text-align:center;">No hay citas para hoy</li>`;
        return;
    }
    lista.innerHTML = citas.map(c => `
        <li class="patient-item" onclick="verDetalleCita(${c.id}, event)" style="cursor: pointer;">
            <div class="patient-item-info">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.paciente)}&background=random" alt="">
                <div class="details">
                    <strong>${c.paciente}</strong>
                    <span>${c.hora} · ${c.tratamiento}</span>
                </div>
            </div>
            <button class="apt-icon-btn red" onclick="abrirModalCancelar(${c.id},event)" title="Cancelar cita">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </li>
    `).join("");
}

// ── 5. MODAL NUEVA CITA ──────────────────────────
function abrirModalNuevaCita(fechaPre, horaPre) {
    if (fechaPre) {
        document.getElementById("fCitaFecha").value = fechaPre;
    }
    
    // We clear the Odontologo since we need both to fetch hours
    document.getElementById("fCitaOdontologo").value = "";
    document.getElementById("fCitaHora").innerHTML = '<option value="">— Seleccionar hora —</option>';
    
    // Si viene horaPre, podríamos intentar preseleccionarla después de que el usuario elija odontólogo, pero es complejo.
    // Lo más limpio es que el usuario seleccione el odontólogo y se re-carguen las horas.
    
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
    const pacienteId   = document.getElementById("fCitaPaciente").value;
    const odontologoId = document.getElementById("fCitaOdontologo").value;
    const fecha        = document.getElementById("fCitaFecha").value;
    const hora         = document.getElementById("fCitaHora").value;
    const tratamiento  = document.getElementById("fCitaTratamiento").value;

    if (!pacienteId || !odontologoId || !fecha || !hora || !tratamiento) {
        mostrarToast("Completa todos los campos obligatorios.", "error"); return;
    }

    fetch('/LOGIN_ORIGINAL/admin/agenda/guardar_cita', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ paciente_id: pacienteId, odontologo_id: odontologoId, fecha, hora, tratamiento })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            document.getElementById("modalCita").style.display = "none";
            document.getElementById("formCita").reset();
            mostrarToast("✅ Cita registrada exitosamente", "success");
            cargarCitasDesdeBD();
        } else {
            mostrarToast("Error: " + (res.message || "intenta de nuevo"), "error");
        }
    })
    .catch(() => mostrarToast("No se pudo conectar con el servidor", "error"));
});

// ── 6. MODAL CANCELAR CITA ───────────────────────
let citaCancelando = null;

function abrirModalCancelar(id, e) {
    if (e) e.stopPropagation();
    const c = dbCitas.find(x => x.id == id);
    if (!c) return;
    citaCancelando = id;

    // Reutilizamos el modal de reprogramar o creamos uno inline
    const info = document.getElementById("rCitaInfo");
    if (info) info.textContent = `${c.paciente} — ${c.tratamiento} (${c.fecha} ${c.hora})`;

    const motivo = prompt(`Motivo de cancelación para la cita de ${c.paciente}:`, "Cancelada por administrador");
    if (motivo === null) return; // el usuario canceló el prompt

    fetch('/LOGIN_ORIGINAL/admin/agenda/cancelar_cita', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_cita: id, motivo })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            mostrarToast("Cita cancelada correctamente", "success");
            cargarCitasDesdeBD();
        } else {
            mostrarToast("Error: " + res.message, "error");
        }
    })
    .catch(() => mostrarToast("No se pudo conectar con el servidor", "error"));
}

// ── 7. MODAL REPROGRAMAR CITA ────────────────────
let citaReprogramandoId = null;

function abrirModalReprogramar(id, e) {
    if (e) e.stopPropagation();
    const c = dbCitas.find(x => x.id == id);
    if (!c) return;
    citaReprogramandoId = id;
    document.getElementById("rCitaInfo").textContent = `${c.paciente} — ${c.tratamiento} (${c.fecha} ${c.hora})`;
    document.getElementById("rCitaFecha").value = c.fecha;
    document.getElementById("modalReprogramar").style.display = "flex";
    
    // Fetch hours for the existing odontologist on the new/current date
    actualizarHorasDisponiblesReprogramar(c.fecha, c.id_odontologo, "rCitaHora", c.hora);
}

document.getElementById("closeModalReprogramar").addEventListener("click", () => {
    document.getElementById("modalReprogramar").style.display = "none";
});

document.getElementById("formReprogramar").addEventListener("submit", e => {
    e.preventDefault();
    const nuevaFecha = document.getElementById("rCitaFecha").value;
    const nuevaHora  = document.getElementById("rCitaHora").value;
    const motivo     = document.getElementById("rCitaMotivo")
                        ? document.getElementById("rCitaMotivo").value
                        : "Reprogramada desde administrador";

    fetch('/LOGIN_ORIGINAL/admin/agenda/reprogramar_cita', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_cita: citaReprogramandoId,
            nueva_fecha_hora: nuevaFecha + ' ' + nuevaHora + ':00',
            motivo
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            document.getElementById("modalReprogramar").style.display = "none";
            document.getElementById("formReprogramar").reset();
            mostrarToast("📅 Cita reprogramada correctamente", "success");
            cargarCitasDesdeBD();
        } else {
            mostrarToast("Error: " + res.message, "error");
        }
    })
    .catch(() => mostrarToast("No se pudo conectar con el servidor", "error"));
});

// ── 8. BOTONES TOOLBAR ───────────────────────────
document.getElementById("btnNuevaCita").addEventListener("click", () => {
    abrirModalNuevaCita(isoFecha(fechaBase), "09:00");
});
document.getElementById("btnReprogramarMain").addEventListener("click", () => {
    const ahora = new Date();
    const citasFuturas = dbCitas.filter(c => {
        const citaDate = new Date(`${c.fecha}T${c.hora}`);
        return citaDate >= ahora;
    });

    if (citasFuturas.length === 0) { 
        mostrarToast("No hay citas futuras para reprogramar.", "info"); 
        return; 
    }
    const sel = document.getElementById("rSeleccionCita");
    if (sel) {
        sel.innerHTML = citasFuturas.map(c =>
            `<option value="${c.id}">${c.paciente} — ${c.fecha} ${c.hora}</option>`
        ).join("");
        sel.onchange = () => {
            const c = citasFuturas.find(x => x.id == sel.value);
            if (c) {
                document.getElementById("rCitaInfo").textContent = `${c.paciente} — ${c.tratamiento}`;
                document.getElementById("rCitaFecha").value = c.fecha;
                citaReprogramandoId = c.id;
                actualizarHorasDisponiblesReprogramar(c.fecha, c.id_odontologo, "rCitaHora", c.hora);
            }
        };
    }

    const first = citasFuturas[0];
    citaReprogramandoId = first.id;
    document.getElementById("rCitaInfo").textContent = `${first.paciente} — ${first.tratamiento}`;
    document.getElementById("rCitaFecha").value = first.fecha;
    actualizarHorasDisponiblesReprogramar(first.fecha, first.id_odontologo, "rCitaHora", first.hora);
    document.getElementById("modalReprogramar").style.display = "flex";
});

// ── 9. CONTROLES DE VISTA Y NAVEGACIÓN ───────────
document.getElementById("btnVistaDia").addEventListener("click", () => {
    vistaActual = "dia"; setActiveVista("btnVistaDia"); render();
});
document.getElementById("btnVistaSemana").addEventListener("click", () => {
    vistaActual = "semana"; setActiveVista("btnVistaSemana"); render();
});
document.getElementById("btnVistaMes").addEventListener("click", () => {
    vistaActual = "mes"; setActiveVista("btnVistaMes"); render();
});
document.getElementById("btnVerHoy").addEventListener("click", () => {
    fechaBase = new Date(); render(); mostrarToast("Mostrando fecha actual.", "info");
});
document.getElementById("btnAnterior").addEventListener("click", () => {
    if (vistaActual === "dia")         fechaBase = addDays(fechaBase, -1);
    else if (vistaActual === "semana") fechaBase = addDays(fechaBase, -7);
    else { fechaBase.setMonth(fechaBase.getMonth()-1); fechaBase = new Date(fechaBase); }
    render();
});
document.getElementById("btnSiguiente").addEventListener("click", () => {
    if (vistaActual === "dia")         fechaBase = addDays(fechaBase, 1);
    else if (vistaActual === "semana") fechaBase = addDays(fechaBase, 7);
    else { fechaBase.setMonth(fechaBase.getMonth()+1); fechaBase = new Date(fechaBase); }
    render();
});
function setActiveVista(activeId) {
    ["btnVistaDia","btnVistaSemana","btnVistaMes"].forEach(id => {
        document.getElementById(id).classList.toggle("active", id === activeId);
    });
}

// ── VER DETALLE DE CITA (MODAL SWAL) ──────────────
function verDetalleCita(id, e) {
    if (e) e.stopPropagation();
    const c = dbCitas.find(x => x.id == id);
    if (!c) return;

    const colorBg = {
        "Pendiente":   "#dbeafe",
        "Completada":  "#dcfce7",
        "Cancelada":   "#fee2e2",
        "No asistió":  "#f1f5f9"
    }[c.estado] || "#f3e8ff";

    const colorText = {
        "Pendiente":   "#1e40af",
        "Completada":  "#166534",
        "Cancelada":   "#991b1b",
        "No asistió":  "#475569"
    }[c.estado] || "#6b21a8";

    let fechaFmt = c.fecha;
    if (c.fecha) {
        const parts = c.fecha.split('-');
        if (parts.length === 3) {
            fechaFmt = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
    }

    Swal.fire({
        title: '<i class="fa-solid fa-calendar-check" style="color: #2563eb; margin-right: 8px;"></i> Detalles de la Cita',
        html: `
            <div style="text-align: left; font-family: 'Inter', sans-serif; font-size: 14.5px; line-height: 1.6; color: #1e293b; padding: 10px 0;">
                <div style="margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Paciente</strong>
                    <span style="font-weight: 600; color: #0f172a; font-size: 16px;"><i class="fa-solid fa-user" style="color: #94a3b8; margin-right: 6px;"></i> ${c.paciente}</span>
                </div>
                <div style="margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Odontólogo</strong>
                    <span style="font-weight: 500; color: #334155;"><i class="fa-solid fa-user-doctor" style="color: #94a3b8; margin-right: 6px;"></i> ${c.odontologo}</span>
                </div>
                <div style="margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Procedimiento / Tratamiento</strong>
                    <span style="font-weight: 500; color: #334155;"><i class="fa-solid fa-tooth" style="color: #94a3b8; margin-right: 6px;"></i> ${c.tratamiento}</span>
                </div>
                <div style="margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Fecha</strong>
                        <span style="font-weight: 500; color: #334155;"><i class="fa-regular fa-calendar-days" style="color: #94a3b8; margin-right: 6px;"></i> ${fechaFmt}</span>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Hora</strong>
                        <span style="font-weight: 500; color: #334155;"><i class="fa-regular fa-clock" style="color: #94a3b8; margin-right: 6px;"></i> ${c.hora}</span>
                    </div>
                </div>
                <div style="margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Consultorio</strong>
                        <span style="font-weight: 500; color: #334155;"><i class="fa-solid fa-door-open" style="color: #94a3b8; margin-right: 6px;"></i> ${c.consultorio}</span>
                    </div>
                    <div>
                        <strong style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 3px;">Estado</strong>
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 12.5px; font-weight: 600; background-color: ${colorBg}; color: ${colorText};">
                            ${c.estado}
                        </span>
                    </div>
                </div>
            </div>
        `,
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Aceptar',
        confirmButtonColor: '#2563eb'
    });
}

// ── 10. TOASTS ───────────────────────────────────
function mostrarToast(mensaje, tipo) {
    let cont = document.getElementById("toastCont");
    if (!cont) {
        cont = document.createElement("div"); cont.id = "toastCont";
        cont.style.cssText = "position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;";
        document.body.appendChild(cont);
    }
    const colors = { success:"#22c55e", error:"#ef4444", info:"#3b82f6" };
    const t = document.createElement("div");
    t.style.cssText = `background:white;border-left:4px solid ${colors[tipo]||colors.info};padding:12px 18px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);font-size:13.5px;color:#1e293b;max-width:340px;`;
    t.textContent = mensaje;
    cont.appendChild(t);
    setTimeout(() => { t.style.opacity="0"; t.style.transition="opacity .3s"; setTimeout(()=>t.remove(),300); }, 3500);
}