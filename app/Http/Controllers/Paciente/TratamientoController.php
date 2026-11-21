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

            // Precarga en una sola consulta para evitar N+1
            $procsAll = DB::table('historia_clinica as hc')
                ->join('historia_clinica_has_procedimientos as hcp', 'hc.ID_HISTORIA_CLINICA', '=', 'hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA')
                ->join('procedimientos as p', 'hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO', '=', 'p.ID_PROCEDIMIENTO')
                ->whereIn('hc.PACIENTE_ID_PACIENTE', $pacientesIds)
                ->select('hc.CITA_ID_CITA', 'hc.PACIENTE_ID_PACIENTE', DB::raw('DATE(hc.FECHA_REGISTRO) as fecha_reg'), 'p.NOMBRE_PROCEDIMIENTO')
                ->get();

            $procsPorCita = [];
            $procsPorPacFecha = [];
            foreach ($procsAll as $pr) {
                if (!empty($pr->CITA_ID_CITA)) {
                    $procsPorCita[$pr->CITA_ID_CITA][] = $pr->NOMBRE_PROCEDIMIENTO;
                }
                $keyPf = $pr->PACIENTE_ID_PACIENTE . '_' . $pr->fecha_reg;
                $procsPorPacFecha[$keyPf][] = $pr->NOMBRE_PROCEDIMIENTO;
            }

            foreach ($citas as &$c) {
                $procsRealizados = [];
                if (!empty($c['ID_CITA']) && isset($procsPorCita[$c['ID_CITA']])) {
                    $procsRealizados = array_merge($procsRealizados, $procsPorCita[$c['ID_CITA']]);
                }
                $fechaCita = !empty($c['FECHA_HORA']) ? date('Y-m-d', strtotime($c['FECHA_HORA'])) : '';
                $keyPf = $c['PACIENTE_ID_PACIENTE'] . '_' . $fechaCita;
                if ($fechaCita && isset($procsPorPacFecha[$keyPf])) {
                    $procsRealizados = array_merge($procsRealizados, $procsPorPacFecha[$keyPf]);
                }
                $procsRealizados = array_values(array_unique($procsRealizados));

                if (!empty($procsRealizados)) {
                    $c['NOMBRE_PROCEDIMIENTO'] = implode(', ', $procsRealizados);
                }
            }
            unset($c);

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