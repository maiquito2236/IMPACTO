<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ConsultorioController;

Route::get('/', function () {
    return view('welcome');
});

// Ruta de prueba
Route::get('/admin/inicio', function () {
    return view('admin.inicio');
});

Route::get('/admin/consultorios', [ConsultorioController::class, 'index']);

// Fíjate que usamos Route::post en lugar de Route::get porque viene de un formulario
Route::post('/admin/consultorios/guardar', [ConsultorioController::class, 'store']);