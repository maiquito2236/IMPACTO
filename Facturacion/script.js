// =====================================================
// SISTEMA DE FACTURACIÓN - ODONTO ESTÉTICA
// Script completamente funcional
// =====================================================
 
// 1. BASE DE DATOS INICIAL
let dbFacturacion = [
    { num: "F-000145", paciente: "Maria Fernanda López", id: "300 123 4567", fecha: "22/05/2026", concepto: "Limpieza Dental", total: 600, pagado: 600, pendiente: 0, estado: "Pagada", metodo: "Efectivo" },
    { num: "F-000144", paciente: "Juan Pablo Ramírez", id: "311 987 6543", fecha: "22/05/2026", concepto: "Resina Compuesta", total: 1200, pagado: 700, pendiente: 500, estado: "Parcial", metodo: "Tarjeta" },
    { num: "F-000143", paciente: "Valentina Torres", id: "320 555 8899", fecha: "22/05/2026", concepto: "Control Ortodoncia", total: 800, pagado: 300, pendiente: 500, estado: "Parcial", metodo: "Transferencia" },
    { num: "F-000142", paciente: "Miguel Ángel Rojas", id: "315 777 2211", fecha: "22/05/2026", concepto: "Ortodoncia Inicial", total: 2500, pagado: 1000, pendiente: 1500, estado: "Pendiente", metodo: "Efectivo" },
    { num: "F-000141", paciente: "Ana Sofia Martínez", id: "301 654 9870", fecha: "21/05/2026", concepto: "Blanqueamiento", total: 900, pagado: 900, pendiente: 0, estado: "Pagada", metodo: "Tarjeta" }
];
 
let dbAbonos = [
    { id: "A-000156", paciente: "Maria Fernanda López", tel: "300 123 4567", fecha: "22/05/2026", factura: "F-000145", concepto: "Abono a tratamiento Limpieza Dental", monto: 300, metodo: "Efectivo", doctor: "Dr. Andrés Díaz", docImg: "https://i.pravatar.cc/150?img=11" },
    { id: "A-000155", paciente: "Juan Pablo Ramírez", tel: "311 987 6543", fecha: "22/05/2026", factura: "F-000144", concepto: "Abono a tratamiento Resina Compuesta", monto: 200, metodo: "Tarjeta", doctor: "Dra. Valentina Gómez", docImg: "https://i.pravatar.cc/150?img=47" },
    { id: "A-000154", paciente: "Valentina Torres", tel: "320 555 8899", fecha: "22/05/2026", factura: "F-000143", concepto: "Abono a tratamiento Control Ortodoncia", monto: 150, metodo: "Transferencia", doctor: "Dr. Andrés Díaz", docImg: "https://i.pravatar.cc/150?img=11" },
    { id: "A-000153", paciente: "Miguel Ángel Rojas", tel: "315 777 2211", fecha: "21/05/2026", factura: "F-000142", concepto: "Abono a tratamiento Ortodoncia inicial", monto: 500, metodo: "Tarjeta", doctor: "Dra. Valentina Gómez", docImg: "https://i.pravatar.cc/150?img=47" },
    { id: "A-000152", paciente: "Ana Sofia Martínez", tel: "301 654 9870", fecha: "21/05/2026", factura: "F-000141", concepto: "Abono a tratamiento Blanqueamiento", monto: 250, metodo: "Efectivo", doctor: "Dr. Andrés Díaz", docImg: "https://i.pravatar.cc/150?img=11" }
];
 
let dbDeudas = [
    { paciente: "Miguel Ángel Rojas", tel: "315 777 2211", tratamiento: "Ortodoncia Inicial", total: 1500, dias: 45, clase: "red" },
    { paciente: "Valentina Torres", tel: "320 555 8899", tratamiento: "Control Ortodoncia", total: 500, dias: 15, clase: "orange" },
    { paciente: "Juan Pablo Ramírez", tel: "311 987 6543", tratamiento: "Resina Compuesta", total: 500, dias: 10, clase: "orange" },
    { paciente: "Carlos Mendoza", tel: "300 444 1122", tratamiento: "Endodoncia Incisivo", total: 1100, dias: 32, clase: "red" },
    { paciente: "Diana Marcela Beltrán", tel: "314 222 3388", tratamiento: "Placa Miorelajante", total: 400, dias: 6, clase: "green" }
];
 
// 2. ESTADO DE LA APLICACIÓN
let activeTab = "facturacion";
let currentPage = 1;
const ROWS_PER_PAGE = 10;
let filtroFecha = "";
let filtroDoctor = "";
let facturaViendoDetalle = null;
 
// 3. GENERADOR DE NÚMERO DE FACTURA
function generarNumFactura() {
    const nums = dbFacturacion.map(f => parseInt(f.num.replace("F-", "")));
    const max = nums.length ? Math.max(...nums) : 0;
    return "F-" + String(max + 1).padStart(6, "0");
}
function generarNumAbono() {
    const nums = dbAbonos.map(a => parseInt(a.id.replace("A-", "")));
    const max = nums.length ? Math.max(...nums) : 0;
    return "A-" + String(max + 1).padStart(6, "0");
}
 
// 4. FECHA ACTUAL FORMATEADA
function fechaHoy() {
    const d = new Date();
    return d.toLocaleDateString("es-CO", { day: "2-digit", month: "2-digit", year: "numeric" }).replace(/\//g, "/");
}
 
// 5. RECALCULAR KPIs DESDE LOS DATOS
function recalcularKPIs() {
    const hoy = fechaHoy();
    const ingDia = dbFacturacion.filter(f => f.fecha === hoy).reduce((s, f) => s + f.pagado, 0);
    const ingMes = dbFacturacion.reduce((s, f) => s + f.pagado, 0);
    const abonosMes = dbAbonos.reduce((s, a) => s + a.monto, 0);
    const deudas = dbFacturacion.reduce((s, f) => s + f.pendiente, 0);
 
    const cards = document.querySelectorAll(".kpi-card .number");
    if (cards.length >= 4) {
        cards[0].textContent = "$ " + ingDia.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        cards[1].textContent = "$ " + ingMes.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        cards[2].textContent = "$ " + abonosMes.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        cards[3].textContent = "$ " + deudas.toLocaleString("es-CO", { minimumFractionDigits: 2 });
    }
 
    // Recalcular cierre de caja
    recalcularCierre();
}
 
function recalcularCierre() {
    const hoy = fechaHoy();
    const factHoy = dbFacturacion.filter(f => f.fecha === hoy);
    const efectivo = factHoy.filter(f => f.metodo === "Efectivo").reduce((s, f) => s + f.pagado, 0);
    const tarjeta = factHoy.filter(f => f.metodo === "Tarjeta").reduce((s, f) => s + f.pagado, 0);
    const transf = factHoy.filter(f => f.metodo === "Transferencia").reduce((s, f) => s + f.pagado, 0);
    const total = efectivo + tarjeta + transf;
 
    const rows = document.querySelectorAll(".cash-row strong");
    if (rows.length >= 3) {
        rows[0].textContent = "$ " + efectivo.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        rows[1].textContent = "$ " + tarjeta.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        rows[2].textContent = "$ " + transf.toLocaleString("es-CO", { minimumFractionDigits: 2 });
    }
    const totalEl = document.querySelector(".cash-total-row strong");
    if (totalEl) totalEl.textContent = "$ " + total.toLocaleString("es-CO", { minimumFractionDigits: 2 });
}
 
// 6. RENDERIZADO DE TABLA CON PAGINACIÓN
function buildTable() {
    const thead = document.getElementById("mainTableHead");
    const tbody = document.getElementById("mainTableBody");
    const searchVal = document.getElementById("mainSearchInput").value.toLowerCase().trim();
 
    thead.innerHTML = "";
    tbody.innerHTML = "";
 
    let filtered = [];
 
    if (activeTab === "facturacion") {
        thead.innerHTML = `<tr>
            <th>N° Factura</th><th>Paciente</th><th>Fecha</th><th>Concepto</th>
            <th>Total</th><th>Pagado</th><th>Pendiente</th><th>Estado</th><th>Acciones</th>
        </tr>`;
 
        filtered = dbFacturacion.filter(f =>
            (!searchVal || f.paciente.toLowerCase().includes(searchVal) || f.num.toLowerCase().includes(searchVal))
        );
 
        const paginated = paginate(filtered);
        paginated.forEach((f, idx) => {
            const statusClass = f.estado === "Pagada" ? "bg-pagada" : (f.estado === "Parcial" ? "bg-parcial" : "bg-pendiente");
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td style="color:var(--text-muted);font-weight:500;">${f.num}</td>
                <td><span class="paciente-td">${f.paciente}</span><span class="paciente-id">Tel: ${f.id}</span></td>
                <td>${f.fecha}</td>
                <td>${f.concepto}</td>
                <td style="font-weight:600;">$ ${f.total.toFixed(2)}</td>
                <td style="color:var(--c-green);font-weight:600;">$ ${f.pagado.toFixed(2)}</td>
                <td style="color:var(--c-orange);font-weight:600;">$ ${f.pendiente.toFixed(2)}</td>
                <td><span class="badge ${statusClass}">${f.estado}</span></td>
                <td><div class="action-icons-wrap">
                    <i class="fa-regular fa-file-pdf" style="color:var(--c-red);cursor:pointer;" title="Imprimir PDF" onclick="imprimirFactura('${f.num}')"></i>
                    <i class="fa-regular fa-eye" style="cursor:pointer;" title="Ver detalle" onclick="verDetalleFactura('${f.num}')"></i>
                    ${f.pendiente > 0 ? `<i class="fa-solid fa-hand-holding-dollar" style="color:var(--c-green);cursor:pointer;" title="Registrar abono" onclick="abrirModalAbono('${f.num}')"></i>` : ''}
                    <i class="fa-solid fa-trash" style="color:#e2e8f0;cursor:pointer;" title="Eliminar" onclick="eliminarFactura('${f.num}')"></i>
                </div></td>
            `;
            tbody.appendChild(tr);
        });
 
    } else if (activeTab === "abonos") {
        thead.innerHTML = `<tr>
            <th>N° Abono</th><th>Paciente</th><th>Fecha</th><th>Factura Ref.</th>
            <th>Concepto</th><th>Monto</th><th>Método</th><th>Recibido Por</th><th>Acciones</th>
        </tr>`;
 
        filtered = dbAbonos.filter(a =>
            (!searchVal || a.paciente.toLowerCase().includes(searchVal) || a.id.toLowerCase().includes(searchVal))
        );
 
        const paginated = paginate(filtered);
        paginated.forEach(a => {
            let badgeStyle = "";
            if (a.metodo === "Tarjeta") badgeStyle = 'style="background:#eff6ff;color:#3b82f6;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;"';
            else if (a.metodo === "Transferencia") badgeStyle = 'style="background:#faf5ff;color:#a855f7;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;"';
            else badgeStyle = 'class="badge bg-pagada"';
 
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td style="color:var(--text-muted);font-weight:500;">${a.id}</td>
                <td><span class="paciente-td">${a.paciente}</span><span class="paciente-id">Tel: ${a.tel}</span></td>
                <td>${a.fecha}</td>
                <td style="color:var(--primary-blue);font-weight:500;">${a.factura}</td>
                <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${a.concepto}</td>
                <td style="color:var(--c-green);font-weight:600;">$ ${a.monto.toFixed(2)}</td>
                <td><span ${badgeStyle}>${a.metodo}</span></td>
                <td><div class="doctor-profile-td"><img src="${a.docImg}" alt="Doctor"><span>${a.doctor}</span></div></td>
                <td><div class="action-icons-wrap">
                    <i class="fa-solid fa-print" style="cursor:pointer;" title="Imprimir recibo" onclick="imprimirAbono('${a.id}')"></i>
                    <i class="fa-solid fa-trash" style="color:#e2e8f0;cursor:pointer;" title="Eliminar" onclick="eliminarAbono('${a.id}')"></i>
                </div></td>
            `;
            tbody.appendChild(tr);
        });
 
    } else if (activeTab === "deudas") {
        thead.innerHTML = `<tr>
            <th>Paciente</th><th>Tratamiento</th><th>Deuda Total</th><th>Antigüedad</th><th>Acciones</th>
        </tr>`;
 
        filtered = dbDeudas.filter(d =>
            (!searchVal || d.paciente.toLowerCase().includes(searchVal))
        );
 
        const paginated = paginate(filtered);
        paginated.forEach(d => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><span class="paciente-td">${d.paciente}</span><span class="paciente-id">Tel: ${d.tel}</span></td>
                <td>${d.tratamiento}</td>
                <td style="font-weight:600;color:#334155;">$ ${d.total.toFixed(2)}</td>
                <td><span class="days-pill ${d.clase}">${d.dias} días en mora</span></td>
                <td><div class="action-icons-wrap">
                    <i class="fa-regular fa-bell" style="color:var(--c-orange);cursor:pointer;" title="Enviar recordatorio" onclick="enviarRecordatorio('${d.paciente}', '${d.tel}')"></i>
                    <i class="fa-regular fa-eye" style="color:var(--primary-blue);cursor:pointer;" title="Ver historial" onclick="verHistorialDeuda('${d.paciente}')"></i>
                </div></td>
            `;
            tbody.appendChild(tr);
        });
 
    } else if (activeTab === "emitidas") {
        thead.innerHTML = `<tr>
            <th>N° Factura</th><th>Paciente</th><th>Fecha Emisión</th><th>Concepto</th>
            <th>Monto Total</th><th>Estado</th><th>Acciones</th>
        </tr>`;
 
        filtered = dbFacturacion.filter(f =>
            (!searchVal || f.paciente.toLowerCase().includes(searchVal) || f.num.toLowerCase().includes(searchVal))
        );
 
        const paginated = paginate(filtered);
        paginated.forEach(f => {
            const statusClass = f.estado === "Pagada" ? "bg-pagada" : (f.estado === "Parcial" ? "bg-parcial" : "bg-pendiente");
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td style="color:var(--text-muted);font-weight:500;">${f.num}</td>
                <td><span class="paciente-td">${f.paciente}</span></td>
                <td>${f.fecha}</td>
                <td>${f.concepto}</td>
                <td style="font-weight:600;">$ ${f.total.toFixed(2)}</td>
                <td><span class="badge ${statusClass}">${f.estado}</span></td>
                <td><div class="action-icons-wrap">
                    <i class="fa-regular fa-file-pdf" style="color:var(--c-red);cursor:pointer;" title="Descargar PDF" onclick="imprimirFactura('${f.num}')"></i>
                    <i class="fa-regular fa-envelope" style="cursor:pointer;" title="Enviar por email" onclick="enviarEmail('${f.num}')"></i>
                </div></td>
            `;
            tbody.appendChild(tr);
        });
    }
 
    // Actualizar contador y paginación
    document.getElementById("rowsCounter").innerText = filtered.length;
    renderPaginacion(filtered.length);
}
 
// 7. PAGINACIÓN
function paginate(data) {
    const start = (currentPage - 1) * ROWS_PER_PAGE;
    return data.slice(start, start + ROWS_PER_PAGE);
}
 
function renderPaginacion(total) {
    const totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    const container = document.querySelector(".pagination-buttons");
    if (!container) return;
 
    container.innerHTML = "";
 
    // Botón anterior
    const prev = document.createElement("button");
    prev.className = "btn-page";
    prev.innerHTML = `<i class="fa-solid fa-chevron-left"></i>`;
    prev.disabled = currentPage === 1;
    prev.style.opacity = currentPage === 1 ? "0.4" : "1";
    prev.addEventListener("click", () => { if (currentPage > 1) { currentPage--; buildTable(); } });
    container.appendChild(prev);
 
    // Números de página
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.className = "btn-page" + (i === currentPage ? " active" : "");
        btn.textContent = i;
        btn.addEventListener("click", () => { currentPage = i; buildTable(); });
        container.appendChild(btn);
    }
 
    // Botón siguiente
    const next = document.createElement("button");
    next.className = "btn-page";
    next.innerHTML = `<i class="fa-solid fa-chevron-right"></i>`;
    next.disabled = currentPage === totalPages;
    next.style.opacity = currentPage === totalPages ? "0.4" : "1";
    next.addEventListener("click", () => { if (currentPage < totalPages) { currentPage++; buildTable(); } });
    container.appendChild(next);
}
 
// 8. ACCIONES DE TABLA - FACTURAS
 
function imprimirFactura(num) {
    const f = dbFacturacion.find(x => x.num === num);
    if (!f) return;
    const win = window.open("", "_blank", "width=600,height=700");
    win.document.write(`
        <html><head><title>Factura ${f.num}</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 30px; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 16px; margin-bottom: 20px; }
            .header h1 { color: #2563eb; font-size: 22px; }
            .row { display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px; }
            .label { color: #64748b; }
            .total { font-size: 18px; font-weight: bold; color: #2563eb; border-top: 2px solid #e2e8f0; padding-top: 12px; margin-top: 12px; }
            .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
            .Pagada { background: #f0fdf4; color: #22c55e; }
            .Parcial { background: #fffbeb; color: #f59e0b; }
            .Pendiente { background: #fef2f2; color: #ef4444; }
        </style></head><body>
        <div class="header">
            <h1>🦷 Odonto Estética</h1>
            <p>Factura de Servicios Odontológicos</p>
        </div>
        <div class="row"><span class="label">N° Factura:</span><strong>${f.num}</strong></div>
        <div class="row"><span class="label">Fecha:</span><span>${f.fecha}</span></div>
        <div class="row"><span class="label">Paciente:</span><span>${f.paciente}</span></div>
        <div class="row"><span class="label">Documento/Tel:</span><span>${f.id}</span></div>
        <div class="row"><span class="label">Concepto:</span><span>${f.concepto}</span></div>
        <div class="row"><span class="label">Método de Pago:</span><span>${f.metodo || "—"}</span></div>
        <div class="row total"><span>Total Factura:</span><span>$ ${f.total.toFixed(2)}</span></div>
        <div class="row"><span class="label">Pagado:</span><span style="color:#22c55e;font-weight:600;">$ ${f.pagado.toFixed(2)}</span></div>
        <div class="row"><span class="label">Pendiente:</span><span style="color:#f59e0b;font-weight:600;">$ ${f.pendiente.toFixed(2)}</span></div>
        <div class="row"><span class="label">Estado:</span><span class="badge ${f.estado}">${f.estado}</span></div>
        <br><p style="text-align:center;color:#94a3b8;font-size:12px;">Gracias por su confianza — Odonto Estética</p>
        <script>window.onload = () => window.print();<\/script>
        </body></html>
    `);
    win.document.close();
}
 
function verDetalleFactura(num) {
    const f = dbFacturacion.find(x => x.num === num);
    if (!f) return;
    mostrarToast(`📋 Factura ${f.num} — ${f.paciente} | Total: $${f.total} | Estado: ${f.estado}`, "info");
}
 
function abrirModalAbono(numFactura) {
    const f = dbFacturacion.find(x => x.num === numFactura);
    if (!f) return;
    // Pre-llenar el modal de abono
    document.getElementById("abonoFacturaRef").value = numFactura;
    document.getElementById("abonoPaciente").value = f.paciente;
    document.getElementById("abonoMaximo").textContent = `Máximo: $ ${f.pendiente.toFixed(2)}`;
    document.getElementById("abonoMonto").max = f.pendiente;
    document.getElementById("modalAbono").style.display = "flex";
}
 
function eliminarFactura(num) {
    if (!confirm(`¿Eliminar la factura ${num}? Esta acción no se puede deshacer.`)) return;
    dbFacturacion = dbFacturacion.filter(f => f.num !== num);
    recalcularKPIs();
    buildTable();
    mostrarToast(`Factura ${num} eliminada.`, "error");
}
 
// 9. ACCIONES DE TABLA - ABONOS
 
function imprimirAbono(id) {
    const a = dbAbonos.find(x => x.id === id);
    if (!a) return;
    const win = window.open("", "_blank", "width=500,height=500");
    win.document.write(`
        <html><head><title>Recibo ${a.id}</title>
        <style>body{font-family:Arial,sans-serif;padding:30px;color:#333;} .header{text-align:center;border-bottom:2px solid #2563eb;padding-bottom:16px;margin-bottom:20px;} .header h1{color:#2563eb;} .row{display:flex;justify-content:space-between;margin:8px 0;font-size:14px;} .label{color:#64748b;}</style>
        </head><body>
        <div class="header"><h1>🦷 Odonto Estética</h1><p>Recibo de Abono</p></div>
        <div class="row"><span class="label">N° Abono:</span><strong>${a.id}</strong></div>
        <div class="row"><span class="label">Fecha:</span><span>${a.fecha}</span></div>
        <div class="row"><span class="label">Paciente:</span><span>${a.paciente}</span></div>
        <div class="row"><span class="label">Factura:</span><span>${a.factura}</span></div>
        <div class="row"><span class="label">Concepto:</span><span>${a.concepto}</span></div>
        <div class="row"><span class="label">Método:</span><span>${a.metodo}</span></div>
        <div class="row"><span class="label">Recibido por:</span><span>${a.doctor}</span></div>
        <div class="row" style="font-size:18px;font-weight:bold;color:#2563eb;border-top:2px solid #e2e8f0;padding-top:12px;margin-top:12px;">
            <span>Monto Abonado:</span><span>$ ${a.monto.toFixed(2)}</span>
        </div>
        <script>window.onload = () => window.print();<\/script>
        </body></html>
    `);
    win.document.close();
}
 
function eliminarAbono(id) {
    if (!confirm(`¿Eliminar el abono ${id}?`)) return;
    dbAbonos = dbAbonos.filter(a => a.id !== id);
    buildTable();
    mostrarToast(`Abono ${id} eliminado.`, "error");
}
 
// 10. ACCIONES DE TABLA - DEUDAS
 
function enviarRecordatorio(paciente, tel) {
    mostrarToast(`📲 Recordatorio enviado a ${paciente} (${tel})`, "success");
}
 
function verHistorialDeuda(paciente) {
    const facturas = dbFacturacion.filter(f => f.paciente === paciente);
    if (facturas.length === 0) { mostrarToast(`Sin facturas registradas para ${paciente}`, "info"); return; }
    const resumen = facturas.map(f => `${f.num}: ${f.concepto} — Pendiente: $${f.pendiente}`).join("\n");
    alert(`Historial de ${paciente}:\n\n${resumen}`);
}
 
function enviarEmail(num) {
    mostrarToast(`📧 Factura ${num} enviada al correo del paciente.`, "success");
}
 
// 11. MODAL NUEVA FACTURA
const modalFactura = document.getElementById("modalFactura");
 
// Fix: el botón en el header usa onclick inline, así que también enlazamos por ID si existe
const btnNuevaFactura = document.querySelector(".btn-primary[onclick]");
// El modal ya tiene onclick en el HTML, pero también cerramos con closeModal
document.getElementById("closeModal").addEventListener("click", () => modalFactura.style.display = "none");
window.addEventListener("click", e => { if (e.target === modalFactura) modalFactura.style.display = "none"; });
 
// Ampliar el form con más campos útiles
(function mejorarFormFactura() {
    const form = document.getElementById("formFactura");
    if (!form) return;
 
    // Agregar campo documento, método de pago y monto pagado
    form.innerHTML = `
        <div class="form-group">
            <label>Paciente *</label>
            <input type="text" id="fPaciente" placeholder="Ej. Juan Pérez" required>
        </div>
        <div class="form-group">
            <label>Documento / Teléfono *</label>
            <input type="text" id="fDocumento" placeholder="Ej. 300 123 4567" required>
        </div>
        <div class="form-group">
            <label>Concepto Médico *</label>
            <input type="text" id="fConcepto" placeholder="Ej. Resina de Premolar" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label>Monto Total ($) *</label>
                <input type="number" id="fTotal" placeholder="0.00" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Monto Pagado ($)</label>
                <input type="number" id="fPagado" placeholder="0.00" min="0" step="0.01">
            </div>
        </div>
        <div class="form-group">
            <label>Método de Pago</label>
            <select id="fMetodo">
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;">
            <i class="fa-solid fa-check"></i> Crear Factura
        </button>
    `;
 
    form.addEventListener("submit", e => {
        e.preventDefault();
        const paciente = document.getElementById("fPaciente").value.trim();
        const doc = document.getElementById("fDocumento").value.trim();
        const concepto = document.getElementById("fConcepto").value.trim();
        const total = parseFloat(document.getElementById("fTotal").value) || 0;
        const pagado = Math.min(parseFloat(document.getElementById("fPagado").value) || 0, total);
        const metodo = document.getElementById("fMetodo").value;
        const pendiente = total - pagado;
        const estado = pendiente <= 0 ? "Pagada" : (pagado > 0 ? "Parcial" : "Pendiente");
 
        if (!paciente || !doc || !concepto || total <= 0) {
            mostrarToast("Por favor completa todos los campos obligatorios.", "error");
            return;
        }
 
        const nuevaFactura = {
            num: generarNumFactura(),
            paciente, id: doc,
            fecha: fechaHoy(),
            concepto, total, pagado, pendiente, estado, metodo
        };
 
        dbFacturacion.unshift(nuevaFactura);
 
        // Si pagó algo, también registrar abono
        if (pagado > 0) {
            dbAbonos.unshift({
                id: generarNumAbono(),
                paciente, tel: doc,
                fecha: fechaHoy(),
                factura: nuevaFactura.num,
                concepto: `Pago a tratamiento ${concepto}`,
                monto: pagado, metodo,
                doctor: "Dr. Administrador",
                docImg: "https://i.pravatar.cc/150?img=3"
            });
        }
 
        modalFactura.style.display = "none";
        form.reset();
        activeTab = "facturacion";
        document.querySelectorAll(".tab-btn").forEach(t => t.classList.remove("active"));
        document.querySelector('[data-tab="facturacion"]').classList.add("active");
        currentPage = 1;
        recalcularKPIs();
        buildTable();
        mostrarToast(`✅ Factura ${nuevaFactura.num} creada exitosamente.`, "success");
    });
})();
 
// 12. MODAL REGISTRO DE ABONO
(function crearModalAbono() {
    const modalHTML = `
    <div id="modalAbono" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeModalAbono">&times;</span>
            <h2>Registrar Abono</h2>
            <div class="form-group">
                <label>Paciente</label>
                <input type="text" id="abonoPaciente" readonly style="background:#f8fafc;">
            </div>
            <div class="form-group">
                <label>Factura de Referencia</label>
                <input type="text" id="abonoFacturaRef" readonly style="background:#f8fafc;">
            </div>
            <div class="form-group">
                <label>Monto a Abonar ($) <small id="abonoMaximo" style="color:var(--c-orange);"></small></label>
                <input type="number" id="abonoMonto" placeholder="0.00" min="1" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Método de Pago</label>
                <select id="abonoMetodo">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>
            <button id="btnGuardarAbono" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;">
                <i class="fa-solid fa-check"></i> Registrar Abono
            </button>
        </div>
    </div>`;
    document.body.insertAdjacentHTML("beforeend", modalHTML);
 
    document.getElementById("closeModalAbono").addEventListener("click", () => {
        document.getElementById("modalAbono").style.display = "none";
    });
    window.addEventListener("click", e => {
        const m = document.getElementById("modalAbono");
        if (e.target === m) m.style.display = "none";
    });
 
    document.getElementById("btnGuardarAbono").addEventListener("click", () => {
        const facturaRef = document.getElementById("abonoFacturaRef").value;
        const monto = parseFloat(document.getElementById("abonoMonto").value) || 0;
        const metodo = document.getElementById("abonoMetodo").value;
        const f = dbFacturacion.find(x => x.num === facturaRef);
 
        if (!f || monto <= 0 || monto > f.pendiente) {
            mostrarToast("Monto inválido o superior al saldo pendiente.", "error");
            return;
        }
 
        // Actualizar factura
        f.pagado += monto;
        f.pendiente -= monto;
        f.estado = f.pendiente <= 0 ? "Pagada" : "Parcial";
 
        // Registrar abono
        dbAbonos.unshift({
            id: generarNumAbono(),
            paciente: f.paciente, tel: f.id,
            fecha: fechaHoy(),
            factura: facturaRef,
            concepto: `Abono a tratamiento ${f.concepto}`,
            monto, metodo,
            doctor: "Dr. Administrador",
            docImg: "https://i.pravatar.cc/150?img=3"
        });
 
        // Actualizar deudas
        const deudaIdx = dbDeudas.findIndex(d => d.paciente === f.paciente);
        if (deudaIdx !== -1) {
            dbDeudas[deudaIdx].total -= monto;
            if (dbDeudas[deudaIdx].total <= 0) dbDeudas.splice(deudaIdx, 1);
        }
 
        document.getElementById("modalAbono").style.display = "none";
        recalcularKPIs();
        buildTable();
        mostrarToast(`✅ Abono de $${monto.toFixed(2)} registrado en ${facturaRef}.`, "success");
    });
})();
 
// 13. TABS
document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", e => {
        document.querySelectorAll(".tab-btn").forEach(t => t.classList.remove("active"));
        e.target.classList.add("active");
        activeTab = e.target.getAttribute("data-tab");
        currentPage = 1;
        buildTable();
    });
});
 
// 14. BÚSQUEDA
document.getElementById("mainSearchInput").addEventListener("input", () => {
    currentPage = 1;
    buildTable();
});
 
// 15. BOTÓN IMPRIMIR REPORTE DIARIO
document.querySelector(".btn-outline-action").addEventListener("click", () => {
    const hoy = fechaHoy();
    const factHoy = dbFacturacion.filter(f => f.fecha === hoy);
    const efectivo = factHoy.filter(f => f.metodo === "Efectivo").reduce((s, f) => s + f.pagado, 0);
    const tarjeta = factHoy.filter(f => f.metodo === "Tarjeta").reduce((s, f) => s + f.pagado, 0);
    const transf = factHoy.filter(f => f.metodo === "Transferencia").reduce((s, f) => s + f.pagado, 0);
    const total = efectivo + tarjeta + transf;
 
    const win = window.open("", "_blank", "width=600,height=600");
    win.document.write(`
        <html><head><title>Cierre de Caja ${hoy}</title>
        <style>body{font-family:Arial,sans-serif;padding:30px;color:#333;} h1{color:#2563eb;border-bottom:2px solid #2563eb;padding-bottom:10px;} .row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:15px;} .total{font-size:18px;font-weight:bold;color:#2563eb;padding-top:14px;}</style>
        </head><body>
        <h1>🦷 Odonto Estética — Cierre de Caja</h1>
        <p>Fecha: <strong>${hoy}</strong></p>
        <div class="row"><span>💵 Efectivo:</span><strong>$ ${efectivo.toFixed(2)}</strong></div>
        <div class="row"><span>💳 Tarjeta:</span><strong>$ ${tarjeta.toFixed(2)}</strong></div>
        <div class="row"><span>🏦 Transferencia:</span><strong>$ ${transf.toFixed(2)}</strong></div>
        <div class="row total"><span>Total en Caja:</span><span>$ ${total.toFixed(2)}</span></div>
        <p style="margin-top:20px;color:#94a3b8;font-size:12px;">Generado: ${new Date().toLocaleString("es-CO")}</p>
        <script>window.onload = () => window.print();<\/script>
        </body></html>
    `);
    win.document.close();
});
 
// 16. BOTÓN DESCARGAR DATOS
document.getElementById("btnDescargarDatos").addEventListener("click", function() {
    const formato = this.closest(".panel").querySelector("select").value;
    
    // Generar CSV (funciona para ambas opciones sin librerías externas)
    let csv = "N° Factura,Paciente,Documento,Fecha,Concepto,Total,Pagado,Pendiente,Estado\n";
    dbFacturacion.forEach(f => {
        csv += `"${f.num}","${f.paciente}","${f.id}","${f.fecha}","${f.concepto}",${f.total},${f.pagado},${f.pendiente},"${f.estado}"\n`;
    });
 
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `facturacion_${fechaHoy().replace(/\//g,"_")}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    mostrarToast("📥 Archivo descargado exitosamente.", "success");
});
 
// 17. FILTROS DROPDOWN (Fecha y Doctor)
(function inicializarFiltros() {
    const btns = document.querySelectorAll(".btn-filter-dropdown");
    if (btns.length < 2) return;
 
    // Botón filtrar por fecha
    btns[0].addEventListener("click", () => {
        const fecha = prompt("Filtrar por fecha (DD/MM/YYYY):\nDeja vacío para ver todos:");
        if (fecha !== null) {
            filtroFecha = fecha.trim();
            btns[0].innerHTML = filtroFecha
                ? `<i class="fa-regular fa-calendar"></i> ${filtroFecha} <i class="fa-solid fa-xmark" style="color:var(--c-red);"></i>`
                : `<i class="fa-regular fa-calendar"></i> Filtrar Fecha <i class="fa-solid fa-chevron-down"></i>`;
            currentPage = 1;
            buildTable();
        }
    });
 
    // Botón filtrar por doctor (solo aplica en abonos)
    btns[1].addEventListener("click", () => {
        const doctores = [...new Set(dbAbonos.map(a => a.doctor))];
        const opciones = ["Todos", ...doctores].map((d, i) => `${i}. ${d}`).join("\n");
        const sel = prompt(`Selecciona doctor:\n${opciones}\n\nEscribe el número:`);
        if (sel !== null) {
            const idx = parseInt(sel);
            filtroDoctor = idx === 0 || isNaN(idx) ? "" : (["Todos", ...doctores][idx] || "");
            btns[1].innerHTML = filtroDoctor
                ? `${filtroDoctor} <i class="fa-solid fa-xmark" style="color:var(--c-red);"></i>`
                : `Todos los Doctores <i class="fa-solid fa-chevron-down"></i>`;
            currentPage = 1;
            buildTable();
        }
    });
})();
 
// 18. SISTEMA DE TOASTS (notificaciones)
function mostrarToast(mensaje, tipo = "success") {
    let container = document.getElementById("toastContainer");
    if (!container) {
        container = document.createElement("div");
        container.id = "toastContainer";
        container.style.cssText = "position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;";
        document.body.appendChild(container);
    }
 
    const colors = { success: "#22c55e", error: "#ef4444", info: "#3b82f6" };
    const toast = document.createElement("div");
    toast.style.cssText = `
        background:white;
        border-left:4px solid ${colors[tipo] || colors.info};
        padding:12px 18px;
        border-radius:8px;
        box-shadow:0 4px 16px rgba(0,0,0,0.12);
        font-family:Inter,sans-serif;
        font-size:13.5px;
        color:#1e293b;
        max-width:340px;
        animation: slideIn 0.3s ease;
    `;
    toast.textContent = mensaje;
    container.appendChild(toast);
 
    // Agregar animación CSS si no existe
    if (!document.getElementById("toastStyle")) {
        const style = document.createElement("style");
        style.id = "toastStyle";
        style.textContent = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
        document.head.appendChild(style);
    }
 
    setTimeout(() => { toast.style.opacity = "0"; toast.style.transition = "opacity 0.3s"; setTimeout(() => toast.remove(), 300); }, 3500);
}
 
// 19. CARGA INICIAL
recalcularKPIs();
buildTable();
 