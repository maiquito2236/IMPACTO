<?php
namespace App\Controllers\paciente;

use App\Models\paciente\DashboardPacienteModel;

class DashboardPacienteController {

    private $modelo;

    public function __construct() {
        $this->modelo = new DashboardPacienteModel();
    }

    /**
     * Muestra el dashboard del paciente con datos reales de la BD.
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /LOGIN_ORIGINAL/login');
            exit;
        }

        $idUsuario = $_SESSION['usuario_id'];
        $idPaciente = $_SESSION['paciente_id'] ?? null;

        if (!$idPaciente) {
            $idPaciente = $this->modelo->obtenerIdPaciente($idUsuario);
            if ($idPaciente) {
                $_SESSION['paciente_id'] = $idPaciente;
            }
        }

        // Datos para el dashboard
        $proximaCita        = $idPaciente ? $this->modelo->obtenerProximaCita($idPaciente) : null;
        $tratamientos       = $idPaciente ? $this->modelo->obtenerResumenTratamientos($idPaciente) : [];
        $totalPendiente     = $idPaciente ? $this->modelo->obtenerPagosPendientes($idPaciente) : 0;
        $citasRecientes     = $idPaciente ? $this->modelo->obtenerCitasRecientes($idPaciente, 5) : [];
        $ultimosPagos       = $idPaciente ? $this->modelo->obtenerUltimosPagos($idPaciente, 2) : [];

        require_once __DIR__ . '/../../Views/paciente/dashboard.php';
    }

    /**
     * Muestra la vista completa de tratamientos del paciente.
     */
    public function tratamientos() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /LOGIN_ORIGINAL/login');
            exit;
        }

        $idUsuario = $_SESSION['usuario_id'];
        $idPaciente = $_SESSION['paciente_id'] ?? null;

        if (!$idPaciente) {
            $idPaciente = $this->modelo->obtenerIdPaciente($idUsuario);
            if ($idPaciente) {
                $_SESSION['paciente_id'] = $idPaciente;
            }
        }

        $modelCita = new \App\Models\paciente\Cita();
        $citas = $idPaciente ? $modelCita->obtenerCitasPorPaciente($idPaciente) : [];
        $estadisticas = $idPaciente ? $modelCita->obtenerEstadisticasHistorial($idPaciente) : [
            'consultas' => 0, 'tratamientos' => 0, 'diagnosticos' => 0, 'ultima_fecha' => '--', 'ultima_especialidad' => '--'
        ];

        require_once __DIR__ . '/../../Views/paciente/tratamientos.php';
    }
}
