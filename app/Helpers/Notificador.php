<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Notificador
{
    /**
     * Inserta una notificación en la base de datos para un usuario específico
     */
    public static function insertar($tipoId, $usuarioId, $mensaje)
    {
        try {
            return DB::table('notificaciones')->insert([
                'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => $tipoId,
                'USUARIOS_ID_USUARIOS' => $usuarioId,
                'MENSAJE' => $mensaje,
                'FECHA_ENVIO' => now(),
                'ESTADO' => 'NO_LEIDA'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en Notificador::insertar: " . $e->getMessage());
            return false;
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