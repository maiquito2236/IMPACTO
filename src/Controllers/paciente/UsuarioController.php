<?php
namespace App\Controllers\paciente;

use App\Models\paciente\Usuario;

class UsuarioController {

    // Muestra la pantalla del perfil del paciente con sus datos clínicos
    public function mostrarPerfil() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $model = new Usuario();

        $usuario = $model->obtenerPorId($_SESSION['usuario_id']);
        $paciente = $model->obtenerPacientePorUsuario($_SESSION['usuario_id']);
        $epsLista = $model->obtenerEPS();
        $enfermedadesLista = $model->obtenerEnfermedades();
        $alergiasLista = $model->obtenerAlergias();
        $condicionesPaciente = $model->obtenerCondicionesPaciente($_SESSION['usuario_id']);

        require_once __DIR__ . '/../../Views/paciente/perfil.php';
    }

    // Procesa los cambios del perfil del paciente y guarda antecedentes médicos
    public function procesarActualizarPerfil() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_SESSION['usuario_id'];

            $datos = [
                'nombres' => $_POST['nombres'] ?? '',
                'apellidos' => $_POST['apellidos'] ?? '',
                'tipo_documento' => $_POST['tipo_documento'] ?? '',
                'numero_documento' => $_POST['numero_documento'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'genero' => $_POST['genero'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'correo' => $_POST['correo'] ?? '',
                'eps' => $_POST['eps'] ?? '',
                'rh' => $_POST['rh'] ?? '',
                'enfermedad' => $_POST['enfermedad'] ?? '',
                'alergia' => $_POST['alergia'] ?? '',
                'nombre_contacto_emergencia' => $_POST['nombre_contacto_emergencia'] ?? '',
                'numero_contacto_emergencia' => $_POST['numero_contacto_emergencia'] ?? ''
            ];

            if (empty($datos['eps'])) {
                $_SESSION['ERROR_PERFIL'] = "Debe seleccionar una EPS.";
                header("Location: /LOGIN_ORIGINAL/perfil?mode=edit");
                exit;
            }

            if (!preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                $_SESSION['ERROR_PERFIL'] = "El teléfono debe tener exactamente 10 dígitos.";
                header("Location: /LOGIN_ORIGINAL/perfil?mode=edit");
                exit;
            }

            if (!empty($datos['numero_contacto_emergencia']) && !preg_match('/^[0-9]{10}$/', $datos['numero_contacto_emergencia'])) {
                $_SESSION['ERROR_PERFIL'] = "El número de contacto de emergencia debe tener exactamente 10 dígitos.";
                header("Location: /LOGIN_ORIGINAL/perfil?mode=edit");
                exit;
            }

            $model = new Usuario();

            if (!empty($_POST['password'])) {
                $nuevaPass = $_POST['password'];

                if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPass)) {
                    $_SESSION['ERROR_PERFIL'] = "La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.";
                    header("Location: /LOGIN_ORIGINAL/perfil?mode=edit");
                    exit;
                }

                $nuevaPassHash = password_hash($nuevaPass, PASSWORD_BCRYPT);
                $model->actualizarContrasena($id, $nuevaPassHash);
            }

            $model->actualizarDatosPerfil($id, $datos);
            $_SESSION['usuario_nome'] = $datos['nombres'] . ' ' . $datos['apellidos'];
            
            $model->actualizarPaciente($id, $datos);
            $model->guardarCondicionesPaciente($id, $datos['enfermedad'], $datos['alergia']);

            // Generar notificación de actualización de datos para el paciente
            \App\Helpers\Notificador::enviarAUsuario($id, 16, "Tus datos personales fueron actualizados con éxito.");

            // Notificar al Administrador que este paciente actualizó sus datos
            $mensajeAdmin = "El paciente " . $datos['nombres'] . " " . $datos['apellidos'] . " ha actualizado sus datos personales.";
            \App\Helpers\Notificador::enviarAAdmin(18, $mensajeAdmin);

            $_SESSION['EXITO_PERFIL'] = "Datos actualizados correctamente.";
            header("Location: /LOGIN_ORIGINAL/perfil");
            exit;
        }
    }

    // =====================================================
    // 📄 PUENTE MAESTRO DE EXPORTACIÓN (EXCEL / PDF / IMPRIMIR)
    // =====================================================
    public function exportarPerfil() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario_id'])) {
            die("Acceso denegado.");
        }

        // Dejamos que la plantilla visual resuelva el formato solicitado (Excel, PDF o Imprimir)
        // Redirigiendo de forma limpia a tu archivo maestro de exportaciones
        require_once __DIR__ . '/../../Views/paciente/exports/perfil_exportar.php';
    }
}