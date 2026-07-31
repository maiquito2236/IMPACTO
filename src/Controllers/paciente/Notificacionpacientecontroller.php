<?php
namespace App\Controllers\paciente;

use App\Config\Database;
use PDO;

class NotificacionPacienteController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $db = Database::getInstance()->getConnection();
        
        // Obtener notificaciones
        $stmt = $db->prepare("
            SELECT n.ID_NOTIFICACIONES, n.MENSAJE, n.FECHA_ENVIO, n.ESTADO, tn.NOMBRE as TIPO_NOMBRE
            FROM notificaciones n
            INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
            WHERE n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 3)
            ORDER BY n.FECHA_ENVIO DESC
        ");
        $stmt->execute([$usuarioId]);
        $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalAlertas = count($notificaciones);
        $sinLeer = 0;
        $leidas = 0;

        foreach ($notificaciones as $n) {
            if ($n['ESTADO'] === 'NO_LEIDA') {
                $sinLeer++;
            } else {
                $leidas++;
            }
        }

        // Obtener tipos únicos de notificaciones para el paciente (ROL 3)
        $stmtTipos = $db->prepare("SELECT DISTINCT NOMBRE FROM tipo_notificacion WHERE ROL_DESTINO = 3 AND ESTADO = 'ACTIVO' ORDER BY NOMBRE ASC");
        $stmtTipos->execute();
        $tiposNotificacion = $stmtTipos->fetchAll(PDO::FETCH_COLUMN);

        require_once __DIR__ . '/../../Views/paciente/notificaciones.php';
    }

    public function marcarLeida() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id']) || !isset($_POST['id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $id = intval($_POST['id']);
        $usuarioId = $_SESSION['usuario_id'];
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notificaciones SET ESTADO = 'LEIDA' WHERE ID_NOTIFICACIONES = ? AND (USUARIOS_ID_USUARIOS = ? OR USUARIOS_ID_USUARIOS IS NULL)");
        $success = $stmt->execute([$id, $usuarioId]);

        echo json_encode(['success' => $success]);
        exit;
    }

    public function marcarTodas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE notificaciones n
            INNER JOIN tipo_notificacion tn ON n.TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION = tn.ID_TIPO_NOTIFICACION
            SET n.ESTADO = 'LEIDA' 
            WHERE n.ESTADO = 'NO_LEIDA' 
              AND (n.USUARIOS_ID_USUARIOS = ? OR (n.USUARIOS_ID_USUARIOS IS NULL AND tn.ROL_DESTINO = 3))
        ");
        $success = $stmt->execute([$usuarioId]);

        echo json_encode(['success' => $success]);
        exit;
    }
}