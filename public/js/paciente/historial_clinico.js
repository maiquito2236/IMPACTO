$(document).ready(function () {
    let paginaActual = 1;

    // Componentes de escucha de filtrado
    $('#registrosPorPagina').on('change', function() { paginaActual = 1; procesarFiltrosTabla(); });
    $('#inputBuscarHistorial').on('keyup', function() { paginaActual = 1; procesarFiltrosTabla(); });

    $('#selectOrdenHistorial').on('change', function() {
        procesarFiltrosTabla();
    });

    function procesarFiltrosTabla() {
        const $contenedor = $('#tbodyHistorial');
        const $items = $('.historial-row');
        const textoBusqueda = $('#inputBuscarHistorial').val().toLowerCase().trim();
        const limitePorPagina = parseInt($('#registrosPorPagina').val());
        const orden = $('#selectOrdenHistorial').val(); 

        if ($items.length === 0) return;

        const itemsOrdenados = $items.toArray().sort(function(a, b) {
            const fechaA = parseInt($(a).attr('data-fecha'));
            const fechaB = parseInt($(b).attr('data-fecha'));
            return orden === 'recientes' ? (fechaB - fechaA) : (fechaA - fechaB);
        });

        $contenedor.append(itemsOrdenados);

        let itemsFiltrados = itemsOrdenados.filter(function(item) {
            const contenidoBusqueda = $(item).attr('data-search');
            if (textoBusqueda === '') return true;
            return contenidoBusqueda.indexOf(textoBusqueda) !== -1;
        });

        $items.addClass('d-none');

        const totalRegistrosFiltrados = itemsFiltrados.length;
        const totalPaginas = Math.ceil(totalRegistrosFiltrados / limitePorPagina) || 1;

        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const indiceInicio = (paginaActual - 1) * limitePorPagina;
        const indiceFin = Math.min(indiceInicio + limitePorPagina, totalRegistrosFiltrados);

        for (let i = indiceInicio; i < indiceFin; i++) {
            $(itemsFiltrados[i]).removeClass('d-none');
        }

        renderizarControlesPaginacion(totalPaginas, totalRegistrosFiltrados, indiceInicio, indiceFin);
    }

    function renderizarControlesPaginacion(totalPaginas, totalFiltrados, inicio, fin) {
        const $ul = $('#ulPaginacion');
        $ul.empty();

        if (totalFiltrados === 0) {
            $('#txtContadorPaginacion').text('Mostrando 0 a 0 de 0 registros');
            return;
        }

        $('#txtContadorPaginacion').text(`Mostrando ${inicio + 1} a ${fin} de ${totalFiltrados} registros`);

        const $btnPrev = $(`<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}"><a class="page-link text-dark border-0 bg-light rounded-2" href="#">←</a></li>`);
        $btnPrev.on('click', function(e) { e.preventDefault(); if(paginaActual > 1) { paginaActual--; procesarFiltrosTabla(); } });
        $ul.append($btnPrev);

        for (let i = 1; i <= totalPaginas; i++) {
            const $pageItem = $(`<li class="page-item ${i === paginaActual ? 'active' : ''}"></li>`);
            const $pageLink = $(`<a class="page-link border-0 rounded-2 fw-bold" href="#">${i}</a>`);
            
            if (i === paginaActual) {
                $pageLink.css({ 'background-color': '#0b57d0', 'color': '#ffffff', 'padding': '6px 12px' });
            } else {
                $pageLink.addClass('text-dark bg-light').css({ 'padding': '6px 12px' });
            }

            $pageItem.on('click', function(e) { e.preventDefault(); paginaActual = i; procesarFiltrosTabla(); });
            $pageItem.append($pageLink);
            $ul.append($pageItem);
        }

        const $btnNext = $(`<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}"><a class="page-link text-dark border-0 bg-light rounded-2" href="#">→</a></li>`);
        $btnNext.on('click', function(e) { e.preventDefault(); if(paginaActual < totalPaginas) { paginaActual++; procesarFiltrosTabla(); } });
        $ul.append($btnNext);
    }

    procesarFiltrosTabla();

    // ==========================================
    // LÓGICA DEL ODONTOGRAMA (MODAL)
    // ==========================================

    const pacienteIdHidden = document.getElementById("pacienteIdHidden");
    const pacienteId = pacienteIdHidden ? pacienteIdHidden.value : 0;

    const elNombre = document.getElementById("historial-nombre-paciente");
    const elMeta = document.getElementById("historial-meta-paciente");
    const elAvatar = document.getElementById("avatarInitials");
    
    const elAlergias = document.getElementById("resumen-alergias");
    const elEnfermedades = document.getElementById("resumen-enfermedades");
    const elMedicamentos = document.getElementById("resumen-medicamentos");
    
    const elMotivoHoy = document.getElementById("resumen-cita-hoy");
    const elProximaCita = document.getElementById("resumen-proxima-cita");

    const toothModal = document.getElementById("toothModal");
    const closeModal = document.getElementById("closeModal");

    function getInitials(name) {
        if (!name) return "--";
        const parts = name.trim().split(" ");
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    function cargarDatosOdontograma(idCitaEspecifica, nombreDoctorListado, fechaIso) {
        // Limpiamos los dientes primero
        document.querySelectorAll(".tooth-shape").forEach(shape => {
            shape.style.backgroundColor = '#f8fafc';
            shape.style.borderColor = '#cbd5e1';
            shape.parentElement.classList.remove('has-record');
        });
        
        // El endpoint viejo cargaba de forma global, usaremos el mismo para alertas y paciente,
        // pero podemos pasarle id_cita si el backend lo soporta, o asumimos que devuelve el estado global
        let url = `/LOGIN_ORIGINAL/index.php?action=paciente/historial_clinico/datos_odontograma&paciente_id=${pacienteId}`;
        if (fechaIso) {
            url += `&target_date=${fechaIso}`;
        }
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.paciente) {
                    if (elNombre) elNombre.textContent = data.paciente.paciente_nombre || "Desconocido";
                    if (elAvatar) elAvatar.textContent = getInitials(data.paciente.paciente_nombre);
                    if (elMeta) elMeta.textContent = `Identificación: ${data.paciente.documento || "--"} • Doctor Tratante: ${nombreDoctorListado || "--"}`;
                }

                if (data.alertas) {
                    if (elAlergias) elAlergias.textContent = data.alertas.ALERGIAS || "Ninguna";
                    if (elEnfermedades) elEnfermedades.textContent = data.alertas.ENFERMEDADES || "Ninguna";
                    if (elMedicamentos) elMedicamentos.textContent = data.alertas.MEDICAMENTOS || "Ninguno";
                }

                // Hacemos un fetch a la cita especifica para el resumen-cita-hoy
                const formData = new FormData();
                formData.append('id_cita', idCitaEspecifica);
                fetch('/LOGIN_ORIGINAL/paciente/cita/detalle-historial-ajax', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(citaRes => {
                    if(citaRes.success) {
                        if(elMotivoHoy) elMotivoHoy.innerText = citaRes.data.TRATAMIENTOS_ODONTOGRAMA || citaRes.data.TRATAMIENTO || "Valoración";
                        if(elProximaCita) elProximaCita.textContent = citaRes.data.RECOMENDACIONES || "Sin recomendaciones específicas";
                    }
                });


                if (data.dientes) {
                    const teethItems = document.querySelectorAll(".tooth-item");
                    const printList = document.getElementById("print-tooth-list");
                    if (printList) printList.innerHTML = ""; 
                    
                    teethItems.forEach(item => {
                        const toothNum = item.getAttribute("data-tooth-number");
                        if (data.dientes[toothNum]) {
                            const toothData = data.dientes[toothNum];
                            
                            const shape = item.querySelector(".tooth-shape");
                            if (shape) {
                                shape.style.backgroundColor = toothData.estado === 'HECHO' ? '#2bc48a' : '#f59e0b';
                                shape.style.borderColor = toothData.estado === 'HECHO' ? '#059669' : '#d97706';
                            }
                            
                            item.classList.add("has-record");
                            
                            // Remover eventos viejos clonando
                            const clone = item.cloneNode(true);
                            item.parentNode.replaceChild(clone, item);
                            
                            clone.addEventListener("click", () => {
                                document.getElementById("modalToothTitle").textContent = "Pieza Dental " + toothNum;
                                document.getElementById("toothName").textContent = toothData.nombre;
                                document.getElementById("toothStatus").textContent = toothData.estado;
                                document.getElementById("toothTreatment").textContent = toothData.tratamiento;
                                document.getElementById("toothDoctor").textContent = toothData.doctor;
                                document.getElementById("toothDate").textContent = toothData.fecha;
                                document.getElementById("toothNotes").textContent = toothData.notas || "Ninguna";
                                
                                toothModal.style.display = "flex";
                            });
                            
                            if (printList) {
                                const estadoClase = toothData.estado === 'HECHO' ? 'hecho' : 'proceso';
                                const colorIcono = toothData.estado === 'HECHO' ? '#10b981' : '#f59e0b';
                                
                                const detailHtml = `
                                    <div class="tooth-detail-card ${estadoClase}">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                                            <div>
                                                <h4 style="margin: 0 0 4px 0; color: #0f172a; font-size: 1.1rem; font-weight: bold;">
                                                    Pieza ${toothNum} <span style="color: #64748b; font-weight: normal; font-size: 0.95rem;">- ${toothData.nombre}</span>
                                                </h4>
                                                <span style="background: ${toothData.estado === 'HECHO' ? '#ecfdf5' : '#fffbeb'}; color: ${colorIcono}; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; border: 1px solid ${toothData.estado === 'HECHO' ? '#a7f3d0' : '#fde68a'};">
                                                    <i class="fa-solid fa-circle-check me-1"></i> ${toothData.estado}
                                                </span>
                                            </div>
                                            <div style="text-align: right;">
                                                <span style="display: block; color: #0f172a; font-weight: 600; font-size: 0.9rem;">Dr(a). ${toothData.doctor}</span>
                                                <span style="color: #64748b; font-size: 0.8rem;"><i class="fa-regular fa-calendar me-1"></i> ${toothData.fecha}</span>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 10px;">
                                            <strong style="color: #334155; font-size: 0.9rem; display: block; margin-bottom: 4px;">Procedimiento Realizado:</strong>
                                            <p style="margin: 0; font-size: 0.95rem; color: #0f172a; background: #f8fafc; padding: 8px; border-radius: 6px;">${toothData.tratamiento}</p>
                                        </div>
                                        <div>
                                            <strong style="color: #334155; font-size: 0.9rem; display: block; margin-bottom: 4px;">Observaciones:</strong>
                                            <p style="margin: 0; font-size: 0.9rem; color: #475569;">${toothData.notas || "Ninguna observación adicional."}</p>
                                        </div>
                                    </div>
                                `;
                                printList.innerHTML += detailHtml;
                            }
                        }
                    });
                }
            })
            .catch(err => {
                console.error("Error cargando odontograma:", err);
            });
    }

    // Al hacer clic en "Ver Detalle" en la tabla
    $(document).on('click', '.btn-ver-detalle', function(e) {
        e.preventDefault();
        const idCita = $(this).data('id');
        const docNombre = $(this).data('doc');
        const fechaIso = $(this).data('fecha-iso');
        
        cargarDatosOdontograma(idCita, docNombre, fechaIso);
        
        // Guardamos el ID de la cita en los botones de imprimir para que exporten esa historia en concreto
        $('#btn-imprimir').data('id', idCita);
        $('#btn-exportar-pdf').data('id', idCita);
        
        // Abrimos el modal grande de la historia clinica
        $('#modalOdontogramaHistoria').modal('show');
    });

    if (closeModal) {
        closeModal.addEventListener("click", () => {
            toothModal.style.display = "none";
        });
    }

    const btnImprimir = document.getElementById("btn-imprimir");
    const btnExportarPdf = document.getElementById("btn-exportar-pdf");

    if (btnImprimir) {
        btnImprimir.addEventListener("click", () => {
            const modalContent = document.getElementById("printable-modal-content");
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = modalContent.outerHTML;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload(); // Para re-atar eventos de JQuery
        });
    }

    if (btnExportarPdf) {
        btnExportarPdf.addEventListener("click", () => {
            const docId = document.getElementById("historial-meta-paciente")?.textContent.split("•")[0].replace("Identificación:", "").trim() || "000000";
            const opt = {
                margin:       [0.5, 0.5, 0.5, 0.5],
                filename:     `Historia_Clinica_${docId}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            const modalContent = document.getElementById("printable-modal-content");
            const printHeader = modalContent.querySelector("#print-header");
            const acciones = modalContent.querySelector("#botones-acciones");

            if (printHeader) printHeader.style.display = "block";
            if (acciones) acciones.style.display = "none";
            
            modalContent.classList.add("pdf-mode");

            html2pdf().set(opt).from(modalContent).save().then(() => {
                if (printHeader) printHeader.style.display = "none";
                if (acciones) acciones.style.display = "flex";
                modalContent.classList.remove("pdf-mode");
            });
        });
    }
});
