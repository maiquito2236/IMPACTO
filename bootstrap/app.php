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
        // Confiar en proxies inversos (Cloudflare Tunnel) para detectar HTTPS correctamente
        $middleware->trustProxies(at: '*');

        // Excluimos nuestras APIs y rutas de seguridad CSRF para compatibilidad con fetch/ajax JS
        $middleware->alias([
            'ruta_segura' => \App\Http\Middleware\RutaSegura::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            // Mercado Pago (webhook público externo — único endpoint que no puede enviar token CSRF)
            'webhooks/mercadopago',
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();