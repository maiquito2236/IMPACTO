<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class FacturacionController extends Controller
{
    /**
     * Muestra la vista principal de Facturación y Caja
     */
    public function index()
    {
        // Citas completadas pendientes por facturar
        $citas = DB::select("
            SELECT c.ID_CITA, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_PACIENTE, c.FECHA_ATENCION, c.MOTIVO
            FROM cita c
            INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
            INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
            WHERE c.ESTADO_CITA_ID = 2 
            AND c.ID_CITA NOT IN (SELECT CITA_ID_CITA FROM factura WHERE CITA_ID_CITA IS NOT NULL)
            ORDER BY c.FECHA_ATENCION DESC
        ");

        return view('admin.facturacion.index', compact('citas'));
    }

    /**
     * Enrutador centralizado de API para Facturación
     */
    public function api(Request $request, $op)
    {
        try {
            switch ($op) {
                case 'listar':
                    return response()->json([
                        'status' => 'success',
                        'facturas' => $this->listarFacturas()
                    ]);

                case 'guardar':
                    $data = $request->json()->all() ?: $request->all();
                    if (empty($data['historia_clinica_id']) || empty($data['total'])) {
                        throw new Exception("Datos incompletos para crear la factura.");
                    }
                    $idFactura = $this->crearFactura($data);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Factura creada.',
                        'id' => $idFactura
                    ]);

                case 'configuracion':
                    return response()->json([
                        'status' => 'success',
                        'data' => $this->obtenerConfiguracionPorcentajes()
                    ]);

                case 'resumen':
                    return response()->json([
                        'status' => 'success',
                        'data' => $this->obtenerResumenFinanciero(),
                        'caja' => $this->obtenerCierreCajaHoy()
                    ]);

                case 'exportar':
                    return $this->exportarReporte($request);

                case 'exportar-individual':
                    return $this->exportarFacturaIndividual($request);

                default:
                    throw new Exception("Operación no válida: $op");
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Enrutador para operaciones con sub-rutas (ej: /api_facturacion/configuracion/actualizar, /api_facturacion/liquidacion/calcular, etc.)
     */
    public function apiSub(Request $request, $sub, $op)
    {
        $ruta = "$sub/$op";
        try {
            switch ($ruta) {
                case 'configuracion/actualizar':
                    $data = $request->json()->all() ?: $request->all();
                    if (empty($data['id_odontologo']) || !isset($data['porcentaje'])) {
                        throw new Exception("Datos incompletos para actualizar.");
                    }
                    $this->actualizarPorcentaje($data['id_odontologo'], $data['porcentaje']);
                    return response()->json(['status' => 'success', 'message' => 'Porcentaje actualizado correctamente.']);

                case 'liquidacion/calcular':
                    $data = $request->json()->all() ?: $request->all();
                    if (empty($data['id_odontologo']) || empty($data['mes'])) {
                        throw new Exception("Debe seleccionar un odontólogo y un mes.");
                    }
                    $resultado = $this->calcularLiquidacion($data['id_odontologo'], $data['mes']);
                    $resultado['total_pagar'] = ($resultado['produccion_total'] * $resultado['porcentaje']) / 100;
                    return response()->json(['status' => 'success', 'data' => $resultado]);

                case 'liquidacion/guardar':
                    $data = $request->json()->all() ?: $request->all();
                    $this->registrarEgreso(
                        $data['id_odontologo'], 
                        $data['mes'], 
                        $data['produccion'], 
                        $data['porcentaje'], 
                        $data['total_pagado']
                    );
                    return response()->json(['status' => 'success', 'message' => 'Pago registrado con éxito.']);

                case 'pago/guardar':
                    $data = $request->json()->all() ?: $request->all();
                    if (empty($data['factura_id']) || empty($data['monto'])) {
                        throw new Exception("Faltan datos para el pago.");
                    }
                    $this->registrarPago($data['factura_id'], $data['monto'], $data['metodo'] ?? 'Efectivo');
                    return response()->json(['status' => 'success', 'message' => 'Pago registrado correctamente']);

                default:
                    throw new Exception("Sub-operación no reconocida: $ruta");
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    // =========================================================================
    // MÉTODOS DE BASE DE DATOS Y LÓGICA
    // =========================================================================

    private function listarFacturas()
    {
        $sql = "SELECT 
                    f.ID_FACTURA AS id,
                    f.FECHA_EMISION AS fecha_emision,
                    f.TOTAL AS total,
                    CASE 
                        WHEN IFNULL(p_totales.total_pagado, 0) >= f.TOTAL THEN 'Pagada'
                        WHEN IFNULL(p_totales.total_pagado, 0) > 0 THEN 'Abonada'
                        ELSE 'Emitida'
                    END AS estado,
                    IFNULL(CONCAT(u.NOMBRES, ' ', u.APELLIDOS), 'Paciente Temporal') AS paciente,
                    IFNULL(proc_hc.nombres_procedimientos, 'Tratamiento General') AS tratamiento,
                    IFNULL(p_totales.total_pagado, 0) AS pagado
                FROM factura f
                LEFT JOIN cita c ON f.CITA_ID_CITA = c.ID_CITA
                LEFT JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                LEFT JOIN (
                    SELECT FACTURA_ID_FACTURA, SUM(MONTO) AS total_pagado 
                    FROM pago WHERE ESTADO = 'PAGADO' 
                    GROUP BY FACTURA_ID_FACTURA
                ) p_totales ON f.ID_FACTURA = p_totales.FACTURA_ID_FACTURA
                LEFT JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                LEFT JOIN paciente p ON (hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE OR f.PACIENTE_ID_PACIENTE = p.ID_PACIENTE)
                LEFT JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN (
                    SELECT hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA, 
                           GROUP_CONCAT(pr.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS nombres_procedimientos
                    FROM historia_clinica_has_procedimientos hchp
                    INNER JOIN procedimientos pr ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO
                    GROUP BY hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                ) proc_hc ON hc.ID_HISTORIA_CLINICA = proc_hc.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                ORDER BY f.ID_FACTURA DESC";

        return DB::select($sql);
    }

    private function crearFactura($data)
    {
        DB::beginTransaction();
        try {
            $idFactura = DB::table('factura')->insertGetId([
                'HISTORIA_CLINICA_ID_HISTORIA_CLINICA' => $data['historia_clinica_id'],
                'CITA_ID_CITA' => $data['cita_id'] ?? null,
                'FECHA_EMISION' => now(),
                'TOTAL' => $data['total'],
                'ESTADO' => 'EMITIDA'
            ]);

            if ((float)$data['total'] > 0) {
                $refs = DB::selectOne("
                    SELECT hc.PACIENTE_ID_PACIENTE, hc.ODONTOLOGO_ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente_nombre 
                    FROM historia_clinica hc 
                    INNER JOIN paciente p ON hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE 
                    INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                    WHERE hc.ID_HISTORIA_CLINICA = ?
                ", [$data['historia_clinica_id']]);

                if ($refs) {
                    $montoFmt = number_format((float)$data['total'], 2, ',', '.');
                    // Notificar Admin
                    $adminUsers = DB::table('usuarios')->where('ROLES_ID_ROLES', 1)->where('ESTADO', 'Activo')->pluck('ID_USUARIOS');
                    foreach ($adminUsers as $adminId) {
                        DB::table('notificaciones')->insert([
                            'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 26,
                            'USUARIOS_ID_USUARIOS' => $adminId,
                            'MENSAJE' => "Se generó la factura #{$idFactura} para {$refs->paciente_nombre} con saldo pendiente de $$montoFmt.",
                            'FECHA_ENVIO' => now(),
                            'ESTADO' => 'NO_LEIDA'
                        ]);
                    }

                    // Notificar Paciente
                    $pacienteUserId = DB::table('paciente')->where('ID_PACIENTE', $refs->PACIENTE_ID_PACIENTE)->value('USUARIOS_ID_USUARIOS');
                    if ($pacienteUserId) {
                        DB::table('notificaciones')->insert([
                            'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 27,
                            'USUARIOS_ID_USUARIOS' => $pacienteUserId,
                            'MENSAJE' => "Tu factura #{$idFactura} ha sido generada. Saldo pendiente: $$montoFmt.",
                            'FECHA_ENVIO' => now(),
                            'ESTADO' => 'NO_LEIDA'
                        ]);
                    }

                    // Notificar Odontólogo
                    $odonUserId = DB::table('odontologo')->where('ID_ODONTOLOGO', $refs->ODONTOLOGO_ID_ODONTOLOGO)->value('USUARIOS_ID_USUARIOS');
                    if ($odonUserId) {
                        DB::table('notificaciones')->insert([
                            'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 28,
                            'USUARIOS_ID_USUARIOS' => $odonUserId,
                            'MENSAJE' => "El paciente {$refs->paciente_nombre} quedó con saldo pendiente de $$montoFmt.",
                            'FECHA_ENVIO' => now(),
                            'ESTADO' => 'NO_LEIDA'
                        ]);
                    }
                }
            }

            DB::commit();
            return $idFactura;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function obtenerConfiguracionPorcentajes()
    {
        $sql = "SELECT 
                    o.ID_ODONTOLOGO,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor,
                    e.NOMBRE_ESPECIALIDAD AS especialidad,
                    IFNULL(cc.PORCENTAJE, 40.00) AS porcentaje
                FROM odontologo o
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD
                LEFT JOIN configuracion_comisiones cc ON o.ID_ODONTOLOGO = cc.ODONTOLOGO_ID_ODONTOLOGO";
                
        return DB::select($sql);
    }

    private function actualizarPorcentaje($idOdontologo, $nuevoPorcentaje)
    {
        $existe = DB::table('configuracion_comisiones')
            ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
            ->exists();

        if ($existe) {
            return DB::table('configuracion_comisiones')
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
                ->update(['PORCENTAJE' => $nuevoPorcentaje]);
        } else {
            return DB::table('configuracion_comisiones')->insert([
                'ODONTOLOGO_ID_ODONTOLOGO' => $idOdontologo,
                'PORCENTAJE' => $nuevoPorcentaje
            ]);
        }
    }

    private function calcularLiquidacion($idOdontologo, $mes)
    {
        $produccionYaPagada = (float) DB::table('egresos')
            ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
            ->where('MES_LIQUIDADO', $mes)
            ->sum('MONTO_TOTAL_PRODUCCION');

        $sql = "SELECT 
                    COUNT(f.ID_FACTURA) as cantidad_procedimientos,
                    IFNULL(SUM(f.TOTAL), 0) as produccion_total,
                    (SELECT IFNULL(PORCENTAJE, 40.00) FROM configuracion_comisiones WHERE ODONTOLOGO_ID_ODONTOLOGO = ?) as porcentaje
                FROM factura f
                INNER JOIN cita c ON f.CITA_ID_CITA = c.ID_CITA
                WHERE c.ODONTOLOGO_ID_ODONTOLOGO = ? 
                AND DATE_FORMAT(f.FECHA_EMISION, '%Y-%m') = ?";

        $res = DB::select($sql, [$idOdontologo, $idOdontologo, $mes]);
        $resultado = !empty($res) ? (array) $res[0] : null;

        if (!$resultado || $resultado['cantidad_procedimientos'] == 0) {
            throw new Exception("El odontólogo no tiene facturas registradas en el mes seleccionado ($mes).");
        }

        $produccionTotalReal = (float)$resultado['produccion_total'];
        $produccionPendiente = $produccionTotalReal - $produccionYaPagada;

        if ($produccionPendiente <= 0) {
            throw new Exception("Este odontólogo ya tiene toda su producción liquidada y pagada en el mes seleccionado ($mes).");
        }

        $resultado['produccion_total'] = $produccionPendiente;
        return $resultado;
    }

    private function registrarEgreso($idOdontologo, $mes, $produccion, $porcentaje, $pago)
    {
        DB::beginTransaction();
        try {
            DB::table('egresos')->insert([
                'ODONTOLOGO_ID_ODONTOLOGO' => $idOdontologo,
                'MES_LIQUIDADO' => $mes,
                'MONTO_TOTAL_PRODUCCION' => $produccion,
                'PORCENTAJE_APLICADO' => $porcentaje,
                'MONTO_PAGADO' => $pago,
                'FECHA_REGISTRO' => now()
            ]);

            $odontologo = DB::selectOne("
                SELECT o.USUARIOS_ID_USUARIOS, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS odontologo_nombre 
                FROM odontologo o 
                JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                WHERE o.ID_ODONTOLOGO = ?
            ", [$idOdontologo]);

            $nombreOdontologo = $odontologo ? $odontologo->odontologo_nombre : 'Odontólogo';
            $montoFmt = number_format($pago, 2, ',', '.');

            if ($odontologo && $odontologo->USUARIOS_ID_USUARIOS) {
                DB::table('notificaciones')->insert([
                    'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 24,
                    'USUARIOS_ID_USUARIOS' => $odontologo->USUARIOS_ID_USUARIOS,
                    'MENSAJE' => "El administrador te ha realizado un pago por $$montoFmt correspondiente al mes de $mes.",
                    'FECHA_ENVIO' => now(),
                    'ESTADO' => 'NO_LEIDA'
                ]);
            }

            $adminUsers = DB::table('usuarios')->where('ROLES_ID_ROLES', 1)->where('ESTADO', 'Activo')->pluck('ID_USUARIOS');
            foreach ($adminUsers as $adminId) {
                DB::table('notificaciones')->insert([
                    'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 20,
                    'USUARIOS_ID_USUARIOS' => $adminId,
                    'MENSAJE' => "Has registrado un pago por $$montoFmt al odontólogo(a) $nombreOdontologo correspondiente al mes de $mes.",
                    'FECHA_ENVIO' => now(),
                    'ESTADO' => 'NO_LEIDA'
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function obtenerResumenFinanciero()
    {
        $sql = "SELECT 
            (SELECT IFNULL(SUM(MONTO), 0) FROM pago WHERE DATE(FECHA_PAGO) = CURDATE() AND ESTADO = 'PAGADO') as ingresos_dia,
            (SELECT IFNULL(SUM(MONTO), 0) FROM pago WHERE MONTH(FECHA_PAGO) = MONTH(CURDATE()) AND YEAR(FECHA_PAGO) = YEAR(CURDATE()) AND ESTADO = 'PAGADO') as ingresos_mes,
            (SELECT IFNULL(SUM(p.MONTO), 0) 
             FROM pago p 
             JOIN factura f ON p.FACTURA_ID_FACTURA = f.ID_FACTURA 
             WHERE MONTH(p.FECHA_PAGO) = MONTH(CURDATE()) 
             AND YEAR(p.FECHA_PAGO) = YEAR(CURDATE()) 
             AND p.ESTADO = 'PAGADO'
             AND p.MONTO < f.TOTAL) as abonos_mes,
            (SELECT IFNULL(SUM(f.TOTAL - IFNULL((SELECT SUM(MONTO) FROM pago p2 WHERE p2.FACTURA_ID_FACTURA = f.ID_FACTURA AND p2.ESTADO = 'PAGADO'), 0)), 0) 
             FROM factura f
             WHERE f.TOTAL > IFNULL((SELECT SUM(MONTO) FROM pago p3 WHERE p3.FACTURA_ID_FACTURA = f.ID_FACTURA AND p3.ESTADO = 'PAGADO'), 0)) as deudas_pendientes";
        
        $res = DB::select($sql);
        return !empty($res) ? $res[0] : [
            'ingresos_dia' => 0,
            'ingresos_mes' => 0,
            'abonos_mes' => 0,
            'deudas_pendientes' => 0
        ];
    }

    private function obtenerCierreCajaHoy()
    {
        $sql = "SELECT 
                    IFNULL(SUM(CASE WHEN METODO_PAGO = 'Efectivo' THEN MONTO ELSE 0 END), 0) as efectivo,
                    IFNULL(SUM(CASE WHEN METODO_PAGO = 'Tarjeta' THEN MONTO ELSE 0 END), 0) as tarjeta,
                    IFNULL(SUM(CASE WHEN METODO_PAGO = 'Transferencia' THEN MONTO ELSE 0 END), 0) as transferencia
                FROM pago 
                WHERE DATE(FECHA_PAGO) = CURDATE() AND ESTADO = 'PAGADO'";
        $res = DB::select($sql);
        return !empty($res) ? $res[0] : ['efectivo' => 0, 'tarjeta' => 0, 'transferencia' => 0];
    }

    private function registrarPago($idFactura, $monto, $metodo)
    {
        DB::beginTransaction();
        try {
            DB::table('pago')->insert([
                'FACTURA_ID_FACTURA' => $idFactura,
                'MONTO' => $monto,
                'METODO_PAGO' => $metodo,
                'ESTADO' => 'PAGADO',
                'FECHA_PAGO' => now()
            ]);

            $info = $this->obtenerFacturaIndividualData($idFactura);
            if ($info && isset($info['factura'])) {
                $f = $info['factura'];
                $montoFmt = number_format($monto, 2, ',', '.');

                $refs = DB::selectOne("
                    SELECT 
                        p.ID_PACIENTE AS PACIENTE_ID_PACIENTE, 
                        CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente_nombre 
                    FROM factura f 
                    LEFT JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA 
                    JOIN paciente p ON (hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE OR f.PACIENTE_ID_PACIENTE = p.ID_PACIENTE) 
                    JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                    WHERE f.ID_FACTURA = ?
                ", [$idFactura]);

                if ($refs) {
                    $esPagoTotal = ($f->pagado >= $f->TOTAL);
                    $adminUsers = DB::table('usuarios')->where('ROLES_ID_ROLES', 1)->where('ESTADO', 'Activo')->pluck('ID_USUARIOS');
                    $pacienteUserId = DB::table('paciente')->where('ID_PACIENTE', $refs->PACIENTE_ID_PACIENTE)->value('USUARIOS_ID_USUARIOS');

                    if ($esPagoTotal) {
                        foreach ($adminUsers as $adminId) {
                            DB::table('notificaciones')->insert([
                                'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 23,
                                'USUARIOS_ID_USUARIOS' => $adminId,
                                'MENSAJE' => "El paciente {$refs->paciente_nombre} ha cancelado la totalidad de la factura #{$idFactura} por $$montoFmt.",
                                'FECHA_ENVIO' => now(),
                                'ESTADO' => 'NO_LEIDA'
                            ]);
                        }
                        if ($pacienteUserId) {
                            DB::table('notificaciones')->insert([
                                'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 5,
                                'USUARIOS_ID_USUARIOS' => $pacienteUserId,
                                'MENSAJE' => "Hemos recibido tu pago total de $$montoFmt por concepto de {$f->tratamiento}. ¡Gracias!",
                                'FECHA_ENVIO' => now(),
                                'ESTADO' => 'NO_LEIDA'
                            ]);
                        }
                    } else {
                        foreach ($adminUsers as $adminId) {
                            DB::table('notificaciones')->insert([
                                'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 20,
                                'USUARIOS_ID_USUARIOS' => $adminId,
                                'MENSAJE' => "El paciente {$refs->paciente_nombre} hizo un abono de $$montoFmt a la factura #{$idFactura}.",
                                'FECHA_ENVIO' => now(),
                                'ESTADO' => 'NO_LEIDA'
                            ]);
                        }
                        if ($pacienteUserId) {
                            DB::table('notificaciones')->insert([
                                'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 19,
                                'USUARIOS_ID_USUARIOS' => $pacienteUserId,
                                'MENSAJE' => "Hemos recibido tu abono de $$montoFmt por concepto de {$f->tratamiento}. ¡Gracias!",
                                'FECHA_ENVIO' => now(),
                                'ESTADO' => 'NO_LEIDA'
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function obtenerFacturaIndividualData($idFactura)
    {
        $sql = "SELECT 
                    f.ID_FACTURA,
                    f.FECHA_EMISION,
                    f.TOTAL,
                    IFNULL(CONCAT(u.NOMBRES, ' ', u.APELLIDOS), 'Paciente Temporal') AS paciente,
                    IFNULL(u.NUMERO_DOCUMENTO, 'N/A') AS documento,
                    IFNULL(u.CORREO, 'N/A') AS correo,
                    IFNULL(u.TELEFONO, 'N/A') AS telefono,
                    IFNULL(proc_hc.nombres_procedimientos, 'Tratamiento General') AS tratamiento,
                    IFNULL(p_totales.total_pagado, 0) AS pagado,
                    CASE 
                        WHEN IFNULL(p_totales.total_pagado, 0) >= f.TOTAL THEN 'Pagada'
                        WHEN IFNULL(p_totales.total_pagado, 0) > 0 THEN 'Abonada'
                        ELSE 'Emitida'
                    END AS estado
                FROM factura f
                LEFT JOIN cita c ON f.CITA_ID_CITA = c.ID_CITA
                LEFT JOIN (
                    SELECT FACTURA_ID_FACTURA, SUM(MONTO) AS total_pagado 
                    FROM pago WHERE ESTADO = 'PAGADO' 
                    GROUP BY FACTURA_ID_FACTURA
                ) p_totales ON f.ID_FACTURA = p_totales.FACTURA_ID_FACTURA
                LEFT JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                LEFT JOIN paciente p ON (hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE OR f.PACIENTE_ID_PACIENTE = p.ID_PACIENTE)
                LEFT JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN (
                    SELECT hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA, 
                           GROUP_CONCAT(pr.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS nombres_procedimientos
                    FROM historia_clinica_has_procedimientos hchp
                    INNER JOIN procedimientos pr ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO
                    GROUP BY hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                ) proc_hc ON hc.ID_HISTORIA_CLINICA = proc_hc.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                WHERE f.ID_FACTURA = ?";

        $factura = DB::selectOne($sql, [$idFactura]);

        $pagos = DB::select("
            SELECT ID_PAGO, MONTO, METODO_PAGO, FECHA_PAGO 
            FROM pago 
            WHERE FACTURA_ID_FACTURA = ? AND ESTADO = 'PAGADO'
            ORDER BY FECHA_PAGO ASC
        ", [$idFactura]);

        return ['factura' => $factura, 'pagos' => $pagos];
    }

    private function exportarReporte(Request $request)
    {
        $tipo = $request->query('tipo', 'ingresos');
        $formato = $request->query('formato', 'imprimir');
        $mes = $request->query('mes', date('Y-m'));

        $partes = explode('-', $mes);
        $anio = $partes[0] ?? date('Y');
        $mesNum = $partes[1] ?? date('m');

        if ($tipo === 'ingresos') {
            $datos = DB::select("
                SELECT ID_PAGO, FACTURA_ID_FACTURA, FECHA_PAGO, MONTO, METODO_PAGO 
                FROM pago 
                WHERE MONTH(FECHA_PAGO) = ? AND YEAR(FECHA_PAGO) = ? AND ESTADO = 'PAGADO'
                ORDER BY FECHA_PAGO DESC
            ", [$mesNum, $anio]);
        } else {
            $datos = DB::select("
                SELECT e.MES_LIQUIDADO as mes, 
                       e.MONTO_TOTAL_PRODUCCION as produccion, 
                       e.MONTO_PAGADO as total_pagado,
                       CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as doctor
                FROM egresos e
                JOIN odontologo o ON e.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE e.MES_LIQUIDADO = ?
            ", [$mes]);
        }

        if ($formato === 'excel') {
            return response()->streamDownload(function() use ($tipo, $mes, $datos) {
                echo "\xEF\xBB\xBF";
                echo "<html><head><meta charset='utf-8'></head><body>";
                echo "<table border='1'>";
                echo "<tr><th colspan='4' style='background-color:#2563eb; color:white; font-size:18px;'>Reporte de " . strtoupper($tipo) . " - $mes</th></tr>";
                if ($tipo === 'ingresos') {
                    echo "<tr style='background-color:#f1f5f9;'><th>ID Pago</th><th>Factura</th><th>Fecha</th><th>Total Ingresado</th></tr>";
                    foreach ($datos as $d) {
                        echo "<tr><td>{$d->ID_PAGO}</td><td>FAC-" . str_pad($d->FACTURA_ID_FACTURA, 4, "0", STR_PAD_LEFT) . "</td><td>{$d->FECHA_PAGO}</td><td>$" . number_format($d->MONTO, 2) . "</td></tr>";
                    }
                } else {
                    echo "<tr style='background-color:#f1f5f9;'><th>Mes</th><th>Odontólogo</th><th>Producción</th><th>Comisión Pagada</th></tr>";
                    foreach ($datos as $d) {
                        echo "<tr><td>{$mes}</td><td>{$d->doctor}</td><td>$" . number_format($d->produccion, 2) . "</td><td>$" . number_format($d->total_pagado, 2) . "</td></tr>";
                    }
                }
                echo "</table></body></html>";
            }, "Reporte_{$tipo}_{$mes}.xls", [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8'
            ]);
        }

        $titulo = $tipo === 'ingresos' ? "Reporte de Ingresos" : "Reporte de Egresos";
        $scriptAccion = $formato === 'pdf'
            ? "<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'></script>
               <script>
                   window.onload = function() {
                       var element = document.getElementById('reporte-contenido');
                       var opt = { margin: 15, filename: 'Reporte_{$tipo}_{$mes}.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
                       html2pdf().set(opt).from(element).save().then(() => { setTimeout(() => { window.close(); }, 1500); });
                   };
               </script>"
            : "<script>window.onload = function() { window.print(); };</script>";

        $logoUrl = asset('img/logo_odontologia.png');
        $html = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>{$titulo}</title>
            <style>body { font-family: 'Arial', sans-serif; padding: 20px; color: #333; } #reporte-contenido { padding: 20px; } .header { border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;} .logo img { max-height: 80px; } .info-reporte { text-align: right; } .info-reporte h1 { margin: 0; font-size: 22px; color: #1e293b; } .info-reporte p { margin: 5px 0 0 0; color: #64748b; } table { width: 100%; border-collapse: collapse; margin-bottom: 30px; } th, td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; } th { background-color: #f8fafc; color: #0f172a; } .total-box { float: right; background: #f8fafc; padding: 15px 30px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 18px; } .total-box strong { color: #2563eb; font-size: 22px; }</style>
            </head><body><div id='reporte-contenido'><div class='header'><div class='logo'><img src='{$logoUrl}' alt='Odonto Estética'></div><div class='info-reporte'><p>Período: <strong>{$mes}</strong></p></div></div><table>";

        $suma = 0;
        if ($tipo === 'ingresos') {
            $html .= "<tr><th>N° Factura</th><th>Fecha y Hora del Pago</th><th>Método</th><th>Monto Ingresado</th></tr>";
            foreach ($datos as $d) {
                $html .= "<tr><td>FAC-" . str_pad($d->FACTURA_ID_FACTURA, 4, "0", STR_PAD_LEFT) . "</td><td>{$d->FECHA_PAGO}</td><td>{$d->METODO_PAGO}</td><td>$ " . number_format($d->MONTO, 2, ',', '.') . "</td></tr>";
                $suma += $d->MONTO;
            }
        } else {
            $html .= "<tr><th>Mes Liquidado</th><th>Odontólogo</th><th>Producción</th><th>Comisión Pagada</th></tr>";
            foreach ($datos as $d) {
                $html .= "<tr><td>{$mes}</td><td>{$d->doctor}</td><td>$ " . number_format($d->produccion, 2, ',', '.') . "</td><td>$ " . number_format($d->total_pagado, 2, ',', '.') . "</td></tr>";
                $suma += $d->total_pagado;
            }
        }

        $html .= "</table><div class='total-box'>Total Consolidado: <strong>$ " . number_format($suma, 2, ',', '.') . "</strong></div></div>{$scriptAccion}</body></html>";
        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function exportarFacturaIndividual(Request $request)
    {
        $idFactura = (int)$request->query('id', 0);
        $formato = $request->query('formato', 'imprimir');

        if ($idFactura <= 0) {
            return response("<h2>Error: ID de factura no válido.</h2>", 400);
        }

        $resultado = $this->obtenerFacturaIndividualData($idFactura);
        $f = $resultado['factura'];
        $pagos = $resultado['pagos'];

        if (!$f) {
            return response("<h2>Error: Factura no encontrada.</h2>", 404);
        }

        $numFactura = "FAC-" . str_pad($f->ID_FACTURA, 4, "0", STR_PAD_LEFT);
        $pendiente = $f->TOTAL - $f->pagado;

        if ($formato === 'excel') {
            return response()->streamDownload(function() use ($numFactura, $f, $pendiente, $pagos) {
                echo "\xEF\xBB\xBF";
                echo "<html><head><meta charset='utf-8'></head><body><table border='1'>";
                echo "<tr><th colspan='4' style='background-color:#2563eb; color:white; font-size:18px;'>Factura {$numFactura} - Odonto Estética</th></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>Paciente</th><td colspan='3'>{$f->paciente}</td></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>Documento</th><td colspan='3'>{$f->documento}</td></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>Fecha de Emisión</th><td colspan='3'>{$f->FECHA_EMISION}</td></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>Concepto</th><td colspan='3'>{$f->tratamiento}</td></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>Estado</th><td colspan='3'>{$f->estado}</td></tr>";
                echo "<tr><th style='background-color:#f1f5f9;'>Total</th><th style='background-color:#f1f5f9;'>Pagado</th><th style='background-color:#f1f5f9;'>Pendiente</th><th style='background-color:#f1f5f9;'>Estado</th></tr>";
                echo "<tr><td>$ " . number_format($f->TOTAL, 2) . "</td><td>$ " . number_format($f->pagado, 2) . "</td><td>$ " . number_format($pendiente, 2) . "</td><td>{$f->estado}</td></tr>";
                if (!empty($pagos)) {
                    echo "<tr><th colspan='4' style='background-color:#e0f2fe; color:#1e40af;'>Historial de Pagos</th></tr>";
                    echo "<tr><th>N° Pago</th><th>Fecha</th><th>Método</th><th>Monto</th></tr>";
                    foreach ($pagos as $p) {
                        echo "<tr><td>{$p->ID_PAGO}</td><td>{$p->FECHA_PAGO}</td><td>{$p->METODO_PAGO}</td><td>$ " . number_format($p->MONTO, 2) . "</td></tr>";
                    }
                }
                echo "</table></body></html>";
            }, "Factura_{$numFactura}.xls", [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8'
            ]);
        }

        $scriptAccion = $formato === 'pdf'
            ? "<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'></script>
               <script>
                   window.onload = function() {
                       var element = document.getElementById('factura-contenido');
                       var opt = { margin: 15, filename: 'Factura_{$numFactura}.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
                       html2pdf().set(opt).from(element).save().then(() => { setTimeout(() => { window.close(); }, 1500); });
                   };
               </script>"
            : "<script>window.onload = function() { window.print(); };</script>";

        $estadoColor = $f->estado === 'Pagada' ? '#dcfce7' : ($f->estado === 'Abonada' ? '#fef9c3' : '#dbeafe');
        $estadoTextoColor = $f->estado === 'Pagada' ? '#166534' : ($f->estado === 'Abonada' ? '#854d0e' : '#1e40af');
        $logoUrl = asset('img/logo_odontologia.png');

        $html = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Factura {$numFactura}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Arial', sans-serif; padding: 30px; color: #333; background: #f8fafc; }
                #factura-contenido { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
                .factura-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #2563eb; padding-bottom: 25px; margin-bottom: 30px; }
                .factura-header .logo img { max-height: 110px; }
                .factura-info { text-align: right; }
                .factura-info h1 { font-size: 28px; color: #1e293b; margin-bottom: 5px; }
                .factura-info .num-factura { font-size: 18px; color: #2563eb; font-weight: 700; }
                .factura-info .fecha { color: #64748b; font-size: 13px; margin-top: 4px; }
                .estado-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; background: {$estadoColor}; color: {$estadoTextoColor}; margin-top: 8px; }
                .datos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
                .datos-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; }
                .datos-box h3 { font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 10px; }
                .concepto-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 15px; font-size: 14px; color: #1e40af; font-weight: 500; margin-bottom: 25px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                th { background: #f1f5f9; color: #0f172a; font-size: 11px; text-transform: uppercase; padding: 12px 15px; text-align: left; }
                td { padding: 12px 15px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
                .resumen-financiero { display: flex; justify-content: flex-end; margin-bottom: 30px; }
                .resumen-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 30px; min-width: 280px; }
                .resumen-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
                .resumen-row.total { border-top: 2px solid #2563eb; padding-top: 12px; margin-top: 8px; font-size: 16px; font-weight: 700; color: #2563eb; }
                .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; color: #94a3b8; font-size: 11px; }
            </style>
            </head><body><div id='factura-contenido'>
                <div class='factura-header'><div class='logo'><img src='{$logoUrl}' alt='Odonto Estética'></div><div class='factura-info'><h1>FACTURA</h1><div class='num-factura'>{$numFactura}</div><div class='fecha'>Fecha: {$f->FECHA_EMISION}</div><div class='estado-badge'>{$f->estado}</div></div></div>
                <div class='datos-grid'><div class='datos-box'><h3>Datos del Paciente</h3><p><strong>{$f->paciente}</strong></p><p>Doc: {$f->documento}</p><p>Tel: {$f->telefono}</p><p>Email: {$f->correo}</p></div><div class='datos-box'><h3>Datos de la Factura</h3><p><strong>N°:</strong> {$numFactura}</p><p><strong>Emisión:</strong> {$f->FECHA_EMISION}</p><p><strong>Estado:</strong> {$f->estado}</p></div></div>
                <h3>Concepto / Procedimientos</h3><div class='concepto-box'>{$f->tratamiento}</div>";

        if (!empty($pagos)) {
            $html .= "<h3>Historial de Pagos</h3><table><thead><tr><th>N° Pago</th><th>Fecha y Hora</th><th>Método</th><th style='text-align: right;'>Monto</th></tr></thead><tbody>";
            foreach ($pagos as $p) {
                $html .= "<tr><td>{$p->ID_PAGO}</td><td>{$p->FECHA_PAGO}</td><td>{$p->METODO_PAGO}</td><td style='text-align: right; font-weight: 600; color: #22c55e;'>$ " . number_format($p->MONTO, 2, ',', '.') . "</td></tr>";
            }
            $html .= "</tbody></table>";
        }

        $html .= "<div class='resumen-financiero'><div class='resumen-box'><div class='resumen-row'><span>Total Factura</span><span>$ " . number_format($f->TOTAL, 2, ',', '.') . "</span></div><div class='resumen-row'><span>Total Pagado</span><span style='color:#22c55e;'>$ " . number_format($f->pagado, 2, ',', '.') . "</span></div><div class='resumen-row'><span>Saldo Pendiente</span><span style='color:#ef4444;'>$ " . number_format($pendiente, 2, ',', '.') . "</span></div><div class='resumen-row total'><span>Estado</span><span>{$f->estado}</span></div></div></div>
            <div class='footer'><p><strong>Odonto Estética - Salud y Bienestar</strong></p><p>Generado el: " . date('d/m/Y H:i:s') . "</p></div>
            </div>{$scriptAccion}</body></html>";

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function cargaMasiva(Request $request)
    {
        if ($request->has('descargar_plantilla')) {
            return $this->descargarPlantillaCsv();
        }

        if ($request->hasFile('archivo_csv')) {
            $file = fopen($request->file('archivo_csv')->getRealPath(), 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);

            $successCount = 0; $errorCount = 0; $erroresDetalle = [];
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 6) { $errorCount++; continue; }
                $docPaciente = trim($row[0]);
                if ($docPaciente === 'DOCUMENTO_PACIENTE') continue;

                $fecha = trim($row[1]); $metodo = trim($row[2]);
                $monto = (float)trim($row[3]); $total = (float)trim($row[4]); $estado = trim($row[5]);

                try {
                    $pac = DB::selectOne("SELECT p.ID_PACIENTE FROM paciente p INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE u.NUMERO_DOCUMENTO = ?", [$docPaciente]);
                    if (!$pac) throw new Exception("Paciente con documento $docPaciente no encontrado");

                    DB::beginTransaction();
                    $idFactura = DB::table('factura')->insertGetId([
                        'PACIENTE_ID_PACIENTE' => $pac->ID_PACIENTE,
                        'FECHA_EMISION' => $fecha,
                        'TOTAL' => $total,
                        'ESTADO' => $estado
                    ]);

                    DB::table('pago')->insert([
                        'FACTURA_ID_FACTURA' => $idFactura,
                        'MONTO' => $monto,
                        'METODO_PAGO' => $metodo,
                        'FECHA_PAGO' => $fecha,
                        'ESTADO' => $estado === 'PAGADA' ? 'PAGADO' : 'PENDIENTE'
                    ]);
                    DB::commit();
                    $successCount++;
                } catch (Exception $e) {
                    DB::rollBack();
                    $errorCount++;
                    $erroresDetalle[] = "Fila con doc $docPaciente: " . $e->getMessage();
                }
            }
            fclose($file);
            return response()->json([
                'success' => true,
                'successCount' => $successCount,
                'errorCount' => $errorCount,
                'erroresDetalle' => $erroresDetalle
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se subió ningún archivo'], 400);
    }

    public function descargarPlantillaCsv()
    {
        return response()->streamDownload(function() {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['DOCUMENTO_PACIENTE', 'FECHA_EMISION', 'METODO_PAGO', 'MONTO_ABONADO', 'TOTAL_FACTURA', 'ESTADO_FACTURA']);
            fputcsv($output, ['1001001001', date('Y-m-d H:i:s'), 'Efectivo', '50000', '150000', 'PAGADA']);
            fclose($output);
        }, 'plantilla_facturacion.csv', [
            'Content-Type' => 'text/csv; charset=utf-8'
        ]);
    }
}
