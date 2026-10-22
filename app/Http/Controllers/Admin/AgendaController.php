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
        \App\Http\Controllers\Paciente\CitaController::actualizarCitasVencidas();
        $where = ''; $params = [];
        if ($inicio && $fin) { $where = "WHERE FECHA BETWEEN ? AND ?"; $params = [$inicio, $fin]; }
        $sql = "SELECT 
                    ID_CITA AS id_cita, 
                    FECHA AS fecha_cita, 
                    TIME_FORMAT(FECHA_HORA, '%H:%i') AS hora_cita, 
                    HOUR(FECHA_HORA) AS hora_num, 
                    ESTADO_CITA AS estado, 
                    MOTIVO AS tratamiento, 
                    NOMBRE_PACIENTE AS paciente, 
                    NOMBRE_ODONTOLOGO AS odontologo, 
                    ODONTOLOGO_ID_ODONTOLOGO AS id_odontologo, 
                    PACIENTE_ID_PACIENTE AS id_paciente, 
                    IFNULL(NOMBRE_CONSULTORIO, 'Sin asignar') AS consultorio 
                FROM vista_citas 
                $where 
                ORDER BY FECHA_HORA ASC";
        return DB::select($sql, $params);
    }

    private function listarCitasHoy() {
        $sql = "SELECT 
                    ID_CITA AS id_cita, 
                    DOC_PACIENTE AS documento, 
                    FECHA AS fecha_cita, 
                    TIME_FORMAT(FECHA_HORA, '%H:%i') AS hora_cita, 
                    ESTADO_CITA AS estado, 
                    MOTIVO AS tratamiento, 
                    NOMBRE_PACIENTE AS paciente, 
                    NOMBRE_ODONTOLOGO AS odontologo, 
                    IFNULL(NOMBRE_CONSULTORIO, 'Sin asignar') AS consultorio 
                FROM vista_citas 
                WHERE FECHA = CURDATE() 
                ORDER BY FECHA_HORA ASC";
        return DB::select($sql);
    }

    private function listarPacientes() {
        return DB::select("SELECT ID_PACIENTE AS id, CONCAT(PRIMER_NOMBRE, ' ', PRIMER_APELLIDO) AS nombre FROM vista_pacientes WHERE ESTADO_USUARIO = 'Activo' ORDER BY PRIMER_APELLIDO, PRIMER_NOMBRE");
    }

    private function listarOdontologos() {
        return DB::select("SELECT ID_ODONTOLOGO AS id, CONCAT(PRIMER_NOMBRE, ' ', PRIMER_APELLIDO, ' — ', NOMBRE_ESPECIALIDAD) AS nombre FROM vista_odontologos WHERE ESTADO_USUARIO = 'Activo' ORDER BY PRIMER_APELLIDO, PRIMER_NOMBRE, NOMBRE_ESPECIALIDAD");
    }

    private function listarTratamientos() {
        return DB::select("SELECT ID_PROCEDIMIENTO AS id, NOMBRE_PROCEDIMIENTO AS nombre, COSTO AS costo, TIEMPO_ESTIMADO AS duracion FROM procedimientos WHERE ESTADO = 'ACTIVO' ORDER BY NOMBRE_PROCEDIMIENTO");
    }

    private function obtenerHorasOcupadas($fecha, $id_odontologo = null) {
        if ($id_odontologo) {
            // 1. Horas ya ocupadas por otras citas
            $horasCitas = DB::table('vista_citas')
                ->where('FECHA', $fecha)
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $id_odontologo)
                ->where('ESTADO_CITA', '!=', 'Cancelada')
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
                $descIni = $s->DESCANSO_INICIO ?? $s->descanso_inicio ?? null;
                $descFin = $s->DESCANSO_FIN ?? $s->descanso_fin ?? null;
                $descanso_start = $descIni ? (int)date('H', strtotime($descIni)) : -1;
                $descanso_end = $descFin ? (int)date('H', strtotime($descFin)) : -1;

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
            return DB::table('vista_citas')
                ->where('FECHA', $fecha)
                ->where('ESTADO_CITA', '!=', 'Cancelada')
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
