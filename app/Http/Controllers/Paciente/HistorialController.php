<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistorialController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        $citas = [];
        if ($idPaciente) {
            $citas = DB::select("
                SELECT 
                    ID_CITA,
                    FECHA_HORA,
                    MOTIVO,
                    ESTADO_CITA_ID,
                    ESTADO_CITA AS NOMBRE_ESTADO,
                    NOMBRE_ODONTOLOGO AS DOCTOR_NOMBRE,
                    NOMBRE_ESPECIALIDAD,
                    OBSERVACIONES_DOCTOR,
                    RECOMENDACIONES,
                    FECHA_ATENCION
                FROM vista_citas
                WHERE PACIENTE_ID_PACIENTE = ?
                ORDER BY FECHA_HORA DESC
            ", [$idPaciente]);

            $citas = json_decode(json_encode($citas), true);
        }

        return view('paciente.historial', compact('citas', 'idPaciente'));
    }

    public function detalleHistorialAjax(Request $request)
    {
        $idCita = intval($request->input('id_cita'));

        $detalle = DB::selectOne("
            SELECT 
                ID_CITA,
                FECHA_HORA,
                MOTIVO,
                OBSERVACIONES_DOCTOR,
                RECOMENDACIONES,
                FECHA_ATENCION,
                ESTADO_CITA AS NOMBRE_ESTADO,
                NOMBRE_ODONTOLOGO AS DOCTOR_NOMBRE,
                NOMBRE_ESPECIALIDAD,
                NOMBRE_CONSULTORIO AS CONSULTORIO_NOMBRE,
                NOMBRE_PACIENTE AS PACIENTE_NOMBRE,
                DOC_PACIENTE AS PACIENTE_DOCUMENTO
            FROM vista_citas
            WHERE ID_CITA = ?
        ", [$idCita]);

        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'No se encontraron detalles clínicos.']);
        }

        $res = (array) $detalle;
        if (!empty($res['FECHA_HORA'])) {
            $ts = strtotime($res['FECHA_HORA']);
            $res['SOLO_FECHA'] = date('d/m/Y', $ts);
            $res['SOLO_HORA']  = date('h:i A', $ts);
        }

        return response()->json(['success' => true, 'data' => $res]);
    }

    public function datosOdontograma(Request $request)
    {
        $idPaciente = intval($request->input('paciente_id') ?? session('paciente_id'));

        $hallazgos = DB::select("
            SELECT 
                o.DIENTE,
                o.CARA,
                o.ESTADO,
                o.OBSERVACIONES,
                o.FECHA_REGISTRO
            FROM odontograma o
            WHERE o.PACIENTE_ID_PACIENTE = ?
            ORDER BY o.FECHA_REGISTRO DESC
        ", [$idPaciente]);

        return response()->json([
            'success' => true,
            'data'    => $hallazgos
        ]);
    }
}