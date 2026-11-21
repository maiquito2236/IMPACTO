function togglePasswordVisibility() {
    const passInput = document.getElementById("passInput");
    // Actualizamos la clase para que encuentre el nuevo ícono del administrador
    const eyeIcon = document.querySelector(".eye-btn");
    
    if (passInput.type === "password") {
        passInput.type = "text";
        eyeIcon.classList.replace("fa-regular", "fa-solid");
    } else {
        passInput.type = "password";
        eyeIcon.classList.replace("fa-solid", "fa-regular");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const toggleNotif = document.getElementById("toggleNotificacionesAdmin");
    if (!toggleNotif) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    toggleNotif.addEventListener("change", function () {
        const nuevoEstado = this.checked;
        this.disabled = true;

        fetch("/admin/perfil/notificaciones", {
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
                throw new Error("No se pudo actualizar");
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
                    text: "No se pudo guardar la preferencia de notificaciones.",
                    confirmButtonColor: "#2563eb"
                });
            }
        })
        .finally(() => {
            toggleNotif.disabled = false;
        });
    });
});
