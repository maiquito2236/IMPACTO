document.addEventListener("DOMContentLoaded", () => {
    // ELEMENTOS DEL DOM
    const teeth = document.querySelectorAll(".tooth-select");
    const selectedToothInput = document.getElementById("selectedTooth");
    const saveModalButton = document.getElementById("saveTreatment"); 
    const btnGuardarHistorial = document.getElementById("btn-guardar-enviar-historial"); 
    
    const patientIdInput = document.getElementById("patientIdInput");
    const dateInput = document.getElementById("dateInput");

    const btnModoAdulto = document.getElementById("btn-odontograma-adulto");
    const btnModoNino = document.getElementById("btn-odontograma-nino");
    const boxAdulto = document.getElementById("odontograma-box-adulto");
    const boxNino = document.getElementById("odontograma-box-nino");
    const tituloOdontograma = document.getElementById("titulo-odontograma-planes");
    const btnResetTeeth = document.getElementById("resetTeeth");

    function cambiarModoOdontograma(modo) {
        if (!boxAdulto || !boxNino) return;
        if (modo === "nino") {
            boxAdulto.style.display = "none";
            boxNino.style.display = "block";
            if (tituloOdontograma) tituloOdontograma.textContent = "Odontograma Clínico Infantil (20 Dientes)";
            if (btnModoNino) {
                btnModoNino.style.background = "#1a56db";
                btnModoNino.style.color = "#ffffff";
            }
            if (btnModoAdulto) {
                btnModoAdulto.style.background = "transparent";
                btnModoAdulto.style.color = "#475569";
            }
        } else {
            boxAdulto.style.display = "block";
            boxNino.style.display = "none";
            if (tituloOdontograma) tituloOdontograma.textContent = "Odontograma Clínico Adulto (32 Dientes)";
            if (btnModoAdulto) {
                btnModoAdulto.style.background = "#1a56db";
                btnModoAdulto.style.color = "#ffffff";
            }
            if (btnModoNino) {
                btnModoNino.style.background = "transparent";
                btnModoNino.style.color = "#475569";
            }
        }
    }

    if (btnModoAdulto) {
        btnModoAdulto.addEventListener("click", () => cambiarModoOdontograma("adulto"));
    }
    if (btnModoNino) {
        btnModoNino.addEventListener("click", () => cambiarModoOdontograma("nino"));
    }

    // MATRIZ LOCAL Y RECORDATORIOS DE FORMULARIO
    let tratamientosEnMemoria = {}; 
    let currentTooth = null;
    let ultimosProcedimientosIds = []; // 🔥 NUEVO: Recuerda las últimas opciones elegidas

    // Función auxiliar para determinar si un procedimiento es de cobro global / boca completa
    function esCobroGlobal(nombre, tipoCobro) {
        if (parseInt(tipoCobro || 1) === 2) return true;
        const nombreLower = (nombre || '').toLowerCase();
        const patronesGenerales = [
            'limpieza', 'detartraje', 'profilaxis', 'valoracion', 'valoración',
            'diagnostico', 'diagnóstico', 'blanqueamiento', 'aclaramiento',
            'protesis total', 'prótesis total', 'protesis parcial', 'prótesis parcial',
            'ortodoncia', 'brackets', 'brakets', 'retenedor', 'diseño de sonrisa', 'diseño sonrisa'
        ];
        return patronesGenerales.some(p => nombreLower.includes(p));
    }

    if (btnResetTeeth) {
        btnResetTeeth.addEventListener("click", () => {
            tratamientosEnMemoria = {};
            teeth.forEach(t => t.classList.remove("active-tooth"));
            cargarOdontogramaPacienteActual();
            renderizarBorradoresEnTabla();
        });
    }

    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }

    /* ==========================================================================
       1. CARGA INICIAL DESDE LA BASE DE DATOS (HISTORIAL PASADO -> VERDES)
       ========================================================================== */
    function cargarOdontogramaPacienteActual() {
        const pacienteId = patientIdInput ? patientIdInput.value.trim() : '';
        teeth.forEach(t => t.classList.remove("active-tooth", "saved-tooth"));

        if (!pacienteId || pacienteId === "" || pacienteId === "0") return;

        fetch(`/odontologo/planes/listarPiezas?id=${pacienteId}`)
            .then(res => res.json())
            .then(tratamientosGuardados => {
                if (Array.isArray(tratamientosGuardados)) {
                    teeth.forEach(t => {
                        const numeroPieza = t.textContent.trim();
                        if (tratamientosGuardados.includes(numeroPieza)) {
                            t.classList.add("saved-tooth"); 
                        }
                    });
                }
            })
            .catch(err => console.error("Error al cargar piezas guardadas:", err));
    }

    cargarOdontogramaPacienteActual();

    /* ==========================================================================
       2. INTERACCIÓN: SE ILUMINA EN AZUL AL MOMENTO DEL CLIC
       ========================================================================== */
    teeth.forEach(tooth => {
        tooth.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            const pacienteId = patientIdInput ? patientIdInput.value.trim() : '';
            if (!pacienteId || pacienteId === "" || pacienteId === "0") {
                Swal.fire({
                    title: 'Sin paciente seleccionado',
                    text: 'Para registrar piezas en el odontograma, primero seleccione un paciente en el menú superior o inicie una cita desde la Agenda.',
                    icon: 'info',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            currentTooth = tooth;
            const toothNumber = tooth.textContent.trim();

            tooth.classList.add("active-tooth");

            if (selectedToothInput) {
                selectedToothInput.textContent = `Configurar Pieza Dental: #${toothNumber}`;
            }

            // Limpiar todos los checkboxes primero
            document.querySelectorAll("#selectProcedimientos input[type='checkbox']").forEach(cb => cb.checked = false);
            document.getElementById("notesInput").value = "";

            // Si el diente ya tiene borradores, los cargamos.
            if (tratamientosEnMemoria[toothNumber] && tratamientosEnMemoria[toothNumber].length > 0) {
                tratamientosEnMemoria[toothNumber].forEach(proc => {
                    const cb = document.querySelector(`#selectProcedimientos input[value='${proc.procedimiento_id}']`);
                    if (cb) cb.checked = true;
                });
                document.getElementById("notesInput").value = tratamientosEnMemoria[toothNumber][0].notas || '';
            } else {
                // Si está vacío, autocompletamos con los últimos procedimientos usados
                ultimosProcedimientosIds.forEach(procId => {
                    const cb = document.querySelector(`#selectProcedimientos input[value='${procId}']`);
                    if (cb) cb.checked = true;
                });
            }

            const modal = document.getElementById("modal-registrar-plan");
            if (modal) {
                modal.style.display = "flex";
                modal.classList.add("active");
            }
        });
    });

    /* ==========================================================================
       3. BOTÓN DEL MODAL: GUARDA EN MEMORIA
       ========================================================================== */
    if (saveModalButton) {
        saveModalButton.addEventListener("click", () => {
            const checkboxes = document.querySelectorAll("#selectProcedimientos input[type='checkbox']:checked");
            const notes = document.getElementById("notesInput")?.value ?? '';
            const piezaNum = currentTooth ? currentTooth.textContent.trim() : '';

            if (checkboxes.length === 0 || !piezaNum) {
                Swal.fire('Atención', 'Por favor seleccione al menos un procedimiento clínico', 'warning');
                return;
            }

            ultimosProcedimientosIds = [];
            tratamientosEnMemoria[piezaNum] = [];

            let nombresProcedimientos = [];
            checkboxes.forEach(cb => {
                const procId = cb.value;
                const nombreProc = cb.dataset.nombre || '';
                const precio = parseFloat(cb.dataset.precio || 0);
                const esGlobal = esCobroGlobal(nombreProc, cb.dataset.tipoCobro);
                const tipoCobro = esGlobal ? 2 : 1;

                ultimosProcedimientosIds.push(procId);
                nombresProcedimientos.push(nombreProc);

                tratamientosEnMemoria[piezaNum].push({
                    procedimiento_id: procId,
                    notas: notes,
                    precio: precio,
                    tipoCobro: tipoCobro,
                    nombre_procedimiento: nombreProc
                });
            });

            if (currentTooth) {
                document.querySelectorAll(`.tooth-select[data-pieza='${piezaNum}']`).forEach(el => {
                    el.classList.remove("active-tooth");
                    el.classList.add("saved-tooth");
                });
            }

            renderizarBorradoresEnTabla();
            const modal = document.getElementById("modal-registrar-plan");
            if (modal) {
                modal.style.display = "none";
                modal.classList.remove("active");
            }

            Swal.fire({
                title: `¡Pieza #${piezaNum} Configurada!`,
                text: `${nombresProcedimientos.join(', ')} asignado(s).`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
    }

    /* ==========================================================================
       4. BOTÓN GENERAL: AGRUPA POR PROCEDIMIENTO Y SINCRONIZA CON PHP
       ========================================================================== */
    if (btnGuardarHistorial) {
        btnGuardarHistorial.addEventListener("click", () => {
            const pacienteId = patientIdInput ? patientIdInput.value.trim() : '';
            const fecha = dateInput ? dateInput.value : new Date().toISOString().split('T')[0];

            if (!pacienteId || pacienteId === "" || pacienteId === "0") {
                Swal.fire('Atención', 'Debe seleccionar un paciente o una cita desde la Agenda para registrar tratamientos.', 'warning');
                return;
            }

            if (Object.keys(tratamientosEnMemoria).length === 0) {
                Swal.fire('Odontograma Vacío', 'Selecciona al menos una pieza y asígnale un procedimiento.', 'info');
                return;
            }

            // Agrupamos las piezas por procedimiento
            const payloadGrupos = {};
            for (const [pieza, arrayDatos] of Object.entries(tratamientosEnMemoria)) {
                arrayDatos.forEach(datos => {
                    if (!payloadGrupos[datos.procedimiento_id]) {
                        payloadGrupos[datos.procedimiento_id] = {
                            procedimiento_id: datos.procedimiento_id,
                            precio_aplicado: datos.precio,
                            tipoCobro: datos.tipoCobro,
                            notas: datos.notas,
                            piezas: []
                        };
                    }
                    payloadGrupos[datos.procedimiento_id].piezas.push(pieza);
                });
            }

            const arrayProcedimientos = Object.values(payloadGrupos);

            Swal.fire({
                title: 'Cierre de Historia Clínica',
                html: `
                    <div style="text-align: left; font-size: 14px; margin-top: 10px;">
                        <label style="font-weight: bold; color: #1f2937; display: block; margin-bottom: 5px;">Diagnóstico Clínico General <span style="color:#6b7280;font-weight:normal">(Opcional)</span></label>
                        <textarea id="swal-diagnostico" class="swal2-textarea" style="margin: 0; width: 100%; height: 80px; box-sizing: border-box; resize: none; margin-bottom: 15px;" placeholder="Ej: Paciente presenta caries profunda en molares..."></textarea>
                        
                        <label style="font-weight: bold; color: #1f2937; display: block; margin-bottom: 5px;">Recomendaciones Médicas <span style="color:#6b7280;font-weight:normal">(Opcional)</span></label>
                        <textarea id="swal-recomendacion" class="swal2-textarea" style="margin: 0; width: 100%; height: 80px; box-sizing: border-box; resize: none;" placeholder="Ej: No comer cosas duras por 24h..."></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar historial',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2bc48a',
                cancelButtonColor: '#6b7280',
                preConfirm: () => {
                    return {
                        diagnostico: document.getElementById('swal-diagnostico').value,
                        recomendacion: document.getElementById('swal-recomendacion').value
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const extraData = result.value;
                    const citaIdInput = document.getElementById("citaIdInput");
                    const cita_id = citaIdInput ? citaIdInput.value : '';

                    Swal.fire({
                        title: 'Guardando plan...',
                        text: 'Por favor espera mientras se actualiza el historial clínico.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviamos todos los procedimientos en un único payload atómico para evitar bloqueos concurrentes
                    const payloadFinal = {
                        paciente_id: pacienteId,
                        cita_id: cita_id,
                        fecha: fecha,
                        diagnostico_general: extraData.diagnostico,
                        recomendacion: extraData.recomendacion,
                        procedimientos: arrayProcedimientos
                    };

                    fetch('/odontologo/procedimientos/guardarMasivo', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payloadFinal)
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            Swal.fire('¡Éxito!', 'Historial clínico actualizado correctamente.', 'success').then(() => {
                                const citaIdParam = (citaIdInput && citaIdInput.value) ? `&cita_id=${citaIdInput.value}` : '';
                                window.location.href = `/odontologo/historial-clinico?paciente_id=${pacienteId}${citaIdParam}`; 
                            });
                        } else {
                            Swal.fire('Error al guardar', response.error || 'No se pudo guardar el plan de tratamiento.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error("Error masivo:", err);
                        Swal.fire('Error de Conexión', 'Revisa la consola para más detalles.', 'error');
                    });
                }
            });
        });
    }

    // Botón Cancelar o cerrar sin guardar
    const btnCancelar = document.querySelector(".btn-secondary");
    if (btnCancelar) {
        btnCancelar.addEventListener("click", () => {
            if (currentTooth) {
                const num = currentTooth.textContent.trim();
                if (!tratamientosEnMemoria[num] && !currentTooth.classList.contains("saved-tooth")) {
                    currentTooth.classList.remove("active-tooth");
                }
            }
            const modal = document.getElementById("modal-registrar-plan");
            if (modal) {
                modal.style.display = "none";
                modal.classList.remove("active");
            }
        });
    }

    /* ==========================================================================
       5. CONSUMIR EL CATÁLOGO DE PROCEDIMIENTOS CLÍNICOS
       ========================================================================== */
    fetch('/odontologo/agenda/obtener_lista_procedimientos')
        .then(res => {
            if (!res.ok) throw new Error("Código de respuesta del servidor: " + res.status);
            return res.json();
        })
        .then(data => {
            const select = document.getElementById("selectProcedimientos");
            if (!select) return;
            
            select.innerHTML = '';
            
            if (Array.isArray(data)) {
                data.forEach(proc => {
                    if (proc.ID_PROCEDIMIENTO || proc.ID_PROCEDIMIENTO === 0 || proc.ID_PROCEDIMIENTO === "") {
                        const label = document.createElement("label");
                        label.style.display = "block";
                        label.style.marginBottom = "5px";
                        label.style.cursor = "pointer";
                        label.style.fontSize = "13px";
                        
                        const checkbox = document.createElement("input");
                        checkbox.type = "checkbox";
                        checkbox.value = proc.ID_PROCEDIMIENTO;
                        checkbox.style.marginRight = "8px";
                        
                        // 🔥 Verificación de cobro: 2 = Global/Boca Completa, 1 = Por Diente
                        const precioBase = proc.COSTO || proc.PRECIO || 0; 
                        const esGlobal = esCobroGlobal(proc.NOMBRE_PROCEDIMIENTO, proc.TIPO_COBRO);
                        checkbox.dataset.precio = precioBase;
                        checkbox.dataset.tipoCobro = esGlobal ? 2 : 1;
                        checkbox.dataset.nombre = proc.NOMBRE_PROCEDIMIENTO;
                        
                        label.appendChild(checkbox);
                        const textoEtiqueta = esGlobal 
                            ? `${proc.NOMBRE_PROCEDIMIENTO} (General / Boca Completa)` 
                            : proc.NOMBRE_PROCEDIMIENTO;
                        label.appendChild(document.createTextNode(textoEtiqueta));
                        
                        select.appendChild(label);
                    }
                });
            }
        })
        .catch(err => console.error("Error al cargar catálogo de procedimientos:", err));

    /* ==========================================================================
       6. FUNCIÓN: RENDERIZAR CON PRECIOS Y ACTUALIZAR PRESUPUESTO
       ========================================================================== */
    function renderizarBorradoresEnTabla() {
        const contenedor = document.getElementById("contenedor-fases-dinamicas");
        if (!contenedor) return;

        const borradoresExistentes = contenedor.querySelectorAll(".fila-temporal-borrador");
        borradoresExistentes.forEach(el => el.remove());

        const procedimientosUnicos = [];
        const idsRegistrados = new Set();

        for (const [pieza, arrayDatos] of Object.entries(tratamientosEnMemoria)) {
            arrayDatos.forEach(item => {
                if (!idsRegistrados.has(item.procedimiento_id)) {
                    idsRegistrados.add(item.procedimiento_id);
                    procedimientosUnicos.push(item);
                }
            });
        }

        const mensajeVacio = contenedor.querySelector("div:not(.table-row-item)");
        
        if (procedimientosUnicos.length > 0) {
            if (mensajeVacio) mensajeVacio.style.display = "none";
        } else {
            const filasReales = contenedor.querySelectorAll(".table-row-item");
            if (filasReales.length === 0 && mensajeVacio) mensajeVacio.style.display = "block";
        }

        let subtotalBorradores = 0; // 🔥 NUEVO: Sumatoria de los borradores

        procedimientosUnicos.forEach((proc) => {
            const itemDiv = document.createElement("div");
            itemDiv.classList.add("table-row-item", "fila-temporal-borrador");
            
            itemDiv.style.cssText = `
                display: flex; 
                justify-content: space-between; 
                padding: 12px 10px; 
                border-bottom: 1px solid #eee; 
                align-items: center; 
                border-left: 4px solid #e67e22; 
                margin-bottom: 5px; 
                background: #fff9e6; 
            `;

            let nombreTexto = proc.nombre_procedimiento || "Procedimiento Temporal";

            let piezasArr = [];
            for (const [pza, arrayDatos] of Object.entries(tratamientosEnMemoria)) {
                if (arrayDatos.some(d => d.procedimiento_id == proc.procedimiento_id)) piezasArr.push(pza);
            }
            
            let cantidadCalculada = 1;
            const esGlobal = (proc.tipoCobro === 2) || esCobroGlobal(proc.nombre_procedimiento, proc.tipoCobro);
            if (!esGlobal) { // 1 = Cobro por diente (solo si NO es global se multiplica por las piezas)
                cantidadCalculada = piezasArr.length;
            }
            // Si es 2 = Cobro Global / Boca Completa, la cantidad a multiplicar es siempre 1.

            let precioFinal = (proc.precio || 0.00) * cantidadCalculada;
            subtotalBorradores += precioFinal; // Acumulamos el costo

            const textoDetalleCobro = esGlobal
                ? `<small style="color: #6b21a8; display: block; font-size: 0.75rem; font-weight: 600;">• Cobro Global / Boca Completa (Piezas: ${piezasArr.join(', ')})</small>`
                : `<small style="color: #0369a1; display: block; font-size: 0.75rem; font-weight: 600;">• ${piezasArr.length} ${piezasArr.length === 1 ? 'pieza' : 'piezas'} (${piezasArr.join(', ')}): $${(proc.precio || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })} c/u</small>`;

            itemDiv.innerHTML = `
                <span class="row-cell-name" style="display: flex; align-items: center; gap: 10px;">
                    <span style="background-color: #e67e22; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; white-space: nowrap;">
                     Borrador
                    </span>
                    
                    <div>
                        <span style="font-weight: 600; color: #1f2937;">${nombreTexto}</span>
                        ${textoDetalleCobro}
                        ${proc.notas ? `
                            <small style="color: #6b7280; font-size: 0.7rem; display: block; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${proc.notas}">
                                Obs: ${proc.notas}
                            </small>
                        ` : ''}
                    </div>
                </span>
                
                <span class="tag-phase-state" style="color: #e67e22; font-weight: 600; font-size: 0.85rem;">
                    PENDIENTE
                </span>
                
                <span class="row-cell-price" style="font-weight: bold; color: #374151;">
                    $${precioFinal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </span>
            `;
            
            contenedor.appendChild(itemDiv);
        });

        // 🔥 NUEVO: ACTUALIZAMOS EL TOTAL ESTIMADO EN LA INTERFAZ
        const montoElemento = document.getElementById("monto-total-fase");
        if (montoElemento) {
            const baseTotal = parseFloat(montoElemento.dataset.baseTotal || 0);
            const totalGeneral = baseTotal + subtotalBorradores;
            
            montoElemento.innerHTML = `Total Estimado: $${totalGeneral.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            
            // Cambiamos el color de la pastilla inferior de gris a azul si el presupuesto sube de $0
            const pillDiv = montoElemento.nextElementSibling;
            if (pillDiv && totalGeneral > 0) {
                pillDiv.style.backgroundColor = '#e0f2fe';
                pillDiv.style.color = '#0369a1';
                pillDiv.innerHTML = `<i class="fa-solid fa-circle-check"></i> Registro Vinculado`;
            } else if (pillDiv && totalGeneral === 0) {
                pillDiv.style.backgroundColor = '#f3f4f6';
                pillDiv.style.color = '#6b7280';
                pillDiv.innerHTML = `<i class="fa-solid fa-circle-check"></i> Esperando Diagnóstico`;
            }
        }
    }

    // --- NUEVO: CIERRE GLOBAL DE MODALES ---
    document.addEventListener("click", (e) => {
        if (e.target.closest(".btn-close-modal") || e.target.classList.contains("modal-overlay-backdrop")) {
            e.preventDefault();
            const modals = document.querySelectorAll(".modal-overlay-backdrop");
            modals.forEach(m => {
                m.style.display = "none";
                m.classList.remove("active");
            });
        }
    });
});