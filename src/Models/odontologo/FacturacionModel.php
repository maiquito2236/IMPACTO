<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class FacturacionModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Obtener el ID_ODONTOLOGO a partir del ID_USUARIOS
    public function obtenerOdontologoId($usuarioId) {
        $sql = "SELECT ID_ODONTOLOGO FROM odontologo WHERE USUARIOS_ID_USUARIOS = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['ID_ODONTOLOGO'] : 0;
    }

    // 💰 1. KPIs
    public function obtenerKPIs($idOdontologo) {
        // Total ganado (histórico o del mes) - sumaremos todo lo pagado en egresos
        $sqlPagado = "SELECT COALESCE(SUM(MONTO_PAGADO), 0) as total_pagado FROM egresos WHERE ODONTOLOGO_ID_ODONTOLOGO = ?";
        $stmtPagado = $this->db->prepare($sqlPagado);
        $stmtPagado->execute([$idOdontologo]);
        $totalPagado = $stmtPagado->fetchColumn();

        // Total generado en comisiones (histórico total)
        $sqlGanado = "SELECT 
                        COALESCE(SUM(hcp.PRECIO_APLICADO * (COALESCE(cc.PORCENTAJE, 40) / 100)), 0) as total_ganado,
                        COUNT(hcp.ID_HISTORIA_CLINICA_HAS_PROCEDIMIENTOS) as total_tratamientos
                      FROM historia_clinica_has_procedimientos hcp
                      INNER JOIN historia_clinica hc ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                      LEFT JOIN configuracion_comisiones cc ON cc.ODONTOLOGO_ID_ODONTOLOGO = hc.ODONTOLOGO_ID_ODONTOLOGO
                      WHERE hc.ODONTOLOGO_ID_ODONTOLOGO = ?";
        $stmtGanado = $this->db->prepare($sqlGanado);
        $stmtGanado->execute([$idOdontologo]);
        $resGanado = $stmtGanado->fetch(PDO::FETCH_ASSOC);

        $totalGanado = $resGanado['total_ganado'] ?? 0;
        $totalTratamientos = $resGanado['total_tratamientos'] ?? 0;

        // El saldo pendiente es lo generado menos lo ya pagado
        $saldoPendiente = $totalGanado - $totalPagado;
        if ($saldoPendiente < 0) $saldoPendiente = 0;

        return [
            'total_ganado_historico' => $totalGanado,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $saldoPendiente,
            'total_tratamientos' => $totalTratamientos
        ];
    }

    // 💰 2. LISTADO DE COMISIONES / TRATAMIENTOS (Facturas)
    public function obtenerComisiones($idOdontologo) {
        $sql = "SELECT 
                    DATE_FORMAT(MAX(hc.FECHA_REGISTRO), '%d/%m/%Y') AS fecha,
                    MAX(CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS)) AS paciente,
                    GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS procedimiento,
                    SUM(hcp.PRECIO_APLICADO) AS monto,
                    MAX(COALESCE(cc.PORCENTAJE, 40.00)) AS porcentaje,
                    SUM(hcp.PRECIO_APLICADO * (COALESCE(cc.PORCENTAJE, 40.00) / 100)) AS ganancia
                FROM historia_clinica_has_procedimientos hcp
                INNER JOIN historia_clinica hc ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                INNER JOIN paciente pa ON hc.PACIENTE_ID_PACIENTE = pa.ID_PACIENTE
                INNER JOIN usuarios u_pac ON pa.USUARIOS_ID_USUARIOS = u_pac.ID_USUARIOS
                LEFT JOIN configuracion_comisiones cc ON cc.ODONTOLOGO_ID_ODONTOLOGO = hc.ODONTOLOGO_ID_ODONTOLOGO
                INNER JOIN procedimientos p ON p.ID_PROCEDIMIENTO = hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO
                WHERE hc.ODONTOLOGO_ID_ODONTOLOGO = ?
                GROUP BY hc.PACIENTE_ID_PACIENTE, hc.FECHA_REGISTRO
                ORDER BY MAX(hc.FECHA_REGISTRO) DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idOdontologo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 💰 3. HISTORIAL DE PAGOS (Egresos liquidados)
    public function obtenerHistorialPagos($idOdontologo) {
        $sql = "SELECT 
                    MES_LIQUIDADO AS mes,
                    MONTO_TOTAL_PRODUCCION AS produccion,
                    PORCENTAJE_APLICADO AS porcentaje,
                    MONTO_PAGADO AS pagado,
                    DATE_FORMAT(FECHA_REGISTRO, '%d/%m/%Y %h:%i %p') AS fecha_pago
                FROM egresos
                WHERE ODONTOLOGO_ID_ODONTOLOGO = ?
                ORDER BY FECHA_REGISTRO DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idOdontologo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}