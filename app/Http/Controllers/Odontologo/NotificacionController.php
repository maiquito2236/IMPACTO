<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class NotificacionController extends Controller
{
    private function getUsuarioId()
    {
        if (Auth::check()) {
            return Auth::id();
        }
        $odontologo = DB::table('odontologo')->first();
        return $odontologo ? $odontologo->USUARIOS_ID_USUARIOS : 2;
    }

    public function index()
    {
        $usuarioId = $this->getUsuarioId();

        $raw = DB::select("
            SELECT ID_NOTIFICACIONES, MENSAJE, FECHA_ENVIO, ESTADO, 
                   COALESCE(TIPO_NOMBRE, 'General') AS TIPO_NOMBRE
            FROM vista_notificaciones
            WHERE ID_USUARIOS = ?
            ORDER BY FECHA_ENVIO DESC
        ", [$usuarioId]);

        $notificaciones = array_map(fn($item) => (array)$item, $raw);
        $totalAlertas = count($notificaciones);
        $sinLeer = count(array_filter($notificaciones, fn($n) => $n['ESTADO'] === 'NO_LEIDA'));

        $tiposNotificacion = DB::table('tipo_notificacion')->pluck('NOMBRE')->toArray();

        return view('odontologo.notificaciones', compact(
            'notificaciones',
            'totalAlertas',
            'sinLeer',
            'tiposNotificacion'
        ));
    }

    public function api(Request $request, $op)
    {
        switch ($op) {
            case 'listar':
                return $this->listar();
            case 'marcar_leida':
                return $this->marcarComoLeida($request);
            case 'marcar_todas_leidas':
            case 'marcar_todas':
                return $this->marcarTodas();
            default:
                return response()->json(['status' => 'error', 'message' => "Operación no válida: $op"], 400);
        }
    }

    public function listar()
    {
        $usuarioId = $this->getUsuarioId();

        $raw = DB::select("
            SELECT ID_NOTIFICACIONES, MENSAJE, FECHA_ENVIO, ESTADO, 
                   COALESCE(TIPO_NOMBRE, 'General') AS TIPO_NOMBRE
            FROM vista_notificaciones
            WHERE ID_USUARIOS = ?
            ORDER BY FECHA_ENVIO DESC
        ", [$usuarioId]);

        return response()->json(['success' => true, 'data' => $raw]);
    }

    public function marcarComoLeida(Request $request)
    {
        $id = (int) ($request->input('id') ?? $request->json('id', 0));
        $usuarioId = $this->getUsuarioId();

        if ($id > 0) {
            $updated = DB::table('notificaciones')
                ->where('ID_NOTIFICACIONES', $id)
                ->where('USUARIOS_ID_USUARIOS', $usuarioId)
                ->update(['ESTADO' => 'LEIDA']);

            return response()->json(['success' => (bool)$updated]);
        }

        return response()->json(['success' => false, 'error' => 'ID inválido'], 400);
    }

    public function marcarTodas()
    {
        $usuarioId = $this->getUsuarioId();

        DB::table('notificaciones')
            ->where('USUARIOS_ID_USUARIOS', $usuarioId)
            ->where('ESTADO', 'NO_LEIDA')
            ->update(['ESTADO' => 'LEIDA']);

        return response()->json(['success' => true]);
    }
}
