// =====================================================
// SISTEMA DE FACTURACIÓN Y LIQUIDACIÓN - OPTIMIZADO
// =====================================================

let dbFacturacion = [];
let activeTab = "ingresos";
let liqDataTemporal = null; // Para la liquidación

function fechaHoy() {
    const d = new Date();
    return d.toLocaleDateString("es-CO", { day: "2-digit", month: "2-digit", year: "numeric" }).replace(/\//g, "/");
}

// =====================================================
// 1. CARGA DE DATOS Y RENDERIZADO (INGRESOS)
// =====================================================

async function cargarFacturasDB() {
    try {
        // El ?t=... obliga al navegador a no usar caché y traer los datos frescos
        const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/listar?t=' + new Date().getTime(), {
            headers: { 'Cache-Control': 'no-cache' }
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            dbFacturacion = data.facturas.map(f => {
                const totalBD = parseFloat(f.total) || 0;
                const pagadoBD = parseFloat(f.pagado) || 0;
                let fechaFormateada = f.fecha_emision;
                let fechaSort = '';
                if(fechaFormateada && fechaFormateada.includes('-')) {
                    const partes = fechaFormateada.split(' ')[0].split('-');
                    fechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
                    fechaSort = `${partes[0]}${partes[1]}${partes[2]}`;
                }

                return {
                    num: "FAC-" + String(f.id).padStart(4, "0"),
                    paciente: f.paciente,
                    id: "No registrado",
                    fecha: fechaFormateada,
                    fechaSort: fechaSort,
                    concepto: f.tratamiento,
                    total: totalBD,
                    pagado: pagadoBD,
                    pendiente: totalBD - pagadoBD,
                    estado: f.estado,
                    metodo: "Múltiple"
                };
            });
            
            buildTable();
        } else {
            mostrarToast("Error cargando facturas", "error");
        }
    } catch (error) {
        console.error("Error AJAX:", error);
    }
}

function buildTable() {
    if (activeTab !== "ingresos") return;

    const tableElement = $('#tablaFacturacion');
    let table;
    
    // Configuramos DataTables y lo guardamos en memoria
    if ($.fn.DataTable.isDataTable(tableElement)) {
        table = tableElement.DataTable();
    } else {
        table = tableElement.DataTable({
            "order": [[ 0, "desc" ]],
            "dom": '<"top"lf>rt<"bottom"ip>', 
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
            }
        });
    }

    // Limpiamos la tabla nativa de DataTables
    table.clear();

    // Dibujamos los datos frescos
    dbFacturacion.forEach((f) => {
        let statusClass = f.estado === "Pagada" ? "bg-pagada" : (f.estado === "Abonada" ? "bg-abonada" : "bg-emitida");
        let statusText = f.estado === "Abonada" ? "Abonado" : (f.estado === "Pagada" ? "Pagado" : "Emitido");

        table.row.add([
            `<span style="color:var(--text-muted);font-weight:500;">${f.num}</span>`,
            `<span class="paciente-td">${f.paciente}</span>`,
            `<span style="display:none;">${f.fechaSort}</span>${f.fecha}`,
            f.concepto,
            `<span style="display:none;">${String(f.total).padStart(15, '0')}</span><span style="font-weight:600;">$ ${f.total.toLocaleString("es-CO")}</span>`,
            `<span style="display:none;">${String(f.pagado).padStart(15, '0')}</span><span style="color:var(--c-green);font-weight:600;">$ ${f.pagado.toLocaleString("es-CO")}</span>`,
            `<span style="display:none;">${String(f.pendiente).padStart(15, '0')}</span><span style="color:var(--c-orange);font-weight:600;">$ ${f.pendiente.toLocaleString("es-CO")}</span>`,
            `<span class="badge ${statusClass}">${statusText}</span>`,
            `<div class="action-icons-wrap">
                <i class="fa-solid fa-hand-holding-dollar" style="cursor:pointer; color:var(--c-green); font-size: 16px;" title="Registrar Pago" onclick="abrirModalPago('${f.num}', '${f.paciente}', ${f.pendiente})"></i>
                <i class="fa-solid fa-download" style="cursor:pointer; color:var(--primary-blue); font-size: 16px;" title="Descargar Factura" onclick="abrirModalExport('${f.num}', '${f.paciente}')"></i>
            </div>`
        ]);
    });

    // Le decimos a DataTables que muestre los cambios sin recargar la página
    table.draw(false);
}

// =====================================================
// 2. INDICADORES (KPIs)
// =====================================================

function recalcularKPIs() {
    const hoy = fechaHoy();
    const ingDia = dbFacturacion.filter(f => f.fecha === hoy).reduce((s, f) => s + f.pagado, 0);
    const ingMes = dbFacturacion.reduce((s, f) => s + f.pagado, 0);
    const deudas = dbFacturacion.reduce((s, f) => s + f.pendiente, 0);

    const cards = document.querySelectorAll(".kpi-card .number");
    if (cards.length >= 4) {
        cards[0].textContent = "$ " + ingDia.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        cards[1].textContent = "$ " + ingMes.toLocaleString("es-CO", { minimumFractionDigits: 2 });
        cards[2].textContent = "$ 0.00"; // Abonos directos desactivados
        cards[3].textContent = "$ " + deudas.toLocaleString("es-CO", { minimumFractionDigits: 2 });
    }
    
    // Cierre de caja
    const factHoy = dbFacturacion.filter(f => f.fecha === hoy);
    const totalHoy = factHoy.reduce((s, f) => s + f.pagado, 0);
    const rows = document.querySelectorAll(".cash-row strong");
    if (rows.length >= 3) {
        rows[0].textContent = "$ " + totalHoy.toLocaleString("es-CO", { minimumFractionDigits: 2 }); // Asumiendo todo efectivo temporalmente
        rows[1].textContent = "$ 0.00"; 
        rows[2].textContent = "$ 0.00"; 
    }
    const totalEl = document.querySelector(".cash-total-row strong");
    if (totalEl) totalEl.textContent = "$ " + totalHoy.toLocaleString("es-CO", { minimumFractionDigits: 2 });
}

// =====================================================
// 3. PESTAÑA 2: CONFIGURACIÓN DE PORCENTAJES
// =====================================================

async function cargarConfiguracionPrueba() {
    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/configuracion');
        const data = await response.json();
        
        if (data.status === 'success') {
            const tbody = document.getElementById("configTableBody");
            tbody.innerHTML = '';
            
            const selectOdontologoLiq = document.getElementById("selectOdontologoLiq");
            if (selectOdontologoLiq) {
                selectOdontologoLiq.innerHTML = '<option value="">Seleccione un doctor...</option>';
            }
            
            data.data.forEach(doc => {
                const tr = document.createElement("tr");
                const inicial = doc.doctor.charAt(0);
                
                tr.innerHTML = `
                    <td>
                        <div class="doctor-profile-td">
                            <div style="width: 30px; height: 30px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">${inicial}</div>
                            <span>${doc.doctor}</span>
                        </div>
                    </td>
                    <td>${doc.especialidad}</td>
                    <td style="font-weight:600; color:var(--primary-blue);">${parseFloat(doc.porcentaje).toFixed(2)} %</td>
                    <td>
                        <button class="btn-outline-action" style="padding: 4px 10px; font-size: 11px;" onclick="editarPorcentaje(${doc.ID_ODONTOLOGO}, '${doc.doctor}', ${doc.porcentaje})">
                            <i class="fa-solid fa-pen-to-square"></i> Editar %
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);

                if (selectOdontologoLiq) {
                    const option = document.createElement("option");
                    option.value = doc.ID_ODONTOLOGO;
                    option.textContent = doc.doctor;
                    selectOdontologoLiq.appendChild(option);
                }
            });
        }
    } catch (error) { console.error(error); }
}

async function editarPorcentaje(idOdontologo, nombre, porcentajeActual) {
    const { value: nuevoPorcentaje } = await Swal.fire({
        title: 'Editar Comisión',
        text: `Configurar porcentaje para: ${nombre}`,
        input: 'number',
        inputLabel: 'Nuevo Porcentaje (%)',
        inputValue: porcentajeActual,
        inputAttributes: { min: 0, max: 100, step: 0.1 },
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        confirmButtonColor: '#2563eb'
    });

    if (nuevoPorcentaje) {
        try {
            const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/configuracion/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_odontologo: idOdontologo, porcentaje: parseFloat(nuevoPorcentaje) })
            });
            const res = await response.json();
            if (res.status === 'success') {
                mostrarToast("Porcentaje actualizado", "success");
                cargarConfiguracionPrueba(); 
            } else { mostrarToast(res.message, "error"); }
        } catch (error) { mostrarToast("Error de conexión", "error"); }
    }
}

// =====================================================
// 4. PESTAÑA 3: LIQUIDACIÓN (EGRESOS)
// =====================================================

document.getElementById("btnCalcular")?.addEventListener("click", async function() {
    const idDoc = document.getElementById("selectOdontologoLiq").value;
    const mes = document.getElementById("mesLiquidacion").value;

    if(!idDoc || !mes) return mostrarToast("Selecciona el Odontólogo y el Mes", "error");

    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/liquidacion/calcular', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_odontologo: idDoc, mes: mes })
        });
        const res = await response.json();

        if (res.status === 'success') {
            const d = res.data;
            liqDataTemporal = { id_odontologo: idDoc, mes: mes, produccion: parseFloat(d.produccion_total), porcentaje: parseFloat(d.porcentaje), total_pagado: parseFloat(d.total_pagar) };
            document.getElementById("resultadoLiquidacion").style.display = "block";
            document.getElementById("liqCantidad").textContent = d.cantidad_procedimientos;
            document.getElementById("liqTotalProd").textContent = "$ " + liqDataTemporal.produccion.toLocaleString("es-CO");
            document.getElementById("liqPorcentaje").textContent = liqDataTemporal.porcentaje + " %";
            document.getElementById("liqTotalPagar").textContent = "$ " + liqDataTemporal.total_pagado.toLocaleString("es-CO");
            mostrarToast("Cálculo realizado correctamente", "info");
        } else {
            mostrarToast(res.message, "error");
            document.getElementById("resultadoLiquidacion").style.display = "none";
        }
    } catch (error) { mostrarToast("Error calculando la liquidación", "error"); }
});

document.getElementById("btnRegistrarEgreso")?.addEventListener("click", async function() {
    if (!liqDataTemporal || liqDataTemporal.total_pagado <= 0) return mostrarToast("No hay producción para registrar.", "error");

    try {
        const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/liquidacion/guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(liqDataTemporal)
        });
        const res = await response.json();

        if (res.status === 'success') {
            mostrarToast("✅ " + res.message, "success");
            document.getElementById("resultadoLiquidacion").style.display = "none";
            document.getElementById("selectOdontologoLiq").value = "";
            document.getElementById("mesLiquidacion").value = "";
            liqDataTemporal = null; 
        } else { mostrarToast(res.message, "error"); }
    } catch (error) { mostrarToast("Error de conexión", "error"); }
});

// =====================================================
// 5. EVENTOS GLOBALES Y UTILIDADES
// =====================================================

document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", e => {
        // Quita la clase activa de todos y la pone en el seleccionado
        document.querySelectorAll(".tab-btn").forEach(t => t.classList.remove("active"));
        e.target.classList.add("active");
        
        // Oculta todos los contenidos
        document.querySelectorAll(".tab-content").forEach(c => c.style.display = "none");
        
        activeTab = e.target.getAttribute("data-tab");
        document.getElementById("content-" + activeTab).style.display = "block";

        // LÓGICA NUEVA: Ocultar o mostrar los paneles de abajo (Cierre y Exportar)
        const panelesAbajo = document.querySelector(".right-col");
        if (panelesAbajo) {
            if (activeTab === "configuracion") {
                panelesAbajo.style.display = "none"; // Desaparecen en configuración
            } else {
                panelesAbajo.style.display = "block"; // Se muestran en Ingresos y Egresos
            }
        }

        // Cargar los datos según la pestaña
        if(activeTab === "ingresos") {
            buildTable();
        } else if (activeTab === "configuracion" || activeTab === "egresos") {
            cargarConfiguracionPrueba();
        }
    });
});

function mostrarToast(mensaje, tipo = "success") {
    Swal.fire({
        toast: true, position: 'top',
        icon: tipo === 'error' ? 'error' : (tipo === 'info' ? 'info' : 'success'),
        title: mensaje, showConfirmButton: false, timer: 3000, timerProgressBar: true,
        width: 'auto', padding: '10px 20px'
    });
}

// =====================================================
// MODAL DE EXPORTACIÓN INDIVIDUAL
// =====================================================

function abrirModalExport(num, paciente) {
    document.getElementById("exportFacturaNum").value = num;
    document.getElementById("exportFacturaInfo").textContent = `${num} - ${paciente}`;
    document.getElementById("modalExportIndividual").style.display = "flex";
}

function exportarIndividual(formato) {
    const num = document.getElementById("exportFacturaNum").value;
    // Extraer solo el número (quitar "FAC-")
    const idFactura = parseInt(num.replace("FAC-", ""));
    
    // Cerrar el modal
    document.getElementById("modalExportIndividual").style.display = "none";
    
    // Mostrar notificación
    if (formato === "imprimir") {
        mostrarToast(`Preparando ${num} para imprimir...`, "info");
    } else {
        mostrarToast(`Generando ${formato.toUpperCase()} de ${num}...`, "info");
    }
    
    // Abrir en nueva pestaña
    window.open(`/LOGIN_ORIGINAL/admin/facturacion/exportar-individual?id=${idFactura}&formato=${formato}`, '_blank');
}

async function cargarKPIsReal() {
    try {
        // Obtenemos Resumen y Cierre en una sola petición
        const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/resumen?t=' + new Date().getTime());
        const res = await response.json();
        
        if (res.status === 'success') {
            const d = res.data; // Aquí vienen los datos del resumen
            const c = res.caja; // Aquí vienen los datos del cierre (Efectivo/Tarjeta/Transf)

            // Actualizar tarjetas superiores
            const cards = document.querySelectorAll(".kpi-card .number");
            cards[0].textContent = "$ " + parseFloat(d.ingresos_dia || 0).toLocaleString("es-CO");
            cards[1].textContent = "$ " + parseFloat(d.ingresos_mes || 0).toLocaleString("es-CO");
            cards[2].textContent = "$ " + parseFloat(d.abonos_mes || 0).toLocaleString("es-CO");
            cards[3].textContent = "$ " + parseFloat(d.deudas_pendientes || 0).toLocaleString("es-CO");

            // Actualizar Cierre de Caja
            document.getElementById("cajaEfectivo").textContent = "$ " + parseFloat(c.efectivo || 0).toLocaleString("es-CO");
            document.getElementById("cajaTarjeta").textContent = "$ " + parseFloat(c.tarjeta || 0).toLocaleString("es-CO");
            document.getElementById("cajaTransferencia").textContent = "$ " + parseFloat(c.transferencia || 0).toLocaleString("es-CO");
            document.getElementById("cajaTotal").textContent = "$ " + (parseFloat(c.efectivo) + parseFloat(c.tarjeta) + parseFloat(c.transferencia)).toLocaleString("es-CO");
        }
    } catch (e) { console.error("Error cargando KPIs:", e); }
}

// =====================================================
// 6. LÓGICA DEL MODAL DE PAGOS / ABONOS (CORREGIDA)
// =====================================================

function abrirModalPago(num, paciente, pendiente) {
    // Si ya está pagada, no dejamos abrir el modal
    if (parseFloat(pendiente) <= 0) {
        return mostrarToast("Esta factura ya está pagada en su totalidad.", "info");
    }

    // Llenamos los datos visuales
    document.getElementById("pagoNumFactura").value = num;
    document.getElementById("pagoDetalle").value = `${num} - ${paciente}`;
    document.getElementById("pagoPendienteVisual").value = "$ " + parseFloat(pendiente).toLocaleString("es-CO");
    
    // Sugerimos pagar la totalidad por defecto
    const inputMonto = document.getElementById("pagoMonto");
    inputMonto.value = pendiente; 
    inputMonto.max = pendiente; // No puede pagar más de lo que debe

    // Mostramos el modal
    document.getElementById("modalPago").style.display = "flex";
}

// Todo lo que interactúa con el DOM se asegura cuando la página cargue
document.addEventListener("DOMContentLoaded", () => {
    // A. Lógica para cerrar la X del modal
    const btnClosePago = document.getElementById("closeModalPago");
    const modalPago = document.getElementById("modalPago");

    if (btnClosePago && modalPago) {
        btnClosePago.addEventListener("click", () => {
            modalPago.style.display = "none";
        });
    }

    // A2. Lógica para cerrar la X del modal de exportación
    const btnCloseExport = document.getElementById("closeModalExport");
    const modalExport = document.getElementById("modalExportIndividual");
    if (btnCloseExport && modalExport) {
        btnCloseExport.addEventListener("click", () => {
            modalExport.style.display = "none";
        });
        // Cerrar al hacer clic fuera del contenido del modal
        modalExport.addEventListener("click", (e) => {
            if (e.target === modalExport) {
                modalExport.style.display = "none";
            }
        });
    }

    // B. Lógica para guardar el pago en la BD sin recargar
    const formPago = document.getElementById("formPago");
    if (formPago) {
        formPago.addEventListener("submit", async (e) => {
            e.preventDefault(); // ¡Esto evita que la página recargue y te devuelva al inicio!

            const numFactura = document.getElementById("pagoNumFactura").value;
            const metodo = document.getElementById("pagoMetodo").value;
            const monto = parseFloat(document.getElementById("pagoMonto").value);
            
            // Le quitamos el "FAC-" para mandarle solo el número a la BD
            const idFacturaBD = parseInt(numFactura.replace("FAC-", "")); 

            try {
                const response = await fetch('/LOGIN_ORIGINAL/admin/facturacion/pago/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ factura_id: idFacturaBD, metodo: metodo, monto: monto })
                });
                const res = await response.json();

                if (res.status === 'success') {
                    mostrarToast("✅ Pago registrado correctamente", "success");
                    modalPago.style.display = "none"; 
                    formPago.reset(); 
                    
                    // Actualizamos la tabla y los números de arriba para que se refleje el pago
                    cargarFacturasDB();
                    cargarKPIsReal();
                } else {
                    mostrarToast(res.message, "error");
                }
                } catch (error) {
                    console.error("Error al guardar:", error);
                    mostrarToast("Error de conexión con el servidor", "error");
                }
            });
        }

        // Carga inicial
        const inputMes = document.getElementById("mesLiquidacion");
        if(inputMes) inputMes.setAttribute("max", new Date().toISOString().slice(0, 7));
        
        cargarFacturasDB();
        cargarKPIsReal();
    });

    // =====================================================
    // EXPORTACIÓN DINÁMICA CON SELECTOR DE MES
    // =====================================================

    document.addEventListener("DOMContentLoaded", () => {
        const inputMesExportar = document.getElementById("mesExportar");
        if(inputMesExportar) {
            // Obtenemos el mes actual en formato YYYY-MM
            const hoyStr = new Date().toISOString().slice(0, 7);
            // Bloqueamos los meses futuros
            inputMesExportar.setAttribute("max", hoyStr);
            // Dejamos el mes actual seleccionado por defecto
            inputMesExportar.value = hoyStr; 
        }
    });

    // =====================================================
    // EXPORTACIÓN DINÁMICA CON SELECTOR DE MES
    // =====================================================

    document.addEventListener("DOMContentLoaded", () => {
        const inputMesExportar = document.getElementById("mesExportar");
        if(inputMesExportar) {
            const hoyStr = new Date().toISOString().slice(0, 7);
            inputMesExportar.setAttribute("max", hoyStr);
            inputMesExportar.value = hoyStr; 
        }
    });

    // =====================================================
    // EXPORTACIÓN DINÁMICA (INGRESOS Y EGRESOS)
    // =====================================================
    document.getElementById("btnDescargarDatos")?.addEventListener("click", (e) => {
        e.preventDefault(); 

        const selectFormato = document.getElementById("formatoExportar");
        const mesExportar = document.getElementById("mesExportar").value;

        if (!mesExportar) {
            return mostrarToast("Por favor selecciona un mes para exportar", "error");
        }

        const formato = selectFormato.value;
        // activeTab ya sabe si estás en 'ingresos' o 'egresos'
        const tipoReporte = activeTab; 

        if (formato === "imprimir") {
            mostrarToast(`Preparando reporte de ${tipoReporte} para imprimir...`, "info");
        } else {
            mostrarToast(`Generando ${formato.toUpperCase()} de ${tipoReporte}...`, "info");
        }

        // Llama a la ruta exportar pasando el tipo correcto
        window.open(`/LOGIN_ORIGINAL/admin/facturacion/exportar?tipo=${tipoReporte}&formato=${formato}&mes=${mesExportar}`, '_blank');
    });