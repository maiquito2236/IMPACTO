<?php
namespace App\Controllers\administrador;

use App\Models\administrador\ConsultorioAdminModel;
use Exception;

class ConsultorioAdminController {

    private $modelo;

    public function __construct() {
        $this->modelo = new ConsultorioAdminModel();
    }

    public function mostrarVista() {
        require_once __DIR__ . '/../../Views/administrador/consultorio.php';
    }

    // ==========================================
    // ENDPOINTS JSON
    // ==========================================

    public function listarConsultorios() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = $this->modelo->listarConsultorios();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function obtenerDetalle() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) throw new Exception("ID no válido.");
            $data = $this->modelo->obtenerConsultorioPorId($id);
            if (!$data) throw new Exception("Consultorio no encontrado.");
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function obtenerOdontologosDisponibles() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = $this->modelo->obtenerOdontologosSinConsultorio();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function obtenerTodosOdontologos() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = $this->modelo->obtenerTodosOdontologos();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function crearConsultorio() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['nombre'])) throw new Exception("El nombre es obligatorio.");
            
            $id = $this->modelo->crearConsultorio([
                'nombre' => $data['nombre'],
                'ubicacion' => $data['ubicacion'] ?? '',
                'descripcion' => $data['descripcion'] ?? ''
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Consultorio creado correctamente.', 'id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function actualizarConsultorio() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id']) || empty($data['nombre'])) throw new Exception("Datos incompletos.");
            
            $this->modelo->actualizarConsultorio((int)$data['id'], [
                'nombre' => $data['nombre'],
                'ubicacion' => $data['ubicacion'] ?? '',
                'descripcion' => $data['descripcion'] ?? ''
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Consultorio actualizado correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function eliminarConsultorio() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) throw new Exception("ID no proporcionado.");
            
            $this->modelo->eliminarConsultorio((int)$data['id']);
            echo json_encode(['status' => 'success', 'message' => 'Consultorio eliminado correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function asignarOdontologo() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['consultorio_id']) || empty($data['odontologo_id'])) {
                throw new Exception("Selecciona un consultorio y un odontólogo.");
            }
            
            $this->modelo->asignarOdontologo((int)$data['consultorio_id'], (int)$data['odontologo_id']);
            echo json_encode(['status' => 'success', 'message' => 'Odontólogo asignado correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function desasignarOdontologo() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['consultorio_id'])) throw new Exception("ID de consultorio no proporcionado.");
            
            $motivo = isset($data['motivo']) ? $data['motivo'] : 'Sin motivo especificado';

            $this->modelo->desasignarOdontologo((int)$data['consultorio_id'], $motivo);
            echo json_encode(['status' => 'success', 'message' => 'Odontólogo desasignado correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function cambiarEstado() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id']) || empty($data['estado'])) {
                throw new Exception("ID o estado no proporcionado.");
            }
            
            $this->modelo->cambiarEstado((int)$data['id'], $data['estado']);
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function obtenerHistorial() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = $this->modelo->obtenerHistorialAsignaciones();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function obtenerKPIs() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $data = $this->modelo->obtenerResumenKPIs();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}
