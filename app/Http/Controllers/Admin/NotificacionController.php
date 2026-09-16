<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificacionController extends Controller
{
    /**
     * Muestra la vista de notificaciones del administrador
     */
    public function index()
    {
        $usuarioId = Auth::id() ?: 1;

        // Limpiar automáticamente notificaciones leídas hace más de 30 días
        DB::table('notificaciones')
            ->where('ESTADO', 'LEIDA')
            ->whereNotNull('FECHA_LECTURA')
            ->where('FECHA_LECTURA', '<', now()->subDays(30))
            ->delete();

        $sql = "SELECT
                    n.ID_NOTIFICACIONES,
                    n.MENSAJE,
                    n.FECHA_ENVIO,
                    n.ESTADO,
                    tn.NOMBRE AS TIPO_NOMBRE,
                    u.NOMBRES AS USUARIO_NOMBRES,
                    u.APELLIDOS AS USUARIO_APELLIDOS
                FROM notificaciones n
                INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                LEFT JOIN usuarios u ON n.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE n.USUARIOS_ID_USUARIOS = ?
                   OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 1)
                ORDER BY n.FECHA_ENVIO DESC";

        $notificacionesRaw = DB::select($sql, [$usuarioId]);
        $totalAlertas = count($notificacionesRaw);
        $sinLeer = count(array_filter($notificacionesRaw, fn($n) => $n->ESTADO === 'NO_LEIDA'));

        $tiposNotificacion = DB::table('tipo_notificacion')
            ->where('ROL_DESTINO', 1)
            ->where('ESTADO', 'ACTIVO')
            ->distinct()
            ->orderBy('NOMBRE', 'ASC')
            ->pluck('NOMBRE')
            ->toArray();

        $notificacionesJS = array_map(function($n) {
            return [
                'id'        => (int) $n->ID_NOTIFICACIONES,
                'fecha'     => date('Y-m-d', strtotime($n->FECHA_ENVIO)),
                'hora'      => date('h:i A', strtotime($n->FECHA_ENVIO)),
                'timestamp' => strtotime($n->FECHA_ENVIO),
                'tipo'      => $n->TIPO_NOMBRE,
                'mensaje'   => $n->MENSAJE,
                'paciente'  => trim(($n->USUARIO_NOMBRES ?? '') . ' ' . ($n->USUARIO_APELLIDOS ?? '')) ?: 'Sistema',
                'estado'    => $n->ESTADO === 'NO_LEIDA' ? 'Sin leer' : 'Leída',
            ];
        }, $notificacionesRaw);

        return view('admin.notificaciones.index', compact('totalAlertas', 'sinLeer', 'tiposNotificacion', 'notificacionesJS'));
    }

    /**
     * Enrutador centralizado de API para Notificaciones
     */
    public function api(Request $request, $op)
    {
        $usuarioId = Auth::id() ?: 1;

        try {
            switch ($op) {
                case 'listar':
                    $sql = "SELECT
                                n.ID_NOTIFICACIONES,
                                n.MENSAJE,
                                n.FECHA_ENVIO,
                                n.ESTADO,
                                tn.NOMBRE AS TIPO_NOMBRE,
                                u.NOMBRES AS USUARIO_NOMBRES,
                                u.APELLIDOS AS USUARIO_APELLIDOS
                            FROM notificaciones n
                            INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                            LEFT JOIN usuarios u ON n.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                            WHERE n.USUARIOS_ID_USUARIOS = ?
                               OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 1)
                            ORDER BY n.FECHA_ENVIO DESC";
                    $datos = DB::select($sql, [$usuarioId]);
                    return response()->json(['success' => true, 'data' => $datos]);

                case 'marcar_leida':
                    $id = (int) $request->input('id', 0);
                    if ($id <= 0) throw new Exception("ID de notificación inválido.");

                    $updated = DB::table('notificaciones')
                        ->where('ID_NOTIFICACIONES', $id)
                        ->where(function($q) use ($usuarioId) {
                            $q->where('USUARIOS_ID_USUARIOS', $usuarioId)
                              ->orWhereNull('USUARIOS_ID_USUARIOS');
                        })
                        ->update([
                            'ESTADO' => 'LEIDA',
                            'FECHA_LECTURA' => now()
                        ]);

                    return response()->json(['success' => (bool)$updated]);

                case 'marcar_todas':
                    DB::statement("
                        UPDATE notificaciones n
                        INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                        SET n.ESTADO = 'LEIDA', n.FECHA_LECTURA = NOW() 
                        WHERE n.ESTADO = 'NO_LEIDA' 
                          AND (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 1))
                    ", [$usuarioId]);

                    return response()->json(['success' => true]);

                default:
                    throw new Exception("Operación no válida: $op");
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
