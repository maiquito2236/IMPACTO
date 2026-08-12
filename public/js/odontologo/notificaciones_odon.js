document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-item');
    const rows = document.querySelectorAll('#notifications-tbody tr');
    const selectTipo = document.getElementById('filter-tipo');
    const selectFecha = document.getElementById('filter-fecha');
    const btnReset = document.getElementById('btn-reset-filters');
    const btnMarkAll = document.getElementById('btn-mark-all');

    // 1. CONTROL DE FILTROS EN TIEMPO REAL
    function aplicarFiltros() {
        const tabActivaElement = document.querySelector('.tab-item.active');
        if (!tabActivaElement) return;
        const tabActiva = tabActivaElement.dataset.tab;
        
        const tableContainer = document.getElementById('notifications-table-container');
        const prefContainer = document.getElementById('preferences-container');
        const filtersSection = document.querySelector('.filters-section');
        const btnMarkAll = document.getElementById('btn-mark-all');

        if (tabActiva === 'preferencias') {
            if (tableContainer) tableContainer.style.display = 'none';
            if (filtersSection) filtersSection.style.display = 'none';
            if (btnMarkAll) btnMarkAll.style.display = 'none';
            if (prefContainer) prefContainer.style.display = 'block';
            return; 
        } else {
            if (tableContainer) tableContainer.style.display = 'block';
            if (filtersSection) filtersSection.style.display = 'grid';
            if (prefContainer) prefContainer.style.display = 'none';
            actualizarContadores(); // Restaura la visibilidad del botón marcar todos si corresponde
        }

        const tipoVal = selectTipo ? selectTipo.value : 'todos';
        const fechaVal = selectFecha ? selectFecha.value : '';

        rows.forEach(row => {
            if(row.cells.length === 1) return; // Ignorar fila de "No hay registros"
            
            let mostrar = true;

            // Filtro por pestaña superior
            if (tabActiva === 'sin-leer' && row.dataset.estado !== 'sin-leer') mostrar = false;
            if (tabActiva === 'leidas' && row.dataset.estado !== 'leida') mostrar = false;

            // Filtros por input de cabecera
            if (tipoVal !== 'todos' && row.dataset.tipo !== tipoVal) mostrar = false;
            if (fechaVal !== '' && row.dataset.fecha !== fechaVal) mostrar = false;

            row.style.display = mostrar ? '' : 'none';
        });
    }

    // ACTUALIZAR CONTADORES DE PESTAÑAS DINÁMICAMENTE
    function actualizarContadores() {
        let total = 0, sinLeer = 0, leidas = 0;
        rows.forEach(row => {
            if(row.cells.length === 1) return;
            total++;
            if (row.dataset.estado === 'sin-leer') {
                sinLeer++;
            } else if (row.dataset.estado === 'leida') {
                leidas++;
            }
        });

        const tabTodas = document.querySelector('.tab-item[data-tab="todas"]');
        const tabSinLeer = document.querySelector('.tab-item[data-tab="sin-leer"]');
        const tabLeidas = document.querySelector('.tab-item[data-tab="leidas"]');

        if(tabTodas) tabTodas.textContent = `Todas (${total})`;
        if(tabSinLeer) tabSinLeer.textContent = `Sin leer (${sinLeer})`;
        if(tabLeidas) tabLeidas.textContent = `Leídas (${leidas})`;

        if (btnMarkAll) {
            const tabActivaElement = document.querySelector('.tab-item.active');
            const isActivePref = tabActivaElement && tabActivaElement.dataset.tab === 'preferencias';
            if (sinLeer === 0 || isActivePref) {
                btnMarkAll.style.display = 'none';
            } else {
                btnMarkAll.style.display = 'inline-block';
            }
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            aplicarFiltros();
        });
    });

    if(selectTipo) selectTipo.addEventListener('change', aplicarFiltros);
    if(selectFecha) selectFecha.addEventListener('change', aplicarFiltros);

    if(btnReset) {
        btnReset.addEventListener('click', () => {
            selectTipo.value = 'todos';
            selectFecha.value = '';
            tabs[0].click();
        });
    }

    // 2. MARCAR UNA NOTIFICACIÓN INDIVIDUAL COMO LEÍDA Y MOSTRAR ALERTA (AJAX)
    const tbody = document.getElementById('notifications-tbody');
    if(tbody) {
        tbody.addEventListener('click', function(e) {
            const row = e.target.closest('tr');
            if(!row || row.cells.length === 1) return; // Ignorar fila vacía

            const isBtnAction = e.target.closest('.btn-marcar-una');
            
            const marcarFilaLeida = (fila) => {
                if (fila.dataset.estado === 'leida') return;
                
                const idNotificacion = fila.dataset.id;
                fila.style.transition = 'all 0.3s ease';
                fila.style.background = '#f4f7fe';
                
                const badge = fila.querySelector('.badge-status-unread');
                if(badge) {
                    badge.className = 'badge badge-status-read';
                    badge.textContent = 'Leída';
                }
                fila.dataset.estado = 'leida';
                fila.classList.remove('row-unread');
                
                const btn = fila.querySelector('.btn-marcar-una');
                if(btn) {
                    btn.replaceWith(document.createRange().createContextualFragment('<span style="color:#198754;"><i class="fa-solid fa-circle-check"></i></span>'));
                }

                const formData = new FormData();
                formData.append('id', idNotificacion);

                const fetchUrl = window.IS_PACIENTE_MODULE ? '/LOGIN_ORIGINAL/paciente/notificaciones/marcar_leida' : '/LOGIN_ORIGINAL/odontologo/notificaciones/marcar_leida';
                fetch(fetchUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    actualizarContadores();
                })
                .catch(err => console.error("Error al silenciar la notificación:", err));
            };

            if (!isBtnAction) {
                const msgText = row.querySelector('.msg-text').textContent;
                const dateText = row.querySelector('.cell-fecha').textContent.trim();
                const tipoText = row.querySelector('.cell-tipo').textContent.trim();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `<div style="font-size: 24px; color: #1a56db; font-weight: 700; margin-bottom: 5px;">${tipoText}</div>`,
                        html: `
                            <div style="text-align: left; background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                <div style="font-size: 14px; color: #6b7280; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                    <i class="fa-regular fa-calendar" style="color: #1a56db; font-size: 16px;"></i> 
                                    <span>FECHA DEL SUCESO:</span> <span style="color: #1e293b;">${dateText}</span>
                                </div>
                                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 15px;">
                                <div style="font-size: 16px; color: #1e293b; line-height: 1.7;">
                                    ${msgText}
                                </div>
                            </div>
                        `,
                        icon: 'info',
                        iconColor: '#1a56db',
                        confirmButtonText: '<i class="fa-solid fa-check"></i> Entendido',
                        confirmButtonColor: '#1a56db',
                        width: '500px',
                        padding: '2em',
                        background: '#ffffff',
                        backdrop: `rgba(15, 23, 42, 0.4)`,
                        scrollbarPadding: false,
                        heightAuto: false
                    }).then(() => {
                        marcarFilaLeida(row);
                    });
                } else {
                    alert(`${tipoText}\nFecha: ${dateText}\n\n${msgText}`);
                    marcarFilaLeida(row);
                }
            } else {
                marcarFilaLeida(row);
            }
        });
    }

    // 3. MARCAR ABSOLUTAMENTE TODAS COMO LEÍDAS (AJAX MASIVO)
    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', () => {
            rows.forEach(row => {
                if (row.classList.contains('row-unread')) {
                    row.classList.remove('row-unread');
                    row.dataset.estado = 'leida';
                    const badge = row.querySelector('.badge-status-unread');
                    if(badge) {
                        badge.className = 'badge badge-status-read';
                        badge.textContent = 'Leída';
                    }
                    const btn = row.querySelector('.btn-marcar-una');
                    if(btn) btn.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#198754;"></i>';
                }
            });

            btnMarkAll.style.display = 'none';
            actualizarContadores();
            aplicarFiltros();

            const fetchUrlMasivo = window.IS_PACIENTE_MODULE ? '/LOGIN_ORIGINAL/paciente/notificaciones/marcar_todas' : '/LOGIN_ORIGINAL/odontologo/notificaciones/marcar_todas_leidas';
            fetch(fetchUrlMasivo)
            .catch(err => console.error("Error en actualización masiva:", err));
        });
    }
});