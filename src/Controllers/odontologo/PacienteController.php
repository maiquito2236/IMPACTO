<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\PacienteModel;

class PacienteController {
    private $modelo;

    public function __construct() {
        $this->modelo = new PacienteModel();
    }

    public function index() {
        require_once __DIR__ . '/../../Views/odontologo/pacientes_odon.php';
    }

    public function listar() {
        header('Content-Type: application/json');
        try {
            echo json_encode($this->modelo->obtenerPacientes());
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}