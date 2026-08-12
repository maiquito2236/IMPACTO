<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;

class NotificacionAdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Trae las notificaciones del sistema para el administrador actual
     * o notificaciones globales para el rol Administrador (ROL_DESTINO = 1).
     */
    public function obtenerPorUsuario(int $usuarioId): array {
        // Eliminar automáticamente las notificaciones leídas hace más de 30 días
        $this->db->exec("DELETE FROM notificaciones WHERE ESTADO = 'LEIDA' AND FECHA_LECTURA IS NOT NULL AND FECHA_LECTURA < DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $sql = "SELECT
                    n.ID_NOTIFICACIONES,
                    n.MENSAJE,
                    n.FECHA_ENVIO,
                    n.ESTADO,
                    tn.NOMBRE          AS TIPO_NOMBRE,
                    u.NOMBRES          AS USUARIO_NOMBRES,
                    u.APELLIDOS        AS USUARIO_APELLIDOS
                FROM notificaciones n
                INNER JOIN tipo_notificacion tn
                        ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                LEFT JOIN usuarios u
                        ON n.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE n.USUARIOS_ID_USUARIOS = ?
                   OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 1)
                ORDER BY n.FECHA_ENVIO DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca como leída UNA notificación por su ID para este usuario.
     */
    public function marcarUnaComoLeida(int $id, int $usuarioId): bool {
        $stmt = $this->db->prepare(
            "UPDATE notificaciones 
             SET ESTADO = 'LEIDA', FECHA_LECTURA = NOW() 
             WHERE ID_NOTIFICACIONES = ? 
               AND (USUARIOS_ID_USUARIOS = ? OR USUARIOS_ID_USUARIOS IS NULL)"
        );
        return $stmt->execute([$id, $usuarioId]);
    }

    /**
     * Marca como leídas TODAS las notificaciones pendientes del administrador.
     */
    public function marcarTodasComoLeidas(int $usuarioId): bool {
        $sql = "UPDATE notificaciones n
                INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                SET n.ESTADO = 'LEIDA', n.FECHA_LECTURA = NOW() 
                WHERE n.ESTADO = 'NO_LEIDA' 
                  AND (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 1))";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }

    /**
     * Obtiene todos los tipos de notificación activos orientados al Administrador.
     */
    public function obtenerTiposNotificacion(): array {
        $sql = "SELECT DISTINCT NOMBRE 
                FROM tipo_notificacion 
                WHERE ROL_DESTINO = 1 AND ESTADO = 'ACTIVO' 
                ORDER BY NOMBRE ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}