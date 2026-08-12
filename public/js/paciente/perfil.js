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