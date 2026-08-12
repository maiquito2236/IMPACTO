<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;

class AgendaAdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista todas las citas con filtro opcional de rango de fechas.
     * Retorna id_cita, fecha_cita, hora_cita, estado, motivo/tratamiento,
     * paciente y odontólogo — igual que el modelo del odontólogo pero
     * sin filtrar por rol, para que el admin vea TODAS las citas.
     */
    public function listarCitas($inicio = null, $fin = null) {
        $where  = '';
        $params = [];

        if ($inicio && $fin) {
            $where  = "WHERE DATE(c.FECHA_HORA) BETWEEN ? AND ?";
            $params = [$inicio, $fin];
        }

        $sql = "SELECT
                    c.ID_CITA                                       AS id_cita,
                    DATE(c.FECHA_HORA)                              AS fecha_cita,
                    TIME_FORMAT(c.FECHA_HORA, '%H:%i')              AS hora_cita,
                    HOUR(c.FECHA_HORA)                              AS hora_num,
                    ec.NOMBRE_ESTADO                                AS estado,
                    c.MOTIVO                                        AS tratamiento,
                    CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS)    AS paciente,
                    CONCAT(u_odo.NOMBRES, ' ', u_odo.APELLIDOS)    AS odontologo,
                    o.ID_ODONTOLOGO                                 AS id_odontologo,
                    p.ID_PACIENTE                                   AS id_paciente,
                    IFNULL(con.NOMBRE, 'Sin asignar')               AS consultorio
                FROM cita c
                INNER JOIN estado_cita  ec  ON c.ESTADO_CITA_ID             = ec.ID_ESTADO
                INNER JOIN paciente     p   ON c.PACIENTE_ID_PACIENTE        = p.ID_PACIENTE
                INNER JOIN usuarios     u_pac ON p.USUARIOS_ID_USUARIOS      = u_pac.ID_USUARIOS
                INNER JOIN odontologo   o   ON c.ODONTOLOGO_ID_ODONTOLOGO   = o.ID_ODONTOLOGO
                INNER JOIN usuarios     u_odo ON o.USUARIOS_ID_USUARIOS      = u_odo.ID_USUARIOS
                LEFT JOIN consultorio   con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO
                $where
                ORDER BY c.FECHA_HORA ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista de citas del día de hoy.
     */
    public function listarCitasHoy() {
        $sql = "SELECT
                    c.ID_CITA                                       AS id_cita,
                    u_pac.NUMERO_DOCUMENTO                          AS documento,
                    DATE(c.FECHA_HORA)                              AS fecha_cita,
                    TIME_FORMAT(c.FECHA_HORA, '%H:%i')              AS hora_cita,
                    ec.NOMBRE_ESTADO                                AS estado,
                    c.MOTIVO                                        AS tratamiento,
                    CONCAT(u_pac.NOMBRES, ' ', u_pac.APELLIDOS)    AS paciente,
                    CONCAT(u_odo.NOMBRES, ' ', u_odo.APELLIDOS)    AS odontologo,
                    IFNULL(con.NOMBRE, 'Sin asignar')               AS consultorio
                FROM cita c
                INNER JOIN estado_cita  ec  ON c.ESTADO_CITA_ID             = ec.ID_ESTADO
                INNER JOIN paciente     p   ON c.PACIENTE_ID_PACIENTE        = p.ID_PACIENTE
                INNER JOIN usuarios     u_pac ON p.USUARIOS_ID_USUARIOS      = u_pac.ID_USUARIOS
                INNER JOIN odontologo   o   ON c.ODONTOLOGO_ID_ODONTOLOGO   = o.ID_ODONTOLOGO
                INNER JOIN usuarios     u_odo ON o.USUARIOS_ID_USUARIOS      = u_odo.ID_USUARIOS
                LEFT JOIN consultorio   con ON o.CONSULTORIO_ID_CONSULTORIO = con.ID_CONSULTORIO
                WHERE DATE(c.FECHA_HORA) = CURDATE()
                ORDER BY c.FECHA_HORA ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todos los pacientes activos para llenar el <select> del modal.
     * Filtra explícitamente por ROLES_ID_ROLES = 3 (PACIENTE) para evitar
     * que administradores u odontólogos aparezcan aquí por error de datos.
     */
    public function listarPacientes() {
        $sql = "SELECT
                    p.ID_PACIENTE                                   AS id,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS)            AS nombre
                FROM paciente p
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE u.ESTADO = 'Activo'
                  AND u.ROLES_ID_ROLES = 3
                ORDER BY u.APELLIDOS, u.NOMBRES";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todos los odontólogos activos para llenar el <select> del modal.
     * Incluye la especialidad en el nombre mostrado para diferenciar
     * registros del mismo odontólogo con distintas especialidades.
     */
    public function listarOdontologos() {
        $sql = "SELECT
                    o.ID_ODONTOLOGO                                            AS id,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS,
                           ' — ', e.NOMBRE_ESPECIALIDAD)                       AS nombre
                FROM odontologo o
                INNER JOIN usuarios u      ON o.USUARIOS_ID_USUARIOS          = u.ID_USUARIOS
                INNER JOIN especialidad e  ON o.ESPECIALIDAD_ID_ESPECIALIDAD  = e.ID_ESPECIALIDAD
                WHERE u.ESTADO = 'Activo'
                  AND u.ROLES_ID_ROLES = 2
                ORDER BY u.APELLIDOS, u.NOMBRES, e.NOMBRE_ESPECIALIDAD";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todos los procedimientos/tratamientos activos para llenar
     * el <select> de "Tratamiento" del modal de nueva cita.
     */
    public function listarTratamientos() {
        $sql = "SELECT
                    ID_PROCEDIMIENTO       AS id,
                    NOMBRE_PROCEDIMIENTO   AS nombre,
                    COSTO                  AS costo,
                    TIEMPO_ESTIMADO        AS duracion
                FROM procedimientos
                WHERE ESTADO = 'ACTIVO'
                ORDER BY NOMBRE_PROCEDIMIENTO";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve las horas ya ocupadas para un odontólogo en una fecha concreta
     * (excluye citas Canceladas). Usado por el modal para bloquear horas.
     */
    public function obtenerHorasOcupadas($fecha, $id_odontologo = null) {
        $horasOcupadasCitas = [];
        
        if ($id_odontologo) {
            $sql = "SELECT TIME_FORMAT(c.FECHA_HORA, '%H:%i') AS hora
                    FROM cita c
                    INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                    WHERE DATE(c.FECHA_HORA) = ?
                      AND c.ODONTOLOGO_ID_ODONTOLOGO = ?
                      AND ec.NOMBRE_ESTADO != 'Cancelada'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha, $id_odontologo]);
            $horasOcupadasCitas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            // Fetch dentist schedule
            $sqlSch = "SELECT HORA_INICIO, HORA_FIN FROM horario_disponibilidad WHERE ODONTOLOGO_ID_ODONTOLOGO = ? AND FECHA = ? AND ESTADO = 'Disponible'";
            $stmtSch = $this->db->prepare($sqlSch);
            $stmtSch->execute([$id_odontologo, $fecha]);
            $schedule = $stmtSch->fetchAll(PDO::FETCH_ASSOC);

            if (empty($schedule)) {
                // If no schedule, all hours are unavailable
                $all = [];
                for ($i = 5; $i <= 20; $i++) {
                    $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $all[] = $h . ':00';
                    if ($i < 20) $all[] = $h . ':30';
                }
                return $all;
            }

            // Allowed hours
            $allowedHours = [];
            foreach ($schedule as $s) {
                $start = (int)date('H', strtotime($s['HORA_INICIO']));
                $end = (int)date('H', strtotime($s['HORA_FIN']));
                for ($i = $start; $i < $end; $i++) {
                    $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $allowedHours[] = $h . ':00';
                    $allowedHours[] = $h . ':30';
                }
            }

            // All possible UI hours
            $todasLasHoras = [];
            for ($i = 5; $i <= 20; $i++) {
                $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                $todasLasHoras[] = $h . ':00';
                if ($i < 20) $todasLasHoras[] = $h . ':30';
            }
            $disabledHours = array_diff($todasLasHoras, $allowedHours);
            
            // Merge disabled (outside schedule) with occupied by appointments
            $finalOcupadas = array_merge($disabledHours, $horasOcupadasCitas);
            return array_values(array_unique($finalOcupadas));

        } else {
            $sql = "SELECT TIME_FORMAT(c.FECHA_HORA, '%H:%i') AS hora
                    FROM cita c
                    INNER JOIN estado_cita ec ON c.ESTADO_CITA_ID = ec.ID_ESTADO
                    WHERE DATE(c.FECHA_HORA) = ?
                      AND ec.NOMBRE_ESTADO != 'Cancelada'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }
    }

    /**
     * Guarda una nueva cita agendada desde el panel de administrador.
     * Busca o crea el horario de disponibilidad y luego inserta la cita.
     */
    public function guardarCita($paciente_id, $odontologo_id, $fecha, $hora, $tratamiento) {
        $this->db->beginTransaction();
        try {
            $fecha_hora = $fecha . ' ' . $hora . ':00';

            // 1. Buscar horario disponible que coincida
            $sqlHorario = "SELECT ID_HORARIO FROM horario_disponibilidad
                           WHERE ODONTOLOGO_ID_ODONTOLOGO = ?
                             AND FECHA = ?
                             AND HORA_INICIO = ?
                             AND ESTADO = 'Disponible'
                           LIMIT 1";
            $stmtH = $this->db->prepare($sqlHorario);
            $stmtH->execute([$odontologo_id, $fecha, $hora . ':00']);
            $horario = $stmtH->fetch(PDO::FETCH_ASSOC);

            if ($horario) {
                $id_horario = $horario['ID_HORARIO'];
                // Marcar horario como ocupado
                $sqlUpH = "UPDATE horario_disponibilidad SET ESTADO = 'Ocupado' WHERE ID_HORARIO = ?";
                $this->db->prepare($sqlUpH)->execute([$id_horario]);
            } else {
                // Crear horario automáticamente si no existe
                $hora_fin = date('H:i:s', strtotime($hora . ':00') + 3600); // +1 hora
                $sqlInsH = "INSERT INTO horario_disponibilidad
                                (ODONTOLOGO_ID_ODONTOLOGO, FECHA, HORA_INICIO, HORA_FIN, ESTADO)
                            VALUES (?, ?, ?, ?, 'Ocupado')";
                $stmtInsH = $this->db->prepare($sqlInsH);
                $stmtInsH->execute([$odontologo_id, $fecha, $hora . ':00', $hora_fin]);
                $id_horario = $this->db->lastInsertId();
            }

            // 2. Insertar la cita (estado 1 = Pendiente)
            $sqlCita = "INSERT INTO cita
                            (PACIENTE_ID_PACIENTE, ODONTOLOGO_ID_ODONTOLOGO, FECHA_HORA,
                             MOTIVO, HORARIO_DISPONIBILIDAD_ID_HORARIO, ESTADO_CITA_ID)
                        VALUES (?, ?, ?, ?, ?, 1)";
            $stmtC = $this->db->prepare($sqlCita);
            $stmtC->execute([$paciente_id, $odontologo_id, $fecha_hora, $tratamiento, $id_horario]);
            $id_cita = $this->db->lastInsertId();

            // 3. Insertar notificación al paciente
            $sqlNot = "INSERT INTO notificaciones
                           (MENSAJE, FECHA_ENVIO, ESTADO, TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION, USUARIOS_ID_USUARIOS)
                       VALUES (?, NOW(), 'NO_LEIDA', 2,
                           (SELECT USUARIOS_ID_USUARIOS FROM paciente WHERE ID_PACIENTE = ?))";
            $fecha_fmt = date('d/m/Y', strtotime($fecha)) . ' a las ' . date('h:i A', strtotime($hora . ':00'));
            $mensaje   = "El administrador agendó una cita para el $fecha_fmt. Motivo: $tratamiento";
            $this->db->prepare($sqlNot)->execute([$mensaje, $paciente_id]);

            $this->db->commit();
            return $id_cita;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Cancela una cita existente y libera su horario.
     */
    public function cancelarCita($id_cita, $motivo) {
        $this->db->beginTransaction();
        try {
            // 1. Obtener id_horario antes de cancelar
            $stmtH = $this->db->prepare(
                "SELECT HORARIO_DISPONIBILIDAD_ID_HORARIO FROM cita WHERE ID_CITA = ?"
            );
            $stmtH->execute([$id_cita]);
            $fila = $stmtH->fetch(PDO::FETCH_ASSOC);

            // 2. Cancelar cita (estado 3 = Cancelada)
            $sql = "UPDATE cita
                    SET ESTADO_CITA_ID = 3,
                        FECHA_CANCELACION = NOW(),
                        MOTIVO_CANCELACION = ?
                    WHERE ID_CITA = ?";
            $this->db->prepare($sql)->execute([$motivo, $id_cita]);

            // 3. Liberar horario si existe
            if ($fila && $fila['HORARIO_DISPONIBILIDAD_ID_HORARIO']) {
                $sqlLib = "UPDATE horario_disponibilidad SET ESTADO = 'Disponible'
                           WHERE ID_HORARIO = ?";
                $this->db->prepare($sqlLib)->execute([$fila['HORARIO_DISPONIBILIDAD_ID_HORARIO']]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reprograma una cita: cambia su fecha/hora y la deja como Pendiente.
     */
    public function reprogramarCita($id_cita, $nueva_fecha_hora, $motivo) {
        $this->db->beginTransaction();
        try {
            $nota = " | Reprogramada por admin: $motivo";

            $sql = "UPDATE cita
                    SET FECHA_HORA    = ?,
                        ESTADO_CITA_ID = 1,
                        MOTIVO        = CONCAT(MOTIVO, ?)
                    WHERE ID_CITA = ?";
            $this->db->prepare($sql)->execute([$nueva_fecha_hora, $nota, $id_cita]);

            // Notificación
            $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($nueva_fecha_hora));
            $mensaje   = "El administrador reprogramó tu cita para el $fecha_fmt. Motivo: $motivo";
            $sqlNot    = "INSERT INTO notificaciones
                              (MENSAJE, FECHA_ENVIO, ESTADO, TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION, USUARIOS_ID_USUARIOS)
                          VALUES (?, NOW(), 'NO_LEIDA', 2,
                              (SELECT p.USUARIOS_ID_USUARIOS FROM cita c
                               INNER JOIN paciente p ON c.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                               WHERE c.ID_CITA = ?))";
            $this->db->prepare($sqlNot)->execute([$mensaje, $id_cita]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function registrarCitaMasiva($docPaciente, $docOdontologo, $fechaHora, $motivo, $estadoId) {
        try {
            $this->db->beginTransaction();
            $stmtP = $this->db->prepare("SELECT p.ID_PACIENTE FROM paciente p INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE u.NUMERO_DOCUMENTO = ?");
            $stmtP->execute([$docPaciente]);
            $pac = $stmtP->fetch(\PDO::FETCH_ASSOC);
            if (!$pac) throw new \Exception("Paciente no encontrado");

            $stmtO = $this->db->prepare("SELECT o.ID_ODONTOLOGO FROM odontologo o INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS WHERE u.NUMERO_DOCUMENTO = ?");
            $stmtO->execute([$docOdontologo]);
            $odo = $stmtO->fetch(\PDO::FETCH_ASSOC);
            if (!$odo) throw new \Exception("Odontólogo no encontrado");

            $sql = "INSERT INTO cita (PACIENTE_ID_PACIENTE, ODONTOLOGO_ID_ODONTOLOGO, FECHA_HORA, MOTIVO, ESTADO_CITA_ID) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pac['ID_PACIENTE'], $odo['ID_ODONTOLOGO'], $fechaHora, $motivo, $estadoId]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}