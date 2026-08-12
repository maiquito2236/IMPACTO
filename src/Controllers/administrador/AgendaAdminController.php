<?php
namespace App\Controllers\administrador;

use App\Models\administrador\AgendaAdminModel;
use Exception;

class AgendaAdminController {

    private $modelo;

    public function __construct() {
        $this->modelo = new AgendaAdminModel();
    }

    // ── Vista principal ───────────────────────────────────────────────
    /**
     * Muestra la vista de agenda del administrador.
     * Llamado desde index.php cuando action = 'admin/agenda'.
     */
    public function mostrarAgenda() {
        require_once __DIR__ . '/../../Views/administrador/agenda.php';
    }

    // ── Endpoints JSON ────────────────────────────────────────────────

    /**
     * GET  /LOGIN_ORIGINAL/admin/agenda/obtener_citas[?inicio=YYYY-MM-DD&fin=YYYY-MM-DD]
     * GET  /LOGIN_ORIGINAL/admin/agenda/obtener_citas?listas=1   → devuelve pacientes+odontólogos
     */
    public function obtenerCitas() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            // Modo especial: devolver listas para los <select> del modal
            if (!empty($_GET['listas'])) {
                echo json_encode([
                    'pacientes'    => $this->modelo->listarPacientes(),
                    'odontologos'  => $this->modelo->listarOdontologos(),
                    'tratamientos' => $this->modelo->listarTratamientos(),
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $inicio = $_GET['inicio'] ?? null;
            $fin    = $_GET['fin']    ?? null;
            $citas  = $this->modelo->listarCitas($inicio, $fin);
            echo json_encode($citas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET  /LOGIN_ORIGINAL/admin/agenda/obtener_citas_hoy
     */
    public function obtenerCitasHoy() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            $citas = $this->modelo->listarCitasHoy();
            echo json_encode($citas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET  /LOGIN_ORIGINAL/admin/agenda/obtener_horas_ocupadas?fecha=YYYY-MM-DD[&id_odontologo=N]
     */
    public function obtenerHorasOcupadas() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            if (empty($_GET['fecha'])) {
                throw new Exception("Falta el parámetro 'fecha'.");
            }
            $fecha         = $_GET['fecha'];
            $id_odontologo = $_GET['id_odontologo'] ?? null;
            $ocupadas      = $this->modelo->obtenerHorasOcupadas($fecha, $id_odontologo);
            echo json_encode($ocupadas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * POST /LOGIN_ORIGINAL/admin/agenda/guardar_cita
     * Body JSON: { paciente_id, odontologo_id, fecha, hora, tratamiento }
     */
    public function guardarCita() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $campos = ['paciente_id', 'odontologo_id', 'fecha', 'hora', 'tratamiento'];
            foreach ($campos as $c) {
                if (empty($data[$c])) {
                    throw new Exception("Falta el campo '$c'.");
                }
            }

            $id_cita = $this->modelo->guardarCita(
                (int) $data['paciente_id'],
                (int) $data['odontologo_id'],
                $data['fecha'],
                $data['hora'],
                trim($data['tratamiento'])
            );

            // Notificaciones
            $fecha_fmt = date('d/m/Y', strtotime($data['fecha'])) . " a las " . date('h:i A', strtotime($data['hora']));
            \App\Helpers\Notificador::enviarAPaciente((int)$data['paciente_id'], 1, "Se le ha agendado una cita médica para el $fecha_fmt.");
            \App\Helpers\Notificador::enviarAOdontologo((int)$data['odontologo_id'], 21, "El administrador le ha asignado una nueva cita para el $fecha_fmt.");

            // 📧 ENVIAR CORREO DE CONFIRMACIÓN AL PACIENTE CON DETALLES DE LA CITA
            \App\Helpers\Notificador::enviarCorreoConfirmacionCita($id_cita);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Cita registrada exitosamente.',
                'id_cita' => $id_cita,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * POST /LOGIN_ORIGINAL/admin/agenda/cancelar_cita
     * Body JSON: { id_cita, motivo }
     */
    public function cancelarCita() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id_cita'])) {
                throw new Exception("Falta el campo 'id_cita'.");
            }

            $motivo = $data['motivo'] ?? 'Cancelada por administrador';
            $this->modelo->cancelarCita((int) $data['id_cita'], $motivo);

            // Notificaciones
            $citaModel = new \App\Models\paciente\Cita();
            $detalle = $citaModel->obtenerDetalleCitaPorId((int) $data['id_cita']);
            if ($detalle) {
                \App\Helpers\Notificador::enviarAPaciente($detalle['PACIENTE_ID_PACIENTE'], 7, "Su cita ha sido cancelada por la administración. Motivo: $motivo");
                if (!empty($detalle['ODONTOLOGO_ID_ODONTOLOGO'])) {
                    \App\Helpers\Notificador::enviarAOdontologo($detalle['ODONTOLOGO_ID_ODONTOLOGO'], 8, "El administrador canceló una cita asignada a usted. Motivo: $motivo");
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Cita cancelada correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * POST /LOGIN_ORIGINAL/admin/agenda/reprogramar_cita
     * Body JSON: { id_cita, nueva_fecha_hora, motivo }
     */
    public function reprogramarCita() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id_cita']) || empty($data['nueva_fecha_hora'])) {
                throw new Exception("Faltan datos: id_cita y nueva_fecha_hora son requeridos.");
            }

            $motivo = $data['motivo'] ?? 'Sin motivo especificado';
            $this->modelo->reprogramarCita(
                (int) $data['id_cita'],
                $data['nueva_fecha_hora'],
                $motivo
            );

            // Notificaciones
            $citaModel = new \App\Models\paciente\Cita();
            $detalle = $citaModel->obtenerDetalleCitaPorId((int) $data['id_cita']);
            if ($detalle) {
                $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($data['nueva_fecha_hora']));
                \App\Helpers\Notificador::enviarAPaciente($detalle['PACIENTE_ID_PACIENTE'], 13, "Su cita fue reprogramada por la administración para el $fecha_fmt. Motivo: $motivo");
                if (!empty($detalle['ODONTOLOGO_ID_ODONTOLOGO'])) {
                    \App\Helpers\Notificador::enviarAOdontologo($detalle['ODONTOLOGO_ID_ODONTOLOGO'], 14, "El administrador reprogramó una de sus citas para el $fecha_fmt.");
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Cita reprogramada correctamente.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
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
            $successCount = 0;
            $errorCount = 0;
            $erroresDetalle = [];
            
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 4) {
                    $errorCount++;
                    $erroresDetalle[] = "Fila incompleta.";
                    continue;
                }
                $docPaciente = trim($row[0]);
                if ($docPaciente === 'DOCUMENTO_PACIENTE') continue;

                $docOdontologo = trim($row[1]);
                $fechaHora = trim($row[2]);
                $motivo = trim($row[3]);
                $estadoId = isset($row[4]) && trim($row[4]) !== '' ? trim($row[4]) : 1;

                try {
                    $this->modelo->registrarCitaMasiva($docPaciente, $docOdontologo, $fechaHora, $motivo, $estadoId);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Fila doc $docPaciente: " . $e->getMessage();
                }
            }
            fclose($file);
            echo json_encode(['success' => true, 'successCount' => $successCount, 'errorCount' => $errorCount, 'erroresDetalle' => $erroresDetalle]);
            exit;
        }
    }

    public function descargarPlantillaCsv() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_agenda.csv');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['DOCUMENTO_PACIENTE', 'DOCUMENTO_ODONTOLOGO', 'FECHA_HORA', 'MOTIVO', 'ESTADO_CITA_ID']);
        fputcsv($output, ['1001001001', '2002002002', date('Y-m-d 08:00:00', strtotime('+1 day')), 'Control General', '1']);
        fclose($output);
        exit;
    }
}