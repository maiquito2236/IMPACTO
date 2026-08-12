<?php
namespace App\Controllers\administrador;

use App\Models\administrador\ReporteAdminModel;

class ReporteAdminController {

    private $model;

    public function __construct() {
        $this->model = new ReporteAdminModel();
    }

    /**
     * Endpoint API que devuelve datos de reportes en JSON.
     * Parámetro GET: periodo = 'este-mes' | 'mes-anterior' | 'trimestre'
     */
    public function api() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $periodo = $_GET['periodo'] ?? 'este-mes';
            
            // Validar periodo
            $periodosValidos = ['este-mes', 'mes-anterior', 'trimestre'];
            if (!in_array($periodo, $periodosValidos)) {
                $periodo = 'este-mes';
            }

            $datos = $this->model->obtenerDatosReporte($periodo);

            echo json_encode([
                'ok' => true,
                'data' => $datos
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error al obtener datos: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
