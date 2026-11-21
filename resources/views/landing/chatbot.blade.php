<!-- WIDGET CHATBOT FLOTANTE: ODONTO ESTÉTICA -->
<div class="denti-chatbot-wrapper" id="dentiChatbotWrapper">

    <!-- Tooltip flotante de bienvenida -->
    <div class="denti-chat-tooltip" id="dentiChatTooltip" onclick="DentiChat.toggleChat()">
        <span>👋 ¿Tienes dudas sobre tu cita o sonrisa? ¡Chatea aquí!</span>
        <button type="button" class="denti-tooltip-close" id="dentiTooltipClose" onclick="event.stopPropagation(); DentiChat.cerrarTooltip();" title="Cerrar aviso">&times;</button>
    </div>

    <!-- Botón disparador del Chatbot -->
    <button type="button" class="denti-chat-launcher" id="dentiChatLauncher" onclick="DentiChat.toggleChat()" aria-label="Abrir asistente virtual Odonto Estética">
        <span class="denti-online-badge"></span>
        <i class="fa-solid fa-comment-dots icon-open"></i>
        <i class="fa-solid fa-xmark icon-close"></i>
    </button>

    <!-- Ventana flotante de conversación -->
    <div class="denti-chat-window" id="dentiChatWindow" role="dialog" aria-modal="true" aria-labelledby="dentiBotTitle">
        
        <!-- Cabecera -->
        <div class="denti-chat-header">
            <div class="denti-header-info">
                <div class="denti-header-avatar">
                    <i class="fa-solid fa-tooth"></i>
                    <span class="denti-avatar-badge"></span>
                </div>
                <div>
                    <h3 class="denti-header-title" id="dentiBotTitle">DentiBot</h3>
                    <p class="denti-header-status">
                        <i class="fa-solid fa-circle" style="font-size: 8px; color: #10b981;"></i> Asistente Odonto Estética (24/7)
                    </p>
                </div>
            </div>
            <div class="denti-header-actions">
                <button type="button" class="denti-btn-action" onclick="DentiChat.reiniciarChat()" title="Reiniciar chat">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button type="button" class="denti-btn-action" onclick="DentiChat.toggleChat()" title="Minimizar">
                    <i class="fa-solid fa-minus"></i>
                </button>
            </div>
        </div>

        <!-- Cuerpo del chat (Mensajes) -->
        <div class="denti-chat-body" id="dentiChatBody">
            <div class="denti-chat-date">Hoy</div>

            <!-- Mensaje de bienvenida inicial -->
            <div class="denti-msg-row bot">
                <div class="denti-msg-avatar">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div class="denti-msg-bubble">
                    ¡Hola! 👋 Soy <strong>DentiBot</strong>, tu asistente de <strong>Odonto Estética</strong> en Fuentedeoro, Meta.
                    <br><br>
                    ¿En qué te puedo colaborar hoy? Puedes elegir una opción rápida o escribir tu duda libremente:
                    <span class="denti-msg-time" id="dentiInitialTime"></span>
                </div>
            </div>

            <!-- Opciones Rápidas Iniciales -->
            <div class="denti-quick-actions" id="dentiInitialQuickActions">
                <button type="button" class="denti-action-btn" onclick="DentiChat.enviarAccion('pedir_cita')">
                    <i class="fa-regular fa-calendar-check"></i> Pedir Cita
                </button>
                <button type="button" class="denti-action-btn" onclick="DentiChat.enviarAccion('horarios')">
                    <i class="fa-regular fa-clock"></i> Horarios y Ubicación
                </button>
                <button type="button" class="denti-action-btn" onclick="DentiChat.enviarAccion('servicios')">
                    <i class="fa-solid fa-teeth-open"></i> Servicios Dentales
                </button>
                <button type="button" class="denti-action-btn" onclick="DentiChat.enviarAccion('urgencia')">
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i> Dolor o Urgencia
                </button>
                <button type="button" class="denti-action-btn" onclick="DentiChat.enviarAccion('contacto')">
                    <i class="fa-brands fa-whatsapp text-success"></i> WhatsApp Asesor
                </button>
            </div>

            <!-- Indicador animado de 'Escribiendo...' -->
            <div class="denti-msg-row bot denti-typing-row" id="dentiTypingIndicator">
                <div class="denti-msg-avatar">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div class="denti-typing-bubble">
                    <span class="denti-dot"></span>
                    <span class="denti-dot"></span>
                    <span class="denti-dot"></span>
                </div>
            </div>
        </div>

        <!-- Pie y Caja de Entrada -->
        <div class="denti-chat-footer">
            <form class="denti-chat-form" id="dentiChatForm" onsubmit="event.preventDefault(); DentiChat.enviarTexto();">
                <input type="text" 
                       id="dentiChatInput" 
                       class="denti-chat-input" 
                       placeholder="Escribe tu consulta o dolor aquí..." 
                       autocomplete="off" 
                       maxlength="500">
                <button type="submit" class="denti-chat-send" id="dentiChatSendBtn" aria-label="Enviar mensaje">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
            <div class="denti-chat-disclaimer">
                Información orientativa. Para atención de urgencias inmediatas llama al <strong>3115204752</strong>.
            </div>
        </div>

    </div>
</div>
