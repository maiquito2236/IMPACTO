<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\HistoriaClinicaModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class HistoriaClinicaController {

    private $modelo;

    public function __construct() {
        $this->modelo = new HistoriaClinicaModel();
    }

    /**
     * Carga y renderiza la vista principal del historial clínico
     * capturando de forma dinámica el paciente desde la URL
     */
    public function renderizarHistorial() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Captura el ID enviado por el odontograma (?paciente_id=X). Fallback al id 9 si no viene ninguno.
        $pacienteId = isset($_GET['paciente_id']) ? intval($_GET['paciente_id']) : 0;

        // Cargamos la vista. La variable $pacienteId queda expuesta automáticamente para el HTML
        require_once __DIR__ . '/../../Views/odontologo/historial_clinico_odon.php';
    }

    public function datosOdontograma() {
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json; charset=utf-8');
        
        $pacienteId = isset($_GET['paciente_id']) ? intval($_GET['paciente_id']) : 0;
        $targetDate = isset($_GET['target_date']) ? $_GET['target_date'] : null;

        if (session_status() === PHP_SESSION_NONE) session_start();
        $nombreDoc = (isset($_SESSION['usuario_nome'])) ? $_SESSION['usuario_nome'] : 'Dr. Usuario';

        $demograficos = $this->modelo->obtenerDatosDemograficosPaciente($pacienteId);
        $datosDientes = $this->modelo->obtenerOdontogramaPaciente($pacienteId, $targetDate);
        
        $toothData = [];
        foreach ($datosDientes as $row) {
            $pieza = $row['PIEZA_DENTAL'];
            $doctorRegistro = $row['doctor'] ?? 'Sin asignar';
            
            if (!isset($toothData[$pieza])) {
                $toothData[$pieza] = [
                    'nombre' => "Pieza Dental " . $pieza,
                    'estado' => $row['estado'],
                    'tratamiento' => $row['tratamiento'],
                    // Si la BD no tiene doctor (tratamientos viejos), usa el doctor de sesión como respaldo
                    'doctor' => ($doctorRegistro !== 'Sin asignar') ? $doctorRegistro : $nombreDoc, 
                    'fecha' => $row['fecha'],
                    'notas' => $row['notas'] ?? '',
                    'es_nuevo' => (isset($row['es_nuevo']) && $row['es_nuevo'] == 1)
                ];
            } else {
                // Ya existe, concatenamos el tratamiento y las notas
                $toothData[$pieza]['tratamiento'] .= ', ' . $row['tratamiento'];
                if (!empty($row['notas'])) {
                    $toothData[$pieza]['notas'] .= ' | ' . $row['notas'];
                }
                if ($row['estado'] === 'HECHO') {
                    $toothData[$pieza]['estado'] = 'HECHO';
                }
            }
        }

        echo json_encode([
            'paciente' => $demograficos,
            'alertas' => $this->modelo->obtenerAlertasPaciente($pacienteId),
            'financiero' => $this->modelo->obtenerResumenFinanciero($pacienteId),
            'citas' => $this->modelo->obtenerControlCitas($pacienteId),
            'dientes' => $toothData,
            'doctor_sesion' => $nombreDoc // 🔥 Enviamos el nombre del doctor activo al JS
        ]);
        exit;
    }

    public function datosTimeline() {
        if (ob_get_length()) ob_clean(); // <--- ESTA LÍNEA ELIMINA CUALQUIER "BASURA" PREVIA
    header('Content-Type: application/json; charset=utf-8');
        $pacienteId = isset($_GET['paciente_id']) ? intval($_GET['paciente_id']) : 0;

        $evoluciones = $this->modelo->obtenerEvolucionesTimeline($pacienteId);
        // Devolvemos el array puro y limpio
        echo json_encode($evoluciones);
        exit;
    }

    public function completarYRedirigirTratamientos() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Debugging
        $logMsg = date('Y-m-d H:i:s') . " - Called completarYRedirigirTratamientos. GET parameters: " . json_encode($_GET) . "\n";

        // Capturamos los IDs enviados por el botón
        $citaId = isset($_GET['cita_id']) ? intval($_GET['cita_id']) : 0;
        $pacienteId = isset($_GET['paciente_id']) ? intval($_GET['paciente_id']) : 0;

        $logMsg .= "Parsed citaId: $citaId, pacienteId: $pacienteId\n";

        // Si hay una cita válida en la navegación, completamos cita y tratamientos en la BD
        if ($citaId > 0 && $pacienteId > 0) {
            try {
                $this->modelo->completarCitaYTratamientos($citaId, $pacienteId);
                $logMsg .= "Success: completarCitaYTratamientos executed.\n";
            } catch (\Exception $e) {
                $logMsg .= "Exception in completarCitaYTratamientos: " . $e->getMessage() . "\n";
            }
        } else {
            $logMsg .= "Failed: citaId or pacienteId is 0 or invalid.\n";
        }
        
        file_put_contents(__DIR__ . '/../../../../debug_completar.log', $logMsg, FILE_APPEND);

        // Redirección inmediata y transparente al módulo de tratamientos generales
        header("Location: index.php?action=odontologo/tratamientos");
        exit;
    }

    public function guardarYEnviarPdf() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $citaId = isset($_POST['cita_id']) ? intval($_POST['cita_id']) : 0;
        $pacienteId = isset($_POST['paciente_id']) ? intval($_POST['paciente_id']) : 0;

        if ($citaId === 0 || $pacienteId === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes']);
            exit;
        }

        if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió el PDF adjunto']);
            exit;
        }

        try {
            // Guardar tratamientos primero (lógica reutilizada)
            $this->modelo->completarCitaYTratamientos($citaId, $pacienteId);

            // Obtener el correo del paciente
            $pacienteData = $this->modelo->obtenerDatosDemograficosPaciente($pacienteId);
            $correoPaciente = $pacienteData['correo'] ?? null;
            $nombrePaciente = $pacienteData['paciente_nombre'] ?? 'Paciente';

            if (!$correoPaciente) {
                echo json_encode(['status' => 'error', 'message' => 'El paciente no tiene un correo registrado, pero los datos se han guardado con éxito.']);
                exit;
            }

            // Preparar el envío del correo
            $pdfPath = $_FILES['pdf']['tmp_name'];
            $pdfName = 'Historia_Clinica_' . ($pacienteData['documento'] ?? 'OdontoEstetica') . '.pdf';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '571egr@gmail.com';  
            $mail->Password   = 'kxddxrqscozpwuxb';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            
            $mail->SMTPOptions = array(
                'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
            );
            
            $mail->setFrom('571egr@gmail.com', 'Odonto Estética');
            $mail->addAddress($correoPaciente, $nombrePaciente);
            $mail->isHTML(true);
            $mail->Subject = 'Historia Clínica Odontológica - Odonto Estética';
            
            // Cuerpo del correo
            $mail->Body = "<div style='background-color: #f1f5f9; padding: 30px; font-family: Arial, sans-serif;'>
                            <div style='background-color: #ffffff; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'>
                                <h2 style='color: #1e293b; text-align: center;'>Tu Historia Clínica Odontológica</h2>
                                <p style='color: #475569; font-size: 16px;'>Hola <strong>" . htmlspecialchars($nombrePaciente) . "</strong>,</p>
                                <p style='color: #475569; font-size: 16px;'>Te hemos adjuntado una copia en formato PDF de tu historia clínica y evolución de tus tratamientos odontológicos tras tu última cita.</p>
                                <p style='color: #475569; font-size: 16px;'>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                                <hr style='border: 1px solid #e2e8f0; margin: 20px 0;'>
                                <p style='color: #94a3b8; font-size: 12px; text-align: center;'>Odonto Estética - Salud y Bienestar</p>
                            </div>
                           </div>";

            // Adjuntar PDF
            $mail->addAttachment($pdfPath, $pdfName, 'base64', 'application/pdf');
            
            $mail->send();

            echo json_encode(['status' => 'success', 'message' => 'Historia guardada y enviada correctamente']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Los datos se guardaron pero falló el envío del correo: ' . $e->getMessage()]);
        }
    }

}