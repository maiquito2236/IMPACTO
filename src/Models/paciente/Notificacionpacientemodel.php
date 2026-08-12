<?php
namespace App\Models\paciente;

use App\Config\Database;
use PDO;

class NotificacionPacienteModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Tipo de notificación exclusivo de uso interno (resumen para el
     * odontólogo/admin) que no es relevante mostrarle a un paciente.
     */
    private const TIPO_OCULTO_PARA_PACIENTE = 'ALERTA DE AGENDA DIARIA';

    /**
     * Trae SOLO las notificaciones del paciente autenticado, excluyendo
     * los tipos de uso interno del consultorio.
     */
    public function obtenerPorUsuario(int $usuarioId): array {
        $sql = "SELECT
                    n.ID_NOTIFICACIONES,
                    n.MENSAJE,
                    n.FECHA_ENVIO,
                    n.ESTADO,
                    tn.NOMBRE AS TIPO_NOMBRE
                FROM notificaciones n
                INNER JOIN tipo_notificacion tn
                        ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
                WHERE (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 3))
                  AND tn.NOMBRE <> ?
                ORDER BY n.FECHA_ENVIO DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId, self::TIPO_OCULTO_PARA_PACIENTE]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca como leída UNA notificación, validando que pertenezca al usuario.
     */
    public function marcarUnaComoLeida(int $id, int $usuarioId): bool {
        $stmt = $this->db->prepare(
            "UPDATE notificaciones
                SET ESTADO = 'LEIDA'
              WHERE ID_NOTIFICACIONES = ?
                AND (USUARIOS_ID_USUARIOS = ? OR USUARIOS_ID_USUARIOS IS NULL)"
        );
        return $stmt->execute([$id, $usuarioId]);
    }

    /**
     * Marca como leídas TODAS las notificaciones pendientes del paciente.
     */
    public function marcarTodasComoLeidas(int $usuarioId): bool {
        $stmt = $this->db->prepare(
            "UPDATE notificaciones n
             INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
             SET n.ESTADO = 'LEIDA'
             WHERE n.ESTADO = 'NO_LEIDA'
               AND (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 3))"
        );
        return $stmt->execute([$usuarioId]);
    }
}