<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\TratamientoModel;
use App\Models\odontologo\PlanestratamientoModel;
use Exception;

class PlanTratamientoController {

    private $modelo;
    private $planesModelo;

    public function __construct() {
        $this->modelo = new TratamientoModel();
        // Asegúrate de que el nombre del archivo de tu modelo coincida exactamente con "PlanestratamientoModel.php"
        $this->planesModelo = new PlanestratamientoModel();
    }

    public function index() {
        $id_cita = isset($_GET['id_cita']) ? intval($_GET['id_cita']) : 0;
        
        $paciente_nombre = "Seleccione Paciente";
        $id_paciente = "";
        $documento = "";
        $tratamiento_motivo = "Ninguno asignado";
        
        // Variables para las fases dinámicas
        $procedimientos_paciente = [];
        $total_presupuesto = 0.00;

        if ($id_cita > 0) {
            $agendaModelo = new \App\Models\odontologo\AgendaModel();
            $detalleCita = $agendaModelo->obtenerDetalleCita($id_cita);

            if ($detalleCita) {
                $id_paciente        = $detalleCita['id_paciente'] ?? '';
                $paciente_nombre    = $detalleCita['paciente'] ?? 'Seleccione Paciente';
                $documento          = $detalleCita['documento'] ?? '';
                $tratamiento_motivo = $detalleCita['tratamiento'] ?? 'Consulta';

                // Buscar los procedimientos reales de su historia clínica
                if (!empty($id_paciente)) {
                    // Extraemos odontologo_id de la sesión
                    if (session_status() === PHP_SESSION_NONE) { session_start(); }
                    $odontologo_id = $_SESSION['odontologo_id'] ?? null;
                    
                    $datosHistorial = $this->planesModelo->obtenerProcedimientosHistoriaPaciente($id_paciente, $odontologo_id);
                    if ($datosHistorial) {
                        $procedimientos_paciente = $datosHistorial;
                        foreach ($datosHistorial as $proc) {
                            if (($proc['TIPO_SEGUIMIENTO'] ?? '') === 'EVOLUCION_FASES' && strtolower($proc['ESTADO_FASE'] ?? '') !== 'hecho') {
                                // Solo sumamos si no está hecho (es decir, no ha sido pagado/finalizado) o si quieres que no se sume lo del pasado.
                                $total_presupuesto += (float) ($proc['PRECIO_APLICADO'] * ($proc['CANTIDAD'] ?? 1));
                            }
                        }
                    }
                }
            }
        }

        require_once __DIR__ . '/../../Views/odontologo/planes_tratamientos_odon.php';
    }

    /* ==========================================================================
       NUEVO MÉTODO: GUARDAR TRATAMIENTO EN BD (Mapeado con tu Javascript)
       ========================================================================== */
    public function guardarPlan() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        // Lee el JSON enviado mediante fetch en JS
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['paciente_id'], $data['fecha'], $data['pieza'], $data['procedimiento_id'])) {
            $notas = $data['notas'] ?? '';
            try {
                $tratamientos = [$data['procedimiento_id']];
                
                $this->modelo->guardarPlanOdontograma(
                    $data['paciente_id'], 
                    $data['pieza'], 
                    $data['fecha'], 
                    $notas, 
                    $tratamientos
                );
                
                echo json_encode(['success' => true, 'mensaje' => 'Tratamiento registrado correctamente']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios para guardar']);
        }
        exit;
    }

    /* ==========================================================================
       NUEVO MÉTODO: OBTENER PIEZAS TRATADAS DEL PACIENTE (Para el JavaScript)
       ========================================================================== */
    public function obtenerTratamientosPaciente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_paciente = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id_paciente <= 0) {
            echo json_encode([]);
            exit;
        }
        try {
            $piezasTratadas = $this->planesModelo->obtenerPiezasOdontograma($id_paciente);
            echo json_encode(array_values($piezasTratadas));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    /* ==========================================================================
       MÉTODO PARA DEVOLVER LAS PIEZAS TRATADAS EN JSON LIMPIO
       ========================================================================== */
    public function listarPiezas() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $id_paciente = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id_paciente <= 0) {
            echo json_encode([]);
            exit;
        }
        try {
            $piezasTratadas = $this->planesModelo->obtenerPiezasOdontograma($id_paciente);
            echo json_encode(array_values($piezasTratadas));
        } catch (\Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    /* ==========================================================================
       PROCESAMIENTO MASIVO INTELIGENTE: Evalúa ÁMBITO (BOCA_COMPLETA o POR_DIENTE)
       ========================================================================== */
    public function guardarMasivo() {
        // Limpiamos cualquier error previo en pantalla para que el JSON no se rompa
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json; charset=utf-8');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $paciente_id      = isset($input['paciente_id']) ? intval($input['paciente_id']) : 0;
        $procedimiento_id = isset($input['procedimiento_id']) ? intval($input['procedimiento_id']) : 0;
        $precio_aplicado  = isset($input['precio_aplicado']) ? floatval($input['precio_aplicado']) : 0.00;
        $notas            = isset($input['notas']) ? trim($input['notas']) : '';
        $fecha            = isset($input['fecha']) ? $input['fecha'] . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $piezasSeleccionadas = isset($input['piezas']) ? $input['piezas'] : []; 
        $diagnostico_general = isset($input['diagnostico_general']) ? trim($input['diagnostico_general']) : '';
        $recomendacion       = isset($input['recomendacion']) ? trim($input['recomendacion']) : '';
        $cita_id             = isset($input['cita_id']) ? intval($input['cita_id']) : 0;

        if ($paciente_id <= 0 || $procedimiento_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos obligatorios incompletos.']);
            exit;
        }

        $db = \App\Config\Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // EXTRAER EL ODONTÓLOGO DE LA SESIÓN (Evita que la base de datos rechace la inserción)
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $usuario_id = $_SESSION['usuario_id'] ?? 0;
            $stmtOd = $db->prepare("SELECT ID_ODONTOLOGO FROM odontologo WHERE USUARIOS_ID_USUARIOS = ?");
            $stmtOd->execute([$usuario_id]);
            $odData = $stmtOd->fetch(\PDO::FETCH_ASSOC);
            $odontologo_id = $odData ? $odData['ID_ODONTOLOGO'] : 1; // Fallback seguro

            // 1. Consultar el procedimiento en la BD
            $sqlProc = "SELECT NOMBRE_PROCEDIMIENTO, TIPO_COBRO FROM procedimientos WHERE ID_PROCEDIMIENTO = ?";
            $stmtProc = $db->prepare($sqlProc);
            $stmtProc->execute([$procedimiento_id]);
            $infoProcedimiento = $stmtProc->fetch(\PDO::FETCH_ASSOC);

            if (!$infoProcedimiento) {
                throw new \Exception("El procedimiento seleccionado no existe en el catálogo.");
            }

            $nombreProc     = $infoProcedimiento['NOMBRE_PROCEDIMIENTO'];
            $tipoCobro      = (int) ($infoProcedimiento['TIPO_COBRO'] ?? 1);
            
            // Si TIPO_COBRO es 2 (Global), tratamos como BOCA_COMPLETA
            $ambito         = ($tipoCobro === 2 || empty($piezasSeleccionadas)) ? 'BOCA_COMPLETA' : 'POR_DIENTE';
            $tipoEvolucion  = 'SESION_UNICA';

            // Preparar consultas (AHORA INCLUYEN ODONTOLOGO_ID_ODONTOLOGO en el odontograma)
            $sqlHistoria = "INSERT INTO historia_clinica (FECHA_REGISTRO, MOTIVO_CONSULTA, DIAGNOSTICO, TRATAMIENTO, PACIENTE_ID_PACIENTE, ODONTOLOGO_ID_ODONTOLOGO, CITA_ID_CITA) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtHistoria = $db->prepare($sqlHistoria);

            $sqlRelacion = "INSERT INTO historia_clinica_has_procedimientos (HISTORIA_CLINICA_ID_HISTORIA_CLINICA, PROCEDIMIENTOS_ID_PROCEDIMIENTO, PRECIO_APLICADO, CANTIDAD) 
                            VALUES (?, ?, ?, ?)";
            $stmtRelacion = $db->prepare($sqlRelacion);

            $sqlOdontograma = "INSERT INTO odontograma_tratamientos (PACIENTE_ID_PACIENTE, ODONTOLOGO_ID_ODONTOLOGO, PIEZA_DENTAL, PROCEDIMIENTO_ID_PROCEDIMIENTO, FECHA_REGISTRO, NOTAS, ESTADO) 
                               VALUES (?, ?, ?, ?, ?, ?, 'HECHO')";
            $stmtOdontograma = $db->prepare($sqlOdontograma);

            // 2. Ejecutar inserción según el Ámbito
            if ($ambito === 'BOCA_COMPLETA') {
                $motivo = $nombreProc; // Cambiado para que refleje el procedimiento
                $notasBase = !empty($piezasSeleccionadas) ? "Piezas: " . implode(', ', $piezasSeleccionadas) . " | " . $notas : $notas;
                $notasFinales = (!empty($diagnostico_general) ? $diagnostico_general . " | " : "") . $notasBase;
                $tratamientoReal = $nombreProc;

                // INSERCIÓN BOCA COMPLETA
                $stmtHistoria->execute([$fecha, $motivo, $notasFinales, $tratamientoReal, $paciente_id, $odontologo_id, $cita_id > 0 ? $cita_id : null]);
                $id_historia_clinica = $db->lastInsertId();

                $stmtRelacion->execute([$id_historia_clinica, $procedimiento_id, $precio_aplicado, 1]);

                foreach ($piezasSeleccionadas as $numeroPieza) {
                    $stmtOdontograma->execute([$paciente_id, $odontologo_id, $numeroPieza, $procedimiento_id, $fecha, $notas]);
                }
                if (empty($piezasSeleccionadas)) {
                    $stmtOdontograma->execute([$paciente_id, $odontologo_id, 'Gral', $procedimiento_id, $fecha, $notas]);
                }

            } else {
                // ÁMBITO: POR_DIENTE (Crea registros individuales por unidad)
                foreach ($piezasSeleccionadas as $numeroPieza) {
                    $motivo_defecto = $nombreProc; // Cambiado para que refleje el procedimiento
                    $notasBase = "Pieza " . $numeroPieza . " | " . $notas;
                    $notasFinales = (!empty($diagnostico_general) ? $diagnostico_general . " | " : "") . $notasBase;
                    $tratamientoReal = $nombreProc;
                    
                    $stmtHistoria->execute([$fecha, $motivo_defecto, $notasFinales, $tratamientoReal, $paciente_id, $odontologo_id, $cita_id > 0 ? $cita_id : null]);
                    $id_historia_clinica = $db->lastInsertId();

                    $stmtRelacion->execute([$id_historia_clinica, $procedimiento_id, $precio_aplicado, 1]);
                    
                    $stmtOdontograma->execute([$paciente_id, $odontologo_id, $numeroPieza, $procedimiento_id, $fecha, $notas]);
                }
            }

            $db->commit();
            
            // Actualizar tabla cita con recomendaciones si aplica
            if ($cita_id > 0 && (!empty($recomendacion) || !empty($diagnostico_general))) {
                $sqlCitaUpdate = "UPDATE cita SET ";
                $updates = [];
                $paramsCita = [];
                if (!empty($recomendacion)) {
                    $updates[] = "RECOMENDACIONES = ?";
                    $paramsCita[] = $recomendacion;
                }
                if (!empty($diagnostico_general)) {
                    $updates[] = "OBSERVACIONES_DOCTOR = ?";
                    $paramsCita[] = $diagnostico_general;
                }
                $sqlCitaUpdate .= implode(", ", $updates) . " WHERE ID_CITA = ?";
                $paramsCita[] = $cita_id;
                
                $stmtCita = $db->prepare($sqlCitaUpdate);
                $stmtCita->execute($paramsCita);
            }
            
            // Notificaciones
            $stmtRef = $db->prepare("SELECT CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as nombre FROM usuarios u INNER JOIN paciente p ON u.ID_USUARIOS = p.USUARIOS_ID_USUARIOS WHERE p.ID_PACIENTE = ?");
            $stmtRef->execute([$paciente_id]);
            $pacData = $stmtRef->fetch(\PDO::FETCH_ASSOC);
            $nombre_paciente = $pacData ? $pacData['nombre'] : 'Paciente';
            
            $montoTotal = $precio_aplicado * count($piezasSeleccionadas);
            if ($ambito === 'BOCA_COMPLETA') { $montoTotal = $precio_aplicado; }
            $monto_fmt = number_format($montoTotal, 2, ',', '.');
            
            \App\Helpers\Notificador::enviarAAdmin(29, "Se ha creado un nuevo plan de tratamiento para {$nombre_paciente} por $$monto_fmt.");
            \App\Helpers\Notificador::enviarAPaciente($paciente_id, 30, "Tu nuevo plan de tratamiento ya está disponible en tu perfil.");
            
            echo json_encode(['success' => true, 'mensaje' => 'Plan de tratamiento e historial guardado correctamente.']);
            
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'error' => 'Error crítico: ' . $e->getMessage()]);
        }
        exit;
    }
}