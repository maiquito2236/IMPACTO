<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\AgendaModel;
use Exception;

class AgendaController {

    private $modelo;

    public function __construct() {
        $this->modelo = new AgendaModel();
    }

    public function obtenerCitas() {
        header('Content-Type: application/json; charset=utf-8');
        $inicio = $_GET['inicio'] ?? null;
        $fin = $_GET['fin'] ?? null;

        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $odontologo_id = $_SESSION['odontologo_id'] ?? null;
        if (!$odontologo_id && isset($_SESSION['usuario_id'])) {
            $authModel = new \App\Models\AuthModel();
            $odoData = $authModel->obtenerOdontologoPorUsuario($_SESSION['usuario_id']);
            $odontologo_id = $odoData ? $odoData['ID_ODONTOLOGO'] : null;
            $_SESSION['odontologo_id'] = $odontologo_id;
        }

        $citas = $this->modelo->listarCitas($inicio, $fin, $odontologo_id);
        echo json_encode($citas, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function obtenerCitasHoy() {
        header('Content-Type: application/json; charset=utf-8');
        
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $odontologo_id = $_SESSION['odontologo_id'] ?? null;
        if (!$odontologo_id && isset($_SESSION['usuario_id'])) {
            $authModel = new \App\Models\AuthModel();
            $odoData = $authModel->obtenerOdontologoPorUsuario($_SESSION['usuario_id']);
            $odontologo_id = $odoData ? $odoData['ID_ODONTOLOGO'] : null;
            $_SESSION['odontologo_id'] = $odontologo_id;
        }

        $citas = $this->modelo->listarCitasHoy($odontologo_id);
        echo json_encode($citas, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function obtenerCitasMes() {
        header('Content-Type: application/json; charset=utf-8');
        $mes = $_GET['mes'] ?? null;
        $anio = $_GET['anio'] ?? null;
        
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $odontologo_id = $_SESSION['odontologo_id'] ?? null;
        if (!$odontologo_id && isset($_SESSION['usuario_id'])) {
            $authModel = new \App\Models\AuthModel();
            $odoData = $authModel->obtenerOdontologoPorUsuario($_SESSION['usuario_id']);
            $odontologo_id = $odoData ? $odoData['ID_ODONTOLOGO'] : null;
            $_SESSION['odontologo_id'] = $odontologo_id;
        }

        $citas = $this->modelo->listarCitasMes($mes, $anio, $odontologo_id);
        echo json_encode($citas, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function obtenerHorasOcupadas() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            if (!isset($_GET['fecha'])) throw new Exception("Falta el parámetro 'fecha'.");
            $fecha = $_GET['fecha'];
            $id_odontologo = $_SESSION['odontologo_id'] ?? $_GET['id_odontologo'] ?? null;

            $ocupadas = $this->modelo->obtenerHorasOcupadas($fecha, $id_odontologo);
            echo json_encode($ocupadas);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        exit;
    }

    public function reprogramarCita() {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['id_cita'], $data['nueva_fecha_hora'])) {
            $id_cita = $data['id_cita'];
            $nueva_fecha_hora = $data['nueva_fecha_hora'];
            $motivo_texto = $data['motivo'] ?? 'Sin motivo específico';
            $nota_reprogramacion = " | Reprogramada: " . $motivo_texto;

            $fecha_formateada = date('d/m/Y a las h:i A', strtotime($nueva_fecha_hora));
            $mensajeNotificacion = "Se reprogramó una cita para el " . $fecha_formateada . ". Motivo: " . $motivo_texto;

            try {
                $this->modelo->registrarReprogramacion($id_cita, $nueva_fecha_hora, $nota_reprogramacion, $mensajeNotificacion);
                echo json_encode(["status" => "success", "message" => "Cita actualizada y notificación creada"]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Faltan datos para actualizar"]);
        }
        exit;
    }

    /* ==========================================================================
       MÉTODO ÚNICO: OBTENER LISTA DE PROCEDIMIENTOS
       ========================================================================== */
    public function obtenerListaProcedimientos() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $procedimientos = $this->modelo->obtenerProcedimientos();
            echo json_encode($procedimientos, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error al cargar procedimientos: " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function obtenerDetalleCita() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_cita = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($id_cita === 0) {
                echo json_encode(null);
                exit;
            }

            $detalle = $this->modelo->obtenerDetalleCita($id_cita);
            echo json_encode($detalle ? $detalle : null, JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error al obtener detalle de la cita: " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function obtenerInfoCancelacion() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_cita = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id_cita === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
                exit;
            }

            $info = $this->modelo->obtenerInfoCancelacion($id_cita);
            if ($info) {
                echo json_encode(['status' => 'success', 'fecha' => $info['fecha'], 'motivo' => $info['motivo']], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se encontró información de cancelación']);
            }
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function buscarPaciente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $doc = $_GET['doc'] ?? '';
        if (!$doc) {
            echo json_encode(["encontrado" => false]);
            exit;
        }

        $paciente = $this->modelo->buscarPacientePorDocumento($doc);
        if ($paciente) {
            echo json_encode([
                "encontrado" => true,
                "ID_PACIENTE" => $paciente['ID_PACIENTE'],
                "nombre" => $paciente['nombre']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["encontrado" => false]);
        }
        exit;
    }
}