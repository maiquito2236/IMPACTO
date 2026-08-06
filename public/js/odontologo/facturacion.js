document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Manejo de Pestañas (Tabs)
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remover 'active' de todos los botones y contenidos
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => {
                c.classList.remove('active');
                c.style.display = 'none';
            });

            // Activar el elemento clickeado
            btn.classList.add('active');
            const targetId = `tab-${btn.getAttribute('data-tab')}`;
            const targetContent = document.getElementById(targetId);
            
            if (targetContent) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
            }
        });
    });

    // 2. Inicialización de DataTables
    const dataTableOptions = {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50]
    };

    // Inicializar Tabla de Pagos
    $('#tablaPagos').DataTable(dataTableOptions);

    // Inicializar Tabla de Comisiones (con suma dinámica en el footer)
    $('#tablaComisiones').DataTable({
        ...dataTableOptions,
        drawCallback: function () {
            const api = this.api();
            let total = 0;
            
            // Sumar los valores de data-ganancia de las filas filtradas
            api.rows({ search: 'applied' }).nodes().each(function (row) {
                const celdaGanancia = $(row).find('td[data-ganancia]');
                if (celdaGanancia.length > 0) {
                    const valorFloat = parseFloat(celdaGanancia.attr('data-ganancia')) || 0;
                    total += valorFloat;
                }
            });

            // Formatear el total a moneda COP
            const totalFormateado = new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(total);

            // Actualizar el texto en el footer
            $('#totalGananciaFooter').text(totalFormateado);
        }
    });
});