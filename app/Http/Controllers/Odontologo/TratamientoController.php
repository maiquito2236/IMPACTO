<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TratamientoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('action')) {
            return $this->api($request);
        }
        return view('odontologo.tratamiento');
    }

    public function api(Request $request, $op = null)
    {
        $op = $op ?: $request->input('action', $request->query('action', 'listar'));
        $op = str_replace('odontologo/tratamientos/', '', $op);
        switch ($op) {
            case 'listar':
                return $this->listar();
            default:
                return response()->json(['status' => 'error', 'message' => "Operación no válida: $op"], 400);
        }
    }

    public function listar()
    {
        // 1. Agrupamos por paciente y día para consolidar todos los procedimientos de la atención/consulta
        // De este modo no se muestran procedimientos separados en filas aisladas si corresponden a la misma atención
        $sql = "
            SELECT 
                MAX(vph.HISTORIA_CLINICA_ID_HISTORIA_CLINICA) as ID_HISTORIA_CLINICA, 
                DATE(vph.FECHA_REGISTRO) as fecha_dia,
                DATE_FORMAT(MAX(vph.FECHA_REGISTRO), '%d/%m/%Y %h:%i %p') as fecha,
                MAX(vph.FECHA_REGISTRO) as fecha_raw,
                vph.DOC_PACIENTE as documento,
                vph.NOMBRE_PACIENTE as paciente,
                MAX(vph.MOTIVO_CONSULTA) as motivo,
                MAX(vph.DIAGNOSTICO) as diagnostico,
                GROUP_CONCAT(DISTINCT vph.DIAGNOSTICO SEPARATOR '||') as diagnosticos_raw,
                GROUP_CONCAT(DISTINCT vph.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') as tratamiento,
                COALESCE(MAX(vph.NOMBRE_ODONTOLOGO), 'Sin asignar') as doctor,
                vph.ID_PACIENTE as id_paciente,
                COUNT(DISTINCT vph.ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS) as total_procedimientos_count
            FROM vista_procedimientos_hc vph
            GROUP BY vph.ID_PACIENTE, DATE(vph.FECHA_REGISTRO), vph.DOC_PACIENTE, vph.NOMBRE_PACIENTE
            ORDER BY MAX(vph.FECHA_REGISTRO) DESC
        ";

        $raw = DB::select($sql);
        $resultados = array_map(fn($item) => (array)$item, $raw);

        // 2. Traer los procedimientos detallados de cada atención para listarlos en el 'ojo' y reporte
        $detallesSql = "
            SELECT 
                vph.ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS,
                vph.HISTORIA_CLINICA_ID_HISTORIA_CLINICA,
                vph.ID_PACIENTE,
                DATE(vph.FECHA_REGISTRO) as fecha_dia,
                vph.NOMBRE_PROCEDIMIENTO as nombre,
                vph.CANTIDAD as cantidad,
                vph.PRECIO_APLICADO as precio,
                vph.SUBTOTAL as subtotal,
                vph.OBSERVACIONES as observaciones,
                vph.DIAGNOSTICO as diagnostico,
                vph.MOTIVO_CONSULTA as motivo
            FROM vista_procedimientos_hc vph
            ORDER BY vph.FECHA_REGISTRO ASC, vph.ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS ASC
        ";

        $rawDetalles = DB::select($detallesSql);
        $detallesPorSesion = [];

        foreach ($rawDetalles as $det) {
            $d = (array)$det;
            $pieza = 'General';
            $textoParaPieza = ($d['diagnostico'] ?? '') . ' ' . ($d['observaciones'] ?? '');
            if (preg_match('/Piezas?:\s*([0-9,\s]+)/i', $textoParaPieza, $matches)) {
                $pieza = trim($matches[1]);
            } elseif (preg_match('/Pieza\s+([0-9]+)/i', $textoParaPieza, $matches)) {
                $pieza = trim($matches[1]);
            }

            $diagLimpio = trim(preg_replace('/(Piezas?(:)?\s*[0-9,\s]+\s*\|\s*)/i', '', $d['diagnostico'] ?? ''), " |");

            $itemDetalle = [
                'id' => $d['ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS'],
                'nombre' => $d['nombre'],
                'cantidad' => (int) ($d['cantidad'] ?: 1),
                'precio' => (float) $d['precio'],
                'subtotal' => (float) $d['subtotal'],
                'pieza' => $pieza,
                'observaciones' => $d['observaciones'] ?: '',
                'diagnostico' => $diagLimpio
            ];

            $clave = $d['ID_PACIENTE'] . '_' . $d['fecha_dia'];
            if (!isset($detallesPorSesion[$clave])) {
                $detallesPorSesion[$clave] = [];
            }
            $detallesPorSesion[$clave][] = $itemDetalle;
        }

        // 3. Procesar dientes y diagnósticos acumulados para cada fila consolidada
        foreach ($resultados as &$row) {
            $clave = $row['id_paciente'] . '_' . ($row['fecha_dia'] ?? '');
            $row['procedimientos_lista'] = $detallesPorSesion[$clave] ?? [];

            // Si motivo está vacío, buscarlo en la cita más cercana
            if (empty($row['motivo'])) {
                try {
                    $cita = DB::table('cita')
                        ->where('PACIENTE_ID_PACIENTE', $row['id_paciente'])
                        ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, FECHA_HORA, ?)) ASC', [$row['fecha_raw']])
                        ->first();
                    $row['motivo'] = $cita->MOTIVO ?? 'Consulta general odontológica';
                } catch (\Throwable $e) {
                    $row['motivo'] = 'Consulta general odontológica';
                }
            }

            $dientes = [];
            if (!empty($row['diagnosticos_raw'])) {
                $partes = explode('||', $row['diagnosticos_raw']);
                foreach ($partes as $diag) {
                    if (preg_match('/Piezas?:\s*([0-9,\s]+)/i', $diag, $matches)) {
                        $nums = explode(',', $matches[1]);
                        foreach ($nums as $num) {
                            if (trim($num) !== '') $dientes[] = trim($num);
                        }
                    } elseif (preg_match('/Pieza\s+([0-9]+)/i', $diag, $matches)) {
                        if (trim($matches[1]) !== '') $dientes[] = trim($matches[1]);
                    }
                }
            }
            if (!empty($dientes)) {
                $dientesUnicos = array_values(array_unique($dientes));
                sort($dientesUnicos, SORT_NUMERIC);
                $row['dientes_tratados'] = implode(', ', $dientesUnicos);
            } else {
                $row['dientes_tratados'] = 'General / No especificado';
            }

            if (!empty($row['diagnostico'])) {
                $clean_diag = preg_replace('/(Piezas?(:)?\s*[0-9,\s]+\s*\|\s*)/i', '', $row['diagnostico']);
                $row['diagnostico'] = trim($clean_diag, " |");
            }
        }

        return response()->json($resultados);
    }
}
