<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class DashboardModel {

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

    // 📅 1. CONTAR CITAS DE HOY (Filtrado correcto por ESTADO_CITA_ID = 1 que es 'Pendiente')
    public function contarCitasHoy($hoy, $idOdontologo) {
        $sql = "SELECT COUNT(*) AS total FROM cita WHERE DATE(FECHA_HORA) = ? AND ESTADO_CITA_ID = 1 AND ODONTOLOGO_ID_ODONTOLOGO = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy, $idOdontologo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['total'] : 0;
    }

    // 📅 2. OBTENER PRÓXIMA CITA
    public function obtenerProximaCita($hoy, $idOdontologo) {
        $sql = "SELECT TIME_FORMAT(FECHA_HORA, '%h:%i %p') AS hora 
                FROM cita 
                WHERE DATE(FECHA_HORA) = ? AND FECHA_HORA >= NOW() AND ESTADO_CITA_ID = 1 AND ODONTOLOGO_ID_ODONTOLOGO = ?
                ORDER BY FECHA_HORA ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy, $idOdontologo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['hora'] : 'No hay más citas';
    }

    // 👤 3. PACIENTES ACTIVOS
    public function contarPacientesActivos($idOdontologo) {
        $sql = "SELECT COUNT(DISTINCT p.ID_PACIENTE) AS total 
                FROM paciente p
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE u.ESTADO = 'Activo' 
                AND (
                    p.ID_PACIENTE IN (SELECT PACIENTE_ID_PACIENTE FROM cita WHERE ODONTOLOGO_ID_ODONTOLOGO = ? AND ESTADO_CITA_ID IN (1, 2))
                    OR 
                    p.ID_PACIENTE IN (SELECT PACIENTE_ID_PACIENTE FROM historia_clinica WHERE ODONTOLOGO_ID_ODONTOLOGO = ?)
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idOdontologo, $idOdontologo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['total'] : 0;
    }

    // 🦷 4. TRATAMIENTOS REGISTRADOS (HISTORIAS CLÍNICAS)
    public function contarTratamientosRegistrados($idOdontologo) {
        $sql = "SELECT COUNT(*) AS total FROM historia_clinica WHERE ODONTOLOGO_ID_ODONTOLOGO = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idOdontologo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['total'] : 0;
    }

    // 📅 5. AGENDA DE HOY COMPLETA (Con cruce correcto de la tabla estado_cita)
    public function obtenerAgendaHoy($hoy, $idOdontologo) {
        $sql = "SELECT c.ID_CITA, TIME_FORMAT(c.FECHA_HORA, '%h:%i %p') AS hora, c.MOTIVO, ec.NOMBRE_ESTADO AS ESTADO, 
                       CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente_nombre
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                WHERE DATE(c.FECHA_HORA) = ? AND c.ODONTOLOGO_ID_ODONTOLOGO = ?
                ORDER BY c.FECHA_HORA ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy, $idOdontologo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 👤 6. PACIENTES RECIENTES
    public function obtenerPacientesRecientes($idOdontologo) {
        $sql = "SELECT p.ID_PACIENTE, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente_nombre, u.TELEFONO, u.FECHA_NACIMIENTO, 
                       GROUP_CONCAT(DISTINCT proc.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS MOTIVO_CONSULTA, 
                       DATE_FORMAT(MAX(hc.FECHA_REGISTRO), '%d %b, %Y') AS fecha
                FROM historia_clinica hc
                INNER JOIN paciente p ON hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN historia_clinica_has_procedimientos hcp ON hc.ID_HISTORIA_CLINICA = hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                LEFT JOIN procedimientos proc ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = proc.ID_PROCEDIMIENTO
                WHERE hc.ODONTOLOGO_ID_ODONTOLOGO = ?
                GROUP BY p.ID_PACIENTE, DATE_FORMAT(hc.FECHA_REGISTRO, '%Y-%m-%d')
                ORDER BY MAX(hc.FECHA_REGISTRO) DESC LIMIT 2";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idOdontologo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 💰 7. INGRESOS POR RANGO (Tu tabla pago sí tiene la columna ESTADO como ENUM)
    public function obtenerIngresosPorRango($inicio, $fin) {
        $sql = "SELECT IFNULL(SUM(MONTO), 0) AS total FROM pago WHERE DATE(FECHA_PAGO) BETWEEN ? AND ? AND UPPER(ESTADO) = 'PAGADO'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inicio, $fin]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (float)$res['total'] : 0.0;
    }

    // 📊 8. RESUMEN DE TRATAMIENTOS DINÁMICO
    public function obtenerResumenTratamientos($idOdontologo) {
        $sqlTotal = "SELECT COUNT(*) AS total FROM historia_clinica_has_procedimientos hcp
                     INNER JOIN historia_clinica hc ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                     WHERE hc.ODONTOLOGO_ID_ODONTOLOGO = ?";
        $stmtTotal = $this->db->prepare($sqlTotal);
        $stmtTotal->execute([$idOdontologo]);
        $resTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        $totalProcedimientos = ($resTotal && $resTotal['total'] > 0) ? (int)$resTotal['total'] : 1;

        $sql = "SELECT p.NOMBRE_PROCEDIMIENTO, COUNT(hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO) AS cantidad 
                FROM historia_clinica_has_procedimientos hchp
                INNER JOIN procedimientos p ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                INNER JOIN historia_clinica hc ON hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                WHERE p.NOMBRE_PROCEDIMIENTO IS NOT NULL AND p.NOMBRE_PROCEDIMIENTO != '' AND hc.ODONTOLOGO_ID_ODONTOLOGO = ?
                GROUP BY p.ID_PROCEDIMIENTO, p.NOMBRE_PROCEDIMIENTO 
                ORDER BY cantidad DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idOdontologo]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tratamientosDinamicos = [];
        
        foreach ($result as $row) {
            $porcentaje = round(($row['cantidad'] / $totalProcedimientos) * 100);
            if ($porcentaje > 0) {
                $tratamientosDinamicos[] = [
                    'nombre' => $row['NOMBRE_PROCEDIMIENTO'],
                    'porcentaje' => $porcentaje
                ];
            }
        }
        return $tratamientosDinamicos;
    }

    // 🔔 9. ALERTAS Y RECORDATORIOS (Tu tabla notificaciones sí tiene la columna ESTADO como ENUM)
    public function obtenerAlertas($idUsuario) {
        $sql = "SELECT n.ID_NOTIFICACIONES, n.MENSAJE, n.FECHA_ENVIO, n.ESTADO, tn.NOMBRE AS TIPO_NOMBRE
                FROM notificaciones n
                INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                WHERE n.ESTADO = 'NO_LEIDA' AND n.USUARIOS_ID_USUARIOS = ?
                ORDER BY n.FECHA_ENVIO DESC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}