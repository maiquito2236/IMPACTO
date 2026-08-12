document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // 1. MODAL CITAS
    // ==========================================
    const modalCitas = document.getElementById("modalCitas");
    const openCitas = document.getElementById("openCitas");
    const closeCitas = document.getElementById("closeCitas");

    if (openCitas && modalCitas) {
        openCitas.addEventListener("click", (e) => {
            e.preventDefault();
            modalCitas.style.display = "flex";
        });
    }

    if (closeCitas && modalCitas) {
        closeCitas.addEventListener("click", () => {
            modalCitas.style.display = "none";
        });
    }

    // ==========================================
    // 2. MODAL PACIENTES
    // ==========================================
    const modalPacientes = document.getElementById("modalPacientes");
    const openPacientes = document.getElementById("openPacientes");
    const closePacientes = document.getElementById("closePacientes");

    if (openPacientes && modalPacientes) {
        openPacientes.addEventListener("click", (e) => {
            e.preventDefault();
            modalPacientes.style.display = "flex";
        });
    }

    if (closePacientes && modalPacientes) {
        closePacientes.addEventListener("click", () => {
            modalPacientes.style.display = "none";
        });
    }

    // ==========================================
    // 3. MODAL INFORMACION PACIENTE (DINÁMICO)
    // ==========================================
    const modalInfo = document.getElementById("modalInfoPaciente");
    const closeInfo = document.getElementById("closeInfo");

    const patientAvatar = document.getElementById("patientAvatar");
    const patientName = document.getElementById("patientName");
    const patientEdad = document.getElementById("patientEdad");
    const patientTelefono = document.getElementById("patientTelefono");
    const patientTratamiento = document.getElementById("patientTratamiento");
    const patientVisita = document.getElementById("patientVisita");

    const botonesVerPaciente = document.querySelectorAll(".btn-ver-paciente");

    botonesVerPaciente.forEach(boton => {
        boton.addEventListener("click", () => {
            if (patientAvatar) patientAvatar.textContent = boton.dataset.iniciales;
            if (patientName) patientName.textContent = boton.dataset.nombre;
            if (patientEdad) patientEdad.textContent = boton.dataset.edad;
            if (patientTelefono) patientTelefono.textContent = boton.dataset.telefono;
            if (patientTratamiento) patientTratamiento.textContent = boton.dataset.tratamiento;
            if (patientVisita) patientVisita.textContent = boton.dataset.visita;

            if (modalInfo) modalInfo.style.display = "flex";
        });
    });

    if (closeInfo && modalInfo) {
        closeInfo.addEventListener("click", () => {
            modalInfo.style.display = "none";
        });
    }

    // CERRAR MODALES AL DAR CLICK AFUERA
    window.addEventListener("click", (e) => {
        if (modalCitas && e.target === modalCitas) modalCitas.style.display = "none";
        if (modalPacientes && e.target === modalPacientes) modalPacientes.style.display = "none";
        if (modalInfo && e.target === modalInfo) modalInfo.style.display = "none";
    });

    // ==========================================
    // 4. FILTRO DE INGRESOS
    // ==========================================
    const selector = document.getElementById("incomeSelector");
    const amount = document.getElementById("incomeAmount");
    const title = document.querySelector(".income-title");

    if (selector && amount) {
        selector.addEventListener("change", () => {
            if (selector.value === "mes") {
                amount.textContent = selector.dataset.mes;
                if (title) title.textContent = "Ingresos del mes";
            } else {
                amount.textContent = selector.dataset.semana;
                if (title) title.textContent = "Ingresos de la semana";
            }
        });
    }

    // ==========================================
    // 5. CONTROL Y BORRADO ASÍNCRONO DE ALERTAS
    // ==========================================
    const listaNotificaciones = document.querySelector(".notifications-list");

    if (listaNotificaciones) {
        listaNotificaciones.addEventListener("click", function (e) {
            const tarjetaAlerta = e.target.closest(".notification-item.interactiva");
            
            if (tarjetaAlerta) {
                e.preventDefault(); // Detiene el salto instantáneo
                
                const idNotificacion = tarjetaAlerta.dataset.id;
                const rutaRedireccion = tarjetaAlerta.getAttribute("href");

                // 1. MODIFICACIÓN VISUAL INMEDIATA (Para que el usuario vea que se borró al instante)
                tarjetaAlerta.style.transition = "all 0.25s ease";
                tarjetaAlerta.style.opacity = "0";
                tarjetaAlerta.style.transform = "translateX(40px)";

                // Restar el contador en caliente ("5 Nuevas" -> "4 Nuevas")
                const contadorBadge = document.querySelector(".notification-badge-count");
                if (contadorBadge) {
                    let cantidadActual = parseInt(contadorBadge.textContent) || 0;
                    if (cantidadActual > 0) {
                        cantidadActual--;
                        contadorBadge.textContent = `${cantidadActual} Nuevas`;
                    }
                }

                setTimeout(() => {
                    tarjetaAlerta.remove();
                    
                    if (listaNotificaciones.querySelectorAll(".notification-item.interactiva").length === 0) {
                        listaNotificaciones.innerHTML = `
                            <div class="no-notifications">
                                <i class="fas fa-bell-slash"></i>
                                <p>No tienes alertas o recordatorios pendientes.</p>
                            </div>
                        `;
                    }
                }, 250);

                // 2. ENVIAR LA PETICIÓN EN SEGUNDO PLANO
                const formData = new FormData();
                formData.append("id", idNotificacion);

                fetch("../../Controllers/eliminar_notificacion.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Una vez guardado con éxito en la BD, hacemos el cambio de página
                    if (rutaRedireccion && rutaRedireccion !== "#") {
                        window.location.href = rutaRedireccion;
                    }
                })
                .catch(error => {
                    console.error("Error al guardar estado:", error);
                    // Si falla la red, redirige igual para no congelar la app
                    if (rutaRedireccion && rutaRedireccion !== "#") {
                        window.location.href = rutaRedireccion;
                    }
                });
            }
        });
    }
});