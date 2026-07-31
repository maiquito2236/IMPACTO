document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.querySelector(".treatment-table tbody");
    const searchInput = document.getElementById("searchTreatment");
    const modalDetalle = document.getElementById("modal-detalle-historia");
    const closeModalBtn = document.getElementById("closeDetailModal");

    let historiaActualSeleccionada = null; // Guardará el item actual para los botones del modal

    function renderTable(data) {
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
        
        if(data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay historias clínicas registradas.</td></tr>`;
            return;
        }

        data.forEach(item => {
            const motivoCorto = item.motivo && item.motivo.length > 40 ? item.motivo.substring(0, 40) + '...' : (item.motivo || 'N/A');
            const diagCorto = item.diagnostico && item.diagnostico.length > 30 ? item.diagnostico.substring(0, 30) + '...' : (item.diagnostico || 'N/A');

            const row = document.createElement("tr");
            
            row.innerHTML = `
                <td style="color: #64748b;"><i class="fa-regular fa-calendar-check" style="color: #3b82f6; margin-right: 5px;"></i> ${item.fecha}</td>
                <td style="font-weight: bold; color: #1e293b;">${item.paciente}</td>
                <td>${item.documento}</td>
                <td style="color: #475569;">${motivoCorto}</td>
                <td><span class="cat-tag" style="background:#e0e7ff; color:#4338ca;">${diagCorto}</span></td>
            `;

            // COLUMNA DE ACCIONES
            const tdAcciones = document.createElement("td");
            tdAcciones.style.display = "flex";
            tdAcciones.style.gap = "8px";

            // Botón VER (Muestra el modal resumen sin redirigir)
            const btnVer = document.createElement("button");
            btnVer.innerHTML = `<i class="fa-solid fa-eye"></i>`;
            btnVer.title = "Ver Resumen General";
            btnVer.style.cssText = "background: #3b82f6; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;";
            btnVer.onclick = () => abrirModalDetalle(item);

            // Función para cargar el historial en un iframe invisible y ejecutar acción
            const ejecutarSilencioso = (accion) => {
                let iframe = document.getElementById("hidden-print-iframe");
                if (iframe) iframe.remove();
                
                iframe = document.createElement("iframe");
                iframe.id = "hidden-print-iframe";
                iframe.style.position = "absolute";
                iframe.style.width = "0";
                iframe.style.height = "0";
                iframe.style.border = "none";
                iframe.src = 'odontologo/historial-clinico?paciente_id=' + item.id_paciente + '&' + accion + '=true&target_date=' + encodeURIComponent(item.fecha_raw.split(' ')[0]);
                document.body.appendChild(iframe);
            };

            // Botón IMPRIMIR (desde la tabla)
            const btnImprimir = document.createElement("button");
            btnImprimir.innerHTML = `<i class="fa-solid fa-print"></i>`;
            btnImprimir.title = "Imprimir Reporte";
            btnImprimir.style.cssText = "background: #64748b; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;";
            btnImprimir.onclick = () => ejecutarSilencioso('print');

            // Botón PDF (desde la tabla)
            const btnPdf = document.createElement("button");
            btnPdf.innerHTML = `<i class="fa-solid fa-file-pdf"></i>`;
            btnPdf.title = "Descargar PDF";
            btnPdf.style.cssText = "background: #ef4444; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;";
            btnPdf.onclick = () => ejecutarSilencioso('pdf');

            tdAcciones.appendChild(btnVer);
            tdAcciones.appendChild(btnImprimir);
            tdAcciones.appendChild(btnPdf);
            row.appendChild(tdAcciones);
            
            tableBody.appendChild(row);
        });
    }

    function cargarHistoriales() {
        fetch('index.php?action=odontologo/tratamientos/listar')
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

        // Generar Iniciales del Avatar (Igual que en Historial Clínico)
        const elAvatar = document.getElementById("avatarInitialsModal");
        if (elAvatar && item.paciente) {
            const partes = item.paciente.trim().split(" ");
            elAvatar.textContent = partes.length >= 2 ? (partes[0][0] + partes[1][0]).toUpperCase() : partes[0].substring(0, 2).toUpperCase();
        }
    }

    function abrirModalDetalle(item) {
        historiaActualSeleccionada = item;
        prepararDatosEnPlantilla(item);
        modalDetalle.style.display = "flex";
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener("click", (e) => {
            e.preventDefault();
            modalDetalle.style.display = "none";
        });
    }

    // ==========================================
    // LÓGICA DE IMPRESIÓN Y EXPORTACIÓN A PDF
    // Ahora todo se maneja de forma remota (invisible)
    // ==========================================

    // Botones dentro del Modal
    const btnImprimirModal = document.getElementById("btn-imprimir-modal");
    if (btnImprimirModal) {
        btnImprimirModal.addEventListener("click", () => {
            if (historiaActualSeleccionada) {
                let iframe = document.getElementById("hidden-print-iframe");
                if (iframe) iframe.remove();
                iframe = document.createElement("iframe");
                iframe.id = "hidden-print-iframe";
                iframe.style.position = "absolute";
                iframe.style.width = "0";
                iframe.style.height = "0";
                iframe.style.border = "none";
                iframe.src = 'odontologo/historial-clinico?paciente_id=' + historiaActualSeleccionada.id_paciente + '&print=true';
                document.body.appendChild(iframe);
            }
        });
    }

    const btnPdfModal = document.getElementById("btn-pdf-modal");
    if (btnPdfModal) {
        btnPdfModal.addEventListener("click", () => {
            if (historiaActualSeleccionada) {
                let iframe = document.getElementById("hidden-print-iframe");
                if (iframe) iframe.remove();
                iframe = document.createElement("iframe");
                iframe.id = "hidden-print-iframe";
                iframe.style.position = "absolute";
                iframe.style.width = "0";
                iframe.style.height = "0";
                iframe.style.border = "none";
                iframe.src = 'odontologo/historial-clinico?paciente_id=' + historiaActualSeleccionada.id_paciente + '&pdf=true';
                document.body.appendChild(iframe);
            }
        });
    }

    cargarHistoriales();
});