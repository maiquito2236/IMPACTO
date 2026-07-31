<?php
namespace App\Controllers\administrador;

use App\Models\administrador\GestionUsuarioModel;
use Exception;

/**
 * Controlador de Gestión de Usuarios (panel del Administrador).
 *
 * Punto de entrada único, igual al que ya consume el front-end:
 *   GET  /LOGIN_ORIGINAL/api_gestion_usuario?op=listar
 *   POST /LOGIN_ORIGINAL/api_gestion_usuario   (op=crear|actualizar_rol|eliminar en el body)
 *
 * Y la vista:
 *   GET  /LOGIN_ORIGINAL/admin/gestion_usuario
 */
class GestionUsuarioController {

    private $modelo;

    public function __construct() {
        $this->modelo = new GestionUsuarioModel();
    }

    /**
     * Muestra la vista de gestión de usuarios.
     */
    public function mostrarVista() {
        require_once __DIR__ . '/../../Views/administrador/gestion_usuario.php';
    }

    /**
     * Único endpoint JSON. Despacha según el parámetro "op"
     * (puede venir por GET o por POST, según la operación).
     */
    public function api() {
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);

        $op = $_REQUEST['op'] ?? '';

        try {
            switch ($op) {
                case 'listar':
                    $this->listar();
                    break;
                
                case 'listar_especialidades':
                    $this->listarEspecialidades();
                    break;

                case 'listar_consultorios':
                    $this->listarConsultorios();
                    break;
                
                case 'crear':
                    $this->crear();
                    break;

                case 'actualizar_rol':
                    $this->actualizarRol();
                    break;

                case 'eliminar':
                    $this->eliminar();
                    break;

                default:
                    throw new Exception("Operación no reconocida: '$op'.");
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ── Operaciones internas ─────────────────────────────────────────

    /**
     * op=listar → { ok, datos: [...] }
     */
    private function listar() {
        $datos = $this->modelo->listarTodos();

        // El front pinta una imagen de avatar; si no manejas fotos de perfil
        // aún, usamos un placeholder por usuario.
        foreach ($datos as &$u) {
            $u['img'] = 'https://ui-avatars.com/api/?name=' . urlencode($u['nombre']) . '&background=0D8ABC&color=fff';
        }

        echo json_encode(['ok' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
    }

    /**
     * op=listar_especialidades → { ok, datos: [{ id, nombre }, ...] }
     * Alimenta el <select> de especialidades que se exige al asignar
     * el rol de Odontólogo desde el modal de edición.
     */
    private function listarEspecialidades() {
        $datos = $this->modelo->listarEspecialidades();
        echo json_encode(['ok' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
    }

    /**
     * op=listar_consultorios → { ok, datos: [{ id, nombre }, ...] }
     */
    private function listarConsultorios() {
        $datos = $this->modelo->listarConsultorios();
        echo json_encode(['ok' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
    }

    /**
     * op=crear → body: { nombre, email, rol_id }
     * Respuesta: { ok, usuario: {...}, pass_temp }
     */
    private function crear() {
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rolId    = $_POST['rol_id'] ?? null;
        $especialidadId = $_POST['especialidad_id'] ?? null;
        $consultorioId = $_POST['consultorio'] ?? null;
        
        if ($nombre === '' || $email === '' || $telefono === '' || $rolId === null) {
            throw new Exception("Nombre, correo, teléfono y rol son obligatorios.");
        }

        if (strlen($telefono) !== 10) {
            throw new Exception("El teléfono debe tener exactamente 10 dígitos.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no es válido.");
        }

         if ((int)$rolId === 2) {
            if (empty($especialidadId) || empty($consultorioId)) {
                throw new Exception("Para crear un Odontólogo, es obligatorio seleccionar Especialidad y Consultorio (Por favor recarga la página con Ctrl+F5 si no ves estos campos).");
            }
        }           

        $rolSesion = (int)($_SESSION['usuario_rol'] ?? 1);
        $rolId = (int)$rolId;

        if ($rolId === 1 && $rolSesion !== 4) {
            throw new Exception("No tienes permisos para crear usuarios con el rol de Administrador. Solo el Administrador Jefe puede hacerlo.");
        }
        if ($rolId === 4) {
            throw new Exception("No es posible crear cuentas con el rol de Administrador Jefe.");
        }

        $resultado = $this->modelo->crear($nombre, $email, $telefono, $rolId, $especialidadId, $consultorioId);

        echo json_encode([
            'ok'        => true,
            'usuario'   => [
                'id'         => $resultado['id'],
                'nombre'     => $resultado['nombre'],
                'email'      => $resultado['email'],
                'rol_id'     => $resultado['rol_id'],
                'rol_nombre' => $resultado['rol_nombre'],
                'estado'     => $resultado['estado'],
                'img'        => 'https://ui-avatars.com/api/?name=' . urlencode($resultado['nombre']) . '&background=0D8ABC&color=fff',
            ],
            'pass_temp' => $resultado['pass_temp'],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * op=actualizar_rol → body: { id, rol_id, estado, especialidad_id?, consultorio? }
     *
     * especialidad_id y consultorio son obligatorios SOLO cuando rol_id
     * corresponde a Odontólogo; el modelo valida y lanza Exception si
     * faltan o no son válidos (el front igualmente los exige antes de
     * enviar, pero la validación real y vinculante vive en el modelo).
     */
    private function actualizarRol() {
        $id     = $_POST['id'] ?? null;
        $rolId  = $_POST['rol_id'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $especialidadId = $_POST['especialidad_id'] ?? null;
        $consultorio    = $_POST['consultorio'] ?? null;

        if (!$id || !$rolId || !$estado) {
            throw new Exception("Faltan datos: id, rol_id y estado son requeridos.");
        }

        $rolSesion = (int)($_SESSION['usuario_rol'] ?? 1);
        $rolId = (int)$rolId;

        $usuario = $this->modelo->obtenerRolActual((int)$id);
        if (!$usuario) {
            throw new Exception("El usuario indicado no existe.");
        }

        // Si el usuario destino es Admin (1) y quien edita no es Admin Jefe (4), bloquear
        if ((int)$usuario['ROLES_ID_ROLES'] === 1 && $rolSesion !== 4) {
            throw new Exception("No tienes permisos para modificar a otros administradores. Solo el Administrador Jefe puede hacerlo.");
        }

        // Si el rol destino a asignar es Admin (1) y quien edita no es Admin Jefe (4), bloquear
        if ($rolId === 1 && $rolSesion !== 4) {
            throw new Exception("No tienes permisos para asignar el rol de Administrador. Solo el Administrador Jefe puede hacerlo.");
        }

        $this->modelo->actualizarRol((int) $id, $rolId, $estado, $especialidadId, $consultorio);

        echo json_encode([
            'ok'         => true,
            'mensaje'    => 'Usuario actualizado correctamente.',
            'rol_nombre' => null, // el front ya tiene su propio mapa de nombres de rol
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * op=eliminar → body: { id }
     */
    private function eliminar() {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            throw new Exception("Falta el campo 'id'.");
        }

        $rolSesion = (int)($_SESSION['usuario_rol'] ?? 1);
        $usuario = $this->modelo->obtenerRolActual((int)$id);
        if (!$usuario) {
            throw new Exception("El usuario indicado no existe.");
        }

        // Si el usuario destino es Admin (1) y quien edita no es Admin Jefe (4), bloquear
        if ((int)$usuario['ROLES_ID_ROLES'] === 1 && $rolSesion !== 4) {
            throw new Exception("No tienes permisos para desactivar a otros administradores. Solo el Administrador Jefe puede hacerlo.");
        }

        $this->modelo->eliminar((int) $id);

        echo json_encode(['ok' => true, 'mensaje' => 'Usuario desactivado correctamente.'], JSON_UNESCAPED_UNICODE);
    }

    public function cargaMasiva() {
        if (isset($_GET['descargar_plantilla'])) {
            return $this->descargarPlantillaCsv();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
            header('Content-Type: application/json; charset=utf-8');
            $file = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);
            $successCount = 0; $errorCount = 0; $erroresDetalle = [];
            
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 8) { $errorCount++; continue; }
                $numDoc = trim($row[3]);
                if ($numDoc === 'NUMERO_DOCUMENTO') continue;
                
                $dataUsuario = [
                    'nombres' => trim($row[0]),
                    'apellidos' => trim($row[1]),
                    'tipo_documento' => trim($row[2]),
                    'numero_documento' => $numDoc,
                    'telefono' => trim($row[4]),
                    'correo' => trim($row[5]),
                    'genero' => trim($row[6]),
                    'estado' => 'Activo',
                    'rol' => strtoupper(trim($row[7])) === 'ODONTOLOGO' ? 2 : 3
                ];
                
                try {
                    $this->modelo->registrarUsuarioMasivo($dataUsuario, isset($row[8]) ? trim($row[8]) : null, isset($row[9]) ? trim($row[9]) : null);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Documento $numDoc: " . $e->getMessage();
                }
            }
            fclose($file);
            echo json_encode(['success' => true, 'successCount' => $successCount, 'errorCount' => $errorCount, 'erroresDetalle' => $erroresDetalle]);
            exit;
        }
    }

    public function descargarPlantillaCsv() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_usuarios.csv');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['NOMBRES', 'APELLIDOS', 'TIPO_DOCUMENTO', 'NUMERO_DOCUMENTO', 'TELEFONO', 'CORREO', 'GENERO', 'ROL', 'ESPECIALIDAD_ID_SI_ES_ODONTOLOGO', 'EPS_ID_SI_ES_PACIENTE']);
        fputcsv($output, ['Juan', 'Perez', 'C.C', '123456789', '3001234567', 'juan@example.com', 'MASCULINO', 'PACIENTE', '', '1']);
        fputcsv($output, ['Maria', 'Gomez', 'C.E', '987654321', '3009876543', 'maria@example.com', 'FEMENINO', 'ODONTOLOGO', '1', '']);
        fclose($output);
        exit;
    }
}