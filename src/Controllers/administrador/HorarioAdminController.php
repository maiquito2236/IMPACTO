<?php
namespace App\Controllers\administrador;

use App\Models\administrador\HorarioAdminModel;

class HorarioAdminController {

    private $model;

    public function __construct() {
        $this->model = new HorarioAdminModel();
    }

    public function mostrarVista() {
        require_once __DIR__ . '/../../Views/administrador/gestion_horarios.php';
    }
    
    public function listar() {
        try {
            $horarios = $this->model->obtenerTodosConOdontologos();
            // Traemos la lista maestra de doctores
            $listaOdontologos = $this->model->obtenerListaOdontologos();
            
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $horarios,
                'listaOdontologos' => $listaOdontologos
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function eliminar() {
        try {
            header('Content-Type: application/json; charset=utf-8');
            $idHorario = isset($_POST['id_horario']) ? (int)$_POST['id_horario'] : 0;

            if ($idHorario <= 0) {
                throw new \Exception("ID de horario no válido.");
            }

            $res = $this->model->eliminar($idHorario);

            if ($res) {
                echo json_encode(['status' => 'success']);
            } else {
                throw new \Exception("No se pudo eliminar el registro en la base de datos.");
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $idHorario = $_POST['id_horario'] ?? null;
            $odontologoId = (int)($_POST['odontologo_id'] ?? 0);
            
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';
            $jornada = $_POST['jornada'] ?? '';
            $fecha = $_POST['fecha'] ?? '';
            $estado = $_POST['estado'] ?? 'Disponible';
            $descansoInicio = !empty($_POST['descanso_inicio']) ? $_POST['descanso_inicio'] : null;
            $descansoFin = !empty($_POST['descanso_fin']) ? $_POST['descanso_fin'] : null;

            if (!$odontologoId || !$fecha) {
                throw new \Exception("Faltan datos obligatorios.");
            }

            if (!empty($idHorario)) {
                $this->model->actualizar($idHorario, $odontologoId, $horaInicio, $horaFin, $jornada, $fecha, $estado, $descansoInicio, $descansoFin);
            } else {
                $this->model->crear($odontologoId, $horaInicio, $horaFin, $jornada, $fecha, $estado, $descansoInicio, $descansoFin);
            }

            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit; 
    }

    public function listarPorFecha() {
        $fecha = $_GET['fecha'] ?? '';
        if (empty($fecha)) {
            echo json_encode(['status' => 'error', 'message' => 'Fecha no válida']);
            return;
        }
        $datos = $this->model->obtenerPorFecha($fecha);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $datos]);
        exit;
    }

    public function cargaMasiva() {
        if (isset($_GET['descargar_plantilla'])) {
            return $this->descargarPlantillaCsv();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
            header('Content-Type: application/json; charset=utf-8');
            $file = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);
            $successCount = 0; $errorCount = 0; $erroresDetalle = [];
            
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 5) { $errorCount++; continue; }
                $docOdontologo = trim($row[0]);
                if ($docOdontologo === 'DOCUMENTO_ODONTOLOGO') continue;

                $fecha = trim($row[1]); $horaInicio = trim($row[2]);
                $horaFin = trim($row[3]); $jornada = trim($row[4]);
                try {
                    $this->model->registrarHorarioMasivo($docOdontologo, $fecha, $horaInicio, $horaFin, $jornada);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Fila doc $docOdontologo - $fecha: " . $e->getMessage();
                }
            }
            fclose($file);
            echo json_encode(['success' => true, 'successCount' => $successCount, 'errorCount' => $errorCount, 'erroresDetalle' => $erroresDetalle]);
            exit;
        }
    }

    public function descargarPlantillaCsv() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_horarios.csv');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['DOCUMENTO_ODONTOLOGO', 'FECHA', 'HORA_INICIO', 'HORA_FIN', 'JORNADA']);
        fputcsv($output, ['2002002002', date('Y-m-d', strtotime('next monday')), '08:00:00', '12:00:00', 'Mañana']);
        fclose($output);
        exit;
    }
}