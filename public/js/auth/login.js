function togglePassword() {
    const inputContrasena = document.getElementById('contrasena');
    const iconoOjo = document.querySelector('.eye-icon');
            
    if (inputContrasena.type === "password") {
        inputContrasena.type = "text";
        iconoOjo.classList.remove('bi-eye');
        iconoOjo.classList.add('bi-eye-slash');
    } else {
        inputContrasena.type = "password";
        iconoOjo.classList.remove('bi-eye-slash');
        iconoOjo.classList.add('bi-eye');
    }
}

        // SCRIPT MAESTRO ANTI-HISTORIAL INTERNO
(function () {
    window.onpageshow = function (event) {
        // Si la página se monta cargando desde el historial (atrás), fuerza un refresco limpio total
        if (event.persisted || (typeof window.performance != "undefined" && window.performance.navigation.type === 2)) {
            window.location.reload(true);
        }
    };
})();

// Aseguramos que los inputs se vacíen físicamente en la carga
document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll('input:not([type="submit"])');
    inputs.forEach(i => i.value = '');
});