<?php
namespace App\Helpers;

use App\Config\Database;
use PDO;
use Exception;

class Notificador {

    /**
     * Inserta una notificación en la base de datos para un usuario específico
     */
    public static function insertar($tipoId, $usuarioId, $mensaje) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO notificaciones (TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION, USUARIOS_ID_USUARIOS, MENSAJE, FECHA_ENVIO, ESTADO) VALUES (?, ?, ?, NOW(), 'NO_LEIDA')");
            return $stmt->execute([$tipoId, $usuarioId, $mensaje]);
        } catch (Exception $e) {
            error_log("Error en Notificador::insertar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación a TODOS los administradores activos
     */
    public static function enviarAAdmin($tipoId, $mensaje) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT ID_USUARIOS FROM usuarios WHERE ROLES_ID_ROLES = 1 AND ESTADO = 'Activo'");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $exito = true;
            foreach ($admins as $admin) {
                if (!self::insertar($tipoId, $admin['ID_USUARIOS'], $mensaje)) {
                    $exito = false;
                }
            }
            return $exito;
        } catch (Exception $e) {
            error_log("Error en Notificador::enviarAAdmin - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación a un paciente específico usando su ID_PACIENTE
     */
    public static function enviarAPaciente($idPaciente, $tipoId, $mensaje) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT USUARIOS_ID_USUARIOS FROM paciente WHERE ID_PACIENTE = ? LIMIT 1");
            $stmt->execute([$idPaciente]);
            $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($paciente) {
                return self::insertar($tipoId, $paciente['USUARIOS_ID_USUARIOS'], $mensaje);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en Notificador::enviarAPaciente - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación a un odontólogo específico usando su ID_ODONTOLOGO
     */
    public static function enviarAOdontologo($idOdontologo, $tipoId, $mensaje) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT USUARIOS_ID_USUARIOS FROM odontologo WHERE ID_ODONTOLOGO = ? LIMIT 1");
            $stmt->execute([$idOdontologo]);
            $odo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($odo) {
                return self::insertar($tipoId, $odo['USUARIOS_ID_USUARIOS'], $mensaje);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en Notificador::enviarAOdontologo - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación directa a un ID de usuario (por si ya se tiene de antemano)
     */
    public static function enviarAUsuario($usuarioId, $tipoId, $mensaje) {
        return self::insertar($tipoId, $usuarioId, $mensaje);
    }

    /**
     * Envía un correo electrónico de confirmación de cita al paciente con todos sus detalles.
     */
    public static function enviarCorreoConfirmacionCita($idCita) {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT 
                        c.ID_CITA,
                        c.FECHA_HORA,
                        c.MOTIVO,
                        u_pac.NOMBRES AS PACIENTE_NOMBRES,
                        u_pac.APELLIDOS AS PACIENTE_APELLIDOS,
                        u_pac.CORREO AS PACIENTE_CORREO,
                        CONCAT(COALESCE(u_doc.NOMBRES, ''), ' ', COALESCE(u_doc.APELLIDOS, '')) AS NOMBRE_DOCTOR,
                        cons.NOMBRE AS CONSULTORIO,
                        GROUP_CONCAT(DISTINCT esp.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS ESPECIALIDAD,
                        COALESCE(
                            NULLIF(GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', '), ''),
                            c.MOTIVO
                        ) AS TRATAMIENTO
                    FROM cita c
                    LEFT JOIN paciente pac ON c.PACIENTE_ID_PACIENTE = pac.ID_PACIENTE
                    LEFT JOIN usuarios u_pac ON pac.USUARIOS_ID_USUARIOS = u_pac.ID_USUARIOS
                    LEFT JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                    LEFT JOIN usuarios u_doc ON o.USUARIOS_ID_USUARIOS = u_doc.ID_USUARIOS
                    LEFT JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                    LEFT JOIN especialidad esp ON oe.ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                    LEFT JOIN consultorio cons ON o.CONSULTORIO_ID_CONSULTORIO = cons.ID_CONSULTORIO
                    LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                    LEFT JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                    WHERE c.ID_CITA = :id_cita
                    GROUP BY c.ID_CITA LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_cita', $idCita, PDO::PARAM_INT);
            $stmt->execute();
            $citaInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$citaInfo || empty($citaInfo['PACIENTE_CORREO'])) {
                error_log("Notificador::enviarCorreoConfirmacionCita - No se encontró correo para la cita ID: $idCita");
                return false;
            }

            $nombrePaciente = trim($citaInfo['PACIENTE_NOMBRES'] . ' ' . $citaInfo['PACIENTE_APELLIDOS']);
            $correoPaciente = $citaInfo['PACIENTE_CORREO'];
            $nombreDoctor   = !empty($citaInfo['NOMBRE_DOCTOR']) ? $citaInfo['NOMBRE_DOCTOR'] : 'Odontólogo Asignado';
            $especialidad   = !empty($citaInfo['ESPECIALIDAD']) ? $citaInfo['ESPECIALIDAD'] : 'Odontología General';
            $consultorio    = !empty($citaInfo['CONSULTORIO']) ? $citaInfo['CONSULTORIO'] : 'Consultorio Principal';
            $tratamiento    = !empty($citaInfo['TRATAMIENTO']) ? $citaInfo['TRATAMIENTO'] : ($citaInfo['MOTIVO'] ?: 'Consulta Odontológica');

            $timestamp      = strtotime($citaInfo['FECHA_HORA']);
            $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            $diaSemana = $dias[date('w', $timestamp)];
            $diaNum    = date('d', $timestamp);
            $mesTexto  = $meses[(int)date('n', $timestamp)];
            $anio      = date('Y', $timestamp);
            $horaFmt   = date('h:i A', $timestamp);

            $fechaCompleta = "$diaSemana, $diaNum de $mesTexto de $anio";

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '571egr@gmail.com';
            $mail->Password   = 'kxddxrqscozpwuxb';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPOptions = array(
                'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
            );

            $logoPath = __DIR__ . '/../../public/img/logo_odontologia.png';
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo_odontologia');
            }

            $mail->setFrom('571egr@gmail.com', 'Odonto Estética');
            $mail->addAddress($correoPaciente, $nombrePaciente);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmación de Cita Médica - Odonto Estética';

            $logoHtml = file_exists($logoPath) ? "<img src='cid:logo_odontologia' alt='Odonto Estética' style='max-height: 70px; margin-bottom: 15px; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2));'>" : "";

            $mail->Body = "
            <div style='background-color: #f4f7fa; padding: 30px 15px; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08);'>
                    
                    <!-- Header -->
                    <div style='background: linear-gradient(135deg, #0F62FE 0%, #0043CE 100%); padding: 30px; text-align: center; color: #ffffff;'>
                        $logoHtml
                        <h1 style='margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;'>¡Cita Confirmada!</h1>
                        <p style='margin-top: 8px; margin-bottom: 0; font-size: 14px; opacity: 0.9;'>Tu cita médica ha sido agendada con éxito</p>
                    </div>

                    <!-- Body Content -->
                    <div style='padding: 30px 25px; color: #333333;'>
                        <p style='font-size: 16px; margin-top: 0;'>Hola <strong style='color: #0F62FE;'>" . htmlspecialchars($nombrePaciente) . "</strong>,</p>
                        <p style='font-size: 14px; color: #555555; line-height: 1.6;'>
                            Te confirmamos que tu cita médica en <strong>Odonto Estética</strong> ha sido agendada correctamente. A continuación encuentras el detalle completo de tu consulta:
                        </p>

                        <!-- Card Detalle de Cita -->
                        <div style='background-color: #f8fafd; border: 1px solid #e1e8f5; border-left: 5px solid #0F62FE; border-radius: 10px; padding: 20px; margin: 25px 0;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                                <tr>
                                    <td style='padding: 8px 0; color: #666666; width: 35%; vertical-align: top;'>📅 <strong>Fecha:</strong></td>
                                    <td style='padding: 8px 0; color: #111111; font-weight: 600;'>" . htmlspecialchars($fechaCompleta) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666666; vertical-align: top;'>⏰ <strong>Hora:</strong></td>
                                    <td style='padding: 8px 0; color: #0F62FE; font-weight: 700; font-size: 16px;'>" . htmlspecialchars($horaFmt) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666666; vertical-align: top;'>👨‍⚕️ <strong>Odontólogo:</strong></td>
                                    <td style='padding: 8px 0; color: #111111; font-weight: 600;'>" . htmlspecialchars($nombreDoctor) . " <span style='font-size: 12px; color: #777777;'>(" . htmlspecialchars($especialidad) . ")</span></td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666666; vertical-align: top;'>🦷 <strong>Procedimiento:</strong></td>
                                    <td style='padding: 8px 0; color: #111111; font-weight: 600;'>" . htmlspecialchars($tratamiento) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666666; vertical-align: top;'>🏥 <strong>Consultorio:</strong></td>
                                    <td style='padding: 8px 0; color: #111111; font-weight: 600;'>" . htmlspecialchars($consultorio) . "</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Indicaciones Importantes -->
                        <div style='background-color: #fff9e6; border: 1px solid #ffe599; border-radius: 8px; padding: 15px; margin-bottom: 25px;'>
                            <h4 style='margin: 0 0 8px 0; color: #b7791f; font-size: 14px;'>
                                📌 Recomendaciones para tu cita:
                            </h4>
                            <ul style='margin: 0; padding-left: 20px; font-size: 13px; color: #744210; line-height: 1.5;'>
                                <li>Llegar con <strong>10 a 15 minutos de anticipación</strong>.</li>
                                <li>Traer tu documento de identidad original.</li>
                                <li>Si necesitas reprogramar o cancelar, por favor realiza el proceso desde el portal con anticipación.</li>
                            </ul>
                        </div>

                        <p style='font-size: 13px; color: #777777; text-align: center; margin-bottom: 0;'>
                            Si tienes alguna duda o inquietud, puedes comunicarte con la clínica. ¡Gracias por confiar en nosotros!
                        </p>
                    </div>

                    <!-- Footer -->
                    <div style='background-color: #eef2f7; padding: 20px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #e1e8f5;'>
                        <p style='margin: 0 0 5px 0;'><strong>Odonto Estética</strong> - Tu sonrisa en buenas manos</p>
                        <p style='margin: 0;'>Este es un mensaje automático, por favor no respondas directamente a este correo.</p>
                    </div>
                </div>
            </div>";

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error en Notificador::enviarCorreoConfirmacionCita - " . $e->getMessage());
            return false;
        }
    }
}

