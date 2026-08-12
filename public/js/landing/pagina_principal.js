document.addEventListener("DOMContentLoaded", function() {
    // Selecciona todos los enlaces dentro de tu menú
    const menuLinks = document.querySelectorAll(".nav-menu a");

    menuLinks.forEach(link => {
        link.addEventListener("click", function() {
            // 1. Remueve la clase 'active' de todos los enlaces
            menuLinks.forEach(item => item.classList.remove("active"));
                
            // 2. Agrega la clase 'active' solo al que recibió el clic
            this.classList.add("active");
        });
    });
})

(function () {
    window.onpageshow = function (event) {
        // Evalúa si el paciente llegó a esta vista dándole al botón "Atrás" desde el dashboard
        const navegoHaciaAtras = event.persisted || 
            (typeof window.performance !== "undefined" && window.performance.navigation.type === 2);
                
        if (navegoHaciaAtras) {
            // 🚀 Destruye la persistencia del token del servidor llamando al método salir.
            // Si el usuario da "Adelante", el navegador rebotará obligatoriamente al login.
            window.location.replace("/LOGIN_ORIGINAL/salir");
        }
    };
})();