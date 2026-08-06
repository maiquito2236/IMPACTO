<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;
use Exception;

class FacturaAdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra una factura nueva vinculada a una cita.
     * Incluye una transacción para asegurar la integridad entre la factura y el estado.
     */
    public function crearFactura($data) {
    $this->db->beginTransaction();
    try {
        $sql = "INSERT INTO factura (HISTORIA_CLINICA_ID_HISTORIA_CLINICA, CITA_ID_CITA, FECHA_EMISION, TOTAL, ESTADO) 
                VALUES (?, ?, NOW(), ?, 'EMITIDA')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['historia_clinica_id'], 
            $data['cita_id'], 
            $data['total']
        ]);
        
        $id_factura = $this->db->lastInsertId();

        // No actualizamos el estado de la cita a 4 (No asistió) aquí.
        // La cita debe mantenerse en estado 2 (Completada) al facturar.

        $this->db->commit();
        return $id_factura;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    public function listarFacturas() {
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
    
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCitasParaFacturar() {
    
        // Hacemos el JOIN con la tabla usuarios para obtener los nombres y apellidos reales
        $sql = "SELECT c.ID_CITA, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_PACIENTE, c.FECHA_ATENCION, c.MOTIVO
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE c.ESTADO_CITA_ID = 2 
                AND c.ID_CITA NOT IN (SELECT CITA_ID_CITA FROM factura)";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // CONFIGURACIÓN DE PORCENTAJES
    // ==========================================
    public function obtenerConfiguracionPorcentajes() {
        // Traemos todos los odontólogos con su especialidad y su porcentaje (o 40.00 por defecto)
        $sql = "SELECT 
                    o.ID_ODONTOLOGO,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor,
                    e.NOMBRE_ESPECIALIDAD AS especialidad,
                    IFNULL(cc.PORCENTAJE, 40.00) AS porcentaje
                FROM odontologo o
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD
                LEFT JOIN configuracion_comisiones cc ON o.ID_ODONTOLOGO = cc.ODONTOLOGO_ID_ODONTOLOGO";
                
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarPorcentaje($idOdontologo, $nuevoPorcentaje) {
        // Verificamos si ya tiene una configuración guardada
        $stmt = $this->db->prepare("SELECT ID_CONFIG FROM configuracion_comisiones WHERE ODONTOLOGO_ID_ODONTOLOGO = ?");
        $stmt->execute([$idOdontologo]);
        
        if ($stmt->rowCount() > 0) {
            // Si ya existe, actualizamos
            $sql = "UPDATE configuracion_comisiones SET PORCENTAJE = ? WHERE ODONTOLOGO_ID_ODONTOLOGO = ?";
            return $this->db->prepare($sql)->execute([$nuevoPorcentaje, $idOdontologo]);
        } else {
            // Si es la primera vez, insertamos
            $sql = "INSERT INTO configuracion_comisiones (ODONTOLOGO_ID_ODONTOLOGO, PORCENTAJE) VALUES (?, ?)";
            return $this->db->prepare($sql)->execute([$idOdontologo, $nuevoPorcentaje]);
        }
    }

    // ==========================================
    // MÓDULO DE EGRESOS / LIQUIDACIÓN
    // ==========================================
    public function calcularLiquidacion($idOdontologo, $mes) {
        // 1. Obtener total de produccion ya liquidada (pagada) en este mes para este doctor
        $sqlPagado = "SELECT IFNULL(SUM(MONTO_TOTAL_PRODUCCION), 0) FROM egresos WHERE ODONTOLOGO_ID_ODONTOLOGO = ? AND MES_LIQUIDADO = ?";
        $stmtPagado = $this->db->prepare($sqlPagado);
        $stmtPagado->execute([$idOdontologo, $mes]);
        $produccion_ya_pagada = (float)$stmtPagado->fetchColumn();

        // 2. Buscamos todas las facturas de ese doctor en ese mes (Formato YYYY-MM)
        $sql = "SELECT 
                    COUNT(f.ID_FACTURA) as cantidad_procedimientos,
                    IFNULL(SUM(f.TOTAL), 0) as produccion_total,
                    (SELECT IFNULL(PORCENTAJE, 40.00) FROM configuracion_comisiones WHERE ODONTOLOGO_ID_ODONTOLOGO = :id_doc) as porcentaje
                FROM factura f
                INNER JOIN cita c ON f.CITA_ID_CITA = c.ID_CITA
                WHERE c.ODONTOLOGO_ID_ODONTOLOGO = :id_doc 
                AND DATE_FORMAT(f.FECHA_EMISION, '%Y-%m') = :mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_doc', $idOdontologo, PDO::PARAM_INT);
        $stmt->bindParam(':mes', $mes, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resultado || $resultado['cantidad_procedimientos'] == 0) {
            throw new Exception("El odontólogo no tiene facturas registradas en el mes seleccionado ($mes).");
        }
        
        $produccion_total_real = (float)$resultado['produccion_total'];
        $produccion_pendiente = $produccion_total_real - $produccion_ya_pagada;

        if ($produccion_pendiente <= 0) {
            throw new Exception("Este odontólogo ya tiene toda su producción liquidada y pagada en el mes seleccionado ($mes).");
        }

        // Modificamos el resultado para reflejar solo lo que falta por pagar
        $resultado['produccion_total'] = $produccion_pendiente;
        
        return $resultado;
    }

    public function registrarEgreso($idOdontologo, $mes, $produccion, $porcentaje, $pago) {
        
        $sql = "INSERT INTO egresos (ODONTOLOGO_ID_ODONTOLOGO, MES_LIQUIDADO, MONTO_TOTAL_PRODUCCION, PORCENTAJE_APLICADO, MONTO_PAGADO) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->prepare($sql)->execute([$idOdontologo, $mes, $produccion, $porcentaje, $pago]);
    }

    public function obtenerResumenFinanciero() {
        try {
            $sql = "SELECT 
                (SELECT IFNULL(SUM(MONTO), 0) FROM pago WHERE DATE(FECHA_PAGO) = CURDATE()) as ingresos_dia,
                
                (SELECT IFNULL(SUM(MONTO), 0) FROM pago WHERE MONTH(FECHA_PAGO) = MONTH(CURDATE()) AND YEAR(FECHA_PAGO) = YEAR(CURDATE())) as ingresos_mes,
                
                (SELECT IFNULL(SUM(p.MONTO), 0) 
                 FROM pago p 
                 JOIN factura f ON p.FACTURA_ID_FACTURA = f.ID_FACTURA 
                 WHERE MONTH(p.FECHA_PAGO) = MONTH(CURDATE()) 
                 AND YEAR(p.FECHA_PAGO) = YEAR(CURDATE()) 
                 AND p.MONTO < f.TOTAL) as abonos_mes,
                 
                (SELECT IFNULL(SUM(f.TOTAL - IFNULL((SELECT SUM(MONTO) FROM pago p2 WHERE p2.FACTURA_ID_FACTURA = f.ID_FACTURA), 0)), 0) 
                 FROM factura f
                 WHERE f.TOTAL > IFNULL((SELECT SUM(MONTO) FROM pago p3 WHERE p3.FACTURA_ID_FACTURA = f.ID_FACTURA), 0)) as deudas_pendientes";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['ingresos_dia' => 0, 'ingresos_mes' => 0, 'abonos_mes' => 0, 'deudas_pendientes' => 0];
        }
    }

    public function registrarPago($id_factura, $monto, $metodo) {
        try {
            // Aquí agregamos FECHA_PAGO y NOW() para que quede registrado el día exacto del abono
            $sql = "INSERT INTO pago (FACTURA_ID_FACTURA, MONTO, METODO_PAGO, ESTADO, FECHA_PAGO) 
                    VALUES (:id_factura, :monto, :metodo, 'PAGADO', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_factura', $id_factura);
            $stmt->bindParam(':monto', $monto);
            $stmt->bindParam(':metodo', $metodo);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error de BD en pago: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerReportePorMes($tipo, $mes) {
        $partes = explode('-', $mes);
        $anio = $partes[0];
        $mesNum = $partes[1];

        if ($tipo === 'ingresos') {
            $sql = "SELECT ID_PAGO, FACTURA_ID_FACTURA, FECHA_PAGO, MONTO, METODO_PAGO 
                    FROM pago 
                    WHERE MONTH(FECHA_PAGO) = :mes AND YEAR(FECHA_PAGO) = :anio
                    ORDER BY FECHA_PAGO DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':mes', $mesNum);
            $stmt->bindParam(':anio', $anio);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            try {
                $sql = "SELECT e.MES_LIQUIDADO as mes, 
                               e.MONTO_TOTAL_PRODUCCION as produccion, 
                               e.MONTO_PAGADO as total_pagado,
                               CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as doctor
                        FROM egresos e
                        JOIN odontologo o ON e.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                        WHERE e.MES_LIQUIDADO = :mesTexto";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':mesTexto', $mes);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                return []; 
            }
        }
    }

    /**
     * Obtiene los datos completos de una factura individual para exportación.
     * Incluye: datos del paciente, procedimientos, pagos realizados.
     */
    public function obtenerFacturaIndividual($idFactura) {
        // Datos generales de la factura
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
                WHERE f.ID_FACTURA = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idFactura, PDO::PARAM_INT);
        $stmt->execute();
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);

        // Historial de pagos de esta factura
        $sqlPagos = "SELECT ID_PAGO, MONTO, METODO_PAGO, FECHA_PAGO 
                     FROM pago 
                     WHERE FACTURA_ID_FACTURA = :id AND ESTADO = 'PAGADO'
                     ORDER BY FECHA_PAGO ASC";
        $stmtPagos = $this->db->prepare($sqlPagos);
        $stmtPagos->bindParam(':id', $idFactura, PDO::PARAM_INT);
        $stmtPagos->execute();
        $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        return ['factura' => $factura, 'pagos' => $pagos];
    }

    public function obtenerCierreCajaHoy() {
        $sql = "SELECT 
                    IFNULL(SUM(CASE WHEN METODO_PAGO = 'Efectivo' THEN MONTO ELSE 0 END), 0) as efectivo,
                    IFNULL(SUM(CASE WHEN METODO_PAGO = 'Tarjeta' THEN MONTO ELSE 0 END), 0) as tarjeta,
                    IFNULL(SUM(CASE WHEN METODO_PAGO = 'Transferencia' THEN MONTO ELSE 0 END), 0) as transferencia
                FROM pago 
                WHERE DATE(FECHA_PAGO) = CURDATE() AND ESTADO = 'PAGADO'";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarFacturaMasiva($docPaciente, $fechaEmision, $metodoPago, $monto, $total, $estado) {
        try {
            $this->db->beginTransaction();
            $stmtP = $this->db->prepare("SELECT p.ID_PACIENTE FROM paciente p INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE u.NUMERO_DOCUMENTO = ?");
            $stmtP->execute([$docPaciente]);
            $pac = $stmtP->fetch(\PDO::FETCH_ASSOC);
            if (!$pac) throw new \Exception("Paciente no encontrado");

            $sqlF = "INSERT INTO factura (PACIENTE_ID_PACIENTE, FECHA_EMISION, TOTAL, ESTADO) VALUES (?, ?, ?, ?)";
            $stmtF = $this->db->prepare($sqlF);
            $stmtF->execute([$pac['ID_PACIENTE'], $fechaEmision, $total, $estado]);
            $idFactura = $this->db->lastInsertId();

            $sqlP = "INSERT INTO pago (FACTURA_ID_FACTURA, MONTO, METODO_PAGO, FECHA_PAGO, ESTADO) VALUES (?, ?, ?, ?, ?)";
            $stmtP2 = $this->db->prepare($sqlP);
            $stmtP2->execute([$idFactura, $monto, $metodoPago, $fechaEmision, $estado === 'PAGADA' ? 'PAGADO' : 'PENDIENTE']);
            $this->db->commit();

            // Enviar notificaciones de pago o abono
            if ($monto > 0) {
                $monto_fmt = number_format($monto, 2, ',', '.');
                $tratamiento = 'Carga Masiva';
                if ($estado === 'PAGADA') {
                    \App\Helpers\Notificador::enviarAAdmin(23, "El paciente ha cancelado la totalidad de la factura #{$idFactura} por $$monto_fmt.");
                    \App\Helpers\Notificador::enviarAPaciente($pac['ID_PACIENTE'], 5, "Hemos recibido tu pago total de $$monto_fmt por concepto de {$tratamiento}. Gracias!");
                } else {
                    \App\Helpers\Notificador::enviarAAdmin(20, "El paciente hizo un abono de $$monto_fmt a la factura #{$idFactura}.");
                    \App\Helpers\Notificador::enviarAPaciente($pac['ID_PACIENTE'], 19, "Hemos recibido tu abono de $$monto_fmt por concepto de {$tratamiento}. Gracias!");
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}