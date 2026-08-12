<?php
namespace App\Controllers\odontologo;

use App\Models\odontologo\PerfilOdonModel;
use Exception;

class PerfilOdonController {
    private $modelo;

    public function __construct() {
        $this->modelo = new PerfilOdonModel();
    }

    public function mostrarPerfil() {
        $idUsuario = $_SESSION['usuario_id'];
        $perfil = $this->modelo->obtenerPerfil($idUsuario);
        
        // Obtenemos las especialidades para el menú desplegable
        $especialidades = $this->modelo->obtenerEspecialidades();
        
        require_once __DIR__ . '/../../Views/odontologo/perfil_odon.php';
    }

    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idUsuario = $_SESSION['usuario_id'];
                
                // Recolectamos exactamente los datos de la nueva vista (Sin RH)
                $datos = [
                    'nombres'          => $_POST['nombres'] ?? '',
                    'apellidos'        => $_POST['apellidos'] ?? '',
                    'tipo_documento'   => $_POST['tipo_documento'] ?? '',
                    'numero_documento' => $_POST['numero_documento'] ?? '',
                    'genero'           => $_POST['genero'] ?? '',
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
                    'direccion'        => $_POST['direccion'] ?? '',
                    'telefono'         => $_POST['telefono'] ?? '',
                    'correo'           => $_POST['correo'] ?? '',
                    'especialidad_id'  => $_POST['especialidad_id'] ?? ''
                ];

                // Solo capturamos la contraseña nueva (como en el Admin)
                $passNueva  = $_POST['pass_nueva'] ?? '';
                $passActual = ''; // Lo mandamos vacío para no romper la firma del modelo

                $this->modelo->actualizarPerfil($idUsuario, $datos, $passActual, $passNueva);
                
                // Actualizamos el nombre en la sesión para que se refleje inmediatamente
                $_SESSION['usuario_nome'] = $datos['nombres'] . ' ' . $datos['apellidos'];

                // 🔔 NOTIFICACION DE ACTUALIZACION DE DATOS ODONTOLOGO
                \App\Helpers\Notificador::enviarAUsuario($idUsuario, 17, "Tus datos personales fueron actualizados con éxito.");
                $mensajeAdmin = "El odontólogo " . $datos['nombres'] . " " . $datos['apellidos'] . " ha actualizado sus datos personales.";
                \App\Helpers\Notificador::enviarAAdmin(18, $mensajeAdmin);

                header("Location: /LOGIN_ORIGINAL/odontologo/perfil?success=1");
                exit;
                
            } catch (Exception $e) {
                header("Location: /LOGIN_ORIGINAL/odontologo/perfil?error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }
}