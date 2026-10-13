<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('action')) {
            return $this->api($request);
        }
        return view('odontologo.pacientes');
    }

    public function api(Request $request, $op = null)
    {
        $op = $op ?: $request->input('action', $request->query('action', 'listar'));
        $op = str_replace('odontologo/pacientes/', '', $op);
        switch ($op) {
            case 'listar':
                return $this->listar();
            default:
                return response()->json(['status' => 'error', 'message' => "Operación no válida: $op"], 400);
        }
    }

    public function listar()
    {
        $pacientes = DB::select("
            SELECT 
                ID_PACIENTE,
                NOMBRES, 
                APELLIDOS, 
                NUMERO_DOCUMENTO, 
                CORREO, 
                TELEFONO,
                COALESCE(ESTADO_USUARIO, 'Activo') AS ESTADO
            FROM vista_pacientes
            ORDER BY NOMBRES ASC
        ");

        return response()->json($pacientes);
    }
}
