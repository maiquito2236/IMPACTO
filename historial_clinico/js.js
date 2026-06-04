// =========================
        // DATOS DIENTES
        // =========================

        const toothData = {

            15: {
                nombre: "Premolar Superior",
                estado: "Protección aplicada",
                tratamiento: "Sellante dental",
                doctor: "Dr. Andrés Díaz",
                fecha: "21 Mayo 2026"
            },

            16: {
                nombre: "Molar Superior",
                estado: "Caries avanzada",
                tratamiento: "Conducto radicular",
                doctor: "Dr. Andrés Díaz",
                fecha: "20 Mayo 2026"
            },

            36: {
                nombre: "Molar Inferior",
                estado: "Corona instalada",
                tratamiento: "Rehabilitación dental",
                doctor: "Dr. Andrés Díaz",
                fecha: "15 Mayo 2026"
            }

        };

        // =========================
        // MODAL
        // =========================

        const modal = document.getElementById('toothModal');

        const modalTitle = document.getElementById('modalToothTitle');

        const toothName = document.getElementById('toothName');
        const toothStatus = document.getElementById('toothStatus');
        const toothTreatment = document.getElementById('toothTreatment');
        const toothDoctor = document.getElementById('toothDoctor');
        const toothDate = document.getElementById('toothDate');

        // =========================
        // CLICK DIENTES
        // =========================

        document.querySelectorAll('.tooth-item').forEach(item => {

    item.addEventListener('click', () => {

        // Número del diente
        const toothNumber =
            item.querySelector('.tooth-number')
            .textContent
            .trim();

        // Buscar info
        const info = toothData[toothNumber];

        // Limpiar selección anterior
        document.querySelectorAll('.tooth-shape')
            .forEach(shape => {

                shape.style.border = '';
                shape.style.transform = '';
                shape.style.boxShadow = '';

            });

        // Resaltar seleccionado
        const currentTooth =
            item.querySelector('.tooth-shape');

        currentTooth.style.border =
            '3px solid #2563eb';

        currentTooth.style.transform =
            'scale(1.08)';

        currentTooth.style.boxShadow =
            '0 0 20px rgba(37,99,235,0.35)';

        // Título modal
        modalTitle.textContent =
            `Diente #${toothNumber}`;

        // Si tiene info
        if(info){

            toothName.textContent =
                info.nombre;

            toothStatus.textContent =
                info.estado;

            toothTreatment.textContent =
                info.tratamiento;

            toothDoctor.textContent =
                info.doctor;

            toothDate.textContent =
                info.fecha;

        }

        // Si NO tiene info
        else {

            toothName.textContent =
                "Sin información";

            toothStatus.textContent =
                "Pendiente de valoración";

            toothTreatment.textContent =
                "No registrado";

            toothDoctor.textContent =
                "No asignado";

            toothDate.textContent =
                "Sin fecha";

        }

        // Abrir modal
        modal.classList.add('active');

    });

});

        // =========================
        // CERRAR MODAL
        // =========================

        document.getElementById('closeModal')
            .addEventListener('click', () => {

                modal.classList.remove('active');

            });

        modal.addEventListener('click', (e) => {

            if (e.target === modal) {
                modal.classList.remove('active');
            }

        });

        // =========================
        // TIMELINE DATA
        // =========================

        const timelineData = {

            day: [

                {
                    hora: "10:30 AM",
                    fecha: "21 Mayo 2026",
                    titulo: "Limpieza dental",
                    doctor: "Dr. Andrés Díaz",
                    estado: "Finalizado"
                },

                {
                    hora: "12:00 PM",
                    fecha: "21 Mayo 2026",
                    titulo: "Radiografía panorámica",
                    doctor: "Dr. Andrés Díaz",
                    estado: "Pendiente"
                }

            ],

            month: [

                {
                    hora: "08:30 AM",
                    fecha: "15 Mayo 2026",
                    titulo: "Conducto radicular",
                    doctor: "Dr. Andrés Díaz",
                    estado: "Finalizado"
                },

                {
                    hora: "11:15 AM",
                    fecha: "09 Mayo 2026",
                    titulo: "Revisión ortodoncia",
                    doctor: "Dr. Andrés Díaz",
                    estado: "En proceso"
                }

            ],

            year: [

                {
                    hora: "09:00 AM",
                    fecha: "15 Enero 2026",
                    titulo: "Inicio tratamiento",
                    doctor: "Dr. Andrés Díaz",
                    estado: "Finalizado"
                },

                {
                    hora: "11:20 AM",
                    fecha: "22 Marzo 2026",
                    titulo: "Blanqueamiento dental",
                    doctor: "Dr. Andrés Díaz",
                    estado: "Finalizado"
                }

            ]

        };

        // =========================
        // TIMELINE
        // =========================

        const timelineContainer =
            document.getElementById('timelineContainer');

        function renderTimeline(filter) {

            timelineContainer.innerHTML = '';

            timelineData[filter].forEach(item => {

                const statusClass =
                    item.estado === 'Finalizado'
                        ? 'status-finished'
                        : '';

                timelineContainer.innerHTML += `

                <div class="timeline-item">

                    <div class="timeline-meta">

                        <span class="time">
                            ${item.hora}
                        </span>

                        <span class="date">
                            ${item.fecha}
                        </span>

                    </div>

                    <div class="timeline-point"></div>

                    <div class="timeline-body">

                        <div class="body-header">

                            <h4>${item.titulo}</h4>

                            <span class="badge ${statusClass}">
                                ${item.estado}
                            </span>

                        </div>

                        <p class="doctor-tag">
                            Médico: ${item.doctor}
                        </p>

                    </div>

                </div>

                `;

            });

        }

        // =========================
        // BOTONES FILTER
        // =========================

        document.querySelectorAll('.timeline-filter')
            .forEach(button => {

                button.addEventListener('click', () => {

                    document.querySelectorAll('.timeline-filter')
                        .forEach(btn =>
                            btn.classList.remove('active')
                        );

                    button.classList.add('active');

                    renderTimeline(button.dataset.filter);

                });

            });

        // =========================
        // LOAD
        // =========================

        renderTimeline('day');