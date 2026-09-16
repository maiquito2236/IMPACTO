<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ReporteController extends Controller
{
    /**
     * Muestra la vista de Reportes y Estadísticas
     */
    public function index()
    {
        return view('admin.reportes.index');
    }

    /**
     * Endpoint API para reportes y estadísticas en JSON
     */
    public function api(Request $request)
    {
        try {
            $periodo = $request->query('periodo', 'este-mes');
            if (!in_array($periodo, ['este-mes', 'mes-anterior', 'trimestre'])) {
                $periodo = 'este-mes';
            }

            $datos = $this->obtenerDatosReporte($periodo);

            return response()->json([
                'ok' => true,
                'data' => $datos
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerDatosReporte($periodo = 'este-mes')
    {
        $fechas = $this->calcularFechas($periodo);
        $fechasAnterior = $this->calcularFechasAnterior($periodo);

        $data = [];

        // KPIs principales
        $data['ingresos'] = $this->obtenerIngresos($fechas['inicio'], $fechas['fin']);
        $data['ingresos_anterior'] = $this->obtenerIngresos($fechasAnterior['inicio'], $fechasAnterior['fin']);
        
        $data['egresos'] = $this->obtenerEgresos($fechas['inicio'], $fechas['fin']);
        $data['egresos_anterior'] = $this->obtenerEgresos($fechasAnterior['inicio'], $fechasAnterior['fin']);
        
        $data['ganancia_neta'] = $data['ingresos'] - $data['egresos'];
        $data['ganancia_anterior'] = $data['ingresos_anterior'] - $data['egresos_anterior'];
        
        $data['margen'] = $data['ingresos'] > 0 ? round(($data['ganancia_neta'] / $data['ingresos']) * 100, 1) : 0;
        $data['margen_anterior'] = $data['ingresos_anterior'] > 0 ? round(($data['ganancia_anterior'] / $data['ingresos_anterior']) * 100, 1) : 0;
        
        $data['total_facturas'] = $this->obtenerTotalFacturas($fechas['inicio'], $fechas['fin']);
        $data['facturas_anterior'] = $this->obtenerTotalFacturas($fechasAnterior['inicio'], $fechasAnterior['fin']);
        
        $data['pacientes_atendidos'] = $this->obtenerPacientesAtendidos($fechas['inicio'], $fechas['fin']);
        $data['pacientes_anterior'] = $this->obtenerPacientesAtendidos($fechasAnterior['inicio'], $fechasAnterior['fin']);

        // Variaciones porcentuales
        $data['var_ingresos'] = $this->calcularVariacion($data['ingresos'], $data['ingresos_anterior']);
        $data['var_ganancia'] = $this->calcularVariacion($data['ganancia_neta'], $data['ganancia_anterior']);
        $data['var_margen'] = round($data['margen'] - $data['margen_anterior'], 1);
        $data['var_facturas'] = $this->calcularVariacion($data['total_facturas'], $data['facturas_anterior']);
        $data['var_pacientes'] = $this->calcularVariacion($data['pacientes_atendidos'], $data['pacientes_anterior']);

        // Datos para gráficos
        $data['datos_diarios'] = $this->obtenerDatosDiarios($fechas['inicio'], $fechas['fin']);
        $data['ingresos_por_tratamiento'] = $this->obtenerIngresosPorTratamiento($fechas['inicio'], $fechas['fin']);
        
        // Estado de resultados
        $data['estado_resultados'] = $this->obtenerEstadoResultados($fechas, $fechasAnterior);

        // Periodo info
        $data['periodo_label'] = $this->obtenerLabelPeriodo($periodo, $fechas);

        return $data;
    }

    private function calcularFechas($periodo)
    {
        $hoy = date('Y-m-d');
        switch ($periodo) {
            case 'mes-anterior':
                $inicio = date('Y-m-01', strtotime('first day of last month'));
                $fin = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'trimestre':
                $inicio = date('Y-m-01', strtotime('-3 months'));
                $fin = $hoy;
                break;
            case 'este-mes':
            default:
                $inicio = date('Y-m-01');
                $fin = $hoy;
                break;
        }
        return ['inicio' => $inicio, 'fin' => $fin . ' 23:59:59'];
    }

    private function calcularFechasAnterior($periodo)
    {
        switch ($periodo) {
            case 'mes-anterior':
                $inicio = date('Y-m-01', strtotime('-2 months'));
                $fin = date('Y-m-t', strtotime('-2 months'));
                break;
            case 'trimestre':
                $inicio = date('Y-m-01', strtotime('-6 months'));
                $fin = date('Y-m-d', strtotime('-3 months'));
                break;
            case 'este-mes':
            default:
                $inicio = date('Y-m-01', strtotime('first day of last month'));
                $fin = date('Y-m-t', strtotime('last day of last month'));
                break;
        }
        return ['inicio' => $inicio, 'fin' => $fin . ' 23:59:59'];
    }

    private function obtenerIngresos($inicio, $fin)
    {
        $sql = "SELECT IFNULL(SUM(MONTO), 0) AS total
                FROM pago
                WHERE ESTADO = 'PAGADO'
                  AND FECHA_PAGO BETWEEN ? AND ?";
        $res = DB::selectOne($sql, [$inicio, $fin]);
        return (float) ($res->total ?? 0);
    }

    private function obtenerEgresos($inicio, $fin)
    {
        $sql = "SELECT IFNULL(SUM(MONTO_PAGADO), 0) AS total
                FROM egresos
                WHERE FECHA_REGISTRO BETWEEN ? AND ?";
        $res = DB::selectOne($sql, [$inicio, $fin]);
        return (float) ($res->total ?? 0);
    }

    private function obtenerTotalFacturas($inicio, $fin)
    {
        $sql = "SELECT COUNT(DISTINCT FACTURA_ID_FACTURA) AS total
                FROM pago
                WHERE ESTADO = 'PAGADO' AND FECHA_PAGO BETWEEN ? AND ?";
        $res = DB::selectOne($sql, [$inicio, $fin]);
        return (int) ($res->total ?? 0);
    }

    private function obtenerPacientesAtendidos($inicio, $fin)
    {
        $sql = "SELECT COUNT(DISTINCT PACIENTE_ID_PACIENTE) AS total
                FROM cita 
                WHERE ESTADO_CITA_ID = 2
                  AND FECHA_HORA BETWEEN ? AND ?";
        $res = DB::selectOne($sql, [$inicio, $fin]);
        return (int) ($res->total ?? 0);
    }

    private function obtenerDatosDiarios($inicio, $fin)
    {
        $sqlIng = "SELECT DATE(FECHA_PAGO) AS fecha, SUM(MONTO) AS total
                   FROM pago
                   WHERE ESTADO = 'PAGADO'
                     AND FECHA_PAGO BETWEEN ? AND ?
                   GROUP BY DATE(FECHA_PAGO)
                   ORDER BY fecha";
        $ingresosDiarios = DB::select($sqlIng, [$inicio, $fin]);

        $sqlEgr = "SELECT DATE(FECHA_REGISTRO) AS fecha, SUM(MONTO_PAGADO) AS total
                   FROM egresos
                   WHERE FECHA_REGISTRO BETWEEN ? AND ?
                   GROUP BY DATE(FECHA_REGISTRO)
                   ORDER BY fecha";
        $egresosDiarios = DB::select($sqlEgr, [$inicio, $fin]);

        $fechasMap = [];
        foreach ($ingresosDiarios as $row) {
            $f = $row->fecha;
            if (!isset($fechasMap[$f])) $fechasMap[$f] = ['fecha' => $f, 'ing' => 0, 'egr' => 0, 'gan' => 0];
            $fechasMap[$f]['ing'] = (float) $row->total;
        }
        foreach ($egresosDiarios as $row) {
            $f = $row->fecha;
            if (!isset($fechasMap[$f])) $fechasMap[$f] = ['fecha' => $f, 'ing' => 0, 'egr' => 0, 'gan' => 0];
            $fechasMap[$f]['egr'] = (float) $row->total;
        }

        $resultado = [];
        ksort($fechasMap);
        $mesesCortos = [
            '01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
            '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'
        ];
        foreach ($fechasMap as $f => $d) {
            $d['gan'] = $d['ing'] - $d['egr'];
            $partes = explode('-', $f);
            $d['label'] = (int)$partes[2] . ' ' . ($mesesCortos[$partes[1]] ?? $partes[1]);
            $resultado[] = $d;
        }

        return $resultado;
    }

    private function obtenerIngresosPorTratamiento($inicio, $fin)
    {
        $sql = "SELECT 
                    pr.NOMBRE_PROCEDIMIENTO AS nombre,
                    SUM((hchp.PRECIO_APLICADO * hchp.CANTIDAD) * (pg.MONTO / f.TOTAL)) AS ingresos
                FROM pago pg
                INNER JOIN factura f ON pg.FACTURA_ID_FACTURA = f.ID_FACTURA
                INNER JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                INNER JOIN historia_clinica_has_procedimientos hchp ON hc.ID_HISTORIA_CLINICA = hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                INNER JOIN procedimientos pr ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO
                WHERE pg.ESTADO = 'PAGADO' 
                  AND pg.FECHA_PAGO BETWEEN ? AND ?
                  AND f.TOTAL > 0
                GROUP BY pr.ID_PROCEDIMIENTO, pr.NOMBRE_PROCEDIMIENTO
                ORDER BY ingresos DESC
                LIMIT 8";

        return DB::select($sql, [$inicio, $fin]);
    }

    private function obtenerEstadoResultados($fechas, $fechasAnterior)
    {
        $ingActual = $this->obtenerIngresos($fechas['inicio'], $fechas['fin']);
        $ingAnterior = $this->obtenerIngresos($fechasAnterior['inicio'], $fechasAnterior['fin']);

        $egrActual = $this->obtenerEgresos($fechas['inicio'], $fechas['fin']);
        $egrAnterior = $this->obtenerEgresos($fechasAnterior['inicio'], $fechasAnterior['fin']);

        $ganActual = $ingActual - $egrActual;
        $ganAnterior = $ingAnterior - $egrAnterior;

        return [
            [
                'concepto' => 'Ingresos por facturación',
                'actual' => $ingActual,
                'anterior' => $ingAnterior,
                'var' => $this->calcularVariacion($ingActual, $ingAnterior) . '%',
                'trend' => $ingActual >= $ingAnterior ? 'up' : 'down'
            ],
            [
                'concepto' => 'Egresos (comisiones odontólogos)',
                'actual' => -$egrActual,
                'anterior' => -$egrAnterior,
                'var' => $this->calcularVariacion($egrActual, $egrAnterior) . '%',
                'trend' => $egrActual <= $egrAnterior ? 'up' : 'down'
            ],
            [
                'concepto' => 'Ganancia Neta',
                'actual' => $ganActual,
                'anterior' => $ganAnterior,
                'var' => $this->calcularVariacion($ganActual, $ganAnterior) . '%',
                'trend' => $ganActual >= $ganAnterior ? 'up' : 'down',
                'isTotal' => true
            ]
        ];
    }

    private function calcularVariacion($actual, $anterior)
    {
        if ($anterior == 0) return $actual > 0 ? 100 : 0;
        return round((($actual - $anterior) / abs($anterior)) * 100, 1);
    }

    private function obtenerLabelPeriodo($periodo, $fechas)
    {
        $meses = [
            '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
            '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
        ];
        
        $ini = explode('-', $fechas['inicio']);
        $mesIni = $meses[$ini[1]] ?? $ini[1];

        switch ($periodo) {
            case 'mes-anterior':
                return "Mes anterior ($mesIni {$ini[0]})";
            case 'trimestre':
                $finClean = explode(' ', $fechas['fin'])[0];
                $finParts = explode('-', $finClean);
                $mesFin = $meses[$finParts[1]] ?? $finParts[1];
                return "Último trimestre ($mesIni - $mesFin {$finParts[0]})";
            default:
                return "Este mes ($mesIni {$ini[0]})";
        }
    }
}
