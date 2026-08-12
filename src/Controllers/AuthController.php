<?php
namespace App\Controllers;

use App\Models\AuthModel; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    
    // =========================================================================
    // CONTROL DE ACCESO AL DASHBOARD SEGÚN EL ROL DE USUARIO
    // =========================================================================
    public function mostrarDashboard() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Si no hay sesión activa, expulsar inmediatamente al login
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        // Capturar el rol de la sesión (por defecto 3 - Paciente si ocurre algún fallo)
        $rol = isset($_SESSION['usuario_rol']) ? (int)$_SESSION['usuario_rol'] : 3;

        // CÓDIGO CORREGIDO
        if ($rol === 1 || $rol === 4) { // <- AÑADIDO: Ahora el rol 4 también entra aquí
            $dashboardAdmin = new \App\Controllers\administrador\DashboardAdminController();
            $dashboardAdmin->index();
        } else if ($rol === 2) {
            $dashboardOdo = new \App\Controllers\odontologo\DashboardOdonController();
            $dashboardOdo->index();
        } else {
            $dashboardPaciente = new \App\Controllers\paciente\DashboardPacienteController();
            $dashboardPaciente->index();
        }
        exit;
    }
    
    // Muestra el Login con control de caché estricto
    public function mostrarLogin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/dashboard");
            exit;
        }

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    // Procesa las credenciales de acceso bajo la regla PRG
    public function procesarLogin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
            $contrasena = $_POST['contrasena'] ?? '';

            if (empty($correo) || empty($contrasena)) {
                $_SESSION['LOGIN_ERROR'] = "Por favor, llene todos los campos.";
                header("Location: /LOGIN_ORIGINAL/login");
                exit;
            }

            $authModel = new \App\Models\AuthModel();
            $usuario = $authModel->buscarPorCorreo($correo);

            if ($usuario && password_verify($contrasena, $usuario['CONTRASEÑA'])) {
                
                // Bloquea el acceso si el administrador desactivó la cuenta.
                // La contraseña es correcta, pero la cuenta esta desactivada no ingresa
                if ($usuario['ESTADO'] !== 'Activo') {
                    $_SESSION['LOGIN_ERROR'] = "Error: Tu cuenta ha sido desactivada. Contacta al administrador.";
                    header("Location: /LOGIN_ORIGINAL/login");
                    exit;
                }

                $_SESSION['usuario_id']   = $usuario['ID_USUARIOS'];
                $_SESSION['usuario_nome'] = $usuario['NOMBRES'] . ' ' . $usuario['APELLIDOS'];
                $_SESSION['usuario_rol']  = $usuario['ROLES_ID_ROLES'];

                // 4. Usamos $authModel en lugar de $model
                $pacienteData = $authModel->obtenerPacientePorUsuario($usuario['ID_USUARIOS']);
                $_SESSION['paciente_id'] = $pacienteData ? $pacienteData['ID_PACIENTE'] : null;

                $odontologoData = $authModel->obtenerOdontologoPorUsuario($usuario['ID_USUARIOS']);
                $_SESSION['odontologo_id'] = $odontologoData ? $odontologoData['ID_ODONTOLOGO'] : null;

                header("Location: /LOGIN_ORIGINAL/dashboard");
                exit;
            } else {
                $_SESSION['LOGIN_ERROR'] = "Correo electrónico o contraseña incorrectos.";
                header("Location: /LOGIN_ORIGINAL/login");
                exit;
            }
        }
    }

    // Muestra el formulario de registro público
    public function mostrarRegistro() {
        require_once __DIR__ . '/../Views/auth/registro.php';
    }

    // Procesa el registro de nuevos pacientes con validaciones BCRYPT
    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipoDoc   = $_POST['tipo_documento'] ?? '';
            $numDoc    = trim($_POST['numero_documento'] ?? '');
            $nombres   = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $telefono  = trim($_POST['telefono'] ?? '');

            if (!preg_match('/^[0-9]{10}$/', $telefono)) {
                $_ERROR = "El teléfono debe tener exactamente 10 dígitos.";
                require_once __DIR__ . '/../Views/auth/registro.php';
                return;
            }
            
            $correo    = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
            $pass      = $_POST['contrasena'] ?? '';
            $genero    = $_POST['genero'] ?? '';

            if (empty($tipoDoc) || empty($numDoc) || empty($nombres) || empty($apellidos) || empty($correo) || empty($pass) || empty($genero)) {
                $_ERROR = "Todos los campos obligatorios deben ser diligenciados.";
                require_once __DIR__ . '/../Views/auth/registro.php';
                return;
            }

            $model = new AuthModel(); 

            if ($model->buscarPorCorreo($correo)) {
                $_ERROR = "El correo electrónico ya se encuentra registrado.";
                require_once __DIR__ . '/../Views/auth/registro.php';
                return;
            }

            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
                $_ERROR = "La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.";
                require_once __DIR__ . '/../Views/auth/registro.php';
                return;
            }

            $contrasenaEncriptada = password_hash($pass, PASSWORD_BCRYPT);
            $exito = $model->registrar($tipoDoc, $numDoc, $nombres, $apellidos, $telefono, $correo, $contrasenaEncriptada, $genero);

            if ($exito) {
                // Notificar al Administrador
                \App\Helpers\Notificador::enviarAAdmin(13, "El paciente $nombres $apellidos se ha registrado en la plataforma.");
                
                // Notificar al nuevo Paciente
                $usuario = $model->buscarPorCorreo($correo);
                if ($usuario) {
                    \App\Helpers\Notificador::enviarAPaciente($usuario['ID_USUARIOS'], 7, "¡Bienvenido/a $nombres a Sonríe Dental! Ya puedes agendar tus citas desde el portal.");
                }

                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                $_SESSION['ALERTA_EXITO'] = "Usuario registrado exitosamente. Ya puede iniciar sesión.";
                header("Location: /LOGIN_ORIGINAL/login");
                exit;
            } else {
                $_ERROR = "Hubo un error al guardar el usuario en el sistema.";
                require_once __DIR__ . '/../Views/auth/registro.php';
            }
        }
    }

    // Muestra el panel de recuperación de contraseña (Fases OTP)
    public function mostrarRecuperar() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION['recuperar_fase'] = 'solicitar';
        unset($_SESSION['recuperar_correo'], $_SESSION['recuperar_token_valido']);
        require_once __DIR__ . '/../Views/auth/recuperar.php';
    }

    // Procesa el token OTP y el envío real por Gmail mediante SMTP
    public function procesarRecuperar() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $model = new AuthModel(); 
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correo'])) {
            $correo = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);
            if (!$correo) {
                $_ERROR = "Por favor, ingrese un correo válido.";
                require_once __DIR__ . '/../Views/auth/recuperar.php';
                return;
            }

            $usuario = $model->buscarPorCorreo($correo);
            if ($usuario) {
                $codigoOTP = (string)rand(100000, 999999);
                $model->guardarTokenRecuperacion($correo, $codigoOTP);

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '571egr@gmail.com';  
                    $mail->Password   = 'kxddxrqscozpwuxb';  
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    
                    $mail->SMTPOptions = array(
                        'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
                    );
                    
                    $mail->addEmbeddedImage(__DIR__ . '/../../public/img/logo_odontologia.png', 'logo_odontologia');
                    $mail->setFrom('571egr@gmail.com', 'Odonto Estética');
                    $mail->addAddress($correo, $usuario['NOMBRES']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Código de Verificación - Odonto Estética';
                    $mail->Body    =   "<div style='background-color: #E6F2FF; padding: 40px 10px; font-family: sans-serif; text-align: center;'>
                                            <!-- Estructura del correo inyectado de tu plantilla original -->
                                            <div style='background-color: #ffffff; padding: 40px 30px; border-radius: 2rem; max-width: 500px; margin: 0 auto; text-align: left;'>
                                                <p>Hola, <strong>" . htmlspecialchars($usuario['NOMBRES']) . "</strong>.</p>
                                                <p>Tu código de recuperación es:</p>
                                                <h2 style='text-align: center; color: #0F62FE; letter-spacing: 4px;'>$codigoOTP</h2>
                                            </div>
                                        </div>";

                    $mail->send();
                    $_SESSION['recuperar_correo'] = $correo;
                    $_SESSION['recuperar_fase'] = 'verificar';
                } catch (Exception $e) {
                    $_ERROR = "No se pudo despachar el correo. Error: " . $mail->ErrorInfo;
                }
            } else {
                $_SESSION['recuperar_correo'] = $correo;
                $_SESSION['recuperar_fase'] = 'verificar';
            }
            require_once __DIR__ . '/../Views/auth/recuperar.php';
            return;
        }

        if (isset($_POST['codigo'])) {
            $codigo = trim($_POST['codigo']);
            $usuarioToken = $model->verificarToken($codigo);

            if ($usuarioToken && $usuarioToken['CORREO'] === ($_SESSION['recuperar_correo'] ?? '')) {
                $_SESSION['recuperar_token_valido'] = $codigo;
                $_SESSION['recuperar_fase'] = 'cambiar';
            } else {
                $_ERROR = "El código ingresado es incorrecto o expiró.";
            }
            require_once __DIR__ . '/../Views/auth/recuperar.php';
            return;
        }

        if (isset($_POST['contrasena']) && isset($_POST['confirmar_contrasena'])) {
            $pass = $_POST['contrasena'];
            $confirmPass = $_POST['confirmar_contrasena'];
            $tokenGuardado = $_SESSION['recuperar_token_valido'] ?? '';

            if ($pass !== $confirmPass) {
                $_ERROR = "Las contraseñas no coinciden.";
                require_once __DIR__ . '/../Views/auth/recuperar.php';
                return;
            }

            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
                $_ERROR = "La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.";
                require_once __DIR__ . '/../Views/auth/recuperar.php';
                return;
            }

            $contrasenaEncriptada = password_hash($pass, PASSWORD_BCRYPT);
            $model->actualizarContrasenaPorToken($tokenGuardado, $contrasenaEncriptada);

            unset($_SESSION['recuperar_fase'], $_SESSION['recuperar_correo'], $_SESSION['recuperar_token_valido']);
            $_SESSION['ALERTA_EXITO'] = "Contraseña restablecida correctamente.";
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }
    }

    // Destruye limpiamente las cookies y variables de sesión de tu sistema (PACIENTE)
    public function salir() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
        }

        session_destroy();
        header("Location: /LOGIN_ORIGINAL/inicio");
        exit;
    }

    // Destruye limpiamente las cookies y variables de sesión de tu sistema (ADMIN)
    public function salirAdmin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, 
                $params["path"], $params["domain"], 
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header("Location: /LOGIN_ORIGINAL/inicio");
        exit;
    }
}