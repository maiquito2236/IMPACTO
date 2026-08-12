<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\FacturacionModel;

class FacturacionController {

    private $modelo;

    public function __construct() {
        $this->modelo = new FacturacionModel();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Protección básica de acceso
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        $idUsuario = $_SESSION['usuario_id'];
        $idOdontologo = $this->modelo->obtenerOdontologoId($idUsuario);

        if (!$idOdontologo) {
            die("Error: Perfil de odontólogo no encontrado para este usuario.");
        }

        // Cargar métricas y listados reales para el odontólogo
        $kpis = $this->modelo->obtenerKPIs($idOdontologo);
        $comisiones = $this->modelo->obtenerComisiones($idOdontologo);
        $pagos = $this->modelo->obtenerHistorialPagos($idOdontologo);

        // Requerir la vista
        require_once __DIR__ . '/../../Views/odontologo/facturacion_odon.php';
    }
}