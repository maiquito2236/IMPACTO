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
                        'descanso_inicio' => $request->descanso_inicio ?: null,
                        'descanso_fin' => $request->descanso_fin ?: null,
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
        return DB::select("SELECT o.ID_ODONTOLOGO, u.NOMBRES, u.APELLIDOS, IFNULL(c.NOMBRE, 'Sin asignar') AS CONSULTORIO_NOMBRE, IFNULL(c.UBICACION, '') AS PISO FROM odontologo o JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN consultorio c ON o.CONSULTORIO_ID_CONSULTORIO = c.ID_CONSULTORIO WHERE u.ESTADO = 'Activo'");
    }

    private function obtenerTodosConOdontologos() {
        $sql = "SELECT h.ID_HORARIO, h.ODONTOLOGO_ID_ODONTOLOGO, TIME(c.FECHA_HORA) AS HORA_INICIO, ADDTIME(TIME(c.FECHA_HORA), '01:00:00') AS HORA_FIN, h.JORNADA, h.FECHA, 'Ocupado' AS ESTADO, u.NOMBRES, u.APELLIDOS, IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO, p.NOMBRES AS NOMBRE_PACIENTE, p.APELLIDOS AS APELLIDO_PACIENTE, pr.ID_PROCEDIMIENTO, pr.NOMBRE_PROCEDIMIENTO, h.descanso_inicio, h.descanso_fin FROM horario_disponibilidad h JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO INNER JOIN cita c ON h.ID_HORARIO = c.HORARIO_DISPONIBILIDAD_ID_HORARIO AND c.ESTADO_CITA_ID = 1 LEFT JOIN paciente pa ON c.PACIENTE_ID_PACIENTE = pa.ID_PACIENTE LEFT JOIN usuarios p ON pa.USUARIOS_ID_USUARIOS = p.ID_USUARIOS LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA LEFT JOIN procedimientos pr ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO WHERE h.ESTADO = 'Disponible' UNION ALL SELECT h.ID_HORARIO, h.ODONTOLOGO_ID_ODONTOLOGO, h.HORA_INICIO, h.HORA_FIN, h.JORNADA, h.FECHA, h.ESTADO, u.NOMBRES, u.APELLIDOS, IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO, NULL AS NOMBRE_PACIENTE, NULL AS APELLIDO_PACIENTE, NULL AS ID_PROCEDIMIENTO, NULL AS NOMBRE_PROCEDIMIENTO, h.descanso_inicio, h.descanso_fin FROM horario_disponibilidad h JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO WHERE h.ESTADO = 'Disponible'";
        return DB::select($sql);
    }

    private function obtenerPorFecha($fecha) {
        $sql = "SELECT h.ID_HORARIO, h.ODONTOLOGO_ID_ODONTOLOGO, TIME(c.FECHA_HORA) AS HORA_INICIO, ADDTIME(TIME(c.FECHA_HORA), '01:00:00') AS HORA_FIN, h.JORNADA, h.FECHA, 'Ocupado' AS ESTADO, u.NOMBRES, u.APELLIDOS, IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO, p.NOMBRES AS NOMBRE_PACIENTE, p.APELLIDOS AS APELLIDO_PACIENTE, pr.ID_PROCEDIMIENTO, pr.NOMBRE_PROCEDIMIENTO, h.descanso_inicio, h.descanso_fin FROM horario_disponibilidad h JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO INNER JOIN cita c ON h.ID_HORARIO = c.HORARIO_DISPONIBILIDAD_ID_HORARIO AND c.ESTADO_CITA_ID = 1 LEFT JOIN paciente pa ON c.PACIENTE_ID_PACIENTE = pa.ID_PACIENTE LEFT JOIN usuarios p ON pa.USUARIOS_ID_USUARIOS = p.ID_USUARIOS LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA LEFT JOIN procedimientos pr ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO WHERE h.ESTADO = 'Disponible' AND h.FECHA = ? UNION ALL SELECT h.ID_HORARIO, h.ODONTOLOGO_ID_ODONTOLOGO, h.HORA_INICIO, h.HORA_FIN, h.JORNADA, h.FECHA, h.ESTADO, u.NOMBRES, u.APELLIDOS, IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO, NULL AS NOMBRE_PACIENTE, NULL AS APELLIDO_PACIENTE, NULL AS ID_PROCEDIMIENTO, NULL AS NOMBRE_PROCEDIMIENTO, h.descanso_inicio, h.descanso_fin FROM horario_disponibilidad h JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO WHERE h.ESTADO = 'Disponible' AND h.FECHA = ?";
        return DB::select($sql, [$fecha, $fecha]);
    }
}
