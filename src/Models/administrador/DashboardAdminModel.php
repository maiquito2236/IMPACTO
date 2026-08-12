<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;

class DashboardAdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // 1. Contar Citas Programadas
    public function contarCitasHoy($hoy) {
        $sql = "SELECT COUNT(*) AS total FROM cita WHERE DATE(FECHA_HORA) = ? AND ESTADO_CITA_ID IN (1, 2)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['total'] : 0;
    }

    // 2. Contar Pacientes Atendidos Hoy (aquellos con cita Completada o con historia clinica creada hoy)
    public function contarPacientesAtendidosHoy($hoy) {
        $sql = "SELECT COUNT(*) AS total FROM cita WHERE DATE(FECHA_HORA) = ? AND ESTADO_CITA_ID = 2"; // 2 = Completada
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['total'] : 0;
    }

    // 3. Contar Tratamientos Realizados Hoy
    public function contarTratamientosHoy($hoy) {
        $sql = "SELECT COUNT(*) AS total FROM historia_clinica WHERE DATE(FECHA_REGISTRO) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['total'] : 0;
    }

    // 4. Ingresos del día
    public function obtenerIngresosHoy($hoy) {
        $sql = "SELECT IFNULL(SUM(MONTO), 0) AS total FROM pago WHERE DATE(FECHA_PAGO) = ? AND UPPER(ESTADO) = 'PAGADO'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (float)$res['total'] : 0.0;
    }

    // 5. Próximas Citas
    public function obtenerProximasCitas($hoy, $limite = 4) {
        $sql = "SELECT c.ID_CITA, DATE_FORMAT(c.FECHA_HORA, '%d %b') AS fecha_corta, TIME_FORMAT(c.FECHA_HORA, '%h:%i %p') AS hora, c.MOTIVO, ec.NOMBRE_ESTADO AS ESTADO, 
                       CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente_nombre
                FROM cita c
                INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                WHERE c.FECHA_HORA >= NOW() AND c.ESTADO_CITA_ID IN (1, 2)
                ORDER BY c.FECHA_HORA ASC LIMIT " . (int)$limite;
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. Recordatorios y Alertas
    public function obtenerRecordatorios($limite = 4) {
        $sql = "SELECT n.ID_NOTIFICACIONES, n.MENSAJE, n.FECHA_ENVIO, n.ESTADO, tn.NOMBRE AS TIPO_NOMBRE
                FROM notificaciones n
                INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                WHERE n.ESTADO = 'NO_LEIDA'
                ORDER BY n.FECHA_ENVIO DESC LIMIT " . (int)$limite;
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. Resumen de Estado de Citas para Gráfico Donut
    public function obtenerEstadoCitasHoy($hoy) {
        $sql = "SELECT ec.NOMBRE_ESTADO, COUNT(c.ID_CITA) AS cantidad
                FROM cita c
                INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                WHERE DATE(c.FECHA_HORA) = ?
                GROUP BY ec.NOMBRE_ESTADO";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoy]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Actividad de la clínica (últimos 7 días)
    public function obtenerActividadUltimos7Dias($hoy) {
        $fechaInicio = date('Y-m-d', strtotime('-6 days', strtotime($hoy)));
        
        $sqlCitas = "SELECT DATE(FECHA_HORA) as fecha, COUNT(*) as cantidad FROM cita WHERE DATE(FECHA_HORA) BETWEEN ? AND ? AND ESTADO_CITA_ID IN (1, 2) GROUP BY DATE(FECHA_HORA)";
        $stmt = $this->db->prepare($sqlCitas);
        $stmt->execute([$fechaInicio, $hoy]);
        $resCitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlPacientes = "SELECT DATE(FECHA_HORA) as fecha, COUNT(*) as cantidad FROM cita WHERE DATE(FECHA_HORA) BETWEEN ? AND ? AND ESTADO_CITA_ID = 2 GROUP BY DATE(FECHA_HORA)";
        $stmt = $this->db->prepare($sqlPacientes);
        $stmt->execute([$fechaInicio, $hoy]);
        $resPacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlIngresos = "SELECT DATE(FECHA_PAGO) as fecha, SUM(MONTO) as cantidad FROM pago WHERE DATE(FECHA_PAGO) BETWEEN ? AND ? AND UPPER(ESTADO) = 'PAGADO' GROUP BY DATE(FECHA_PAGO)";
        $stmt = $this->db->prepare($sqlIngresos);
        $stmt->execute([$fechaInicio, $hoy]);
        $resIngresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['citas' => $resCitas, 'pacientes' => $resPacientes, 'ingresos' => $resIngresos, 'inicio' => $fechaInicio];
    }
}
