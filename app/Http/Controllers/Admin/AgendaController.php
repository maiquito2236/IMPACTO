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
                    $nuevaFecha = $request->nueva_fecha;
                    $nuevaHora  = $request->nueva_hora;
                    if ((!$nuevaFecha || !$nuevaHora) && $request->nueva_fecha_hora) {
                        $ts = strtotime($request->nueva_fecha_hora);
                        if ($ts !== false) {
                            $nuevaFecha = date('Y-m-d', $ts);
                            $nuevaHora  = date('H:i', $ts);
                        }
                    }
                    if (!$request->id_cita || !$nuevaFecha || !$nuevaHora) {
                        throw new Exception("Faltan datos válidos de fecha u hora para reprogramar la cita.");
                    }
                    $this->reprogramarCita($request->id_cita, $nuevaFecha, $nuevaHora, $request->motivo ?? 'Reprogramada desde administrador');
                    return response()->json(['status' => 'success']);
                
                case 'carga_masiva':
                    return $this->cargaMasiva($request);

                case 'descargar_plantilla':
                    return $this->descargarPlantillaCsv();
                
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

            // Enviar notificación automática (web + correo electrónico) al paciente y al odontólogo
            $usr = DB::table('paciente')->where('ID_PACIENTE', $paciente_id)->value('USUARIOS_ID_USUARIOS');
            $fecha_fmt = date('d/m/Y', strtotime($fecha)) . ' a las ' . date('h:i A', strtotime($hora . ':00'));
            \App\Helpers\Notificador::enviarAUsuario(
                $usr,
                1,
                "El administrador agendó tu cita médica para el $fecha_fmt. Motivo: $tratamiento"
            );
            if ($odontologo_id) {
                \App\Helpers\Notificador::enviarAOdontologo(
                    $odontologo_id,
                    21,
                    "El administrador te ha asignado una nueva cita médica para el $fecha_fmt. Motivo: $tratamiento"
                );
            }

            DB::commit();
            return $id_cita;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function cancelarCita($id_cita, $motivo) {
        $cita = DB::table('cita')->where('ID_CITA', $id_cita)->first();
        DB::statement('CALL sp_cancelar_cita(?, ?)', [
            (int) $id_cita,
            mb_substr((string) ($motivo ?? ''), 0, 255)
        ]);

        if ($cita) {
            $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($cita->FECHA_HORA));
            $usr = DB::table('paciente')->where('ID_PACIENTE', $cita->PACIENTE_ID_PACIENTE)->value('USUARIOS_ID_USUARIOS');
            if ($usr) {
                $msg = "Tu cita programada para el $fecha_fmt ha sido cancelada." . (!empty($motivo) ? " Motivo: $motivo" : "");
                \App\Helpers\Notificador::enviarCorreoCita($usr, 7, $msg);
            }
            if ($cita->ODONTOLOGO_ID_ODONTOLOGO) {
                $msgOdo = "La cita programada en tu agenda para el $fecha_fmt ha sido cancelada por administración." . (!empty($motivo) ? " Motivo: $motivo" : "");
                \App\Helpers\Notificador::enviarAOdontologo($cita->ODONTOLOGO_ID_ODONTOLOGO, 8, $msgOdo);
            }
        }
    }

    private function reprogramarCita($id_cita, $nueva_fecha, $nueva_hora, $motivo) {
        $fecha_hora = $nueva_fecha . ' ' . $nueva_hora . ':00';
        $notaRepro = " | Reprogramada: " . ($motivo ?: 'Reprogramada desde administrador');
        DB::table('cita')->where('ID_CITA', $id_cita)->update([
            'FECHA_HORA' => $fecha_hora,
            'ESTADO_CITA_ID' => 1,
            'MOTIVO' => DB::raw("CONCAT(COALESCE(MOTIVO, ''), " . DB::getPdo()->quote($notaRepro) . ")")
        ]);

        $cita = DB::table('cita')->where('ID_CITA', $id_cita)->first();
        if ($cita) {
            $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($fecha_hora));
            $usr = DB::table('paciente')->where('ID_PACIENTE', $cita->PACIENTE_ID_PACIENTE)->value('USUARIOS_ID_USUARIOS');
            if ($usr) {
                \App\Helpers\Notificador::enviarAUsuario(
                    $usr,
                    13,
                    "Tu cita médica ha sido reprogramada para el $fecha_fmt." . (!empty($motivo) ? " Motivo: $motivo" : "")
                );
            }
            if ($cita->ODONTOLOGO_ID_ODONTOLOGO) {
                \App\Helpers\Notificador::enviarAOdontologo(
                    $cita->ODONTOLOGO_ID_ODONTOLOGO,
                    14,
                    "Una cita en tu agenda ha sido reprogramada por administración para el $fecha_fmt." . (!empty($motivo) ? " Motivo: $motivo" : "")
                );
            }
        }
    }

    public function cargaMasiva(Request $request)
    {
        if ($request->has('descargar_plantilla')) {
            return $this->descargarPlantillaCsv();
        }

        if ($request->hasFile('archivo_csv')) {
            $uploaded = $request->file('archivo_csv');
            $ext = strtolower($uploaded->getClientOriginalExtension());
            if (!in_array($ext, ['csv', 'txt'], true) || $uploaded->getSize() > 5 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'El archivo debe ser CSV/TXT y no superar los 5 MB.'], 422);
            }
            $file = fopen($uploaded->getRealPath(), 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);
            $successCount = 0; $errorCount = 0; $erroresDetalle = [];

            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 4) { $errorCount++; continue; }
                $docPaciente = trim($row[0]);
                if (strtoupper($docPaciente) === 'DOCUMENTO_PACIENTE') continue;

                $docOdontologo = trim($row[1]);
                $fechaHora = trim($row[2]);
                $motivo = trim($row[3]);
                $estadoId = isset($row[4]) && trim($row[4]) !== '' ? (int)trim($row[4]) : 1;

                try {
                    $pac = DB::selectOne("
                        SELECT p.ID_PACIENTE 
                        FROM paciente p 
                        JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                        WHERE u.NUMERO_DOCUMENTO = ?
                    ", [$docPaciente]);

                    if (!$pac) {
                        throw new Exception("Paciente con documento $docPaciente no encontrado.");
                    }

                    $odo = DB::selectOne("
                        SELECT o.ID_ODONTOLOGO 
                        FROM odontologo o 
                        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                        WHERE u.NUMERO_DOCUMENTO = ?
                    ", [$docOdontologo]);

                    if (!$odo) {
                        throw new Exception("Odontólogo con documento $docOdontologo no encontrado.");
                    }

                    // Buscar o crear horario de disponibilidad
                    $fechaSolo = date('Y-m-d', strtotime($fechaHora));
                    $horaSolo = date('H:i:s', strtotime($fechaHora));
                    $horaFin = date('H:i:s', strtotime($fechaHora) + 3600);

                    $horario = DB::table('horario_disponibilidad')
                        ->where('ODONTOLOGO_ID_ODONTOLOGO', $odo->ID_ODONTOLOGO)
                        ->where('FECHA', $fechaSolo)
                        ->where('HORA_INICIO', $horaSolo)
                        ->first();

                    if ($horario) {
                        $idHorario = $horario->ID_HORARIO;
                        DB::table('horario_disponibilidad')->where('ID_HORARIO', $idHorario)->update(['ESTADO' => 'Ocupado']);
                    } else {
                        $diasSemana = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
                        $numDia = date('N', strtotime($fechaSolo));
                        $idHorario = DB::table('horario_disponibilidad')->insertGetId([
                            'ODONTOLOGO_ID_ODONTOLOGO' => $odo->ID_ODONTOLOGO,
                            'FECHA' => $fechaSolo,
                            'HORA_INICIO' => $horaSolo,
                            'HORA_FIN' => $horaFin,
                            'DIA' => $diasSemana[$numDia] ?? 'lunes',
                            'ESTADO' => 'Ocupado'
                        ]);
                    }

                    DB::table('cita')->insert([
                        'PACIENTE_ID_PACIENTE'     => $pac->ID_PACIENTE,
                        'ODONTOLOGO_ID_ODONTOLOGO' => $odo->ID_ODONTOLOGO,
                        'FECHA_HORA'               => $fechaHora,
                        'FECHA_ATENCION'           => $fechaHora,
                        'MOTIVO'                   => $motivo,
                        'HORARIO_DISPONIBILIDAD_ID_HORARIO' => $idHorario,
                        'ESTADO_CITA_ID'           => $estadoId,
                        'FECHA_CREACION'           => now()
                    ]);
                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Cita doc $docPaciente: " . $e->getMessage();
                }
            }
            fclose($file);
            return response()->json([
                'success'        => true,
                'successCount'   => $successCount,
                'errorCount'     => $errorCount,
                'erroresDetalle' => $erroresDetalle
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se subió ningún archivo'], 400);
    }

    public function descargarPlantillaCsv()
    {
        $mes = date('Y-m');
        return response()->streamDownload(function() use ($mes) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['DOCUMENTO_PACIENTE', 'PACIENTE', 'DOCUMENTO_ODONTOLOGO', 'ODONTOLOGO', 'FECHA_HORA', 'MOTIVO', 'ESTADO'], ';');

            $citas = DB::select("
                SELECT DOC_PACIENTE, NOMBRE_PACIENTE, 
                       (SELECT uo.NUMERO_DOCUMENTO FROM odontologo o2 JOIN usuarios uo ON o2.USUARIOS_ID_USUARIOS = uo.ID_USUARIOS WHERE o2.ID_ODONTOLOGO = vc.ODONTOLOGO_ID_ODONTOLOGO) AS DOC_ODONTOLOGO,
                       NOMBRE_ODONTOLOGO, FECHA_HORA, MOTIVO, ESTADO_CITA
                FROM vista_citas vc
                WHERE DATE_FORMAT(FECHA_HORA, '%Y-%m') = ?
                GROUP BY ID_CITA
                ORDER BY FECHA_HORA
            ", [$mes]);

            foreach ($citas as $c) {
                fputcsv($output, [
                    $c->DOC_PACIENTE, $c->NOMBRE_PACIENTE, $c->DOC_ODONTOLOGO,
                    $c->NOMBRE_ODONTOLOGO, $c->FECHA_HORA, $c->MOTIVO, $c->ESTADO_CITA
                ], ';');
            }
            fclose($output);
        }, "citas_{$mes}.csv", [
            'Content-Type' => 'text/csv; charset=utf-8'
        ]);
    }
}
