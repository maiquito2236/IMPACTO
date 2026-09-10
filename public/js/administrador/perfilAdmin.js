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