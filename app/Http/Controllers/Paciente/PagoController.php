<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Helpers\FacturacionPaciente;
use App\Helpers\Notificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

            $facturaIds = array_map(fn($item) => (int) $item->ID_FACTURA, $rawFacturas);
            $pagosPorFactura = !empty($facturaIds)
                ? DB::table('pago')
                    ->whereIn('FACTURA_ID_FACTURA', $facturaIds)
                    ->orderBy('FECHA_PAGO', 'desc')
                    ->get()
                    ->groupBy('FACTURA_ID_FACTURA')
                : collect();

            foreach ($rawFacturas as $f) {
                $factura = (array) $f;

                // Historial de cuotas / abonos de esta factura (precargado sin N+1)
                $cuotas = ($pagosPorFactura->get($factura['ID_FACTURA']) ?? collect())
                    ->map(fn($item) => (array) $item)
                    ->values()
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
        $monto = round(floatval($request->input('monto')), 2);
        $metodo = strtoupper(trim((string) ($request->input('metodo') ?? ($request->input('metodo_pago') ?? 'TRANSFERENCIA'))));

        if ($metodo === 'MERCADO_PAGO' || $metodo === 'TARJETA') {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Para pagos con tarjeta, PSE o Nequi debes usar la opción Mercado Pago.',
            ], 422);
        }

        if (!in_array($metodo, ['EFECTIVO', 'TRANSFERENCIA'], true)) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Método de pago no válido.']);
        }

        if (!$idFactura || $monto <= 0) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Datos de pago inválidos.']);
        }

        // Solo se puede pagar una factura propia o de un menor a cargo.
        $factura = FacturacionPaciente::obtenerFacturaAutorizada($idFactura);
        if (!$factura) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'La factura no existe o no pertenece a tu cuenta.'], 403);
        }

        $saldo = round((float) $factura->TOTAL - (float) $factura->TOTAL_PAGADO, 2);
        if ($saldo <= 0) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Esta factura ya está pagada en su totalidad.']);
        }

        if ($monto > $saldo) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'El monto no puede superar el saldo pendiente ($' . number_format($saldo, 0, ',', '.') . ').',
            ], 422);
        }

        try {
            DB::statement('CALL sp_registrar_pago_factura(?, ?, ?, @nuevo_pago_id)', [
                $idFactura,
                $monto,
                $metodo
            ]);

            $this->notificarPagoManual($idFactura, $monto, $metodo);

            return response()->json(['status' => 'success', 'success' => true, 'message' => 'Abono registrado con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Notifica al admin y al paciente cuando el paciente registra un pago
     * manual (efectivo o transferencia), igual que el flujo del administrador.
     */
    private function notificarPagoManual(int $idFactura, float $monto, string $metodo): void
    {
        try {
            $factura = DB::table('vista_facturacion')->where('ID_FACTURA', $idFactura)->first();
            if (!$factura) {
                return;
            }

            $total = (float) $factura->TOTAL;
            $pagado = (float) $factura->TOTAL_PAGADO;
            $esTotal = $total > 0 && $pagado >= $total;
            $montoFmt = number_format($monto, 0, ',', '.');
            $numeroFactura = 'FAC-' . str_pad((string) $idFactura, 4, '0', STR_PAD_LEFT);
            $metodoFmt = Str::ucfirst(Str::lower($metodo));
            $paciente = (string) ($factura->NOMBRE_PACIENTE ?? 'El paciente');

            $detalle = $esTotal
                ? "canceló la totalidad de la factura {$numeroFactura} por \${$montoFmt} ({$metodoFmt})."
                : "realizó un abono de \${$montoFmt} a la factura {$numeroFactura} ({$metodoFmt}).";

            Notificador::enviarAAdmin($esTotal ? 23 : 20, "El paciente {$paciente} {$detalle}");
            Notificador::enviarAPaciente(
                (int) $factura->PACIENTE_ID_PACIENTE,
                $esTotal ? 5 : 19,
                "Hemos recibido tu " . ($esTotal ? 'pago total' : 'abono') . " de \${$montoFmt} para la factura {$numeroFactura}. ¡Gracias!"
            );
        } catch (\Throwable $e) {
            Log::error('Pago manual - Error al notificar: ' . $e->getMessage());
        }
    }

    /**
     * Exportación de la factura a Excel con BOM UTF-8
     */
    public function exportarExcel(Request $request)
    {
        $idFactura = (int) $request->input('id');
        $factura = FacturacionPaciente::obtenerFacturaAutorizada($idFactura);
        if (!$factura) {
            return back()->with('error', 'Factura no encontrada o no autorizada.');
        }

        $pagos = DB::table('pago')->where('FACTURA_ID_FACTURA', $idFactura)->orderBy('FECHA_PAGO', 'desc')->get();
        $filename = "Factura_FAC-" . str_pad((string) $idFactura, 4, '0', STR_PAD_LEFT) . ".xls";

        return response()->streamDownload(function () use ($factura, $pagos) {
            echo "\xEF\xBB\xBF";
            echo "<table border='1'>";
            echo "<tr style='background-color:#0b57d0;color:#ffffff;'><th colspan='4'>COMPROBANTE FINANCIERO - ODONTO ESTÉTICA</th></tr>";
            echo "<tr><td><b>Factura:</b></td><td>FAC-" . str_pad((string) $factura->ID_FACTURA, 4, '0', STR_PAD_LEFT) . "</td><td><b>Fecha:</b></td><td>" . e($factura->FECHA_EMISION) . "</td></tr>";
            echo "<tr><td><b>Paciente:</b></td><td>" . e($factura->NOMBRE_PACIENTE) . "</td><td><b>Documento:</b></td><td>" . e($factura->DOC_PACIENTE) . "</td></tr>";
            echo "<tr><td><b>Procedimiento:</b></td><td>" . e($factura->PROCEDIMIENTO) . "</td><td><b>Especialista:</b></td><td>" . e($factura->NOMBRE_ODONTOLOGO) . "</td></tr>";
            echo "<tr><td><b>Total:</b></td><td>$" . number_format($factura->TOTAL, 0, ',', '.') . "</td><td><b>Total Abonado:</b></td><td>$" . number_format($factura->TOTAL_PAGADO, 0, ',', '.') . "</td></tr>";
            echo "<tr><td><b>Saldo Pendiente:</b></td><td>$" . number_format($factura->SALDO_PENDIENTE, 0, ',', '.') . "</td><td><b>Estado:</b></td><td>" . e($factura->ESTADO_FACTURA) . "</td></tr>";
            echo "<tr><th colspan='4' style='background-color:#f1f5f9;'>HISTORIAL DE CUOTAS Y ABONOS</th></tr>";
            echo "<tr style='background-color:#e2e8f0;'><th>Fecha de Pago</th><th>Método</th><th>Monto</th><th>Estado</th></tr>";

            foreach ($pagos as $p) {
                echo "<tr>";
                echo "<td>" . date('d/m/Y h:i A', strtotime($p->FECHA_PAGO)) . "</td>";
                echo "<td>" . e($p->METODO_PAGO) . "</td>";
                echo "<td>$" . number_format($p->MONTO, 0, ',', '.') . "</td>";
                echo "<td>" . e($p->ESTADO) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        ]);
    }

    /**
     * Vista de impresión oficial
     */
    public function imprimir(Request $request)
    {
        $idFactura = (int) $request->input('id');
        $factura = FacturacionPaciente::obtenerFacturaAutorizada($idFactura);
        if (!$factura) {
            return redirect('/paciente/pagos')->with('error', 'Factura no encontrada o no autorizada.');
        }
        $pagos = DB::table('pago')->where('FACTURA_ID_FACTURA', $idFactura)->orderBy('FECHA_PAGO', 'desc')->get();

        return view('paciente.exports.pago_imprimir', compact('factura', 'pagos'));
    }
}