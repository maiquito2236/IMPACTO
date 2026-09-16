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
        // Excluimos nuestra API de seguridad CSRF para que tu JS viejo funcione
        $middleware->validateCsrfTokens(except: [
            'api_gestion_usuario',
            'api_agenda/*',
            'api_horarios/*',
            'api_consultorios/*'
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    

