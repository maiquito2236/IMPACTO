<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\DashboardModel;

class DashboardOdonController {

    private $modelo;

    public function __construct() {
        $this->modelo = new DashboardModel();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Protección de Sesión obligatoria
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        $idUsuario = $_SESSION['usuario_id'];
        $idOdontologo = $this->modelo->obtenerOdontologoId($idUsuario);

        if (!$idOdontologo) {
            die("Error: Perfil de odontólogo no encontrado para este usuario.");
        }

        $hoy = date('Y-m-d');

        // Consultas delegadas limpiamente al Modelo (filtradas por doctor)
        $citasHoy            = $this->modelo->contarCitasHoy($hoy, $idOdontologo);
        $proximaCita         = $this->modelo->obtenerProximaCita($hoy, $idOdontologo);
        $pacientesActivos    = $this->modelo->contarPacientesActivos($idOdontologo);
        $tratamientosActivos = $this->modelo->contarTratamientosRegistrados($idOdontologo);
        $agendaHoy           = $this->modelo->obtenerAgendaHoy($hoy, $idOdontologo);
        $pacientesRecientes  = $this->modelo->obtenerPacientesRecientes($idOdontologo);
        $tratamientosDinamicos = $this->modelo->obtenerResumenTratamientos($idOdontologo);
        $alertas             = $this->modelo->obtenerAlertas($idUsuario);


        // Cargar la vista limpia
        require_once __DIR__ . '/../../Views/odontologo/dashboard_odon.php';
    }
}