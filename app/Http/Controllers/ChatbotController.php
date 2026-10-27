<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Procesa la consulta del visitante o paciente en la página principal.
     */
    public function enviarMensaje(Request $request)
    {
        $request->validate([
            'mensaje' => 'nullable|string|max:500',
            'accion'  => 'nullable|string|max:50',
        ]);

        $accion = trim($request->input('accion', ''));
        $mensaje = trim($request->input('mensaje', ''));

        // 1. Si el usuario hizo clic en una acción predefinida rápida (0 tokens, instantáneo)
        if (!empty($accion)) {
            return response()->json($this->manejarAccionRapida($accion));
        }

        if (empty($mensaje)) {
            return response()->json([
                'ok' => false,
                'respuesta' => 'Por favor escribe tu consulta o selecciona una de las opciones sugeridas.',
            ], 422);
        }

        // 2. Revisar si la respuesta está en caché local para ahorrar tokens
        $cacheKey = 'chatbot_faq_' . md5(mb_strtolower($mensaje));
        try {
            if (Cache::has($cacheKey)) {
                return response()->json([
                    'ok' => true,
                    'respuesta' => Cache::get($cacheKey),
                    'fuente' => 'cache',
                    'sugerencias' => $this->generarSugerencias($mensaje)
                ]);
            }
        } catch (\Throwable $cacheEx) {
            // Si la base de datos o el driver de caché no están disponibles, continuar sin romper
            Log::info('Caché no disponible temporalmente: ' . $cacheEx->getMessage());
        }

        // 3. Si hay API Key de Gemini configurada, intentamos consultar a la IA
        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (!empty($apiKey)) {
            try {
                $respuestaIa = $this->consultarGemini($mensaje, $apiKey);
                if (!empty($respuestaIa)) {
                    // Guardamos en caché por 24 horas para preguntas idénticas
                    try {
                        Cache::put($cacheKey, $respuestaIa, now()->addHours(24));
                    } catch (\Throwable $e) {}

                    return response()->json([
                        'ok' => true,
                        'respuesta' => $respuestaIa,
                        'fuente' => 'ia',
                        'sugerencias' => $this->generarSugerencias($mensaje)
                    ]);
                }
            } catch (\Throwable $e) {
                // Si la cuota se agota (429), hay error de red o timeout, registramos y caemos al fallback inteligente
                Log::warning('Fallo en API IA Chatbot, activando fallback local: ' . $e->getMessage());
            }
        }

        // 4. Fallback Inteligente / Motor de Respuestas Locales (Garantiza servicio 100% continuo)
        $respuestaLocal = $this->motorReglasLocales($mensaje);

        // Guardamos en caché si está disponible
        try {
            Cache::put($cacheKey, $respuestaLocal, now()->addHours(48));
        } catch (\Throwable $e) {}

        return response()->json([
            'ok' => true,
            'respuesta' => $respuestaLocal,
            'fuente' => 'local',
            'sugerencias' => $this->generarSugerencias($mensaje)
        ]);
    }

    /**
     * Resuelve las acciones rápidas de botones (100% exactas y libres de costo de tokens).
     */
    protected function manejarAccionRapida(string $accion): array
    {
        switch ($accion) {
            case 'horarios':
                return [
                    'ok' => true,
                    'respuesta' => "🕒 **Nuestros Horarios de Atención:**\n\n" .
                                   "• **Lunes a Viernes:** 8:00 AM a 8:00 PM (Jornada continua)\n" .
                                   "• **Sábados:** 9:00 AM a 2:00 PM\n" .
                                   "• **Domingos y Festivos:** Cerrado\n\n" .
                                   "📍 Estamos ubicados en **Calle 10 # 10-35, Fuentedeoro, Meta**.",
                    'sugerencias' => ['Pedir Cita', 'Ver Servicios', '¿Cómo llegar?']
                ];

            case 'servicios':
                return [
                    'ok' => true,
                    'respuesta' => "🦷 **Especialidades y Servicios en Odonto Estética:**\n\n" .
                                   "1. **Valoración y Diagnóstico Inicial:** Examen clínico con odontograma.\n" .
                                   "2. **Higiene Profunda (Detartraje):** Remoción de sarro y profilaxis dental.\n" .
                                   "3. **Resinas Estéticas:** Calzas del color natural de tu diente.\n" .
                                   "4. **Ortodoncia:** Brackets metálicos y controles mensuales de alineación.\n" .
                                   "5. **Endodoncia:** Tratamiento de conducto para salvar piezas con dolor profundo.\n" .
                                   "6. **Prótesis Dentales:** Totales o parciales removibles en acrílico.\n" .
                                   "7. **Exodoncias:** Extracciones dentales seguras y sin dolor.",
                    'sugerencias' => ['Pedir Cita', 'Horarios y Ubicación', 'Hablar con un asesor']
                ];

            case 'pedir_cita':
                return [
                    'ok' => true,
                    'respuesta' => "📅 **¿Cómo agendar tu cita?**\n\n" .
                                   "Es muy fácil a través de nuestra plataforma:\n\n" .
                                   "1. Si ya tienes cuenta, [Inicia Sesión aquí](/login) y dirígete a **Pedir Cita**.\n" .
                                   "2. Si es tu primera vez, [Crea tu cuenta de Paciente aquí](/registro) en solo 1 minuto.\n" .
                                   "3. Podrás elegir tu odontólogo de preferencia, el tratamiento y el horario que mejor te convenga.",
                    'sugerencias' => ['Ver Servicios', 'Horarios y Ubicación', 'Contactar por WhatsApp']
                ];

            case 'urgencia':
                return [
                    'ok' => true,
                    'respuesta' => "🚨 **Orientación para Urgencias Dentales:**\n\n" .
                                   "• **Dolor intenso de muela:** Puedes colocar una compresa fría en la mejilla externa. No coloques pastillas directamente sobre la encía ni consumas bebidas extremadamente frías o calientes.\n" .
                                   "• **Sangrado o inflamación:** Realiza enjuagues suaves con agua tibia y sal.\n" .
                                   "• **Diente fracturado o desalojado:** Si se cayó el diente, guárdalo en un vaso con leche fría o suero y acude lo más pronto posible.\n\n" .
                                   "📞 **Línea de soporte y atención prioritaria:** Llámanos o escríbenos al **3115204752** para recibirte de inmediato.",
                    'sugerencias' => ['Llamar al 3115204752', 'Horarios y Ubicación', 'Pedir Cita']
                ];

            case 'contacto':
                return [
                    'ok' => true,
                    'respuesta' => "💬 **Canales de Atención Directa:**\n\n" .
                                   "• **Teléfono / WhatsApp:** [3115204752](https://wa.me/573115204752?text=Hola,%20quisiera%20información%20sobre%20una%20cita%20en%20Odonto%20Estética)\n" .
                                   "• **Correo:** odontoestetica@gmail.com\n" .
                                   "• **Dirección:** Calle 10 # 10-35, Fuentedeoro, Meta\n\n" .
                                   "También puedes diligenciar el formulario de contacto al final de esta página y te responderemos a la brevedad.",
                    'sugerencias' => ['Pedir Cita', 'Horarios y Ubicación', 'Ver Servicios']
                ];

            default:
                return [
                    'ok' => true,
                    'respuesta' => 'Hola, ¿en qué te puedo orientar hoy sobre tu salud oral o sobre nuestros servicios?',
                    'sugerencias' => ['Pedir Cita', 'Horarios y Ubicación', 'Ver Servicios', 'Urgencias']
                ];
        }
    }

    /**
     * Consulta a la API de Google Gemini con un prompt del sistema especializado en la clínica.
     */
    protected function consultarGemini(string $mensaje, string $apiKey): ?string
    {
        $modelo = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

        $systemInstruction = "Eres 'DentiBot', el asistente virtual inteligente y empático de la clínica odontológica 'Odonto Estética' en Fuentedeoro, Meta, Colombia (Dirección: Calle 10#10-35, Teléfono/WhatsApp: 3115204752).
Horarios: Lunes a Viernes de 8:00 AM a 8:00 PM, Sábados de 9:00 AM a 2:00 PM, Domingos cerrado.
Servicios ofrecidos: Valoración y diagnóstico inicial, Limpieza profunda (detartraje y profilaxis), Resinas de fotocurado (calzas estéticas), Ortodoncia (brackets metálicos y controles), Endodoncias (tratamiento de conductos), Prótesis dentales completas y parciales removibles, y Extracciones (exodoncias simples).
Reglas:
1. Responde siempre en español, con un tono cálido, humano, profesional y tranquilizador.
2. Respuestas directas, concisas y fáciles de leer (máximo 2 párrafos cortos). Usa viñetas breves si listas cosas.
3. NUNCA recetes medicamentos específicos con dosis (no indiques 'tome amoxicilina 500mg'). En caso de dolor, sugiere analgésicos de venta libre comunes o medidas físicas (compresas frías) e invita a la valoración médica.
4. Para precios, aclara que dependen de la valoración clínica personalizada, pero que la consulta diagnóstica es accesible.
5. Invita al usuario a registrarse o iniciar sesión en la web para pedir su cita con su odontólogo preferido, o a contactar al 3115204752.";

        $response = Http::timeout(8)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemInstruction . "\n\nPregunta del paciente: " . $mensaje]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 350,
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!empty($texto)) {
                return trim($texto);
            }
        }

        return null;
    }

    /**
     * Motor de reglas locales inteligentes: responde con alta precisión cuando no hay IA o se agotan los tokens.
     */
    protected function motorReglasLocales(string $mensaje): string
    {
        $m = mb_strtolower($mensaje);

        // 1. Cuidados Post-Operatorios / Post-Extracción / Post-Cirugía (Duda frecuente de pacientes)
        if (preg_match('/\b(ayer|sacaron|sacar|extraje|extracci[oó]n|oper[oó]|cirug[ií]a|post|despu[eé]s de|puedo comer|tomar caf[eé]|sangr|gasa)\b/u', $m) &&
            preg_match('/\b(comer|tomar|caf[eé]|sopa|caliente|sangre|sangrado|cuidado|hielo|enjuag|fumar|licor|alcohol)\b/u', $m)) {
            return "📌 **Recomendaciones para Cuidados Post-Tratamiento / Extracción:**\n\n" .
                   "• **Alimentación:** Consume dieta blanda y fría o tibia (helado, gelatina, puré). Evita comidas y bebidas calientes durante las primeras 24 a 48 horas.\n" .
                   "• **Sangrado leve:** Es normal que la saliva salga rosada. Si gotea sangre, muerde una gasa estéril limpia durante 30 a 45 minutos sin escupir.\n" .
                   "• **Prohibido:** NO uses pitillos/pajillas (la succión desaloja el coágulo), no fumes, no tomes alcohol ni hagas enjuagues con fuerza el primer día.\n\n" .
                   "Si el sangrado es abundante o el dolor es muy fuerte, comunícate con nosotros de inmediato al **3115204752**.";
        }

        // 2. Dolor Agudo / Urgencias Dentales
        if (preg_match('/\b(dolor|duele|muela|urgencia|emergencia|hinchad[ao]|inflamad[ao]|infecci[oó]n|pus|golpe|parti[oó]|rompi[oó]|palpita)\b/u', $m)) {
            return "Comprendo lo incómodo y preocupante que es el dolor dental. Para aliviar la molestia mientras te atendemos:\n\n" .
                   "• Aplica compresas frías en la mejilla externa (evita colocar calor directo o compresas calientes).\n" .
                   "• Haz buches suaves con agua tibia y media cucharadita de sal (no enjuagues con alcohol ni coloques pastillas sobre la encía).\n" .
                   "• Mantén la cabeza ligeramente elevada al acostarte para reducir la presión.\n\n" .
                   "⚠️ Te sugerimos valoración prioritaria. Contáctanos al **3115204752** o agenda tu cita prioritaria aquí en la página.";
        }

        // 3. Niños, Menores y Acudientes
        if (preg_match('/\b(ni[ñn]o|ni[ñn]a|hijo|hija|menor|menores|acudiente|padre|madre|pediatr[ií]a|beb[eé])\b/u', $m)) {
            return "👨‍👩‍👧 **¡Atendemos a toda la familia!**\n\n" .
                   "En Odonto Estética cuidamos la salud bucal de niños, jóvenes y adultos en un ambiente cálido y sin miedo.\n\n" .
                   "Además, nuestra plataforma cuenta con el módulo de **Acudiente**: puedes crear tu cuenta de adulto y desde tu perfil vincular a tus hijos para solicitar y gestionar sus citas fácilmente.";
        }

        // 4. EPS y Convenios Médicos
        if (preg_match('/\b(eps|sura|sanitas|nueva eps|famisanar|salud total|seguro|convenio|particular|prepagada)\b/u', $m)) {
            return "🏥 **Atención Clínica y Convenios EPS:**\n\n" .
                   "Brindamos atención odontológica particular de alta calidad a tarifas muy accesibles y transparentes. En nuestro sistema registramos tu EPS (Sura, Sanitas, Nueva EPS, Famisanar, Salud Total, etc.) en tu ficha clínica para trazabilidad y antecedentes médicos.\n\n" .
                   "Para conocer coberturas y convenios específicos vigentes, puedes consultarnos directamente al WhatsApp **3115204752**.";
        }

        // 5. Métodos de Pago y Financiación
        if (preg_match('/\b(pago|pagar|m[eé]todos de pago|tarjeta|transferencia|nequi|daviplata|efectivo|financiaci[oó]n|cuota|abono)\b/u', $m)) {
            return "💳 **Medios de Pago Aceptados:**\n\n" .
                   "• Efectivo en recepción.\n" .
                   "• Transferencias electrónicas (Nequi, Daviplata, Bancolombia).\n" .
                   "• Tarjetas débito y crédito.\n" .
                   "• En tratamientos como Ortodoncia y Prótesis, contamos con esquemas de cuota inicial y abonos mensuales según el avance de tus fases.";
        }

        // 6. Citas / Agendamiento / Doctores
        if (preg_match('/\b(cita|agendar|apartar|pedir cita|turno|horario de cita|separar|doctor|doctora|martha)\b/u', $m)) {
            return "Para pedir una cita con nosotros:\n\n" .
                   "1. Haz clic en el botón superior **Pedir Cita**.\n" .
                   "2. Si ya estás registrado, [Inicia Sesión](/login) para elegir tu odontólogo (como la Dra. Martha Romero o especialistas), fecha y hora.\n" .
                   "3. Si es tu primera vez, [Regístrate aquí](/registro) en solo 1 minuto.\n\n" .
                   "¡Estaremos felices de atenderte!";
        }

        // 7. Horarios
        if (preg_match('/\b(horario|hora|abren|cierran|atenci[oó]n|d[ií]as|domingo|s[aá]bado)\b/u', $m)) {
            return "Nuestro horario de atención en consultorio es:\n\n" .
                   "• **Lunes a Viernes:** 8:00 AM a 8:00 PM (Jornada continua)\n" .
                   "• **Sábados:** 9:00 AM a 2:00 PM\n" .
                   "• **Domingos y Festivos:** Cerrado.\n\n" .
                   "Estamos ubicados en Calle 10 # 10-35, Fuentedeoro, Meta.";
        }

        // 8. Ubicación / Dirección
        if (preg_match('/\b(d[oó]nde|ubicaci[oó]n|direcci[oó]n|queda|llegar|ciudad|municipio|meta|fuentedeoro)\b/u', $m)) {
            return "📍 Nos encontramos en la **Calle 10 # 10-35, Fuentedeoro, Meta**.\n\n" .
                   "En la parte inferior de esta página puedes consultar nuestro mapa interactivo de Google Maps para llegar con facilidad.";
        }

        // 9. Ortodoncia / Brackets
        if (preg_match('/\b(ortodoncia|bracket|brackets|frenillos|alinear|dientes torcidos)\b/u', $m)) {
            return "Contamos con servicio especializado de **Ortodoncia**:\n\n" .
                   "• Montaje de brackets metálicos modernos y confortables.\n" .
                   "• Manejo con cuota inicial accesible y controles mensuales periódicos para alinear y corregir la mordida.\n" .
                   "• Acompañamiento en cada fase de tu tratamiento. Te invitamos a una valoración para revisar tu caso.";
        }

        // 10. Limpieza / Profilaxis / Detartraje
        if (preg_match('/\b(limpieza|profilaxis|detartraje|sarro|c[aá]lculo|placa)\b/u', $m)) {
            return "Realizamos **Limpieza Profunda (Detartraje y Profilaxis)** utilizando ultrasonido para remover el sarro acumulado sin desgastar el esmalte, dejando tus dientes limpios y libres de manchas superficiales. Se recomienda realizarla cada 6 meses.";
        }

        // 11. Resinas / Calzas / Caries
        if (preg_match('/\b(resina|calza|calzas|carie|caries|hueco|restauraci[oó]n)\b/u', $m)) {
            return "Realizamos **Resinas de Fotocurado** de alta estética. Restauramos cavidades por caries con un material del mismo tono de tu diente natural, resistente y prácticamente invisible al sonreír.";
        }

        // 12. Endodoncia / Conducto
        if (preg_match('/\b(endodoncia|conducto|nervio|matar nervio)\b/u', $m)) {
            return "La **Endodoncia** es el tratamiento que permite salvar un diente con caries muy profunda o inflamación del nervio, eliminando el dolor de raíz sin necesidad de extraer la pieza dental.";
        }

        // 13. Extracciones / Cordales
        if (preg_match('/\b(extraer|sacar|extracci[oó]n|exodoncia|cordal|cordales|muela del juicio)\b/u', $m)) {
            return "Realizamos **Exodoncias Simples** y retiro de piezas dañadas bajo protocolos de anestesia local cuidadosos, asegurando que el procedimiento sea cómodo, rápido y completamente libre de dolor.";
        }

        // 14. Prótesis
        if (preg_match('/\b(pr[oó]tesis|dentadura|caja|dientes postizos|removible)\b/u', $m)) {
            return "Ofrecemos diseño y adaptación de **Prótesis Totales y Parciales Removibles** en acrílico de excelente calidad, devolviendo la funcionalidad al masticar y la estética a tu sonrisa.";
        }

        // 15. Precios / Costos
        if (preg_match('/\b(precio|precios|costo|costos|cu[aá]nto vale|tarifa|valor|cotizaci[oó]n)\b/u', $m)) {
            return "El costo exacto de los tratamientos depende de la valoración clínica individual, ya que cada caso y anatomía dental es único. Te invitamos a agendar tu cita de diagnóstico para entregarte un presupuesto detallado y transparente sin sorpresas.";
        }

        // 16. Teléfono / WhatsApp
        if (preg_match('/\b(tel[eé]fono|celular|whatsapp|contacto|llamar|n[uú]mero)\b/u', $m)) {
            return "Puedes comunicarte directamente con nosotros vía llamada o WhatsApp al **3115204752**, o escribirnos a **odontoestetica@gmail.com**.";
        }

        // 17. Saludos corteses (cuando no preguntó un tema médico específico)
        if (preg_match('/\b(hola|buenos d[ií]as|buenas tardes|buenas noches|saludos|hey|buen d[ií]a)\b/u', $m)) {
            return "¡Hola! Bienvenido a **Odonto Estética**. Soy tu asistente virtual. ¿En qué te puedo ayudar hoy? Puedes preguntarme sobre citas, horarios, tratamientos, precios o si tienes alguna molestia dental.";
        }

        // Respuesta general de cortesía
        return "Con gusto te ayudo en **Odonto Estética**. Ofrecemos valoración, limpiezas, resinas, ortodoncia, prótesis y endodoncias en Calle 10 # 10-35, Fuentedeoro. ¿Te gustaría agendar una cita o conocer más sobre algún tratamiento?";
    }

    /**
     * Genera sugerencias contextuales según la temática consultada.
     */
    protected function generarSugerencias(string $mensaje): array
    {
        $m = mb_strtolower($mensaje);

        if (preg_match('/(ayer|sacaron|post|oper|sangr|caf[eé]|comer)/u', $m)) {
            return ['Llamar al 3115204752', 'Horarios y Ubicación', 'Pedir Cita de Control'];
        }

        if (preg_match('/(dolor|muela|urgencia)/u', $m)) {
            return ['Llamar al 3115204752', 'Pedir Cita Prioritaria', 'Horarios y Ubicación'];
        }

        if (preg_match('/(precio|costo|valor|cuota)/u', $m)) {
            return ['Pedir Cita de Valoración', 'Ver Servicios', 'Horarios'];
        }

        if (preg_match('/(cita|agendar|doctor|martha)/u', $m)) {
            return ['Iniciar Sesión', 'Crear Cuenta', 'Ver Servicios'];
        }

        if (preg_match('/(eps|convenio|pago)/u', $m)) {
            return ['Consultar al WhatsApp', 'Pedir Cita', 'Ver Servicios'];
        }

        return ['Pedir Cita', 'Horarios y Ubicación', 'Nuestros Servicios', 'Urgencias'];
    }
}
