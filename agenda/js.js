document.addEventListener("DOMContentLoaded", () => {

    /* =====================================
       BOTONES DE VISTA DEL CALENDARIO
    ===================================== */

    const toggleButtons = document.querySelectorAll(".btn-toggle");
    const calendarGrid = document.querySelector(".calendar-grid");

    const originalCalendar = calendarGrid.innerHTML;

    toggleButtons.forEach(btn => {

        btn.addEventListener("click", () => {

            toggleButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const text = btn.textContent.trim();

            if(text === "Ver Hoy"){

                mostrarVistaHoy();

            }else if(text === "Semana"){

                mostrarVistaSemana();

            }else if(text === "Mes"){

                mostrarVistaMes();

            }

        });

    });

    /* =====================================
       VISTA HOY
    ===================================== */

    function mostrarVistaHoy(){

        calendarGrid.classList.remove("month-view");

        calendarGrid.innerHTML = `

            <div class="today-view-container">

                <div class="today-header">

                    <h2>Agenda de Hoy</h2>

                    <span>Jueves 14 Mayo 2026</span>

                </div>

                <div class="today-events-list">

                    ${crearEventoHoy(
                        "09:00 AM",
                        "Alejandra Torres",
                        "Ortodoncia",
                        "Confirmada",
                        "green-card"
                    )}

                    ${crearEventoHoy(
                        "11:30 AM",
                        "Maria Fernanda",
                        "Limpieza",
                        "Pendiente",
                        "blue-card"
                    )}

                    ${crearEventoHoy(
                        "01:00 PM",
                        "Carlos Ramirez",
                        "Consulta General",
                        "Confirmada",
                        "purple-card"
                    )}

                </div>

            </div>

        `;

    }

    function crearEventoHoy(
        hora,
        nombre,
        tratamiento,
        estado,
        color
    ){

        return `

            <div class="today-event-card event-card ${color} patient-card"

                data-name="${nombre}"
                data-treatment="${tratamiento}"
                data-time="${hora}"
                data-id="${Math.floor(Math.random() * 999999)}"
                data-status="${estado}">

                <div class="today-event-hour">
                    ${hora}
                </div>

                <div class="today-event-info">

                    <h4>${nombre}</h4>

                    <p>${tratamiento}</p>

                    <span>${estado}</span>

                </div>

            </div>

        `;

    }

    /* =====================================
       VISTA SEMANA
    ===================================== */

    function mostrarVistaSemana(){

        calendarGrid.classList.remove("month-view");

        calendarGrid.innerHTML = originalCalendar;

    }

    /* =====================================
       VISTA MES
    ===================================== */

    function mostrarVistaMes(){

        calendarGrid.classList.add("month-view");

        calendarGrid.innerHTML = `

            <div class="month-header">

                <h2>Mayo 2026</h2>

            </div>

            <div class="month-days-row">

                <div>Lun</div>
                <div>Mar</div>
                <div>Mié</div>
                <div>Jue</div>
                <div>Vie</div>
                <div>Sáb</div>
                <div>Dom</div>

            </div>

            <div class="month-grid">

                ${crearDiasMes()}

            </div>

        `;

    }

    /* =====================================
       CREAR DÍAS DEL MES
    ===================================== */

    function crearDiasMes(){

        let html = "";

        const citasPorDia = {

            2: [
                {
                    nombre: "Alejandra Torres",
                    tratamiento: "Ortodoncia",
                    hora: "08:00 AM",
                    color: "green-card",
                    id: "12345",
                    estado: "Confirmada"
                }
            ],

            5: [
                {
                    nombre: "Juan Guarnizo",
                    tratamiento: "Limpieza",
                    hora: "10:30 AM",
                    color: "blue-card",
                    id: "54321",
                    estado: "Confirmada"
                }
            ],

            14: [

                {
                    nombre: "Maria Fernanda",
                    tratamiento: "Consulta",
                    hora: "02:00 PM",
                    color: "purple-card",
                    id: "99887",
                    estado: "Pendiente"
                },

                {
                    nombre: "Carlos Ramirez",
                    tratamiento: "Blanqueamiento",
                    hora: "04:00 PM",
                    color: "orange-card",
                    id: "77665",
                    estado: "Confirmada"
                }

            ]

        };

        for(let i = 1; i <= 31; i++){

            html += `

                <div class="month-day-card ${i === 14 ? "current-day" : ""}">

                    <div class="month-day-number">

                        ${i}

                    </div>

                    <div class="month-events-container">

            `;

            if(citasPorDia[i]){

                citasPorDia[i].forEach(cita => {

                    html += `

                        <div class="event-card ${cita.color} patient-card"

                            data-name="${cita.nombre}"
                            data-treatment="${cita.tratamiento}"
                            data-time="${cita.hora}"
                            data-id="${cita.id}"
                            data-status="${cita.estado}">

                            <h5>${cita.nombre}</h5>

                            <p>${cita.tratamiento}</p>

                            <span class="ev-time">
                                ${cita.hora}
                            </span>

                        </div>

                    `;

                });

            }

            html += `

                    </div>

                </div>

            `;

        }

        return html;

    }

    /* =====================================
       TARJETAS DE PACIENTES
    ===================================== */

    document.addEventListener("click", (e) => {

        const card = e.target.closest(".patient-card");

        if(!card) return;

        const name = card.dataset.name;
        const treatment = card.dataset.treatment;
        const time = card.dataset.time;
        const id = card.dataset.id;
        const status = card.dataset.status;

        document.querySelector(".orig-details h4").textContent =
        name;

        document.querySelector(".treatment-type").textContent =
        treatment;

        document.querySelector(".time-tag").textContent =
        time;

        mostrarAlertaPaciente(
            name,
            treatment,
            time,
            id,
            status
        );

    });

    /* =====================================
       ALERTA VISUAL
    ===================================== */

    function mostrarAlertaPaciente(
        name,
        treatment,
        time,
        id,
        status
    ){

        const oldAlert = document.querySelector(".patient-alert");

        if(oldAlert){

            oldAlert.remove();

        }

        const alerta = document.createElement("div");

        alerta.classList.add("patient-alert");

        alerta.innerHTML = `

            <div class="patient-alert-header">

                <h3>${name}</h3>

                <button class="close-alert">
                    ×
                </button>

            </div>

            <div class="patient-alert-content">

                <p>
                    <strong>Tratamiento:</strong>
                    ${treatment}
                </p>

                <p>
                    <strong>Hora:</strong>
                    ${time}
                </p>

                <p>
                    <strong>ID:</strong>
                    ${id}
                </p>

                <p>
                    <strong>Estado:</strong>
                    ${status}
                </p>

            </div>

        `;

        document.body.appendChild(alerta);

        const closeBtn = alerta.querySelector(".close-alert");

        closeBtn.addEventListener("click", () => {

            alerta.remove();

        });

        setTimeout(() => {

            alerta.classList.add("show-alert");

        }, 100);

    }

    /* =====================================
       HORARIOS
    ===================================== */

    const timeSlots = document.querySelectorAll(".time-slot-btn");

    timeSlots.forEach(slot => {

        slot.addEventListener("click", () => {

            timeSlots.forEach(s => {

                s.classList.remove("active-slot");

            });

            slot.classList.add("active-slot");

        });

    });

    /* =====================================
       CONFIRMAR REPROGRAMACIÓN
    ===================================== */

    const confirmBtn =
document.querySelector(".btn-confirm-reprogram");

if(confirmBtn){

    confirmBtn.addEventListener("click", (e) => {

        e.preventDefault();

        const selectedHour =
        document.querySelector(".active-slot");

        const selectedDay =
        document.querySelector(".selected-day-circle");

        if(!selectedHour){

            alert("Debes seleccionar una hora.");
            return;

        }

        if(!selectedDay){

            alert("Debes seleccionar un día.");
            return;

        }

       alert(
    "Cita reprogramada correctamente.\n\n" +
    "Nueva fecha: " +
    selectedDay + "/" +
    (selectedMonth + 1) + "/" +
    selectedYear +
    "\nHora: " +
    selectedHour.textContent
);

    });
}

    /* =====================================
       CANCELAR
    ===================================== */

    const cancelBtn =
document.querySelector(".btn-cancel-modal");

if(cancelBtn){

    cancelBtn.addEventListener("click", () => {

        alert("Reprogramación cancelada");

    });

}

    /* =====================================
       CAMPANA
    ===================================== */

    const bell =
document.querySelector(".notification-bell");

if (bell) {

    bell.addEventListener("click", () => {

        alert(
            "Notificaciones:\n\n" +
            "- 1 cita pendiente\n" +
            "- 2 pacientes confirmaron asistencia"
        );

    });

}

    /* =====================================
       MENÚ
    ===================================== */

    const menuItems =
    document.querySelectorAll(".nav-item");

    menuItems.forEach(item => {

        item.addEventListener("click", () => {

            menuItems.forEach(i => {

                i.classList.remove("active");

            });

            item.classList.add("active");

        });

    });

    /* =====================================
   SELECTOR PACIENTE
===================================== */

const patientSelector =
document.getElementById("patient-selector");

if (patientSelector) {

    patientSelector.addEventListener("change", () => {

        const option =
        patientSelector.options[
            patientSelector.selectedIndex
        ];

        const name =
        option.dataset.name || "---";

        const treatment =
        option.dataset.treatment || "---";

        const time =
        option.dataset.time || "---";

        const id =
        option.dataset.id || "---";

        const status =
        option.dataset.status || "---";

        document.querySelector(
            ".orig-details h4"
        ).textContent = name;

        document.querySelector(
            ".treatment-type"
        ).textContent = treatment;

        document.querySelector(
            ".time-tag"
        ).textContent = time;

        if(name !== "---"){

            mostrarAlertaPaciente(
                name,
                treatment,
                time,
                id,
                status
            );

        }

    });

}
/* =====================================
   MINI CALENDARIO DINÁMICO
===================================== */
const monthLabel =
document.getElementById("calendar-month-label");

const daysContainer =
document.getElementById("calendar-days");

const prevBtn =
document.querySelector(".prev-month");

const nextBtn =
document.querySelector(".next-month");

if(
    monthLabel &&
    daysContainer &&
    prevBtn &&
    nextBtn
){

    const monthNames = [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre"
    ];

    const today = new Date();

    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    let selectedDay = null;
    let selectedMonth = null;
    let selectedYear = null;

    selectedDay = today.getDate();
selectedMonth = today.getMonth();
selectedYear = today.getFullYear();

    function renderCalendar(){

        monthLabel.textContent =
        `${monthNames[currentMonth]} de ${currentYear}`;

        daysContainer.innerHTML = "";

        const firstDay =
        new Date(
            currentYear,
            currentMonth,
            1
        );

        let startDay =
        firstDay.getDay();

        startDay =
        startDay === 0 ? 6 : startDay - 1;

        const daysInMonth =
        new Date(
            currentYear,
            currentMonth + 1,
            0
        ).getDate();

        const prevMonthDays =
        new Date(
            currentYear,
            currentMonth,
            0
        ).getDate();

        for(
            let i = startDay;
            i > 0;
            i--
        ){

            const span =
            document.createElement("span");

            span.classList.add("muted-day");

            span.textContent =
            prevMonthDays - i + 1;

            daysContainer.appendChild(span);

        }

        for(
            let day = 1;
            day <= daysInMonth;
            day++
        ){

            const span =
            document.createElement("span");

            span.textContent = day;

            const isToday =
                day === today.getDate() &&
                currentMonth === today.getMonth() &&
                currentYear === today.getFullYear();

            if(
    day === selectedDay &&
    currentMonth === selectedMonth &&
    currentYear === selectedYear
){

    span.classList.add("selected-day-circle");

}

            span.addEventListener("click", () => {

    document
    .querySelectorAll("#calendar-days span")
    .forEach(el => {
        el.classList.remove("selected-day-circle");
    });

    span.classList.add("selected-day-circle");

    selectedDay = day;
    selectedMonth = currentMonth;
    selectedYear = currentYear;

});

            daysContainer.appendChild(span);

        }

    }

    prevBtn.addEventListener(
        "click",
        () => {

            currentMonth--;

            if(currentMonth < 0){

                currentMonth = 11;
                currentYear--;

            }

            renderCalendar();

        }
    );

    nextBtn.addEventListener(
        "click",
        () => {

            currentMonth++;

            if(currentMonth > 11){

                currentMonth = 0;
                currentYear++;

            }

            renderCalendar();

        }
    );

    renderCalendar();

}

});
