<?php

use Illuminate\Support\Facades\Route;

// ==========================================
// CONTROLADORES DE AUTENTICACIÓN
// ==========================================
use App\Http\Controllers\Auth\AuthController;

// ==========================================
// CONTROLADORES ADMINISTRADOR
// ==========================================
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\AgendaController as AdminAgenda;
use App\Http\Controllers\Admin\GestionUsuarioController as AdminGestionUsuario;
use App\Http\Controllers\Admin\ConsultorioController as AdminConsultorio;
use App\Http\Controllers\Admin\HorarioController as AdminHorario;
use App\Http\Controllers\Admin\FacturacionController as AdminFacturacion;
use App\Http\Controllers\Admin\ReporteController as AdminReporte;
use App\Http\Controllers\Admin\OtrosController as AdminOtros;
use App\Http\Controllers\Admin\NotificacionController as AdminNotificacion;
use App\Http\Controllers\Admin\PerfilController as AdminPerfil;

// ==========================================
// CONTROLADORES ODONTÓLOGO
// ==========================================
use App\Http\Controllers\Odontologo\DashboardController as OdoDashboard;
use App\Http\Controllers\Odontologo\AgendaController as OdoAgenda;
use App\Http\Controllers\Odontologo\PacienteController as OdoPaciente;
use App\Http\Controllers\Odontologo\HistoriaClinicaController as OdoHistoriaClinica;
use App\Http\Controllers\Odontologo\TratamientoController as OdoTratamiento;
use App\Http\Controllers\Odontologo\PlanTratamientoController as OdoPlanTratamiento;
use App\Http\Controllers\Odontologo\FacturacionController as OdoFacturacion;
use App\Http\Controllers\Odontologo\ReporteController as OdoReporte;
use App\Http\Controllers\Odontologo\NotificacionController as OdoNotificacion;
use App\Http\Controllers\Odontologo\PerfilController as OdoPerfil;

// ==========================================
// CONTROLADORES PACIENTE
// ==========================================
use App\Http\Controllers\Paciente\DashboardController as PacDashboard;
use App\Http\Controllers\Paciente\CitaController as PacCita;
use App\Http\Controllers\Paciente\HistorialController as PacHistorial;
use App\Http\Controllers\Paciente\TratamientoController as PacTratamiento;
use App\Http\Controllers\Paciente\PagoController as PacPago;
use App\Http\Controllers\Paciente\PerfilController as PacPerfil;
use App\Http\Controllers\Paciente\NotificacionController as PacNotificacion;

// =========================================================================
// RUTAS PÚBLICAS Y LANDING PAGE
// =========================================================================
Route::get('/', function () {
    return view('landing.pagina_principal');
})->name('inicio');

Route::get('/inicio', function () {
    return view('landing.pagina_principal');
});

Route::post('/contacto', function (\Illuminate\Http\Request $request) {
    $nombre = trim($request->input('nombre', ''));
    $email = trim($request->input('email', ''));
    $telefono = trim($request->input('telefono', ''));
    $servicio = trim($request->input('servicio_interes', ''));
    $mensaje = trim($request->input('mensaje', ''));

    if (empty($nombre) || empty($email) || empty($mensaje)) {
        return back()->with('contacto_error', 'Por favor diligencia los campos requeridos (nombre, correo y mensaje).');
    }

    $procId = null;
    if (!empty($servicio)) {
        $proc = DB::table('procedimientos')
            ->where('NOMBRE_PROCEDIMIENTO', 'LIKE', "%{$servicio}%")
            ->value('ID_PROCEDIMIENTO');
        if ($proc) {
            $procId = $proc;
        }
    }

    DB::table('contacto_landing')->insert([
        'NOMBRE_COMPLETO' => $nombre,
        'CORREO' => $email,
        'TELEFONO' => $telefono ?: null,
        'SERVICIO_INTERES' => $servicio ?: null,
        'MENSAJE' => $mensaje,
        'PROCEDIMIENTOS_ID_PROCEDIMIENTO' => $procId,
        'FECHA_REGISTRO' => now(),
        'ESTADO' => 'PENDIENTE'
    ]);

    \App\Helpers\Notificador::enviarAAdmin(
        34,
        "Nuevo mensaje de contacto web de {$nombre}. Tel: " . ($telefono ?: 'No especificado') . ". Mensaje: " . \Illuminate\Support\Str::limit($mensaje, 60)
    );

    return back()->with('contacto_success', '¡Gracias por contactarnos! Tu mensaje ha sido recibido exitosamente. Uno de nuestros asesores se comunicará contigo pronto.');
})->name('contacto.post');

// =========================================================================
// RUTAS DE AUTENTICACIÓN
// =========================================================================
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'procesarLogin'])->name('login.post');
Route::post('/procesarLogin', [AuthController::class, 'procesarLogin']);

Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'procesarRegistro'])->name('registro.post');
Route::post('/procesarRegistro', [AuthController::class, 'procesarRegistro']);

Route::get('/recuperar', [AuthController::class, 'mostrarRecuperar'])->name('recuperar');
Route::post('/recuperar', [AuthController::class, 'procesarRecuperar'])->name('recuperar.post');
Route::post('/procesarRecuperar', [AuthController::class, 'procesarRecuperar']);

// Cierre de sesión (destruye sesión y redirige limpiamente)
Route::any('/salir', [AuthController::class, 'salir'])->name('logout');
Route::any('/logout', [AuthController::class, 'salir']);

// Router inteligente según rol
Route::get('/dashboard', [AuthController::class, 'mostrarDashboard'])->name('dashboard')->middleware('ruta_segura');

// =========================================================================
// RUTA SEGURA: MÓDULOS DEL PACIENTE (ROL 3)
// =========================================================================
Route::middleware(['ruta_segura:3'])->group(function () {
    // 1. Dashboard
    Route::get('/paciente/dashboard', [PacDashboard::class, 'index'])->name('paciente.dashboard');
    Route::get('/paciente/inicio', [PacDashboard::class, 'index']);

    // 2. Citas
    Route::get('/paciente/citas', [PacCita::class, 'index'])->name('paciente.citas.index');
    Route::get('/paciente/mis_citas', [PacCita::class, 'index']);
    Route::get('/paciente/pedir_cita', [PacCita::class, 'pedirCita'])->name('paciente.citas.pedir');
    Route::post('/paciente/citas/guardar', [PacCita::class, 'guardarCita'])->name('paciente.citas.guardar');
    Route::any('/paciente/cita/horas-disponibles-ajax', [PacCita::class, 'horasDisponiblesAjax']);
    Route::any('/paciente/cita/detalle-ajax', [PacCita::class, 'detalleAjax']);
    Route::any('/paciente/cita/detalle-historial-ajax', [PacHistorial::class, 'detalleHistorialAjax']);
    Route::post('/paciente/cita/cancelar', [PacCita::class, 'cancelarCita'])->name('paciente.citas.cancelar');
    Route::post('/paciente/cita/reprogramar', [PacCita::class, 'reprogramarCita'])->name('paciente.citas.reprogramar');

    // 3. Historial Clínico
    Route::get('/paciente/historial', [PacHistorial::class, 'index'])->name('paciente.historial');
    Route::get('/paciente/historial_clinico', [PacHistorial::class, 'index']);
    Route::get('/paciente/historial/exportar', [PacHistorial::class, 'exportar'])->name('paciente.historial.exportar');
    Route::any('/paciente/historial_clinico/datos_odontograma', [PacHistorial::class, 'datosOdontograma']);

    // 4. Tratamientos
    Route::get('/paciente/tratamientos', [PacTratamiento::class, 'index'])->name('paciente.tratamientos');

    // 5. Facturación y Pagos
    Route::get('/paciente/pagos', [PacPago::class, 'index'])->name('paciente.pagos');
    Route::post('/paciente/pagos/guardar', [PacPago::class, 'guardarPago']);
    Route::get('/paciente/pagos/exportar-excel', [PacPago::class, 'exportarExcel']);
    Route::get('/paciente/pagos/imprimir', [PacPago::class, 'imprimir']);

    // 6. Perfil y Familiares / Menores a Cargo
    Route::get('/paciente/perfil', [PacPerfil::class, 'index'])->name('paciente.perfil');
    Route::post('/paciente/perfil/actualizar', [PacPerfil::class, 'actualizar'])->name('paciente.perfil.actualizar');
    Route::post('/paciente/perfil/cambiar-password', [PacPerfil::class, 'cambiarPassword'])->name('paciente.perfil.cambiarPassword');
    Route::get('/paciente/perfil/exportar', [PacPerfil::class, 'exportar']);
    Route::post('/paciente/hijos/registrar', [PacPerfil::class, 'registrarHijo'])->name('paciente.hijos.registrar');
    Route::post('/paciente/hijos/{id}/desvincular', [PacPerfil::class, 'desvincularHijo'])->name('paciente.hijos.desvincular');

    // 7. Notificaciones
    Route::get('/paciente/notificaciones', [PacNotificacion::class, 'index'])->name('paciente.notificaciones');
    Route::post('/paciente/notificaciones/marcar_leida', [PacNotificacion::class, 'marcarLeida']);
    Route::post('/paciente/notificaciones/marcar_todas', [PacNotificacion::class, 'marcarTodas']);

    // Rutas directas de compatibilidad para paciente
    Route::get('/mis_citas', [PacCita::class, 'index']);
    Route::get('/pedir_cita', [PacCita::class, 'pedirCita']);
    Route::post('/guardar_cita', [PacCita::class, 'guardarCita']);
    Route::post('/cancelar_cita', [PacCita::class, 'cancelarCita']);
    Route::post('/reprogramar_cita', [PacCita::class, 'reprogramarCita']);
    Route::get('/historial_clinico', [PacHistorial::class, 'index']);
    Route::get('/imprimir_historial', [PacHistorial::class, 'exportar']);
    Route::get('/tratamientos', [PacTratamiento::class, 'index']);
    Route::get('/pagos', [PacPago::class, 'index']);
    Route::get('/exportarExcel', [PacPago::class, 'exportarExcel']);
    Route::get('/imprimir', [PacPago::class, 'imprimir']);
    Route::get('/exportarPdf', [PacPago::class, 'imprimir']);
    Route::get('/perfil', [PacPerfil::class, 'index']);
    Route::post('/procesarActualizarPerfil', [PacPerfil::class, 'actualizar']);
    Route::get('/exportarPerfil', [PacPerfil::class, 'exportar']);
    Route::get('/notificaciones', [PacNotificacion::class, 'index']);
});

// =========================================================================
// RUTA SEGURA: MÓDULOS DE ADMINISTRADOR (ROLES 1 Y 4)
// =========================================================================
Route::middleware(['ruta_segura:1,4'])->group(function () {
    Route::get('/admin/inicio', [AdminDashboard::class, 'index']);
    Route::get('/admin/agenda', [AdminAgenda::class, 'index']);
    Route::any('/api_agenda/{op}', [AdminAgenda::class, 'api']);

    Route::get('/admin/usuarios', [AdminGestionUsuario::class, 'index']);
    Route::any('/api_gestion_usuario', [AdminGestionUsuario::class, 'api']);

    Route::get('/admin/consultorios', [AdminConsultorio::class, 'index']);
    Route::any('/api_consultorios/{op}', [AdminConsultorio::class, 'api']);

    Route::get('/admin/horarios', [AdminHorario::class, 'index']);
    Route::any('/api_horarios/{op}', [AdminHorario::class, 'api']);

    Route::get('/admin/facturacion', [AdminFacturacion::class, 'index']);
    Route::any('/api_facturacion/{sub}/{op}', [AdminFacturacion::class, 'apiSub']);
    Route::any('/api_facturacion/{op}', [AdminFacturacion::class, 'api']);
    Route::post('/admin/facturacion/carga_masiva', [AdminFacturacion::class, 'cargaMasiva']);
    Route::get('/admin/facturacion/plantilla', [AdminFacturacion::class, 'descargarPlantillaCsv']);

    Route::get('/admin/reportes', [AdminReporte::class, 'index']);
    Route::any('/api_reportes', [AdminReporte::class, 'api']);
    Route::any('/admin/reportes/api', [AdminReporte::class, 'api']);

    Route::get('/admin/otros', [AdminOtros::class, 'index']);
    Route::any('/api_otros/{op}', [AdminOtros::class, 'api']);
    Route::any('/admin/otros/{op}', [AdminOtros::class, 'api']);

    Route::get('/admin/notificaciones', [AdminNotificacion::class, 'index']);
    Route::any('/api_notificaciones/{op}', [AdminNotificacion::class, 'api']);
    Route::any('/admin/notificaciones/{op}', [AdminNotificacion::class, 'api']);

    Route::get('/admin/perfil', [AdminPerfil::class, 'index']);
    Route::post('/admin/perfil/actualizar', [AdminPerfil::class, 'actualizar']);
});

// =========================================================================
// RUTA SEGURA: MÓDULOS DE ODONTÓLOGO (ROL 2)
// =========================================================================
Route::middleware(['ruta_segura:2'])->group(function () {
    Route::get('/odontologo/inicio', [OdoDashboard::class, 'index']);
    Route::get('/odontologo/dashboard', [OdoDashboard::class, 'index']);

    Route::get('/odontologo/agenda', [OdoAgenda::class, 'index']);
    Route::any('/odontologo/agenda/{op}', [OdoAgenda::class, 'api']);

    Route::get('/odontologo/pacientes', [OdoPaciente::class, 'index']);
    Route::any('/odontologo/pacientes/{op}', [OdoPaciente::class, 'api']);

    Route::get('/odontologo/historia', [OdoHistoriaClinica::class, 'index']);
    Route::get('/odontologo/historial-clinico', [OdoHistoriaClinica::class, 'index']);
    Route::get('/odontologo/historial_clinico', [OdoHistoriaClinica::class, 'index']);
    Route::any('/odontologo/historial-clinico/{op}', [OdoHistoriaClinica::class, 'api']);
    Route::any('/odontologo/historial_clinico/{op}', [OdoHistoriaClinica::class, 'api']);
    Route::any('/odontologo/odontograma/guardar_lote', [OdoHistoriaClinica::class, 'guardarLote']);

    Route::get('/odontologo/tratamientos', [OdoTratamiento::class, 'index']);
    Route::any('/odontologo/tratamientos/{op}', [OdoTratamiento::class, 'api']);

    Route::get('/odontologo/planes', [OdoPlanTratamiento::class, 'index']);
    Route::get('/odontologo/planes-tratamiento', [OdoPlanTratamiento::class, 'index']);
    Route::get('/odontologo/planes_tratamiento', [OdoPlanTratamiento::class, 'index']);
    Route::any('/odontologo/planes/{op}', [OdoPlanTratamiento::class, 'api']);
    Route::any('/odontologo/procedimientos/guardarMasivo', [OdoPlanTratamiento::class, 'guardarMasivo']);

    Route::get('/odontologo/facturacion', [OdoFacturacion::class, 'index']);
    Route::any('/odontologo/facturacion/{op}', [OdoFacturacion::class, 'api']);

    Route::get('/odontologo/reportes', [OdoReporte::class, 'index']);
    Route::any('/odontologo/reportes/{op}', [OdoReporte::class, 'api']);

    Route::get('/odontologo/notificaciones', [OdoNotificacion::class, 'index']);
    Route::any('/odontologo/notificaciones/{op}', [OdoNotificacion::class, 'api']);

    Route::get('/odontologo/perfil', [OdoPerfil::class, 'index']);
    Route::post('/odontologo/perfil/actualizar', [OdoPerfil::class, 'actualizarPerfil']);

    Route::any('/Controllers/eliminar_notificacion.php', [OdoNotificacion::class, 'marcarComoLeida']);
});

// =========================================================================
// COMPATIBILIDAD CON PREFIJO /LOGIN_ORIGINAL/
// =========================================================================
Route::prefix('LOGIN_ORIGINAL')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrarLogin']);
    Route::post('/procesarLogin', [AuthController::class, 'procesarLogin']);
    Route::get('/registro', [AuthController::class, 'mostrarRegistro']);
    Route::post('/procesarRegistro', [AuthController::class, 'procesarRegistro']);
    Route::get('/recuperar', [AuthController::class, 'mostrarRecuperar']);
    Route::post('/procesarRecuperar', [AuthController::class, 'procesarRecuperar']);
    Route::get('/dashboard', [AuthController::class, 'mostrarDashboard'])->middleware('ruta_segura');
    Route::any('/salir', [AuthController::class, 'salir']);

    // Rutas protegidas de paciente con prefijo
    Route::middleware(['ruta_segura:3'])->group(function () {
        Route::get('/mis_citas', [PacCita::class, 'index']);
        Route::get('/pedir_cita', [PacCita::class, 'pedirCita']);
        Route::post('/guardar_cita', [PacCita::class, 'guardarCita']);
        Route::any('/paciente/cita/horas-disponibles-ajax', [PacCita::class, 'horasDisponiblesAjax']);
        Route::any('/paciente/cita/detalle-ajax', [PacCita::class, 'detalleAjax']);
        Route::any('/paciente/cita/detalle-historial-ajax', [PacHistorial::class, 'detalleHistorialAjax']);
        Route::any('/paciente/historial_clinico/datos_odontograma', [PacHistorial::class, 'datosOdontograma']);
        Route::post('/cancelar_cita', [PacCita::class, 'cancelarCita']);
        Route::post('/reprogramar_cita', [PacCita::class, 'reprogramarCita']);
        Route::get('/historial_clinico', [PacHistorial::class, 'index']);
        Route::get('/tratamientos', [PacTratamiento::class, 'index']);
        Route::get('/pagos', [PacPago::class, 'index']);
        Route::post('/paciente/pagos/guardar', [PacPago::class, 'guardarPago']);
        Route::get('/exportarPdf', [PacPago::class, 'imprimir']);
        Route::get('/imprimir', [PacPago::class, 'imprimir']);
        Route::get('/exportarExcel', [PacPago::class, 'exportarExcel']);
        Route::get('/perfil', [PacPerfil::class, 'index']);
        Route::post('/procesarActualizarPerfil', [PacPerfil::class, 'actualizar']);
        Route::post('/cambiarPassword', [PacPerfil::class, 'cambiarPassword']);
        Route::post('/paciente/perfil/cambiar-password', [PacPerfil::class, 'cambiarPassword']);
        Route::get('/exportarPerfil', [PacPerfil::class, 'exportar']);
        Route::get('/notificaciones', [PacNotificacion::class, 'index']);
        Route::post('/paciente/notificaciones/marcar_leida', [PacNotificacion::class, 'marcarLeida']);
        Route::post('/paciente/notificaciones/marcar_todas', [PacNotificacion::class, 'marcarTodas']);
    });
});