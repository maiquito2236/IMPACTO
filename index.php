<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Bogota');

$meses_es = [
    1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

$dia_num = date('j');
$mes_num = (int)date('n');
$anio_num = date('Y');

$_SESSION['fecha_completa_es'] = $dia_num . " de " . $meses_es[$mes_num] . " de " . $anio_num;

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

require_once __DIR__ . '/vendor/autoload.php';

// Actualizar automáticamente citas que ya pasaron (con 4 horas de tolerancia) y siguen pendientes a "No asistió" (Estado 4)
try {
    $pdoGlobal = \App\Config\Database::getInstance()->getConnection();
    $sqlAutoUpdate = "UPDATE cita SET ESTADO_CITA_ID = 4 WHERE FECHA_HORA < DATE_SUB(NOW(), INTERVAL 4 HOUR) AND ESTADO_CITA_ID = 1";
    $pdoGlobal->exec($sqlAutoUpdate);
} catch (\Exception $e) {
    // Silently ignore errors here to not break the app
}


// BLOQUE PACIENTE (PSR-4)
use App\Controllers\paciente\UsuarioController;
use App\Controllers\AuthController;
use App\Controllers\paciente\CitaController;
use App\Controllers\paciente\PagoController;
use App\Controllers\paciente\NotificacionPacienteController;

// BLOQUE ODONTÓLOGO (PSR-4)
use App\Controllers\odontologo\AgendaController;
use App\Controllers\odontologo\NotificacionController;
use App\Controllers\odontologo\PacienteController;
use App\Controllers\odontologo\PlanTratamientoController;
use App\Controllers\odontologo\TratamientoController;
use App\Controllers\odontologo\HistoriaClinicaController;
use App\Controllers\odontologo\FacturacionController;
use App\Controllers\odontologo\ReporteController;
use App\Controllers\odontologo\PerfilOdonController;

// BLOQUE ADMINISTRADOR (PSR-4)
use App\Controllers\administrador\PerfilAdminController;
use App\Controllers\administrador\NotificacionAdminController;
use App\Controllers\administrador\AgendaAdminController;
use App\Controllers\administrador\HorarioAdminController;
use App\Controllers\administrador\FacturaAdminController;
use App\Controllers\administrador\GestionUsuarioController;
use App\Controllers\administrador\ConsultorioAdminController;
use App\Controllers\administrador\ReporteAdminController;
use App\Controllers\administrador\OtrosAdminController;

// BLOQUE PACIENTE (PSR-4)
$controller     = new UsuarioController();
$authController = new AuthController();
$citaController = new CitaController();
$pagoController = new PagoController();
$notificacionPaciente = new NotificacionPacienteController();

// BLOQUE ODONTÓLOGO (PSR-4)
$agendaOdo          = new AgendaController();
$notificacionOdo    = new NotificacionController();
$pacienteOdo        = new PacienteController();
$planTratamientoOdo = new PlanTratamientoController();
$tratamientoOdo     = new TratamientoController();
$historiaCtrl       = new HistoriaClinicaController();
$facturacionCtrl    = new FacturacionController();
$reporteCtrl        = new ReporteController();
$perfilOdo          = new PerfilOdonController();

// BLOQUE ADMINISTRADOR (PSR-4)
$perfilAdminController  = new PerfilAdminController();
$agendaAdmin            = new AgendaAdminController();
$notificacionAdmin      = new NotificacionAdminController();
$facturaAdmin           = new FacturaAdminController();
$horarioAdmin           = new HorarioAdminController();
$gestionUsuarioAdmin    = new GestionUsuarioController();
$consultorioAdmin       = new ConsultorioAdminController();
$reporteAdmin           = new ReporteAdminController();
$otrosAdmin             = new OtrosAdminController();

$action = !empty($_GET['action']) ? $_GET['action'] : 'inicio';

// --- SEGURIDAD DE RUTAS GLOBALES ---
if (strpos($action, 'admin/') === 0) {
    if (!isset($_SESSION['usuario_id']) || !in_array((int)($_SESSION['usuario_rol'] ?? 0), [1, 4], true)) {
        header('Location: /LOGIN_ORIGINAL/login');
        exit;
    }
}

if (strpos($action, 'odontologo/') === 0) {
    if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol'] ?? 0) !== 2) {
        header('Location: /LOGIN_ORIGINAL/login');
        exit;
    }
}

$rutasPaciente = [
    'perfil', 'procesarActualizarPerfil', 'exportarPerfil', 'mis_citas', 'pedir_cita', 'guardar_cita',
    'reprogramar_cita', 'cancelar_cita', 'pagos', 'exportarPdf', 'exportarExcel', 'imprimir', 'verFactura',
    'historial_clinico', 'notificaciones', 'tratamientos'
];
if (strpos($action, 'paciente/') === 0 || in_array($action, $rutasPaciente, true)) {
    if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol'] ?? 0) !== 3) {
        header('Location: /LOGIN_ORIGINAL/login');
        exit;
    }
}
// -----------------------------------

switch ($action) {
    case 'inicio':
        require_once __DIR__ . '/src/Views/landing/pagina_principal.php';
        break;
    
    // ==========================================
    // 🔐 SEGURIDAD Y ACCESO (AuthController)
    // ==========================================
    case 'login':
        if (isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/dashboard");
            exit;
        }
        $authController->mostrarLogin();
        break;

    case 'procesarLogin':
        $authController->procesarLogin();
        break;

    case 'registro':
        $authController->mostrarRegistro();
        break;

    case 'procesarRegistro':
        $authController->procesarRegistro();
        break;

    case 'recuperar':
    case 'restablecer':
        $authController->mostrarRecuperar();
        break;

    case 'procesarRecuperar':
    case 'procesarRestablecer':
        $authController->procesarRecuperar();
        break;

    case 'salir': //PACIENTE
        $authController->salir();
        break;

    case 'salirAdmin':
        $authController->salirAdmin();
        break;

    // ==========================================
    // 👤 PERFIL DE USUARIO (UsuarioController)
    // ==========================================
    case 'perfil':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $controller->mostrarPerfil();
        break;
        
    case 'procesarActualizarPerfil':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $controller->procesarActualizarPerfil();
        break;

    case 'exportarPerfil':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $controller->exportarPerfil(); 
        break;
    
    // ==========================================
    // 📅 AGENDAMIENTO Y CITAS (CitaController)
    // ==========================================
    case 'mis_citas':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;   
        }
        $citaController->mostrarMisCitas();
        break;

    case 'pedir_cita':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $citaController->mostrarPedirCita();
        break;

    case 'guardar_cita':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $citaController->guardarCita();
        break;

    case 'paciente/cita/detalle-ajax':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesión expirada.']);
            exit;
        }
        $citaController->obtenerDetalleAjax();
        break;

    case 'reprogramar_cita':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $citaController->procesarReprogramarCita();
        break;

    case 'cancelar_cita':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $citaController->procesarCancelarCita();
        break;

    case 'paciente/cita/horas-disponibles-ajax':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesión expirada.']);
            exit;
        }
        $citaController->obtenerHorasDisponiblesAjax();
        break;

    case 'paciente/historial/exportar':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $citaController->exportarHistorial();
        break;

    // ==========================================
    // 💰 FINANZAS Y EXTRACTOS (PagoController)
    // ==========================================
    case 'pagos':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $pagoController->index();
        break;
        
    case 'paciente/pagos/guardar':
        $pagoController->guardarPago();
        break;

    case 'exportarPdf':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $pagoController->exportarPdf();
        break;

    case 'exportarExcel':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $pagoController->exportarExcel();
        break;

    case 'imprimir':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        $pagoController->imprimir();
        break;

    /*SE AGREGO ESTO PARA VER LA FACTURA */ 
    case 'verFactura':
    $controller = new \App\Controllers\paciente\PagoController();
    $controller->verFactura();
    break;

    // ==========================================
    // 🖥 DASHBOARD MULTI-ROL (ADMIN, ODONTÓLOGO, PACIENTE)
    // ==========================================
    case 'dashboard':
        $authController->mostrarDashboard();
        break;

    // ==========================================
    // 📄 HISTORIAL CLINICO
    // ==========================================
    case 'historial_clinico':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
        
        $citaController->mostrarHistorialClinico();
        break;
    
    case 'paciente/cita/detalle-historial-ajax':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesión expirada.']);
            exit;
        }
        $citaController->obtenerDetalleHistorialAjax();
        break;

    case 'paciente/historial_clinico/datos_odontograma':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Sesión expirada']);
            exit;
        }
        $historialCtrl = new \App\Controllers\odontologo\HistoriaClinicaController();
        $historialCtrl->datosOdontograma();
        break;

    // ==========================================
    // 🔔 NOTIFICACIONES
    // ==========================================
    case 'notificaciones':
        $notificacionPaciente->index();
        break;
        
    case 'paciente/notificaciones/marcar_leida':
        $notificacionPaciente->marcarLeida();
        break;

    case 'paciente/notificaciones/marcar_todas':
        $notificacionPaciente->marcarTodas();
        break;
    
    // ==========================================
    // 💉 TRATAMIENTOS
    // ==========================================
    case 'tratamientos':
        $dashboardPaciente = new \App\Controllers\paciente\DashboardPacienteController();
        $dashboardPaciente->tratamientos();
        break;
        




    // =========================================================================
    // 📅 AGENDA - ODONTOLOGO
    // =========================================================================
    case 'odontologo/agenda':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        require_once __DIR__ . '/src/Views/odontologo/agenda_odon.php';
        break;

    case 'odontologo/agenda/obtener_citas':
        $agendaOdo->obtenerCitas();
        break;

    case 'odontologo/agenda/obtener_citas_hoy':
        $agendaOdo->obtenerCitasHoy();
        break;

    case 'odontologo/agenda/obtener_citas_mes':
        $agendaOdo->obtenerCitasMes();
        break;

    case 'odontologo/agenda/obtener_horas_ocupadas':
        $agendaOdo->obtenerHorasOcupadas();
        break;

    case 'odontologo/agenda/reprogramar_cita':
        $agendaOdo->reprogramarCita();
        break;

    // =========================================================================
    // 💉 CATÁLOGO DE TRATAMIENTOS
    // =========================================================================
    case 'odontologo/tratamientos':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        require_once __DIR__ . '/src/Views/odontologo/tratamiento_odon.php';
        break;

    case 'odontologo/tratamientos/listar':
        $tratamientoOdo->listar();
        break;

    case 'odontologo/tratamientos/obtener_detalle':
        $tratamientoOdo->obtenerDetalle();
        break;

    case 'odontologo/tratamientos/obtener_metricas':
        $tratamientoOdo->obtenerMetricas();
        break;

    case 'odontologo/tratamientos/guardar':
        $tratamientoOdo->guardar();
        break;

    case 'odontologo/tratamientos/estadisticas':
        $tratamientoOdo->estadisticas();
        break;

    case 'odontologo/tratamientos/get_procedimientos':
        $tratamientoOdo->obtenerProcedimientosSelect();
        break;

    // =========================================================================
    // 👤 PANEL DE PACIENTES
    // =========================================================================
    case 'odontologo/pacientes':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $pacienteOdo->index();
        break;

    case 'odontologo/pacientes/listar':
        $pacienteOdo->listar();
        break;

    case 'odontologo/pacientes/buscar_doc':
        $pacienteOdo->buscarPorDocumento();
        break;

    case 'odontologo/pacientes/crear':
        $pacienteOdo->crear();
        break;

    // =========================================================================
    // 🦷 ODONTOGRAMA Y PLANES DE TRATAMIENTO
    // =========================================================================
    case 'odontologo/planes':
        if (!isset($_SESSION['usuario_id'])) { 
            header("Location: index.php?action=login"); 
            exit; 
        }
        // SOLUCIÓN: Instanciar el controlador y ejecutar el método index()
        $planesCtrl = new \App\Controllers\odontologo\PlanTratamientoController();
        $planesCtrl->index();
        break;
    case 'odontologo/planes/listar':
    if (!isset($_SESSION['usuario_id'])) { exit; }
    $planesCtrl = new \App\Controllers\odontologo\PlanTratamientoController();
    $planesCtrl->listarPiezas();
    break;

    case 'odontologo/planes/guardar':
    if (!isset($_SESSION['usuario_id'])) { exit; }
    $planesCtrl = new \App\Controllers\odontologo\PlanTratamientoController();
    $planesCtrl->guardarPlan();
    break;

    // =========================================================================
    // 📜 HISTORIA CLÍNICA Y EVOLUCIONES
    // =========================================================================
    case 'odontologo/historia':
    case 'odontologo/historial-clinico':
    case 'odontologo/historial_clinico':
        if (!isset($_SESSION['usuario_id'])) { 
            header("Location: index.php?action=login"); 
            exit; 
        }
        $historialCtrl = new \App\Controllers\odontologo\HistoriaClinicaController();
        $historialCtrl->renderizarHistorial(); 
        break;

    // NUEVA RUTA INTERMEDIA PARA EL BOTÓN TRATAMIENTOS
    case 'odontologo/historial_clinico/procesar_tratamientos':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        $historialCtrl = new \App\Controllers\odontologo\HistoriaClinicaController();
        $historialCtrl->completarYRedirigirTratamientos();
        break;

    // RUTA PARA GUARDAR Y ENVIAR PDF POR CORREO AL PACIENTE
    case 'odontologo/historial_clinico/guardar_y_enviar_pdf':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Sesión expirada']);
            exit;
        }
        $historialCtrl = new \App\Controllers\odontologo\HistoriaClinicaController();
        $historialCtrl->guardarYEnviarPdf();
        break;

    // AQUÍ ESTÁ EL REQUISITO FALANTE: El endpoint que llama el JS de los dientes
    case 'odontologo/historial_clinico/datos_odontograma':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Sesión expirada']);
            exit;
        }
        $historialCtrl = new \App\Controllers\odontologo\HistoriaClinicaController();
        $historialCtrl->datosOdontograma();
        break;

    // Endpoint para la línea de tiempo del historial clínico
    case 'odontologo/historial_clinico/datos_timeline':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Sesión expirada']);
            exit;
        }
        $historialCtrl = new \App\Controllers\odontologo\HistoriaClinicaController();
        $historialCtrl->datosTimeline();
        break;

    // =========================================================================
    // 🦷 API: GUARDAR ODONTOGRAMA EN LOTE Y REDIRIGIR
    // =========================================================================
    case 'odontologo/odontograma/guardar_lote':
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['status' => 'error', 'error' => 'Sesión no iniciada']);
            exit;
        }

        // Leer el JSON que envía el fetch de JavaScript
        $input = json_decode(file_get_contents('php://input'), true);
        $pacienteId = isset($input['paciente_id']) ? intval($input['paciente_id']) : null;
        $dientes = isset($input['dientes']) ? $input['dientes'] : [];

        if (!$pacienteId) {
            echo json_encode(['status' => 'error', 'error' => 'ID de paciente inválido']);
            exit;
        }

        // Aquí tu sistema procesará el guardado en la base de datos si lo requieres.
        // Por ahora, respondemos de forma exitosa para permitir que el JS haga la redirección.
        echo json_encode([
            'status' => 'success',
            'message' => 'Lote procesado correctamente',
            'cantidad' => count($dientes)
        ]);
        exit;

    // =========================================================================
    // 🦷 MÓDULO ODONTÓLOGO: PLANES DE TRATAMIENTO
    // =========================================================================
    case 'odontologo/planes':
    case 'odontologo/planes-tratamiento':
    case 'odontologo/planes_tratamiento':
        if (!isset($_SESSION['usuario_id'])) { 
            header("Location: index.php?action=login"); 
            exit; 
        }
        $planesCtrl = new \App\Controllers\odontologo\PlanTratamientoController();
        $planesCtrl->index(); 
        break;

    // =========================================================================
    // 🦷 MÓDULO ODONTÓLOGO: LISTAR PIEZAS DEL ODONTOGRAMA (RETORNO JSON EN FETCH)
    // =========================================================================
    case 'odontologo/planes/listarPiezas':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        $planesCtrl = new \App\Controllers\odontologo\PlanTratamientoController();
        $planesCtrl->listarPiezas(); // <--- Llamamos al método limpio en tu controlador
        break;
    
    // =========================================================================
    // RUTA PARA GUARDADO MASIVO DEL ODONTOGRAMA
    // =========================================================================
    case 'odontologo/procedimientos/guardarMasivo':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Sesión inválida']);
            exit;
        }
        $planesCtrl = new \App\Controllers\odontologo\PlanTratamientoController();
        $planesCtrl->guardarMasivo();
        break;

    // =========================================================================
    // 🔔 NOTIFICACIONES ASÍNCRONAS
    // =========================================================================
    case 'odontologo/notificaciones':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $notificacionOdo->index();
        break;

    case 'odontologo/notificaciones/marcar_leida':
        $notificacionOdo->marcarComoLeida();
        break;
        
    case 'odontologo/notificaciones/marcar_todas_leidas':
        $notificacionOdo->marcarTodas();
        break;

    // =========================================================================
    // 💰 FACTURACIÓN GENERAL
    // =========================================================================
    case 'odontologo/facturacion':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $facturacionCtrl->index();
        break;

    // =========================================================================
    // 📅 MÓDULO ODONTÓLOGO: ENDPOINTS DE LA AGENDA / CALENDARIO
    // =========================================================================
    case 'odontologo/agenda/obtener_detalle_cita':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Sesión inválida']);
            exit;
        }
        $agendaCtrl = new \App\Controllers\odontologo\AgendaController();
        $agendaCtrl->obtenerDetalleCita();
        break;

    case 'odontologo/agenda/obtener_lista_procedimientos':
        // Si no hay sesión, NO redirecciones, solo devuelve error JSON para el JS
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            http_response_code(401); // Error de no autorizado
            echo json_encode(['error' => 'Sesión expirada']);
            exit;
        }
        $agendaCtrl = new \App\Controllers\odontologo\AgendaController();
        $agendaCtrl->obtenerListaProcedimientos();
        break;

    case 'odontologo/agenda/buscar_paciente':
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['encontrado' => false, 'error' => 'Sesión inválida']);
            exit;
        }
        $agendaCtrl = new \App\Controllers\odontologo\AgendaController();
        $agendaCtrl->buscarPaciente();
        break;

    // =========================================================================
    // 📊 REPORTES Y AUDITORÍA CONTABLE
    // =========================================================================
    case 'odontologo/reportes':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $reporteCtrl->index();
        break;

    // =========================================================================
    // 🤵 PERFIL
    // =========================================================================
    case 'odontologo/perfil':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $perfilOdo->mostrarPerfil();
        break;

    case 'odontologo/perfil/actualizar':
        if (!isset($_SESSION['usuario_id'])) { header("Location: /LOGIN_ORIGINAL/login"); exit; }
        $perfilOdo->actualizarPerfil();
        break;




    // =========================================================================
    // 📅 AGENDA - ADMINISTRADOR
    // =========================================================================
    case 'admin/agenda':
        $agendaAdmin->mostrarAgenda();
        break;
 
    case 'admin/agenda/obtener_citas':
        $agendaAdmin->obtenerCitas();
        break;
 
    case 'admin/agenda/obtener_citas_hoy':
        $agendaAdmin->obtenerCitasHoy();
        break;
 
    case 'admin/agenda/obtener_horas_ocupadas':
        $agendaAdmin->obtenerHorasOcupadas();
        break;
 
    case 'admin/agenda/guardar_cita':
        $agendaAdmin->guardarCita();
        break;
 
    case 'admin/agenda/cancelar_cita':
        $agendaAdmin->cancelarCita();
        break;
 
    case 'admin/agenda/reprogramar_cita':
        $agendaAdmin->reprogramarCita();
        break;

    case 'admin/agenda/carga_masiva':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $agendaAdmin->cargaMasiva();
        break;

    // =========================================================================
    // 🔔 NOTIFICACIONES
    // =========================================================================
    case 'admin/notificaciones':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $notificacionAdmin->index();
        break;

    case 'admin/notificaciones/listar':
        $notificacionAdmin->listar();
        break;

    case 'admin/notificaciones/marcar_leida':
        $notificacionAdmin->marcarComoLeida();
        break;

    case 'admin/notificaciones/marcar_todas':
        $notificacionAdmin->marcarTodas();
        break;

    // =========================================================================
    // 🛡️ PERFIL
    // =========================================================================
    case 'admin/perfil':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $perfilAdminController->mostrarPerfil();
        break;

    case 'admin/perfil/actualizar':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $perfilAdminController->procesarActualizarPerfil();
        break;

    // =========================================================================
    // 💳 Facturación
    // =========================================================================
    case 'admin/facturacion':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $facturaAdmin->mostrarFacturacion();
        break;

    case 'admin/facturacion/listar':
        $facturaAdmin->obtenerFacturas();
        break;

    case 'admin/facturacion/guardar':
        $facturaAdmin->guardarFactura();
        break;
    
    case 'admin/facturacion/configuracion':
        $facturaAdmin->obtenerConfiguracion();
        break;

    case 'admin/facturacion/configuracion/actualizar':
        $facturaAdmin->actualizarConfiguracion();
        break;

    case 'admin/facturacion/liquidacion/calcular':
        $facturaAdmin->calcularLiquidacion();
        break;

    case 'admin/facturacion/liquidacion/guardar':
        $facturaAdmin->guardarLiquidacion();
        break;

    case 'admin/facturacion/pago/guardar':
        $facturaAdmin->guardarPago(); 
        break;

    case 'admin/facturacion/resumen':
        $facturaAdmin->obtenerResumenKPIs();
        break;

    case 'admin/facturacion/exportar':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        // Esta es la función que crearemos en el siguiente paso para armar el PDF/Excel
        $facturaAdmin->exportarReporte(); 
        break;

    case 'admin/facturacion/exportar-individual':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $facturaAdmin->exportarFacturaIndividual(); 
        break;

    case 'admin/facturacion/carga_masiva':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $facturaAdmin->cargaMasiva(); 
        break;

    // =========================================================================
    // 🕜 HORARIOS (Corrección de rutas)
    // =========================================================================
    case 'admin/horarios':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $horarioAdmin->mostrarVista();
        break;

    case 'admin/horarios/listar':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            exit; 
        }
        $horarioAdmin->listar();
        exit;

    case 'admin/horarios/guardar':
        if (!isset($_SESSION['usuario_id'])) { exit; }
        $horarioAdmin->guardar();
        exit;

    case 'admin/horarios/eliminar':
        if (!isset($_SESSION['usuario_id'])) { exit; }
        $horarioAdmin->eliminar();
        exit;
    
    case 'admin/horarios/listar-por-fecha':
    $horarioAdmin->listarPorFecha();
    exit;
    
    case 'admin/horarios/carga_masiva':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $horarioAdmin->cargaMasiva();
        break;

    // =========================================================================
    // 👥 GESTIÓN DE USUARIOS
    // =========================================================================
    case 'admin/gestion_usuario':
        if (!isset($_SESSION['usuario_id']) || !in_array((int)($_SESSION['usuario_rol'] ?? 0), [1, 4], true)) {
            header('Location: /LOGIN_ORIGINAL/login'); exit;
        }
        $gestionUsuarioAdmin->mostrarVista();
        break;
        
    case 'api_gestion_usuario':
        if (!isset($_SESSION['usuario_id']) || !in_array((int)($_SESSION['usuario_rol'] ?? 0), [1, 4], true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'mensaje' => 'No autorizado.']);
            exit;
        }
        $gestionUsuarioAdmin->api();
        break;
    
    case 'admin/gestion_usuario/carga_masiva':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $gestionUsuarioAdmin->cargaMasiva();
        break;

    // =========================================================================
    // 🏥 GESTIÓN DE CONSULTORIOS
    // =========================================================================
    case 'admin/consultorios':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $consultorioAdmin->mostrarVista(); 
        break;

    case 'admin/consultorio/listar':
        $consultorioAdmin->listarConsultorios();
        break;

    case 'admin/consultorio/detalle':
        $consultorioAdmin->obtenerDetalle();
        break;

    case 'admin/consultorio/crear':
        $consultorioAdmin->crearConsultorio();
        break;

    case 'admin/consultorio/actualizar':
        $consultorioAdmin->actualizarConsultorio();
        break;

    case 'admin/consultorio/eliminar':
        $consultorioAdmin->eliminarConsultorio();
        break;

    case 'admin/consultorio/asignar':
        $consultorioAdmin->asignarOdontologo();
        break;

    case 'admin/consultorio/desasignar':
        $consultorioAdmin->desasignarOdontologo();
        break;

    case 'admin/consultorio/estado':
        $consultorioAdmin->cambiarEstado();
        break;

    case 'admin/consultorio/odontologos-disponibles':
        $consultorioAdmin->obtenerOdontologosDisponibles();
        break;

    case 'admin/consultorio/odontologos-todos':
        $consultorioAdmin->obtenerTodosOdontologos();
        break;

    case 'admin/consultorio/historial':
        $consultorioAdmin->obtenerHistorial();
        break;

    case 'admin/consultorio/kpis':
        $consultorioAdmin->obtenerKPIs();
        break;
    
    // =========================================================================
    // 🏥 REPORTES
    // =========================================================================
    case 'admin/reportes':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        require_once __DIR__ . '/src/Views/administrador/reportes.php';
        break;

    case 'admin/reportes/api':
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'mensaje' => 'No autorizado.']);
            exit;
        }
        $reporteAdmin->api();
        break;

    // =========================================================================
    // ⚙️ OTROS CATÁLOGOS
    // =========================================================================
    case 'admin/otros':
        if (!isset($_SESSION['usuario_id'])) { header('Location: /LOGIN_ORIGINAL/login'); exit; }
        $otrosAdmin->mostrarVista();
        break;

    case 'admin/otros/guardar_procedimiento':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->guardarProcedimiento();
        break;

    case 'admin/otros/editar_procedimiento':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->editarProcedimiento();
        break;

    case 'admin/otros/eliminar_procedimiento':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->eliminarProcedimiento();
        break;

    case 'admin/otros/guardar_especialidad':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->guardarEspecialidad();
        break;

    case 'admin/otros/editar_especialidad':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->editarEspecialidad();
        break;

    case 'admin/otros/eliminar_especialidad':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->eliminarEspecialidad();
        break;

    case 'admin/otros/guardar_eps':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->guardarEps();
        break;

    case 'admin/otros/editar_eps':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->editarEps();
        break;

    case 'admin/otros/eliminar_eps':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->eliminarEps();
        break;

    case 'admin/otros/guardar_alergia':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->guardarAlergia();
        break;

    case 'admin/otros/editar_alergia':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->editarAlergia();
        break;

    case 'admin/otros/eliminar_alergia':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->eliminarAlergia();
        break;

    case 'admin/otros/guardar_enfermedad':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->guardarEnfermedad();
        break;

    case 'admin/otros/editar_enfermedad':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->editarEnfermedad();
        break;

    case 'admin/otros/eliminar_enfermedad':
        if (!isset($_SESSION['usuario_id'])) { 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']); 
            exit; 
        }
        $otrosAdmin->eliminarEnfermedad();
        break;


    default:
        require_once __DIR__ . '/src/Views/landing/pagina_principal.php';
        break;
}
?>