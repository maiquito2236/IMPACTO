function togglePasswordVisibility() {
    const passInput = document.getElementById("passInput");
    const eyeIcon = document.querySelector(".pass-toggle-eye");
    if (passInput.type === "password") {
        passInput.type = "text";
        eyeIcon.classList.replace("fa-regular", "fa-solid");
    } else {
        passInput.type = "password";
        eyeIcon.classList.replace("fa-solid", "fa-regular");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    // Toggle de notificaciones en perfil del paciente
    const toggleNotif = document.getElementById("toggleNotificaciones");
    if (toggleNotif) {
        toggleNotif.addEventListener("change", function () {
            const nuevoEstado = this.checked;
            this.disabled = true;

            fetch("/paciente/perfil/notificaciones", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ activa: nuevoEstado ? 1 : 0 })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    throw new Error("No se pudo actualizar la preferencia");
                }
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: data.activa ? "Notificaciones activadas" : "Notificaciones desactivadas",
                        showConfirmButton: false,
                        timer: 2200,
                        timerProgressBar: true
                    });
                }
            })
            .catch(() => {
                toggleNotif.checked = !nuevoEstado;
                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No fue posible actualizar tu preferencia de notificaciones.",
                        confirmButtonColor: "#0b57d0",
                        heightAuto: false
                    });
                }
            })
            .finally(() => {
                toggleNotif.disabled = false;
            });
        });
    }

    // Confirmación con SweetAlert2 al desvincular / eliminar un menor
    document.querySelectorAll(".form-desvincular-menor").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const nombreMenor = this.dataset.nombre || "este menor";

            if (typeof Swal === "undefined") {
                this.submit();
                return;
            }

            Swal.fire({
                icon: "warning",
                title: "¿Desvincular menor?",
                text: `¿Seguro que deseas desvincular a ${nombreMenor} de tu cuenta de acudiente?`,
                showCancelButton: true,
                confirmButtonColor: "#dc2626",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Sí, desvincular",
                cancelButtonText: "Cancelar",
                reverseButtons: true,
                heightAuto: false
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
