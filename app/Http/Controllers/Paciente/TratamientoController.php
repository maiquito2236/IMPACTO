<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TratamientoController extends Controller
{
    /**
     * Muestra la línea de tiempo de tratamientos y evoluciones clínicas
     */
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        // Pacientes autorizados: el titular + menores a su cargo
        $pacientesIds = [];
        if ($idPaciente) {
            $pacientesIds[] = $idPaciente;
        }
        $hijosIds = DB::table('paciente_has_acudiente')
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->pluck('PACIENTE_ID_PACIENTE')
            ->toArray();
        $pacientesIds = array_unique(array_merge($pacientesIds, $hijosIds));

        $citas = [];
        $estadisticas = [
            'consultas'           => 0,
            'tratamientos'        => 0,
            'diagnosticos'        => 0,
            'ultima_fecha'        => '--',
            'ultima_especialidad' => '--'
        ];

        if (!empty($pacientesIds)) {
            $placeholders = implode(',', array_fill(0, count($pacientesIds), '?'));
            $citas = DB::select("
                SELECT 
                    ID_CITA,
                    PACIENTE_ID_PACIENTE,
                    NOMBRE_PACIENTE,
                    DOC_PACIENTE,
                    ODONTOLOGO_ID_ODONTOLOGO,
                    FECHA_HORA,
                    MOTIVO,
                    ESTADO_CITA_ID,
                    ESTADO_CITA,
                    NOMBRE_ODONTOLOGO,
                    NOMBRE_ESPECIALIDAD,
                    NOMBRE_PROCEDIMIENTO,
                    NOMBRE_CONSULTORIO,
                    OBSERVACIONES_DOCTOR,
                    RECOMENDACIONES,
                    FECHA_ATENCION
                FROM vista_citas
                WHERE PACIENTE_ID_PACIENTE IN ($placeholders)
                ORDER BY FECHA_HORA DESC
            ", $pacientesIds);

            $citas = json_decode(json_encode($citas), true);

            // Estadísticas
            $totalConsultas = count($citas);
            $totalTratamientos = DB::table('historia_clinica')->whereIn('PACIENTE_ID_PACIENTE', $pacientesIds)->count();
            if ($totalTratamientos === 0) {
                $totalTratamientos = count(array_filter($citas, fn($c) => !empty($c['NOMBRE_PROCEDIMIENTO'])));
            }
            $totalDiagnosticos = count(array_filter($citas, fn($c) => !empty($c['OBSERVACIONES_DOCTOR']) || !empty($c['RECOMENDACIONES'])));

            $ultimaCita = !empty($citas) ? $citas[0] : null;

            $estadisticas = [
                'consultas'           => $totalConsultas,
                'tratamientos'        => $totalTratamientos,
                'diagnosticos'        => $totalDiagnosticos > 0 ? $totalDiagnosticos : $totalConsultas,
                'ultima_fecha'        => $ultimaCita ? date('d/m/Y', strtotime($ultimaCita['FECHA_HORA'])) : '--',
                'ultima_especialidad' => $ultimaCita ? (!empty($ultimaCita['NOMBRE_ESPECIALIDAD']) ? $ultimaCita['NOMBRE_ESPECIALIDAD'] : 'General') : '--'
            ];
        }

        return view('paciente.tratamientos', compact('citas', 'estadisticas'));
    }
}