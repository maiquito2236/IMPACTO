/* ========================================================
   NOTIFICACIONES — script.js  (completamente funcional)
   Odonto Estética
======================================================== */
 
/* ── 1. BASE DE DATOS ─────────────────────────────── */
let notificaciones = [
  { id:1,  fecha:"2026-05-16", hora:"10:30 AM", tipo:"Cita próxima",              categoria:"Agenda",          mensaje:"Tienes una cita con María Fernanda López mañana a las 10:30 AM.",         paciente:"María F. López",    estado:"Sin leer",  archivada:false },
  { id:2,  fecha:"2026-05-15", hora:"3:45 PM",  tipo:"Tratamiento completado",    categoria:"Tratamientos",    mensaje:"Has completado el tratamiento de limpieza dental para Ana Sofía Martínez.", paciente:"Ana Sofía Martínez",estado:"Sin leer",  archivada:false },
  { id:3,  fecha:"2026-05-15", hora:"11:20 AM", tipo:"Pago recibido",             categoria:"Facturación",     mensaje:"Se ha registrado un pago de $120.000 de Juan Camilo Ramírez.",              paciente:"Juan Camilo Ramírez",estado:"Leída",   archivada:false },
  { id:4,  fecha:"2026-05-15", hora:"9:00 AM",  tipo:"Recordatorio",              categoria:"Recordatorios",   mensaje:"Tienes 3 citas por confirmar para mañana.",                                 paciente:"—",                 estado:"Sin leer",  archivada:false },
  { id:5,  fecha:"2026-05-14", hora:"4:30 PM",  tipo:"Nuevo paciente",            categoria:"Pacientes",       mensaje:"Valentina Gómez se ha registrado como nuevo paciente.",                     paciente:"Valentina Gómez",   estado:"Leída",    archivada:false },
  { id:6,  fecha:"2026-05-14", hora:"12:15 PM", tipo:"Plan de tratamiento creado",categoria:"Planes",          mensaje:"Has creado un nuevo plan de tratamiento para Diego Alejandro Ruiz.",        paciente:"Diego A. Ruiz",     estado:"Leída",    archivada:false },
  { id:7,  fecha:"2026-05-13", hora:"5:10 PM",  tipo:"Documento subido",          categoria:"Historia Clínica",mensaje:"El documento Radiografía Panorámica fue subido a la historia clínica.",    paciente:"Claudia Bernal",    estado:"Leída",    archivada:false },
  { id:8,  fecha:"2026-05-13", hora:"10:05 AM", tipo:"Actualización del sistema", categoria:"Sistema",         mensaje:"Se han realizado mejoras en el rendimiento del sistema.",                    paciente:"Sistema",           estado:"Leída",    archivada:false },
  { id:9,  fecha:"2026-05-12", hora:"2:00 PM",  tipo:"Cita próxima",              categoria:"Agenda",          mensaje:"Recordatorio: cita con Carlos Mendoza el viernes a las 3:00 PM.",          paciente:"Carlos Mendoza",    estado:"Leída",    archivada:false },
  { id:10, fecha:"2026-05-12", hora:"10:00 AM", tipo:"Pago recibido",             categoria:"Facturación",     mensaje:"Pago de $85.000 recibido de Sofía Herrera por ortodoncia mensual.",        paciente:"Sofía Herrera",     estado:"Leída",    archivada:false },
  { id:11, fecha:"2026-05-11", hora:"5:30 PM",  tipo:"Tratamiento completado",    categoria:"Tratamientos",    mensaje:"Tratamiento de blanqueamiento dental completado para Pedro Salcedo.",       paciente:"Pedro Salcedo",     estado:"Leída",    archivada:false },
  { id:12, fecha:"2026-05-11", hora:"9:15 AM",  tipo:"Nuevo paciente",            categoria:"Pacientes",       mensaje:"Andrés Felipe Torres se ha registrado como nuevo paciente.",               paciente:"A. Felipe Torres",  estado:"Leída",    archivada:false },
  { id:13, fecha:"2026-05-10", hora:"4:00 PM",  tipo:"Recordatorio",              categoria:"Recordatorios",   mensaje:"2 pacientes no han confirmado sus citas de mañana.",                        paciente:"—",                 estado:"Leída",    archivada:false },
  { id:14, fecha:"2026-05-10", hora:"11:30 AM", tipo:"Documento subido",          categoria:"Historia Clínica",mensaje:"Radiografía periapical subida para Laura Jiménez.",                       paciente:"Laura Jiménez",     estado:"Leída",    archivada:false },
  { id:15, fecha:"2026-05-09", hora:"3:20 PM",  tipo:"Plan de tratamiento creado",categoria:"Planes",          mensaje:"Plan de ortodoncia fase 2 creado para Roberto Castro.",                    paciente:"Roberto Castro",    estado:"Leída",    archivada:false },
  { id:16, fecha:"2026-05-09", hora:"8:45 AM",  tipo:"Actualización del sistema", categoria:"Sistema",         mensaje:"Respaldo automático de base de datos completado exitosamente.",             paciente:"Sistema",           estado:"Leída",    archivada:false },
  // Archivadas
  { id:17, fecha:"2026-05-08", hora:"2:30 PM",  tipo:"Pago recibido",             categoria:"Facturación",     mensaje:"Pago de $200.000 recibido de Marcela Ríos.",                               paciente:"Marcela Ríos",      estado:"Leída",    archivada:true  },
  { id:18, fecha:"2026-05-08", hora:"10:00 AM", tipo:"Cita próxima",              categoria:"Agenda",          mensaje:"Recordatorio cita con Diego Lozano el 09/05.",                             paciente:"Diego Lozano",      estado:"Leída",    archivada:true  },
  { id:19, fecha:"2026-05-07", hora:"6:00 PM",  tipo:"Tratamiento completado",    categoria:"Tratamientos",    mensaje:"Endodoncia molar completada para Carmen Suárez.",                          paciente:"Carmen Suárez",     estado:"Leída",    archivada:true  },
  { id:20, fecha:"2026-05-07", hora:"11:00 AM", tipo:"Nuevo paciente",            categoria:"Pacientes",       mensaje:"María José Vargas registrada como nueva paciente.",                        paciente:"María J. Vargas",   estado:"Leída",    archivada:true  },
  { id:21, fecha:"2026-05-06", hora:"4:15 PM",  tipo:"Recordatorio",              categoria:"Recordatorios",   mensaje:"4 citas pendientes de confirmación para la próxima semana.",               paciente:"—",                 estado:"Leída",    archivada:true  },
  { id:22, fecha:"2026-05-06", hora:"9:30 AM",  tipo:"Actualización del sistema", categoria:"Sistema",         mensaje:"Actualización de módulo de facturación instalada.",                        paciente:"Sistema",           estado:"Leída",    archivada:true  },
  { id:23, fecha:"2026-05-05", hora:"3:00 PM",  tipo:"Documento subido",          categoria:"Historia Clínica",mensaje:"Examen de sangre subido al expediente de Juan Pérez.",                    paciente:"Juan Pérez",        estado:"Leída",    archivada:true  },
  { id:24, fecha:"2026-05-05", hora:"8:00 AM",  tipo:"Plan de tratamiento creado",categoria:"Planes",          mensaje:"Plan de implantes dental creado para Hernán Ortiz.",                      paciente:"Hernán Ortiz",      estado:"Leída",    archivada:true  }
];
 
/* ── 2. CONFIGURACIÓN DE ICONOS Y COLORES ──────────── */
const TIPO_CONFIG = {
  "Cita próxima":               { icon:"fa-regular fa-calendar-check", bg:"#dbeafe", color:"#1d4ed8" },
  "Tratamiento completado":     { icon:"fa-solid fa-tooth",            bg:"#d1fae5", color:"#065f46" },
  "Pago recibido":              { icon:"fa-solid fa-circle-dollar-to-slot", bg:"#fef3c7", color:"#92400e" },
  "Recordatorio":               { icon:"fa-solid fa-bell",             bg:"#fde8d8", color:"#9a3412" },
  "Nuevo paciente":             { icon:"fa-solid fa-user-plus",        bg:"#f3e8ff", color:"#6b21a8" },
  "Plan de tratamiento creado": { icon:"fa-solid fa-clipboard-list",   bg:"#ccfbf1", color:"#0f766e" },
  "Documento subido":           { icon:"fa-regular fa-file-lines",     bg:"#cffafe", color:"#155e75" },
  "Actualización del sistema":  { icon:"fa-solid fa-gear",             bg:"#f1f5f9", color:"#475569" }
};
 
/* ── 3. ESTADO ─────────────────────────────────────── */
let paginaActual   = 1;
let porPagina      = 10;
let tabActiva      = "todas";
let seleccionados  = new Set();
let dropdownAbierto = null;
let sortDir        = "desc"; // fecha más reciente primero
 
let filtros = { tipo: "", categoria: "", estado: "", fecha: "" };
 
/* ── 4. HELPERS ────────────────────────────────────── */
const $ = id => document.getElementById(id);
const badgeClass = cat => "badge badge-" + cat.replace(/\s/g, "-").replace(/\//g, "-");
 
function toast(msg, tipo = "success") {
  const el = $("toast");
  const icons = { success: "fa-circle-check", warning: "fa-triangle-exclamation", error: "fa-circle-xmark" };
  el.className = `toast ${tipo}`;
  el.innerHTML = `<i class="fa-solid ${icons[tipo]}"></i> ${msg}`;
  el.classList.add("show");
  clearTimeout(el._t);
  el._t = setTimeout(() => el.classList.remove("show"), 3000);
}
 
function formatFecha(fecha, hora) {
  const meses = ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];
  const d = new Date(fecha + "T00:00:00");
  return `${d.getDate()} ${meses[d.getMonth()]}, ${hora}`;
}
 
/* ── 5. OBTENER LISTA FILTRADA ─────────────────────── */
function getListaFiltrada() {
  return notificaciones.filter(n => {
    // tab
    if (tabActiva === "sin-leer"  && (n.archivada || n.estado !== "Sin leer")) return false;
    if (tabActiva === "archivadas" && !n.archivada)  return false;
    if (tabActiva === "todas"     && n.archivada)    return false;
 
    // filtros
    if (filtros.tipo      && n.tipo      !== filtros.tipo)      return false;
    if (filtros.categoria && n.categoria !== filtros.categoria) return false;
    if (filtros.estado    && n.estado    !== filtros.estado)    return false;
    if (filtros.fecha     && n.fecha     !== filtros.fecha)     return false;
 
    return true;
  }).sort((a, b) => sortDir === "desc"
    ? (a.fecha + a.hora) < (b.fecha + b.hora) ? 1 : -1
    : (a.fecha + a.hora) > (b.fecha + b.hora) ? 1 : -1
  );
}
 
/* ── 6. CONTAR TABS ─────────────────────────────────── */
function actualizarContadores() {
  const todas     = notificaciones.filter(n => !n.archivada).length;
  const sinLeer   = notificaciones.filter(n => !n.archivada && n.estado === "Sin leer").length;
  const archivadas = notificaciones.filter(n => n.archivada).length;
 
  $("count-todas").textContent     = todas;
  $("count-sin-leer").textContent  = sinLeer;
  $("count-archivadas").textContent = archivadas;
 
  // badge campana
  const badge = $("bell-badge");
  badge.textContent = sinLeer;
  badge.style.display = sinLeer > 0 ? "" : "none";
  if (sinLeer === 0) badge.style.display = "none";
}
 
/* ── 7. RENDER TABLA ───────────────────────────────── */
function renderTabla() {
  const lista = getListaFiltrada();
  const total = lista.length;
  const inicio = (paginaActual - 1) * porPagina;
  const pagina = lista.slice(inicio, inicio + porPagina);
 
  const tbody = $("noti-tbody");
  const empty = $("empty-state");
 
  tbody.innerHTML = "";
 
  if (pagina.length === 0) {
    empty.style.display = "";
    $("pag-info").textContent = "Sin resultados";
    renderPaginacion(0, 0);
    return;
  }
  empty.style.display = "none";
 
  pagina.forEach(n => {
    const cfg  = TIPO_CONFIG[n.tipo] || { icon:"fa-solid fa-bell", bg:"#f1f5f9", color:"#475569" };
    const unread = n.estado === "Sin leer" && !n.archivada;
    const selec  = seleccionados.has(n.id);
    const estadoClass = n.archivada ? "pill-archivada" : (unread ? "pill-sin-leer" : "pill-leida");
    const estadoLabel = n.archivada ? "Archivada" : n.estado;
 
    const tr = document.createElement("tr");
    tr.dataset.id = n.id;
    if (unread)  tr.classList.add("unread");
    if (selec)   tr.classList.add("selected");
 
    tr.innerHTML = `
      <td>
        <div class="td-check-cell">
          ${unread ? '<span class="unread-dot"></span>' : '<span style="width:8px;display:inline-block;"></span>'}
          <input type="checkbox" class="row-check" data-id="${n.id}" ${selec ? "checked" : ""} onclick="event.stopPropagation()">
        </div>
      </td>
      <td style="color:var(--muted);white-space:nowrap;font-size:12.5px;">${formatFecha(n.fecha, n.hora)}</td>
      <td>
        <div class="td-tipo">
          <span class="tipo-icon" style="background:${cfg.bg};color:${cfg.color};">
            <i class="${cfg.icon}"></i>
          </span>
          <span style="font-size:12.5px;color:#334155;">${n.tipo}</span>
        </div>
      </td>
      <td><span class="${badgeClass(n.categoria)}">${n.categoria}</span></td>
      <td class="td-msg ${unread ? 'unread-msg' : ''}">${n.mensaje}</td>
      <td class="td-paciente">${n.paciente}</td>
      <td><span class="estado-pill ${estadoClass}">${estadoLabel}</span></td>
      <td class="td-acciones" onclick="event.stopPropagation()">
        <button class="btn-menu-dots" data-id="${n.id}">
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
      </td>
    `;
 
    // click fila → abrir modal
    tr.addEventListener("click", () => abrirModal(n.id));
 
    tbody.appendChild(tr);
  });
 
  // paginación info
  const fin = Math.min(inicio + porPagina, total);
  $("pag-info").textContent = `Mostrando ${total > 0 ? inicio + 1 : 0} a ${fin} de ${total} resultados`;
  renderPaginacion(total, paginaActual);
 
  // checkboxes
  document.querySelectorAll(".row-check").forEach(cb => {
    cb.addEventListener("change", e => {
      const id = +e.target.dataset.id;
      if (e.target.checked) seleccionados.add(id);
      else seleccionados.delete(id);
      actualizarBulkBar();
      e.target.closest("tr").classList.toggle("selected", e.target.checked);
    });
  });
 
  // botones de 3 puntos
  document.querySelectorAll(".btn-menu-dots").forEach(btn => {
    btn.addEventListener("click", e => {
      e.stopPropagation();
      const id = +btn.dataset.id;
      cerrarDropdowns();
      abrirDropdown(id, btn);
    });
  });
 
  // check-all estado
  const allVisible = pagina.every(n => seleccionados.has(n.id));
  $("check-all").checked = pagina.length > 0 && allVisible;
  $("check-all").indeterminate = !allVisible && pagina.some(n => seleccionados.has(n.id));
}
 
/* ── 8. PAGINACIÓN ─────────────────────────────────── */
function renderPaginacion(total, pag) {
  const totalPag = Math.ceil(total / porPagina);
  const cont = $("pag-pages");
  cont.innerHTML = "";
 
  const rango = [];
  if (totalPag <= 5) {
    for (let i = 1; i <= totalPag; i++) rango.push(i);
  } else {
    rango.push(1);
    if (pag > 3) rango.push("...");
    for (let i = Math.max(2, pag-1); i <= Math.min(totalPag-1, pag+1); i++) rango.push(i);
    if (pag < totalPag - 2) rango.push("...");
    rango.push(totalPag);
  }
 
  rango.forEach(r => {
    if (r === "...") {
      const span = document.createElement("span");
      span.textContent = "…";
      span.style.cssText = "padding:0 4px;color:var(--muted);font-size:13px;";
      cont.appendChild(span);
    } else {
      const btn = document.createElement("button");
      btn.textContent = r;
      btn.className = "pag-num" + (r === pag ? " active" : "");
      btn.addEventListener("click", () => { paginaActual = r; renderTabla(); });
      cont.appendChild(btn);
    }
  });
 
  $("pag-prev").disabled = pag <= 1;
  $("pag-next").disabled = pag >= totalPag || totalPag === 0;
}
 
/* ── 9. DROPDOWN DE ACCIONES ───────────────────────── */
function abrirDropdown(id, btn) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
 
  const dd = document.createElement("div");
  dd.className = "action-dropdown";
  dd.id = "dropdown-" + id;
 
  const acciones = [];
  if (n.estado === "Sin leer")
    acciones.push({ label:"Marcar como leída",    icon:"fa-regular fa-circle-check",  fn:() => marcarLeida(id) });
  else if (!n.archivada)
    acciones.push({ label:"Marcar como sin leer", icon:"fa-regular fa-circle",         fn:() => marcarSinLeer(id) });
 
  acciones.push({ label:"Ver detalle",  icon:"fa-regular fa-eye",           fn:() => abrirModal(id) });
 
  if (!n.archivada)
    acciones.push({ label:"Archivar",    icon:"fa-regular fa-folder",        fn:() => archivar(id) });
  else
    acciones.push({ label:"Desarchivar", icon:"fa-solid fa-folder-open",     fn:() => desarchivar(id) });
 
  acciones.push({ sep: true });
  acciones.push({ label:"Eliminar",    icon:"fa-regular fa-trash-can",      fn:() => eliminar(id), danger:true });
 
  acciones.forEach(a => {
    if (a.sep) {
      dd.appendChild(document.createElement("hr"));
      return;
    }
    const b = document.createElement("button");
    b.innerHTML = `<i class="${a.icon}"></i> ${a.label}`;
    if (a.danger) b.classList.add("danger");
    b.addEventListener("click", e => { e.stopPropagation(); cerrarDropdowns(); a.fn(); });
    dd.appendChild(b);
  });
 
  btn.closest(".td-acciones").appendChild(dd);
  dropdownAbierto = dd;
}
 
function cerrarDropdowns() {
  document.querySelectorAll(".action-dropdown").forEach(d => d.remove());
  dropdownAbierto = null;
}
 
/* ── 10. ACCIONES SOBRE NOTIFICACIONES ─────────────── */
function marcarLeida(id) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
  n.estado = "Leída";
  actualizarContadores();
  renderTabla();
  toast("Marcada como leída.");
}
 
function marcarSinLeer(id) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
  n.estado = "Sin leer";
  actualizarContadores();
  renderTabla();
  toast("Marcada como sin leer.", "warning");
}
 
function archivar(id) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
  n.archivada = true;
  seleccionados.delete(id);
  actualizarContadores();
  renderTabla();
  actualizarBulkBar();
  toast("Notificación archivada.");
}
 
function desarchivar(id) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
  n.archivada = false;
  actualizarContadores();
  renderTabla();
  toast("Notificación restaurada.");
}
 
function eliminar(id) {
  notificaciones = notificaciones.filter(x => x.id !== id);
  seleccionados.delete(id);
  if (paginaActual > 1 && getListaFiltrada().length <= (paginaActual - 1) * porPagina)
    paginaActual--;
  actualizarContadores();
  renderTabla();
  actualizarBulkBar();
  toast("Notificación eliminada.", "error");
}
 
/* ── 11. MODAL DE DETALLE ─────────────────────────── */
function abrirModal(id) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
 
  // marcar como leída al abrir
  if (n.estado === "Sin leer" && !n.archivada) {
    n.estado = "Leída";
    actualizarContadores();
    renderTabla();
  }
 
  const cfg = TIPO_CONFIG[n.tipo] || { icon:"fa-solid fa-bell", bg:"#f1f5f9", color:"#475569" };
  const overlay = $("modal-overlay");
 
  $("modal-icon-wrap").style.background = cfg.bg;
  $("modal-icon-wrap").style.color      = cfg.color;
  $("modal-icon-wrap").innerHTML = `<i class="${cfg.icon}"></i>`;
  $("modal-title").textContent  = n.tipo;
  $("modal-fecha").textContent  = formatFecha(n.fecha, n.hora);
  $("modal-msg").textContent    = n.mensaje;
 
  $("modal-meta").innerHTML = `
    <div class="modal-meta-row"><span>Categoría</span><strong><span class="${badgeClass(n.categoria)}">${n.categoria}</span></strong></div>
    <div class="modal-meta-row"><span>Paciente / Origen</span><strong>${n.paciente}</strong></div>
    <div class="modal-meta-row"><span>Estado</span><strong>${n.archivada ? "Archivada" : n.estado}</strong></div>
  `;
 
  // botón archivar/desarchivar
  const btnArch = $("modal-archivar");
  if (n.archivada) {
    btnArch.textContent = "Desarchivar";
    btnArch.onclick = () => { desarchivar(id); cerrarModal(); };
  } else {
    btnArch.textContent = "Archivar";
    btnArch.onclick = () => { archivar(id); cerrarModal(); };
  }
 
  // botón acción principal según categoría
  const btnAcc = $("modal-accion");
  const acciones = {
    "Agenda":         "Ver Cita",
    "Tratamientos":   "Ver Tratamiento",
    "Facturación":    "Ver Factura",
    "Recordatorios":  "Ver Agenda",
    "Pacientes":      "Ver Paciente",
    "Planes":         "Ver Plan",
    "Historia Clínica":"Ver Historia",
    "Sistema":        "Ver Bitácora"
  };
  btnAcc.textContent = acciones[n.categoria] || "Ver detalle";
  btnAcc.onclick = () => {
    cerrarModal();
    toast(`Redirigiendo a ${acciones[n.categoria] || "detalle"}…`);
  };
 
  overlay.classList.add("open");
}
 
function cerrarModal() {
  $("modal-overlay").classList.remove("open");
}
 
/* ── 12. BULK BAR ─────────────────────────────────── */
function actualizarBulkBar() {
  const bar   = $("bulk-bar");
  const count = seleccionados.size;
  $("bulk-count").textContent = `${count} seleccionada${count !== 1 ? "s" : ""}`;
  bar.classList.toggle("visible", count > 0);
}
 
/* ── 13. INICIALIZAR EVENTOS ──────────────────────── */
function init() {
  /* TABS */
  document.querySelectorAll(".tab").forEach(tab => {
    tab.addEventListener("click", () => {
      document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      tabActiva = tab.dataset.tab;
      paginaActual = 1;
      seleccionados.clear();
      actualizarBulkBar();
      renderTabla();
    });
  });
 
  /* FILTROS */
  ["filtro-tipo","filtro-categoria","filtro-estado"].forEach(fid => {
    $(fid).addEventListener("change", e => {
      const key = fid.replace("filtro-","");
      filtros[key === "tipo" ? "tipo" : key === "categoria" ? "categoria" : "estado"] = e.target.value;
      paginaActual = 1;
      renderTabla();
    });
  });
 
  $("filtro-fecha").addEventListener("change", e => {
    filtros.fecha = e.target.value;
    paginaActual = 1;
    renderTabla();
  });
 
  const limpiar = () => {
    filtros = { tipo:"", categoria:"", estado:"", fecha:"" };
    ["filtro-tipo","filtro-categoria","filtro-estado"].forEach(id => $(id).value = "");
    $("filtro-fecha").value = "";
    paginaActual = 1;
    renderTabla();
    toast("Filtros limpiados.");
  };
  $("btn-limpiar").addEventListener("click", limpiar);
  $("btn-limpiar2").addEventListener("click", limpiar);
 
  /* MARCAR TODAS */
  $("btn-marcar-todas").addEventListener("click", () => {
    const lista = getListaFiltrada();
    const hayNoLeidas = lista.some(n => n.estado === "Sin leer");
    if (!hayNoLeidas) { toast("Ya no hay notificaciones sin leer.","warning"); return; }
    lista.forEach(n => { if(n.estado === "Sin leer") n.estado = "Leída"; });
    actualizarContadores();
    renderTabla();
    toast("Todas marcadas como leídas.");
  });
 
  /* CHECK ALL */
  $("check-all").addEventListener("change", e => {
    const lista = getListaFiltrada();
    const pag   = lista.slice((paginaActual - 1) * porPagina, paginaActual * porPagina);
    pag.forEach(n => {
      if (e.target.checked) seleccionados.add(n.id);
      else seleccionados.delete(n.id);
    });
    actualizarBulkBar();
    renderTabla();
  });
 
  /* PAGINACIÓN */
  $("pag-prev").addEventListener("click", () => {
    if (paginaActual > 1) { paginaActual--; renderTabla(); }
  });
  $("pag-next").addEventListener("click", () => {
    const total = getListaFiltrada().length;
    if (paginaActual < Math.ceil(total / porPagina)) { paginaActual++; renderTabla(); }
  });
  $("per-page").addEventListener("change", e => {
    porPagina = +e.target.value;
    paginaActual = 1;
    renderTabla();
  });
 
  /* SORT FECHA */
  document.querySelector(".sortable").addEventListener("click", function() {
    sortDir = sortDir === "desc" ? "asc" : "desc";
    this.querySelector("i").className = sortDir === "desc"
      ? "fa-solid fa-sort-down" : "fa-solid fa-sort-up";
    paginaActual = 1;
    renderTabla();
  });
 
  /* BULK ACTIONS */
  $("bulk-leer").addEventListener("click", () => {
    seleccionados.forEach(id => {
      const n = notificaciones.find(x => x.id === id);
      if (n) n.estado = "Leída";
    });
    const c = seleccionados.size;
    seleccionados.clear();
    actualizarContadores();
    actualizarBulkBar();
    renderTabla();
    toast(`${c} notificación${c!==1?"es":""} marcada${c!==1?"s":""} como leída${c!==1?"s":""}.`);
  });
 
  $("bulk-archivar").addEventListener("click", () => {
    const c = seleccionados.size;
    seleccionados.forEach(id => {
      const n = notificaciones.find(x => x.id === id);
      if (n) n.archivada = true;
    });
    seleccionados.clear();
    actualizarContadores();
    actualizarBulkBar();
    renderTabla();
    toast(`${c} notificación${c!==1?"es":""} archivada${c!==1?"s":""}.`);
  });
 
  $("bulk-eliminar").addEventListener("click", () => {
    const c = seleccionados.size;
    const ids = [...seleccionados];
    notificaciones = notificaciones.filter(n => !ids.includes(n.id));
    seleccionados.clear();
    if (paginaActual > 1 && getListaFiltrada().length <= (paginaActual - 1) * porPagina)
      paginaActual--;
    actualizarContadores();
    actualizarBulkBar();
    renderTabla();
    toast(`${c} notificación${c!==1?"es":""} eliminada${c!==1?"s":""}.`, "error");
  });
 
  $("bulk-close").addEventListener("click", () => {
    seleccionados.clear();
    actualizarBulkBar();
    renderTabla();
  });
 
  /* MODAL */
  $("modal-close").addEventListener("click", cerrarModal);
  $("modal-overlay").addEventListener("click", e => {
    if (e.target === $("modal-overlay")) cerrarModal();
  });
  document.addEventListener("keydown", e => { if (e.key === "Escape") cerrarModal(); });
 
  /* CAMPANA */
  $("bell-btn").addEventListener("click", () => {
    tabActiva = "sin-leer";
    document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
    document.querySelector('[data-tab="sin-leer"]').classList.add("active");
    paginaActual = 1;
    renderTabla();
    $("bell-badge").classList.add("pulse");
    setTimeout(() => $("bell-badge").classList.remove("pulse"), 400);
    toast("Mostrando notificaciones sin leer.");
  });
 
  /* NUEVA CITA */
  $("btn-nueva-cita").addEventListener("click", () => {
    toast("Redirigiendo al módulo de Agenda…");
    setTimeout(() => window.location.href = "../Agenda/index.php", 1200);
  });
 
  /* CERRAR DROPDOWNS al hacer click fuera */
  document.addEventListener("click", () => cerrarDropdowns());
}
 
/* ── 14. ARRANQUE ─────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
  init();
  actualizarContadores();
  renderTabla();
});
