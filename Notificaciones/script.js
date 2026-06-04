document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-item');
    const rows = document.querySelectorAll('#notifications-tbody tr');
    const selectTipo = document.getElementById('filter-tipo');
    const selectCategoria = document.getElementById('filter-categoria');
    const selectEstado = document.getElementById('filter-estado');
    const btnReset = document.getElementById('btn-reset-filters');
    const btnMarkAll = document.getElementById('btn-mark-all');
    const bellCounter = document.getElementById('bell-counter');

    // 1. Manejo Dinámico de Pestañas (Todas / Sin Leer / Archivadas)
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            aplicarFiltros();
        });
    });

    // 2. Ejecutar Filtros combinados (Selects + Pestaña activa)
    function aplicarFiltros() {
        const tabActiva = document.querySelector('.tab-item.active').dataset.tab;
        const tipoVal = selectTipo.value;
        const catVal = selectCategoria.value;
        const estadoVal = selectEstado.value;

        rows.forEach(row => {
            let mostrar = true;

            // Filtro por pestañas superiores
            if (tabActiva === 'sin-leer' && row.dataset.estado !== 'sin-leer') mostrar = false;
            if (tabActiva === 'archivadas') mostrar = false; // Simulación: Ninguna está archivada inicialmente

            // Filtros por Select Dropdowns
            if (tipoVal !== 'todos' && row.dataset.tipo !== tipoVal) mostrar = false;
            if (catVal !== 'todas' && row.dataset.categoria !== catVal) mostrar = false;
            if (estadoVal !== 'todos' && row.dataset.estado !== estadoVal) mostrar = false;

            row.style.display = mostrar ? '' : 'none';
        });
    }

    // Escuchar cambios en los filtros select
    [selectTipo, selectCategoria, selectEstado].forEach(select => {
        select.addEventListener('change', aplicarFiltros);
    });

    // 3. Botón para Reiniciar Filtros
    btnReset.addEventListener('click', () => {
        selectTipo.value = 'todos';
        selectCategoria.value = 'todas';
        selectEstado.value = 'todos';
        tabs[0].click(); // Regresa a la pestaña "Todas"
        aplicarFiltros();
    });

    // 4. Acción de "Marcar todas como leídas"
    btnMarkAll.addEventListener('click', () => {
        rows.forEach(row => {
            if (row.classList.contains('row-unread')) {
                row.classList.remove('row-unread');
                row.dataset.estado = 'leida';
                
                // Cambiar el badge visual de la celda de estado
                const statusBadge = row.querySelector('.badge-status-unread');
                if (statusBadge) {
                    statusBadge.className = 'badge badge-status-read';
                    statusBadge.textContent = 'Leída';
                }
            }
        });
        // Reiniciar el contador de notificaciones de la campana a 0
        bellCounter.style.display = 'none';
        aplicarFiltros();
    });
});