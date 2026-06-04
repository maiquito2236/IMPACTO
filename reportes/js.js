document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       DATA PACIENTES
    ========================= */
    const pacientes = [
        {
            id: 1,
            nombre: "Alejandra Torres",
            documento: "12345678",
            tratamiento: "Nuevo paciente",
            fecha: "15 Mayo, 2026",
            telefono: "300 111 2222",
            email: "alejandra@mail.com"
        },
        {
            id: 2,
            nombre: "Luis Eduardo Pérez",
            documento: "87654321",
            tratamiento: "Evaluación",
            fecha: "15 Mayo, 2026",
            telefono: "310 999 8888",
            email: "luis@mail.com"
        }
    ];

    /* =========================
       TOAST (ALERTA FLOTANTE)
    ========================= */
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

    /* =========================
       MODAL PACIENTE
    ========================= */
    const modal = document.getElementById("modalPaciente");
    const cerrar = document.querySelector(".cerrar-modal-paciente");

    function abrirModal(p) {
        document.getElementById("infoNombre").textContent = p.nombre;
        document.getElementById("infoDocumento").textContent = p.documento;
        document.getElementById("infoTratamiento").textContent = p.tratamiento;
        document.getElementById("infoFecha").textContent = p.fecha;
        document.getElementById("infoTelefono").textContent = p.telefono;
        document.getElementById("infoEmail").textContent = p.email;

        modal.style.display = "flex";
    }

    if (cerrar) {
        cerrar.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    /* =========================
       VER PACIENTE
    ========================= */
    document.querySelectorAll(".ver-paciente").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();

            const id = Number(btn.dataset.id);
            const paciente = pacientes.find(p => p.id === id);

            if (paciente) abrirModal(paciente);
        });
    });

    /* =========================
       VER TODAS LAS CITAS
    ========================= */
    let citasAbiertas = false;

    const btnCitas = document.getElementById("verTodasCitas");

if (btnCitas) {
    btnCitas.addEventListener("click", (e) => {
        e.preventDefault();

        const citas = document.querySelectorAll(".agenda-item");

        let mensaje = " CITAS DEL DÍA\n\n";

        citas.forEach((cita, i) => {
            const nombre = cita.querySelector(".meta-txt strong")?.textContent;
            const detalle = cita.querySelector(".meta-txt span")?.textContent;
            const hora = cita.querySelector(".agenda-time-meta strong")?.textContent;

            mensaje += ` ${i + 1}. ${hora}\n ${nombre}\n ${detalle}\n\n`;
        });

        mostrarToast(mensaje, "info");
    });
}

    /* =========================
       VER TODOS LOS PACIENTES
    ========================= */
    let pacientesAbiertos = false;

    const btnPacientes = document.getElementById("verTodosPacientes");

if (btnPacientes) {
    btnPacientes.addEventListener("click", (e) => {
        e.preventDefault();

        const filas = document.querySelectorAll("tbody tr");

        let mensaje = " PACIENTES REGISTRADOS\n\n";

        filas.forEach((fila, i) => {
            const nombre = fila.querySelector("strong")?.textContent;
            const tratamiento = fila.querySelectorAll("td")[1]?.textContent;
            const fecha = fila.querySelectorAll("td")[2]?.textContent;

            mensaje += ` ${i + 1}. ${nombre}\n ${tratamiento}\n ${fecha}\n\n`;
        });

        mostrarToast(mensaje, "success");
    });
}

    /* =========================
       INGRESOS
    ========================= */
    const filtro = document.getElementById("filtroIngresos");

    const ingresosData = {
        mes: { valor: "$8,750", crecimiento: "+18%" },
        semana: { valor: "$2,150", crecimiento: "+6%" }
    };

    if (filtro) {
        filtro.addEventListener("change", () => {

            const data = ingresosData[filtro.value];

            document.querySelector(".floating-tooltip-value").textContent = data.valor;

            const brief = document.querySelectorAll(".f-brief-item strong");

            brief[0].textContent = data.valor;
            brief[2].textContent = data.crecimiento;

            mostrarToast("Ingresos actualizados");
        });
    }

});