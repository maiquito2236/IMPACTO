/* =========================================================
   REPORTES — reportes.js  (conectado a BD real)
   Odonto Estética — Administrador
   ========================================================= */

/* ── 1. ESTADO GLOBAL ─────────────────────────────────────── */
let periodoActivo = "este-mes";
let datosActuales = null;

const API_URL = "/LOGIN_ORIGINAL/admin/reportes/api";

/* ── 2. HELPERS ──────────────────────────────────────────── */
const fmt = n => n < 0
  ? `-$${Math.abs(n).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
  : `$${n.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;

const pct = v => `${v.toFixed(1)}%`;

function trendHTML(valor, sufijo = '% vs periodo anterior') {
  if (valor === 0) return `<span style="color:var(--text-muted)">Sin cambios</span>`;
  const icon = valor > 0 ? 'fa-arrow-up' : 'fa-arrow-down';
  const color = valor > 0 ? 'green' : 'red';
  const signo = valor > 0 ? '+' : '';
  return `<i class="fa-solid ${icon}"></i> ${signo}${valor}${sufijo}`;
}

/* ── 3. FETCH DATA ───────────────────────────────────────── */
async function cargarDatos(periodo) {
  const overlay = document.getElementById('loading-overlay');
  
  try {
    overlay.style.display = 'flex';

    const res = await fetch(`${API_URL}&periodo=${periodo}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    
    const json = await res.json();
    if (!json.ok) throw new Error(json.mensaje || 'Error desconocido');

    datosActuales = json.data;
    renderTodo();

  } catch (err) {
    console.error('Error cargando datos:', err);
    mostrarErrorGeneral(err.message);
  } finally {
    overlay.style.display = 'none';
  }
}

function mostrarErrorGeneral(msg) {
  const grid = document.getElementById('kpi-grid');
  if (grid) {
    // Show a simple error toast
    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed; bottom:24px; right:24px; background:#fef2f2; color:#dc2626;
      border:1px solid #fca5a5; padding:14px 20px; border-radius:12px;
      font-size:13px; font-weight:600; z-index:1000; box-shadow:0 8px 24px rgba(0,0,0,.12);
      animation: slideDown 0.3s ease;
    `;
    toast.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
  }
}

/* ── 4. RENDER TODO ──────────────────────────────────────── */
function renderTodo() {
  if (!datosActuales) return;
  actualizarKPIs();
  actualizarChartSummary();
  renderLineChart();
  renderBarChart();
  renderTablaResultados();
  actualizarBottomStats();
}

/* ── 5. KPI — actualizar tarjetas ────────────────────────── */
function actualizarKPIs() {
  const d = datosActuales;

  const kpis = [
    { id: 'kpi-val-ingresos',  val: fmt(d.ingresos),          trendId: 'kpi-trend-ingresos',  trendVal: d.var_ingresos, cls: d.var_ingresos >= 0 ? 'green' : 'red' },
    { id: 'kpi-val-ganancia',  val: fmt(d.ganancia_neta),      trendId: 'kpi-trend-ganancia',  trendVal: d.var_ganancia,  cls: d.var_ganancia >= 0 ? 'green' : 'red' },
    { id: 'kpi-val-margen',    val: pct(d.margen),             trendId: 'kpi-trend-margen',    trendVal: d.var_margen,    cls: d.var_margen >= 0 ? 'green' : 'red', sufijo: '% vs anterior' },
    { id: 'kpi-val-facturas',  val: d.total_facturas.toString(), trendId: 'kpi-trend-facturas',  trendVal: d.var_facturas,  cls: 'text-muted' },
    { id: 'kpi-val-pacientes', val: d.pacientes_atendidos.toString(), trendId: 'kpi-trend-pacientes', trendVal: d.var_pacientes, cls: 'text-muted' }
  ];

  kpis.forEach(k => {
    const el = document.getElementById(k.id);
    const trendEl = document.getElementById(k.trendId);
    if (el) {
      el.style.opacity = '0';
      el.style.transition = 'opacity 0.3s';
      setTimeout(() => {
        el.textContent = k.val;
        el.style.opacity = '1';
      }, 150);
    }
    if (trendEl) {
      trendEl.className = `trend ${k.cls}`;
      trendEl.innerHTML = trendHTML(k.trendVal, k.sufijo || '% vs periodo anterior');
    }
  });
}

/* ── 6. CHART SUMMARY ────────────────────────────────────── */
function actualizarChartSummary() {
  const d = datosActuales;
  const el = (id, val) => { const e = document.getElementById(id); if(e) e.textContent = val; };
  el('summary-ingresos', fmt(d.ingresos));
  el('summary-egresos', fmt(d.egresos));
  el('summary-ganancia', fmt(d.ganancia_neta));
  el('summary-margen', pct(d.margen));
}

/* ── 7. GRÁFICO DE LÍNEAS (SVG interactivo) ──────────────── */
function renderLineChart() {
  const wrapper = document.getElementById('line-chart-wrapper');
  if (!wrapper) return;

  const datos = datosActuales.datos_diarios;
  
  if (!datos || datos.length === 0) {
    wrapper.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:200px;color:var(--text-muted);font-size:13px;">No hay datos para graficar</div>';
    return;
  }

  const W = 600, H = 200, PAD = 15;

  const allVals = datos.flatMap(d => [d.ing, d.egr, Math.abs(d.gan)]);
  const maxVal  = Math.max(...allVals, 1);

  const scaleY = v => H - PAD - ((v / maxVal) * (H - PAD * 2));
  const scaleX = i => datos.length === 1 ? W / 2 : (i / (datos.length - 1)) * (W - 30) + 15;

  const puntos = (key) =>
    datos.map((d, i) => `${scaleX(i)},${scaleY(Math.max(0, d[key]))}`).join(" L ");

  // tooltip
  let tooltip = document.getElementById("line-tooltip");
  if (!tooltip) {
    tooltip = document.createElement("div");
    tooltip.id = "line-tooltip";
    tooltip.style.cssText = `
      position:absolute; background:#1e293b; color:#fff;
      padding:10px 14px; border-radius:10px; font-size:12px;
      pointer-events:none; opacity:0; transition:opacity 0.15s;
      box-shadow:0 8px 24px rgba(0,0,0,.3); z-index:100;
      white-space:nowrap; line-height:1.7; font-weight:500;
    `;
    wrapper.appendChild(tooltip);
  }

  let svg = wrapper.querySelector(".line-chart-svg");
  if (!svg) {
    svg = document.createElementNS("http://www.w3.org/2000/svg","svg");
    svg.classList.add("line-chart-svg");
    wrapper.insertBefore(svg, wrapper.querySelector(".chart-xaxis"));
  }
  svg.setAttribute("viewBox", `0 0 ${W} ${H}`);
  svg.setAttribute("preserveAspectRatio", "none");

  // grid
  let gridLines = "";
  [20, 60, 100, 140, 180].forEach(y => {
    gridLines += `<line x1="0" y1="${y}" x2="${W}" y2="${y}" stroke="${y===180?'#e2e8f0':'#f1f5f9'}" stroke-width="1"/>`;
  });

  // gradient fills under lines
  const gradients = `
    <defs>
      <linearGradient id="gradGreen" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#10b981" stop-opacity="0.15"/>
        <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
      </linearGradient>
      <linearGradient id="gradRed" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.1"/>
        <stop offset="100%" stop-color="#ef4444" stop-opacity="0"/>
      </linearGradient>
      <linearGradient id="gradBlue" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.12"/>
        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
      </linearGradient>
    </defs>
  `;

  const series = [
    { key: "ing", color: "#10b981", label: "Ingresos", grad: "gradGreen" },
    { key: "egr", color: "#ef4444", label: "Egresos", grad: "gradRed" },
    { key: "gan", color: "#3b82f6", label: "Ganancia", grad: "gradBlue" }
  ];

  let paths = "";
  series.forEach(s => {
    const pts = puntos(s.key);
    paths += `<path d="M ${pts}" fill="none" stroke="${s.color}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`;
    // area fill
    const firstX = scaleX(0);
    const lastX = scaleX(datos.length - 1);
    paths += `<path d="M ${pts} L ${lastX},${H - PAD} L ${firstX},${H - PAD} Z" fill="url(#${s.grad})"/>`;
  });

  // interactive circles
  let circles = "";
  datos.forEach((d, i) => {
    const cx = scaleX(i);
    series.forEach(s => {
      const cy = scaleY(Math.max(0, d[s.key]));
      circles += `
        <circle cx="${cx}" cy="${cy}" r="4" fill="${s.color}" opacity="0.9"/>
        <circle cx="${cx}" cy="${cy}" r="12" fill="transparent" class="chart-hit"
          data-label="${d.label}" data-ing="${d.ing}" data-egr="${d.egr}" data-gan="${d.gan}" data-x="${cx}" data-y="${cy}"/>
      `;
    });
  });

  svg.innerHTML = gradients + gridLines + paths + circles;

  // X axis
  let xAxis = wrapper.querySelector(".chart-xaxis");
  if (!xAxis) { xAxis = document.createElement("div"); xAxis.className = "chart-xaxis"; wrapper.appendChild(xAxis); }
  const step = Math.max(1, Math.floor(datos.length / 7));
  const labelsHTML = datos.filter((_, i) => i % step === 0 || i === datos.length - 1)
    .map(d => `<span>${d.label}</span>`).join("");
  xAxis.innerHTML = labelsHTML;

  // tooltip events
  svg.querySelectorAll(".chart-hit").forEach(el => {
    el.addEventListener("mousemove", () => {
      const svgRect = svg.getBoundingClientRect();
      const scaleFactorX = svgRect.width / W;
      const scaleFactorY = svgRect.height / H;
      const rawX = parseFloat(el.getAttribute("data-x")) * scaleFactorX;
      const rawY = parseFloat(el.getAttribute("data-y")) * scaleFactorY;

      tooltip.innerHTML = `
        <strong style="display:block;margin-bottom:4px;color:#94a3b8;">${el.dataset.label}</strong>
        <span style="color:#10b981;">● Ingresos: </span><strong>${fmt(+el.dataset.ing)}</strong><br>
        <span style="color:#ef4444;">● Egresos: </span><strong>${fmt(+el.dataset.egr)}</strong><br>
        <span style="color:#3b82f6;">● Ganancia: </span><strong>${fmt(+el.dataset.gan)}</strong>
      `;
      let left = rawX + 16;
      if (left + 180 > svgRect.width) left = rawX - 190;
      tooltip.style.left = left + "px";
      tooltip.style.top  = (rawY - 20) + "px";
      tooltip.style.opacity = "1";
    });
    el.addEventListener("mouseleave", () => { tooltip.style.opacity = "0"; });
  });
}

/* ── 8. GRÁFICO DE BARRAS ────────────────────────────────── */
function renderBarChart() {
  const container = document.getElementById("bar-chart-container");
  const emptyState = document.getElementById("bar-empty");
  if (!container) return;

  const datos = datosActuales.ingresos_por_tratamiento;
  
  if (!datos || datos.length === 0) {
    container.style.display = "none";
    if (emptyState) emptyState.style.display = "flex";
    return;
  }
  
  container.style.display = "block";
  if (emptyState) emptyState.style.display = "none";

  const maxVal  = Math.max(...datos.map(d => +d.ingresos), 1);
  const W = 960, H = 340, barW = Math.min(80, (W - 40) / datos.length - 16);
  const gap = 24;
  const totalW  = datos.length * (barW + gap) - gap;
  const startX  = (W - totalW) / 2;

  const colores = ["#3b82f6","#10b981","#f59e0b","#8b5cf6","#14b8a6","#ef4444","#ec4899","#06b6d4"];

  let tooltip = document.getElementById("bar-tooltip");
  if (!tooltip) {
    tooltip = document.createElement("div");
    tooltip.id = "bar-tooltip";
    tooltip.style.cssText = `
      position:absolute; background:#1e293b; color:#fff;
      padding:8px 14px; border-radius:10px; font-size:12px;
      pointer-events:none; opacity:0; transition:opacity 0.15s;
      box-shadow:0 8px 24px rgba(0,0,0,.3); z-index:100; white-space:nowrap; font-weight:500;
    `;
    container.style.position = "relative";
    container.appendChild(tooltip);
  }

  const chartHeight = H - 120; // Reserve 120px at bottom for rotated labels
  
  let bars = "", labels = "";
  datos.forEach((d, i) => {
    const barH  = Math.max(8, ((+d.ingresos / maxVal) * chartHeight));
    const x     = startX + i * (barW + gap);
    const y     = chartHeight + 20 - barH; // 20px top margin
    const color = colores[i % colores.length];

    bars += `
      <rect x="${x}" y="${y}" width="${barW}" height="${barH}" rx="6" fill="${color}" opacity="0.85"
        class="bar-rect" data-label="${d.nombre}" data-val="${d.ingresos}" style="cursor:pointer;transition:all 0.2s;"/>
      <text x="${x + barW/2}" y="${y - 6}" text-anchor="middle" font-size="11" fill="#475569" font-weight="700" font-family="Inter, sans-serif">${fmt(+d.ingresos)}</text>
    `;

    // Se han ocultado los nombres debajo de las barras a petición del usuario
    // Ya que se muestran al poner el cursor encima (tooltip)
  });

  // Guide lines
  let guides = "";
  [0.25, 0.5, 0.75, 1].forEach(f => {
    const y = chartHeight + 20 - f * chartHeight;
    const val = Math.round(maxVal * f);
    guides += `
      <line x1="0" y1="${y}" x2="${W}" y2="${y}" stroke="#f1f5f9" stroke-width="1"/>
      <text x="4" y="${y - 3}" font-size="9" fill="#94a3b8" font-family="Inter, sans-serif">${fmt(val)}</text>
    `;
  });

  let svg = container.querySelector("svg");
  if (!svg) {
    svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.style.cssText = "width:100%;overflow:visible;";
    container.insertBefore(svg, tooltip);
  }
  svg.setAttribute("viewBox", `0 0 ${W} ${H}`);
  svg.innerHTML = guides + bars + labels;

  // Bar hover events
  svg.querySelectorAll(".bar-rect").forEach(el => {
    el.addEventListener("mouseenter", function(e) {
      this.setAttribute("opacity","1");
      this.style.filter = "brightness(1.1)";
      const cRect = container.getBoundingClientRect();
      const elRect = this.getBoundingClientRect();
      tooltip.innerHTML = `
        <strong>${this.dataset.label}</strong><br>
        <span style="color:#94a3b8;">Ingresos: </span><strong>${fmt(+this.dataset.val)}</strong>
      `;
      let left = elRect.left - cRect.left + elRect.width / 2 - 70;
      let topPos = elRect.top - cRect.top - 50;
      
      // Si el tooltip se sale por arriba, lo mostramos un poco más abajo (dentro de la barra)
      if (topPos < 0) {
          topPos = elRect.top - cRect.top + 20;
      }
      
      tooltip.style.left  = left + "px";
      tooltip.style.top   = topPos + "px";
      tooltip.style.opacity = "1";
    });
    el.addEventListener("mouseleave", function() {
      this.setAttribute("opacity","0.85");
      this.style.filter = "";
      tooltip.style.opacity = "0";
    });
  });
}

/* ── 9. TABLA: Estado de Resultados ──────────────────────── */
function renderTablaResultados() {
  const body = document.getElementById("resultadosBody");
  const emptyState = document.getElementById("table-empty");
  if (!body) return;

  const resultados = datosActuales.estado_resultados;
  
  if (!resultados || resultados.length === 0) {
    body.innerHTML = "";
    if (emptyState) emptyState.style.display = "flex";
    return;
  }
  
  if (emptyState) emptyState.style.display = "none";
  body.innerHTML = "";

  resultados.forEach(item => {
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

/* ── 10. STATS INFERIORES DEL GRÁFICO ────────────────────── */
function actualizarBottomStats() {
  const datos = datosActuales.datos_diarios;
  if (!datos || datos.length === 0) return;

  const maxIng = datos.reduce((a, b) => a.ing > b.ing ? a : b);
  const maxEgr = datos.reduce((a, b) => a.egr > b.egr ? a : b);
  const totalIng = datos.reduce((s, b) => s + b.ing, 0);
  const totalEgr = datos.reduce((s, b) => s + b.egr, 0);
  const avgIng = totalIng / datos.length;
  const avgEgr = totalEgr / datos.length;

  const el = (id, val) => { const e = document.getElementById(id); if(e) e.textContent = val; };
  el('stat-avg-ing', fmt(Math.round(avgIng)));
  el('stat-avg-egr', fmt(Math.round(avgEgr)));

  const maxIngEl = document.getElementById('stat-max-ing');
  const maxEgrEl = document.getElementById('stat-max-egr');
  if (maxIngEl) maxIngEl.innerHTML = `${maxIng.label} <small>(${fmt(maxIng.ing)})</small>`;
  if (maxEgrEl) maxEgrEl.innerHTML = `${maxEgr.label} <small>(${fmt(maxEgr.egr)})</small>`;
}

/* ── 11. SELECTOR GLOBAL DE PERIODO ──────────────────────── */
function inicializarSelectores() {
  const btnPeriodo = document.getElementById("btn-periodo");
  if (!btnPeriodo) return;

  const dd = document.createElement("div");
  dd.id = "periodo-dropdown";
  dd.style.cssText = `
    position:absolute; background:#fff; border:1px solid #e2e8f0;
    border-radius:12px; box-shadow:0 12px 36px rgba(0,0,0,.12);
    z-index:200; min-width:220px; padding:6px 0; display:none;
    backdrop-filter:blur(8px);
  `;

  const periodos = [
    { key: "este-mes", label: "Este mes" },
    { key: "mes-anterior", label: "Mes anterior" },
    { key: "trimestre", label: "Último trimestre" }
  ];

  periodos.forEach(p => {
    const opt = document.createElement("div");
    opt.textContent = p.label;
    opt.dataset.key = p.key;
    opt.style.cssText = "padding:10px 18px;font-size:13px;cursor:pointer;color:#334155;transition:all 0.15s;font-weight:500;border-radius:6px;margin:2px 6px;";
    opt.addEventListener("mouseenter", () => { opt.style.background = "#f1f5f9"; });
    opt.addEventListener("mouseleave", () => { opt.style.background = ""; });
    opt.addEventListener("click", () => {
      periodoActivo = p.key;
      btnPeriodo.innerHTML = `<i class="fa-regular fa-calendar"></i> ${p.label}`;
      dd.style.display = "none";
      sincronizarSelects(p.key);
      cargarDatos(p.key);
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

  // Select change handlers
  document.querySelectorAll(".select-sm").forEach(sel => {
    sel.addEventListener("change", e => {
      periodoActivo = e.target.value;
      sincronizarSelects(e.target.value);
      cargarDatos(e.target.value);
      
      const label = periodos.find(p => p.key === e.target.value)?.label || "Este mes";
      btnPeriodo.innerHTML = `<i class="fa-regular fa-calendar"></i> ${label}`;
    });
  });
}

function sincronizarSelects(periodo) {
  document.querySelectorAll(".select-sm").forEach(s => {
    if (s.querySelector(`option[value="${periodo}"]`)) s.value = periodo;
  });
}

/* ── 12. EXPORTAR Y MODAL ──────────────────────────────────── */
function inicializarExportar() {
  const btnExportar = document.getElementById("btn-exportar");
  const modal = document.getElementById("modalExportReportes");
  const btnClose = document.getElementById("closeModalExportReportes");
  const btnPdf = document.getElementById("btn-export-pdf");
  const btnExcel = document.getElementById("btn-export-excel");

  if (!btnExportar || !modal) return;

  // Abrir Modal
  btnExportar.addEventListener("click", () => {
    modal.style.display = "flex";
  });

  // Cerrar Modal
  const cerrarModal = () => { modal.style.display = "none"; };
  if (btnClose) btnClose.addEventListener("click", cerrarModal);
  modal.addEventListener("click", e => { if (e.target === modal) cerrarModal(); });

  // Exportar PDF
  if (btnPdf) {
    btnPdf.addEventListener("click", () => {
      exportarPDF();
      cerrarModal();
      btnExportar.innerHTML = '<i class="fa-solid fa-check"></i> ¡Exportado a PDF!';
      btnExportar.style.background = "linear-gradient(135deg, #ef4444, #dc2626)";
      btnExportar.style.color = "#fff";
      btnExportar.style.borderColor = "transparent";
      setTimeout(() => {
        btnExportar.innerHTML = '<i class="fa-solid fa-arrow-up-from-bracket"></i> Exportar';
        btnExportar.style.background = "";
        btnExportar.style.color = "";
        btnExportar.style.borderColor = "";
      }, 2000);
    });
  }

  // Exportar Excel
  if (btnExcel) {
    btnExcel.addEventListener("click", () => {
      exportarExcel();
      cerrarModal();
      btnExportar.innerHTML = '<i class="fa-solid fa-check"></i> ¡Exportado a Excel!';
      btnExportar.style.background = "linear-gradient(135deg, #10b981, #059669)";
      btnExportar.style.color = "#fff";
      btnExportar.style.borderColor = "transparent";
      setTimeout(() => {
        btnExportar.innerHTML = '<i class="fa-solid fa-arrow-up-from-bracket"></i> Exportar';
        btnExportar.style.background = "";
        btnExportar.style.color = "";
        btnExportar.style.borderColor = "";
      }, 2000);
    });
  }
}

async function exportarExcel() {
  if (!datosActuales || !window.ExcelJS) return;
  const d = datosActuales;

  const workbook = new ExcelJS.Workbook();
  workbook.creator = 'Odonto Estética';
  workbook.created = new Date();

  const sheet = workbook.addWorksheet('Reporte Financiero', {
    views: [{ showGridLines: false }] // Clean look
  });

  // Estilos base
  const titleStyle = { font: { name: 'Arial', size: 16, bold: true, color: { argb: 'FFFFFFFF' } }, alignment: { vertical: 'middle', horizontal: 'center' }, fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } } };
  const subtitleStyle = { font: { name: 'Arial', size: 12, italic: true, color: { argb: 'FF64748B' } } };
  const headerStyle = { font: { name: 'Arial', size: 11, bold: true, color: { argb: 'FFFFFFFF' } }, fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF3B82F6' } }, alignment: { vertical: 'middle', horizontal: 'center' }, border: { top: {style:'thin', color: {argb:'FFCBD5E1'}}, bottom: {style:'thin', color: {argb:'FFCBD5E1'}} } };
  const headerStyleVerde = { ...headerStyle, fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10B981' } } };
  const cellStyle = { font: { name: 'Arial', size: 11 }, alignment: { vertical: 'middle' }, border: { bottom: {style:'thin', color: {argb:'FFF1F5F9'}} } };
  const boldStyle = { font: { name: 'Arial', size: 11, bold: true }, alignment: { vertical: 'middle' }, border: { bottom: {style:'thin', color: {argb:'FFF1F5F9'}} } };
  
  const currencyFmt = '"$"#,##0';
  const percentFmt = '0.0%';

  // Definir anchos de columna
  sheet.columns = [
    { width: 45 },
    { width: 22 },
    { width: 22 },
    { width: 22 }
  ];

  // 1. Título principal
  sheet.mergeCells('A1:D2');
  const titleCell = sheet.getCell('A1');
  titleCell.value = 'REPORTE FINANCIERO - ODONTO ESTÉTICA';
  titleCell.style = titleStyle;

  sheet.getCell('A3').value = `Periodo: ${d.periodo_label}`;
  sheet.getCell('A3').style = subtitleStyle;
  sheet.addRow([]);

  // 2. Resumen KPIs
  const kpiTitle = sheet.addRow(['RESUMEN DE INDICADORES (KPIs)']);
  kpiTitle.font = { bold: true, size: 12, color: { argb: 'FF0F172A' } };
  
  const kpiHeader = sheet.addRow(['Indicador', 'Valor', '', '']);
  kpiHeader.eachCell(c => c.style = headerStyle);
  
  const addKpi = (label, val, numFmt = null) => {
    const row = sheet.addRow([label, val]);
    row.eachCell(c => c.style = cellStyle);
    row.getCell(1).font = { bold: true };
    row.getCell(2).alignment = { horizontal: 'right' };
    if (numFmt) row.getCell(2).numFmt = numFmt;
  };

  addKpi('Ingresos Totales', d.ingresos, currencyFmt);
  addKpi('Egresos Totales', d.egresos, currencyFmt);
  addKpi('Ganancia Neta', d.ganancia_neta, currencyFmt);
  addKpi('Margen de Ganancia', d.margen / 100, percentFmt);
  addKpi('Total Facturas', d.total_facturas);
  addKpi('Pacientes Atendidos', d.pacientes_atendidos);
  
  sheet.addRow([]);
  sheet.addRow([]);

  // 3. Estado de Resultados
  const erTitle = sheet.addRow(['ESTADO DE RESULTADOS']);
  erTitle.font = { bold: true, size: 12, color: { argb: 'FF0F172A' } };
  
  const erHeader = sheet.addRow(['Concepto', 'Este Periodo', 'Periodo Anterior', 'Variación']);
  erHeader.eachCell(c => c.style = headerStyle);

  d.estado_resultados.forEach(r => {
    const row = sheet.addRow([
      r.concepto, 
      r.actual, 
      r.anterior, 
      `${r.var} ${r.trend === 'up' ? '(+)' : '(-)'}`
    ]);
    const isTotal = r.isTotal;
    row.eachCell((c, colNum) => {
      c.style = isTotal ? boldStyle : cellStyle;
      if (colNum > 1) c.alignment = { horizontal: 'right' };
      if (colNum === 2 || colNum === 3) c.numFmt = currencyFmt;
    });
  });

  sheet.addRow([]);
  sheet.addRow([]);

  // 4. Ingresos por Tratamiento
  if (d.ingresos_por_tratamiento && d.ingresos_por_tratamiento.length > 0) {
    const itTitle = sheet.addRow(['INGRESOS POR TRATAMIENTO']);
    itTitle.font = { bold: true, size: 12, color: { argb: 'FF0F172A' } };
    
    const itHeader = sheet.addRow(['Tratamiento', 'Ingresos', '', '']);
    sheet.mergeCells(`A${itHeader.number}:C${itHeader.number}`); // merge header
    itHeader.getCell(1).value = 'Tratamiento';
    itHeader.getCell(4).value = 'Ingresos';
    itHeader.eachCell(c => c.style = headerStyleVerde);

    d.ingresos_por_tratamiento.forEach(t => {
      const row = sheet.addRow([t.nombre, '', '', +t.ingresos]);
      sheet.mergeCells(`A${row.number}:C${row.number}`);
      row.eachCell((c, colNum) => {
        c.style = cellStyle;
        if (colNum === 4) {
          c.alignment = { horizontal: 'right' };
          c.numFmt = currencyFmt;
        }
      });
    });
  }

  // Descargar Archivo
  const buffer = await workbook.xlsx.writeBuffer();
  const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `reporte_odonto_${periodoActivo}_${new Date().toISOString().slice(0,10)}.xlsx`;
  a.click();
  URL.revokeObjectURL(url);
}

function exportarPDF() {
  if (!datosActuales) return;
  const d = datosActuales;
  const { jsPDF } = window.jspdf;
  
  const doc = new jsPDF();
  
  // Encabezado
  doc.setFontSize(18);
  doc.setTextColor(15, 23, 42); // slate-900
  doc.text("Reporte Financiero - Odonto Estética", 14, 20);
  
  doc.setFontSize(11);
  doc.setTextColor(100, 116, 139); // slate-500
  doc.text(`Periodo: ${d.periodo_label}`, 14, 28);
  
  // Resumen KPIs
  doc.autoTable({
    startY: 35,
    head: [['Indicador', 'Valor']],
    body: [
      ['Ingresos Totales', fmt(d.ingresos)],
      ['Egresos Totales', fmt(d.egresos)],
      ['Ganancia Neta', fmt(d.ganancia_neta)],
      ['Margen de Ganancia', pct(d.margen)],
      ['Total Facturas', d.total_facturas],
      ['Pacientes Atendidos', d.pacientes_atendidos]
    ],
    theme: 'grid',
    headStyles: { fillColor: [59, 130, 246] }, // blue-500
    styles: { font: 'helvetica', fontSize: 10 }
  });
  
  // Estado de Resultados
  doc.autoTable({
    startY: doc.lastAutoTable.finalY + 15,
    head: [['Concepto', 'Este Periodo', 'Periodo Anterior', 'Variación']],
    body: d.estado_resultados.map(r => [
      r.concepto, 
      fmt(r.actual), 
      fmt(r.anterior), 
      `${r.var} ${r.trend === 'up' ? '(+)' : '(-)'}`
    ]),
    theme: 'grid',
    headStyles: { fillColor: [59, 130, 246] }, // blue-500
    styles: { font: 'helvetica', fontSize: 10 },
    didParseCell: function(data) {
      if (data.row.index >= 0 && d.estado_resultados[data.row.index].isTotal) {
        data.cell.styles.fontStyle = 'bold';
      }
    }
  });
  
  // Ingresos por Tratamiento
  if (d.ingresos_por_tratamiento && d.ingresos_por_tratamiento.length > 0) {
    doc.autoTable({
      startY: doc.lastAutoTable.finalY + 15,
      head: [['Tratamiento', 'Ingresos']],
      body: d.ingresos_por_tratamiento.map(t => [t.nombre, fmt(+t.ingresos)]),
      theme: 'grid',
      headStyles: { fillColor: [16, 185, 129] }, // emerald-500
      styles: { font: 'helvetica', fontSize: 10 }
    });
  }
  
  doc.save(`reporte_odonto_${periodoActivo}_${new Date().toISOString().slice(0,10)}.pdf`);
}

/* ── 13. LINKS "Ver detalle" de KPIs ─────────────────────── */
function inicializarKPILinks() {
  const modal = document.createElement("div");
  modal.id = "kpi-modal";
  modal.style.cssText = `
    position:fixed; inset:0; background:rgba(15,23,42,.5); z-index:500;
    display:none; align-items:center; justify-content:center;
    backdrop-filter:blur(4px);
  `;
  modal.innerHTML = `
    <div style="background:#fff;border-radius:20px;padding:32px;min-width:360px;max-width:460px;
                box-shadow:0 24px 60px rgba(0,0,0,.2);position:relative;">
      <button id="modal-close" style="position:absolute;top:14px;right:18px;background:none;
        border:none;font-size:22px;cursor:pointer;color:#64748b;transition:color 0.2s;">×</button>
      <h3 id="modal-title" style="font-size:17px;font-weight:700;color:#0f172a;margin-bottom:18px;"></h3>
      <div id="modal-body"></div>
    </div>
  `;
  document.body.appendChild(modal);

  modal.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });
  document.getElementById("modal-close").addEventListener("click", () => { modal.style.display = "none"; });

  document.querySelectorAll(".kpi-link").forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault();
      if (!datosActuales) return;

      const kpi = link.dataset.kpi;
      const d = datosActuales;
      let titulo = "", contenido = "";

      switch(kpi) {
        case 'ingresos':
          titulo = "Detalle — Ingresos Totales";
          contenido = d.ingresos_por_tratamiento.map(t => `
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
              <span style="color:#334155;font-weight:500;">${t.nombre}</span>
              <strong>${fmt(+t.ingresos)}</strong>
            </div>`).join("") +
            `<div style="display:flex;justify-content:space-between;padding:12px 0 0;font-size:15px;">
              <strong>Total</strong><strong style="color:var(--c-green);">${fmt(d.ingresos)}</strong>
            </div>`;
          break;
        case 'ganancia':
          titulo = "Detalle — Ganancia Neta";
          contenido = `
            <div style="font-size:13px;line-height:2.2;">
              <div style="display:flex;justify-content:space-between;"><span>Ingresos totales:</span><strong style="color:var(--c-green)">${fmt(d.ingresos)}</strong></div>
              <div style="display:flex;justify-content:space-between;color:#ef4444;"><span>Egresos totales:</span><span>-${fmt(d.egresos)}</span></div>
              <hr style="margin:10px 0;border-color:#e2e8f0;">
              <div style="display:flex;justify-content:space-between;"><strong>Ganancia Neta:</strong><strong style="color:#3b82f6;">${fmt(d.ganancia_neta)}</strong></div>
            </div>`;
          break;
        case 'margen':
          titulo = "Detalle — Margen de Ganancia";
          contenido = `
            <div style="font-size:13px;">
              <p style="color:#64748b;margin-bottom:14px;">El margen indica qué porcentaje de los ingresos se convierte en ganancia neta.</p>
              <div style="background:#f1f5f9;border-radius:10px;height:16px;overflow:hidden;margin-bottom:12px;">
                <div style="background:linear-gradient(90deg,#10b981,#3b82f6);height:100%;width:${d.margen}%;border-radius:10px;transition:width 0.6s;"></div>
              </div>
              <div style="display:flex;justify-content:space-between;font-weight:700;">
                <span>Margen actual</span><span style="color:#10b981;">${pct(d.margen)}</span>
              </div>
              <div style="display:flex;justify-content:space-between;font-weight:500;margin-top:8px;color:var(--text-muted);font-size:12px;">
                <span>Periodo anterior</span><span>${pct(d.margen_anterior)}</span>
              </div>
            </div>`;
          break;
        case 'facturas':
          titulo = "Detalle — Facturas";
          const promFact = d.total_facturas > 0 ? fmt(Math.round(d.ingresos / d.total_facturas)) : '$0';
          contenido = `
            <div style="font-size:13px;line-height:2.2;">
              <div style="display:flex;justify-content:space-between;"><span>Facturas emitidas:</span><strong>${d.total_facturas}</strong></div>
              <div style="display:flex;justify-content:space-between;"><span>Valor promedio:</span><strong>${promFact}</strong></div>
              <div style="display:flex;justify-content:space-between;"><span>Periodo anterior:</span><strong>${d.facturas_anterior}</strong></div>
            </div>`;
          break;
        case 'pacientes':
          titulo = "Detalle — Pacientes Atendidos";
          const ingPac = d.pacientes_atendidos > 0 ? fmt(Math.round(d.ingresos / d.pacientes_atendidos)) : '$0';
          contenido = `
            <div style="font-size:13px;line-height:2.2;">
              <div style="display:flex;justify-content:space-between;"><span>Total pacientes:</span><strong>${d.pacientes_atendidos}</strong></div>
              <div style="display:flex;justify-content:space-between;"><span>Ingreso por paciente:</span><strong>${ingPac}</strong></div>
              <div style="display:flex;justify-content:space-between;"><span>Periodo anterior:</span><strong>${d.pacientes_anterior}</strong></div>
            </div>`;
          break;
      }

      document.getElementById("modal-title").textContent = titulo;
      document.getElementById("modal-body").innerHTML = contenido;
      modal.style.display = "flex";
    });
  });
}

/* ── 14. LINK "Ver reporte completo" ─────────────────────── */
function inicializarVerReporte() {
  const link = document.getElementById("link-ver-reporte");
  if (!link) return;
  link.addEventListener("click", e => {
    e.preventDefault();
    const barPanel = document.querySelector(".panel-bars");
    if (barPanel) {
      barPanel.scrollIntoView({ behavior: "smooth", block: "start" });
      barPanel.style.boxShadow = "0 0 0 3px #3b82f6";
      setTimeout(() => { barPanel.style.boxShadow = ""; }, 1500);
    }
  });
}

/* ── 15. INIT ─────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectores();
  inicializarExportar();
  inicializarKPILinks();
  inicializarVerReporte();

  // Cargar datos iniciales
  cargarDatos(periodoActivo);
});