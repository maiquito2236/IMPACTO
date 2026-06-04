/* =========================================================
   REPORTES — script.js  (versión funcional completa)
   Odonto Estética — Administrador
   ========================================================= */
 
/* ── 1. BASE DE DATOS ─────────────────────────────────────── */
 
const DB = {
  periodos: {
    "este-periodo": {
      label: "Este periodo (1–16 May 2026)",
      ingresos:    58750.00,
      gananciaNet: 46270.00,
      margen:      78.7,
      facturas:    45,
      pacientes:   158,
      // datos diarios para el gráfico de líneas (16 días)
      dias: [
        { label: "1 May",  ing: 2800, egr: 1100, gan: 1700 },
        { label: "3 May",  ing: 3200, egr: 1300, gan: 1900 },
        { label: "5 May",  ing: 3900, egr: 1500, gan: 2400 },
        { label: "7 May",  ing: 3100, egr: 1200, gan: 1900 },
        { label: "9 May",  ing: 4200, egr: 1800, gan: 2400 },
        { label: "11 May", ing: 3700, egr: 1600, gan: 2100 },
        { label: "13 May", ing: 4500, egr: 1900, gan: 2600 },
        { label: "15 May", ing: 5100, egr: 2480, gan: 2620 },
        { label: "16 May", ing: 8750, egr: 2100, gan: 6650 }
      ],
      // datos para gráfica de barras (por tratamiento)
      tratamientos: [
        { nombre: "Blanqueamiento",  ingresos: 12400 },
        { nombre: "Ortodoncia",      ingresos: 18900 },
        { nombre: "Limpieza",        ingresos:  7200 },
        { nombre: "Implantes",       ingresos: 11800 },
        { nombre: "Carillas",        ingresos:  5650 },
        { nombre: "Endodoncia",      ingresos:  2800 }
      ],
      resultados: [
        { concepto: "Ingresos por tratamientos", actual: 45230, anterior: 38120, var: "+18.6%", trend: "up" },
        { concepto: "Ingresos por productos",    actual:  8250, anterior:  6420, var: "+28.5%", trend: "up" },
        { concepto: "Descuentos y devoluciones", actual:  -730, anterior:  -420, var: "+73.8%", trend: "down" },
        { concepto: "Costo de materiales",       actual: -6150, anterior: -5230, var: "+17.6%", trend: "down" },
        { concepto: "Gastos operativos",         actual: -6330, anterior: -5250, var: "+20.6%", trend: "down" },
        { concepto: "Ganancia Neta",             actual: 46270, anterior: 33640, var: "+37.6%", trend: "up", isTotal: true }
      ]
    },
    "mes-anterior": {
      label: "Mes anterior (Abr 2026)",
      ingresos:    49600.00,
      gananciaNet: 36800.00,
      margen:      74.2,
      facturas:    38,
      pacientes:   134,
      dias: [
        { label: "1 Abr",  ing: 2200, egr:  900, gan: 1300 },
        { label: "5 Abr",  ing: 2900, egr: 1100, gan: 1800 },
        { label: "10 Abr", ing: 3400, egr: 1400, gan: 2000 },
        { label: "15 Abr", ing: 4100, egr: 1700, gan: 2400 },
        { label: "20 Abr", ing: 4600, egr: 1900, gan: 2700 },
        { label: "25 Abr", ing: 5200, egr: 2100, gan: 3100 },
        { label: "30 Abr", ing: 5800, egr: 2200, gan: 3600 }
      ],
      tratamientos: [
        { nombre: "Blanqueamiento",  ingresos: 10200 },
        { nombre: "Ortodoncia",      ingresos: 15600 },
        { nombre: "Limpieza",        ingresos:  6100 },
        { nombre: "Implantes",       ingresos:  9800 },
        { nombre: "Carillas",        ingresos:  4700 },
        { nombre: "Endodoncia",      ingresos:  3200 }
      ],
      resultados: [
        { concepto: "Ingresos por tratamientos", actual: 38120, anterior: 32400, var: "+17.6%", trend: "up" },
        { concepto: "Ingresos por productos",    actual:  6420, anterior:  5100, var: "+25.9%", trend: "up" },
        { concepto: "Descuentos y devoluciones", actual:  -420, anterior:  -310, var: "+35.5%", trend: "down" },
        { concepto: "Costo de materiales",       actual: -5230, anterior: -4600, var: "+13.7%", trend: "down" },
        { concepto: "Gastos operativos",         actual: -5250, anterior: -4800, var: "+9.4%",  trend: "down" },
        { concepto: "Ganancia Neta",             actual: 33640, anterior: 27790, var: "+21.1%", trend: "up", isTotal: true }
      ]
    },
    "trimestre": {
      label: "Último trimestre (Feb–Abr 2026)",
      ingresos:    142300.00,
      gananciaNet: 105400.00,
      margen:      74.1,
      facturas:    112,
      pacientes:   396,
      dias: [
        { label: "Feb",    ing: 38200, egr: 10100, gan: 28100 },
        { label: "Mar",    ing: 43100, egr: 11800, gan: 31300 },
        { label: "Abr",    ing: 49600, egr: 13200, gan: 36400 }
      ],
      tratamientos: [
        { nombre: "Blanqueamiento",  ingresos: 31200 },
        { nombre: "Ortodoncia",      ingresos: 52800 },
        { nombre: "Limpieza",        ingresos: 18700 },
        { nombre: "Implantes",       ingresos: 24900 },
        { nombre: "Carillas",        ingresos: 10400 },
        { nombre: "Endodoncia",      ingresos:  4300 }
      ],
      resultados: [
        { concepto: "Ingresos por tratamientos", actual: 108200, anterior: 91400, var: "+18.4%", trend: "up" },
        { concepto: "Ingresos por productos",    actual:  19800, anterior: 15600, var: "+26.9%", trend: "up" },
        { concepto: "Descuentos y devoluciones", actual:  -1400, anterior:  -980, var: "+42.9%", trend: "down" },
        { concepto: "Costo de materiales",       actual: -17200, anterior:-14900, var: "+15.4%", trend: "down" },
        { concepto: "Gastos operativos",         actual: -16500, anterior:-14200, var: "+16.2%", trend: "down" },
        { concepto: "Ganancia Neta",             actual: 105400, anterior: 88320, var: "+19.3%", trend: "up", isTotal: true }
      ]
    }
  }
};
 
const ACTIVIDADES = [
  { fecha: "16/05/2026 10:30 AM", evento: "Inicio de sesión",      detalle: "Usuario: Dr. Andrés Díaz",   estado: "Éxito" },
  { fecha: "16/05/2026 10:25 AM", evento: "Respaldo automático",   detalle: "Base de datos",              estado: "Éxito" },
  { fecha: "16/05/2026 10:20 AM", evento: "Generación de factura", detalle: "FAC-00086",                  estado: "Éxito" },
  { fecha: "16/05/2026 10:15 AM", evento: "Envío de recordatorio", detalle: "Paciente: María López",      estado: "Éxito" },
  { fecha: "16/05/2026 10:10 AM", evento: "Error de validación",   detalle: "Intento de doble cita",     estado: "Advertencia" },
  { fecha: "16/05/2026 09:55 AM", evento: "Nuevo paciente",        detalle: "Registro: Carlos Ruiz",      estado: "Éxito" },
  { fecha: "16/05/2026 09:40 AM", evento: "Cita cancelada",        detalle: "Paciente: Ana Torres",       estado: "Advertencia" },
  { fecha: "16/05/2026 09:20 AM", evento: "Factura anulada",       detalle: "FAC-00082",                  estado: "Advertencia" }
];
 
/* ── 2. ESTADO GLOBAL ─────────────────────────────────────── */
let periodoActivo = "este-periodo";
 
/* ── 3. HELPERS ──────────────────────────────────────────── */
const fmt = n => n < 0
  ? `-$${Math.abs(n).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
  : `$${n.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
 
const pct = v => `${v.toFixed(1)}%`;
 
/* ── 4. KPI — actualizar tarjetas ───────────────────────── */
function actualizarKPIs(p) {
  const d = DB.periodos[p];
 
  const kpis = [
    { sel: ".kpi-card:nth-child(1) .number", val: fmt(d.ingresos) },
    { sel: ".kpi-card:nth-child(2) .number", val: fmt(d.gananciaNet) },
    { sel: ".kpi-card:nth-child(3) .number", val: pct(d.margen) },
    { sel: ".kpi-card:nth-child(4) .number", val: d.facturas.toString() },
    { sel: ".kpi-card:nth-child(5) .number", val: d.pacientes.toString() }
  ];
 
  kpis.forEach(k => {
    const el = document.querySelector(k.sel);
    if (el) {
      el.style.transition = "opacity 0.3s";
      el.style.opacity = "0";
      setTimeout(() => { el.textContent = k.val; el.style.opacity = "1"; }, 150);
    }
  });
 
  // actualizar chart-summary
  const summaryEls = document.querySelectorAll(".chart-stat strong");
  const vals = [fmt(d.ingresos), fmt(d.gananciaNet), pct(d.margen)];
  summaryEls.forEach((el, i) => { el.textContent = vals[i]; });
}
 
/* ── 5. TABLA: Estado de Resultados ─────────────────────── */
function renderTablaResultados(p) {
  const body = document.getElementById("resultadosBody");
  if (!body) return;
  body.innerHTML = "";
 
  DB.periodos[p].resultados.forEach(item => {
    const tr = document.createElement("tr");
    if (item.isTotal) tr.classList.add("row-total");
 
    const isNeg = item.actual < 0;
    const varClass = item.trend === "up" ? "var-up" : "var-down";
    const icon = item.trend === "up"
      ? '<i class="fa-solid fa-arrow-up"></i>'
      : '<i class="fa-solid fa-arrow-down"></i>';
 
    tr.innerHTML = `
      <td class="concept-td ${item.isTotal ? 'fw-bold' : ''}">${item.concepto}</td>
      <td class="${item.isTotal ? 'fw-bold' : ''} ${isNeg ? 'text-red' : ''}">${fmt(item.actual)}</td>
      <td class="${item.isTotal ? 'fw-bold' : ''}">${fmt(item.anterior)}</td>
      <td class="${varClass}">${item.var} ${icon}</td>
    `;
    body.appendChild(tr);
  });
}
 
/* ── 6. TABLA: Actividad del Sistema ──────────────────────── */
function renderTablaActividad(limit = 5) {
  const body = document.getElementById("actividadBody");
  if (!body) return;
  body.innerHTML = "";
 
  const lista = ACTIVIDADES.slice(0, limit);
  lista.forEach(item => {
    const tr = document.createElement("tr");
    const stClass = item.estado === "Éxito" ? "st-success" : "st-warning";
    tr.innerHTML = `
      <td style="color:var(--text-muted);">${item.fecha}</td>
      <td style="font-weight:500;color:#1e293b;">${item.evento}</td>
      <td>${item.detalle}</td>
      <td><span class="status-pill ${stClass}">${item.estado}</span></td>
    `;
    body.appendChild(tr);
  });
}
 
/* ── 7. GRÁFICO DE LÍNEAS (SVG interactivo) ──────────────── */
function renderLineChart(p) {
  const wrapper = document.querySelector(".chart-wrapper");
  if (!wrapper) return;
 
  const datos = DB.periodos[p].dias;
  const W = 600, H = 200, PAD = 10;
 
  // calcular escala
  const allVals = datos.flatMap(d => [d.ing, d.egr, d.gan]);
  const maxVal  = Math.max(...allVals);
 
  const scaleY = v => H - PAD - ((v / maxVal) * (H - PAD * 2));
  const scaleX = i => (i / (datos.length - 1)) * (W - 20) + 10;
 
  const puntos = (key) =>
    datos.map((d, i) => `${scaleX(i)},${scaleY(d[key])}`).join(" L ");
 
  // tooltip div (crear si no existe)
  let tooltip = document.getElementById("line-tooltip");
  if (!tooltip) {
    tooltip = document.createElement("div");
    tooltip.id = "line-tooltip";
    tooltip.style.cssText = `
      position:absolute; background:#1e293b; color:#fff;
      padding:8px 12px; border-radius:8px; font-size:12px;
      pointer-events:none; opacity:0; transition:opacity 0.15s;
      box-shadow:0 4px 12px rgba(0,0,0,.25); z-index:100;
      white-space:nowrap; line-height:1.6;
    `;
    wrapper.style.position = "relative";
    wrapper.appendChild(tooltip);
  }
 
  // construir SVG
  const svg = wrapper.querySelector(".line-chart-svg") || document.createElementNS("http://www.w3.org/2000/svg","svg");
  svg.setAttribute("viewBox", `0 0 ${W} ${H}`);
  svg.setAttribute("preserveAspectRatio", "none");
  svg.classList.add("line-chart-svg");
 
  // líneas de cuadrícula
  let gridLines = "";
  [20, 60, 100, 140, 180].forEach(y => {
    gridLines += `<line x1="0" y1="${y}" x2="${W}" y2="${y}" stroke="${y===180?'#e2e8f0':'#f1f5f9'}" stroke-width="1"/>`;
  });
 
  // paths
  const series = [
    { key: "ing", color: "#22c55e", label: "Ingresos" },
    { key: "egr", color: "#ef4444", label: "Egresos" },
    { key: "gan", color: "#3b82f6", label: "Ganancia" }
  ];
 
  let paths = "";
  series.forEach(s => {
    paths += `<path d="M ${puntos(s.key)}" fill="none" stroke="${s.color}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`;
  });
 
  // puntos interactivos (círculos invisibles grandes para hit-area)
  let circles = "";
  datos.forEach((d, i) => {
    const cx = scaleX(i);
    series.forEach(s => {
      const cy = scaleY(d[s.key]);
      circles += `
        <circle cx="${cx}" cy="${cy}" r="4" fill="${s.color}" opacity="0.9"/>
        <circle cx="${cx}" cy="${cy}" r="10" fill="transparent" class="chart-hit"
          data-label="${d.label}" data-ing="${d.ing}" data-egr="${d.egr}" data-gan="${d.gan}" data-x="${cx}" data-y="${cy}"/>
      `;
    });
    // línea vertical fantasma
    circles += `<line x1="${cx}" y1="0" x2="${cx}" y2="${H}" stroke="#cbd5e1" stroke-dasharray="4" stroke-width="1" opacity="0" class="v-line" data-idx="${i}"/>`;
  });
 
  svg.innerHTML = gridLines + paths + circles;
  if (!wrapper.contains(svg)) wrapper.insertBefore(svg, wrapper.querySelector(".chart-xaxis") || null);
 
  // actualizar eje X
  let xAxis = wrapper.querySelector(".chart-xaxis");
  if (!xAxis) { xAxis = document.createElement("div"); xAxis.className = "chart-xaxis"; wrapper.appendChild(xAxis); }
  // mostrar solo algunos labels para no apiñar
  const step = Math.max(1, Math.floor(datos.length / 6));
  const labelsHTML = datos.filter((_, i) => i % step === 0 || i === datos.length - 1)
    .map(d => `<span>${d.label}</span>`).join("");
  xAxis.innerHTML = labelsHTML;
 
  // eventos tooltip en SVG
  svg.querySelectorAll(".chart-hit").forEach(el => {
    el.addEventListener("mousemove", (e) => {
      const rect = wrapper.getBoundingClientRect();
      const svgRect = svg.getBoundingClientRect();
      const scaleFactorX = svgRect.width  / W;
      const scaleFactorY = svgRect.height / H;
      const rawX = parseFloat(el.getAttribute("data-x")) * scaleFactorX;
      const rawY = parseFloat(el.getAttribute("data-y")) * scaleFactorY;
 
      tooltip.innerHTML = `
        <strong style="display:block;margin-bottom:4px;color:#94a3b8;">${el.dataset.label}</strong>
        <span style="color:#22c55e;">● Ingresos: </span><strong>${fmt(+el.dataset.ing)}</strong><br>
        <span style="color:#ef4444;">● Egresos: </span><strong>${fmt(+el.dataset.egr)}</strong><br>
        <span style="color:#3b82f6;">● Ganancia: </span><strong>${fmt(+el.dataset.gan)}</strong>
      `;
      let left = rawX + 14;
      if (left + 160 > svgRect.width) left = rawX - 170;
      tooltip.style.left = left + "px";
      tooltip.style.top  = (rawY - 20) + "px";
      tooltip.style.opacity = "1";
 
      // resaltar línea vertical
      svg.querySelectorAll(".v-line").forEach(l => l.setAttribute("opacity","0"));
    });
    el.addEventListener("mouseleave", () => { tooltip.style.opacity = "0"; });
  });
}
 
/* ── 8. GRÁFICO DE BARRAS (SVG interactivo) ──────────────── */
function crearBarChart() {
  const panel = document.querySelector(".left-col .panel:first-child");
  if (!panel || document.getElementById("bar-chart-section")) return;
 
  // insertar sección nueva antes del panel de Estado de Resultados
  const section = document.createElement("div");
  section.id = "bar-chart-section";
  section.className = "panel";
  section.style.marginBottom = "0";
  section.innerHTML = `
    <div class="panel-header-flex">
      <h2 class="panel-title">Ingresos por Tratamiento</h2>
      <select class="select-sm" id="select-bar-periodo">
        <option value="este-periodo">Este periodo</option>
        <option value="mes-anterior">Mes anterior</option>
        <option value="trimestre">Último trimestre</option>
      </select>
    </div>
    <div id="bar-chart-container" style="width:100%;overflow:hidden;position:relative;"></div>
  `;
 
  const leftCol = document.querySelector(".left-col");
  const segundoPanel = leftCol.querySelectorAll(".panel")[1];
  leftCol.insertBefore(section, segundoPanel);
 
  document.getElementById("select-bar-periodo").addEventListener("change", e => {
    renderBarChart(e.target.value);
  });
}
 
function renderBarChart(p) {
  const container = document.getElementById("bar-chart-container");
  if (!container) return;
 
  const datos   = DB.periodos[p].tratamientos;
  const maxVal  = Math.max(...datos.map(d => d.ingresos));
  const W = 560, H = 180, barW = 60, gap = 20;
  const totalW  = datos.length * (barW + gap) - gap;
  const startX  = (W - totalW) / 2;
 
  const colores  = ["#3b82f6","#22c55e","#f59e0b","#a855f7","#14b8a6","#ef4444"];
 
  // tooltip
  let tooltip = document.getElementById("bar-tooltip");
  if (!tooltip) {
    tooltip = document.createElement("div");
    tooltip.id = "bar-tooltip";
    tooltip.style.cssText = `
      position:absolute; background:#1e293b; color:#fff;
      padding:7px 12px; border-radius:8px; font-size:12px;
      pointer-events:none; opacity:0; transition:opacity 0.15s;
      box-shadow:0 4px 12px rgba(0,0,0,.25); z-index:100; white-space:nowrap;
    `;
    container.style.position = "relative";
    container.appendChild(tooltip);
  }
 
  let bars = "", labels = "";
  datos.forEach((d, i) => {
    const barH  = Math.max(8, ((d.ingresos / maxVal) * (H - 30)));
    const x     = startX + i * (barW + gap);
    const y     = H - barH - 20;
    const color = colores[i % colores.length];
 
    bars += `
      <rect x="${x}" y="${y}" width="${barW}" height="${barH}" rx="6" fill="${color}" opacity="0.85"
        class="bar-rect" data-label="${d.nombre}" data-val="${d.ingresos}" style="cursor:pointer;transition:opacity 0.15s;"/>
      <text x="${x + barW/2}" y="${y - 5}" text-anchor="middle" font-size="10" fill="#475569" font-weight="600">${fmt(d.ingresos).replace('$','$')}</text>
    `;
 
    // label partido en dos líneas si es largo
    const words = d.nombre.split(" ");
    const line1 = words.slice(0,1).join(" ");
    const line2 = words.slice(1).join(" ");
    labels += `
      <text x="${x + barW/2}" y="${H - 4}" text-anchor="middle" font-size="9.5" fill="#64748b">${line1}</text>
      ${line2 ? `<text x="${x + barW/2}" y="${H + 8}" text-anchor="middle" font-size="9.5" fill="#64748b">${line2}</text>` : ''}
    `;
  });
 
  // líneas horizontales de guía
  let guides = "";
  [0.25, 0.5, 0.75, 1].forEach(f => {
    const y = H - 20 - f * (H - 30);
    const val = Math.round(maxVal * f);
    guides += `
      <line x1="0" y1="${y}" x2="${W}" y2="${y}" stroke="#f1f5f9" stroke-width="1"/>
      <text x="4" y="${y - 3}" font-size="9" fill="#94a3b8">$${(val/1000).toFixed(0)}k</text>
    `;
  });
 
  const svgNS = "http://www.w3.org/2000/svg";
  let svg = container.querySelector("svg");
  if (!svg) {
    svg = document.createElementNS(svgNS, "svg");
    svg.style.cssText = "width:100%;overflow:visible;";
    container.insertBefore(svg, tooltip);
  }
  svg.setAttribute("viewBox", `0 0 ${W} ${H + 14}`);
  svg.innerHTML = guides + bars + labels;
 
  // eventos barra
  svg.querySelectorAll(".bar-rect").forEach(el => {
    el.addEventListener("mouseenter", function(e) {
      this.setAttribute("opacity","1");
      const svgRect = svg.getBoundingClientRect();
      const cRect   = container.getBoundingClientRect();
      const elRect  = this.getBoundingClientRect();
      tooltip.innerHTML = `
        <strong>${this.dataset.label}</strong><br>
        <span style="color:#94a3b8;">Ingresos: </span><strong>${fmt(+this.dataset.val)}</strong>
      `;
      let left = elRect.left - cRect.left + elRect.width / 2 - 60;
      tooltip.style.left  = left + "px";
      tooltip.style.top   = (elRect.top - cRect.top - 48) + "px";
      tooltip.style.opacity = "1";
    });
    el.addEventListener("mouseleave", function() {
      this.setAttribute("opacity","0.85");
      tooltip.style.opacity = "0";
    });
  });
}
 
/* ── 9. SELECTOR GLOBAL DE PERIODO ───────────────────────── */
function inicializarSelectores() {
  // botón "Este periodo" → abrir mini-dropdown
  const btnPeriodo = document.querySelector(".btn-outline:not(.btn-export)");
  if (btnPeriodo) {
    // crear dropdown
    const dd = document.createElement("div");
    dd.id = "periodo-dropdown";
    dd.style.cssText = `
      position:absolute; background:#fff; border:1px solid #e2e8f0;
      border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12);
      z-index:200; min-width:210px; padding:6px 0; display:none;
    `;
    ["este-periodo", "mes-anterior", "trimestre"].forEach(key => {
      const opt = document.createElement("div");
      opt.textContent = DB.periodos[key].label;
      opt.dataset.key = key;
      opt.style.cssText = "padding:9px 16px;font-size:13px;cursor:pointer;color:#334155;transition:background 0.15s;";
      opt.addEventListener("mouseenter", () => opt.style.background = "#f8fafc");
      opt.addEventListener("mouseleave", () => opt.style.background = "");
      opt.addEventListener("click", () => {
        periodoActivo = key;
        btnPeriodo.innerHTML = `<i class="fa-regular fa-calendar"></i> ${DB.periodos[key].label.split("(")[0].trim()}`;
        dd.style.display = "none";
        cambiarPeriodo(key);
      });
      dd.appendChild(opt);
    });
    btnPeriodo.parentElement.style.position = "relative";
    btnPeriodo.parentElement.appendChild(dd);
 
    btnPeriodo.addEventListener("click", (e) => {
      e.stopPropagation();
      dd.style.display = dd.style.display === "none" ? "block" : "none";
    });
    document.addEventListener("click", () => { dd.style.display = "none"; });
  }
 
  // select del panel "Resultados Financieros"
  const selPanel = document.querySelector(".panel .select-sm");
  if (selPanel) {
    selPanel.innerHTML = `
      <option value="este-periodo">Este periodo</option>
      <option value="mes-anterior">Mes anterior</option>
      <option value="trimestre">Último trimestre</option>
    `;
    selPanel.addEventListener("change", e => cambiarPeriodo(e.target.value));
  }
}
 
function cambiarPeriodo(p) {
  periodoActivo = p;
  actualizarKPIs(p);
  renderTablaResultados(p);
  renderLineChart(p);
  renderBarChart(p);
  actualizarBottomStats(p);
 
  // sincronizar selects
  document.querySelectorAll(".select-sm").forEach(s => { if(s.querySelector(`option[value="${p}"]`)) s.value = p; });
  const selBar = document.getElementById("select-bar-periodo");
  if (selBar) selBar.value = p;
}
 
/* ── 10. STATS INFERIORES DEL GRÁFICO ───────────────────── */
function actualizarBottomStats(p) {
  const d  = DB.periodos[p].dias;
  const maxIng = d.reduce((a, b) => a.ing > b.ing ? a : b);
  const maxEgr = d.reduce((a, b) => a.egr > b.egr ? a : b);
  const totalIng = d.reduce((s, b) => s + b.ing, 0);
  const totalEgr = d.reduce((s, b) => s + b.egr, 0);
  const avgIng = totalIng / d.length;
  const avgEgr = totalEgr / d.length;
 
  const boxes = document.querySelectorAll(".stat-box strong");
  if (boxes.length >= 4) {
    boxes[0].textContent = fmt(Math.round(avgIng));
    boxes[1].textContent = fmt(Math.round(avgEgr));
    boxes[2].innerHTML   = `${maxIng.label} <small>(${fmt(maxIng.ing)})</small>`;
    boxes[3].innerHTML   = `${maxEgr.label} <small>(${fmt(maxEgr.egr)})</small>`;
  }
}
 
/* ── 11. BOTÓN EXPORTAR ──────────────────────────────────── */
function inicializarExportar() {
  const btn = document.querySelector(".btn-export");
  if (!btn) return;
 
  btn.addEventListener("click", () => {
    const d = DB.periodos[periodoActivo];
    const rows = [
      ["REPORTE — ODONTO ESTÉTICA"],
      ["Periodo:", DB.periodos[periodoActivo].label],
      [""],
      ["KPIs"],
      ["Ingresos Totales",    fmt(d.ingresos)],
      ["Ganancia Neta",       fmt(d.gananciaNet)],
      ["Margen de Ganancia",  pct(d.margen)],
      ["Total Facturas",      d.facturas],
      ["Pacientes Atendidos", d.pacientes],
      [""],
      ["ESTADO DE RESULTADOS"],
      ["Concepto", "Este Periodo", "Periodo Anterior", "Variación"],
      ...d.resultados.map(r => [r.concepto, fmt(r.actual), fmt(r.anterior), r.var]),
      [""],
      ["INGRESOS POR TRATAMIENTO"],
      ["Tratamiento", "Ingresos"],
      ...d.tratamientos.map(t => [t.nombre, fmt(t.ingresos)])
    ];
 
    const csv = rows.map(r => r.map(c => `"${c}"`).join(",")).join("\n");
    const blob = new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8;" });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement("a");
    a.href = url;
    a.download = `reporte_odonto_${periodoActivo}_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
 
    // feedback visual
    btn.innerHTML = '<i class="fa-solid fa-check"></i> ¡Exportado!';
    btn.style.background = "#22c55e";
    setTimeout(() => {
      btn.innerHTML = '<i class="fa-solid fa-arrow-up-from-bracket"></i> Exportar';
      btn.style.background = "";
    }, 2000);
  });
}
 
/* ── 12. LINKS "Ver detalle" de KPIs ────────────────────── */
function inicializarKPILinks() {
  const modal = document.createElement("div");
  modal.id = "kpi-modal";
  modal.style.cssText = `
    position:fixed; inset:0; background:rgba(15,23,42,.45); z-index:500;
    display:none; align-items:center; justify-content:center;
    backdrop-filter:blur(3px);
  `;
  modal.innerHTML = `
    <div style="background:#fff;border-radius:16px;padding:32px;min-width:340px;max-width:440px;
                box-shadow:0 20px 60px rgba(0,0,0,.18);position:relative;">
      <button id="modal-close" style="position:absolute;top:12px;right:16px;background:none;
        border:none;font-size:20px;cursor:pointer;color:#64748b;">×</button>
      <h3 id="modal-title" style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:16px;"></h3>
      <div id="modal-body"></div>
    </div>
  `;
  document.body.appendChild(modal);
 
  modal.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });
  document.getElementById("modal-close").addEventListener("click", () => { modal.style.display = "none"; });
 
  const detalles = [
    {
      titulo: "Detalle — Ingresos Totales",
      fn: p => {
        const d = DB.periodos[p];
        return d.tratamientos.map(t => `
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
            <span style="color:#334155;">${t.nombre}</span>
            <strong>${fmt(t.ingresos)}</strong>
          </div>`).join("") +
          `<div style="display:flex;justify-content:space-between;padding:10px 0 0;font-size:14px;">
            <strong>Total</strong><strong style="color:var(--c-green);">${fmt(d.ingresos)}</strong>
          </div>`;
      }
    },
    {
      titulo: "Detalle — Ganancia Neta",
      fn: p => {
        const d = DB.periodos[p];
        return `
          <div style="font-size:13px;line-height:2;">
            <div style="display:flex;justify-content:space-between;"><span>Ingresos totales:</span><strong>${fmt(d.ingresos)}</strong></div>
            <div style="display:flex;justify-content:space-between;color:#ef4444;"><span>Costo materiales:</span><span>-$6,150.00</span></div>
            <div style="display:flex;justify-content:space-between;color:#ef4444;"><span>Gastos operativos:</span><span>-$6,330.00</span></div>
            <div style="display:flex;justify-content:space-between;color:#ef4444;"><span>Descuentos:</span><span>-$730.00</span></div>
            <hr style="margin:8px 0;border-color:#e2e8f0;">
            <div style="display:flex;justify-content:space-between;"><strong>Ganancia Neta:</strong><strong style="color:#3b82f6;">${fmt(d.gananciaNet)}</strong></div>
          </div>`;
      }
    },
    {
      titulo: "Detalle — Margen de Ganancia",
      fn: p => {
        const d = DB.periodos[p];
        const bar = d.margen;
        return `
          <div style="font-size:13px;">
            <p style="color:#64748b;margin-bottom:12px;">El margen indica qué porcentaje de los ingresos se convierte en ganancia neta.</p>
            <div style="background:#f1f5f9;border-radius:8px;height:14px;overflow:hidden;margin-bottom:10px;">
              <div style="background:linear-gradient(90deg,#22c55e,#3b82f6);height:100%;width:${bar}%;border-radius:8px;transition:width 0.6s;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;">
              <span>Margen actual</span><span style="color:#22c55e;">${pct(bar)}</span>
            </div>
          </div>`;
      }
    },
    {
      titulo: "Detalle — Facturas",
      fn: p => {
        const d = DB.periodos[p];
        return `
          <div style="font-size:13px;line-height:2;">
            <div style="display:flex;justify-content:space-between;"><span>Facturas emitidas:</span><strong>${d.facturas}</strong></div>
            <div style="display:flex;justify-content:space-between;"><span>Valor promedio:</span><strong>${fmt(Math.round(d.ingresos / d.facturas))}</strong></div>
            <div style="display:flex;justify-content:space-between;color:#22c55e;"><span>Estado:</span><strong>Procesadas correctamente</strong></div>
          </div>`;
      }
    },
    {
      titulo: "Detalle — Pacientes Atendidos",
      fn: p => {
        const d = DB.periodos[p];
        return `
          <div style="font-size:13px;line-height:2;">
            <div style="display:flex;justify-content:space-between;"><span>Total pacientes:</span><strong>${d.pacientes}</strong></div>
            <div style="display:flex;justify-content:space-between;"><span>Ingreso por paciente:</span><strong>${fmt(Math.round(d.ingresos / d.pacientes))}</strong></div>
            <div style="display:flex;justify-content:space-between;"><span>Facturas por paciente:</span><strong>${(d.facturas / d.pacientes).toFixed(2)}</strong></div>
          </div>`;
      }
    }
  ];
 
  document.querySelectorAll(".kpi-link").forEach((link, i) => {
    link.addEventListener("click", e => {
      e.preventDefault();
      const det = detalles[i];
      if (!det) return;
      document.getElementById("modal-title").textContent = det.titulo;
      document.getElementById("modal-body").innerHTML    = det.fn(periodoActivo);
      modal.style.display = "flex";
    });
  });
}
 
/* ── 13. LINK "Ver todas" de actividad ─────────────────────── */
function inicializarVerTodas() {
  const links = document.querySelectorAll(".right-col .link-sm");
  links.forEach(link => {
    if (link.textContent.trim() === "Ver todas") {
      link.addEventListener("click", e => {
        e.preventDefault();
        const body = document.getElementById("actividadBody");
        const mostrandoTodas = link.dataset.expanded === "1";
        if (mostrandoTodas) {
          renderTablaActividad(5);
          link.textContent = "Ver todas";
          link.dataset.expanded = "0";
        } else {
          renderTablaActividad(ACTIVIDADES.length);
          link.textContent = "Ver menos";
          link.dataset.expanded = "1";
        }
      });
    }
  });
}
 
/* ── 14. "Ver reporte completo" ──────────────────────────── */
function inicializarVerReporte() {
  const link = document.querySelector(".left-col .link-sm");
  if (!link) return;
  link.addEventListener("click", e => {
    e.preventDefault();
    const section = document.getElementById("bar-chart-section");
    if (section) {
      section.scrollIntoView({ behavior: "smooth", block: "start" });
      section.style.boxShadow = "0 0 0 3px #3b82f6";
      setTimeout(() => { section.style.boxShadow = ""; }, 1500);
    }
  });
}
 
/* ── 15. ACTUALIZAR UPTIME (animación en vivo) ──────────── */
function inicializarUptime() {
  const el = document.querySelector(".status-right strong");
  if (!el) return;
  // simular pequeña variación visual
  let base = 99.9;
  setInterval(() => {
    base = 99.8 + Math.random() * 0.15;
    el.textContent = base.toFixed(1) + "%";
  }, 8000);
}
 
/* ── 16. CSS ADICIONAL INYECTADO ─────────────────────────── */
function inyectarCSS() {
  const style = document.createElement("style");
  style.textContent = `
    .row-total td { background:#eff6ff !important; border-top: 2px solid #bfdbfe !important; }
    .text-red { color: #ef4444 !important; }
    #periodo-dropdown { animation: fadeInDown 0.15s ease; }
    @keyframes fadeInDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
    #kpi-modal { animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    #kpi-modal > div { animation: scaleIn 0.2s ease; }
    @keyframes scaleIn { from { transform:scale(0.95); opacity:0; } to { transform:scale(1); opacity:1; } }
    .bar-rect:hover { filter: brightness(1.1); }
    .kpi-card { cursor: default; }
    .kpi-link { cursor: pointer; }
    #bar-chart-section { transition: box-shadow 0.4s; }
  `;
  document.head.appendChild(style);
}
 
/* ── 17. INIT ─────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
  inyectarCSS();
  renderTablaResultados(periodoActivo);
  renderTablaActividad(5);
  inicializarSelectores();
  renderLineChart(periodoActivo);
  crearBarChart();
  renderBarChart(periodoActivo);
  actualizarBottomStats(periodoActivo);
  inicializarExportar();
  inicializarKPILinks();
  inicializarVerTodas();
  inicializarVerReporte();
  inicializarUptime();
});
 