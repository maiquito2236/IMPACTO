<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        if ($idPaciente && !session()->has('paciente_id')) {
            session(['paciente_id' => $idPaciente]);
        }

        // 1. Próxima Cita (usando vista_citas)
        $proximaCita = null;
        if ($idPaciente) {
            $proximaCita = DB::table('vista_citas')
                ->where('PACIENTE_ID_PACIENTE', $idPaciente)
                ->where('ESTADO_CITA_ID', 1)
                ->where('FECHA_HORA', '>=', now())
                ->select([
                    'ID_CITA',
                    'FECHA_HORA',
                    'MOTIVO',
                    'ESTADO_CITA as estado',
                    'NOMBRE_ODONTOLOGO as odontologo',
                    'NOMBRE_ESPECIALIDAD as especialidad',
                    'NOMBRE_CONSULTORIO as consultorio',
                    'NOMBRE_PROCEDIMIENTO as procedimiento'
                ])
                ->orderBy('FECHA_HORA', 'asc')
                ->first();
        }

        // 2. Resumen Tratamientos (últimos 3, usando vista_procedimientos_hc y vista_citas)
        $tratamientos = [];
        if ($idPaciente) {
            $rawTrat = DB::select("
                SELECT nombre, estado FROM (
                    SELECT 
                        NOMBRE_PROCEDIMIENTO AS nombre,
                        'Completada' AS estado,
                        FECHA_REGISTRO AS fecha_ord
                    FROM vista_procedimientos_hc
                    WHERE ID_PACIENTE = ?
                    
                    UNION ALL
                    
                    SELECT 
                        NOMBRE_PROCEDIMIENTO AS nombre,
                        ESTADO_CITA AS estado,
                        FECHA_HORA AS fecha_ord
                    FROM vista_citas
                    WHERE PACIENTE_ID_PACIENTE = ? AND NOMBRE_PROCEDIMIENTO IS NOT NULL
                ) AS temp
                ORDER BY fecha_ord DESC
                LIMIT 3
            ", [$idPaciente, $idPaciente]);

            $tratamientos = json_decode(json_encode($rawTrat), true);
        }

        // 3. Pagos Pendientes (Deuda Total usando vista_facturacion)
        $totalPendiente = 0;
        if ($idPaciente) {
            $deudaRow = DB::selectOne("
                SELECT COALESCE(SUM(SALDO_PENDIENTE), 0) AS deuda_total
                FROM vista_facturacion
                WHERE PACIENTE_ID_PACIENTE = ?
            ", [$idPaciente]);

            $totalPendiente = $deudaRow ? max(0, (float) $deudaRow->deuda_total) : 0;
        }

        // 4. Citas Recientes (5, usando vista_citas)
        $citasRecientes = [];
        if ($idPaciente) {
            $citasRecientes = DB::table('vista_citas')
                ->where('PACIENTE_ID_PACIENTE', $idPaciente)
                ->select([
                    'ID_CITA',
                    'FECHA_HORA',
                    'MOTIVO',
                    'ESTADO_CITA as estado',
                    'NOMBRE_ODONTOLOGO as odontologo',
                    'NOMBRE_ESPECIALIDAD as especialidad'
                ])
                ->orderBy('FECHA_HORA', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        }

        // 5. Últimos Pagos (2, usando vista_facturacion)
        $ultimosPagos = [];
        if ($idPaciente) {
            $rawPagos = DB::select("
                SELECT 
                    pg.ID_PAGO,
                    pg.MONTO,
                    pg.FECHA_PAGO,
                    pg.METODO_PAGO,
                    COALESCE(vf.PROCEDIMIENTO, 'Tratamiento Odontológico') AS concepto
                FROM pago pg
                INNER JOIN vista_facturacion vf ON pg.FACTURA_ID_FACTURA = vf.ID_FACTURA
                WHERE vf.PACIENTE_ID_PACIENTE = ?
                ORDER BY pg.FECHA_PAGO DESC
                LIMIT 2
            ", [$idPaciente]);

            $ultimosPagos = json_decode(json_encode($rawPagos), true);
        }

        $proximaCitaArray = $proximaCita ? (array) $proximaCita : null;

        return view('paciente.dashboard', [
            'proximaCita'    => $proximaCitaArray,
            'tratamientos'   => $tratamientos,
            'totalPendiente' => $totalPendiente,
            'citasRecientes' => $citasRecientes,
            'ultimosPagos'   => $ultimosPagos
        ]);
    }
}