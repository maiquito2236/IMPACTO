document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Lógica para el botón de Recordatorios
    const btnReminders = document.getElementById("btn-toggle-reminders");
    if(btnReminders) {
        btnReminders.addEventListener("click", function (e) {
            e.preventDefault();
            const extras = this.closest('.list-container').querySelectorAll('.reminder-extra');
            let oculto = false;
            
            extras.forEach(row => {
                row.classList.toggle("is-hidden");
                if (row.classList.contains("is-hidden")) oculto = true;
            });
            
            this.innerHTML = oculto ? "Ver todos los recordatorios →" : "Ver menos ↑";
        });
    }

    // 2. Lógica para el botón de Citas
    const btnCitas = document.getElementById("btn-toggle-citas");
    if(btnCitas) {
        btnCitas.addEventListener("click", function (e) {
            e.preventDefault();
            const rows = this.closest('.list-container').querySelectorAll('.list-items .item-row');
            let oculto = false;

            rows.forEach((row, index) => {
                if (index >= 2) {
                    row.classList.toggle("is-hidden");
                    if (row.classList.contains("is-hidden")) oculto = true;
                }
            });

            this.innerHTML = oculto ? "Ver todas las citas →" : "Ver menos ↑";
        });
    }

    // 3. Lógica Nueva e Independiente para el botón de Cumpleaños
    const btnCumpleanos = document.getElementById("btn-toggle-cumpleanos");
    if(btnCumpleanos) {
        btnCumpleanos.addEventListener("click", function (e) {
            e.preventDefault();
            // Buscamos las filas específicas de esta tarjeta
            const rows = this.closest('.list-container').querySelectorAll('.list-items .item-row');
            let oculto = false;

            rows.forEach((row, index) => {
                // Mantenemos los 3 primeros visibles por defecto, ocultamos/mostramos los siguientes
                if (index >= 3) {
                    row.classList.toggle("is-hidden");
                    if (row.classList.contains("is-hidden")) oculto = true;
                }
            });

            this.innerHTML = oculto ? "Ver todos los cumpleaños →" : "Ver menos ↑";
        });
    }
});
// 4. Lógica para el Filtro Dropdown del Gráfico
const btnFilter = document.getElementById("btn-chart-filter");
const menuFilter = document.getElementById("menu-chart-filter");

if (btnFilter && menuFilter) {
    // Abrir o cerrar al hacer clic en el botón
    btnFilter.addEventListener("click", function (e) {
        e.stopPropagation(); // Evita que el evento se propague al documento
        menuFilter.classList.toggle("is-hidden");
    });

    // Escuchar la selección de las opciones del filtro
    menuFilter.querySelectorAll("a").forEach(option => {
        option.addEventListener("click", function (e) {
            e.preventDefault();
            
            // Obtener el texto seleccionado y el valor (por si lo usas en backend después)
            const selectedText = this.textContent;
            const filterValue = this.getAttribute("data-value");

            // Actualizar el texto del botón manteniendo el ícono de la flecha
            btnFilter.innerHTML = `${selectedText} <i class="fas fa-chevron-down" style="margin-left: 6px; font-size: 10px;"></i>`;
            
            // Ocultar el menú nuevamente
            menuFilter.classList.add("is-hidden");

            // NOTA: Aquí podrás añadir la lógica AJAX/Fetch en el futuro para redibujar el gráfico
            console.log(`Filtrando gráfico por rango: ${filterValue}`);
        });
    });

    // Cerrar el menú si se hace clic por fuera de él
    document.addEventListener("click", function (e) {
        if (!btnFilter.contains(e.target) && !menuFilter.contains(e.target)) {
            menuFilter.classList.add("is-hidden");
        }
    });
}