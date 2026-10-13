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
        $sql = "
            SELECT MAX(HISTORIA_CLINICA_ID_HISTORIA_CLINICA) as ID_HISTORIA_CLINICA, 
                   DATE_FORMAT(FECHA_REGISTRO, '%d/%m/%Y %h:%i %p') as fecha,
                   FECHA_REGISTRO as fecha_raw,
                   DOC_PACIENTE as documento,
                   NOMBRE_PACIENTE as paciente,
                   COALESCE(NULLIF((SELECT c.MOTIVO FROM cita c WHERE c.PACIENTE_ID_PACIENTE = vph.ID_PACIENTE ORDER BY ABS(TIMESTAMPDIFF(SECOND, c.FECHA_HORA, vph.FECHA_REGISTRO)) ASC LIMIT 1), ''), MAX(MOTIVO_CONSULTA)) as motivo,
                   MAX(DIAGNOSTICO) as diagnostico,
                   GROUP_CONCAT(DIAGNOSTICO SEPARATOR '||') as diagnosticos_raw,
                   GROUP_CONCAT(DISTINCT NOMBRE_PROCEDIMIENTO SEPARATOR ', ') as tratamiento,
                   COALESCE(MAX(NOMBRE_ODONTOLOGO), 'Sin asignar') as doctor,
                   ID_PACIENTE as id_paciente
            FROM vista_procedimientos_hc vph
            GROUP BY ID_PACIENTE, FECHA_REGISTRO, DOC_PACIENTE, NOMBRE_PACIENTE
            ORDER BY FECHA_REGISTRO DESC
        ";

        $raw = DB::select($sql);
        $resultados = array_map(fn($item) => (array)$item, $raw);

        foreach ($resultados as &$row) {
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
                $row['dientes_tratados'] = implode(', ', array_unique($dientes));
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
