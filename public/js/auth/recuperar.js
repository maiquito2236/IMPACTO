function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}

// Validación nativa por JavaScript en el Submit de la fase final
const formFinal = document.getElementById('formFinal');
if(formFinal) {
    formFinal.addEventListener('submit', function(e) {
        const pass = document.getElementById('contrasena').value;
        const confirmPass = document.getElementById('confirmar_contrasena').value;
        const errorAlert = document.getElementById('jsErrorAlert');

        if (pass !== confirmPass) {
            e.preventDefault();
            errorAlert.classList.remove('d-none');
        } else {
            errorAlert.classList.add('d-none');
        }
    });
}

// =========================================================================
// LÓGICA DE CONTROL DEL TEMPORIZADOR (COOLDOWN DE 30 SEGUNDOS)
// =========================================================================

const btnReenviar = document.getElementById('btnReenviarCode');

if (btnReenviar) {
    // Inicializa la cuenta regresiva leyendo el almacenamiento de sesión
    iniciarContador();

    // Interceptamos el envío para registrar el Cooldown justo antes de viajar al backend
    document.getElementById('formReenviar').addEventListener('submit', function() {
        // Almacenamos el momento exacto en el futuro en que el botón debe volver a activarse (Tiempo actual + 30 segundos)
        const tiempoExpiracion = Date.now() + 30000;
        sessionStorage.setItem('reenvio_cooldown', tiempoExpiracion);
    });
}

function iniciarContador() {
    const tiempoGuardado = sessionStorage.getItem('reenvio_cooldown');
    
    if (!tiempoGuardado) return; // Si no hay un cooldown guardado previamente, no bloqueamos el botón.

    const actualizarProgreso = () => {
        const tiempoActual = Date.now();
        // Calculamos cuántos segundos reales hacen falta restando el tiempo de expiración menos el actual
        const segundosRestantes = Math.ceil((tiempoGuardado - tiempoActual) / 1000);

        if (segundosRestantes > 0) {
            btnReenviar.disabled = true; // Deshabilitamos el botón en el DOM
            btnReenviar.textContent = `Reenviar código (${segundosRestantes}s)`; // Modificamos el texto dinámicamente
        } else {
            // Si el tiempo terminó, limpiamos el almacenamiento, activamos el botón y devolvemos su texto original
            btnReenviar.disabled = false;
            btnReenviar.textContent = "Reenviar código";
            clearInterval(intervalo);
            sessionStorage.removeItem('reenvio_cooldown');
        }
    };

    // Ejecutamos la función de inmediato y luego creamos un bucle repetitivo cada 1 segundo (1000ms)
    actualizarProgreso();
    const intervalo = setInterval(actualizarProgreso, 1000);
}