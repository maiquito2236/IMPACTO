/**
 * DentiBot - Asistente Virtual para Odonto Estética (Landing Page)
 */
window.DentiChat = (function() {
    const state = {
        isOpen: false,
        isBusy: false
    };

    // Referencias a elementos del DOM
    let wrapper, chatWindow, chatBody, chatInput, typingIndicator, tooltip;

    function init() {
        wrapper = document.getElementById('dentiChatbotWrapper');
        chatWindow = document.getElementById('dentiChatWindow');
        chatBody = document.getElementById('dentiChatBody');
        chatInput = document.getElementById('dentiChatInput');
        typingIndicator = document.getElementById('dentiTypingIndicator');
        tooltip = document.getElementById('dentiChatTooltip');

        // Poner la hora actual en el mensaje inicial
        const initialTimeEl = document.getElementById('dentiInitialTime');
        if (initialTimeEl) {
            initialTimeEl.textContent = obtenerHoraActual();
        }

        // Revisar si ya había cerrado el tooltip antes
        if (sessionStorage.getItem('denti_tooltip_closed') === 'true' && tooltip) {
            tooltip.style.display = 'none';
        }

        // Cerrar al presionar Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && state.isOpen) {
                toggleChat();
            }
        });
    }

    function toggleChat() {
        if (!wrapper) init();

        state.isOpen = !state.isOpen;
        if (state.isOpen) {
            wrapper.classList.add('is-open');
            cerrarTooltip();
            setTimeout(() => {
                if (chatInput) chatInput.focus();
                scrollBottom();
            }, 300);
        } else {
            wrapper.classList.remove('is-open');
        }
    }

    function cerrarTooltip() {
        if (tooltip) {
            tooltip.style.display = 'none';
            sessionStorage.setItem('denti_tooltip_closed', 'true');
        }
    }

    function obtenerHoraActual() {
        const ahora = new Date();
        let h = ahora.getHours();
        let m = ahora.getMinutes();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        h = h ? h : 12;
        m = m < 10 ? '0' + m : m;
        return `${h}:${m} ${ampm}`;
    }

    function scrollBottom() {
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }

    function mostrarTyping(mostrar) {
        if (!typingIndicator) return;
        typingIndicator.style.display = mostrar ? 'flex' : 'none';
        scrollBottom();
    }

    // Escapar HTML para evitar XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Convertidor básico de Markdown a HTML seguro
    function formatearMarkdown(texto) {
        if (!texto) return '';

        // 1. Escapar HTML base
        let html = escapeHtml(texto);

        // 2. Negritas **texto**
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 3. Enlaces [texto](url)
        html = html.replace(/\[(.*?)\]\((.*?)\)/g, function(match, label, url) {
            const safeUrl = (url.startsWith('/') || url.startsWith('http://') || url.startsWith('https://') || url.startsWith('tel:'))
                ? url : '#';
            const target = url.startsWith('/') ? '' : 'target="_blank" rel="noopener noreferrer"';
            return `<a href="${safeUrl}" ${target}>${label}</a>`;
        });

        // 4. Saltos de línea y viñetas
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    function agregarMensaje(remitente, textoHtml, sugerencias = []) {
        const row = document.createElement('div');
        row.className = `denti-msg-row ${remitente}`;

        const hora = obtenerHoraActual();

        if (remitente === 'bot') {
            row.innerHTML = `
                <div class="denti-msg-avatar">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div class="denti-msg-bubble">
                    ${textoHtml}
                    <span class="denti-msg-time">${hora}</span>
                </div>
            `;
        } else {
            row.innerHTML = `
                <div class="denti-msg-bubble">
                    ${textoHtml}
                    <span class="denti-msg-time">${hora}</span>
                </div>
            `;
        }

        // Insertar antes del typing indicator
        if (typingIndicator && typingIndicator.parentNode) {
            chatBody.insertBefore(row, typingIndicator);
        } else {
            chatBody.appendChild(row);
        }

        // Si hay sugerencias rápidas asociadas, agregarlas debajo
        if (remitente === 'bot' && sugerencias && sugerencias.length > 0) {
            const actionsContainer = document.createElement('div');
            actionsContainer.className = 'denti-quick-actions';
            sugerencias.forEach(sug => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'denti-action-btn';
                btn.innerHTML = `<i class="fa-regular fa-comment"></i> ${escapeHtml(sug)}`;
                btn.onclick = function() {
                    enviarPreguntaSugerida(sug);
                };
                actionsContainer.appendChild(btn);
            });
            chatBody.insertBefore(actionsContainer, typingIndicator);
        }

        scrollBottom();
    }

    function enviarPreguntaSugerida(pregunta) {
        if (state.isBusy) return;
        if (chatInput) chatInput.value = pregunta;
        enviarTexto();
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function enviarAccion(accion) {
        if (state.isBusy) return;

        const etiquetas = {
            'pedir_cita': '📅 ¿Cómo pido una cita?',
            'horarios': '🕒 Horarios y Ubicación',
            'servicios': '🦷 Servicios Dentales',
            'urgencia': '🚨 Tengo una urgencia / dolor',
            'contacto': '💬 Quiero hablar con un asesor'
        };

        const etiquetaUsuario = etiquetas[accion] || accion;
        agregarMensaje('user', escapeHtml(etiquetaUsuario));

        state.isBusy = true;
        mostrarTyping(true);

        fetch('/chatbot/mensaje', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ accion: accion })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
            return response.json();
        })
        .then(data => {
            mostrarTyping(false);
            state.isBusy = false;
            if (data.ok && data.respuesta) {
                agregarMensaje('bot', formatearMarkdown(data.respuesta), data.sugerencias || []);
            } else {
                agregarMensaje('bot', 'No pude procesar esa opción en este momento. Por favor llámanos al 3115204752.');
            }
        })
        .catch(err => {
            console.error('Error chatbot:', err);
            mostrarTyping(false);
            state.isBusy = false;
            agregarMensaje('bot', 'Hubo una breve interrupción en la conexión. Puedes comunicarte directamente a nuestro WhatsApp o teléfono al **3115204752**.');
        });
    }

    function enviarTexto() {
        if (state.isBusy) return;
        if (!chatInput) return;

        const texto = chatInput.value.trim();
        if (!texto) return;

        agregarMensaje('user', escapeHtml(texto));
        chatInput.value = '';
        chatInput.focus();

        state.isBusy = true;
        mostrarTyping(true);

        fetch('/chatbot/mensaje', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mensaje: texto })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al procesar consulta');
            return response.json();
        })
        .then(data => {
            mostrarTyping(false);
            state.isBusy = false;
            if (data.ok && data.respuesta) {
                agregarMensaje('bot', formatearMarkdown(data.respuesta), data.sugerencias || []);
            } else {
                agregarMensaje('bot', data.respuesta || 'Por favor indícanos más detalles o contáctanos al 3115204752.');
            }
        })
        .catch(err => {
            console.error('Error chatbot:', err);
            mostrarTyping(false);
            state.isBusy = false;
            agregarMensaje('bot', 'No pudimos conectar con el servidor en este instante. Si tienes una consulta urgente, escríbenos a nuestro WhatsApp al **3115204752**.');
        });
    }

    function reiniciarChat() {
        if (!chatBody) return;
        
        // Conservar solo el mensaje inicial, las acciones iniciales y el typing
        const initialMsg = chatBody.querySelector('.denti-msg-row.bot');
        const initialActions = document.getElementById('dentiInitialQuickActions');
        const dateEl = chatBody.querySelector('.denti-chat-date');

        chatBody.innerHTML = '';
        if (dateEl) chatBody.appendChild(dateEl);
        if (initialMsg) chatBody.appendChild(initialMsg);
        if (initialActions) chatBody.appendChild(initialActions);
        if (typingIndicator) chatBody.appendChild(typingIndicator);

        if (chatInput) {
            chatInput.value = '';
            chatInput.focus();
        }

        scrollBottom();
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return {
        toggleChat,
        cerrarTooltip,
        enviarAccion,
        enviarTexto,
        enviarPreguntaSugerida,
        reiniciarChat
    };
})();
