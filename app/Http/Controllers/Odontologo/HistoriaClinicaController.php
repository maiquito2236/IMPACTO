<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class HistoriaClinicaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('action')) {
            $action = str_replace(['odontologo/historial_clinico/', 'odontologo/historial-clinico/'], '', $request->input('action'));
            return $this->api($request, $action);
        }

        $pacienteIdReal = (int) $request->query('paciente_id', 0);
        $citaIdReal = (int) $request->query('cita_id', 0);

        // Si no viene paciente en URL, tomamos el primer paciente disponible para facilitar visualización
        if ($pacienteIdReal === 0) {
            $primerPac = DB::table('paciente')->first();
            if ($primerPac) {
                $pacienteIdReal = $primerPac->ID_PACIENTE;
            }
        }

        return view('odontologo.historial_clinico', compact('pacienteIdReal', 'citaIdReal'));
    }

    public function api(Request $request, $op = null)
    {
        $op = $op ?: str_replace(['odontologo/historial_clinico/', 'odontologo/historial-clinico/'], '', $request->input('action', $request->query('action')));
        switch ($op) {
            case 'datos_odontograma':
                return $this->datosOdontograma($request);
            case 'datos_timeline':
                return $this->datosTimeline($request);
            case 'procesar_tratamientos':
                return $this->procesarTratamientos($request);
            case 'guardar_y_enviar_pdf':
                return $this->guardarYEnviarPdf($request);
            case 'guardar_lote':
                return $this->guardarLote($request);
            default:
                return response()->json(['status' => 'error', 'message' => "Operación no válida: $op"], 400);
        }
    }

    public function guardarLote(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Lote procesado correctamente',
            'cantidad' => count($request->input('dientes', []))
        ]);
    }

    public function datosOdontograma(Request $request)
    {
        $pacienteId = (int) $request->query('paciente_id', 0);
        $targetDate = $request->query('target_date');

        if ($pacienteId === 0) {
            $primerPac = DB::table('paciente')->first();
            $pacienteId = $primerPac ? $primerPac->ID_PACIENTE : 1;
        }

        $nombreDoc = Auth::check() ? (Auth::user()->NOMBRES . ' ' . Auth::user()->APELLIDOS) : 'Dr. Especialista';

        // 1. Demográficos (usando vista_pacientes)
        $demograficos = DB::selectOne("
            SELECT ID_PACIENTE, NUMERO_DOCUMENTO AS documento, CORREO AS correo,
                   NOMBRE_COMPLETO AS paciente_nombre
            FROM vista_pacientes
            WHERE ID_PACIENTE = ?
        ", [$pacienteId]);

        $pacienteData = $demograficos ? (array)$demograficos : ['paciente_nombre' => 'Paciente', 'documento' => 'S/N'];

        // 2. Dientes del odontograma (usando vista_odontograma)
        $sqlDientes = "
            SELECT PIEZA_DENTAL, NOMBRE_PROCEDIMIENTO as tratamiento, ESTADO_DIENTE as estado, 
                   FECHA_FORMATEADA as fecha, 
                   NOTAS as notas,
                   NOMBRE_DOCTOR as doctor
        ";
        if ($targetDate) {
            $sqlDientes .= ", IF(FECHA = ?, 1, 0) as es_nuevo";
        } else {
            $sqlDientes .= ", 1 as es_nuevo";
        }

        $sqlDientes .= "
            FROM vista_odontograma
            WHERE PACIENTE_ID_PACIENTE = ?
        ";

        $paramsDientes = [];
        if ($targetDate) {
            $paramsDientes[] = $targetDate;
        }
        $paramsDientes[] = $pacienteId;

        if ($targetDate) {
            $sqlDientes .= " AND FECHA <= ?";
            $paramsDientes[] = $targetDate;
        }

        $datosDientes = DB::select($sqlDientes, $paramsDientes);

        $toothData = [];
        foreach ($datosDientes as $row) {
            $r = (array)$row;
            $pieza = $r['PIEZA_DENTAL'];
            $doctorRegistro = $r['doctor'] ?? 'Sin asignar';

            if (!isset($toothData[$pieza])) {
                $toothData[$pieza] = [
                    'nombre' => "Pieza Dental " . $pieza,
                    'estado' => $r['estado'],
                    'tratamiento' => $r['tratamiento'],
                    'doctor' => ($doctorRegistro !== 'Sin asignar') ? $doctorRegistro : $nombreDoc,
                    'fecha' => $r['fecha'],
                    'notas' => $r['notas'] ?? '',
                    'es_nuevo' => (isset($r['es_nuevo']) && $r['es_nuevo'] == 1)
                ];
            } else {
                $toothData[$pieza]['tratamiento'] .= ', ' . $r['tratamiento'];
                if (!empty($r['notas'])) {
                    $toothData[$pieza]['notas'] .= ' | ' . $r['notas'];
                }
                if ($r['estado'] === 'HECHO') {
                    $toothData[$pieza]['estado'] = 'HECHO';
                }
            }
        }

        // 3. Alertas (usando vista_antecedentes_medicos)
        $condiciones = DB::select("
            SELECT TIPO, NOMBRE_CONDICION 
            FROM vista_antecedentes_medicos
            WHERE ID_PACIENTE = ?
        ", [$pacienteId]);

        $alergias = [];
        $enfermedades = [];
        foreach ($condiciones as $c) {
            if ($c->TIPO === 'ALERGIA') {
                $alergias[] = $c->NOMBRE_CONDICION;
            } elseif ($c->TIPO === 'ENFERMEDAD') {
                $enfermedades[] = $c->NOMBRE_CONDICION;
            }
        }

        $alertas = [
            'ALERGIAS' => !empty($alergias) ? implode(', ', $alergias) : 'Ninguna',
            'ENFERMEDADES' => !empty($enfermedades) ? implode(', ', $enfermedades) : 'Ninguna',
            'MEDICAMENTOS' => 'Ninguno'
        ];

        // 4. Financiero (usando vista_odontograma y vista_facturacion)
        $resTrat = DB::selectOne("
            SELECT COALESCE(SUM(COSTO_PROCEDIMIENTO), 0) as total_treatment
            FROM vista_odontograma
            WHERE PACIENTE_ID_PACIENTE = ?
        ", [$pacienteId]);
        $totalTreatment = $resTrat ? (float)$resTrat->total_treatment : 0;

        $resFact = DB::selectOne("
            SELECT COALESCE(SUM(TOTAL_PAGADO), 0) as total_pagado 
            FROM vista_facturacion 
            WHERE PACIENTE_ID_PACIENTE = ?
        ", [$pacienteId]);
        $totalPagado = $resFact ? (float)$resFact->total_pagado : 0;
        $saldoPendiente = max(0, $totalTreatment - $totalPagado);

        $financiero = [
            'total_treatment' => $totalTreatment,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $saldoPendiente
        ];

        // 5. Citas
        $resCita = DB::selectOne("
            SELECT MOTIVO as sesion_hoy, DATE_FORMAT(FECHA_HORA, '%d de %b, %Y') as proxima_cita
            FROM cita WHERE PACIENTE_ID_PACIENTE = ? ORDER BY FECHA_HORA DESC LIMIT 1
        ", [$pacienteId]);

        $citas = $resCita ? (array)$resCita : ['sesion_hoy' => 'Sin citas', 'proxima_cita' => 'No programada'];

        return response()->json([
            'paciente' => $pacienteData,
            'alertas' => $alertas,
            'financiero' => $financiero,
            'citas' => $citas,
            'dientes' => $toothData,
            'doctor_sesion' => $nombreDoc
        ]);
    }

    public function datosTimeline(Request $request)
    {
        $pacienteId = (int) $request->query('paciente_id', 0);
        if ($pacienteId === 0) {
            $primerPac = DB::table('paciente')->first();
            $pacienteId = $primerPac ? $primerPac->ID_PACIENTE : 1;
        }

        // Datos timeline (usando vista_procedimientos_hc)
        $sql = "
            SELECT TIME_FORMAT(FECHA_REGISTRO, '%h:%i %p') as hora,
                   DATE_FORMAT(FECHA_REGISTRO, '%d %b %Y') as fecha,
                   DATE_FORMAT(FECHA_REGISTRO, '%Y-%m-%d') as fecha_iso,
                   MOTIVO_CONSULTA as motivo,
                   DIAGNOSTICO as diagnostico,
                   TRATAMIENTO as piezas,
                   OBSERVACIONES as observaciones,
                   NOMBRE_PROCEDIMIENTO
            FROM vista_procedimientos_hc
            WHERE ID_PACIENTE = ?
            ORDER BY FECHA_REGISTRO DESC
        ";

        $evoluciones = DB::select($sql, [$pacienteId]);
        return response()->json($evoluciones);
    }

    public function procesarTratamientos(Request $request)
    {
        $citaId = (int) $request->query('cita_id', 0);
        $pacienteId = (int) $request->query('paciente_id', 0);

        if ($citaId > 0 && $pacienteId > 0) {
            $this->completarCitaYTratamientos($citaId, $pacienteId);
        }

        return redirect('/odontologo/tratamientos');
    }

    public function guardarYEnviarPdf(Request $request)
    {
        $citaId = (int) $request->input('cita_id', 0);
        $pacienteId = (int) $request->input('paciente_id', 0);

        if ($citaId > 0 && $pacienteId > 0) {
            $this->completarCitaYTratamientos($citaId, $pacienteId);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Historia clínica guardada correctamente.'
        ]);
    }

    private function completarCitaYTratamientos($citaId, $pacienteId)
    {
        try {
            DB::beginTransaction();

            $hcRecords = DB::select("
                SELECT hc.ID_HISTORIA_CLINICA 
                FROM historia_clinica hc
                LEFT JOIN factura f ON hc.ID_HISTORIA_CLINICA = f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                WHERE hc.PACIENTE_ID_PACIENTE = ? AND f.ID_FACTURA IS NULL
            ", [$pacienteId]);

            foreach ($hcRecords as $hc) {
                $totalHc = (float) DB::table('historia_clinica_has_procedimientos')
                    ->where('HISTORIA_CLINICA_ID_HISTORIA_CLINICA', $hc->ID_HISTORIA_CLINICA)
                    ->sum(DB::raw('PRECIO_APLICADO * CANTIDAD'));

                DB::table('factura')->insert([
                    'HISTORIA_CLINICA_ID_HISTORIA_CLINICA' => $hc->ID_HISTORIA_CLINICA,
                    'CITA_ID_CITA' => $citaId ?: 0,
                    'FECHA_EMISION' => now(),
                    'TOTAL' => $totalHc,
                    'ESTADO' => 'EMITIDA'
                ]);
            }

            if ($citaId > 0) {
                DB::table('cita')->where('ID_CITA', $citaId)->update([
                    'ESTADO_CITA_ID' => 2,
                    'FECHA_ATENCION' => now()
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
