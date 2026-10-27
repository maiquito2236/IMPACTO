<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación - Odonto Estética</title>
</head>
<body style="margin: 0; padding: 0; background-color: #E6F2FF; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1E293B;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #E6F2FF; padding: 40px 15px;">
        <tr>
            <td align="center">
                <!-- Tarjeta con el estilo exacto de LOGIN_ORIGINAL -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 520px; background-color: #FFFFFF; border-radius: 2rem; box-shadow: 0 15px 40px rgba(15, 98, 254, 0.08); overflow: hidden;">
                    
                    <!-- Cabecera con Logo de la Empresa de Odonto Estética -->
                    <tr>
                        <td style="padding: 36px 30px 22px 30px; text-align: center;">
                            <img src="{{ isset($message) ? $message->embed(public_path('img/logo_odontologia.png')) : asset('img/logo_odontologia.png') }}" 
                                 alt="Odonto Estética" 
                                 style="max-width: 220px; width: 100%; height: auto; display: block; margin: 0 auto 16px auto;">
                            <h1 style="color: #0F172A; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">
                                Odonto Estética
                            </h1>
                        </td>
                    </tr>

                    <!-- Separador suave -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 0;">
                        </td>
                    </tr>

                    <!-- Contenido Principal -->
                    <tr>
                        <td style="padding: 28px 32px 24px 32px;">
                            <p style="font-size: 16px; margin: 0 0 14px 0; color: #0F172A;">
                                Hola, <strong>{{ $nombres }}</strong>:
                            </p>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 22px 0;">
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Tu código de verificación es:
                            </p>

                            <!-- Código OTP con el estilo azul rey característico -->
                            <div style="background-color: #EDF5FF; border: 2px dashed #0F62FE; border-radius: 1rem; padding: 22px 15px; text-align: center; margin: 0 0 22px 0;">
                                <span style="font-size: 38px; font-weight: 800; letter-spacing: 10px; color: #0F62FE; font-family: monospace;">
                                    {{ $codigo }}
                                </span>
                            </div>

                            <p style="font-size: 13px; line-height: 1.6; color: #64748B; margin: 0 0 16px 0;">
                                ⏱️ Este código es válido durante los próximos <strong>15 minutos</strong>. Si no lo utilizas en ese tiempo, deberás solicitar uno nuevo.
                            </p>

                            <p style="font-size: 12px; line-height: 1.5; color: #94A3B8; margin: 0; border-top: 1px solid #E2E8F0; padding-top: 18px;">
                                Si no solicitaste este código, puedes desestimar este mensaje de forma segura. Tu contraseña no cambiará hasta que ingreses este código en el sistema.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer Corporativo -->
                    <tr>
                        <td style="background-color: #F8FAFC; padding: 20px 30px; text-align: center; border-top: 1px solid #E2E8F0; border-bottom-left-radius: 2rem; border-bottom-right-radius: 2rem;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; color: #475569;">
                                Odonto Estética - Tu sonrisa en buenas manos
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94A3B8;">
                                © {{ date('Y') }} Odonto Estética. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
