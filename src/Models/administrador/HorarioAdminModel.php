<?php
namespace App\Models\administrador;
use App\Config\Database;
use PDO;

class HorarioAdminModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // NUEVO: Trae todos los odontólogos con su consultorio y piso fijos
    public function obtenerListaOdontologos() {
        $sql = "SELECT 
                    o.ID_ODONTOLOGO, 
                    u.NOMBRES, 
                    u.APELLIDOS, 
                    IFNULL(c.NOMBRE, 'Sin asignar') AS CONSULTORIO_NOMBRE,
                    IFNULL(c.UBICACION, '') AS PISO
                FROM odontologo o
                JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN consultorio c ON o.CONSULTORIO_ID_CONSULTORIO = c.ID_CONSULTORIO
                WHERE u.ESTADO = 'Activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosConOdontologos() {
        $sql = "SELECT
            h.ID_HORARIO,
            h.ODONTOLOGO_ID_ODONTOLOGO,
            TIME(c.FECHA_HORA) AS HORA_INICIO,
            ADDTIME(TIME(c.FECHA_HORA), '01:00:00') AS HORA_FIN,
            h.JORNADA,
            h.FECHA,
            'Ocupado' AS ESTADO,
            u.NOMBRES,
            u.APELLIDOS,
            IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO,
            p.NOMBRES AS NOMBRE_PACIENTE,
            p.APELLIDOS AS APELLIDO_PACIENTE,
            pr.ID_PROCEDIMIENTO,
            pr.NOMBRE_PROCEDIMIENTO,
            h.descanso_inicio,
            h.descanso_fin
        FROM horario_disponibilidad h
        JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
        LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO
        INNER JOIN cita c ON h.ID_HORARIO = c.HORARIO_DISPONIBILIDAD_ID_HORARIO AND c.ESTADO_CITA_ID = 1
        LEFT JOIN paciente pa ON c.PACIENTE_ID_PACIENTE = pa.ID_PACIENTE
        LEFT JOIN usuarios p ON pa.USUARIOS_ID_USUARIOS = p.ID_USUARIOS
        LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
        LEFT JOIN procedimientos pr ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO
        WHERE h.ESTADO = 'Disponible'
        
        UNION ALL
        
        SELECT
            h.ID_HORARIO,
            h.ODONTOLOGO_ID_ODONTOLOGO,
            h.HORA_INICIO,
            h.HORA_FIN,
            h.JORNADA,
            h.FECHA,
            h.ESTADO,
            u.NOMBRES,
            u.APELLIDOS,
            IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO,
            NULL AS NOMBRE_PACIENTE,
            NULL AS APELLIDO_PACIENTE,
            NULL AS ID_PROCEDIMIENTO,
            NULL AS NOMBRE_PROCEDIMIENTO,
            h.descanso_inicio,
            h.descanso_fin
        FROM horario_disponibilidad h
        JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
        LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO
        WHERE h.ESTADO = 'Disponible'";
            
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(int $odontologoId, string $inicio, string $fin, string $jornada, string $fecha, string $estado, ?string $descansoInicio = null, ?string $descansoFin = null): bool {
        $stmt = $this->db->prepare("
            INSERT INTO horario_disponibilidad (ODONTOLOGO_ID_ODONTOLOGO, HORA_INICIO, HORA_FIN, JORNADA, FECHA, ESTADO, descanso_inicio, descanso_fin)
            VALUES (:id, :inicio, :fin, :jornada, :fecha, :estado, :d_inicio, :d_fin)
        ");
        return $stmt->execute([
            'id' => $odontologoId,
            'inicio' => $inicio,
            'fin' => $fin,
            'jornada' => $jornada,
            'fecha' => $fecha,
            'estado' => $estado,
            'd_inicio' => $descansoInicio,
            'd_fin' => $descansoFin
        ]);
    }

    public function actualizar(int $idHorario, int $odontologoId, string $inicio, string $fin, string $jornada, string $fecha, string $estado, ?string $descansoInicio = null, ?string $descansoFin = null): bool {
        $stmt = $this->db->prepare("
            UPDATE horario_disponibilidad
            SET
                ODONTOLOGO_ID_ODONTOLOGO = :odontologo,
                HORA_INICIO = :inicio,
                HORA_FIN = :fin,
                JORNADA = :jornada,
                FECHA = :fecha,
                ESTADO = :estado,
                descanso_inicio = :d_inicio,
                descanso_fin = :d_fin
            WHERE ID_HORARIO = :id
        ");
        return $stmt->execute([
            'id' => $idHorario,
            'odontologo' => $odontologoId,
            'inicio' => $inicio,
            'fin' => $fin,
            'jornada' => $jornada,
            'fecha' => $fecha,
            'estado' => $estado,
            'd_inicio' => $descansoInicio,
            'd_fin' => $descansoFin
        ]);
    }
    
    public function eliminar(int $idHorario): bool {
        $stmt = $this->db->prepare("UPDATE horario_disponibilidad SET ESTADO = 'Inactivo' WHERE ID_HORARIO = :id_horario");
        return $stmt->execute(['id_horario' => $idHorario]);
    }

    public function listar() {
        try {
            $horarios = $this->model->obtenerTodosConOdontologos();
            // Traemos la lista maestra de doctores
            $listaOdontologos = $this->model->obtenerListaOdontologos();
            
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $horarios,
                'listaOdontologos' => $listaOdontologos
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $idHorario = $_POST['id_horario'] ?? null;
            $odontologoId = (int)($_POST['odontologo_id'] ?? 0);
            
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';
            $jornada = $_POST['jornada'] ?? '';
            $fecha = $_POST['fecha'] ?? '';
            $estado = $_POST['estado'] ?? 'Disponible';

            if (!$odontologoId || !$fecha) {
                throw new \Exception("Faltan datos obligatorios.");
            }

            if (!empty($idHorario)) {
                $this->model->actualizar($idHorario, $odontologoId, $horaInicio, $horaFin, $jornada, $fecha, $estado);
            } else {
                $this->model->crear($odontologoId, $horaInicio, $horaFin, $jornada, $fecha, $estado);
            }

            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit; 
    }

    public function obtenerPorFecha(string $fecha) {
        $sql = "SELECT
            h.ID_HORARIO,
            h.ODONTOLOGO_ID_ODONTOLOGO,
            TIME(c.FECHA_HORA) AS HORA_INICIO,
            ADDTIME(TIME(c.FECHA_HORA), '01:00:00') AS HORA_FIN,
            h.JORNADA,
            h.FECHA,
            'Ocupado' AS ESTADO,
            u.NOMBRES,
            u.APELLIDOS,
            IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO,
            p.NOMBRES AS NOMBRE_PACIENTE,
            p.APELLIDOS AS APELLIDO_PACIENTE,
            pr.ID_PROCEDIMIENTO,
            pr.NOMBRE_PROCEDIMIENTO,
            h.descanso_inicio,
            h.descanso_fin
        FROM horario_disponibilidad h
        JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
        LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO
        INNER JOIN cita c ON h.ID_HORARIO = c.HORARIO_DISPONIBILIDAD_ID_HORARIO AND c.ESTADO_CITA_ID = 1
        LEFT JOIN paciente pa ON c.PACIENTE_ID_PACIENTE = pa.ID_PACIENTE
        LEFT JOIN usuarios p ON pa.USUARIOS_ID_USUARIOS = p.ID_USUARIOS
        LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
        LEFT JOIN procedimientos pr ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = pr.ID_PROCEDIMIENTO
        WHERE h.FECHA = :fecha AND h.ESTADO = 'Disponible'
        
        UNION ALL
        
        SELECT
            h.ID_HORARIO,
            h.ODONTOLOGO_ID_ODONTOLOGO,
            h.HORA_INICIO,
            h.HORA_FIN,
            h.JORNADA,
            h.FECHA,
            h.ESTADO,
            u.NOMBRES,
            u.APELLIDOS,
            IFNULL(con.NOMBRE, 'Sin asignar') AS CONSULTORIO,
            NULL AS NOMBRE_PACIENTE,
            NULL AS APELLIDO_PACIENTE,
            NULL AS ID_PROCEDIMIENTO,
            NULL AS NOMBRE_PROCEDIMIENTO,
            h.descanso_inicio,
            h.descanso_fin
        FROM horario_disponibilidad h
        JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
        JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
        LEFT JOIN consultorio con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO
        WHERE h.FECHA = :fecha AND h.ESTADO = 'Disponible'";
            
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarHorarioMasivo($docOdontologo, $fecha, $horaInicio, $horaFin, $jornada) {
        $stmtO = $this->db->prepare("SELECT o.ID_ODONTOLOGO FROM odontologo o INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE u.NUMERO_DOCUMENTO = ?");
        $stmtO->execute([$docOdontologo]);
        $odo = $stmtO->fetch(\PDO::FETCH_ASSOC);
        if (!$odo) throw new \Exception("Odontólogo no encontrado");

        return $this->crear($odo['ID_ODONTOLOGO'], $horaInicio, $horaFin, $jornada, $fecha, 'Disponible');
    }
}