<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Excluimos nuestras APIs y rutas de seguridad CSRF para compatibilidad con fetch/ajax JS
        $middleware->alias([
            'ruta_segura' => \App\Http\Middleware\RutaSegura::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            // Chatbot Landing
            'chatbot/*',
            'chatbot/mensaje',

            // Admin
            'api_gestion_usuario',
            'api_agenda/*',
            'api_horarios/*',
            'api_consultorios/*',
            'api_facturacion/*',
            'api_facturacion',
            'api_reportes',
            'api_reportes/*',
            'api_otros/*',
            'api_otros',
            'admin/otros/*',
            'admin/otros',
            'api_otros',
            'api_notificaciones/*',
            'api_notificaciones',
            'admin/facturacion/cargar-csv',
            'admin/perfil/actualizar',

            // Odontólogo
            'odontologo/*',
            'odontologo',
            'Controllers/*',

            // Paciente APIs y AJAX
            'paciente/*',
            'paciente/cita/*',
            'paciente/pagos/*',
            'paciente/notificaciones/*',
            'guardar_cita',
            'cancelar_cita',
            'reprogramar_cita',
            'login',
            'registro',
            'recuperar',
            'procesarLogin',
            'procesarRegistro',
            'procesarRecuperar',
            'procesarActualizarPerfil',
            'contacto',
            'LOGIN_ORIGINAL/*',
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();