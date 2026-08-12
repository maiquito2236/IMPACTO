<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class PlanestratamientoModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
public function obtenerDetalleCitaPaciente($id_cita) {
    // Usamos ALIAS (AS) para que concuerde exactamente con lo que extrae tu controlador
    $sql = "SELECT 
                p.ID_PACIENTE AS id_paciente,
                CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente_nombre,
                u.NUMERO_DOCUMENTO AS documento,
                c.MOTIVO AS tratamiento_motivo
            FROM cita c
            INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
            INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
            WHERE c.ID_CITA = ?";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id_cita]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function obtenerProcedimientosHistoriaPaciente($id_paciente, $odontologo_id = null) {
    $sql = "SELECT 
                hc.ID_HISTORIA_CLINICA,
                hc.FECHA_REGISTRO,
                p.ID_PROCEDIMIENTO,
                p.NOMBRE_PROCEDIMIENTO,
                '' AS AMBITO,
                '' AS TIPO_EVOLUCION,
                IF(p.TIEMPO_ESTIMADO > 40, 'EVOLUCION_FASES', 'SESION_UNICA') AS TIPO_SEGUIMIENTO,
                hchp.PRECIO_APLICADO,
                hchp.CANTIDAD,
                IF(hc.TRATAMIENTO IS NULL OR hc.TRATAMIENTO = '', 'Gral', hc.TRATAMIENTO) AS PIEZA_DENTAL,
                hc.DIAGNOSTICO AS NOTAS,
                IF(hchp.PRECIO_APLICADO > 0, 'Hecho', 'Pendiente') AS ESTADO_FASE
            FROM historia_clinica hc
            INNER JOIN historia_clinica_has_procedimientos hchp 
                ON hc.ID_HISTORIA_CLINICA = hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
            INNER JOIN procedimientos p 
                ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
            WHERE hc.PACIENTE_ID_PACIENTE = ? ";
            
    if ($odontologo_id !== null) {
        $sql .= " AND hc.ODONTOLOGO_ID_ODONTOLOGO = ? ";
    }
            
    $sql .= " ORDER BY hc.ID_HISTORIA_CLINICA ASC";

    $stmt = $this->db->prepare($sql);
    
    if ($odontologo_id !== null) {
        $stmt->execute([$id_paciente, $odontologo_id]);
    } else {
        $stmt->execute([$id_paciente]);
    }
    
    // Retornamos el array puro de registros
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerPiezasOdontograma($id_paciente) {
    $sql = "SELECT DISTINCT PIEZA_DENTAL FROM odontograma_tratamientos WHERE PACIENTE_ID_PACIENTE = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id_paciente]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

}