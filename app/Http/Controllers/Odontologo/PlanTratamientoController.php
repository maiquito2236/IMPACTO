<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PlanTratamientoController extends Controller
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
            $action = str_replace(['odontologo/planes/', 'odontologo/planes-tratamiento/', 'odontologo/planes_tratamiento/'], '', $request->input('action'));
            return $this->api($request, $action);
        }

        $id_cita = (int) $request->query('id_cita', 0);
        $paciente_id_param = (int) $request->query('paciente_id', 0);

        $paciente_nombre = "Ningún paciente seleccionado";
        $id_paciente = "";
        $documento = "";
        $tratamiento_motivo = "";
        $procedimientos_paciente = [];
        $total_presupuesto = 0.00;
        $es_menor = false;
        $edad_paciente = null;

        if ($id_cita > 0) {
            $detalleCita = DB::table('vista_citas')
                ->where('ID_CITA', $id_cita)
                ->first();

            if ($detalleCita) {
                $id_paciente        = $detalleCita->PACIENTE_ID_PACIENTE;
                $paciente_nombre    = $detalleCita->NOMBRE_PACIENTE;
                $documento          = $detalleCita->DOC_PACIENTE;
                $tratamiento_motivo = trim($detalleCita->MOTIVO) ?: 'Consulta';
            }
        } elseif ($paciente_id_param > 0) {
            $pac = DB::table('vista_pacientes')
                ->where('ID_PACIENTE', $paciente_id_param)
                ->first();
            if ($pac) {
                $id_paciente = $pac->ID_PACIENTE;
                $paciente_nombre = $pac->NOMBRE_COMPLETO;
                $documento = $pac->NUMERO_DOCUMENTO;
                $tratamiento_motivo = 'Plan de tratamiento general';
                $es_menor = !empty($pac->ES_MENOR);
                $edad_paciente = $pac->EDAD ?? null;
            }
        }

        if (!empty($id_paciente) && $edad_paciente === null) {
            $pacInfo = DB::table('vista_pacientes')
                ->select('EDAD', 'ES_MENOR')
                ->where('ID_PACIENTE', $id_paciente)
                ->first();
            if ($pacInfo) {
                $es_menor = !empty($pacInfo->ES_MENOR);
                $edad_paciente = $pacInfo->EDAD ?? null;
            }
        }

        $lista_pacientes = DB::table('vista_pacientes')
            ->select('ID_PACIENTE', 'NUMERO_DOCUMENTO', 'NOMBRE_COMPLETO')
            ->orderBy('NOMBRE_COMPLETO', 'asc')
            ->get();

        if (!empty($id_paciente)) {
            $odontologo_id = $this->getOdontologoId();

            $sqlHist = "
                SELECT 
                    HISTORIA_CLINICA_ID_HISTORIA_CLINICA,
                    FECHA_REGISTRO,
                    PROCEDIMIENTOS_ID_PROCEDIMIENTO,
                    NOMBRE_PROCEDIMIENTO,
                    IF(TIEMPO_ESTIMADO_PROCEDIMIENTO > 40, 'EVOLUCION_FASES', 'SESION_UNICA') AS TIPO_SEGUIMIENTO,
                    PRECIO_APLICADO,
                    CANTIDAD,
                    IF(DIAGNOSTICO LIKE '%Piezas: %', SUBSTRING_INDEX(SUBSTRING_INDEX(DIAGNOSTICO, 'Piezas: ', -1), ' |', 1), IF(DIAGNOSTICO LIKE '%Pieza %', SUBSTRING_INDEX(SUBSTRING_INDEX(DIAGNOSTICO, 'Pieza ', -1), ' ', 1), 'Gral')) AS PIEZA_DENTAL,
                    DIAGNOSTICO AS NOTAS,
                    IF(PRECIO_APLICADO > 0, 'Hecho', 'Pendiente') AS ESTADO_FASE
                FROM vista_procedimientos_hc
                WHERE ID_PACIENTE = ?
                ORDER BY HISTORIA_CLINICA_ID_HISTORIA_CLINICA ASC
            ";

            $rawHist = DB::select($sqlHist, [$id_paciente]);
            $procedimientos_paciente = array_map(fn($item) => (array)$item, $rawHist);

            foreach ($procedimientos_paciente as $proc) {
                if (($proc['TIPO_SEGUIMIENTO'] ?? '') === 'EVOLUCION_FASES' && strtolower($proc['ESTADO_FASE'] ?? '') !== 'hecho') {
                    $total_presupuesto += (float) ($proc['PRECIO_APLICADO'] * ($proc['CANTIDAD'] ?? 1));
                }
            }
        }

        $cleanUtf8 = function ($item) use (&$cleanUtf8) {
            if (is_array($item)) {
                return array_map($cleanUtf8, $item);
            }
            if (is_object($item)) {
                foreach ($item as $k => $v) {
                    $item->$k = $cleanUtf8($v);
                }
                return $item;
            }
            if (is_string($item) && strpos($item, 'Ã') !== false) {
                return mb_convert_encoding($item, 'ISO-8859-1', 'UTF-8');
            }
            return $item;
        };

        $paciente_nombre = $cleanUtf8($paciente_nombre);
        $tratamiento_motivo = $cleanUtf8($tratamiento_motivo);
        $procedimientos_paciente = array_map($cleanUtf8, $procedimientos_paciente);
        $lista_pacientes = $lista_pacientes->map(fn($p) => $cleanUtf8($p));

        return view('odontologo.planes', compact(
            'id_cita',
            'paciente_nombre',
            'id_paciente',
            'documento',
            'tratamiento_motivo',
            'procedimientos_paciente',
            'total_presupuesto',
            'lista_pacientes',
            'es_menor',
            'edad_paciente'
        ));
    }

    public function api(Request $request, $op = null)
    {
        $op = $op ?: str_replace(['odontologo/planes/', 'odontologo/planes-tratamiento/', 'odontologo/planes_tratamiento/'], '', $request->input('action', $request->query('action')));
        switch ($op) {
            case 'listar':
            case 'listarPiezas':
                return $this->listarPiezas($request);
            case 'guardar':
            case 'guardarPlan':
                return $this->guardarPlan($request);
            case 'guardarMasivo':
                return $this->guardarMasivo($request);
            default:
                return response()->json(['status' => 'error', 'message' => "Operación no válida: $op"], 400);
        }
    }

    public function listarPiezas(Request $request)
    {
        $id_paciente = (int) $request->query('id', 0);
        if ($id_paciente <= 0) {
            return response()->json([]);
        }

        $piezas = DB::table('odontograma_tratamientos')
            ->where('PACIENTE_ID_PACIENTE', $id_paciente)
            ->distinct()
            ->pluck('PIEZA_DENTAL');

        return response()->json(array_values($piezas->toArray()));
    }

    public function guardarPlan(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();

        if (isset($data['paciente_id'], $data['fecha'], $data['pieza'], $data['procedimiento_id'])) {
            try {
                $odontologo_id = $this->getOdontologoId();

                DB::table('odontograma_tratamientos')->insert([
                    'PACIENTE_ID_PACIENTE' => $data['paciente_id'],
                    'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                    'PIEZA_DENTAL' => $data['pieza'],
                    'PROCEDIMIENTO_ID_PROCEDIMIENTO' => $data['procedimiento_id'],
                    'FECHA_REGISTRO' => $data['fecha'],
                    'NOTAS' => $data['notas'] ?? '',
                    'ESTADO' => 'HECHO'
                ]);

                return response()->json(['success' => true, 'mensaje' => 'Tratamiento registrado correctamente']);
            } catch (Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'error' => 'Faltan datos obligatorios para guardar'], 400);
    }

    public function guardarMasivo(Request $request)
    {
        $input = $request->json()->all() ?: $request->all();

        $paciente_id         = (int) ($input['paciente_id'] ?? 0);
        $cita_id             = (int) ($input['cita_id'] ?? 0);
        $diagnostico_general = trim($input['diagnostico_general'] ?? '');
        $recomendacion        = trim($input['recomendacion'] ?? '');
        $fecha               = isset($input['fecha']) ? (strlen($input['fecha']) > 10 ? $input['fecha'] : $input['fecha'] . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');
        $fechaSoloDia        = date('Y-m-d', strtotime($fecha));

        // Normalizar los procedimientos: puede recibirse como array en 'procedimientos' o en formato individual
        $procedimientosList = [];
        if (isset($input['procedimientos']) && is_array($input['procedimientos'])) {
            $procedimientosList = $input['procedimientos'];
        } elseif (isset($input['procedimiento_id'])) {
            $procedimientosList = [$input];
        }

        if ($paciente_id <= 0 || empty($procedimientosList)) {
            return response()->json(['success' => false, 'error' => 'Datos obligatorios incompletos o lista de procedimientos vacía.'], 400);
        }

        $odontologo_id = $this->getOdontologoId();

        try {
            DB::beginTransaction();

            $totalPresupuestoTotal = 0.0;
            $nombresProcsGuardados = [];
            $facturasCreadas = [];

            foreach ($procedimientosList as $itemProc) {
                $procedimiento_id    = (int) ($itemProc['procedimiento_id'] ?? 0);
                $precio_aplicado     = (float) ($itemProc['precio_aplicado'] ?? 0.00);
                $notas               = trim($itemProc['notas'] ?? ($input['notas'] ?? ''));
                $piezasSeleccionadas = $itemProc['piezas'] ?? [];

                if ($procedimiento_id <= 0) {
                    continue;
                }

                $proc = DB::table('procedimientos')->where('ID_PROCEDIMIENTO', $procedimiento_id)->first();
                if (!$proc) {
                    throw new Exception("El procedimiento (ID: {$procedimiento_id}) no existe en el catálogo.");
                }

                $nombreProc = $proc->NOMBRE_PROCEDIMIENTO;
                $nombresProcsGuardados[] = $nombreProc;
                $tipoCobro  = (int) ($itemProc['tipoCobro'] ?? ($proc->TIPO_COBRO ?? 1));

                // Si es general por catálogo, por frontend o por naturaleza clínica
                if ($tipoCobro === 2 || $this->esProcedimientoGeneral($nombreProc)) {
                    $tipoCobro = 2;
                }

                $ambito = ($tipoCobro === 2 || empty($piezasSeleccionadas)) ? 'BOCA_COMPLETA' : 'POR_DIENTE';
                $precioParaFactura = $precio_aplicado > 0 ? $precio_aplicado : (float) ($proc->COSTO ?? 0);

                if ($ambito === 'BOCA_COMPLETA') {
                    $motivo = $nombreProc;
                    $notasBase = !empty($piezasSeleccionadas) ? "Piezas: " . implode(', ', $piezasSeleccionadas) . " | " . $notas : $notas;
                    $notasFinales = (!empty($diagnostico_general) ? $diagnostico_general . " | " : "") . $notasBase;

                    $id_historia_clinica = DB::table('historia_clinica')->insertGetId([
                        'FECHA_REGISTRO' => $fecha,
                        'MOTIVO_CONSULTA' => $motivo,
                        'DIAGNOSTICO' => $notasFinales,
                        'TRATAMIENTO' => $nombreProc,
                        'PACIENTE_ID_PACIENTE' => $paciente_id,
                        'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                        'CITA_ID_CITA' => $cita_id > 0 ? $cita_id : null
                    ]);

                    DB::statement('CALL sp_registrar_atencion_hc(?, ?, ?, ?, ?, @nuevo_reg_hc)', [
                        $id_historia_clinica,
                        $procedimiento_id,
                        1,
                        $precioParaFactura,
                        mb_substr($notas, 0, 200)
                    ]);

                    // Emitir factura automáticamente para este tratamiento
                    $idFacturaCreada = DB::table('factura')->insertGetId([
                        'HISTORIA_CLINICA_ID_HISTORIA_CLINICA' => $id_historia_clinica,
                        'CITA_ID_CITA' => $cita_id > 0 ? $cita_id : 0,
                        'FECHA_EMISION' => now(),
                        'TOTAL' => $precioParaFactura,
                        'ESTADO' => 'EMITIDA',
                        'PACIENTE_ID_PACIENTE' => $paciente_id
                    ]);
                    $facturasCreadas[] = $idFacturaCreada;

                    $totalPresupuestoTotal += $precioParaFactura;

                    if (!empty($piezasSeleccionadas)) {
                        foreach ($piezasSeleccionadas as $numPieza) {
                            $piezaInt = intval($numPieza);
                            DB::table('odontograma_tratamientos')->insert([
                                'PACIENTE_ID_PACIENTE' => $paciente_id,
                                'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                                'PIEZA_DENTAL' => $piezaInt,
                                'PROCEDIMIENTO_ID_PROCEDIMIENTO' => $procedimiento_id,
                                'FECHA_REGISTRO' => $fechaSoloDia,
                                'NOTAS' => $notas,
                                'ESTADO' => 'HECHO'
                            ]);
                        }
                    } else {
                        DB::table('odontograma_tratamientos')->insert([
                            'PACIENTE_ID_PACIENTE' => $paciente_id,
                            'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                            'PIEZA_DENTAL' => 0,
                            'PROCEDIMIENTO_ID_PROCEDIMIENTO' => $procedimiento_id,
                            'FECHA_REGISTRO' => $fechaSoloDia,
                            'NOTAS' => $notas,
                            'ESTADO' => 'HECHO'
                        ]);
                    }
                } else {
                    foreach ($piezasSeleccionadas as $numPieza) {
                        $piezaInt = intval($numPieza);
                        $motivo_defecto = $nombreProc;
                        $notasBase = "Pieza " . $piezaInt . " | " . $notas;
                        $notasFinales = (!empty($diagnostico_general) ? $diagnostico_general . " | " : "") . $notasBase;

                        $id_historia_clinica = DB::table('historia_clinica')->insertGetId([
                            'FECHA_REGISTRO' => $fecha,
                            'MOTIVO_CONSULTA' => $motivo_defecto,
                            'DIAGNOSTICO' => $notasFinales,
                            'TRATAMIENTO' => $nombreProc,
                            'PACIENTE_ID_PACIENTE' => $paciente_id,
                            'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                            'CITA_ID_CITA' => $cita_id > 0 ? $cita_id : null
                        ]);

                        DB::statement('CALL sp_registrar_atencion_hc(?, ?, ?, ?, ?, @nuevo_reg_hc)', [
                            $id_historia_clinica,
                            $procedimiento_id,
                            1,
                            $precioParaFactura,
                            mb_substr($notas, 0, 200)
                        ]);

                        // Emitir factura automáticamente para este tratamiento
                        $idFacturaCreada = DB::table('factura')->insertGetId([
                            'HISTORIA_CLINICA_ID_HISTORIA_CLINICA' => $id_historia_clinica,
                            'CITA_ID_CITA' => $cita_id > 0 ? $cita_id : 0,
                            'FECHA_EMISION' => now(),
                            'TOTAL' => $precioParaFactura,
                            'ESTADO' => 'EMITIDA',
                            'PACIENTE_ID_PACIENTE' => $paciente_id
                        ]);
                        $facturasCreadas[] = $idFacturaCreada;

                        $totalPresupuestoTotal += $precioParaFactura;

                        DB::table('odontograma_tratamientos')->insert([
                            'PACIENTE_ID_PACIENTE' => $paciente_id,
                            'ODONTOLOGO_ID_ODONTOLOGO' => $odontologo_id,
                            'PIEZA_DENTAL' => $piezaInt,
                            'PROCEDIMIENTO_ID_PROCEDIMIENTO' => $procedimiento_id,
                            'FECHA_REGISTRO' => $fechaSoloDia,
                            'NOTAS' => $notas,
                            'ESTADO' => 'HECHO'
                        ]);
                    }
                }
            }

            // Actualizar cita con recomendaciones, observaciones y marcarla como completada (UNA SOLA VEZ)
            if ($cita_id > 0) {
                $citaUpdate = [
                    'ESTADO_CITA_ID' => 2,
                    'FECHA_ATENCION' => now()
                ];
                if (!empty($recomendacion)) {
                    $citaUpdate['RECOMENDACIONES'] = $recomendacion;
                }
                if (!empty($diagnostico_general)) {
                    $citaUpdate['OBSERVACIONES_DOCTOR'] = $diagnostico_general;
                }
                DB::table('cita')->where('ID_CITA', $cita_id)->update($citaUpdate);
            }

            // Notificaciones (UNA SOLA VEZ)
            $pacUser = DB::selectOne("
                SELECT ID_USUARIOS, NOMBRE_COMPLETO as nombre 
                FROM vista_pacientes 
                WHERE ID_PACIENTE = ?
            ", [$paciente_id]);

            $nombre_paciente = $pacUser ? $pacUser->nombre : 'Paciente';
            $monto_fmt = number_format($totalPresupuestoTotal, 2, ',', '.');

            if ($pacUser) {
                \App\Helpers\Notificador::insertar(30, $pacUser->ID_USUARIOS, "Tu nuevo plan de tratamiento ya está disponible en tu perfil.");
            }

            \App\Helpers\Notificador::enviarAAdmin(29, "Se ha creado un nuevo plan de tratamiento para {$nombre_paciente} por \${$monto_fmt}.");

            if ($totalPresupuestoTotal > 0) {
                $refFacturas = !empty($facturasCreadas)
                    ? implode(', ', array_map(fn($id) => 'FAC-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT), $facturasCreadas))
                    : '';
                $detalleFac = $refFacturas ? " ({$refFacturas})" : "";

                \App\Helpers\Notificador::enviarAAdmin(26, "Se generó nueva deuda{$detalleFac} para {$nombre_paciente} con saldo pendiente de \${$monto_fmt}.");

                if ($pacUser && $pacUser->ID_USUARIOS) {
                    \App\Helpers\Notificador::insertar(27, $pacUser->ID_USUARIOS, "Tu factura{$detalleFac} ha sido generada. Saldo pendiente: \${$monto_fmt}.");
                }

                \App\Helpers\Notificador::enviarAOdontologo($odontologo_id, 28, "El paciente {$nombre_paciente} quedó con saldo pendiente de \${$monto_fmt}{$detalleFac}.");
            }

            DB::commit();
            return response()->json(['success' => true, 'mensaje' => 'Plan de tratamiento e historial guardado correctamente.']);
        } catch (Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error crítico en guardarMasivo: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'error' => 'Error crítico: ' . $e->getMessage()], 500);
        }
    }

    private function esProcedimientoGeneral($nombre)
    {
        $nombreLower = mb_strtolower($nombre);
        $patrones = [
            'limpieza', 'detartraje', 'profilaxis', 'valoracion', 'valoración',
            'diagnostico', 'diagnóstico', 'blanqueamiento', 'aclaramiento',
            'protesis total', 'prótesis total', 'protesis parcial', 'prótesis parcial',
            'ortodoncia', 'brackets', 'brakets', 'retenedor', 'diseño de sonrisa', 'diseño sonrisa'
        ];
        foreach ($patrones as $p) {
            if (strpos($nombreLower, $p) !== false) {
                return true;
            }
        }
        return false;
    }
}
