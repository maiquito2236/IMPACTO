function togglePassword() {
    const input = document.getElementById('contrasena');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('contrasena');
    const form = document.querySelector('form');
    const strengthIndicator = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    
    // Regular expressions for validation
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/;
    const hasUpperCase = /[A-Z]/;
    const hasNumber = /\d/;
    
    passwordInput.addEventListener('input', function() {
        const val = passwordInput.value;
        let strength = 'Débil';
        let color = '#dc3545'; // red
        
        if (val.length === 0) {
            strengthIndicator.style.width = '0%';
            strengthText.textContent = '';
            return;
        }

        if (val.length >= 8 && hasSpecialChar.test(val)) {
            if (hasUpperCase.test(val) && hasNumber.test(val)) {
                strength = 'Fuerte';
                color = '#28a745'; // green
                strengthIndicator.style.width = '100%';
            } else {
                strength = 'Media';
                color = '#ffc107'; // yellow
                strengthIndicator.style.width = '66%';
            }
        } else {
            strength = 'Débil';
            color = '#dc3545'; // red
            strengthIndicator.style.width = '33%';
        }
        
        strengthIndicator.style.backgroundColor = color;
        strengthText.textContent = 'Fortaleza: ' + strength;
        strengthText.style.color = color;
    });

    form.addEventListener('submit', function(event) {
        const val = passwordInput.value;
        
        if (val.length < 8 || !hasSpecialChar.test(val)) {
            event.preventDefault(); // Prevent submission
            // Remove previous js-alert if it exists
            const existingAlert = document.getElementById('js-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            // Create a bootstrap styled alert
            const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show rounded-3 small text-center mb-4" role="alert" id="js-alert">
                <span>La contraseña es débil. Debe tener al menos 8 caracteres e incluir caracteres especiales.</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            `;
            
            // Insert it just before the form
            form.insertAdjacentHTML('beforebegin', alertHtml);
            passwordInput.focus();
	document.querySelector('.card-register').scrollIntoView({ behavior: 'smooth' });
        }
    });
});
