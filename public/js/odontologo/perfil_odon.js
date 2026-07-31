// Mostrar / Ocultar Contraseña
function togglePasswordVisibility() {
    const passInput = document.getElementById("passInput");
    const eyeIcon = document.querySelector(".pass-toggle-eye");
    
    if (passInput && eyeIcon) {
        if (passInput.type === "password") {
            passInput.type = "text";
            eyeIcon.classList.replace("fa-regular", "fa-solid");
        } else {
            passInput.type = "password";
            eyeIcon.classList.replace("fa-solid", "fa-regular");
        }
    }
}

// Generar Alerta Roja Centrada (Estilo PHP)
function mostrarAlertaRoja(mensaje) {
    // 1. Eliminar alerta vieja si el usuario intenta varias veces
    const alertaVieja = document.getElementById('js-alert-error');
    if (alertaVieja) alertaVieja.remove();

    // 2. Crear la nueva alerta usando tus clases CSS
    const alerta = document.createElement('div');
    alerta.id = 'js-alert-error';
    alerta.className = 'alert-danger'; // Esta clase ya la centra y la pone roja en tu CSS
    alerta.innerHTML = `
        <div class="alert-danger-content">
            <i class="fa-solid fa-triangle-exclamation"></i> ${mensaje}
        </div>
        <i class="fa-solid fa-xmark alert-close" style="cursor:pointer;" onclick="this.parentElement.style.display='none';"></i>
    `;

    // 3. Inyectarla justo arriba del formulario
    const formPanel = document.querySelector('.form-panel');
    if (formPanel) {
        formPanel.parentNode.insertBefore(alerta, formPanel);
        window.scrollTo({ top: alerta.offsetTop - 20, behavior: 'smooth' }); // Sube la pantalla a la alerta
    }
}

// Validaciones W3C
document.addEventListener('DOMContentLoaded', function() {
    const formPerfil = document.getElementById('formPerfilOdon');
    
    if (formPerfil) {
        formPerfil.addEventListener('submit', function(event) {
            
            // 1. Validar Celular (10 dígitos exactos)
            const celularInput = document.getElementById('celular_odon');
            if (celularInput) {
                const valorCelular = celularInput.value.trim();
                const regexCelular = /^\d{10}$/; 
                
                if (!regexCelular.test(valorCelular)) {
                    event.preventDefault(); 
                    mostrarAlertaRoja('El número de teléfono celular debe contener exactamente 10 dígitos.');
                    celularInput.focus();
                    return; 
                }
            }

            // 2. Validar Contraseña (Min. 8 caracteres, 1 mayúscula, 1 número, 1 carácter especial)
            const passInput = document.getElementById('passInput');
            if (passInput) {
                const valorPass = passInput.value;
                
                if (valorPass.length > 0) { // Solo si intenta cambiarla
                    const regexPass = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                    
                    if (!regexPass.test(valorPass)) {
                        event.preventDefault();
                        mostrarAlertaRoja('La nueva contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.');
                        passInput.focus();
                        return;
                    }
                }
            }
        });
    }
});