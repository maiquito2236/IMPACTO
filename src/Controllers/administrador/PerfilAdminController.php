<?php
namespace App\Controllers\administrador;

use App\Models\administrador\PerfilAdminModel;

class PerfilAdminController {

    // Muestra la pantalla del perfil del administrador
    public function mostrarPerfil() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        $model = new PerfilAdminModel();
        $usuario = $model->obtenerPorId($_SESSION['usuario_id']);

        // IMPORTANTE: Asegúrate de que el nombre del archivo coincida con cómo lo guardaste
        require_once __DIR__ . '/../../Views/administrador/perfilAdmin.php';
    }

    // Procesa los cambios del perfil y aplica validaciones
    public function procesarActualizarPerfil() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_SESSION['usuario_id'];

            $datos = [
                'nombres'          => $_POST['nombres'] ?? '',
                'apellidos'        => $_POST['apellidos'] ?? '',
                'tipo_documento'   => $_POST['tipo_documento'] ?? '',
                'numero_documento' => $_POST['numero_documento'] ?? '',
                'telefono'         => $_POST['telefono'] ?? '',
                'correo'           => $_POST['correo'] ?? '',
                'genero'           => $_POST['genero'] ?? '',
                'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                'direccion'        => $_POST['direccion'] ?? '',
                'rh'               => $_POST['rh'] ?? ''
            ];

            // 🛡️ VALIDACIÓN 1: El teléfono debe tener exactamente 10 dígitos
            if (!preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                $_SESSION['ERROR_PERFIL'] = "El teléfono debe tener exactamente 10 dígitos.";
                header("Location: /LOGIN_ORIGINAL/admin/perfil?mode=edit");
                exit;
            }

            $model = new PerfilAdminModel();

            // 🛡️ VALIDACIÓN 2: Reglas de seguridad para la nueva contraseña
            if (!empty($_POST['password'])) {
                $nuevaPass = $_POST['password'];

                // Mínimo 8 caracteres, al menos una mayúscula, un número y un carácter especial
                if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPass)) {
                    $_SESSION['ERROR_PERFIL'] = "La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.";
                    header("Location: /LOGIN_ORIGINAL/admin/perfil?mode=edit");
                    exit;
                }

                $nuevaPassHash = password_hash($nuevaPass, PASSWORD_BCRYPT);
                $model->actualizarContrasena($id, $nuevaPassHash);
            }

            // Actualizamos los datos básicos si pasó todas las validaciones
            $model->actualizarDatosPerfil($id, $datos);
            
            // Actualizamos el nombre en la sesión para que se refleje inmediatamente en el menú lateral
            $_SESSION['usuario_nome'] = $datos['nombres'] . ' ' . $datos['apellidos'];

            // 🔔 NOTIFICACION DE ACTUALIZACION DE DATOS ADMIN
            \App\Helpers\Notificador::enviarAUsuario($id, 18, "Tus datos personales fueron actualizados con éxito.");

            $_SESSION['EXITO_PERFIL'] = "Datos actualizados correctamente.";
            header("Location: /LOGIN_ORIGINAL/admin/perfil");
            exit;
        }
    }
}