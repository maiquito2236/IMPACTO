/* ========================================================
   NOTIFICACIONES — administrador
   Odonto Estética
   Los datos vienen de la BD a través de NOTIFICACIONES_BD
   inyectado por PHP en la vista.
======================================================== */

/* ── 1. DATOS DESDE LA BD ─────────────────────────── */
// NOTIFICACIONES_BD es inyectado por la vista PHP.
// Lo copiamos a un array mutable para que el JS pueda
// reflejar cambios de estado sin recargar la página.
let notificaciones = (typeof NOTIFICACIONES_BD !== 'undefined')
    ? NOTIFICACIONES_BD.map(n => ({ ...n, archivada: false }))
    : [];

/* ── 2. CONFIGURACIÓN DE ICONOS Y COLORES ──────────── */
const TIPO_CONFIG = {
  "AGENDAMIENTO DE CITA":   { icon:"fa-regular fa-calendar-check",      bg:"#dbeafe", color:"#1d4ed8" },
  "RECORDATORIO 24H":       { icon:"fa-solid fa-bell",                  bg:"#fde8d8", color:"#9a3412" },
  "ALERTA DE AGENDA DIARIA":{ icon:"fa-solid fa-list-check",            bg:"#fef3c7", color:"#92400e" },
  "CANCELACION DE CITA":    { icon:"fa-solid fa-calendar-xmark",        bg:"#fee2e2", color:"#991b1b" },
  "CONFIRMACION DEL PAGO":  { icon:"fa-solid fa-circle-dollar-to-slot", bg:"#d1fae5", color:"#065f46" },
  "NUEVO PACIENTE REGISTRADO":  { icon:"fa-solid fa-user-plus",             bg:"#dbeafe", color:"#1d4ed8" },
  "ALERTA DE PAGO RECIBIDO":    { icon:"fa-solid fa-money-bill-trend-up",   bg:"#d1fae5", color:"#065f46" },
  "CITA CANCELADA / MODIFICADA":{ icon:"fa-solid fa-calendar-xmark",        bg:"#fee2e2", color:"#991b1b" },
  "NUEVA CITA REGISTRADA":      { icon:"fa-regular fa-calendar-plus",       bg:"#dcfce7", color:"#16a34a" },
  "ABONO":                      { icon:"fa-solid fa-money-bill-wave",       bg:"#d1fae5", color:"#065f46" },
  "PAGO RECIBIDO":              { icon:"fa-solid fa-check-double",          bg:"#dcfce7", color:"#16a34a" }
};

/* ── 3. ESTADO ─────────────────────────────────────── */
let paginaActual    = 1;
let porPagina       = 10;
let tabActiva       = "todas";
let seleccionados   = new Set();
let dropdownAbierto = null;
let sortDir         = "desc";

let filtros = { tipo: "", fecha: "" };

/* ── 4. HELPERS ────────────────────────────────────── */
const $ = id => document.getElementById(id);

function toast(msg, tipo = "success") {
  const el = $("toast");
  const icons = { success:"fa-circle-check", warning:"fa-triangle-exclamation", error:"fa-circle-xmark" };
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

/* ── 5. LISTA FILTRADA ─────────────────────────────── */
function getListaFiltrada() {
  return notificaciones.filter(n => {
    if (tabActiva === "sin-leer" && (n.archivada || n.estado !== "Sin leer")) return false;
    if (tabActiva === "leidas"   && (n.archivada || n.estado !== "Leída"))    return false;
    if (tabActiva === "todas"    && n.archivada)                               return false;

    if (filtros.tipo   && n.tipo   !== filtros.tipo)   return false;
    if (filtros.fecha  && n.fecha  !== filtros.fecha)   return false;

    return true;
  }).sort((a, b) => sortDir === "desc"
    ? (a.timestamp < b.timestamp) ? 1 : -1
    : (a.timestamp > b.timestamp) ? 1 : -1
  );
}

/* ── 6. CONTADORES ─────────────────────────────────── */
function actualizarContadores() {
  const todas   = notificaciones.filter(n => !n.archivada).length;
  const sinLeer = notificaciones.filter(n => !n.archivada && n.estado === "Sin leer").length;
  const leidas  = notificaciones.filter(n => !n.archivada && n.estado === "Leída").length;

  $("count-todas").textContent    = todas;
  $("count-sin-leer").textContent = sinLeer;
  if ($("count-leidas")) {
    $("count-leidas").textContent = leidas;
  }

  const badge = $("bell-badge");
  badge.textContent   = sinLeer;
  badge.style.display = sinLeer > 0 ? "" : "none";
}

/* ── 7. RENDER TABLA ───────────────────────────────── */
function renderTabla() {
  const lista  = getListaFiltrada();
  const total  = lista.length;
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
    const cfg    = TIPO_CONFIG[n.tipo] || { icon:"fa-solid fa-bell", bg:"#f1f5f9", color:"#475569" };
    const unread = n.estado === "Sin leer" && !n.archivada;
    const selec  = seleccionados.has(n.id);

    const tr = document.createElement("tr");
    tr.dataset.id = n.id;
    if (unread) tr.classList.add("unread");
    if (selec)  tr.classList.add("selected");

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
      <td class="td-msg ${unread ? 'unread-msg' : ''}">${n.mensaje}</td>
      <td class="td-paciente">${n.paciente}</td>
      <td><span class="estado-pill ${unread ? 'pill-sin-leer' : 'pill-leida'}">${n.estado}</span></td>
      <td class="td-acciones" onclick="event.stopPropagation()">
        <button class="btn-menu-dots" data-id="${n.id}">
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
      </td>
    `;

    tr.addEventListener("click", () => abrirModal(n.id));
    tbody.appendChild(tr);
  });

  const fin = Math.min(inicio + porPagina, total);
  $("pag-info").textContent = `Mostrando ${total > 0 ? inicio + 1 : 0} a ${fin} de ${total} resultados`;
  renderPaginacion(total, paginaActual);

  document.querySelectorAll(".row-check").forEach(cb => {
    cb.addEventListener("change", e => {
      const id = +e.target.dataset.id;
      if (e.target.checked) seleccionados.add(id);
      else seleccionados.delete(id);
      actualizarBulkBar();
      e.target.closest("tr").classList.toggle("selected", e.target.checked);
    });
  });

  document.querySelectorAll(".btn-menu-dots").forEach(btn => {
    btn.addEventListener("click", e => {
      e.stopPropagation();
      cerrarDropdowns();
      abrirDropdown(+btn.dataset.id, btn);
    });
  });

  const allVisible = pagina.every(n => seleccionados.has(n.id));
  $("check-all").checked       = pagina.length > 0 && allVisible;
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
    for (let i = Math.max(2, pag - 1); i <= Math.min(totalPag - 1, pag + 1); i++) rango.push(i);
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
    acciones.push({ label:"Marcar como leída", icon:"fa-regular fa-circle-check", fn:() => marcarLeida(id) });
  else
    acciones.push({ label:"Marcar como sin leer", icon:"fa-regular fa-circle", fn:() => marcarSinLeer(id) });

  acciones.push({ label:"Ver detalle", icon:"fa-regular fa-eye", fn:() => abrirModal(id) });

  acciones.forEach(a => {
    const b = document.createElement("button");
    b.innerHTML = `<i class="${a.icon}"></i> ${a.label}`;
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

/* ── 10. ACCIONES CON AJAX A LA BD ─────────────────── */
async function marcarLeida(id) {
  const fd = new FormData();
  fd.append("id", id);
  try {
    const r = await fetch("/LOGIN_ORIGINAL/admin/notificaciones/marcar_leida", { method:"POST", body:fd });
    const j = await r.json();
    if (j.success) {
      const n = notificaciones.find(x => x.id === id);
      if (n) n.estado = "Leída";
      actualizarContadores();
      renderTabla();
      toast("Marcada como leída.");
    } else {
      toast("Error al actualizar.", "error");
    }
  } catch {
    toast("Error de conexión.", "error");
  }
}

async function marcarSinLeer(id) {
  // La BD solo maneja NO_LEIDA/LEIDA; actualizamos solo visualmente
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;
  n.estado = "Sin leer";
  actualizarContadores();
  renderTabla();
  toast("Marcada como sin leer.", "warning");
}

async function marcarTodas() {
  const hayNoLeidas = notificaciones.some(n => !n.archivada && n.estado === "Sin leer");
  if (!hayNoLeidas) { toast("Ya no hay notificaciones sin leer.", "warning"); return; }

  try {
    const r = await fetch("/LOGIN_ORIGINAL/admin/notificaciones/marcar_todas", { method:"POST" });
    const j = await r.json();
    if (j.success) {
      notificaciones.forEach(n => { if (!n.archivada) n.estado = "Leída"; });
      actualizarContadores();
      renderTabla();
      toast("Todas marcadas como leídas.");
    } else {
      toast("Error al actualizar.", "error");
    }
  } catch {
    toast("Error de conexión.", "error");
  }
}

async function marcarSeleccionadasLeidas() {
  const ids = [...seleccionados];
  const fd  = new FormData();

  // Llamadas en paralelo, una por ID
  const promesas = ids.map(id => {
    const f = new FormData();
    f.append("id", id);
    return fetch("/LOGIN_ORIGINAL/admin/notificaciones/marcar_leida", { method:"POST", body:f });
  });

  try {
    await Promise.all(promesas);
    ids.forEach(id => {
      const n = notificaciones.find(x => x.id === id);
      if (n) n.estado = "Leída";
    });
    const c = seleccionados.size;
    seleccionados.clear();
    actualizarContadores();
    actualizarBulkBar();
    renderTabla();
    toast(`${c} notificación${c !== 1 ? "es" : ""} marcada${c !== 1 ? "s" : ""} como leída${c !== 1 ? "s" : ""}.`);
  } catch {
    toast("Error de conexión.", "error");
  }
}

/* ── 11. MODAL DE DETALLE ─────────────────────────── */
function abrirModal(id) {
  const n = notificaciones.find(x => x.id === id);
  if (!n) return;

  if (n.estado === "Sin leer" && !n.archivada) {
    marcarLeida(id); // también persiste en BD
  }

  const cfg     = TIPO_CONFIG[n.tipo] || { icon:"fa-solid fa-bell", bg:"#f1f5f9", color:"#475569" };
  const overlay = $("modal-overlay");

  $("modal-icon-wrap").style.background = cfg.bg;
  $("modal-icon-wrap").style.color      = cfg.color;
  $("modal-icon-wrap").innerHTML = `<i class="${cfg.icon}"></i>`;
  $("modal-title").textContent   = n.tipo;
  $("modal-fecha").textContent   = formatFecha(n.fecha, n.hora);
  $("modal-msg").textContent     = n.mensaje;

  $("modal-meta").innerHTML = `
    <div class="modal-meta-row"><span>Usuario / Origen</span><strong>${n.paciente}</strong></div>
    <div class="modal-meta-row"><span>Estado</span><strong>${n.estado}</strong></div>
  `;

  $("modal-cerrar").onclick = cerrarModal;
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
  $("filtro-tipo").addEventListener("change",   e => { filtros.tipo   = e.target.value; paginaActual = 1; renderTabla(); });
  $("filtro-fecha").addEventListener("change",  e => { filtros.fecha  = e.target.value; paginaActual = 1; renderTabla(); });

  const limpiar = () => {
    filtros = { tipo:"", fecha:"" };
    ["filtro-tipo","filtro-fecha"].forEach(id => {
      const el = $(id);
      if (el) el.value = "";
    });
    paginaActual = 1;
    renderTabla();
    toast("Filtros limpiados.");
  };
  $("btn-limpiar").addEventListener("click",  limpiar);
  $("btn-limpiar2").addEventListener("click", limpiar);

  /* MARCAR TODAS */
  $("btn-marcar-todas").addEventListener("click", marcarTodas);

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

  /* BULK */
  $("bulk-leer").addEventListener("click", marcarSeleccionadasLeidas);
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
    toast("Mostrando notificaciones sin leer.");
  });

  /* NUEVA CITA */
  $("btn-nueva-cita").addEventListener("click", () => {
    window.location.href = "/LOGIN_ORIGINAL/admin/agenda";
  });

  /* CERRAR DROPDOWNS */
  document.addEventListener("click", () => cerrarDropdowns());
}

/* ── 14. ARRANQUE ─────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
  init();
  actualizarContadores();
  renderTabla();
});