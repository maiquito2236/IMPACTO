document.addEventListener("DOMContentLoaded", () => {

    /* ==========================================================================
       TOAST / NOTIFICACIONES FLOTANTES
       ========================================================================== */
    function mostrarToast(mensaje, tipo = "info") {
        const toast = document.getElementById("toastInfo");
        if (!toast) return;

        toast.className = "toast-info show";
        if (tipo === "success") toast.classList.add("toast-success");
        if (tipo === "warning") toast.classList.add("toast-warning");

        toast.textContent = mensaje;

        setTimeout(() => {
            toast.classList.remove("show", "toast-success", "toast-warning");
        }, 4000);
    }

    /* ==========================================================================
       MANEJO DEL MODAL DE INFORMACIÓN DEL PACIENTE
       ========================================================================== */
    const modal = document.getElementById("modalPaciente");
    const cerrar = document.querySelector(".cerrar-modal-paciente");

    document.querySelectorAll(".ver-paciente").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            
            document.getElementById("infoNombre").textContent = btn.dataset.nombre;
            document.getElementById("infoDocumento").textContent = btn.dataset.documento;
            document.getElementById("infoTratamiento").textContent = btn.dataset.tratamiento;
            document.getElementById("infoFecha").textContent = btn.dataset.fecha;
            document.getElementById("infoTelefono").textContent = btn.dataset.telefono;

            if(modal) modal.style.display = "flex";
        });
    });

    if (cerrar && modal) {
        cerrar.addEventListener("click", () => { modal.style.display = "none"; });
    }

    window.addEventListener("click", (e) => {
        if (modal && e.target === modal) modal.style.display = "none";
    });

    /* ==========================================================================
       FILTRO DINÁMICO DE INGRESOS ANALÍTICOS
       ========================================================================== */
    const filtro = document.getElementById("filtroIngresos");
    const incomeLabel = document.getElementById("incomeLabel");
    const briefIncome = document.getElementById("briefIncome");

    if (filtro && incomeLabel && briefIncome) {
        filtro.addEventListener("change", () => {
            const valorSeleccionado = (filtro.value === "mes") ? filtro.dataset.mes : filtro.dataset.semana;
            
            incomeLabel.textContent = valorSeleccionado;
            briefIncome.textContent = valorSeleccionado;

            mostrarToast(`Balance de ingresos actualizado a: ${filtro.value === 'mes' ? 'Mensual' : 'Semanal'}`, "success");
        });
    }
});