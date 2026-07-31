/**
 * SISTEMA AUTOMATIZADO DE CITAS - ODONTO ESTÉTICA
 * Gestiona el calendario dinámico con zona horaria de Bogotá, la selección
 * de servicios/horas, el flujo por pasos secuencial (Wizard) y sus validaciones.
 */

document.addEventListener("DOMContentLoaded", function () {
    
    // =========================================================================
    // 1. CONFIGURACIÓN DEL FLUJO POR PASOS (WIZARD) Y ELEMENTOS DOM
    // =========================================================================
    const colServicio = document.getElementById("btn-continuar-servicio").closest('.col-1024-4');
    const colFechaHora = document.getElementById("btn-continuar-fecha").closest('.col-1024-4');
    const colResumen = document.getElementById("btn-confirmar-final").closest('.col-1024-4');

    const stepIndicator1 = document.getElementById('step-indicator-1');
    const stepIndicator2 = document.getElementById('step-indicator-2');
    const stepIndicator3 = document.getElementById('step-indicator-3');

    const btnContinuarServicio = document.getElementById('btn-continuar-servicio');
    const btnContinuarFecha = document.getElementById('btn-continuar-fecha');
    const btnVolverFecha = document.getElementById('btn-volver-fecha');
    const btnVolverResumen = document.getElementById('btn-volver-resumen');
    const botonFinalConfirmar = document.getElementById('btn-confirmar-final');

    const daysContainer = document.getElementById("calendar-days-container");
    const selectedDateLabel = document.getElementById("selected-date-label");
    const contenedorHorarios = document.getElementById("contenedor-horarios");
    const horariosDisponibles = JSON.parse(contenedorHorarios.getAttribute("data-horarios") || "[]");
    const formCita = document.getElementById('form-agendar-cita');

    // Inputs Ocultos de envío de datos corporativos
    const inputProcedimientoId = document.getElementById('input-procedimiento-id');
    const inputHorarioId = document.getElementById('input-horario-id');
    const inputFechaSeleccionada = document.getElementById('input-fecha-seleccionada');
    const inputHoraSeleccionada = document.getElementById('input-hora-seleccionada');

    // Elementos del Contenedor de Resumen (Paso 3)
    const resumenServicio = document.getElementById("summary-service");
    const resumenDate = document.getElementById("summary-date");
    const resumenTime = document.getElementById("summary-time");
    const resumenDuration = document.getElementById("summary-duration");
    const resumenPrecio = document.getElementById("summary-price");
    const resumenDoctor = document.getElementById("summary-doctor");
    const contenedorOdontologosWrapper = document.getElementById("contenedor-odontologos-wrapper");
    const contenedorOdontologos = document.getElementById("contenedor-odontologos");

    /**
     * Sincroniza visualmente los steppers superiores según el paso activo
     */
    function actualizarBarraProgreso(pasoActivo) {
        const pasos = [stepIndicator1, stepIndicator2, stepIndicator3];
        pasos.forEach((paso, index) => {
            if (!paso) return;
            const numeroPaso = index + 1;
            const badge = paso.querySelector('.badge');
            const texto = paso.querySelector('span:not(.badge)');

            if (numeroPaso === pasoActivo) {
                badge.className = "badge bg-primary rounded-circle d-flex align-items-center justify-content-center";
                texto.className = "text-primary fw-bold small";
            } else if (numeroPaso < pasoActivo) {
                badge.className = "badge bg-success rounded-circle d-flex align-items-center justify-content-center";
                texto.className = "text-success fw-bold small";
            } else {
                badge.className = "badge bg-secondary-subtle text-muted rounded-circle d-flex align-items-center justify-content-center";
                texto.className = "text-muted small";
            }
        });
    }

    function gestionarEstadoColumna(columna, bloquear) {
        if (!columna) return;
        if (bloquear) {
            columna.classList.add('opacity-50', 'pe-none');
        } else {
            columna.classList.remove('opacity-50', 'pe-none');
        }
    }

    // Inicialización del estado del asistente de navegación
    gestionarEstadoColumna(colFechaHora, true);
    gestionarEstadoColumna(colResumen, true);
    actualizarBarraProgreso(1);

    // --- ESCUCHADORES DE EVENTOS DEL WIZARD ---

    btnContinuarServicio.addEventListener('click', function () {
        if (!inputProcedimientoId.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección requerida',
                text: 'Por favor, selecciona un tratamiento dental antes de continuar.',
                confirmButtonColor: '#0b57d0',
                heightAuto: false 
            });
            return;
        }
        gestionarEstadoColumna(colFechaHora, false);
        gestionarEstadoColumna(colServicio, true);
        actualizarBarraProgreso(2);
    });

    btnContinuarFecha.addEventListener('click', function () {
        if (!inputFechaSeleccionada.value || !inputHoraSeleccionada.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha u Hora incompleta',
                text: 'Debes seleccionar un día en el calendario y una hora antes de continuar.',
                confirmButtonColor: '#0b57d0',
                heightAuto: false
            });
            return;
        }
        if (!inputHorarioId.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Odontólogo incompleto',
                text: 'Debes seleccionar un odontólogo para la hora elegida.',
                confirmButtonColor: '#0b57d0',
                heightAuto: false
            });
            return;
        }
        gestionarEstadoColumna(colResumen, false);
        gestionarEstadoColumna(colFechaHora, true);
        actualizarBarraProgreso(3);
    });

    btnVolverFecha.addEventListener('click', function () {
        gestionarEstadoColumna(colServicio, false);
        gestionarEstadoColumna(colFechaHora, true);
        actualizarBarraProgreso(1);
    });

    btnVolverResumen.addEventListener('click', function (e) {
        e.preventDefault(); 
        gestionarEstadoColumna(colFechaHora, false);
        gestionarEstadoColumna(colResumen, true);
        actualizarBarraProgreso(2);
    });

    // =========================================================================
    // 2. LÓGICA AUTOMATIZADA DEL CALENDARIO (ZONA HORARIA BOGOTÁ)
    // =========================================================================
    let especialidadSeleccionada = null;
    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    const dayNames = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];
    
    const bogotaString = new Date().toLocaleString("en-US", { timeZone: "America/Bogota" });
    const realBogotaDate = new Date(bogotaString);
    const horaActualStr = realBogotaDate.getHours().toString().padStart(2, '0') + ':' + realBogotaDate.getMinutes().toString().padStart(2, '0') + ':00';
    
    let currentMonth = realBogotaDate.getMonth();
    let currentYear = realBogotaDate.getFullYear();

    const monthLabel = document.getElementById("current-month");
    const prevBtn = document.getElementById("prev-month");
    const nextBtn = document.getElementById("next-month");

    // Filtrar fechas únicas con disponibilidad real activa
    let fechasDisponibles = new Set();

    function actualizarFechasDisponibles() {
        fechasDisponibles = new Set(
            horariosDisponibles
                .filter(h => {
                    if (h.ESTADO !== "Disponible") return false;
                    
                    if (especialidadSeleccionada) {
                        const espDocArr = (h.ESPECIALIDADES || "").toString().split(",");
                        if (!espDocArr.includes(especialidadSeleccionada.toString())) return false;
                    }

                    const [yearSel, monthSel, daySel] = h.FECHA.split('-');
                    const esHoy = (parseInt(yearSel) === realBogotaDate.getFullYear() && (parseInt(monthSel) - 1) === realBogotaDate.getMonth() && parseInt(daySel) === realBogotaDate.getDate());
                    if (esHoy && h.HORA_INICIO <= horaActualStr) return false;
                    return true;
                })
                .map(h => h.FECHA)
        );
    }
    actualizarFechasDisponibles();

    /**
     * Convierte cadenas de hora militar a formato estándar AM/PM
     */
    function formatearHora12h(horaString) {
        const partes = horaString.split(':');
        let horas = parseInt(partes[0]);
        const minutos = partes[1];
        const ampm = horas >= 12 ? 'PM' : 'AM';
        horas = horas % 12;
        horas = horas ? horas : 12; 
        return `${horas}:${minutos} ${ampm}`;
    }

    function mostrarHorarios(fechaSeleccionada) {
        contenedorHorarios.innerHTML = "";
        if (contenedorOdontologosWrapper) contenedorOdontologosWrapper.style.display = "none";
        
        let horariosDia = horariosDisponibles.filter(h => {
            if (h.FECHA !== fechaSeleccionada || h.ESTADO !== "Disponible") return false;
            if (especialidadSeleccionada) {
                const espDocArr = (h.ESPECIALIDADES || "").toString().split(",");
                if (!espDocArr.includes(especialidadSeleccionada.toString())) return false;
            }
            return true;
        });

        // Filtrar horas que ya pasaron si es hoy
        const [yearSel, monthSel, daySel] = fechaSeleccionada.split('-');
        const esHoy = (parseInt(yearSel) === realBogotaDate.getFullYear() && (parseInt(monthSel) - 1) === realBogotaDate.getMonth() && parseInt(daySel) === realBogotaDate.getDate());
        
        if (esHoy) {
            horariosDia = horariosDia.filter(h => h.HORA_INICIO > horaActualStr);
        }

        if (horariosDia.length === 0) {
            contenedorHorarios.innerHTML = `<div class="col-12 text-muted font-xs">No hay horarios para este día o ya pasaron.</div>`;
            return;
        }

        // Agrupar por hora de inicio para no repetir botones de hora
        const horasUnicas = [];
        const horariosPorHora = {};
        
        horariosDia.forEach(horario => {
            if (!horariosPorHora[horario.HORA_INICIO]) {
                horariosPorHora[horario.HORA_INICIO] = [];
                horasUnicas.push(horario.HORA_INICIO);
            }
            horariosPorHora[horario.HORA_INICIO].push(horario);
        });

        horasUnicas.sort().forEach(horaInicio => {
            const col = document.createElement("div");
            col.className = "col";
            const horaFormateada = formatearHora12h(horaInicio);

            col.innerHTML = `
                <button type="button" class="btn btn-hour w-100 font-xs py-2" data-hora="${horaInicio}">
                    ${horaFormateada}
                </button>
            `;
            contenedorHorarios.appendChild(col);
        });

        activarEventosHoras(horariosPorHora);
    }

    function activarEventosHoras(horariosPorHora) {
        const horas = document.querySelectorAll(".btn-hour");
        horas.forEach(boton => {
            boton.addEventListener("click", () => {
                horas.forEach(h => h.classList.remove("active"));
                boton.classList.add("active");

                // Reset doctor selection
                inputHorarioId.value = "";
                if (resumenDoctor) resumenDoctor.textContent = "--";

                const horaSeleccionada = boton.getAttribute('data-hora');
                inputHoraSeleccionada.value = horaSeleccionada;
                
                if (resumenTime) {
                    resumenTime.textContent = boton.textContent.trim();
                }

                // Mostrar doctores disponibles para esta hora
                mostrarDoctores(horariosPorHora[horaSeleccionada]);
            });
        });
    }

    function mostrarDoctores(doctoresArray) {
        if (!contenedorOdontologosWrapper || !contenedorOdontologos) return;
        
        contenedorOdontologos.innerHTML = "";
        contenedorOdontologosWrapper.style.display = "block";

        doctoresArray.forEach(docHorario => {
            const label = document.createElement("label");
            label.className = "border rounded-3 p-3 d-flex align-items-center doc-card position-relative w-100 mb-2 shadow-sm transition-all";
            label.style.cursor = "pointer";
            
            label.innerHTML = `
                <input type="radio" name="doctor_radio" class="d-none" value="${docHorario.ID_HORARIO}">
                <span class="custom-radio-circle me-3"></span>
                <div class="bg-primary-light text-primary p-2 rounded-2 me-3">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="fs-6 fw-bold m-0 text-dark-blue">${docHorario.NOMBRE_DOCTOR}</h4>
                </div>
            `;

            label.addEventListener("click", () => {
                document.querySelectorAll(".doc-card").forEach(c => c.classList.remove("border-primary", "bg-primary-light", "selected"));
                label.classList.add("border-primary", "bg-primary-light", "selected");
                
                const radio = label.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;

                inputHorarioId.value = docHorario.ID_HORARIO;
                if (resumenDoctor) resumenDoctor.textContent = docHorario.NOMBRE_DOCTOR;
            });

            contenedorOdontologos.appendChild(label);
        });
    }

    function renderCalendar(month, year) {
        daysContainer.innerHTML = ""; 
        monthLabel.textContent = `${monthNames[month]} ${year}`;

        const firstDayIndex = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();
        const prevTotalDays = new Date(year, month, 0).getDate();

        // Relleno mes anterior
        for (let i = firstDayIndex; i > 0; i--) {
            const dayDiv = document.createElement("div");
            dayDiv.classList.add("text-light-gray");
            dayDiv.textContent = prevTotalDays - i + 1;
            daysContainer.appendChild(dayDiv);
        }

        // Renderizado de días válidos
        for (let i = 1; i <= totalDays; i++) {
            const dayDiv = document.createElement("div");
            dayDiv.textContent = i;

            const fechaMysql = `${year}-${String(month + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;
            const fechaCelda = new Date(year, month, i);
            const hoySoloFecha = new Date(realBogotaDate.getFullYear(), realBogotaDate.getMonth(), realBogotaDate.getDate());
            const tieneHorario = fechasDisponibles.has(fechaMysql);

            // Requisito: Si no hay disponibilidad, bloquear la selección del día
            if (fechaCelda < hoySoloFecha || !tieneHorario) { 
                dayDiv.classList.add("text-light-gray");
                dayDiv.style.cursor = "not-allowed";
                dayDiv.style.opacity = "0.5";
            } else {
                dayDiv.style.cursor = "pointer";

                if (i === realBogotaDate.getDate() && month === realBogotaDate.getMonth() && year === realBogotaDate.getFullYear()) {
                    dayDiv.classList.add("fw-bold", "text-primary", "border", "border-primary", "square");
                }

                dayDiv.addEventListener("click", function() {
                    const clickedDate = new Date(year, month, i);
                    const dayName = dayNames[clickedDate.getDay()];
                    const monthName = monthNames[month].toLowerCase();
                    const textFormatted = `${dayName}, ${i} de ${monthName} de ${year}`;

                    inputFechaSeleccionada.value = fechaMysql;
                    mostrarHorarios(fechaMysql);
                    
                    selectedDateLabel.innerHTML = `Horas disponibles para el <br><strong>${textFormatted}</strong>`;
                    if (resumenDate) {
                        resumenDate.textContent = textFormatted.charAt(0).toUpperCase() + textFormatted.slice(1);
                    }
                    
                    document.querySelectorAll("#calendar-days-container div").forEach(d => {
                        d.classList.remove("bg-primary", "text-white", "square");
                    });
                    dayDiv.classList.add("bg-primary", "text-white", "square");
                });
            }
            daysContainer.appendChild(dayDiv);
        }

        // Relleno mes siguiente
        const totalSlots = 42;
        const currentSlotsUsed = firstDayIndex + totalDays;
        const nextMonthDaysNeeded = totalSlots - currentSlotsUsed;

        for (let i = 1; i <= nextMonthDaysNeeded; i++) {
            const dayDiv = document.createElement("div");
            dayDiv.classList.add("text-light-gray");
            dayDiv.textContent = i;
            daysContainer.appendChild(dayDiv);
        }
    }

    prevBtn.addEventListener("click", () => {
        const mesActualBogota = realBogotaDate.getMonth();
        const anioActualBogota = realBogotaDate.getFullYear();

        if (currentYear < anioActualBogota || (currentYear === anioActualBogota && currentMonth <= mesActualBogota)) {
            return;
        }
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar(currentMonth, currentYear);
    });

    nextBtn.addEventListener("click", () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar(currentMonth, currentYear);
    });

    renderCalendar(currentMonth, currentYear);

    // Auto-seleccionar primer día disponible mapeado en la BD
    const primeraFechaDisponible = [...fechasDisponibles].sort()[0];
    if (primeraFechaDisponible) {
        const partes = primeraFechaDisponible.split('-');
        const anioP = parseInt(partes[0]);
        const mesP = parseInt(partes[1]) - 1;
        const diaP = parseInt(partes[2]);

        if (anioP === currentYear && mesP === currentMonth) {
            const primerDiaNode = Array.from(daysContainer.children).find(el =>
                el.textContent == diaP && !el.classList.contains("text-light-gray")
            );
            if (primerDiaNode) primerDiaNode.click();
        }
    }

    // =============================================================================
    // 3. SELECCIÓN DE TARJETAS DE SERVICIO (ACTUALIZACIÓN DINÁMICA DEL RESUMEN)
    // =============================================================================
    const serviceCards = document.querySelectorAll(".service-card");

    serviceCards.forEach(card => {
        card.addEventListener("click", () => {
            serviceCards.forEach(c => c.classList.remove("selected"));
            card.classList.add("selected");

            const radioInput = card.querySelector('input[type="radio"]');
            if (radioInput) radioInput.checked = true;

            const idProcedimiento = card.getAttribute("data-id");
            const nombreServicio = card.getAttribute("data-nombre");
            const precioServicio = card.getAttribute("data-precio");
            const duracionServicio = card.getAttribute("data-duracion");
            const especialidadId = card.getAttribute("data-especialidad");

            especialidadSeleccionada = especialidadId;
            actualizarFechasDisponibles();
            
            // Re-render calendar so dates without this specialty are grayed out
            renderCalendar(currentMonth, currentYear);
            
            // Reset selected date and time to enforce re-selection based on new procedure
            inputFechaSeleccionada.value = '';
            inputHoraSeleccionada.value = '';
            inputHorarioId.value = '';
            selectedDateLabel.innerHTML = 'Selecciona una fecha';
            contenedorHorarios.innerHTML = '<div class="col-12 text-muted font-xs">Selecciona un día en el calendario para ver las horas disponibles.</div>';
            if (contenedorOdontologosWrapper) contenedorOdontologosWrapper.style.display = "none";
            if (resumenDate) resumenDate.textContent = '--';
            if (resumenTime) resumenTime.textContent = '--';
            if (resumenDoctor) resumenDoctor.textContent = '--';

            // Pasar información a los inputs ocultos del formulario corporativo
            inputProcedimientoId.value = idProcedimiento;

            // Renderizar dinámicamente el cuadro de resumen del paso 3
            if (resumenServicio) resumenServicio.textContent = nombreServicio;
            if (resumenPrecio) resumenPrecio.textContent = precioServicio;
            if (resumenDuration) resumenDuration.textContent = duracionServicio;
        });
    });

    // =============================================================================
    // 5. ENVÍO DE FORMULARIO Y ALERTA DE CONFIRMACIÓN FINAL
    // =============================================================================
    if (botonFinalConfirmar) {
        botonFinalConfirmar.addEventListener('click', function (e) {
            e.preventDefault();

            if (!inputProcedimientoId.value || !inputHorarioId.value || !inputFechaSeleccionada.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de integridad',
                    text: 'Faltan parámetros del proceso de agendamiento.',
                    confirmButtonColor: '#0b57d0'
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: '¡Cita Agendada Exitosamente!',
                html: '<p class="mb-2 text-muted font-sm">Tu cita ha sido guardada en nuestro sistema.</p>',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#0b57d0',
                heightAuto: false 
            }).then((result) => {
                if (result.isConfirmed) {
                    formCita.submit(); // Envío físico real de datos vía POST al controlador PHP
                }
            });
        });
    }
});