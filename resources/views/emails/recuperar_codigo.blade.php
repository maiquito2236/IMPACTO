<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación - Odonto Estética</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F0F4F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1E293B;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F0F4F8; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 520px; background-color: #FFFFFF; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden;">
                    <!-- Cabecera -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0F62FE 0%, #0043CE 100%); padding: 32px 30px; text-align: center;">
                            <h1 style="color: #FFFFFF; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">
                                Odonto Estética
                            </h1>
                            <p style="color: #D0E2FF; margin: 6px 0 0 0; font-size: 13px;">
                                Sistema de Gestión Odontológica (SIGCO)
                            </p>
                        </td>
                    </tr>

                    <!-- Contenido Principal -->
                    <tr>
                        <td style="padding: 36px 32px 28px 32px;">
                            <p style="font-size: 16px; margin: 0 0 16px 0; color: #0F172A;">
                                Hola, <strong>{{ $nombres }}</strong>:
                            </p>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px 0;">
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Tu código de verificación es:
                            </p>

                            <!-- Código OTP -->
                            <div style="background-color: #EDF5FF; border: 2px dashed #0F62FE; border-radius: 12px; padding: 20px 15px; text-align: center; margin: 0 0 24px 0;">
                                <span style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #0F62FE; font-family: monospace;">
                                    {{ $codigo }}
                                </span>
                            </div>

                            <p style="font-size: 13px; line-height: 1.6; color: #64748B; margin: 0 0 16px 0;">
                                ⏱️ Este código es válido durante los próximos <strong>15 minutos</strong>. Si no lo utilizas en ese tiempo, deberás solicitar uno nuevo.
                            </p>

                            <p style="font-size: 12px; line-height: 1.5; color: #94A3B8; margin: 0; border-top: 1px solid #E2E8F0; padding-top: 18px;">
                                Si no solicitaste este código, puedes desestimar este mensaje de forma segura. Tu contraseña no cambiará hasta que ingreses este código.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F8FAFC; padding: 20px 30px; text-align: center; border-top: 1px solid #E2E8F0;">
                            <p style="margin: 0; font-size: 12px; color: #94A3B8;">
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
