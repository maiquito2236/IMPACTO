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
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoController extends Controller
{
    /**
     * Crea una preferencia de Checkout Pro y devuelve el init_point al JS.
     */
    public function crearPreferencia(Request $request)
    {
        $facturaId = intval($request->input('factura_id'));
        $monto     = round(floatval($request->input('monto')), 2);

        if (!$facturaId || $monto <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos de pago inválidos.',
            ], 422);
        }

        // Verificar que la factura pertenece al paciente autenticado
        $factura = FacturacionPaciente::obtenerFacturaAutorizada($facturaId);
        if (!$factura) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La factura no existe o no te pertenece.',
            ], 403);
        }

        $saldo = round((float) $factura->TOTAL - (float) $factura->TOTAL_PAGADO, 2);
        if ($saldo <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Esta factura ya está pagada en su totalidad.',
            ], 422);
        }

        if ($monto > $saldo) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El monto no puede superar el saldo pendiente ($' . number_format($saldo, 0, ',', '.') . ').',
            ], 422);
        }

        // Configurar SDK de Mercado Pago
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
        MercadoPagoConfig::setRuntimeEnviroment(
            config('services.mercadopago.sandbox') ? MercadoPagoConfig::LOCAL : MercadoPagoConfig::SERVER
        );

        $numeroFactura = 'FAC-' . str_pad((string) $facturaId, 4, '0', STR_PAD_LEFT);
        $appUrl        = rtrim(config('app.url'), '/');
        $isSandbox     = config('services.mercadopago.sandbox', true);

        $backUrls = [
            'success' => $appUrl . '/paciente/pagos/mercadopago/retorno/success?factura=' . $facturaId . '&monto=' . $monto,
            'failure' => $appUrl . '/paciente/pagos/mercadopago/retorno/failure?factura=' . $facturaId,
            'pending' => $appUrl . '/paciente/pagos/mercadopago/retorno/pending?factura=' . $facturaId,
        ];

        $preferenceData = [
            'items' => [
                [
                    'id'          => (string) $facturaId,
                    'title'       => 'Pago Odonto Estética - ' . $numeroFactura,
                    'description' => 'Abono / pago a factura ' . $numeroFactura,
                    'quantity'    => 1,
                    'unit_price'  => $monto,
                    'currency_id' => config('services.mercadopago.currency_id', 'COP'),
                ],
            ],
            'back_urls'            => $backUrls,
            'statement_descriptor' => config('services.mercadopago.statement_descriptor', 'ODONTOESTETICA'),
            'external_reference'   => 'factura_' . $facturaId . '_monto_' . $monto . '_user_' . Auth::id(),
        ];

        // auto_return solo funciona con URLs HTTPS públicas; en local/sandbox se omite
        // para que el usuario vuelva manualmente o Mercado Pago use back_urls sin redirect automático.
        if (!$isSandbox) {
            $preferenceData['auto_return'] = 'approved';
        }

        try {
            $client     = new PreferenceClient();
            $preference = $client->create($preferenceData);

            return response()->json([
                'status'      => 'success',
                'init_point'  => $preference->init_point,
                'preference_id' => $preference->id,
            ]);
        } catch (MPApiException $e) {
            Log::error('MercadoPago API Error: ' . $e->getMessage(), [
                'status_code'  => $e->getApiResponse()?->getStatusCode(),
                'content'      => $e->getApiResponse()?->getContent(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al crear la preferencia de pago. Intenta nuevamente.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('MercadoPago Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Error inesperado al procesar el pago.',
            ], 500);
        }
    }

    /**
     * Retorno desde Mercado Pago (success / failure / pending).
     */
    public function retorno(Request $request, string $estado)
    {
        $facturaId = intval($request->query('factura'));
        $monto     = round(floatval($request->query('monto')), 2);
        $paymentId = $request->query('payment_id');
        $mpStatus  = $request->query('status');       // approved, rejected, pending

        if ($estado === 'success' && $mpStatus === 'approved' && $facturaId && $monto > 0) {
            // Verificar que no sea un pago duplicado
            $existente = DB::table('pago')
                ->where('FACTURA_ID_FACTURA', $facturaId)
                ->where('REFERENCIA_EXTERNA', $paymentId)
                ->exists();

            if (!$existente) {
                try {
                    DB::statement('CALL sp_registrar_pago_factura(?, ?, ?, @nuevo_pago_id)', [
                        $facturaId,
                        $monto,
                        'MERCADO_PAGO',
                    ]);

                    // Guardar referencia de Mercado Pago si la tabla tiene la columna
                    if ($paymentId) {
                        $ultimoPago = DB::table('pago')
                            ->where('FACTURA_ID_FACTURA', $facturaId)
                            ->orderByDesc('FECHA_PAGO')
                            ->first();
                        if ($ultimoPago && DB::getSchemaBuilder()->hasColumn('pago', 'REFERENCIA_EXTERNA')) {
                            DB::table('pago')
                                ->where('ID_PAGO', $ultimoPago->ID_PAGO)
                                ->update(['REFERENCIA_EXTERNA' => $paymentId]);
                        }
                    }

                    $this->notificarPagoMP($facturaId, $monto);
                } catch (\Exception $e) {
                    Log::error('MercadoPago retorno - Error al registrar pago: ' . $e->getMessage());
                }
            }

            return redirect('/paciente/pagos')
                ->with('success', '¡Pago realizado con éxito! Tu abono ha sido registrado.');
        }

        if ($estado === 'pending') {
            return redirect('/paciente/pagos')
                ->with('info', 'Tu pago está pendiente de confirmación por Mercado Pago. Se registrará cuando sea aprobado.');
        }

        // failure o cualquier otro estado
        return redirect('/paciente/pagos')
            ->with('error', 'El pago no pudo completarse. Puedes intentarlo nuevamente.');
    }

    /**
     * Webhook de Mercado Pago para confirmaciones asíncronas.
     */
    public function webhook(Request $request)
    {
        Log::info('MercadoPago Webhook recibido', $request->all());

        $type = $request->input('type') ?? $request->input('topic');

        if ($type !== 'payment') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $paymentId = $request->input('data.id') ?? $request->input('id');
        if (!$paymentId) {
            return response()->json(['status' => 'no_payment_id'], 200);
        }

        try {
            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

            $client  = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get((int) $paymentId);

            if ($payment->status !== 'approved') {
                return response()->json(['status' => 'not_approved'], 200);
            }

            // Parsear external_reference: "factura_X_monto_Y_user_Z"
            $ref   = $payment->external_reference ?? '';
            preg_match('/factura_(\d+)_monto_([\d.]+)/', $ref, $m);

            if (empty($m[1]) || empty($m[2])) {
                Log::warning('MercadoPago Webhook: external_reference sin formato esperado: ' . $ref);
                return response()->json(['status' => 'invalid_ref'], 200);
            }

            $facturaId = (int) $m[1];
            $monto     = round((float) $m[2], 2);

            // Evitar duplicados
            $existente = DB::table('pago')
                ->where('FACTURA_ID_FACTURA', $facturaId)
                ->where('REFERENCIA_EXTERNA', (string) $paymentId)
                ->exists();

            if (!$existente) {
                DB::statement('CALL sp_registrar_pago_factura(?, ?, ?, @nuevo_pago_id)', [
                    $facturaId,
                    $monto,
                    'MERCADO_PAGO',
                ]);
                $this->notificarPagoMP($facturaId, $monto);
                Log::info("MercadoPago Webhook: Pago registrado - Factura #{$facturaId}, Monto: {$monto}");
            }
        } catch (\Exception $e) {
            Log::error('MercadoPago Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Envía notificaciones al admin y al paciente tras un pago con Mercado Pago.
     */
    private function notificarPagoMP(int $facturaId, float $monto): void
    {
        try {
            $factura = DB::table('vista_facturacion')->where('ID_FACTURA', $facturaId)->first();
            if (!$factura) return;

            $total      = (float) $factura->TOTAL;
            $pagado     = (float) $factura->TOTAL_PAGADO;
            $esTotal    = $total > 0 && $pagado >= $total;
            $montoFmt   = number_format($monto, 0, ',', '.');
            $numFactura = 'FAC-' . str_pad((string) $facturaId, 4, '0', STR_PAD_LEFT);
            $paciente   = (string) ($factura->NOMBRE_PACIENTE ?? 'El paciente');

            $detalle = $esTotal
                ? "canceló la totalidad de la factura {$numFactura} por \${$montoFmt} (Mercado Pago)."
                : "realizó un abono de \${$montoFmt} a la factura {$numFactura} (Mercado Pago).";

            Notificador::enviarAAdmin($esTotal ? 23 : 20, "El paciente {$paciente} {$detalle}");
            Notificador::enviarAPaciente(
                (int) $factura->PACIENTE_ID_PACIENTE,
                $esTotal ? 5 : 19,
                "Hemos recibido tu " . ($esTotal ? 'pago total' : 'abono') . " de \${$montoFmt} para la factura {$numFactura} vía Mercado Pago. ¡Gracias!"
            );
        } catch (\Throwable $e) {
            Log::error('MercadoPago - Error al notificar: ' . $e->getMessage());
        }
    }
}
