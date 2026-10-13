<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        $facturas = [];
        $resumen = [
            'total_tratamientos' => 0,
            'total_pagado'       => 0,
            'saldo_pendiente'    => 0
        ];

        if ($idPaciente) {
            // Traer facturas del paciente
            $rawFacturas = DB::select("
                SELECT 
                    ID_FACTURA,
                    TOTAL,
                    FECHA_EMISION,
                    TOTAL_PAGADO AS MONTO_PAGADO,
                    CASE 
                        WHEN TOTAL_PAGADO >= TOTAL THEN 'PAGADA'
                        WHEN TOTAL_PAGADO > 0 THEN 'ABONADA'
                        ELSE 'PENDIENTE'
                    END AS ESTADO_CALCULADO,
                    PROCEDIMIENTO,
                    NOMBRE_ODONTOLOGO AS DOCTOR
                FROM vista_facturacion
                WHERE PACIENTE_ID_PACIENTE = ?
                ORDER BY FECHA_EMISION DESC
            ", [$idPaciente]);

            foreach ($rawFacturas as $f) {
                $factura = (array) $f;
                // Cuotas / historial de pagos para esta factura
                $cuotas = DB::table('pago')
                    ->where('FACTURA_ID_FACTURA', $factura['ID_FACTURA'])
                    ->orderBy('FECHA_PAGO', 'desc')
                    ->get()
                    ->map(fn($item) => (array) $item)
                    ->toArray();

                $factura['HISTORIAL_CUOTAS'] = $cuotas;
                $facturas[] = $factura;

                $resumen['total_tratamientos'] += (float) $factura['TOTAL'];
                $resumen['total_pagado'] += (float) $factura['MONTO_PAGADO'];
            }

            $resumen['saldo_pendiente'] = max(0, $resumen['total_tratamientos'] - $resumen['total_pagado']);
        }

        return view('paciente.pagos', compact('facturas', 'resumen'));
    }

    public function guardarPago(Request $request)
    {
        $idFactura = intval($request->input('id_factura'));
        $monto = floatval($request->input('monto'));
        $metodo = $request->input('metodo_pago') ?? 'Transferencia';

        if (!$idFactura || $monto <= 0) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos.']);
        }

        $comprobante = null;
        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $filename = 'comprobante_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/comprobantes'), $filename);
            $comprobante = 'uploads/comprobantes/' . $filename;
        }

        try {
            DB::table('pago')->insert([
                'FACTURA_ID_FACTURA' => $idFactura,
                'MONTO'              => $monto,
                'FECHA_PAGO'         => now(),
                'METODO_PAGO'        => $metodo,
                'ESTADO'             => 'PAGADO',
                'COMPROBANTE'        => $comprobante
            ]);

            return response()->json(['success' => true, 'message' => 'Abono registrado con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al registrar pago: ' . $e->getMessage()]);
        }
    }

    public function exportarExcel(Request $request)
    {
        $idFactura = $request->input('id');
        $factura = DB::table('factura')->where('ID_FACTURA', $idFactura)->first();
        if (!$factura) {
            return back()->with('error', 'Factura no encontrada');
        }

        $pagos = DB::table('pago')->where('FACTURA_ID_FACTURA', $idFactura)->get();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"Factura_{$idFactura}.xls\"");
        
        echo "<table border='1'>";
        echo "<tr><th>Factura</th><th>Total</th><th>Fecha Emision</th></tr>";
        echo "<tr><td>{$factura->ID_FACTURA}</td><td>{$factura->TOTAL}</td><td>{$factura->FECHA_EMISION}</td></tr>";
        echo "<tr><th colspan='3'>Historial de Abonos</th></tr>";
        echo "<tr><th>Monto</th><th>Fecha Pago</th><th>Metodo</th></tr>";
        foreach ($pagos as $p) {
            echo "<tr><td>{$p->MONTO}</td><td>{$p->FECHA_PAGO}</td><td>{$p->METODO_PAGO}</td></tr>";
        }
        echo "</table>";
        exit;
    }

    public function imprimir(Request $request)
    {
        $idFactura = $request->input('id');
        $factura = DB::table('factura')->where('ID_FACTURA', $idFactura)->first();
        $pagos = DB::table('pago')->where('FACTURA_ID_FACTURA', $idFactura)->get();

        return view('paciente.exports.pago_imprimir', compact('factura', 'pagos'));
    }
}