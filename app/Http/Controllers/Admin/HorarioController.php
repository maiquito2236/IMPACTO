<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class HorarioController extends Controller
{
    // Retorna la vista HTML
    public function index()
    {
        return view('admin.horario.index');
    }

    // Enrutador de API centralizado
    public function api(Request $request, $op)
    {
        try {
            switch ($op) {
                case 'listar':
                    return response()->json([
                        'status' => 'success',
                        'data' => $this->obtenerTodosConOdontologos(),
                        'listaOdontologos' => $this->obtenerListaOdontologos()
                    ]);
                
                case 'eliminar':
                    DB::table('horario_disponibilidad')
                        ->where('ID_HORARIO', $request->id_horario)
                        ->update(['ESTADO' => 'Inactivo']);
                    return response()->json(['status' => 'success']);
                
                case 'guardar':
                    $data = [
                        'ODONTOLOGO_ID_ODONTOLOGO' => $request->odontologo_id,
                        'HORA_INICIO' => $request->hora_inicio,
                        'HORA_FIN' => $request->hora_fin,
                        'JORNADA' => $request->jornada,
                        'FECHA' => $request->fecha,
                        'ESTADO' => $request->estado ?? 'Disponible',
                        'DESCANSO_INICIO' => $request->descanso_inicio ?: null,
                        'DESCANSO_FIN' => $request->descanso_fin ?: null,
                    ];
                    
                    if ($request->filled('id_horario')) {
                        DB::table('horario_disponibilidad')->where('ID_HORARIO', $request->id_horario)->update($data);
                    } else {
                        DB::table('horario_disponibilidad')->insert($data);
                    }
                    return response()->json(['status' => 'success']);
                
                case 'listarPorFecha':
                    return response()->json([
                        'status' => 'success',
                        'data' => $this->obtenerPorFecha($request->fecha)
                    ]);
                
                default:
                    return response()->json(['status' => 'error', 'message' => 'Operación no válida'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // --- MÉTODOS PRIVADOS DE BASE DE DATOS ---

    private function obtenerListaOdontologos() {
        return DB::select("
            SELECT 
                ID_ODONTOLOGO, 
                PRIMER_NOMBRE AS NOMBRES, 
                PRIMER_APELLIDO AS APELLIDOS, 
                IFNULL(NOMBRE_CONSULTORIO, 'Sin asignar') AS CONSULTORIO_NOMBRE, 
                IFNULL(UBICACION_CONSULTORIO, '') AS PISO 
            FROM vista_odontologos 
            WHERE ESTADO_USUARIO = 'Activo'
        ");
    }

    private function obtenerTodosConOdontologos() {
        return DB::select("
            SELECT 
                *, 
                DESCANSO_INICIO AS descanso_inicio, 
                DESCANSO_FIN AS descanso_fin 
            FROM vista_horarios_con_citas
        ");
    }

    private function obtenerPorFecha($fecha) {
        return DB::select("
            SELECT 
                *, 
                DESCANSO_INICIO AS descanso_inicio, 
                DESCANSO_FIN AS descanso_fin 
            FROM vista_horarios_con_citas 
            WHERE FECHA = ?
        ", [$fecha]);
    }
}
