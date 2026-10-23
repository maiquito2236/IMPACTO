<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Paciente;
use App\Helpers\Notificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Muestra la vista de Login
     */
    public function mostrarLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Procesa el inicio de sesión
     */
    public function procesarLogin(Request $request)
    {
        $correo = trim($request->input('correo'));
        $contrasena = $request->input('contrasena');

        if (empty($correo) || empty($contrasena)) {
            return redirect()->route('login')->with('LOGIN_ERROR', 'Por favor, llene todos los campos.');
        }

        $usuario = User::where('CORREO', $correo)->first();

        if (!$usuario) {
            return redirect()->route('login')->with('LOGIN_ERROR', 'Correo electrónico o contraseña incorrectos.');
        }

        // Validación de contraseña (Bcrypt o fallback de texto plano para migraciones)
        $passwordValida = false;
        if (password_verify($contrasena, $usuario->CONTRASEÑA)) {
            $passwordValida = true;
        } elseif ($contrasena === $usuario->CONTRASEÑA) {
            // Re-hashear automáticamente si estaba en texto plano
            $usuario->CONTRASEÑA = Hash::make($contrasena);
            $usuario->save();
            $passwordValida = true;
        }

        if (!$passwordValida) {
            return redirect()->route('login')->with('LOGIN_ERROR', 'Correo electrónico o contraseña incorrectos.');
        }

        if ($usuario->ESTADO !== 'Activo') {
            return redirect()->route('login')->with('LOGIN_ERROR', 'Error: Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        // Iniciar sesión con Laravel Auth
        Auth::login($usuario);

        // Variables de sesión legadas para compatibilidad con código existente
        $meses_es = [
            1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];
        $dia_num = date('j');
        $mes_num = (int)date('n');
        $anio_num = date('Y');

        $pacienteData = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $usuario->ID_USUARIOS)->first();
        $odontologoData = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $usuario->ID_USUARIOS)->first();

        session([
            'usuario_id'         => $usuario->ID_USUARIOS,
            'usuario_nome'       => $usuario->NOMBRES . ' ' . $usuario->APELLIDOS,
            'usuario_rol'        => (int) $usuario->ROLES_ID_ROLES,
            'paciente_id'        => $pacienteData ? $pacienteData->ID_PACIENTE : null,
            'odontologo_id'      => $odontologoData ? $odontologoData->ID_ODONTOLOGO : null,
            'fecha_completa_es'  => "$dia_num de {$meses_es[$mes_num]} de $anio_num",
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Redirige al dashboard según el rol del usuario autenticado
     */
    public function mostrarDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $rol = (int) (Auth::user()->ROLES_ID_ROLES ?? session('usuario_rol', 3));

        if ($rol === 1 || $rol === 4) {
            return redirect('/admin/inicio');
        } elseif ($rol === 2) {
            return redirect('/odontologo/inicio');
        } else {
            return redirect('/paciente/dashboard');
        }
    }

    /**
     * Muestra la vista de registro
     */
    public function mostrarRegistro()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.registro');
    }

    /**
     * Procesa el registro de nuevo paciente
     */
    public function procesarRegistro(Request $request)
    {
        $tipoDoc   = $request->input('tipo_documento');
        $numDoc    = trim($request->input('numero_documento'));
        $nombres   = trim($request->input('nombres'));
        $apellidos = trim($request->input('apellidos'));
        $telefono  = trim($request->input('telefono'));
        $correo    = trim($request->input('correo'));
        $pass      = $request->input('contrasena');
        $genero    = $request->input('genero');

        if (empty($tipoDoc) || empty($numDoc) || empty($nombres) || empty($apellidos) || empty($correo) || empty($pass) || empty($genero)) {
            return back()->withInput()->with('ERROR', 'Todos los campos obligatorios deben ser diligenciados.');
        }

        if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
            return back()->withInput()->with('ERROR', 'El teléfono debe tener exactamente 10 dígitos.');
        }

        if (User::where('CORREO', $correo)->exists()) {
            return back()->withInput()->with('ERROR', 'El correo electrónico ya se encuentra registrado.');
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
            return back()->withInput()->with('ERROR', 'La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.');
        }

        try {
            DB::beginTransaction();

            $idUsuario = DB::table('usuarios')->insertGetId([
                'ROLES_ID_ROLES'   => 3,
                'NOMBRES'          => $nombres,
                'APELLIDOS'        => $apellidos,
                'TIPO_DOCUMENTO'   => $tipoDoc,
                'NUMERO_DOCUMENTO' => $numDoc,
                'TELEFONO'         => $telefono,
                'CORREO'           => $correo,
                'GENERO'           => $genero,
                'CONTRASEÑA'       => Hash::make($pass),
                'ESTADO'           => 'Activo'
            ]);

            // Asignar EPS 1 por defecto para cumplir la regla NOT NULL de la BD
            DB::table('paciente')->insert([
                'USUARIOS_ID_USUARIOS'         => $idUsuario,
                'EPS_ID_EPS'                   => 1,
                'NOMBRE_CONTACTO_EMERGENCIA'   => '',
                'NUMERO_CONTACTO_EMERGENCIA'   => ''
            ]);

            DB::commit();

            // Notificar al Administrador
            Notificador::enviarAAdmin(18, "El paciente $nombres $apellidos se ha registrado en la plataforma.");

            // Notificar al Paciente
            Notificador::enviarAUsuario($idUsuario, 16, "¡Bienvenido/a $nombres a Sonríe Dental! Ya puedes agendar tus citas desde el portal.");

            return redirect()->route('login')->with('ALERTA_EXITO', 'Usuario registrado exitosamente. Ya puede iniciar sesión.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en procesarRegistro: " . $e->getMessage());
            return back()->withInput()->with('ERROR', 'Hubo un error al guardar el usuario en el sistema.');
        }
    }

    /**
     * Muestra la vista de recuperar contraseña
     */
    public function mostrarRecuperar(Request $request)
    {
        $token = $request->query('token');
        if ($token) {
            $usuario = DB::table('usuarios')
                ->where('TOKEN_RECUPERACION', $token)
                ->where('TOKEN_EXPIRACION', '>=', now())
                ->first();

            if ($usuario) {
                session([
                    'recuperar_token_valido' => $token,
                    'recuperar_correo'       => $usuario->CORREO,
                    'recuperar_fase'         => 'cambiar'
                ]);
                return view('auth.recuperar')->with('SUCCESS', 'Código de recuperación validado correctamente. Ingresa tu nueva contraseña.');
            } else {
                session(['recuperar_fase' => 'solicitar']);
                return view('auth.recuperar')->with('ERROR', 'El enlace o token de recuperación es inválido o ha expirado.');
            }
        }

        session(['recuperar_fase' => 'solicitar']);
        session()->forget(['recuperar_correo', 'recuperar_token_valido']);
        return view('auth.recuperar');
    }

    /**
     * Procesa la recuperación de contraseña (Fases: Solicitar, Verificar OTP, Cambiar)
     */
    public function procesarRecuperar(Request $request)
    {
        // FASE 1: Solicitar Código OTP
        if ($request->has('correo') && !$request->has('codigo') && !$request->has('contrasena')) {
            $correo = trim($request->input('correo'));
            $usuario = User::where('CORREO', $correo)->first();

            if ($usuario) {
                $codigoOTP = (string) rand(100000, 999999);
                DB::table('usuarios')->where('CORREO', $correo)->update([
                    'TOKEN_RECUPERACION' => $codigoOTP,
                    'TOKEN_EXPIRACION'   => now()->addMinutes(15)
                ]);

                // Almacenar correo en sesión y pasar a fase verificar
                session([
                    'recuperar_correo' => $correo,
                    'recuperar_fase'   => 'verificar'
                ]);

                return view('auth.recuperar')->with('SUCCESS', "Hemos generado tu código de verificación. (Código: $codigoOTP)");
            } else {
                session([
                    'recuperar_correo' => $correo,
                    'recuperar_fase'   => 'verificar'
                ]);
                return view('auth.recuperar');
            }
        }

        // FASE 2: Verificar Código OTP
        if ($request->has('codigo')) {
            $codigo = trim($request->input('codigo'));
            $correo = session('recuperar_correo');

            $usuario = DB::table('usuarios')
                ->where('TOKEN_RECUPERACION', $codigo)
                ->where('TOKEN_EXPIRACION', '>=', now())
                ->first();

            if ($usuario && $usuario->CORREO === $correo) {
                session([
                    'recuperar_token_valido' => $codigo,
                    'recuperar_fase'         => 'cambiar'
                ]);
            } else {
                session(['recuperar_fase' => 'verificar']);
                return view('auth.recuperar')->with('ERROR', 'El código ingresado es incorrecto o ha expirado.');
            }

            return view('auth.recuperar');
        }

        // FASE 3: Cambiar Contraseña
        if ($request->has('contrasena') && $request->has('confirmar_contrasena')) {
            $pass = $request->input('contrasena');
            $confirmPass = $request->input('confirmar_contrasena');
            $tokenGuardado = session('recuperar_token_valido');

            if ($pass !== $confirmPass) {
                session(['recuperar_fase' => 'cambiar']);
                return view('auth.recuperar')->with('ERROR', 'Las contraseñas no coinciden.');
            }

            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
                session(['recuperar_fase' => 'cambiar']);
                return view('auth.recuperar')->with('ERROR', 'La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.');
            }

            DB::table('usuarios')->where('TOKEN_RECUPERACION', $tokenGuardado)->update([
                'CONTRASEÑA'         => Hash::make($pass),
                'TOKEN_RECUPERACION' => null,
                'TOKEN_EXPIRACION'   => null
            ]);

            session()->forget(['recuperar_fase', 'recuperar_correo', 'recuperar_token_valido']);
            return redirect()->route('login')->with('ALERTA_EXITO', 'Contraseña restablecida correctamente.');
        }

        return redirect()->route('recuperar');
    }

    /**
     * Cierra la sesión
     */
    public function salir(Request $request)
    {
        // 1. Cerrar sesión de Laravel Auth
        Auth::logout();

        // 2. Destruir y vaciar la sesión de Laravel
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Destruir sesión nativa de PHP si estuviera activa
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_unset();
            session_destroy();
        }

        // 4. Redirigir a la landing page limpiando cookies de sesión y enviando cabeceras anti-caché
        return redirect('/')
            ->withCookie(Cookie::forget(config('session.cookie')))
            ->withCookie(Cookie::forget('XSRF-TOKEN'))
            ->withCookie(Cookie::forget('sigco-esteban-alone-session'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}