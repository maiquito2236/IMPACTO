document.addEventListener('DOMContentLoaded', () => {
    /* ==========================================================================
       VARIABLES GLOBALES
       ========================================================================== */
    let currentAppointmentId = null;
    const hoyAlArranque = new Date();
    let selectedDay = hoyAlArranque.getDate();
    let selectedMonth = hoyAlArranque.getMonth();
    let selectedYear = hoyAlArranque.getFullYear();
    let miniSelectedDay = hoyAlArranque.getDate();
    let miniSelectedMonth = hoyAlArranque.getMonth();
    let miniSelectedYear = hoyAlArranque.getFullYear();

    const toggleButtons = document.querySelectorAll(".btn-toggle");
    const calendarGrid = document.querySelector(".calendar-grid");
    const originalCalendar = calendarGrid ? calendarGrid.innerHTML : "";

    toggleButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            toggleButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const text = btn.textContent.trim();
            if(text === "Ver Hoy"){
                mostrarVistaHoy();
            } else if(text === "Semana"){
                mostrarVistaSemana();
            } else if(text === "Mes"){
                mostrarVistaMes();
            }
        });
    });
    
    function mostrarVistaHoy(){
        if (!calendarGrid) return;
        calendarGrid.classList.remove("month-view");
        calendarGrid.classList.add("today-mode");

        calendarGrid.innerHTML = `
            <div class="today-view-container">
                <div class="today-header">
                    <div class="today-left">
                        <h2>Agenda de Hoy</h2>
                        <p class="today-subtitle">Consultorio Odonto Estética</p>
                    </div>
                    <div class="today-counter">
                        <span class="counter-number">0</span>
                        <span class="counter-text">Citas Programadas</span>
                    </div>
                </div>
                <div class="today-events-list"></div>
            </div>
        `;
        cargarVistaHoy();
    }

    async function cargarVistaHoy(){
        try {
            const respuesta = await fetch("index.php?action=odontologo/agenda/obtener_citas_hoy");
            const citas = await respuesta.json(); 
            let html = "";

            const contadorCitas = document.querySelector(".counter-number");
            if (contadorCitas) {
                contadorCitas.textContent = citas.length;
            }

            citas.forEach(cita => {
                let colorClass = "blue-card";
                if (cita.estado == 2) colorClass = "green-card";
                else if (cita.estado == 3) colorClass = "red-card";
                
                html += `
                    <div class="today-event-card event-card ${colorClass} patient-card" 
                         data-id="${cita.id_cita}"
                         data-name="${cita.paciente}"
                         data-treatment="${cita.tratamiento || ''}"
                         data-time="${cita.hora_cita}"
                         data-date="${cita.fecha_cita || ''}"
                         data-documento="${cita.documento || ''}" 
                         data-status="${cita.estado}">
                        <div class="today-event-hour">${cita.hora_cita}</div>
                        <div class="today-event-info">
                            <h4>${cita.paciente}</h4>
                            <p>${cita.tratamiento || 'Sin tratamiento asignado'}</p>
                            <span>${cita.estado}</span>
                        </div>
                    </div>
                `;
            });
            const listaEventos = document.querySelector(".today-events-list");
            if (listaEventos) listaEventos.innerHTML = html;
        } catch (error) {
            console.error("Error cargando citas de hoy:", error);
        }
    }

    function mostrarVistaSemana(){
        if (!calendarGrid) return;
        calendarGrid.classList.remove("today-mode");
        calendarGrid.classList.remove("month-view");
        calendarGrid.innerHTML = originalCalendar;
        cargarCitas();
    }

    async function mostrarVistaMes(){
        if (!calendarGrid) return;
        calendarGrid.classList.remove("today-mode");
        calendarGrid.classList.add("month-view");

        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        calendarGrid.innerHTML = `
            <div class="month-header">
                <h2>Planificación Mensual</h2>
                <div class="cal-nav-modern">
                    <button id="btn-prev-month" class="btn-cal-nav-modern" title="Mes anterior">&#10094;</button>
                    <span id="label-current-month" class="label-cal-nav-modern">${monthNames[selectedMonth]} ${selectedYear}</span>
                    <button id="btn-next-month" class="btn-cal-nav-modern" title="Mes siguiente">&#10095;</button>
                </div>
            </div>
            <div class="month-days-row">
                <div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div><div>Dom</div>
            </div>
            <div class="month-grid"></div>
        `;

        const btnPrev = document.getElementById('btn-prev-month');
        const btnNext = document.getElementById('btn-next-month');

        if(btnPrev) {
            btnPrev.addEventListener('click', async () => {
                selectedMonth--;
                if(selectedMonth < 0) { selectedMonth = 11; selectedYear--; }
                await actualizarMesUI();
            });
        }
        if(btnNext) {
            btnNext.addEventListener('click', async () => {
                selectedMonth++;
                if(selectedMonth > 11) { selectedMonth = 0; selectedYear++; }
                await actualizarMesUI();
            });
        }

        const monthGrid = document.querySelector(".month-grid");
        if (monthGrid) monthGrid.innerHTML = await crearDiasMes();
    }

    async function actualizarMesUI() {
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const label = document.getElementById('label-current-month');
        if(label) label.textContent = `${monthNames[selectedMonth]} ${selectedYear}`;
        
        const monthGrid = document.querySelector(".month-grid");
        if (monthGrid) monthGrid.innerHTML = await crearDiasMes();
    }

    async function crearDiasMes(){
        let html = "";
        try {
            // Mes en JS es 0-11, MySQL es 1-12
            const mesReal = selectedMonth + 1;
            const response = await fetch(`index.php?action=odontologo/agenda/obtener_citas_mes&mes=${mesReal}&anio=${selectedYear}`);
            const citas = await response.json();
            const citasPorDia = {};

            citas.forEach(cita => {
                const dia = parseInt(cita.dia);
                if(!citasPorDia[dia]) citasPorDia[dia] = [];
                citasPorDia[dia].push(cita);
            });

            // Calcular el día de la semana en que empieza el mes (1 = Lunes, 7 = Domingo)
            const firstDayDate = new Date(selectedYear, selectedMonth, 1);
            let startDay = firstDayDate.getDay();
            startDay = startDay === 0 ? 7 : startDay; // Convertir domingo de 0 a 7
            
            // Días totales del mes
            const daysInMonth = new Date(selectedYear, selectedMonth + 1, 0).getDate();

            // Celdas vacías previas
            for(let i = 1; i < startDay; i++){
                html += `<div class="month-day-card empty-day"></div>`;
            }

            for(let i = 1; i <= daysInMonth; i++){
                html += `
                    <div class="month-day-card" data-month-day="${i}">
                        <div class="month-day-number">${i}</div>
                        <div class="month-events-container">
                `;
                if(citasPorDia[i]){
                    citasPorDia[i].forEach(cita => {
                        let colorClass = "blue-card";
                        if (cita.estado == 2) colorClass = "green-card";
                        else if (cita.estado == 3) colorClass = "red-card";
                        
                        html += `
                            <div class="event-card ${colorClass} patient-card"
                                 data-id="${cita.id || cita.id_cita}"
                                 data-name="${cita.nombre || cita.paciente}"
                                 data-treatment="${cita.tratamiento}"
                                 data-time="${cita.hora || cita.hora_cita}"
                                 data-date="${selectedYear}-${(selectedMonth+1).toString().padStart(2,'0')}-${String(i).padStart(2, '0')}"
                                 data-documento="${cita.documento || ''}"
                                 data-status="${cita.estado}">
                                <h5>${cita.nombre || cita.paciente}</h5>
                                <p>${cita.tratamiento}</p>
                                <span class="ev-time">${cita.hora || cita.hora_cita}</span>
                            </div>
                        `;
                    });
                }
                html += `</div></div>`;
            }
        } catch (error) {
            console.error("Error en vista mes:", error);
        }
        return html;
    }

    /* ==========================================================================
       CONTROLADOR DE EVENTOS (CLICK EN CITAS Y AUTO-SELECCIÓN)
       ========================================================================== */
    document.addEventListener("click", async (e) => {
        
        if (e.target.closest('.btn-close-modal') || e.target.closest('.btn-cancel-modal') || e.target.closest('.btn-action-dismiss')) {
            e.preventDefault();
            const modalReprog = document.getElementById("modal-reprogramar");
            const modalNuevo = document.getElementById("modal-nuevo-procedimiento");
            if (modalReprog) { modalReprog.style.display = 'none'; modalReprog.classList.remove('active'); }
            if (modalNuevo) { modalNuevo.style.display = 'none'; modalNuevo.classList.remove('active'); }
            return;
        }
        
        const card = e.target.closest(".patient-card, .patient-sidebar-item");
        if (!card) return;

        const esCitaDeHoy = card.classList.contains("today-event-card");
        const esBarraLateral = card.classList.contains("patient-sidebar-item");
        const esCitaSemanaMes = card.classList.contains("event-card") && !esCitaDeHoy;

        // === CASO A: ABRIR MODAL "NUEVO TRATAMIENTO" ===
        if (esCitaDeHoy || esBarraLateral) {
            e.preventDefault();
            e.stopPropagation();

            const estado = card.dataset.status;
            if (estado == 2) {
                Swal.fire('Completada', 'Esta cita ya fue completada.', 'info');
                return;
            }
            if (estado == 3) {
                fetchCancelInfo(card.dataset.id);
                return;
            }

            const idCita = card.dataset.id;
            if (!idCita) return;
            let datosBD = null;

            try {
                const respuesta = await fetch(`index.php?action=odontologo/agenda/obtener_detalle_cita&id=${idCita}`);
                if (respuesta.ok) {
                    const contentType = respuesta.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        datosBD = await respuesta.json();
                    }
                }
            } catch (error) { console.error(error); }

            if (!datosBD) {
                datosBD = {
                    id_cita: card.dataset.id,
                    paciente: card.dataset.name,
                    tratamiento: card.dataset.treatment,
                    fecha_cita: card.dataset.date,
                    documento: card.dataset.documento
                };
            }

            // Llenar campos de texto
            const inputDocumento = document.getElementById("input-doc-paciente");
            const inputNombre = document.getElementById("input-nombre-paciente");
            const inputFecha = document.getElementById("dateInput");
            const inputOculto = document.getElementById("idCitaOculto");

            if (inputOculto) inputOculto.value = datosBD.id_cita || idCita;
            if (inputDocumento) inputDocumento.value = datosBD.documento || "";
            if (inputNombre) inputNombre.value = datosBD.paciente || datosBD.nombre || "No indicado";

            let fechaCita = datosBD.fecha_cita || datosBD.fecha || "";
            if (inputFecha && fechaCita) {
                fechaCita = fechaCita.split(" ")[0];
                if (fechaCita.includes("-")) {
                    const partes = fechaCita.split("-");
                    inputFecha.value = `${partes[2]}/${partes[1]}/${partes[0]}`;
                } else {
                    inputFecha.value = fechaCita;
                }
            }

            // --- LÓGICA MEJORADA DE AUTO-SELECCIÓN ---
            const selProc = document.getElementById("select-procedimiento"); 
            if (selProc) {
                const seleccionarProcedimientoCoincidente = () => {
                    // Limpiamos la cadena (quitamos cosas como " | Reprogramada")
                    const textoCita = (datosBD.tratamiento || "").split('|')[0].trim().toLowerCase();
                    let indiceEncontrado = 0; 

                    if (textoCita !== "") {
                        for (let i = 1; i < selProc.options.length; i++) {
                            const textoOpcion = selProc.options[i].text.trim().toLowerCase();
                            
                            // Búsqueda inteligente: si la opción incluye la cita o la cita incluye la opción
                            if (textoCita.includes(textoOpcion) || textoOpcion.includes(textoCita) || textoOpcion === textoCita) {
                                indiceEncontrado = i;
                                break;
                            }
                        }
                    }

                    // Selecciona la opción encontrada
                    selProc.selectedIndex = indiceEncontrado;
                    
                    // Dispara el evento change para que se llenen los campos de Costo y Tiempo
                    selProc.dispatchEvent(new Event('change'));
                };

                // Si ya cargaron los procedimientos, selecciona. Si no, espera el evento.
                if (selProc.options.length > 1) {
                    seleccionarProcedimientoCoincidente();
                } else {
                    selProc.addEventListener('procedimientos-cargados', seleccionarProcedimientoCoincidente, { once: true });
                }
            }

            // Abrir el modal
            const modalNuevo = document.getElementById("modal-nuevo-procedimiento");
            if (modalNuevo) {
                modalNuevo.style.display = 'flex'; 
                modalNuevo.classList.add('active');
            }

        // === CASO B: REPROGRAMAR CITA ===
        } else if (esCitaSemanaMes) { 
            e.preventDefault();
            e.stopPropagation();

            const estado = card.dataset.status;
            if (estado == 2) {
                Swal.fire('Completada', 'Esta cita ya fue completada.', 'info');
                return;
            }
            if (estado == 3) {
                fetchCancelInfo(card.dataset.id);
                return;
            }

            if (card.classList.contains("cita-reprogramada-old") || card.classList.contains("orange-card")) return;

            currentAppointmentId = card.dataset.id;
            const nombrePaciente = card.dataset.name || "No indicado";
            const tratamientoPaciente = card.dataset.treatment || "No indicado";
            const horaPaciente = card.dataset.time || "";
            const fechaPaciente = card.dataset.date || "";

            let fechaFormateada = fechaPaciente;
            if (fechaPaciente && fechaPaciente.includes("-")) {
                const partes = fechaPaciente.split("-");
                fechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
            }

            const elNombre = document.getElementById("modal-paciente-nombre");
            const elTratamiento = document.getElementById("modal-paciente-tratamiento");
            const elHorario = document.getElementById("modal-paciente-horario");

            if (elNombre) elNombre.textContent = nombrePaciente;
            if (elTratamiento) elTratamiento.textContent = tratamientoPaciente;
            if (elHorario) elHorario.textContent = `Programada para el: ${fechaFormateada} a las ${horaPaciente} hs`;

            // REMOVED window.location.hash to prevent page navigation
            const modalReprog = document.getElementById("modal-reprogramar");
            if (modalReprog) {
                modalReprog.style.display = 'flex';
                modalReprog.classList.add('active');
            }
        }
    }, true); 


    /* ==========================================================================
       CARGA PRINCIPAL DE PROCEDIMIENTOS AL ARRANCAR
       ========================================================================== */
    function cargarProcedimientos() {
        const select = document.getElementById("select-procedimiento");
        if (!select) return;

        fetch("index.php?action=odontologo/agenda/obtener_lista_procedimientos")
            .then(res => res.json())
            .then(data => {
                select.innerHTML = '<option value="">Seleccionar...</option>';
                if (Array.isArray(data)) {
                    data.forEach(p => {
                        const opt = document.createElement("option");
                        opt.value = p.ID_PROCEDIMIENTO;
                        opt.text = p.NOMBRE_PROCEDIMIENTO;
                        opt.dataset.costo = p.COSTO || 0;
                        opt.dataset.duracion = p.TIEMPO_ESTIMADO || "30";
                        select.appendChild(opt);
                    });
                    
                    // Avisamos al sistema que ya cargaron los procedimientos
                    select.dispatchEvent(new CustomEvent('procedimientos-cargados'));
                }
            })
            .catch(err => console.error("Error al cargar procedimientos:", err));
    }

    // Auto-llenar Precio y Duración al cambiar el Select
    const treatmentSelect = document.getElementById("select-procedimiento"); 
    if (treatmentSelect) {
        treatmentSelect.addEventListener("change", (e) => {
            const opt = e.target.options[e.target.selectedIndex];
            const inputCosto = document.getElementById("input-costo"); 
            const inputDuracion = document.getElementById("input-duracion"); 
            
            if(opt && opt.value !== ""){
                if(inputCosto) inputCosto.value = opt.dataset.costo ? `$ ${parseInt(opt.dataset.costo).toLocaleString('es-CO')}` : "";
                if(inputDuracion) inputDuracion.value = opt.dataset.duracion ? `${opt.dataset.duracion} min` : "";
            } else {
                if(inputCosto) inputCosto.value = "";
                if(inputDuracion) inputDuracion.value = "";
            }
        });
    }

    /* =====================================
       MINI CALENDARIO Y REPROGRAMACIÓN
       ===================================== */
    const monthLabel = document.getElementById("calendar-month-label");
    const daysContainer = document.getElementById("calendar-days");
    const prevBtn = document.querySelector(".prev-month");
    const nextBtn = document.querySelector(".next-month");

    if(monthLabel && daysContainer && prevBtn && nextBtn){
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        let currentMonth = hoyAlArranque.getMonth();
        let currentYear = hoyAlArranque.getFullYear();

        function renderCalendar(){
            monthLabel.textContent = `${monthNames[currentMonth]} de ${currentYear}`;
            daysContainer.innerHTML = "";

            const firstDay = new Date(currentYear, currentMonth, 1);
            let startDay = firstDay.getDay();
            startDay = startDay === 0 ? 6 : startDay - 1;

            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

            for(let i = startDay; i > 0; i--){
                const span = document.createElement("span");
                span.classList.add("muted-day");
                span.textContent = prevMonthDays - i + 1;
                daysContainer.appendChild(span);
            }

            for(let day = 1; day <= daysInMonth; day++){
                const span = document.createElement("span");
                span.textContent = day;

                if(day === selectedDay && currentMonth === selectedMonth && currentYear === selectedYear){
                    span.classList.add("selected-day-circle");
                }

                span.addEventListener("click", () => {
                    document.querySelectorAll("#calendar-days span").forEach(el => el.classList.remove("selected-day-circle"));
                    span.classList.add("selected-day-circle");
                    miniSelectedDay = day;
                    miniSelectedMonth = currentMonth;
                    miniSelectedYear = currentYear;

                    const fechaSeleccionada = `${miniSelectedYear}-${(miniSelectedMonth + 1).toString().padStart(2, '0')}-${miniSelectedDay.toString().padStart(2, '0')}`;
                    renderizarHorasDisponibles(fechaSeleccionada);
                });
                daysContainer.appendChild(span);
            }
        }
        prevBtn.addEventListener("click", () => { currentMonth--; if(currentMonth < 0){ currentMonth = 11; currentYear--; } renderCalendar(); });
        nextBtn.addEventListener("click", () => { currentMonth++; if(currentMonth > 11){ currentMonth = 0; currentYear++; } renderCalendar(); });
        renderCalendar();
    }

    async function cargarCitas() {
        try {
            const respuesta = await fetch("index.php?action=odontologo/agenda/obtener_citas");
            const citas = await respuesta.json();

            citas.forEach(cita => {
                const fechaCita = cita.fecha_cita.split(' ')[0];
                const horaInt = parseInt(cita.hora_cita.split(':')[0], 10);
                const selector = `.calendar-day-column[data-dia="${fechaCita}"][data-hora="${horaInt}"]`;
                const celda = document.querySelector(selector);

                if (celda) {
                    let colorClass = "blue-card";
                    if (cita.estado == 2) colorClass = "green-card";
                    else if (cita.estado == 3) colorClass = "red-card";
                    
                    const card = document.createElement('div');
                    card.className = `event-card ${colorClass} patient-card`;
                    card.dataset.id = cita.id_cita;
                    card.dataset.name = cita.paciente;
                    card.dataset.treatment = cita.tratamiento;
                    card.dataset.date = fechaCita;
                    card.dataset.documento = cita.documento || '';
                    card.dataset.time = cita.hora_cita.substring(0,5);
                    card.dataset.status = cita.estado;
                    card.innerHTML = `<h5>${cita.paciente}</h5><p>${cita.tratamiento}</p><span class="ev-time">${cita.hora_cita.substring(0,5)}</span>`;
                    card.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation(); // Evitar que el listener global se cruce
                        
                        const estado = card.dataset.status;
                        if (estado == 2) {
                            Swal.fire('Completada', 'Esta cita ya fue completada.', 'info');
                            return;
                        }
                        if (estado == 3) {
                            fetchCancelInfo(card.dataset.id);
                            return;
                        }

                        const modalReprog = document.getElementById("modal-reprogramar");
                        if (modalReprog) {
                            currentAppointmentId = card.dataset.id;
                            const nombrePaciente = card.dataset.name;
                            const tratamientoPaciente = card.dataset.treatment;
                            const horaPaciente = card.dataset.time;
                            const fechaPaciente = card.dataset.date;
                            const elNombre = document.getElementById("modal-paciente-nombre");
                            const elTratamiento = document.getElementById("modal-paciente-tratamiento");
                            const elHorario = document.getElementById("modal-paciente-horario");
                            if (elNombre) elNombre.textContent = nombrePaciente;
                            if (elTratamiento) elTratamiento.textContent = tratamientoPaciente;
                            if (elHorario) elHorario.textContent = `Programada para el: ${fechaPaciente} a las ${horaPaciente} hs`;
                            modalReprog.style.display = 'flex';
                            modalReprog.classList.add('active');
                        }
                    });
                    celda.appendChild(card);
                }
            });
        } catch (error) { console.error("Error al inyectar citas:", error); }
    }

    async function cargarSidebarPacientesHoy(){
        try {
            const respuesta = await fetch("index.php?action=odontologo/agenda/obtener_citas_hoy");
            let citasHoy = await respuesta.json();
            
            const ahora = new Date();
            const horaActual = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');
            
            citasHoy = citasHoy.filter(cita => {
                if (cita.estado != 1 && cita.estado !== "Pendiente") return false;
                const horaCita = cita.hora_cita ? cita.hora_cita.substring(0, 5) : "00:00";
                return horaCita >= horaActual;
            });

            const sidebarList = document.querySelector(".patient-list");
            if(!sidebarList) return;

            let html = "";
            if(citasHoy.length === 0) {
                html = "<p style='text-align:center; margin-top:20px; color:#888; font-size: 0.9em;'>No hay pacientes programados para hoy.</p>";
            } else {
                citasHoy.forEach(cita => {
                    const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(cita.paciente)}&background=random&color=fff&rounded=true`;
                    html += `
                        <div class="patient-sidebar-item" 
                             data-id="${cita.id_cita}"
                             data-name="${cita.paciente}"
                             data-treatment="${cita.tratamiento || ''}"
                             data-time="${cita.hora_cita || ''}"
                             data-date="${cita.fecha_cita || ''}"
                             data-documento="${cita.documento || ''}">
                            <img src="${avatarUrl}" alt="Avatar de ${cita.paciente}">
                            <div class="p-meta">
                                <h4>${cita.paciente}</h4>
                                <p>ID: ${cita.documento}</p>
                            </div>
                        </div>
                    `;
                });
            }
            sidebarList.innerHTML = html;
        } catch (error) { console.error("Error en la barra lateral:", error); }
    }

    async function renderizarHorasDisponibles(fechaSeleccionada) {
        const container = document.getElementById("time-slots-container");
        if (!container) return;
        container.innerHTML = "Cargando horarios..."; 

        try {
            const url = `index.php?action=odontologo/agenda/obtener_horas_ocupadas&fecha=${fechaSeleccionada}`;
            const response = await fetch(url);
            const horasOcupadas = await response.json();
            const todasLasHoras = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00"];
            container.innerHTML = ""; 

            todasLasHoras.forEach(hora => {
                const btn = document.createElement("button");
                btn.className = "time-slot-btn";
                btn.textContent = hora;
                btn.type = "button"; 
                
                if (horasOcupadas.includes(hora)) {
                    btn.disabled = true;
                    btn.classList.add("slot-ocupado");
                    btn.style.backgroundColor = "#e0e0e0";
                    btn.style.color = "#999";
                    btn.style.cursor = "not-allowed";
                } else {
                    btn.addEventListener("click", () => {
                        document.querySelectorAll(".time-slot-btn").forEach(b => b.classList.remove("active-slot"));
                        btn.classList.add("active-slot");
                    });
                }
                container.appendChild(btn);
            });
        } catch (e) { console.error("Error detectado en el proceso:", e); }
    }

    const confirmBtn = document.querySelector(".btn-confirm-reprogram");
    if (confirmBtn) {
        confirmBtn.addEventListener("click", async (e) => {
            e.preventDefault(); 
            e.stopPropagation();

            const selectedHour = document.querySelector(".time-slot-btn.active-slot");
            if (!selectedHour) {
                Swal.fire({ title: 'Atención', text: 'Debes seleccionar una hora primero.', icon: 'warning' });
                return;
            }

            const nuevaHoraTexto = selectedHour.textContent.trim();
            const nuevaFechaFormateada = `${miniSelectedYear}-${(miniSelectedMonth + 1).toString().padStart(2, '0')}-${miniSelectedDay.toString().padStart(2, '0')}`;
            const inputMotivo = document.querySelector(".form-control-input");
            const motivoTexto = inputMotivo ? inputMotivo.value : "Cambio de horario";
            const fechaHoraSQL = `${nuevaFechaFormateada} ${nuevaHoraTexto}:00`;

            try {
                const respuestaGuardado = await fetch("index.php?action=odontologo/agenda/reprogramar_cita", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        id_cita: currentAppointmentId,
                        nueva_fecha_hora: fechaHoraSQL,
                        motivo: motivoTexto
                    })
                });

                const resultadoBD = await respuestaGuardado.json();
                if (resultadoBD.status === "success") {
                    Swal.fire({ title: '¡Cita Reprogramada!', text: 'La cita ha sido actualizada.', icon: 'success' })
                    .then(() => { window.location.reload(); });
                } else {
                    Swal.fire({ title: 'Error', text: resultadoBD.message, icon: 'error' });
                }
            } catch (error) {}
        });
    }

    const btnBuscar = document.getElementById("btnBuscarPaciente");
    if (btnBuscar) {
        btnBuscar.addEventListener("click", async () => {
            const docInput = document.getElementById("modal-nuevo-documento") || document.getElementById("docInput");
            if (!docInput) return;
            const doc = docInput.value;
            const res = await fetch(`index.php?action=odontologo/agenda/buscar_paciente&doc=${doc}`);
            const data = await res.json();
            
            if (data.encontrado) {
                if(document.getElementById("nombrePacienteLabel")) {
                    document.getElementById("nombrePacienteLabel").textContent = "Paciente: " + data.nombre;
                    document.getElementById("nombrePacienteLabel").style.display = "block";
                }
                if(document.getElementById("idPacienteHidden")) document.getElementById("idPacienteHidden").value = data.ID_PACIENTE;
            } else {
                Swal.fire("Error", "Paciente no encontrado", "error");
            }
        });
    }

    const btnIniciar = document.getElementById('btn-iniciar-treatment') || document.getElementById('btn-iniciar-tratamiento');
    if (btnIniciar) {
        btnIniciar.addEventListener('click', (e) => {
            e.preventDefault();
            const idCita = document.getElementById("idCitaOculto")?.value;
            if (idCita) { window.location.href = `index.php?action=odontologo/planes&id_cita=${idCita}`; } 
            else { alert("Error: No se pudo identificar la cita."); }
        });
    }

    async function fetchCancelInfo(id_cita) {
        try {
            const res = await fetch(`index.php?action=odontologo/agenda/obtener_info_cancelacion&id=${id_cita}`);
            const data = await res.json();
            if(data && data.status !== 'error') {
                Swal.fire({
                    title: 'Cita Cancelada',
                    html: `<b>Fecha de cancelación:</b> ${data.fecha}<br><b>Motivo:</b> ${data.motivo}`,
                    icon: 'error'
                });
            } else {
                Swal.fire('Cita Cancelada', 'Esta cita fue cancelada por el paciente.', 'error');
            }
        } catch (e) {
            Swal.fire('Cita Cancelada', 'Esta cita fue cancelada por el paciente.', 'error');
        }
    }

    // Ejecuciones iniciales
    cargarProcedimientos();
    cargarCitas();
    cargarSidebarPacientesHoy();
});