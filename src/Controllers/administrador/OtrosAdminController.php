<?php
namespace App\Controllers\administrador;

use App\Models\administrador\OtrosAdminModel;

class OtrosAdminController {

    private $modelo;

    public function __construct() {
        $this->modelo = new OtrosAdminModel();
    }

    public function mostrarVista() {
        // Obtener los procedimientos para la vista
        $procedimientosRaw = $this->modelo->listarProcedimientos();
        
        // Mapear los estilos y demás para la vista
        $procedimientos = [];
        foreach ($procedimientosRaw as $proc) {
            // Mapeo de estado a CSS (usando las clases est-activo y est-inactivo del diseño)
            if ($proc['estado'] === 'ACTIVO') {
                $proc['css_estado'] = 'est-activo';
                $proc['estado'] = 'Activo';
            } else {
                $proc['css_estado'] = 'est-inactivo';
                $proc['estado'] = 'Inactivo';
            }
            
            $procedimientos[] = $proc;
        }

        // Obtener y mapear especialidades
        $especialidadesRaw = $this->modelo->listarEspecialidades();
        $especialidades = [];
        foreach ($especialidadesRaw as $esp) {
            if ($esp['estado'] === 'ACTIVO') {
                $esp['css_estado'] = 'est-activo';
                $esp['estado'] = 'Activo';
            } else {
                $esp['css_estado'] = 'est-inactivo';
                $esp['estado'] = 'Inactivo';
            }
            $especialidades[] = $esp;
        }

        // Obtener y mapear EPS
        $epsRaw = $this->modelo->listarEps();
        $listaEps = [];
        foreach ($epsRaw as $eps) {
            if ($eps['estado'] === 'ACTIVO') {
                $eps['css_estado'] = 'est-activo';
                $eps['estado'] = 'Activo';
            } else {
                $eps['css_estado'] = 'est-inactivo';
                $eps['estado'] = 'Inactivo';
            }
            $listaEps[] = $eps;
        }

        // Obtener y mapear Alergias
        $alergiasRaw = $this->modelo->listarAlergias();
        $alergias = [];
        foreach ($alergiasRaw as $alg) {
            if (strtoupper($alg['estado']) === 'ACTIVA' || strtoupper($alg['estado']) === 'ACTIVO') {
                $alg['css_estado'] = 'est-activo';
                $alg['estado'] = 'Activo';
            } else {
                $alg['css_estado'] = 'est-inactivo';
                $alg['estado'] = 'Inactivo';
            }
            $alergias[] = $alg;
        }

        // Obtener y mapear Enfermedades
        $enfermedadesRaw = $this->modelo->listarEnfermedades();
        $enfermedades = [];
        foreach ($enfermedadesRaw as $enf) {
            if (strtoupper($enf['estado']) === 'ACTIVA' || strtoupper($enf['estado']) === 'ACTIVO') {
                $enf['css_estado'] = 'est-activo';
                $enf['estado'] = 'Activo';
            } else {
                $enf['css_estado'] = 'est-inactivo';
                $enf['estado'] = 'Inactivo';
            }
            $enfermedades[] = $enf;
        }

        // Cargar la vista
        require_once __DIR__ . '/../../Views/administrador/otros.php';
    }

    public function guardarProcedimiento() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['nombre_procedimiento']) || empty($data['costo'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios']);
            return;
        }

        try {
            $id = $this->modelo->guardarProcedimiento(
                $data['nombre_procedimiento'],
                $data['descripcion'] ?? '',
                $data['costo'],
                $data['tiempo_estimado'] ?? 0,
                $data['estado'] ?? 'ACTIVO'
            );
            
            echo json_encode(['status' => 'success', 'id' => $id]);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el procedimiento']);
        }
    }

    public function editarProcedimiento() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_procedimiento']) || empty($data['nombre_procedimiento']) || empty($data['costo'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios']);
            return;
        }

        try {
            $this->modelo->actualizarProcedimiento(
                $data['id_procedimiento'],
                $data['nombre_procedimiento'],
                $data['descripcion'] ?? '',
                $data['costo'],
                $data['tiempo_estimado'] ?? 0,
                $data['estado'] ?? 'ACTIVO'
            );
            
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el procedimiento']);
        }
    }
    public function eliminarProcedimiento() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_procedimiento'])) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID del procedimiento']);
            return;
        }

        try {
            $this->modelo->eliminarProcedimiento($data['id_procedimiento']);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al inactivar el procedimiento']);
        }
    }

    public function guardarEspecialidad() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['nombre_especialidad'])) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la especialidad es obligatorio']);
            return;
        }

        try {
            if (empty($data['id_especialidad'])) {
                // Nuevo
                $this->modelo->guardarEspecialidad($data['nombre_especialidad'], $data['estado'] ?? 'ACTIVO');
            } else {
                // Se preparó el if para futura edición, pero el usuario solo pidió Crear y Deshabilitar por ahora
                // Si llegara con ID, se podría llamar a un actualizarEspecialidad()
            }
            
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la especialidad']);
        }
    }

    public function editarEspecialidad() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_especialidad']) || empty($data['nombre_especialidad'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios para editar']);
            return;
        }

        try {
            $this->modelo->actualizarEspecialidad(
                $data['id_especialidad'],
                $data['nombre_especialidad'],
                $data['estado'] ?? 'ACTIVO'
            );
            
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la especialidad']);
        }
    }

    public function eliminarEspecialidad() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_especialidad'])) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la especialidad']);
            return;
        }

        try {
            $this->modelo->eliminarEspecialidad($data['id_especialidad']);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al inactivar la especialidad']);
        }
    }

    public function guardarEps() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['nombre_eps'])) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la EPS es obligatorio']);
            return;
        }

        try {
            if (empty($data['id_eps'])) {
                $this->modelo->guardarEps($data['nombre_eps'], $data['estado'] ?? 'ACTIVO');
            }
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la EPS']);
        }
    }

    public function editarEps() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_eps']) || empty($data['nombre_eps'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios para editar']);
            return;
        }

        try {
            $this->modelo->actualizarEps(
                $data['id_eps'],
                $data['nombre_eps'],
                $data['estado'] ?? 'ACTIVO'
            );
            
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la EPS']);
        }
    }

    public function eliminarEps() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_eps'])) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la EPS']);
            return;
        }

        try {
            $this->modelo->eliminarEps($data['id_eps']);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al inactivar la EPS']);
        }
    }

    public function guardarAlergia() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['nombre_alergia'])) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la alergia es obligatorio']);
            return;
        }

        try {
            $this->modelo->guardarAlergia(
                $data['nombre_alergia'],
                $data['descripcion'] ?? '',
                $data['estado'] ?? 'ACTIVO'
            );
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la alergia']);
        }
    }

    public function editarAlergia() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_alergia']) || empty($data['nombre_alergia'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios para editar']);
            return;
        }

        try {
            $this->modelo->actualizarAlergia(
                $data['id_alergia'],
                $data['nombre_alergia'],
                $data['descripcion'] ?? '',
                $data['estado'] ?? 'ACTIVO'
            );
            
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la alergia']);
        }
    }

    public function eliminarAlergia() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_alergia'])) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la alergia']);
            return;
        }

        try {
            $this->modelo->eliminarAlergia($data['id_alergia']);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al inactivar la alergia']);
        }
    }

    public function guardarEnfermedad() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['nombre_enfermedad'])) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la enfermedad es obligatorio']);
            return;
        }

        try {
            $this->modelo->guardarEnfermedad(
                $data['nombre_enfermedad'],
                $data['descripcion'] ?? '',
                $data['estado'] ?? 'ACTIVO'
            );
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la enfermedad']);
        }
    }

    public function editarEnfermedad() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_enfermedad']) || empty($data['nombre_enfermedad'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios para editar']);
            return;
        }

        try {
            $this->modelo->actualizarEnfermedad(
                $data['id_enfermedad'],
                $data['nombre_enfermedad'],
                $data['descripcion'] ?? '',
                $data['estado'] ?? 'ACTIVO'
            );
            
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la enfermedad']);
        }
    }

    public function eliminarEnfermedad() {
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['id_enfermedad'])) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la enfermedad']);
            return;
        }

        try {
            $this->modelo->eliminarEnfermedad($data['id_enfermedad']);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al inactivar la enfermedad']);
        }
    }
}