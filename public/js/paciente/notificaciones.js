/**
 * Módulo de Notificaciones - Odonto Estética (Paciente)
 * Filtrado de pestañas + marcar como leída (individual y masivo) vía AJAX real
 */

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. LÓGICA DE FILTRADO DE PESTAÑAS
    // ==========================================
    const tabs = document.querySelectorAll('.btn-tab');
    const container = document.getElementById('notifications-container');

    function aplicarFiltro(filterValue) {
        const cards = container.querySelectorAll('.notification-card');
        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (filterValue === 'all' || filterValue === cardStatus) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            aplicarFiltro(tab.getAttribute('data-filter'));
        });
    });

    // ==========================================
    // 2. MARCAR UNA NOTIFICACIÓN COMO LEÍDA (clic en el punto)
    // ==========================================
    if (container) {
        container.addEventListener('click', (e) => {
            const card = e.target.closest('.notification-card');
            if (!card) return;

            // Si ya está leída, ignorar el clic para no descontar dos veces ni hacer doble petición
            if (card.dataset.status === 'read') return;

            const id = card.dataset.id;

            // Efecto visual inmediato
            card.classList.remove('unread');
            card.dataset.status = 'read';
            
            const dot = card.querySelector('.notify-dot');
            if (dot) dot.remove();

            actualizarBadgeSinLeer(-1);

            const formData = new FormData();
            formData.append('id', id);

            fetch('/LOGIN_ORIGINAL/paciente/notificaciones/marcar_leida', {
                method: 'POST',
                body: formData
            }).catch(err => console.error('Error al marcar como leída:', err));
        });
    }

    // ==========================================
    // 3. MARCAR TODAS COMO LEÍDAS
    // ==========================================
    const btnMarkAll = document.getElementById('btn-mark-all');
    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', () => {
            const cardsSinLeer = container.querySelectorAll('.notification-card.unread');
            cardsSinLeer.forEach(card => {
                card.classList.remove('unread');
                card.dataset.status = 'read';
                const dot = card.querySelector('.notify-dot');
                if (dot) dot.remove();
            });

            actualizarBadgeSinLeer(0, true);
            btnMarkAll.style.display = 'none';

            fetch('/LOGIN_ORIGINAL/paciente/notificaciones/marcar_todas', { method: 'POST' })
                .catch(err => console.error('Error en actualización masiva:', err));
        });
    }

    function actualizarBadgeSinLeer(delta, reset = false) {
        const badge = document.querySelector('[data-filter="unread"] .badge');
        if (!badge) return;
        let actual = reset ? 0 : Math.max(0, parseInt(badge.textContent || '0', 10) + delta);
        badge.textContent = actual;
    }

    // ==========================================
    // 4. ALERTA PREMIUM DE PREFERENCIAS (sin cambios)
    // ==========================================
    const btnGuardar = document.querySelector('.panel-card .btn-pedir-cita');

    if (btnGuardar) {
        btnGuardar.addEventListener('click', (e) => {
            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '<h2 style="font-family: \'Segoe UI\', sans-serif; font-weight: 700; color: #1e293b; font-size: 20px; margin: 0;">¡Preferencias actualizadas!</h2>',
                    html: '<p style="font-family: \'Segoe UI\', sans-serif; color: #64748b; font-size: 14px; margin: 5px 0 0 0;">Tus configuraciones de canales y recordatorios de notificación se han guardado con éxito.</p>',
                    icon: 'success',
                    iconColor: '#0b57d0',
                    background: '#ffffff',
                    backdrop: `rgba(226, 237, 248, 0.4)`,
                    buttonsStyling: false,
                    heightAuto: false,
                    scrollbarPadding: false,
                    customClass: {
                        popup: 'rounded-4 border-0 p-4 shadow-lg',
                        confirmButton: 'btn btn-primary fw-bold px-4 py-2'
                    },
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('¡Preferencias guardadas correctamente en Odonto Estética!');
            }
        });
    }
});