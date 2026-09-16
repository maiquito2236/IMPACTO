<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class AgendaController extends Controller
{
    // Carga la vista HTML
    public function index()
    {
        return view('admin.agenda.index');
    }

    // Enrutador principal de la API que simula tu viejo controlador
    public function api(Request $request, $op)
    {
        try {
            switch ($op) {
                case 'obtener_citas':
                    if ($request->has('listas')) {
                        return response()->json([
                            'pacientes'    => $this->listarPacientes(),
                            'odontologos'  => $this->listarOdontologos(),
                            'tratamientos' => $this->listarTratamientos(),
                        ], 200, [], JSON_UNESCAPED_UNICODE);
                    }
                    return response()->json($this->listarCitas($request->inicio, $request->fin), 200, [], JSON_UNESCAPED_UNICODE);
                
                case 'obtener_citas_hoy':
                    return response()->json($this->listarCitasHoy(), 200, [], JSON_UNESCAPED_UNICODE);
                
                case 'obtener_horas_ocupadas':
                    if (!$request->has('fecha')) throw new Exception("Falta el parámetro 'fecha'.");
                    return response()->json($this->obtenerHorasOcupadas($request->fecha, $request->id_odontologo), 200, [], JSON_UNESCAPED_UNICODE);
                
                case 'guardar_cita':
                    $id = $this->guardarCita($request->paciente_id, $request->odontologo_id, $request->fecha, $request->hora, $request->tratamiento);
                    return response()->json(['status' => 'success', 'id_cita' => $id]);
                
                case 'cancelar_cita':
                    $this->cancelarCita($request->id_cita, $request->motivo);
                    return response()->json(['status' => 'success']);
                
                case 'reprogramar_cita':
                    $this->reprogramarCita($request->id_cita, $request->nueva_fecha, $request->nueva_hora, $request->motivo);
                    return response()->json(['status' => 'success']);
                
                default:
                    return response()->json(['status' => 'error', 'message' => 'Operación no válida'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // --- MÉTODOS DE BASE DE DATOS ---

    private function listarCitas($inicio, $fin) {
        $where = ''; $params = [];
        if ($inicio && $fin) { $where = "WHERE DATE(c.FECHA_HORA) BETWEEN ? AND ?"; $params = [$inicio, $fin]; }
        $sql = "SELECT c.ID_CITA AS id_cita, DATE(c.FECHA_HORA) AS fecha_cita, TIME_FORMAT(c.FECHA_HORA, '%H:%i') AS hora_cita, HOUR(c.FECHA_HORA) AS hora_num, ec.NOMBRE_ESTADO AS estado, c.MOTIVO AS tratamiento, CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS) AS paciente, CONCAT(u_odo.NOMBRES, ' ', u_odo.APELLIDOS) AS odontologo, o.ID_ODONTOLOGO AS id_odontologo, p.ID_PACIENTE AS id_paciente, IFNULL(con.NOMBRE, 'Sin asignar') AS consultorio FROM cita c INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE INNER JOIN usuarios u_pac ON p.USUARIOS_ID_USUARIOS = u_pac.ID_USUARIOS INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO INNER JOIN usuarios u_odo ON o.USUARIOS_ID_USUARIOS = u_odo.ID_USUARIOS LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO $where ORDER BY c.FECHA_HORA ASC";
        return DB::select($sql, $params);
    }

    private function listarCitasHoy() {
        $sql = "SELECT c.ID_CITA AS id_cita, u_pac.NUMERO_DOCUMENTO AS documento, DATE(c.FECHA_HORA) AS fecha_cita, TIME_FORMAT(c.FECHA_HORA, '%H:%i') AS hora_cita, ec.NOMBRE_ESTADO AS estado, c.MOTIVO AS tratamiento, CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS) AS paciente, CONCAT(u_odo.NOMBRES, ' ', u_odo.APELLIDOS) AS odontologo, IFNULL(con.NOMBRE, 'Sin asignar') AS consultorio FROM cita c INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE INNER JOIN usuarios u_pac ON p.USUARIOS_ID_USUARIOS = u_pac.ID_USUARIOS INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO INNER JOIN usuarios u_odo ON o.USUARIOS_ID_USUARIOS = u_odo.ID_USUARIOS LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO WHERE DATE(c.FECHA_HORA) = CURDATE() ORDER BY c.FECHA_HORA ASC";
        return DB::select($sql);
    }

    private function listarPacientes() {
        return DB::select("SELECT p.ID_PACIENTE AS id, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS nombre FROM paciente p INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE u.ESTADO = 'Activo' AND u.ROLES_ID_ROLES = 3 ORDER BY u.APELLIDOS, u.NOMBRES");
    }

    private function listarOdontologos() {
        return DB::select("SELECT o.ID_ODONTOLOGO AS id, CONCAT(u.NOMBRES, ' ', u.APELLIDOS, ' — ', e.NOMBRE_ESPECIALIDAD) AS nombre FROM odontologo o INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD WHERE u.ESTADO = 'Activo' AND u.ROLES_ID_ROLES = 2 ORDER BY u.APELLIDOS, u.NOMBRES, e.NOMBRE_ESPECIALIDAD");
    }

    private function listarTratamientos() {
        return DB::select("SELECT ID_PROCEDIMIENTO AS id, NOMBRE_PROCEDIMIENTO AS nombre, COSTO AS costo, TIEMPO_ESTIMADO AS duracion FROM procedimientos WHERE ESTADO = 'ACTIVO' ORDER BY NOMBRE_PROCEDIMIENTO");
    }

    private function obtenerHorasOcupadas($fecha, $id_odontologo = null) {
        if ($id_odontologo) {
            // 1. Horas ya ocupadas por otras citas
            $horasCitas = DB::table('cita')
                ->join('estado_cita', 'cita.ESTADO_CITA_ID', '=', 'estado_cita.ID_ESTADO')
                ->whereDate('FECHA_HORA', $fecha)
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $id_odontologo)
                ->where('NOMBRE_ESTADO', '!=', 'Cancelada')
                ->pluck('FECHA_HORA')
                ->map(function($f) { return date('H:i', strtotime($f)); })
                ->toArray();

            // 2. Revisar el horario de trabajo del odontólogo
            $schedule = DB::table('horario_disponibilidad')
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $id_odontologo)
                ->where('FECHA', $fecha)
                ->where('ESTADO', 'Disponible')
                ->get();

            if ($schedule->isEmpty()) {
                // Si no tiene horario configurado hoy, bloqueamos TODO (de 5 AM a 8 PM)
                $all = [];
                for ($i = 5; $i <= 20; $i++) {
                    $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $all[] = $h . ':00';
                    if ($i < 20) $all[] = $h . ':30';
                }
                return $all;
            }

            // 3. Si sí tiene horario, calculamos qué horas están fuera de su turno (y sus descansos)
            $allowedHours = [];
            foreach ($schedule as $s) {
                $start = (int)date('H', strtotime($s->HORA_INICIO));
                $end = (int)date('H', strtotime($s->HORA_FIN));
                
                // Calculamos el inicio y fin del descanso (si tiene)
                $descanso_start = $s->descanso_inicio ? (int)date('H', strtotime($s->descanso_inicio)) : -1;
                $descanso_end = $s->descanso_fin ? (int)date('H', strtotime($s->descanso_fin)) : -1;

                for ($i = $start; $i < $end; $i++) {
                    // Si la hora choca con el descanso, nos la saltamos
                    if ($i >= $descanso_start && $i < $descanso_end && $descanso_start != -1) {
                        continue;
                    }

                    $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $allowedHours[] = $h . ':00';
                    $allowedHours[] = $h . ':30';
                }
            }

            $todasLasHoras = [];
            for ($i = 5; $i <= 20; $i++) {
                $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                $todasLasHoras[] = $h . ':00';
                if ($i < 20) $todasLasHoras[] = $h . ':30';
            }

            $disabledHours = array_diff($todasLasHoras, $allowedHours);
            
            // Unimos las horas fuera de turno + las horas que ya tienen cita
            return array_values(array_unique(array_merge($disabledHours, $horasCitas)));

        } else {
            return DB::table('cita')
                ->join('estado_cita', 'cita.ESTADO_CITA_ID', '=', 'estado_cita.ID_ESTADO')
                ->whereDate('FECHA_HORA', $fecha)
                ->where('NOMBRE_ESTADO', '!=', 'Cancelada')
                ->pluck('FECHA_HORA')
                ->map(function($f) { return date('H:i', strtotime($f)); })
                ->toArray();
        }
    }

    private function guardarCita($paciente_id, $odontologo_id, $fecha, $hora, $tratamiento) {
        DB::beginTransaction();
        try {
            $fecha_hora = $fecha . ' ' . $hora . ':00';
            
            // Buscar o apartar el bloque en su horario de disponibilidad
            $horario = DB::table('horario_disponibilidad')
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $odontologo_id)
                ->where('FECHA', $fecha)
                ->where('HORA_INICIO', $hora . ':00')
                ->where('ESTADO', 'Disponible')
                ->first();

            if ($horario) {
                $id_horario = $horario->ID_HORARIO;
                DB::table('horario_disponibilidad')->where('ID_HORARIO', $id_horario)->update(['ESTADO' => 'Ocupado']);
            } else {
                $hora_fin = date('H:i:s', strtotime($hora . ':00') + 3600);
                $id_horario = DB::table('horario_disponibilidad')->insertGetId([
                    'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                    'FECHA' => $fecha,
                    'HORA_INICIO' => $hora . ':00',
                    'HORA_FIN' => $hora_fin,
                    'ESTADO' => 'Ocupado'
                ]);
            }

            // Insertar la cita
            $id_cita = DB::table('cita')->insertGetId([
                'PACIENTE_ID_PACIENTE' => $paciente_id,
                'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                'FECHA_HORA' => $fecha_hora,
                'MOTIVO' => $tratamiento,
                'HORARIO_DISPONIBILIDAD_ID_HORARIO' => $id_horario,
                'ESTADO_CITA_ID' => 1
            ]);

            // Enviar notificación automática
            $usr = DB::table('paciente')->where('ID_PACIENTE', $paciente_id)->value('USUARIOS_ID_USUARIOS');
            $fecha_fmt = date('d/m/Y', strtotime($fecha)) . ' a las ' . date('h:i A', strtotime($hora . ':00'));
            DB::table('notificaciones')->insert([
                'MENSAJE' => "El administrador agendó una cita para el $fecha_fmt. Motivo: $tratamiento",
                'FECHA_ENVIO' => now(),
                'ESTADO' => 'NO_LEIDA',
                'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 2,
                'USUARIOS_ID_USUARIOS' => $usr
            ]);

            DB::commit();
            return $id_cita;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function cancelarCita($id_cita, $motivo) {
        DB::table('cita')->where('ID_CITA', $id_cita)->update([
            'ESTADO_CITA_ID' => 3,
            'FECHA_CANCELACION' => now(),
            'MOTIVO_CANCELACION' => $motivo
        ]);
    }

    private function reprogramarCita($id_cita, $nueva_fecha, $nueva_hora, $motivo) {
        $fecha_hora = $nueva_fecha . ' ' . $nueva_hora . ':00';
        DB::table('cita')->where('ID_CITA', $id_cita)->update([
            'FECHA_HORA' => $fecha_hora,
            'ESTADO_CITA_ID' => 1,
            'MOTIVO' => DB::raw("CONCAT(MOTIVO, ' | Reprogramada: $motivo')")
        ]);
    }
}
