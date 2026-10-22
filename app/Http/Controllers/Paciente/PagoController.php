<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    /**
     * Muestra el panel financiero principal del paciente
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

        $facturas = [];
        $resumen = [
            'precio_total' => 0,
            'abono_total'  => 0,
            'deuda_total'  => 0
        ];

        if (!empty($pacientesIds)) {
            $placeholders = implode(',', array_fill(0, count($pacientesIds), '?'));
            $rawFacturas = DB::select("
                SELECT 
                    ID_FACTURA,
                    FECHA_EMISION,
                    TOTAL,
                    TOTAL_PAGADO,
                    SALDO_PENDIENTE,
                    ESTADO_FACTURA,
                    PROCEDIMIENTO,
                    NOMBRE_ODONTOLOGO,
                    NOMBRE_PACIENTE,
                    DOC_PACIENTE,
                    PACIENTE_ID_PACIENTE
                FROM vista_facturacion
                WHERE PACIENTE_ID_PACIENTE IN ($placeholders)
                ORDER BY FECHA_EMISION DESC
            ", $pacientesIds);

            foreach ($rawFacturas as $f) {
                $factura = (array) $f;

                // Historial de cuotas / abonos de esta factura
                $cuotas = DB::table('pago')
                    ->where('FACTURA_ID_FACTURA', $factura['ID_FACTURA'])
                    ->orderBy('FECHA_PAGO', 'desc')
                    ->get()
                    ->map(fn($item) => (array) $item)
                    ->toArray();

                $factura['HISTORIAL_CUOTAS'] = $cuotas;
                $factura['MONTO_PAGADO']     = $factura['TOTAL_PAGADO'];
                $factura['ESTADO_CALCULADO'] = $factura['ESTADO_FACTURA'];
                $factura['ODONTOLOGO']       = $factura['NOMBRE_ODONTOLOGO'];

                $facturas[] = $factura;

                $resumen['precio_total'] += (float) $factura['TOTAL'];
                $resumen['abono_total']  += (float) $factura['TOTAL_PAGADO'];
            }

            $resumen['deuda_total'] = max(0, $resumen['precio_total'] - $resumen['abono_total']);
        }

        return view('paciente.pagos', compact('facturas', 'resumen'));
    }

    /**
     * AJAX: Guarda un nuevo abono o pago
     */
    public function guardarPago(Request $request)
    {
        $idFactura = intval($request->input('factura_id') ?? $request->input('id_factura'));
        $monto = floatval($request->input('monto'));
        $metodo = $request->input('metodo') ?? ($request->input('metodo_pago') ?? 'Transferencia');

        if (!$idFactura || $monto <= 0) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Datos de pago inválidos.']);
        }

        try {
            DB::table('pago')->insert([
                'FACTURA_ID_FACTURA' => $idFactura,
                'MONTO'              => $monto,
                'FECHA_PAGO'         => now(),
                'METODO_PAGO'        => $metodo,
                'ESTADO'             => 'PAGADO'
            ]);

            return response()->json(['status' => 'success', 'success' => true, 'message' => 'Abono registrado con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Exportación de la factura a Excel con BOM UTF-8
     */
    public function exportarExcel(Request $request)
    {
        $idFactura = $request->input('id');
        $factura = DB::table('vista_facturacion')->where('ID_FACTURA', $idFactura)->first();
        if (!$factura) {
            return back()->with('error', 'Factura no encontrada');
        }

        $pagos = DB::table('pago')->where('FACTURA_ID_FACTURA', $idFactura)->orderBy('FECHA_PAGO', 'desc')->get();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"Factura_FAC-" . str_pad($idFactura, 4, '0', STR_PAD_LEFT) . ".xls\"");
        echo "\xEF\xBB\xBF";

        echo "<table border='1'>";
        echo "<tr style='background-color:#0b57d0;color:#ffffff;'><th colspan='4'>COMPROBANTE FINANCIERO - ODONTO ESTÉTICA</th></tr>";
        echo "<tr><td><b>Factura:</b></td><td>FAC-" . str_pad($factura->ID_FACTURA, 4, '0', STR_PAD_LEFT) . "</td><td><b>Fecha:</b></td><td>{$factura->FECHA_EMISION}</td></tr>";
        echo "<tr><td><b>Paciente:</b></td><td>{$factura->NOMBRE_PACIENTE}</td><td><b>Documento:</b></td><td>{$factura->DOC_PACIENTE}</td></tr>";
        echo "<tr><td><b>Procedimiento:</b></td><td>{$factura->PROCEDIMIENTO}</td><td><b>Especialista:</b></td><td>{$factura->NOMBRE_ODONTOLOGO}</td></tr>";
        echo "<tr><td><b>Total:</b></td><td>$" . number_format($factura->TOTAL, 0, ',', '.') . "</td><td><b>Total Abonado:</b></td><td>$" . number_format($factura->TOTAL_PAGADO, 0, ',', '.') . "</td></tr>";
        echo "<tr><td><b>Saldo Pendiente:</b></td><td>$" . number_format($factura->SALDO_PENDIENTE, 0, ',', '.') . "</td><td><b>Estado:</b></td><td>{$factura->ESTADO_FACTURA}</td></tr>";
        echo "<tr><th colspan='4' style='background-color:#f1f5f9;'>HISTORIAL DE CUOTAS Y ABONOS</th></tr>";
        echo "<tr style='background-color:#e2e8f0;'><th>Fecha de Pago</th><th>Método</th><th>Monto</th><th>Estado</th></tr>";

        foreach ($pagos as $p) {
            echo "<tr>";
            echo "<td>" . date('d/m/Y h:i A', strtotime($p->FECHA_PAGO)) . "</td>";
            echo "<td>{$p->METODO_PAGO}</td>";
            echo "<td>$" . number_format($p->MONTO, 0, ',', '.') . "</td>";
            echo "<td>{$p->ESTADO}</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }

    /**
     * Vista de impresión oficial
     */
    public function imprimir(Request $request)
    {
        $idFactura = $request->input('id');
        $factura = DB::table('vista_facturacion')->where('ID_FACTURA', $idFactura)->first();
        if (!$factura) {
            die("Factura no encontrada.");
        }
        $pagos = DB::table('pago')->where('FACTURA_ID_FACTURA', $idFactura)->orderBy('FECHA_PAGO', 'desc')->get();

        return view('paciente.exports.pago_imprimir', compact('factura', 'pagos'));
    }
}