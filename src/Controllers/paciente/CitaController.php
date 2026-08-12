<?php
namespace App\Controllers\paciente;

use App\Models\paciente\Cita;
use App\Models\paciente\Usuario;
use Dompdf\Dompdf;
use Dompdf\Options;

class CitaController {

    public function mostrarPedirCita() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $model = new Cita();
        $procedimientos = $model->obtenerProcedimientos(); 
        $horarios = $model->obtenerHorariosDisponibles(); 

        require_once __DIR__ . '/../../Views/paciente/pedir_cita.php';
    }

   public function guardarCita() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idProcedimiento = $_POST['procedimiento_id'] ?? null;
            $idHorario       = $_POST['horario_id'] ?? null;
            $fechaCita       = $_POST['fecha_cita'] ?? null;
            $horaCita        = $_POST['hora_cita'] ?? null;
            $idPaciente      = $_SESSION['paciente_id'] ?? null;

            // 1. Instanciamos el modelo Cita desde el principio
            $modelCita = new Cita();

            // 2. Extraemos el ID del paciente de forma segura usando el modelo Cita (NO Usuario)
            if (empty($idPaciente) && isset($_SESSION['usuario_id'])) {
                $pacienteData = $modelCita->obtenerPacientePorUsuario($_SESSION['usuario_id']);
                if ($pacienteData) {
                    $idPaciente = $pacienteData['ID_PACIENTE'];
                    $_SESSION['paciente_id'] = $idPaciente;
                }
            }

            if ($idProcedimiento && $idHorario && $idPaciente) {
                $horarioInfo = $modelCita->obtenerHorarioPorId($idHorario);
                
                if ($horarioInfo) {
                    $idOdontologo = $horarioInfo['ODONTOLOGO_ID_ODONTOLOGO'];
                    
                    // Si el formulario no envió la hora, la sacamos directo del horario de la BD
                    if (empty($horaCita)) {
                        $horaCita = $horarioInfo['HORA_INICIO'];
                    }

                    // Formateamos la fecha a formato estricto de MySQL para evitar errores
                    $fechaLimpia = date('Y-m-d', strtotime(str_replace('/', '-', $fechaCita)));
                    $fechaHoraFormato = $fechaLimpia . ' ' . $horaCita;

                    // 3. OBTENER EL NOMBRE REAL DEL PROCEDIMIENTO
                    $procedimientos = $modelCita->obtenerProcedimientos();
                    $nombreProcedimiento = "Cita odontológica";
                    foreach($procedimientos as $proc) {
                        if($proc['ID_PROCEDIMIENTO'] == $idProcedimiento) {
                            $nombreProcedimiento = $proc['NOMBRE_PROCEDIMIENTO'];
                            break;
                        }
                    }

                    // 4. Creamos la cita guardando el NOMBRE REAL para que el Doctor lo vea
                    $idCitaNueva = $modelCita->crear([
                        'PACIENTE_ID_PACIENTE'              => $idPaciente,
                        'ODONTOLOGO_ID_ODONTOLOGO'          => $idOdontologo,
                        'FECHA_HORA'                        => $fechaHoraFormato,
                        'ESTADO_CITA_ID'                    => 1, 
                        'MOTIVO'                            => $nombreProcedimiento, 
                        'HORARIO_DISPONIBILIDAD_ID_HORARIO' => $idHorario
                    ]);

                    if ($idCitaNueva) {
                        $modelCita->asociarCitaProcedimiento($idCitaNueva, $idProcedimiento);
                        $modelCita->actualizarEstadoHorario($idHorario, 'Ocupado');

                        // ==========================================
                        // 🔔 GENERAR NOTIFICACIONES DE NUEVA CITA
                        // ==========================================
                        $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($fechaHoraFormato));
                        $msgPaciente = "Tu cita médica ha sido agendada para el $fecha_fmt.";
                        $msgPersonal = "El paciente ha agendado una nueva cita médica para el $fecha_fmt. Procedimiento: " . $nombreProcedimiento;
                        
                        // Paciente
                        if (!empty($_SESSION['usuario_id'])) {
                            \App\Helpers\Notificador::enviarAUsuario($_SESSION['usuario_id'], 1, $msgPaciente);
                        }
                        // Odontólogo
                        \App\Helpers\Notificador::enviarAOdontologo($idOdontologo, 21, $msgPersonal);
                        // Admin
                        \App\Helpers\Notificador::enviarAAdmin(22, $msgPersonal);

                        // 📧 ENVIAR CORREO DE CONFIRMACIÓN AL PACIENTE CON DETALLES DE LA CITA
                        \App\Helpers\Notificador::enviarCorreoConfirmacionCita($idCitaNueva);

                        // Enviar al paciente a su tabla de citas para que vea el éxito
                        $_SESSION['mensaje_success'] = "Cita agendada correctamente.";
                        header("Location: /LOGIN_ORIGINAL/mis_citas");
                        exit;
                    }
                }
            }
            // Si algo falla, devuelve al paciente al formulario
            $_SESSION['mensaje_error'] = "Error: Por favor completa todos los datos.";
            header("Location: /LOGIN_ORIGINAL/pedir_cita");
            exit;
        }
    }

    public function mostrarMisCitas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $idPaciente = $_SESSION['paciente_id'] ?? null;
        
        if (empty($idPaciente) && isset($_SESSION['usuario_id'])) {
            $modelUsuario = new Usuario();
            $pacienteData = $modelUsuario->obtenerPacientePorUsuario($_SESSION['usuario_id']);
            $idPaciente = $pacienteData ? $pacienteData['ID_PACIENTE'] : null;
        }

        $modelCita = new Cita();
        $citas = $modelCita->obtenerCitasPorPaciente($idPaciente);

        require_once __DIR__ . '/../../Views/paciente/mis_citas.php';
    }

    public function obtenerDetalleAjax() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cita'])) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            
            $id_cita = intval($_POST['id_cita']);
            $citaModel = new Cita(); 
            $detalle = $citaModel->obtenerDetalleCitaPorId($id_cita);

            if ($detalle) {
                if (!empty($detalle['FECHA_HORA'])) {
                    $timestamp = strtotime($detalle['FECHA_HORA']);
                    $detalle['SOLO_FECHA'] = date('d/m/Y', $timestamp);
                    $detalle['SOLO_HORA'] = date('h:i A', $timestamp);
                }
                if (!empty($detalle['CREADA_EL'])) {
                    $detalle['CREADA_EL_FORMATO'] = date('d/m/Y - h:i A', strtotime($detalle['CREADA_EL']));
                }
                if (!empty($detalle['FECHA_CANCELACION'])) {
                    $detalle['FECHA_CANCELACION_FORMATO'] = date('d/m/Y - h:i A', strtotime($detalle['FECHA_CANCELACION']));
                }
                if (!empty($detalle['FECHA_ATENCION'])) {
                    $detalle['FECHA_ATENCION_FORMATO'] = date('d/m/Y - h:i A', strtotime($detalle['FECHA_ATENCION']));
                }

                // 🌟 NUEVO: Adjuntar la bitácora de cambios formateada
                $historialRaw = $citaModel->obtenerHistorialPorCitaId($id_cita);
                $historialFormateado = [];
                foreach ($historialRaw as $hist) {
                    $hist['FECHA_ANTERIOR_FMT'] = !empty($hist['FECHA_HORA_ANTERIOR']) ? date('d/m/Y - h:i A', strtotime($hist['FECHA_HORA_ANTERIOR'])) : '--';
                    $hist['FECHA_NUEVA_FMT'] = !empty($hist['FECHA_HORA_NUEVA']) ? date('d/m/Y - h:i A', strtotime($hist['FECHA_HORA_NUEVA'])) : '--';
                    $hist['REGISTRO_FMT'] = date('d/m/Y - h:i A', strtotime($hist['CREADO_EL']));
                    $historialFormateado[] = $hist;
                }
                $detalle['HISTORIAL'] = $historialFormateado;

                echo json_encode(['success' => true, 'data' => $detalle]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontraron detalles de la cita.']);
            }
            exit;
        }
    }

    public function procesarCancelarCita() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_cita = intval($_POST['id_cita'] ?? 0);
            $motivo = $_POST['motivo_cancelacion'] ?? null;
            $observaciones = trim($_POST['observaciones_paciente'] ?? '');

            if ($id_cita && $motivo) {
                $motivo_final = !empty($observaciones) ? $motivo . " - Observaciones: " . $observaciones : $motivo;
                $citaModel = new Cita();
                
                // Obtener datos de la cita antes de cancelarla para notificar al doctor
                $detalle = $citaModel->obtenerDetalleCitaPorId($id_cita);

                if ($citaModel->cancelarCitaPorId($id_cita, $motivo_final)) {
                    $_SESSION['mensaje_success'] = "La cita fue cancelada correctamente.";
                    
                    // ==========================================
                    // 🔔 GENERAR NOTIFICACIONES DE CANCELACIÓN
                    // ==========================================
                    $fecha_fmt = !empty($detalle['FECHA_HORA']) ? date('d/m/Y \a \l\a\s h:i A', strtotime($detalle['FECHA_HORA'])) : '--';
                    $msgPaciente = "Tu cita médica del $fecha_fmt ha sido cancelada. Motivo: $motivo_final";
                    $msgPersonal = "El paciente ha cancelado su cita del $fecha_fmt. Motivo: $motivo_final";

                    if (!empty($_SESSION['usuario_id'])) {
                        \App\Helpers\Notificador::enviarAUsuario($_SESSION['usuario_id'], 7, $msgPaciente);
                    }
                    if ($detalle && !empty($detalle['ODONTOLOGO_ID'])) {
                        \App\Helpers\Notificador::enviarAOdontologo($detalle['ODONTOLOGO_ID'], 8, $msgPersonal);
                    }
                    \App\Helpers\Notificador::enviarAAdmin(9, $msgPersonal);

                } else {
                    $_SESSION['mensaje_error'] = "Ocurrió un error al intentar cancelar la cita.";
                }
            }
            header("Location: /LOGIN_ORIGINAL/mis_citas");
            exit;
        }
    }

    public function obtenerHorasDisponiblesAjax() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            
            $fecha_recibida = $_POST['fecha'];

            // Convertimos la fecha a formato estricto de MySQL (YYYY-MM-DD)
            $fecha_mysql = date('Y-m-d', strtotime(str_replace('/', '-', $fecha_recibida)));

            $citaModel = new Cita();
            // Consultamos la base de datos con la fecha (sin filtrar por doctor para permitir cambios)
            $horarios = $citaModel->obtenerHorariosDisponiblesPorFecha($fecha_mysql);

            echo json_encode(['success' => true, 'data' => $horarios]);
            exit;
        }
    }

    public function procesarReprogramarCita() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_cita = intval($_POST['id_cita'] ?? 0);
            $id_horario_nuevo = intval($_POST['nuevo_id_horario'] ?? 0);
            $nueva_fecha = $_POST['nueva_fecha'] ?? null;
            $motivo_paciente = trim($_POST['motivo'] ?? '');

            if ($id_cita && $id_horario_nuevo && $nueva_fecha) {
                $citaModel = new Cita();
                $horarioInfo = $citaModel->obtenerHorarioPorId($id_horario_nuevo);
                
                if ($horarioInfo) {
                    $nueva_fecha_hora = $nueva_fecha . ' ' . $horarioInfo['HORA_INICIO'];
                    $nuevo_id_odontologo = $horarioInfo['ODONTOLOGO_ID_ODONTOLOGO'];
                    
                    // Guardamos exactamente lo que el usuario digitó en la caja de texto
                    $motivo_final = !empty($motivo_paciente) ? $motivo_paciente : "Reprogramación solicitada por el usuario.";

                    if ($citaModel->actualizarReprogramacionCita($id_cita, $id_horario_nuevo, $nueva_fecha_hora, $motivo_final, $nuevo_id_odontologo)) {
                        $_SESSION['mensaje_success'] = "Cita reprogramada correctamente.";
                        
                        // ==========================================
                        // 🔔 GENERAR NOTIFICACIONES DE REPROGRAMACIÓN
                        // ==========================================
                        $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($nueva_fecha_hora));
                        $msgPaciente = "Tu cita médica ha sido reprogramada para el $fecha_fmt.";
                        $msgPersonal = "El paciente ha reprogramado su cita para el $fecha_fmt.";

                        if (!empty($_SESSION['usuario_id'])) {
                            \App\Helpers\Notificador::enviarAUsuario($_SESSION['usuario_id'], 13, $msgPaciente);
                        }
                        
                        // Obtener detalle para sacar el ID del odontologo original
                        $detalle = $citaModel->obtenerDetalleCitaPorId($id_cita);
                        if ($detalle && !empty($detalle['ODONTOLOGO_ID'])) {
                            \App\Helpers\Notificador::enviarAOdontologo($detalle['ODONTOLOGO_ID'], 14, $msgPersonal);
                        }
                        \App\Helpers\Notificador::enviarAAdmin(15, $msgPersonal);
                    } else {
                        $_SESSION['mensaje_error'] = "Error al intentar reprogramar la cita.";
                    }
                }
            }
            header("Location: /LOGIN_ORIGINAL/mis_citas");
            exit;
        }
    }

    public function mostrarHistorialClinico() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Capturar el ID del paciente en sesión
        $idPaciente = $_SESSION['paciente_id'] ?? null;
        $idUsuario = $_SESSION['usuario_id'] ?? null;
        
        if (empty($idPaciente) && isset($_SESSION['usuario_id'])) {
            $modelUsuario = new \App\Models\paciente\Usuario();
            $pacienteData = $modelUsuario->obtenerPacientePorUsuario($_SESSION['usuario_id']);
            $idPaciente = $pacienteData ? $pacienteData['ID_PACIENTE'] : null;
        }

        $modelCita = new Cita();
        // Traemos el listado cronológico de citas atendidas para poblar el Datatable
        $citas = $modelCita->obtenerCitasPorPaciente($idPaciente);

        require_once __DIR__ . '/../../Views/paciente/historial_clinico.php';
    }

    // =========================================================================
    // AJAX EXCLUSIVO PARA EL OJITO DEL HISTORIAL CLINICO (TRAE DATOS DEL PACIENTE)
    // =========================================================================
    public function obtenerDetalleHistorialAjax() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cita'])) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            
            $id_cita = intval($_POST['id_cita']);
            $citaModel = new Cita(); 
            
            // Llama al método independiente que acabas de pegar en tu modelo Cita.php
            $detalle = $citaModel->obtenerDetalleHistorialPorId($id_cita);

            if ($detalle) {
                if (!empty($detalle['FECHA_HORA'])) {
                    $timestamp = strtotime($detalle['FECHA_HORA']);
                    $detalle['SOLO_FECHA'] = date('d/m/Y', $timestamp);
                    $detalle['SOLO_HORA'] = date('h:i A', $timestamp);
                }
                echo json_encode(['success' => true, 'data' => $detalle]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontraron detalles clínicos.']);
            }
            exit;
        }
    }

    public function exportarHistorial() {
        // Ejecuta directamente el archivo maestro que tiene toda la lógica
        require_once __DIR__ . '/../../Views/paciente/exports/historial_exportar.php';
    }
}