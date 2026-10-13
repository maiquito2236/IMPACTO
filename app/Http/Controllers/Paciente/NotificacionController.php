<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        $notificaciones = DB::select("
            SELECT ID_NOTIFICACIONES, MENSAJE, FECHA_ENVIO, ESTADO, TIPO_NOMBRE
            FROM vista_notificaciones
            WHERE ID_USUARIOS = ? OR (USUARIOS_ID_USUARIOS IS NULL AND ROL_DESTINO = 3)
            ORDER BY FECHA_ENVIO DESC
        ", [$idUsuario]);

        $notificaciones = json_decode(json_encode($notificaciones), true);

        $totalAlertas = count($notificaciones);
        $sinLeer = 0;
        $leidas = 0;

        foreach ($notificaciones as $n) {
            if ($n['ESTADO'] === 'NO_LEIDA') {
                $sinLeer++;
            } else {
                $leidas++;
            }
        }

        $tiposNotificacion = DB::table('tipo_notificacion')
            ->where('ROL_DESTINO', 3)
            ->where('ESTADO', 'ACTIVO')
            ->orderBy('NOMBRE', 'asc')
            ->pluck('NOMBRE')
            ->toArray();

        return view('paciente.notificaciones', compact('notificaciones', 'totalAlertas', 'sinLeer', 'leidas', 'tiposNotificacion'));
    }

    public function marcarLeida(Request $request)
    {
        $id = intval($request->input('id'));
        $idUsuario = Auth::id() ?? session('usuario_id');

        $updated = DB::table('notificaciones')
            ->where('ID_NOTIFICACIONES', $id)
            ->where(function ($q) use ($idUsuario) {
                $q->where('USUARIOS_ID_USUARIOS', $idUsuario)
                  ->orWhereNull('USUARIOS_ID_USUARIOS');
            })
            ->update(['ESTADO' => 'LEIDA']);

        return response()->json(['success' => (bool) $updated]);
    }

    public function marcarTodas(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        $updated = DB::update("
            UPDATE notificaciones n
            INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
            SET n.ESTADO = 'LEIDA'
            WHERE n.ESTADO = 'NO_LEIDA'
              AND (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 3))
        ", [$idUsuario]);

        return response()->json(['success' => true]);
    }
}