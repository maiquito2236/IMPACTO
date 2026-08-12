<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class NotificacionModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // 🔔 Traer todas las notificaciones reales del sistema para este odontólogo
    public function obtenerPorUsuario(int $usuarioId) {
        $sql = "SELECT n.ID_NOTIFICACIONES, n.MENSAJE, n.FECHA_ENVIO, n.ESTADO, 
                       tn.NOMBRE AS TIPO_NOMBRE
                FROM notificaciones n
                INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                WHERE n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 2)
                ORDER BY n.FECHA_ENVIO DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔔 Marcar todas las notificaciones como leídas de un solo golpe para este usuario
    public function marcarTodasComoLeidas(int $usuarioId) {
        $sql = "UPDATE notificaciones n
                INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                SET n.ESTADO = 'LEIDA' 
                WHERE n.ESTADO = 'NO_LEIDA' 
                  AND (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 2))";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }

    // 🔔 Marcar una notificación como leída
    public function marcarUnaComoLeida(int $id, int $usuarioId): bool {
        $stmt = $this->db->prepare(
            "UPDATE notificaciones 
             SET ESTADO = 'LEIDA' 
             WHERE ID_NOTIFICACIONES = ? 
               AND (USUARIOS_ID_USUARIOS = ? OR USUARIOS_ID_USUARIOS IS NULL)"
        );
        return $stmt->execute([$id, $usuarioId]);
    }

    // 🔔 Obtener tipos de notificación para el odontólogo
    public function obtenerTiposNotificacion(): array {
        $sql = "SELECT DISTINCT NOMBRE 
                FROM tipo_notificacion 
                WHERE ROL_DESTINO = 2 AND ESTADO = 'ACTIVO' 
                ORDER BY NOMBRE ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}