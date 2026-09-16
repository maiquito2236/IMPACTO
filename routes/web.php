<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\GestionUsuarioController;
use App\Http\Controllers\Admin\ConsultorioController;
use App\Http\Controllers\Admin\HorarioController;
use App\Http\Controllers\Admin\FacturacionController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\OtrosController;
use App\Http\Controllers\Admin\NotificacionController;
use App\Http\Controllers\Admin\PerfilController;

Route::get('/', function () {
    return view('welcome');
});

// 1. Dashboard
Route::get('/admin/inicio', [DashboardController::class, 'index']);

// 2. Agenda y Citas
Route::get('/admin/agenda', [AgendaController::class, 'index']);
Route::any('/api_agenda/{op}', [AgendaController::class, 'api']);

// 3. Gestión de Usuarios
Route::get('/admin/usuarios', [GestionUsuarioController::class, 'index']);
Route::any('/api_gestion_usuario', [GestionUsuarioController::class, 'api']);

// 4. Gestión de Consultorios
Route::get('/admin/consultorios', [ConsultorioController::class, 'index']);
Route::any('/api_consultorios/{op}', [ConsultorioController::class, 'api']);

// 5. Gestión de Horarios
Route::get('/admin/horarios', [HorarioController::class, 'index']);
Route::any('/api_horarios/{op}', [HorarioController::class, 'api']);

// 6. Facturación y Caja
Route::get('/admin/facturacion', [FacturacionController::class, 'index']);
Route::any('/api_facturacion/{sub}/{op}', [FacturacionController::class, 'apiSub']);
Route::any('/api_facturacion/{op}', [FacturacionController::class, 'api']);
Route::post('/admin/facturacion/carga_masiva', [FacturacionController::class, 'cargaMasiva']);
Route::get('/admin/facturacion/plantilla', [FacturacionController::class, 'descargarPlantillaCsv']);

// 7. Reportes y Estadísticas
Route::get('/admin/reportes', [ReporteController::class, 'index']);
Route::any('/api_reportes', [ReporteController::class, 'api']);
Route::any('/admin/reportes/api', [ReporteController::class, 'api']);

// 8. Otros Catálogos
Route::get('/admin/otros', [OtrosController::class, 'index']);
Route::any('/api_otros/{op}', [OtrosController::class, 'api']);
Route::any('/admin/otros/{op}', [OtrosController::class, 'api']);

// 9. Notificaciones
Route::get('/admin/notificaciones', [NotificacionController::class, 'index']);
Route::any('/api_notificaciones/{op}', [NotificacionController::class, 'api']);
Route::any('/admin/notificaciones/{op}', [NotificacionController::class, 'api']);

// 10. Mi Perfil
Route::get('/admin/perfil', [PerfilController::class, 'index']);
Route::post('/admin/perfil/actualizar', [PerfilController::class, 'actualizar']);
