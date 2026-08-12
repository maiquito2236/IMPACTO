document.addEventListener("DOMContentLoaded", () => {
    const pacienteIdHidden = document.getElementById("pacienteIdHidden");
    if (!pacienteIdHidden) return;
    
    const pacienteId = pacienteIdHidden.value;

    // Elementos del DOM
    const elNombre = document.getElementById("historial-nombre-paciente");
    const elMeta = document.getElementById("historial-meta-paciente");
    const elAvatar = document.getElementById("avatarInitials");
    
    const elAlergias = document.getElementById("resumen-alergias");
    const elEnfermedades = document.getElementById("resumen-enfermedades");
    const elMedicamentos = document.getElementById("resumen-medicamentos");
    
    const elMotivoHoy = document.getElementById("resumen-cita-hoy");
    const elProximaCita = document.getElementById("resumen-proxima-cita");
    
    const timelineContainer = document.getElementById("timeline-container");

    // Modal del Odontograma
    const toothModal = document.getElementById("toothModal");
    const closeModal = document.getElementById("closeModal");

    // Función para obtener iniciales
    function getInitials(name) {
        if (!name) return "--";
        const parts = name.trim().split(" ");
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    // 1. CARGAR DATOS GENERALES Y ODONTOGRAMA
    function cargarOdontograma(targetDate = null) {
        let url = `index.php?action=odontologo/historial_clinico/datos_odontograma&paciente_id=${pacienteId}`;
        if (targetDate) {
            url += `&target_date=${targetDate}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                // Demográficos
                if (data.paciente) {
                    if (elNombre) elNombre.textContent = data.paciente.paciente_nombre || "Desconocido";
                    if (elAvatar) elAvatar.textContent = getInitials(data.paciente.paciente_nombre);
                    if (elMeta) elMeta.textContent = `Identificación: ${data.paciente.documento || "--"} • Doctor Asignado: ${data.doctor_sesion || "--"}`;
                }

                // Alertas
                if (data.alertas) {
                    if (elAlergias) elAlergias.textContent = data.alertas.ALERGIAS || "Ninguna";
                    if (elEnfermedades) elEnfermedades.textContent = data.alertas.ENFERMEDADES || "Ninguna";
                    if (elMedicamentos) elMedicamentos.textContent = data.alertas.MEDICAMENTOS || "Ninguno";
                }

                // Citas
                if (data.citas) {
                    if (elMotivoHoy) elMotivoHoy.textContent = data.citas.sesion_hoy || "Sin registro";
                    if (elProximaCita) elProximaCita.textContent = data.citas.proxima_cita || "No programada";
                }

                // Odontograma
                if (data.dientes) {
                    const teethItems = document.querySelectorAll(".tooth-item");
                    const printList = document.getElementById("print-tooth-list");
                    if (printList) printList.innerHTML = ""; // Limpiar lista
                    
                    // Resetear estado previo
                    teethItems.forEach(item => {
                        const shape = item.querySelector(".tooth-shape");
                        if (shape) {
                            shape.style.backgroundColor = '#ffffff';
                            shape.style.borderColor = '#cbd5e1';
                        }
                        item.classList.remove("has-record");
                        // Remover event listeners clonando
                        const newItem = item.cloneNode(true);
                        item.parentNode.replaceChild(newItem, item);
                    });

                    // Volver a seleccionar los elementos nuevos
                    const newTeethItems = document.querySelectorAll(".tooth-item");

                    newTeethItems.forEach(item => {
                        const toothNum = item.getAttribute("data-tooth-number");
                        if (data.dientes[toothNum]) {
                            const toothData = data.dientes[toothNum];
                            
                            const shape = item.querySelector(".tooth-shape");
                            if (shape) {
                                if (toothData.es_nuevo) {
                                    // Nuevo / Actual (Verde o Amarillo si no está hecho)
                                    shape.style.backgroundColor = toothData.estado === 'HECHO' ? '#2bc48a' : '#f59e0b';
                                    shape.style.borderColor = toothData.estado === 'HECHO' ? '#059669' : '#d97706';
                                } else {
                                    // Procedimiento anterior (Azul)
                                    shape.style.backgroundColor = '#3b82f6';
                                    shape.style.borderColor = '#2563eb';
                                }
                            }
                            
                            item.classList.add("has-record");
                            
                            // Click para abrir modal con info de ese diente
                            item.addEventListener("click", () => {
                                document.getElementById("modalToothTitle").textContent = "Diente " + toothNum;
                                document.getElementById("toothName").textContent = toothData.nombre;
                                document.getElementById("toothStatus").textContent = toothData.estado;
                                document.getElementById("toothTreatment").textContent = toothData.tratamiento;
                                document.getElementById("toothDoctor").textContent = toothData.doctor;
                                document.getElementById("toothDate").textContent = toothData.fecha;
                                document.getElementById("toothNotes").textContent = toothData.notas || "Ninguna";
                                
                                toothModal.style.display = "flex";
                            });
                            
                            // Agregar detalle a la lista para impresión/PDF
                            if (printList) {
                                const pastText = !toothData.es_nuevo ? ' <span style="color:#3b82f6;font-size:0.8rem;">(Procedimiento Anterior)</span>' : '';
                                const detailHtml = `
                                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 8px;">
                                        <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 1rem;">Pieza Dental ${toothNum} - ${toothData.nombre}${pastText}</h4>
                                        <p style="margin: 3px 0; font-size: 0.85rem;"><strong>Estado:</strong> <span style="color: ${toothData.estado === 'HECHO' ? '#059669' : '#d97706'}">${toothData.estado}</span></p>
                                        <p style="margin: 3px 0; font-size: 0.85rem;"><strong>Tratamiento:</strong> ${toothData.tratamiento}</p>
                                        <p style="margin: 3px 0; font-size: 0.85rem;"><strong>Doctor:</strong> ${toothData.doctor}</p>
                                        <p style="margin: 3px 0; font-size: 0.85rem;"><strong>Fecha:</strong> ${toothData.fecha}</p>
                                        <p style="margin: 3px 0; font-size: 0.85rem;"><strong>Notas:</strong> ${toothData.notas || "Ninguna"}</p>
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
                if (elNombre) elNombre.textContent = "Error al cargar datos";
            });
    }

      // Inicializamos con el estado global
      const urlParamsInit = new URLSearchParams(window.location.search);
      const initialTargetDate = urlParamsInit.get('target_date');
      cargarOdontograma(initialTargetDate);

    // CERRAR MODAL
    if (closeModal) {
        closeModal.addEventListener("click", () => {
            toothModal.style.display = "none";
        });
    }

    // 2. CARGAR TIMELINE
    fetch(`index.php?action=odontologo/historial_clinico/datos_timeline&paciente_id=${pacienteId}`)
        .then(res => res.json())
        .then(evoluciones => {
            if (!timelineContainer) return;
            timelineContainer.innerHTML = "";

            if (!evoluciones || evoluciones.length === 0) {
                timelineContainer.innerHTML = '<p style="color: #6b7280; text-align: center; padding: 20px;">No hay evoluciones clínicas registradas.</p>';
                return;
            }

            const wrapper = document.createElement("div");
            wrapper.className = "timeline";
            
            evoluciones.forEach(evo => {
                const item = document.createElement("div");
                item.className = "timeline-item main-highlight";
                
                item.innerHTML = `
                    <div class="timeline-meta">
                        <span class="time">${evo.hora || '--'}</span>
                        <span class="date">${evo.fecha || '--'}</span>
                    </div>
                    <div class="timeline-point"></div>
                    <div class="timeline-body">
                        <h4 style="margin:0; color:#1e293b; font-size:1.05rem;">${evo.NOMBRE_PROCEDIMIENTO || evo.motivo || 'Evolución'}</h4>
                        <p style="margin:5px 0 0 0; color:#475569; font-size:0.9rem;">
                            <strong>Piezas:</strong> ${evo.piezas || 'Gral'}<br>
                            <strong>Diagnóstico:</strong> ${evo.diagnostico || 'N/A'}<br>
                            <strong>Notas:</strong> ${evo.observaciones || 'Ninguna'}
                        </p>
                        <button class="btn-ver-estado" data-fecha="${evo.fecha_iso}" style="margin-top: 10px; background-color: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-clock-rotate-left"></i> Ver estado en esta fecha
                        </button>
                    </div>
                `;
                wrapper.appendChild(item);
            });
            
            timelineContainer.appendChild(wrapper);

            // Escuchar clics en los botones de "Ver estado"
            document.querySelectorAll('.btn-ver-estado').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const btnElement = e.currentTarget;
                    const fechaIso = btnElement.getAttribute('data-fecha');
                    
                    // Actualizar el título de la sección
                    const sectionTitle = document.querySelector('.odontogram-section h3');
                    if (sectionTitle) {
                        const dateText = btnElement.parentElement.parentElement.querySelector('.date').textContent;
                        sectionTitle.innerHTML = `2D odontograma adulto <span style="color:#3b82f6; font-size: 0.9rem; margin-left: 10px;">(Estado al ${dateText})</span>`;
                    }
                    
                    // Resaltar tarjeta seleccionada
                    document.querySelectorAll('.timeline-item').forEach(i => {
                        i.style.backgroundColor = 'transparent';
                        i.style.borderRadius = '0';
                        i.style.padding = '0';
                    });
                    
                    const card = btnElement.parentElement.parentElement;
                    card.style.backgroundColor = '#f1f5f9';
                    card.style.borderRadius = '8px';
                    card.style.padding = '10px';

                    cargarOdontograma(fechaIso);
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        })
        .catch(err => {
            console.error("Error cargando timeline:", err);
            if (timelineContainer) timelineContainer.innerHTML = '<p style="color: #ef4444; text-align: center;">Error al cargar evoluciones.</p>';
        });

    // LOGICA DE IMPRESION Y PDF
    const btnImprimir = document.getElementById("btn-imprimir");
    const btnExportarPdf = document.getElementById("btn-exportar-pdf");

    if (btnImprimir) {
        btnImprimir.addEventListener("click", () => {
            window.print();
        });
    }

    // Escuchar botón Imprimir (PDF)
    const btnPdf = document.getElementById("btn-exportar-pdf");
    if (btnPdf) {
        btnPdf.addEventListener("click", () => {
            const docId = document.getElementById("historial-meta-paciente")?.textContent.split("•")[0].replace("Identificación:", "").trim() || "000000";
            const opt = {
                margin:       [0.5, 0.5, 0.5, 0.5],
                filename:     `Historia_Clinica_${docId}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            const printHeader = document.getElementById("print-header");
            const acciones = document.getElementById("botones-acciones");

            if (printHeader) printHeader.style.display = "block";
            if (acciones) acciones.style.display = "none";
            
            document.body.classList.add("pdf-mode");

            html2pdf().set(opt).from(document.querySelector('.main-content')).save().then(() => {
                if (printHeader) printHeader.style.display = "none";
                if (acciones) acciones.style.display = "flex";
                document.body.classList.remove("pdf-mode");
            });
        });
    }
    
    // Auto-print or auto-pdf if requested by URL
    setTimeout(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true' && btnImprimir) {
            btnImprimir.click();
        } else if (urlParams.get('pdf') === 'true' && btnPdf) {
            btnPdf.click();
        }
    }, 1000);

     // Escuchar botón Guardar y Enviar PDF
    const btnGuardarEnviar = document.getElementById("btn-guardar-enviar-historial");
    if (btnGuardarEnviar) {
        btnGuardarEnviar.addEventListener("click", () => {
            const citaId = btnGuardarEnviar.getAttribute("data-cita-id");
            const pacienteId = btnGuardarEnviar.getAttribute("data-paciente-id");
            
            // Cambiar estado del botón
            const originalText = btnGuardarEnviar.innerHTML;
            btnGuardarEnviar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando y Enviando...';
            btnGuardarEnviar.disabled = true;
            const docId = document.getElementById("historial-meta-paciente")?.textContent.split("•")[0].replace("Identificación:", "").trim() || "000000";
            const opt = {
                margin:       [0.5, 0.5, 0.5, 0.5],
                filename:     `Historia_Clinica_${docId}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };
            const printHeader = document.getElementById("print-header");
            const acciones = document.getElementById("botones-acciones");
            if (printHeader) printHeader.style.display = "block";
            if (acciones) acciones.style.display = "none";
            document.body.classList.add("pdf-mode");
            // Generar PDF como Blob (en memoria)
            html2pdf().set(opt).from(document.querySelector('.main-content')).outputPdf('blob').then(function(pdfBlob) {
                if (printHeader) printHeader.style.display = "none";
                if (acciones) acciones.style.display = "flex";
                document.body.classList.remove("pdf-mode");
                // Enviar por Fetch API
                const formData = new FormData();
                formData.append('pdf', pdfBlob, `Historia_Clinica_${docId}.pdf`);
                formData.append('cita_id', citaId);
                formData.append('paciente_id', pacienteId);
                fetch('index.php?action=odontologo/historial_clinico/guardar_y_enviar_pdf', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Redirigir a tratamientos
                        window.location.href = 'index.php?action=odontologo/tratamientos';
                    } else {
                        alert(data.message || 'Ocurrió un error al procesar la solicitud.');
                        btnGuardarEnviar.innerHTML = originalText;
                        btnGuardarEnviar.disabled = false;
                        window.location.href = 'index.php?action=odontologo/tratamientos'; // redirigir igual si se guardó pero falló correo
                    }
                })
                .catch(err => {
                    console.error("Error al enviar el PDF:", err);
                    alert("Error de conexión al guardar.");
                    btnGuardarEnviar.innerHTML = originalText;
                    btnGuardarEnviar.disabled = false;
                });
            });
        });
    }
});