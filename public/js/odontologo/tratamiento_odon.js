document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.querySelector(".treatment-table tbody");
    const searchInput = document.getElementById("searchTreatment");
    const modalDetalle = document.getElementById("modal-detalle-historia");
    const closeModalBtn = document.getElementById("closeDetailModal");

    let historiaActualSeleccionada = null; // Guardará el item actual para los botones del modal

    function renderTable(data) {
        if (!tableBody) return;
        tableBody.innerHTML = "";
        
        // Actualizar KPIs de resumen
        const totalRegistrosEl = document.getElementById("total-registros");
        const pacientesUnicosEl = document.getElementById("pacientes-unicos");
        
        if (totalRegistrosEl) {
            totalRegistrosEl.textContent = data.length;
        }
        
        if (pacientesUnicosEl) {
            const uniquePatients = new Set(data.map(item => item.documento || item.id_paciente));
            pacientesUnicosEl.textContent = uniquePatients.size;
        }
        
        if (data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 25px; color: #94a3b8;">No hay historias clínicas registradas.</td></tr>`;
            return;
        }

        data.forEach(item => {
            const motivoCorto = item.motivo && item.motivo.length > 40 ? item.motivo.substring(0, 40) + '...' : (item.motivo || 'N/A');
            const diagCorto = item.diagnostico && item.diagnostico.length > 30 ? item.diagnostico.substring(0, 30) + '...' : (item.diagnostico || 'N/A');
            const procsCount = item.total_procedimientos_count || (item.procedimientos_lista ? item.procedimientos_lista.length : 1);
            const badgeProcs = procsCount > 1 
                ? `<span style="background: #eff6ff; color: #1d4ed8; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; margin-left: 5px; display: inline-block;">${procsCount} procs</span>` 
                : '';

            const row = document.createElement("tr");
            
            row.innerHTML = `
                <td style="color: #64748b;"><i class="fa-regular fa-calendar-check" style="color: #3b82f6; margin-right: 5px;"></i> ${item.fecha}</td>
                <td style="font-weight: bold; color: #1e293b;">${item.paciente}</td>
                <td>${item.documento}</td>
                <td style="color: #475569;">${motivoCorto}</td>
                <td><span class="cat-tag" style="background:#e0e7ff; color:#4338ca;">${diagCorto}</span>${badgeProcs}</td>
            `;

            // COLUMNA DE ACCIONES
            const tdAcciones = document.createElement("td");
            tdAcciones.style.display = "flex";
            tdAcciones.style.gap = "8px";

            // Botón VER (Muestra el modal con todos los procedimientos totales consolidados)
            const btnVer = document.createElement("button");
            btnVer.type = "button";
            btnVer.innerHTML = `<i class="fa-solid fa-eye"></i>`;
            btnVer.title = "Ver Detalle y Procedimientos Totales";
            btnVer.style.cssText = "background: #3b82f6; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;";
            btnVer.onclick = () => abrirModalDetalle(item);

            // Botón IMPRIMIR (desde la tabla)
            const btnImprimir = document.createElement("button");
            btnImprimir.type = "button";
            btnImprimir.innerHTML = `<i class="fa-solid fa-print"></i>`;
            btnImprimir.title = "Imprimir Historia Clínica";
            btnImprimir.style.cssText = "background: #64748b; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;";
            btnImprimir.onclick = () => imprimirHistoria(item);

            // Botón PDF (desde la tabla)
            const btnPdf = document.createElement("button");
            btnPdf.type = "button";
            btnPdf.innerHTML = `<i class="fa-solid fa-file-pdf"></i>`;
            btnPdf.title = "Descargar Historia Clínica en PDF";
            btnPdf.style.cssText = "background: #ef4444; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;";
            btnPdf.onclick = () => descargarPdf(item);

            tdAcciones.appendChild(btnVer);
            tdAcciones.appendChild(btnImprimir);
            tdAcciones.appendChild(btnPdf);
            row.appendChild(tdAcciones);
            
            tableBody.appendChild(row);
        });
    }

    function cargarHistoriales() {
        fetch('/odontologo/tratamientos/listar')
            .then(res => res.json())
            .then(data => renderTable(data))
            .catch(err => console.error("Error al cargar la tabla de historiales:", err));
    }

    if (searchInput) {
        searchInput.addEventListener("input", e => {
            const value = e.target.value.toLowerCase();
            document.querySelectorAll(".treatment-table tbody tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });
    }

    function prepararDatosEnPlantilla(item) {
        const elPaciente = document.getElementById("modal-paciente");
        if (elPaciente) elPaciente.textContent = item.paciente;

        const elDocumento = document.getElementById("modal-documento");
        if (elDocumento) elDocumento.textContent = item.documento;

        const elDoctor = document.getElementById("modal-doctor");
        if (elDoctor) elDoctor.textContent = item.doctor;
        
        const elFecha = document.getElementById("modal-fecha-impresion");
        if (elFecha) elFecha.textContent = item.fecha;

        const elTratamiento = document.getElementById("modal-tratamiento");
        if (elTratamiento) elTratamiento.textContent = item.tratamiento || "No se registró tratamiento específico.";

        const elMotivo = document.getElementById("modal-motivo");
        if (elMotivo) elMotivo.textContent = item.motivo || "No se registró motivo.";

        const elDiagnostico = document.getElementById("modal-diagnostico");
        if (elDiagnostico) elDiagnostico.textContent = item.diagnostico || "No se registró diagnóstico.";

        const elDientesTratados = document.getElementById("modal-dientes-tratados");
        if (elDientesTratados) elDientesTratados.textContent = item.dientes_tratados || "General / No especificado";

        // Generar Iniciales del Avatar
        const elAvatar = document.getElementById("avatarInitialsModal");
        if (elAvatar && item.paciente) {
            const partes = item.paciente.trim().split(" ");
            elAvatar.textContent = partes.length >= 2 ? (partes[0][0] + partes[1][0]).toUpperCase() : partes[0].substring(0, 2).toUpperCase();
        }

        // Llenar tabla detallada de Procedimientos Totales Realizados
        const tbodyProcs = document.getElementById("tbody-procedimientos-modal");
        const badgeTotal = document.getElementById("badge-total-procs");
        if (tbodyProcs) {
            tbodyProcs.innerHTML = "";
            const lista = item.procedimientos_lista || [];
            
            if (lista.length > 0) {
                if (badgeTotal) badgeTotal.textContent = `${lista.length} procedimiento${lista.length === 1 ? '' : 's'}`;
                lista.forEach(proc => {
                    const tr = document.createElement("tr");
                    tr.style.borderBottom = "1px solid #f1f5f9";
                    const piezaBadge = proc.pieza && proc.pieza !== 'General'
                        ? `<span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Pieza ${proc.pieza}</span>`
                        : `<span style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">${proc.pieza || 'General'}</span>`;

                    tr.innerHTML = `
                        <td style="padding: 10px 12px; font-weight: 600; color: #1e293b;">${proc.nombre}</td>
                        <td style="padding: 10px 12px; text-align: center; font-weight: 500;">${proc.cantidad || 1}</td>
                        <td style="padding: 10px 12px; text-align: center;">${piezaBadge}</td>
                        <td style="padding: 10px 12px; color: #475569; font-size: 0.9rem;">${proc.observaciones || proc.diagnostico || 'Atención realizada satisfactoriamente'}</td>
                    `;
                    tbodyProcs.appendChild(tr);
                });
            } else {
                // Fallback con el campo tratamiento separado por comas
                const procsNombres = (item.tratamiento || '').split(',').map(s => s.trim()).filter(Boolean);
                if (badgeTotal) badgeTotal.textContent = `${procsNombres.length} procedimiento${procsNombres.length === 1 ? '' : 's'}`;
                
                if (procsNombres.length > 0) {
                    procsNombres.forEach(nombre => {
                        const tr = document.createElement("tr");
                        tr.style.borderBottom = "1px solid #f1f5f9";
                        tr.innerHTML = `
                            <td style="padding: 10px 12px; font-weight: 600; color: #1e293b;">${nombre}</td>
                            <td style="padding: 10px 12px; text-align: center;">1</td>
                            <td style="padding: 10px 12px; text-align: center;"><span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">${item.dientes_tratados || 'General'}</span></td>
                            <td style="padding: 10px 12px; color: #475569;">${item.diagnostico || 'Atención realizada'}</td>
                        `;
                        tbodyProcs.appendChild(tr);
                    });
                } else {
                    tbodyProcs.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 15px; color: #94a3b8;">No se registraron procedimientos detallados.</td></tr>`;
                }
            }
        }
    }

    function abrirModalDetalle(item) {
        historiaActualSeleccionada = item;
        prepararDatosEnPlantilla(item);
        if (modalDetalle) {
            modalDetalle.style.display = "flex";
        }
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (modalDetalle) modalDetalle.style.display = "none";
        });
    }

    if (modalDetalle) {
        modalDetalle.addEventListener("click", (e) => {
            if (e.target === modalDetalle) {
                modalDetalle.style.display = "none";
            }
        });
    }

    // ==========================================
    // LÓGICA DIRECTA DE IMPRESIÓN Y EXPORTACIÓN A PDF
    // ==========================================

    function descargarPdf(item) {
        prepararDatosEnPlantilla(item);
        const elemento = document.getElementById("print-area-historia");
        if (!elemento) return;

        const docId = item.documento || "Paciente";
        const fechaArchivo = (item.fecha_raw ? item.fecha_raw.split(" ")[0] : "consulta").replace(/[^a-zA-Z0-9]/g, "_");

        const opt = {
            margin:       [8, 8, 8, 8],
            filename:     `Historia_Clinica_${docId}_${fechaArchivo}.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, letterRendering: true, scrollY: 0 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
        };

        if (typeof html2pdf === "function") {
            html2pdf().set(opt).from(elemento).save();
        } else {
            // Fallback si no está cargada la librería externa
            window.print();
        }
    }

    function imprimirHistoria(item) {
        prepararDatosEnPlantilla(item);
        const elemento = document.getElementById("print-area-historia");
        if (!elemento) return;

        const ventana = window.open('', '_blank', 'width=900,height=750');
        if (!ventana) {
            // Fallback si el navegador bloquea la ventana emergente
            window.print();
            return;
        }

        ventana.document.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Historia Clínica - ${item.paciente || ''}</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                        margin: 20px;
                        color: #1e293b;
                        background: #ffffff;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    @media print {
                        body {
                            margin: 0;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                    }
                </style>
            </head>
            <body>
                <div style="max-width: 800px; margin: 0 auto;">
                    ${elemento.innerHTML}
                </div>
                <script>
                    window.onload = function() {
                        window.focus();
                        window.print();
                        setTimeout(function() { window.close(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        ventana.document.close();
    }

    // Botones dentro del Modal
    const btnImprimirModal = document.getElementById("btn-imprimir-modal");
    if (btnImprimirModal) {
        btnImprimirModal.addEventListener("click", () => {
            if (historiaActualSeleccionada) {
                imprimirHistoria(historiaActualSeleccionada);
            }
        });
    }

    const btnPdfModal = document.getElementById("btn-pdf-modal");
    if (btnPdfModal) {
        btnPdfModal.addEventListener("click", () => {
            if (historiaActualSeleccionada) {
                descargarPdf(historiaActualSeleccionada);
            }
        });
    }

    // Inicializar carga de tabla
    cargarHistoriales();
});
