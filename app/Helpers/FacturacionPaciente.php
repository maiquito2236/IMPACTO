<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacturacionPaciente
{
    /**
     * Obtiene una factura solo si pertenece al paciente autenticado
     * o a un menor que esté bajo su custodia (acudiente).
     *
     * @param  int  $idFactura
     * @return object|null  Fila de vista_facturacion o null si no autorizado
     */
    public static function obtenerFacturaAutorizada(int $idFactura): ?object
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        if (!$idUsuario) {
            return null;
        }

        // IDs de pacientes que este usuario puede gestionar
        $paciente = DB::table('paciente')
            ->where('USUARIOS_ID_USUARIOS', $idUsuario)
            ->first();

        $pacientesIds = [];

        if ($paciente) {
            $pacientesIds[] = $paciente->ID_PACIENTE;
        }

        // Menores / dependientes a cargo del usuario (acudiente)
        $hijosIds = DB::table('paciente_has_acudiente')
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->pluck('PACIENTE_ID_PACIENTE')
            ->toArray();

        $pacientesIds = array_unique(array_merge($pacientesIds, $hijosIds));

        if (empty($pacientesIds)) {
            return null;
        }

        // Verificar que la factura pertenece a alguno de esos pacientes
        $factura = DB::table('vista_facturacion')
            ->where('ID_FACTURA', $idFactura)
            ->whereIn('PACIENTE_ID_PACIENTE', $pacientesIds)
            ->first();

        return $factura ?: null;
    }

    /**
     * Retorna todos los IDs de pacientes que el usuario autenticado puede ver.
     *
     * @return int[]
     */
    public static function pacientesAutorizados(): array
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        if (!$idUsuario) {
            return [];
        }

        $paciente = DB::table('paciente')
            ->where('USUARIOS_ID_USUARIOS', $idUsuario)
            ->first();

        $ids = $paciente ? [$paciente->ID_PACIENTE] : [];

        $hijosIds = DB::table('paciente_has_acudiente')
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->pluck('PACIENTE_ID_PACIENTE')
            ->toArray();

        return array_unique(array_merge($ids, $hijosIds));
    }
}
