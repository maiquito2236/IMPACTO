<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notificador
{
    /**
     * Procesa la notificación:
     * 1. Si es un aviso crítico de cita, envía SIEMPRE el correo electrónico transaccional.
     * 2. Registra la notificación en la bandeja web mediante sp_registrar_notificacion_web
     *    únicamente si el usuario tiene NOTIFICACIONES_ACTIVAS = 1.
     */
    public static function insertar($tipoId, $usuarioId, $mensaje)
    {
        try {
            // 1. Los correos de citas son transaccionales e importantes: se envían siempre
            if ($usuarioId && in_array((int) $tipoId, [1, 2, 7, 8, 10, 11, 13, 14, 21], true)) {
                self::enviarCorreoCita($usuarioId, (int) $tipoId, $mensaje);
            }

            // 2. Verificar SIEMPRE el flag antes de cualquier inserción web
            if ($usuarioId) {
                $notificacionesActivas = DB::table('usuarios')
                    ->where('ID_USUARIOS', $usuarioId)
                    ->value('NOTIFICACIONES_ACTIVAS');

                // Si el usuario desactivó notificaciones, no insertar en bandeja web
                if ($notificacionesActivas !== null && (int) $notificacionesActivas === 0) {
                    return false;
                }
            }

            // 3. La notificación en la página web respeta el switch NOTIFICACIONES_ACTIVAS
            try {
                DB::statement('CALL sp_registrar_notificacion_web(?, ?, ?, @p_insertado)', [
                    (int) $tipoId,
                    (int) $usuarioId,
                    (string) $mensaje,
                ]);
                $res = DB::selectOne('SELECT @p_insertado AS insertado');
                return (bool) ($res->insertado ?? false);
            } catch (\Exception $spEx) {
                // Fallback directo si el SP falla — el flag ya fue verificado arriba
                return DB::table('notificaciones')->insert([
                    'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => $tipoId,
                    'USUARIOS_ID_USUARIOS'                   => $usuarioId,
                    'MENSAJE'                                => $mensaje,
                    'FECHA_ENVIO'                            => now(),
                    'ESTADO'                                 => 'NO_LEIDA'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error en Notificador::insertar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía un correo electrónico al paciente (o su acudiente) o al odontólogo sobre el estado de su cita
     */
    public static function enviarCorreoCita($usuarioId, int $tipoId, string $mensaje)
    {
        try {
            if (!$usuarioId) {
                return;
            }

            $usuario = DB::table('usuarios')->where('ID_USUARIOS', $usuarioId)->first();
            if (!$usuario) {
                return;
            }

            $correo = trim($usuario->CORREO ?? '');
            $nombres = trim($usuario->NOMBRES ?? 'Usuario');

            // Si es un menor sin correo propio, buscar el correo de su acudiente principal
            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $acudiente = DB::table('paciente as p')
                    ->join('paciente_has_acudiente as pha', 'p.ID_PACIENTE', '=', 'pha.PACIENTE_ID_PACIENTE')
                    ->join('usuarios as u', 'pha.USUARIOS_ID_ACUDIENTE', '=', 'u.ID_USUARIOS')
                    ->where('p.USUARIOS_ID_USUARIOS', $usuarioId)
                    ->select('u.CORREO', 'u.NOMBRES')
                    ->first();

                if ($acudiente && !empty($acudiente->CORREO)) {
                    $correo = trim($acudiente->CORREO);
                    $nombres = trim($acudiente->NOMBRES ?? $nombres);
                }
            }

            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $titulos = [
                1  => 'Confirmación de Cita Agendada',
                2  => 'Recordatorio de Cita Odontológica',
                7  => 'Notificación de Cita Cancelada',
                8  => 'Aviso de Cita Cancelada en Agenda',
                10 => 'Confirmación de Cita',
                11 => 'Confirmación de Cita en Agenda',
                13 => 'Notificación de Cita Reprogramada',
                14 => 'Aviso de Cita Reprogramada en Agenda',
                21 => 'Nueva Cita Asignada en Agenda',
            ];

            $titulo = $titulos[$tipoId] ?? 'Notificación de Cita';
            $esCancelacion = in_array($tipoId, [7, 8], true);

            Mail::send('emails.notificacion_cita', [
                'nombres'        => $nombres,
                'titulo'         => $titulo,
                'mensaje'        => $mensaje,
                'es_cancelacion' => $esCancelacion,
            ], function ($mail) use ($correo, $nombres, $titulo) {
                $mail->to($correo, $nombres)
                     ->subject("$titulo - Odonto Estética");
            });
        } catch (\Exception $eMail) {
            Log::warning("No se pudo enviar correo de cita a usuario {$usuarioId}: " . $eMail->getMessage());
        }
    }

    /**
     * Enviar notificación a TODOS los administradores activos
     */
    public static function enviarAAdmin($tipoId, $mensaje)
    {
        try {
            $admins = DB::table('usuarios')
                ->whereIn('ROLES_ID_ROLES', [1, 4])
                ->where('ESTADO', 'Activo')
                ->pluck('ID_USUARIOS');

            $exito = true;
            foreach ($admins as $id) {
                if (!self::insertar($tipoId, $id, $mensaje)) {
                    $exito = false;
                }
            }
            return $exito;
        } catch (\Exception $e) {
            Log::error("Error en Notificador::enviarAAdmin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación a un paciente específico usando su ID_PACIENTE
     */
    public static function enviarAPaciente($idPaciente, $tipoId, $mensaje)
    {
        try {
            $paciente = DB::table('paciente')->where('ID_PACIENTE', $idPaciente)->first();
            if ($paciente && $paciente->USUARIOS_ID_USUARIOS) {
                return self::insertar($tipoId, $paciente->USUARIOS_ID_USUARIOS, $mensaje);
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Error en Notificador::enviarAPaciente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación a un odontólogo específico usando su ID_ODONTOLOGO
     */
    public static function enviarAOdontologo($idOdontologo, $tipoId, $mensaje)
    {
        try {
            $odo = DB::table('odontologo')->where('ID_ODONTOLOGO', $idOdontologo)->first();
            if ($odo && $odo->USUARIOS_ID_USUARIOS) {
                return self::insertar($tipoId, $odo->USUARIOS_ID_USUARIOS, $mensaje);
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Error en Notificador::enviarAOdontologo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación directa a un ID de usuario
     */
    public static function enviarAUsuario($usuarioId, $tipoId, $mensaje)
    {
        return self::insertar($tipoId, $usuarioId, $mensaje);
    }
}