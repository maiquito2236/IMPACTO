<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\GestionUsuarioController;
use App\Http\Controllers\Admin\ConsultorioController;
use App\Http\Controllers\Admin\HorarioController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/inicio', [DashboardController::class, 'index']);

Route::get('/admin/agenda', [AgendaController::class, 'index']);
Route::any('/api_agenda/{op}', [AgendaController::class, 'api']);

Route::get('/admin/usuarios', [GestionUsuarioController::class, 'index']);
Route::any('/api_gestion_usuario', [GestionUsuarioController::class, 'api']);

Route::get('/admin/consultorios', [ConsultorioController::class, 'index']);
Route::any('/api_consultorios/{op}', [ConsultorioController::class, 'api']);

Route::get('/admin/horarios', [HorarioController::class, 'index']);
Route::any('/api_horarios/{op}', [HorarioController::class, 'api']);

