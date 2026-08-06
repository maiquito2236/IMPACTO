<?php
namespace App\Models\paciente;

use App\Config\Database;
use PDO;

class Pago {

    private $conexion;

    public function __construct()
    {
        $this->conexion = Database::getInstance()->getConnection();
    }

    // ?? 1. LISTADO DE FACTURAS (Rastreado v�a Historia Cl�nica directamente)
    public function obtenerFacturas($idUsuario)
    {
        $sql = "
            SELECT
                f.ID_FACTURA,
                f.FECHA_EMISION,
                f.TOTAL,
                (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) AS MONTO_PAGADO,
                CASE 
                    WHEN (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) >= f.TOTAL THEN 'Pagada'
                    WHEN (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) > 0 THEN 'Abonada'
                    ELSE 'Pendiente'
                END AS ESTADO_CALCULADO,
                CONCAT(u_doc.NOMBRES, ' ', u_doc.APELLIDOS) AS ODONTOLOGO,
                GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS PROCEDIMIENTO
            FROM factura f
            INNER JOIN historia_clinica hc ON hc.ID_HISTORIA_CLINICA = f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
            INNER JOIN paciente pa ON pa.ID_PACIENTE = hc.PACIENTE_ID_PACIENTE
            INNER JOIN odontologo o ON o.ID_ODONTOLOGO = hc.ODONTOLOGO_ID_ODONTOLOGO
            INNER JOIN usuarios u_doc ON u_doc.ID_USUARIOS = o.USUARIOS_ID_USUARIOS
            INNER JOIN historia_clinica_has_procedimientos hcp ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
            INNER JOIN procedimientos p ON p.ID_PROCEDIMIENTO = hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO
            
            WHERE pa.USUARIOS_ID_USUARIOS = ?
            
            GROUP BY f.ID_FACTURA
            ORDER BY f.ID_FACTURA DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ?? 2. DETALLE DE FACTURA
    public function obtenerFacturaPorId($idFactura)
    {
        $sql = "
            SELECT
                f.ID_FACTURA,
                f.FECHA_EMISION,
                f.TOTAL,
                (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) AS MONTO_PAGADO,
                CASE 
                    WHEN (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) >= f.TOTAL THEN 'Pagada'
                    WHEN (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) > 0 AND (SELECT COALESCE(SUM(MONTO), 0) FROM pago WHERE FACTURA_ID_FACTURA = f.ID_FACTURA) < f.TOTAL THEN 'Abonada'
                    ELSE 'Pendiente'
                END AS ESTADO_CALCULADO,
                CONCAT(u_doc.NOMBRES, ' ', u_doc.APELLIDOS) AS ODONTOLOGO,
                GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS PROCEDIMIENTO,
                u_pac.NUMERO_DOCUMENTO AS PACIENTE_DOCUMENTO, 
                CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS) AS PACIENTE_NOMBRE,
                u_pac.TELEFONO AS PACIENTE_TELEFONO,
                u_pac.CORREO AS PACIENTE_CORREO
            FROM factura f
            INNER JOIN historia_clinica hc ON hc.ID_HISTORIA_CLINICA = f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
            INNER JOIN paciente pa ON pa.ID_PACIENTE = hc.PACIENTE_ID_PACIENTE
            LEFT JOIN usuarios u_pac ON u_pac.ID_USUARIOS = pa.USUARIOS_ID_USUARIOS
            INNER JOIN odontologo o ON o.ID_ODONTOLOGO = hc.ODONTOLOGO_ID_ODONTOLOGO
            INNER JOIN usuarios u_doc ON u_doc.ID_USUARIOS = o.USUARIOS_ID_USUARIOS
            INNER JOIN historia_clinica_has_procedimientos hcp ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
            INNER JOIN procedimientos p ON p.ID_PROCEDIMIENTO = hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO
            
            WHERE f.ID_FACTURA = ?
            GROUP BY f.ID_FACTURA
            LIMIT 1
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$idFactura]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ?? 3. RESUMEN FINANCIERO (M�tricas superiores calculadas sobre los totales reales del paciente)
    public function obtenerResumenPagos($idUsuario)
    {
        $sql = "
            SELECT 
                COALESCE((
                    SELECT SUM(f2.TOTAL)
                    FROM factura f2
                    INNER JOIN historia_clinica hc2 ON f2.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc2.ID_HISTORIA_CLINICA
                    INNER JOIN paciente pa2 ON hc2.PACIENTE_ID_PACIENTE = pa2.ID_PACIENTE
                    WHERE pa2.USUARIOS_ID_USUARIOS = ?
                ), 0) AS precio_total,

                COALESCE((
                    SELECT SUM(pg2.MONTO)
                    FROM pago pg2
                    INNER JOIN factura f3 ON pg2.FACTURA_ID_FACTURA = f3.ID_FACTURA
                    INNER JOIN historia_clinica hc3 ON f3.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc3.ID_HISTORIA_CLINICA
                    INNER JOIN paciente pa3 ON hc3.PACIENTE_ID_PACIENTE = pa3.ID_PACIENTE
                    WHERE pa3.USUARIOS_ID_USUARIOS = ?
                ), 0) AS abono_total,

                (
                    COALESCE((
                        SELECT SUM(f2.TOTAL)
                        FROM factura f2
                        INNER JOIN historia_clinica hc2 ON f2.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc2.ID_HISTORIA_CLINICA
                        INNER JOIN paciente pa2 ON hc2.PACIENTE_ID_PACIENTE = pa2.ID_PACIENTE
                        WHERE pa2.USUARIOS_ID_USUARIOS = ?
                    ), 0) - 
                    COALESCE((
                        SELECT SUM(pg2.MONTO)
                        FROM pago pg2
                        INNER JOIN factura f3 ON pg2.FACTURA_ID_FACTURA = f3.ID_FACTURA
                        INNER JOIN historia_clinica hc3 ON f3.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc3.ID_HISTORIA_CLINICA
                        INNER JOIN paciente pa3 ON hc3.PACIENTE_ID_PACIENTE = pa3.ID_PACIENTE
                        WHERE pa3.USUARIOS_ID_USUARIOS = ?
                    ), 0)
                ) AS deuda_total
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$idUsuario, $idUsuario, $idUsuario, $idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ?? 4. HISTORIAL DE PAGOS
    public function obtenerHistorialPagos($idFactura)
    {
        $sql = "
            SELECT
                FECHA_PAGO AS fecha,  
                MONTO AS monto,       
                'Abono' AS concepto   
            FROM pago
            WHERE FACTURA_ID_FACTURA = ?
            ORDER BY FECHA_PAGO ASC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$idFactura]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ?? 5. REGISTRAR PAGO
    public function registrarPago($idFactura, $monto, $metodo) {
        $sql = "INSERT INTO pago (FACTURA_ID_FACTURA, MONTO, FECHA_PAGO, METODO_PAGO, ESTADO) VALUES (?, ?, NOW(), ?, 'PAGADO')";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$idFactura, $monto, $metodo]);
    }
}


