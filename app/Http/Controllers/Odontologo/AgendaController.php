<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use DateTime;

class AgendaController extends Controller
{
    private function getOdontologoId()
    {
        $usuarioId = Auth::id() ?? 2;
        $odontologo = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $usuarioId)->first()
                   ?? DB::table('odontologo')->first();
        return $odontologo ? $odontologo->ID_ODONTOLOGO : 1;
    }

    public function index(Request $request)
    {
        if ($request->has('action')) {
            $action = str_replace('odontologo/agenda/', '', $request->input('action'));
            return $this->api($request, $action);
        }
        return view('odontologo.agenda');
    }

    /**
     * Dispatcher unificado para rutas /odontologo/agenda/{op}
     */
    public function api(Request $request, $op = null)
    {
        $op = $op ?: str_replace('odontologo/agenda/', '', $request->input('action', $request->query('action')));
        switch ($op) {
            case 'obtener_citas':
                return $this->obtenerCitas($request);
            case 'obtener_citas_hoy':
                return $this->obtenerCitasHoy($request);
            case 'obtener_citas_mes':
                return $this->obtenerCitasMes($request);
            case 'obtener_horas_ocupadas':
                return $this->obtenerHorasOcupadas($request);
            case 'reprogramar_cita':
                return $this->reprogramarCita($request);
            case 'obtener_lista_procedimientos':
                return $this->obtenerListaProcedimientos($request);
            case 'obtener_detalle_cita':
                return $this->obtenerDetalleCita($request);
            case 'obtener_info_cancelacion':
                return $this->obtenerInfoCancelacion($request);
            case 'buscar_paciente':
                return $this->buscarPaciente($request);
            default:
                return response()->json(['status' => 'error', 'message' => "Operación no válida: $op"], 400);
        }
    }

    public function obtenerCitas(Request $request)
    {
        \App\Http\Controllers\Paciente\CitaController::actualizarCitasVencidas();
        $inicio = $request->query('inicio');
        $fin = $request->query('fin');
        $idOdontologo = $this->getOdontologoId();

        $whereClause = "";
        $params = [];

        if ($inicio && $fin) {
            $whereClause .= " AND DATE(c.FECHA_HORA) BETWEEN ? AND ?";
            $params[] = $inicio;
            $params[] = $fin;
        }

        $params[] = $idOdontologo;

        $sql = "SELECT 
                    ID_CITA AS id_cita, 
                    FECHA AS fecha_cita, 
                    HORA AS hora_cita,
                    HOUR(FECHA_HORA) AS hora_num, 
                    ESTADO_CITA_ID AS estado, 
                    MOTIVO AS tratamiento,
                    NOMBRE_PACIENTE AS paciente, 
                    NOMBRE_ODONTOLOGO AS odontologo
                FROM vista_citas
                WHERE 1=1 $whereClause AND ODONTOLOGO_ID_ODONTOLOGO = ?
                ORDER BY FECHA_HORA ASC";

        $citas = DB::select($sql, $params);
        return response()->json($citas);
    }

    public function obtenerCitasHoy(Request $request)
    {
        $idOdontologo = $this->getOdontologoId();

        $sql = "SELECT 
                    ID_CITA AS id_cita, 
                    DOC_PACIENTE AS documento, 
                    FECHA AS fecha_cita,
                    TIME_FORMAT(FECHA_HORA, '%H:%i') AS hora_cita, 
                    ESTADO_CITA_ID AS estado, 
                    MOTIVO AS tratamiento,
                    NOMBRE_PACIENTE AS paciente
                FROM vista_citas
                WHERE FECHA = CURDATE() AND ODONTOLOGO_ID_ODONTOLOGO = ?
                ORDER BY FECHA_HORA ASC";

        $citas = DB::select($sql, [$idOdontologo]);
        return response()->json($citas);
    }

    public function obtenerCitasMes(Request $request)
    {
        $mes = $request->query('mes', date('m'));
        $anio = $request->query('anio', date('Y'));
        $idOdontologo = $this->getOdontologoId();

        $sql = "SELECT 
                    ID_CITA AS id, 
                    DAY(FECHA_HORA) AS dia, 
                    TIME_FORMAT(FECHA_HORA, '%H:%i') AS hora,
                    ESTADO_CITA_ID AS estado, 
                    MOTIVO AS tratamiento, 
                    NOMBRE_PACIENTE AS nombre
                FROM vista_citas
                WHERE MONTH(FECHA_HORA) = ? AND YEAR(FECHA_HORA) = ? AND ODONTOLOGO_ID_ODONTOLOGO = ?
                ORDER BY FECHA_HORA ASC";

        $citas = DB::select($sql, [$mes, $anio, $idOdontologo]);
        return response()->json($citas);
    }

    public function obtenerHorasOcupadas(Request $request)
    {
        try {
            $fecha = $request->query('fecha');
            if (!$fecha) throw new Exception("Falta el parámetro 'fecha'.");
            $idOdontologo = $this->getOdontologoId();

            $ocupadas = DB::table('cita')
                ->whereDate('FECHA_HORA', $fecha)
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
                ->where('ESTADO_CITA_ID', '!=', 3)
                ->selectRaw("TIME_FORMAT(FECHA_HORA, '%H:%i') as hora")
                ->pluck('hora');

            return response()->json($ocupadas);
        } catch (Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }

    public function reprogramarCita(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();

        if (isset($data['id_cita'], $data['nueva_fecha_hora'])) {
            $id_cita = $data['id_cita'];
            $nueva_fecha_hora = $data['nueva_fecha_hora'];
            $motivo_texto = $data['motivo'] ?? 'Sin motivo específico';
            $nota_reprogramacion = " | Reprogramada: " . $motivo_texto;

            $fecha_formateada = date('d/m/Y a las h:i A', strtotime($nueva_fecha_hora));
            $mensajeNotificacion = "Se reprogramó una cita para el " . $fecha_formateada . ". Motivo: " . $motivo_texto;

            try {
                DB::beginTransaction();

                $citaRow = DB::table('vista_citas')
                    ->where('ID_CITA', $id_cita)
                    ->first();

                $usuario_id = $citaRow ? $citaRow->ID_USUARIO_PACIENTE : 1;

                DB::table('cita')->where('ID_CITA', $id_cita)->update([
                    'FECHA_HORA' => $nueva_fecha_hora,
                    'ESTADO_CITA_ID' => 1,
                    'MOTIVO' => DB::raw("CONCAT(MOTIVO, " . DB::getPdo()->quote($nota_reprogramacion) . ")")
                ]);

                DB::table('notificaciones')->insert([
                    'MENSAJE' => $mensajeNotificacion,
                    'FECHA_ENVIO' => now(),
                    'ESTADO' => 'NO_LEIDA',
                    'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 2,
                    'USUARIOS_ID_USUARIOS' => $usuario_id
                ]);

                DB::commit();
                return response()->json(["status" => "success", "message" => "Cita actualizada y notificación creada"]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
            }
        }

        return response()->json(["status" => "error", "message" => "Faltan datos para actualizar"], 400);
    }

    public function obtenerListaProcedimientos(Request $request)
    {
        $procedimientos = DB::select("
            SELECT ID_PROCEDIMIENTO, NOMBRE_PROCEDIMIENTO, COSTO, TIEMPO_ESTIMADO, TIPO_COBRO 
            FROM procedimientos 
            WHERE UPPER(ESTADO) = 'ACTIVO' OR ESTADO = '1'
        ");
        return response()->json($procedimientos);
    }

    public function obtenerDetalleCita(Request $request)
    {
        $id_cita = (int) $request->query('id', 0);
        if ($id_cita === 0) {
            return response()->json(null);
        }

        $detalle = DB::selectOne("
            SELECT 
                ID_CITA AS id_cita, 
                DOC_PACIENTE AS documento, 
                PACIENTE_ID_PACIENTE AS id_paciente,
                NOMBRE_PACIENTE AS paciente,
                DATE_FORMAT(FECHA_HORA, '%Y-%m-%d') AS fecha_cita,
                TRIM(MOTIVO) AS tratamiento  
            FROM vista_citas
            WHERE ID_CITA = ?
        ", [$id_cita]);

        return response()->json($detalle ?: null);
    }

    public function obtenerInfoCancelacion(Request $request)
    {
        $id_cita = (int) $request->query('id', 0);
        if ($id_cita === 0) {
            return response()->json(['status' => 'error', 'message' => 'ID inválido'], 400);
        }

        $info = DB::selectOne("
            SELECT 'Cancelada por el paciente' as motivo, DATE_FORMAT(FECHA_HORA, '%d/%m/%Y') as fecha 
            FROM cita WHERE ID_CITA = ?
        ", [$id_cita]);

        if ($info) {
            return response()->json(['status' => 'success', 'fecha' => $info->fecha, 'motivo' => $info->motivo]);
        }

        return response()->json(['status' => 'error', 'message' => 'No se encontró información de cancelación'], 404);
    }

    public function buscarPaciente(Request $request)
    {
        $doc = $request->query('doc', '');
        if (!$doc) {
            return response()->json(["encontrado" => false]);
        }

        $paciente = DB::selectOne("
            SELECT ID_PACIENTE, CONCAT(PRIMER_NOMBRE, ' ', PRIMER_APELLIDO) AS nombre 
            FROM vista_pacientes
            WHERE NUMERO_DOCUMENTO = ?
        ", [$doc]);

        if ($paciente) {
            return response()->json([
                "encontrado" => true,
                "ID_PACIENTE" => $paciente->ID_PACIENTE,
                "nombre" => $paciente->nombre
            ]);
        }

        return response()->json(["encontrado" => false]);
    }
}
