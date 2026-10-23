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

    public function cargaMasiva(Request $request)
    {
        if ($request->has('descargar_plantilla')) {
            return $this->descargarPlantillaCsv();
        }

        if ($request->hasFile('archivo_csv')) {
            $file = fopen($request->file('archivo_csv')->getRealPath(), 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);
            $successCount = 0; $errorCount = 0; $erroresDetalle = [];

            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 5) { $errorCount++; continue; }
                $docOdontologo = trim($row[0]);
                if (strtoupper($docOdontologo) === 'DOCUMENTO_ODONTOLOGO') continue;

                $fecha = trim($row[1]);
                $horaInicio = trim($row[2]);
                $horaFin = trim($row[3]);
                $rawJornada = strtoupper(trim($row[4]));
                $jornada = 'Jornada completa';
                if (str_contains($rawJornada, 'MA')) $jornada = 'Mañana';
                elseif (str_contains($rawJornada, 'TAR')) $jornada = 'Tarde';

                try {
                    $odo = DB::selectOne("
                        SELECT o.ID_ODONTOLOGO 
                        FROM odontologo o 
                        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                        WHERE u.NUMERO_DOCUMENTO = ?
                    ", [$docOdontologo]);

                    if (!$odo) {
                        throw new Exception("Odontólogo con documento $docOdontologo no encontrado.");
                    }

                    DB::table('horario_disponibilidad')->insert([
                        'ODONTOLOGO_ID_ODONTOLOGO' => $odo->ID_ODONTOLOGO,
                        'FECHA'                    => $fecha,
                        'HORA_INICIO'              => $horaInicio,
                        'HORA_FIN'                 => $horaFin,
                        'JORNADA'                  => $jornada,
                        'ESTADO'                   => 'Disponible'
                    ]);
                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Documento $docOdontologo ($fecha): " . $e->getMessage();
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
        return response()->streamDownload(function() {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['DOCUMENTO_ODONTOLOGO', 'FECHA', 'HORA_INICIO', 'HORA_FIN', 'JORNADA']);
            fputcsv($output, ['1002003001', date('Y-m-d'), '08:00:00', '17:00:00', 'Jornada completa']);
            fclose($output);
        }, 'plantilla_horarios.csv', [
            'Content-Type' => 'text/csv; charset=utf-8'
        ]);
    }
}
