<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class AgendaModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarCitas($inicio = null, $fin = null, $odontologo_id = null) {
        $whereClause = "";
        $params = [];
        $conditions = [];

        if ($inicio && $fin) {
            $conditions[] = "DATE(c.FECHA_HORA) BETWEEN ? AND ?";
            $params[] = $inicio;
            $params[] = $fin;
        }

        if ($odontologo_id) {
            $conditions[] = "c.ODONTOLOGO_ID_ODONTOLOGO = ?";
            $params[] = $odontologo_id;
        }

        if (count($conditions) > 0) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }

        $sql = "SELECT c.ID_CITA AS id_cita, DATE(c.FECHA_HORA) AS fecha_cita, TIME(c.FECHA_HORA) AS hora_cita,
                       HOUR(c.FECHA_HORA) AS hora_num, c.ESTADO_CITA_ID AS estado, c.MOTIVO AS tratamiento,
                       CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS) AS paciente, CONCAT(u_odo.NOMBRES, ' ', u_odo.APELLIDOS) AS odontologo
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u_pac ON p.USUARIOS_ID_USUARIOS = u_pac.ID_USUARIOS
                INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN usuarios u_odo ON o.USUARIOS_ID_USUARIOS = u_odo.ID_USUARIOS
                $whereClause 
                ORDER BY c.FECHA_HORA ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCitasHoy($odontologo_id = null) {
        $params = [];
        $whereClause = "WHERE DATE(c.FECHA_HORA) = CURDATE()";
        if ($odontologo_id) {
            $whereClause .= " AND c.ODONTOLOGO_ID_ODONTOLOGO = ?";
            $params[] = $odontologo_id;
        }

        $sql = "SELECT c.ID_CITA AS id_cita, u.NUMERO_DOCUMENTO AS documento, DATE(c.FECHA_HORA) AS fecha_cita,
                       TIME_FORMAT(c.FECHA_HORA, '%H:%i') AS hora_cita, c.ESTADO_CITA_ID AS estado, c.MOTIVO AS tratamiento,
                       CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                $whereClause 
                ORDER BY c.FECHA_HORA ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCitasMes($mes = null, $anio = null, $odontologo_id = null) {
        $mes = $mes ?: date('m');
        $anio = $anio ?: date('Y');

        $params = [$mes, $anio];
        $whereClause = "WHERE MONTH(c.FECHA_HORA) = ? AND YEAR(c.FECHA_HORA) = ?";

        if ($odontologo_id) {
            $whereClause .= " AND c.ODONTOLOGO_ID_ODONTOLOGO = ?";
            $params[] = $odontologo_id;
        }

        $sql = "SELECT c.ID_CITA AS id, DAY(c.FECHA_HORA) AS dia, TIME_FORMAT(c.FECHA_HORA, '%H:%i') AS hora,
                       c.ESTADO_CITA_ID AS estado, c.MOTIVO AS tratamiento, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS nombre
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                $whereClause
                ORDER BY c.FECHA_HORA ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHorasOcupadas($fecha, $id_odontologo = null) {
        if ($id_odontologo) {
            $query = "SELECT TIME_FORMAT(FECHA_HORA, '%H:%i') as hora FROM cita 
                      WHERE DATE(FECHA_HORA) = ? AND ODONTOLOGO_ID_ODONTOLOGO = ? AND ESTADO_CITA_ID != 3";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$fecha, $id_odontologo]);
        } else {
            $query = "SELECT TIME_FORMAT(FECHA_HORA, '%H:%i') as hora FROM cita 
                      WHERE DATE(FECHA_HORA) = ? AND ESTADO_CITA_ID != 3";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$fecha]);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function registrarReprogramacion($id_cita, $nueva_fecha_hora, $nota_reprogramacion, $mensajeAlerta) {
        $this->db->beginTransaction();
        try {
            $sqlUser = "SELECT u.ID_USUARIOS 
                        FROM cita c 
                        JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE 
                        JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS 
                        WHERE c.ID_CITA = ?";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([$id_cita]);
            $usuario_id = $stmtUser->fetchColumn();

            if (!$usuario_id) $usuario_id = 1; 

            $sql = "UPDATE cita SET FECHA_HORA = ?, ESTADO_CITA_ID = 1, MOTIVO = CONCAT(MOTIVO, ?) WHERE ID_CITA = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nueva_fecha_hora, $nota_reprogramacion, $id_cita]);

            $sqlAlerta = "INSERT INTO notificaciones 
                          (MENSAJE, FECHA_ENVIO, ESTADO, TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION, USUARIOS_ID_USUARIOS) 
                          VALUES (?, NOW(), 'NO_LEIDA', 2, ?)";
            $stmtAlerta = $this->db->prepare($sqlAlerta);
            $stmtAlerta->execute([$mensajeAlerta, $usuario_id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function buscarPacientePorDocumento($doc) {
        $sql = "SELECT p.ID_PACIENTE, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS nombre 
                FROM paciente p
                JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE u.NUMERO_DOCUMENTO = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$doc]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerProcedimientos() {
        // Traemos los 12 datos reales de tu base de datos
        $sql = "SELECT ID_PROCEDIMIENTO, NOMBRE_PROCEDIMIENTO, COSTO, TIEMPO_ESTIMADO, TIPO_COBRO 
                FROM procedimientos 
                WHERE ESTADO = 'ACTIVO' OR ESTADO = 1";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nuevo método para mapear exactamente con las tablas reales de tu BD
    public function obtenerDetalleCita($id_cita) {
        $sql = "SELECT 
                    c.ID_CITA AS id_cita, 
                    u.NUMERO_DOCUMENTO AS documento, 
                    p.ID_PACIENTE AS id_paciente,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente,
                    DATE_FORMAT(c.FECHA_HORA, '%Y-%m-%d') AS fecha_cita,
                    TRIM(c.MOTIVO) AS tratamiento  
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE c.ID_CITA = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_cita]); //  ¡Listo! Con el '$' corregido
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosLosProcedimientosParaAgenda() {
        $sql = "SELECT ID_PROCEDIMIENTO, NOMBRE_PROCEDIMIENTO, 
                       COSTO AS COSTO_OFICIAL, TIEMPO_ESTIMADO AS DURACION_ESTIMADA 
                FROM procedimientos 
                WHERE ESTADO = 1 OR ESTADO = '1' OR ESTADO = 'ACTIVO'"; 
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerInfoCancelacion($id_cita) {
        // Asumiendo que la información se guarda en historial_cita
        $sql = "SELECT DATE_FORMAT(FECHA_HORA, '%d/%m/%Y %H:%i') as fecha, MOTIVO as motivo 
                FROM historial_cita 
                WHERE CITA_ID_CITA = ? AND ESTADO_CITA_ID = 3 
                ORDER BY FECHA_HORA DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_cita]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($res) {
            return $res;
        }

        // Si no está en historial_cita, intentar buscar en cita directamente
        $sqlCita = "SELECT 'Cancelada por el paciente' as motivo, DATE_FORMAT(FECHA_HORA, '%d/%m/%Y') as fecha 
                    FROM cita WHERE ID_CITA = ?";
        $stmtCita = $this->db->prepare($sqlCita);
        $stmtCita->execute([$id_cita]);
        return $stmtCita->fetch(PDO::FETCH_ASSOC);
    }
}