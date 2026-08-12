<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\ReporteModel;

class ReporteController {

    private $reporteModelo;

    public function __construct() {
        $this->reporteModelo = new ReporteModel();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        // Obtener el ID del odontólogo automáticamente
        $idUsuario = $_SESSION['usuario_id'];
        $idOdontologo = $this->reporteModelo->obtenerIdOdontologo($idUsuario);

        if (!$idOdontologo) {
            die("Error de acceso: No se encontró un perfil de odontólogo asociado a esta cuenta.");
        }

        $hoy = date('Y-m-d');

        // Carga de variables analíticas cruzadas (Todo a través de ReporteModel)
        $citasHoy            = $this->reporteModelo->contarCitasHoy($hoy, $idOdontologo);
        $proximaCita         = $this->reporteModelo->obtenerProximaCita($hoy, $idOdontologo);
        $pacientesActivos    = $this->reporteModelo->contarPacientesActivos($idOdontologo);
        $tratamientosActivos = $this->reporteModelo->contarTratamientosActivos($idOdontologo);
        
        $agendaHoy           = $this->reporteModelo->obtenerAgendaHoy($hoy, $idOdontologo);
        $pacientesRecientes  = $this->reporteModelo->obtenerPacientesRecientes($idOdontologo);
        $distribucion        = $this->reporteModelo->obtenerDistribucionTratamientos($idOdontologo);


        require_once __DIR__ . '/../../Views/odontologo/reportes_odon.php';
    }
}