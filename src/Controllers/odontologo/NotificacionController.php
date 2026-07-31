<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\NotificacionModel;
use Exception;

class NotificacionController {

    private $modelo;

    public function __construct() {
        $this->modelo = new NotificacionModel();
    }

    /**
     * Renderiza la vista principal de notificaciones del odontólogo.
     * action = odontologo/notificaciones
     */
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /LOGIN_ORIGINAL/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $notificaciones = $this->modelo->obtenerPorUsuario($usuarioId);
        $totalAlertas   = count($notificaciones);
        $sinLeer        = count(array_filter($notificaciones, fn($n) => $n['ESTADO'] === 'NO_LEIDA'));
        $tiposNotificacion = $this->modelo->obtenerTiposNotificacion();

        require_once __DIR__ . '/../../Views/odontologo/notificaciones_odon.php';
    }

    /**
     * Devuelve todas las notificaciones en JSON.
     * action = odontologo/notificaciones/listar
     */
    public function listar(): void {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $usuarioId = $_SESSION['usuario_id'] ?? 0;
        
        try {
            $datos = $this->modelo->obtenerPorUsuario($usuarioId);
            echo json_encode(['success' => true, 'data' => $datos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Marca UNA notificación como leída (AJAX POST, campo: id).
     * action = odontologo/notificaciones/marcar_leida
     */
    public function marcarComoLeida(): void {
        header('Content-Type: application/json');
        ini_set('display_errors', 0);
        $response = ['success' => false, 'error' => ''];

        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $usuarioId = $_SESSION['usuario_id'] ?? 0;

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                $id = intval($_POST['id']);
                $response['success'] = $this->modelo->marcarUnaComoLeida($id, $usuarioId);
            } else {
                $response['error'] = 'Petición inválida.';
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Marca TODAS las notificaciones como leídas (AJAX POST).
     * action = odontologo/notificaciones/marcar_todas
     */
    public function marcarTodas(): void {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $usuarioId = $_SESSION['usuario_id'] ?? 0;

        try {
            $ok = $this->modelo->marcarTodasComoLeidas($usuarioId);
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}