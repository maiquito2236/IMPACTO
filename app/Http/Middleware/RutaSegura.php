<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RutaSegura
{
    /**
     * Maneja la petición entrante verificando autenticación, estado, roles y cabeceras anti-caché.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Verificación de autenticación
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            return redirect()->route('login')->with('LOGIN_ERROR', 'Debes iniciar sesión para acceder a esta sección.');
        }

        $user = Auth::user();

        // 2. Verificación de estado de cuenta
        if ($user->ESTADO !== 'Activo') {
            Auth::logout();
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('LOGIN_ERROR', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        // 3. Verificación de roles autorizados
        if (!empty($roles)) {
            $userRol = (string) $user->ROLES_ID_ROLES;
            $rolesPermitidos = [];
            foreach ($roles as $r) {
                foreach (explode(',', $r) as $sub) {
                    $rolesPermitidos[] = trim($sub);
                }
            }

            if (!in_array($userRol, $rolesPermitidos)) {
                // Redirige al dashboard correspondiente según el rol del usuario
                if ($userRol === '1' || $userRol === '4') {
                    return redirect('/admin/inicio');
                } elseif ($userRol === '2') {
                    return redirect('/odontologo/inicio');
                } else {
                    return redirect('/paciente/dashboard');
                }
            }
        }

        // 4. Continuar la petición
        $response = $next($request);

        // 5. Encabezados estrictos anti-caché (evita que el botón atrás muestre pantallas tras cerrar sesión)
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}