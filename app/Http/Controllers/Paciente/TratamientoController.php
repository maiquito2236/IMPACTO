<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TratamientoController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        $citas = [];
        $estadisticas = [
            'consultas'           => 0,
            'tratamientos'        => 0,
            'diagnosticos'        => 0,
            'ultima_fecha'        => '--',
            'ultima_especialidad' => '--'
        ];

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

            // Estadísticas
            $totalConsultas = count($citas);
            $totalTratamientos = DB::table('historia_clinica')->where('PACIENTE_ID_PACIENTE', $idPaciente)->count();
            $ultimaCita = !empty($citas) ? $citas[0] : null;

            $estadisticas = [
                'consultas'           => $totalConsultas,
                'tratamientos'        => $totalTratamientos,
                'diagnosticos'        => $totalTratamientos,
                'ultima_fecha'        => $ultimaCita ? date('d/m/Y', strtotime($ultimaCita['FECHA_HORA'])) : '--',
                'ultima_especialidad' => $ultimaCita ? ($ultimaCita['NOMBRE_ESPECIALIDAD'] ?? 'General') : '--'
            ];
        }

        return view('paciente.tratamientos', compact('citas', 'estadisticas'));
    }
}