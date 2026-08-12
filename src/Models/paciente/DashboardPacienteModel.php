<?php
namespace App\Models\paciente;

use App\Config\Database;
use PDO;

class DashboardPacienteModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene la próxima cita pendiente del paciente.
     */
    public function obtenerProximaCita($idPaciente) {
        $sql = "SELECT 
                    c.ID_CITA,
                    c.FECHA_HORA,
                    c.MOTIVO,
                    ec.NOMBRE_ESTADO AS estado,
                    CONCAT(uo.NOMBRES, ' ', uo.APELLIDOS) AS odontologo,
                    esp.NOMBRE_ESPECIALIDAD AS especialidad,
                    co.NOMBRE AS consultorio,
                    p.NOMBRE_PROCEDIMIENTO AS procedimiento
                FROM cita c
                JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                JOIN usuarios uo ON o.USUARIOS_ID_USUARIOS = uo.ID_USUARIOS
                LEFT JOIN especialidad esp ON o.ESPECIALIDAD_ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                LEFT JOIN consultorio co ON o.CONSULTORIO_ID_CONSULTORIO = co.ID_CONSULTORIO
                LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                LEFT JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                WHERE c.PACIENTE_ID_PACIENTE = :id_paciente
                  AND c.ESTADO_CITA_ID = 1
                  AND c.FECHA_HORA >= NOW()
                ORDER BY c.FECHA_HORA ASC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene los tratamientos (procedimientos) del paciente con su estado más reciente.
     */
    public function obtenerResumenTratamientos($idPaciente) {
        $sql = "SELECT nombre, estado FROM (
                    SELECT 
                        p.NOMBRE_PROCEDIMIENTO AS nombre,
                        'Completada' AS estado,
                        hc.FECHA_REGISTRO AS fecha_ord
                    FROM historia_clinica hc
                    INNER JOIN historia_clinica_has_procedimientos hcp ON hc.ID_HISTORIA_CLINICA = hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                    INNER JOIN procedimientos p ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                    WHERE hc.PACIENTE_ID_PACIENTE = :id_paciente
                    
                    UNION ALL
                    
                    SELECT 
                        p.NOMBRE_PROCEDIMIENTO AS nombre,
                        ec.NOMBRE_ESTADO AS estado,
                        c.FECHA_HORA AS fecha_ord
                    FROM cita c
                    JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                    JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                    JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                    WHERE c.PACIENTE_ID_PACIENTE = :id_paciente2
                ) AS temp
                ORDER BY fecha_ord DESC
                LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->bindParam(':id_paciente2', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el monto total de pagos pendientes del paciente.
     */
    public function obtenerPagosPendientes($idPaciente) {
        $sql = "
            SELECT 
                (
                    COALESCE((
                        SELECT SUM(f.TOTAL)
                        FROM factura f
                        INNER JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                        WHERE hc.PACIENTE_ID_PACIENTE = :id_paciente
                    ), 0) - 
                    COALESCE((
                        SELECT SUM(pg.MONTO)
                        FROM pago pg
                        INNER JOIN factura f2 ON pg.FACTURA_ID_FACTURA = f2.ID_FACTURA
                        INNER JOIN historia_clinica hc2 ON f2.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc2.ID_HISTORIA_CLINICA
                        WHERE hc2.PACIENTE_ID_PACIENTE = :id_paciente
                    ), 0)
                ) AS deuda_total
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['deuda_total'] : 0;
    }

    /**
     * Obtiene las últimas N citas del paciente.
     */
    public function obtenerCitasRecientes($idPaciente, $limit = 5) {
        $sql = "SELECT 
                    c.ID_CITA,
                    c.FECHA_HORA,
                    c.MOTIVO,
                    ec.NOMBRE_ESTADO AS estado,
                    CONCAT(uo.NOMBRES, ' ', uo.APELLIDOS) AS odontologo,
                    esp.NOMBRE_ESPECIALIDAD AS especialidad,
                    p.NOMBRE_PROCEDIMIENTO AS procedimiento
                FROM cita c
                JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                JOIN usuarios uo ON o.USUARIOS_ID_USUARIOS = uo.ID_USUARIOS
                LEFT JOIN especialidad esp ON o.ESPECIALIDAD_ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                LEFT JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                WHERE c.PACIENTE_ID_PACIENTE = :id_paciente
                ORDER BY c.FECHA_HORA DESC
                LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los últimos pagos/facturas del paciente.
     */
    public function obtenerUltimosPagos($idPaciente, $limit = 2) {
        $sql = "SELECT 
                    f.ID_FACTURA,
                    f.FECHA_EMISION,
                    f.TOTAL,
                    f.ESTADO,
                    pa.MONTO AS monto_pagado,
                    pa.ESTADO AS estado_pago
                FROM factura f
                LEFT JOIN pago pa ON f.ID_FACTURA = pa.FACTURA_ID_FACTURA
                INNER JOIN historia_clinica hc ON f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                WHERE hc.PACIENTE_ID_PACIENTE = :id_paciente
                ORDER BY f.FECHA_EMISION DESC
                LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene TODOS los tratamientos del paciente para la vista de Tratamientos.
     * Agrupa por procedimiento y muestra la última cita asociada.
     */
    public function obtenerTratamientosCompletos($idPaciente) {
        $sql = "SELECT 
                    ID_PROCEDIMIENTO,
                    nombre,
                    descripcion,
                    estado,
                    fecha_inicio,
                    odontologo,
                    especialidad,
                    proxima_cita
                FROM (
                    SELECT DISTINCT
                        p.ID_PROCEDIMIENTO,
                        p.NOMBRE_PROCEDIMIENTO AS nombre,
                        p.DESCRIPCION AS descripcion,
                        'Completada' AS estado,
                        hc.FECHA_REGISTRO AS fecha_inicio,
                        CONCAT(uo.NOMBRES, ' ', uo.APELLIDOS) AS odontologo,
                        esp.NOMBRE_ESPECIALIDAD AS especialidad,
                        NULL AS proxima_cita
                    FROM historia_clinica hc
                    INNER JOIN historia_clinica_has_procedimientos hchp ON hc.ID_HISTORIA_CLINICA = hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                    INNER JOIN procedimientos p ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                    INNER JOIN odontologo o ON hc.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                    INNER JOIN usuarios uo ON o.USUARIOS_ID_USUARIOS = uo.ID_USUARIOS
                    LEFT JOIN especialidad esp ON o.ESPECIALIDAD_ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                    WHERE hc.PACIENTE_ID_PACIENTE = :id_paciente
                    
                    UNION ALL
                    
                    SELECT DISTINCT
                        p.ID_PROCEDIMIENTO,
                        p.NOMBRE_PROCEDIMIENTO AS nombre,
                        p.DESCRIPCION AS descripcion,
                        ec.NOMBRE_ESTADO AS estado,
                        c.FECHA_HORA AS fecha_inicio,
                        CONCAT(uo.NOMBRES, ' ', uo.APELLIDOS) AS odontologo,
                        esp.NOMBRE_ESPECIALIDAD AS especialidad,
                        (SELECT c2.FECHA_HORA 
                         FROM cita c2 
                         JOIN cita_has_procedimiento chp2 ON c2.ID_CITA = chp2.CITA_ID_CITA
                         WHERE chp2.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                           AND c2.PACIENTE_ID_PACIENTE = :id_paciente2
                           AND c2.ESTADO_CITA_ID = 1
                           AND c2.FECHA_HORA >= NOW()
                         ORDER BY c2.FECHA_HORA ASC
                         LIMIT 1
                        ) AS proxima_cita
                    FROM cita c
                    JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                    JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                    JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                    JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                    JOIN usuarios uo ON o.USUARIOS_ID_USUARIOS = uo.ID_USUARIOS
                    LEFT JOIN especialidad esp ON o.ESPECIALIDAD_ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                    WHERE c.PACIENTE_ID_PACIENTE = :id_paciente3
                ) AS temp
                ORDER BY fecha_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->bindParam(':id_paciente2', $idPaciente, PDO::PARAM_INT);
        $stmt->bindParam(':id_paciente3', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene conteos para KPIs de tratamientos.
     */
    public function obtenerKPIsTratamientos($idPaciente) {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) AS completados,
                    SUM(CASE WHEN estado IN ('Cancelada','No asistió') THEN 1 ELSE 0 END) AS cancelados
                FROM (
                    SELECT 'Completada' AS estado FROM historia_clinica hc
                    INNER JOIN historia_clinica_has_procedimientos hcp ON hc.ID_HISTORIA_CLINICA = hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                    WHERE hc.PACIENTE_ID_PACIENTE = :id_paciente
                    
                    UNION ALL
                    
                    SELECT ec.NOMBRE_ESTADO AS estado FROM cita c
                    JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                    JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                    WHERE c.PACIENTE_ID_PACIENTE = :id_paciente2
                ) as temp";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->bindParam(':id_paciente2', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el ID de paciente a partir del ID de usuario.
     */
    public function obtenerIdPaciente($idUsuario) {
        $sql = "SELECT ID_PACIENTE FROM paciente WHERE USUARIOS_ID_USUARIOS = :id_usuario LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['ID_PACIENTE'] : null;
    }
}
