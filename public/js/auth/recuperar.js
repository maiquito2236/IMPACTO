function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;
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

document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('contrasena');
    const confirmPasswordInput = document.getElementById('confirmar_contrasena');
    const formFinal = document.getElementById('formFinal');
    const strengthIndicator = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');

    // Expresiones regulares para validación (idénticas a las de registro)
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/;
    const hasUpperCase = /[A-Z]/;
    const hasNumber = /\d/;

    // Indicador dinámico de fortaleza de contraseña
    if (passwordInput && strengthIndicator && strengthText) {
        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let strength = 'Débil';
            let color = '#dc3545'; // rojo

            if (val.length === 0) {
                strengthIndicator.style.width = '0%';
                strengthText.textContent = '';
                return;
            }

            if (val.length >= 8 && hasSpecialChar.test(val)) {
                if (hasUpperCase.test(val) && hasNumber.test(val)) {
                    strength = 'Fuerte';
                    color = '#28a745'; // verde
                    strengthIndicator.style.width = '100%';
                } else {
                    strength = 'Media';
                    color = '#ffc107'; // amarillo
                    strengthIndicator.style.width = '66%';
                }
            } else {
                strength = 'Débil';
                color = '#dc3545'; // rojo
                strengthIndicator.style.width = '33%';
            }

            strengthIndicator.style.backgroundColor = color;
            strengthText.textContent = 'Fortaleza: ' + strength;
            strengthText.style.color = color;
        });
    }

    // Validación al enviar el formulario final
    if (formFinal) {
        formFinal.addEventListener('submit', function(event) {
            const val = passwordInput ? passwordInput.value : '';
            const confirmVal = confirmPasswordInput ? confirmPasswordInput.value : '';

            const removeExistingAlert = () => {
                const existingAlert = document.getElementById('js-alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
            };

            const showAlert = (message, inputFocus) => {
                removeExistingAlert();
                const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show rounded-3 small text-center mb-4" role="alert" id="js-alert">
                    <span>${message}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                `;
                formFinal.insertAdjacentHTML('beforebegin', alertHtml);
                if (inputFocus) inputFocus.focus();
                const card = document.querySelector('.card-custom') || formFinal;
                card.scrollIntoView({ behavior: 'smooth' });
            };

            if (val.length < 8 || !hasSpecialChar.test(val) || !hasUpperCase.test(val) || !hasNumber.test(val)) {
                event.preventDefault();
                showAlert('La contraseña es débil. Debe tener al menos 8 caracteres e incluir mayúscula, número y carácter especial.', passwordInput);
                return;
            }

            if (val !== confirmVal) {
                event.preventDefault();
                showAlert('Las contraseñas no coinciden. Por favor verifícalas.', confirmPasswordInput);
                return;
            }

            removeExistingAlert();
        });
    }

    // =========================================================================
    // LÓGICA DE CONTROL DEL TEMPORIZADOR (COOLDOWN DE 30 SEGUNDOS)
    // =========================================================================
    const btnReenviar = document.getElementById('btnReenviarCode');
    const formReenviar = document.getElementById('formReenviar');

    if (btnReenviar) {
        iniciarContador(btnReenviar);

        if (formReenviar) {
            formReenviar.addEventListener('submit', function() {
                const tiempoExpiracion = Date.now() + 30000;
                sessionStorage.setItem('reenvio_cooldown', tiempoExpiracion);
            });
        }
    }
});

function iniciarContador(btnReenviar) {
    if (!btnReenviar) return;
    const tiempoGuardado = sessionStorage.getItem('reenvio_cooldown');
    if (!tiempoGuardado) return;

    const actualizarProgreso = () => {
        const tiempoActual = Date.now();
        const segundosRestantes = Math.ceil((tiempoGuardado - tiempoActual) / 1000);

        if (segundosRestantes > 0) {
            btnReenviar.disabled = true;
            btnReenviar.textContent = `Reenviar código (${segundosRestantes}s)`;
        } else {
            btnReenviar.disabled = false;
            btnReenviar.textContent = "Reenviar código";
            clearInterval(intervalo);
            sessionStorage.removeItem('reenvio_cooldown');
        }
    };

    actualizarProgreso();
    const intervalo = setInterval(actualizarProgreso, 1000);
}
