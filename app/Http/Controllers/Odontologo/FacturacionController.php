<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacturacionController extends Controller
{
    private function getOdontologoId()
    {
        $usuarioId = Auth::id() ?? 2;
        $odontologo = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $usuarioId)->first()
                   ?? DB::table('odontologo')->first();
        return $odontologo ? $odontologo->ID_ODONTOLOGO : 1;
    }

    public function index()
    {
        $idOdontologo = $this->getOdontologoId();

        // 1. KPIs (usando vista_comisiones_odontologo)
        $totalPagado = (float) DB::table('egresos')
            ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
            ->sum('MONTO_PAGADO');

        $sqlGanado = "
            SELECT 
                COALESCE(SUM(GANANCIA_COMISION), 0) as total_ganado,
                COUNT(ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS) as total_tratamientos
            FROM vista_comisiones_odontologo
            WHERE ID_ODONTOLOGO = ?
        ";
        $resGanado = DB::selectOne($sqlGanado, [$idOdontologo]);
        $totalGanado = $resGanado ? (float)$resGanado->total_ganado : 0;
        $totalTratamientos = $resGanado ? (int)$resGanado->total_tratamientos : 0;
        $saldoPendiente = max(0, $totalGanado - $totalPagado);

        $kpis = [
            'total_ganado_historico' => $totalGanado,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $saldoPendiente,
            'total_tratamientos' => $totalTratamientos
        ];

        // 2. Comisiones (usando vista_comisiones_odontologo)
        $sqlComisiones = "
            SELECT 
                FECHA_FORMATEADA AS fecha,
                MAX(NOMBRE_PACIENTE) AS paciente,
                GROUP_CONCAT(DISTINCT NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS procedimiento,
                SUM(TOTAL_PROCEDIMIENTO) AS monto,
                MAX(PORCENTAJE_COMISION) AS porcentaje,
                SUM(GANANCIA_COMISION) AS ganancia
            FROM vista_comisiones_odontologo
            WHERE ID_ODONTOLOGO = ?
            GROUP BY ID_PACIENTE, FECHA_REGISTRO, FECHA_FORMATEADA
            ORDER BY MAX(FECHA_REGISTRO) DESC
        ";
        $rawComisiones = DB::select($sqlComisiones, [$idOdontologo]);
        $comisiones = array_map(fn($item) => (array)$item, $rawComisiones);

        // 3. Historial de Pagos
        $sqlPagos = "
            SELECT 
                MES_LIQUIDADO AS mes,
                MONTO_TOTAL_PRODUCCION AS produccion,
                PORCENTAJE_APLICADO AS porcentaje,
                MONTO_PAGADO AS pagado,
                DATE_FORMAT(FECHA_REGISTRO, '%d/%m/%Y %h:%i %p') AS fecha_pago
            FROM egresos
            WHERE ODONTOLOGO_ID_ODONTOLOGO = ?
            ORDER BY FECHA_REGISTRO DESC
        ";
        $rawPagos = DB::select($sqlPagos, [$idOdontologo]);
        $pagos = array_map(fn($item) => (array)$item, $rawPagos);

        return view('odontologo.facturacion', compact('kpis', 'comisiones', 'pagos'));
    }
}
