<?php

namespace App\Models\paciente;

use App\Config\Database;
use PDO;

class Cita
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerProcedimientos()
    {
        $sql = "SELECT *
                FROM procedimientos
                WHERE ESTADO = 'ACTIVO'
                ORDER BY ID_PROCEDIMIENTO ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerOdontologos()
    {
        $sql = "
            SELECT
                o.ID_ODONTOLOGO,
                CONCAT(u.NOMBRES,' ',u.APELLIDOS) AS NOMBRE,
                GROUP_CONCAT(e.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS NOMBRE_ESPECIALIDAD
            FROM odontologo o
            INNER JOIN usuarios u
                ON u.ID_USUARIOS = o.USUARIOS_ID_USUARIOS
            INNER JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
            INNER JOIN especialidad e
                ON e.ID_ESPECIALIDAD = oe.ID_ESPECIALIDAD
            GROUP BY o.ID_ODONTOLOGO
            ORDER BY NOMBRE
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHorariosDisponibles()
    {
        $sql = "
            SELECT h.*, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_DOCTOR,
            (
                SELECT GROUP_CONCAT(ID_ESPECIALIDAD SEPARATOR ',') 
                FROM odontologo_especialidad 
                WHERE ID_ODONTOLOGO = o.ID_ODONTOLOGO
            ) AS ESPECIALIDADES
            FROM horario_disponibilidad h
            INNER JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
            INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
            WHERE h.ESTADO = 'Disponible'
            ORDER BY h.FECHA, h.HORA_INICIO
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rawHorarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->expandirHorariosYFiltrarOcupados($rawHorarios);
    }

    private function expandirHorariosYFiltrarOcupados($rawHorarios) {
        $expanded = [];
        
        // 1. Obtener todas las citas activas para optimizar consultas
        $sqlCitas = "SELECT ODONTOLOGO_ID_ODONTOLOGO, FECHA_HORA FROM cita WHERE ESTADO_CITA_ID IN (1, 2)";
        $stmtCitas = $this->db->prepare($sqlCitas);
        $stmtCitas->execute();
        $citasActivas = [];
        while ($c = $stmtCitas->fetch(PDO::FETCH_ASSOC)) {
            $key = $c['ODONTOLOGO_ID_ODONTOLOGO'] . '_' . $c['FECHA_HORA'];
            $citasActivas[$key] = true;
        }

        foreach ($rawHorarios as $row) {
            $start = strtotime($row['HORA_INICIO']);
            $end = strtotime($row['HORA_FIN']);
            
            $descansoStart = !empty($row['descanso_inicio']) ? strtotime($row['descanso_inicio']) : null;
            $descansoEnd = !empty($row['descanso_fin']) ? strtotime($row['descanso_fin']) : null;

            // Si la hora de fin es menor o igual al inicio, o si la diferencia es exactamente de 1 hora o menos,
            // lo tomamos como un solo bloque.
            if ($end <= $start || ($end - $start) <= 3600) {
                if ($descansoStart !== null && $descansoEnd !== null) {
                    $slotEnd = $start + 3600;
                    if ($start < $descansoEnd && $slotEnd > $descansoStart) {
                        continue;
                    }
                }
                
                $slotTime = date('H:i:s', $start);
                $dateTimeStr = $row['FECHA'] . ' ' . $slotTime;
                $key = $row['ODONTOLOGO_ID_ODONTOLOGO'] . '_' . $dateTimeStr;
                
                if (!isset($citasActivas[$key])) {
                    $newRow = $row;
                    $newRow['HORA_INICIO'] = $slotTime;
                    $expanded[] = $newRow;
                }
                continue;
            }

            // De lo contrario (rango amplio), recorremos el rango hora por hora
            for ($time = $start; $time < $end; $time += 3600) {
                // Si se cruza con el horario de descanso, lo saltamos
                if ($descansoStart !== null && $descansoEnd !== null) {
                    $slotEnd = $time + 3600;
                    if ($time < $descansoEnd && $slotEnd > $descansoStart) {
                        continue;
                    }
                }

                $slotTime = date('H:i:s', $time);
                $dateTimeStr = $row['FECHA'] . ' ' . $slotTime;
                $key = $row['ODONTOLOGO_ID_ODONTOLOGO'] . '_' . $dateTimeStr;

                if (!isset($citasActivas[$key])) {
                    $newRow = $row;
                    $newRow['HORA_INICIO'] = $slotTime;
                    $newRow['HORA_FIN'] = date('H:i:s', $time + 3600);
                    $expanded[] = $newRow;
                }
            }
        }

        return $expanded;
    }

    public function obtenerPacientePorUsuario($idUsuario)
    {
        $sql = "
            SELECT ID_PACIENTE
            FROM paciente
            WHERE USUARIOS_ID_USUARIOS = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerHorarioPorId($idHorario)
    {
        $sql = "
            SELECT * FROM horario_disponibilidad 
            WHERE ID_HORARIO = :id 
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idHorario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($datos)
    {
        $sql = "
            INSERT INTO cita (
                PACIENTE_ID_PACIENTE, 
                ODONTOLOGO_ID_ODONTOLOGO, 
                FECHA_HORA, 
                ESTADO_CITA_ID, 
                MOTIVO, 
                HORARIO_DISPONIBILIDAD_ID_HORARIO
            ) VALUES (
                :paciente_id, 
                :odontologo_id, 
                :fecha_hora, 
                :estado_cita_id, 
                :motivo, 
                :horario_id
            )
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':paciente_id', $datos['PACIENTE_ID_PACIENTE'], PDO::PARAM_INT);
        $stmt->bindParam(':odontologo_id', $datos['ODONTOLOGO_ID_ODONTOLOGO'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_hora', $datos['FECHA_HORA']);
        $stmt->bindParam(':estado_cita_id', $datos['ESTADO_CITA_ID'], PDO::PARAM_INT);
        $stmt->bindParam(':motivo', $datos['MOTIVO']);
        $stmt->bindParam(':horario_id', $datos['HORARIO_DISPONIBILIDAD_ID_HORARIO'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function asociarCitaProcedimiento($idCita, $idProcedimiento)
    {
        $sql = "
            INSERT INTO cita_has_procedimiento (
                CITA_ID_CITA, 
                PROCEDIMIENTO_ID_PROCEDIMIENTO
            ) VALUES (
                :cita_id, 
                :procedimiento_id
            )
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cita_id', $idCita, PDO::PARAM_INT);
        $stmt->bindParam(':procedimiento_id', $idProcedimiento, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function actualizarEstadoHorario($idHorario, $estado)
    {
        // Obtener la información del horario
        $sqlInfo = "SELECT HORA_INICIO, HORA_FIN FROM horario_disponibilidad WHERE ID_HORARIO = :id";
        $stmtInfo = $this->db->prepare($sqlInfo);
        $stmtInfo->bindParam(':id', $idHorario, PDO::PARAM_INT);
        $stmtInfo->execute();
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $start = strtotime($info['HORA_INICIO']);
            $end = strtotime($info['HORA_FIN']);
            
            // Si la diferencia es mayor a 1 hora (3600 segundos), no cambiamos el estado global
            // porque es un margen de horario que sigue disponible para otras horas.
            if ($end > $start && ($end - $start) > 3600) {
                return true;
            }
        }

        $sql = "
            UPDATE horario_disponibilidad 
            SET ESTADO = :estado 
            WHERE ID_HORARIO = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $idHorario, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function obtenerCitasPorPaciente($id_paciente) {
        $sql = "SELECT 
                    c.ID_CITA,
                    c.FECHA_HORA,
                    c.MOTIVO,
                    c.RECOMENDACIONES,
                    c.ODONTOLOGO_ID_ODONTOLOGO,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', '),
                        GROUP_CONCAT(DISTINCT hc_p.NOMBRE_PROCEDIMIENTO SEPARATOR ', '),
                        hc.TRATAMIENTO,
                        c.MOTIVO
                    ) AS TRATAMIENTO, 
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_DOCTOR, 
                    GROUP_CONCAT(DISTINCT esp.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS ESPECIALIDAD, 
                    cons.NOMBRE AS CONSULTORIO, 
                    e.NOMBRE_ESTADO AS ESTADO 
                FROM cita c
                INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN especialidad esp ON oe.ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                LEFT JOIN consultorio cons ON o.CONSULTORIO_ID_CONSULTORIO = cons.ID_CONSULTORIO
                INNER JOIN estado_cita e ON c.ESTADO_CITA_ID = e.ID_ESTADO
                LEFT JOIN cita_has_procedimiento chp ON c.ID_CITA = chp.CITA_ID_CITA
                LEFT JOIN procedimientos p ON chp.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                
                LEFT JOIN factura f ON c.ID_CITA = f.CITA_ID_CITA
                LEFT JOIN historia_clinica hc ON (hc.CITA_ID_CITA = c.ID_CITA OR f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA)
                LEFT JOIN historia_clinica_has_procedimientos hcp ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                LEFT JOIN procedimientos hc_p ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = hc_p.ID_PROCEDIMIENTO
                
                WHERE c.PACIENTE_ID_PACIENTE = :id_paciente
                GROUP BY c.ID_CITA
                ORDER BY c.FECHA_HORA DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalleCitaPorId($id_cita) {
        $sql = "SELECT 
                    c.ID_CITA,
                    c.PACIENTE_ID_PACIENTE,
                    c.ODONTOLOGO_ID_ODONTOLOGO,
                    c.ODONTOLOGO_ID_ODONTOLOGO AS ODONTOLOGO_ID,
                    c.FECHA_HORA,
                    GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS LISTA_TRATAMIENTOS,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_DOCTOR,
                    GROUP_CONCAT(DISTINCT esp.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS ESPECIALIDAD,
                    cons.NOMBRE AS CONSULTORIO,
                    e.NOMBRE_ESTADO AS ESTADO,
                    c.FECHA_CANCELACION,
                    c.MOTIVO_CANCELACION,
                    c.OBSERVACIONES_DOCTOR,
                    c.RECOMENDACIONES,
                    c.FECHA_ATENCION,
                    c.created_at AS CREADA_EL,
                    GROUP_CONCAT(DISTINCT hc.DIAGNOSTICO SEPARATOR '||') as diagnosticos_raw
                FROM cita c
                INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN especialidad esp ON oe.ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                LEFT JOIN consultorio cons ON o.CONSULTORIO_ID_CONSULTORIO = cons.ID_CONSULTORIO
                INNER JOIN estado_cita e ON c.ESTADO_CITA_ID = e.ID_ESTADO
                
                -- Usamos el CITA_ID_CITA o el puente de Factura para llegar a la Historia Clínica
                LEFT JOIN factura f ON c.ID_CITA = f.CITA_ID_CITA
                LEFT JOIN historia_clinica hc ON (hc.CITA_ID_CITA = c.ID_CITA OR f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA)
                LEFT JOIN historia_clinica_has_procedimientos hcp ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                LEFT JOIN procedimientos p ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                
                WHERE c.ID_CITA = :id_cita 
                GROUP BY c.ID_CITA LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            // Si no encontró procedimientos en la historia, ponemos un texto amigable
            if (empty($resultado['LISTA_TRATAMIENTOS'])) {
                $resultado['LISTA_TRATAMIENTOS'] = 'No se registraron procedimientos';
            }
            
            // Extraer los dientes del diagnóstico
            $dientes = [];
            if (!empty($resultado['diagnosticos_raw'])) {
                $partes = explode('||', $resultado['diagnosticos_raw']);
                foreach ($partes as $diag) {
                    if (preg_match('/Piezas?:\s*([0-9,\s]+)/i', $diag, $matches)) {
                        $nums = explode(',', $matches[1]);
                        foreach ($nums as $num) {
                            if (trim($num) !== '') $dientes[] = trim($num);
                        }
                    } elseif (preg_match('/Pieza\s+([0-9]+)/i', $diag, $matches)) {
                        if (trim($matches[1]) !== '') $dientes[] = trim($matches[1]);
                    }
                }
            }
            if (!empty($dientes)) {
                $resultado['DIENTES_TRATADOS'] = implode(', ', array_unique($dientes));
            } else {
                $resultado['DIENTES_TRATADOS'] = 'General / No especificado';
            }
            
            // Limpiar las observaciones para que no muestren los dientes si están ahí
            if (!empty($resultado['OBSERVACIONES_DOCTOR'])) {
                $clean_obs = preg_replace('/(Piezas?(:)?\s*[0-9,\s]+\s*\|\s*)/i', '', $resultado['OBSERVACIONES_DOCTOR']);
                $resultado['OBSERVACIONES_DOCTOR'] = trim($clean_obs, " |");
            }
        }

        return $resultado;
    }

    public function obtenerHistorialPorCitaId($id_cita) {
        $sql = "SELECT h.*, e.NOMBRE_ESTADO AS ESTADO 
                FROM historial_cita h
                INNER JOIN estado_cita e ON h.ESTADO_CITA_ID = e.ID_ESTADO
                WHERE h.CITA_ID_CITA = :id_cita
                ORDER BY h.CREADO_EL DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelarCitaPorId($id_cita, $motivo_cancelacion) {
        try {
            // 1. Obtener el id_horario vinculado para poder liberarlo en la agenda
            $sqlCita = "SELECT HORARIO_DISPONIBILIDAD_ID_HORARIO FROM cita WHERE ID_CITA = :id_cita LIMIT 1";
            $stmtCita = $this->db->prepare($sqlCita);
            $stmtCita->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmtCita->execute();
            $cita = $stmtCita->fetch(PDO::FETCH_ASSOC);

            $this->db->beginTransaction();

            if ($cita && !empty($cita['HORARIO_DISPONIBILIDAD_ID_HORARIO'])) {
                // 2. Cambiar el estado del horario a 'Disponible' para que otro paciente lo tome
                $this->actualizarEstadoHorario($cita['HORARIO_DISPONIBILIDAD_ID_HORARIO'], 'Disponible');
            }

            // 3. Actualizar la cita: Estado 3 (Cancelada), la fecha actual y el motivo seleccionado
            $fecha_actual = date('Y-m-d H:i:s');
            $sqlCancel = "UPDATE cita 
                        SET ESTADO_CITA_ID = 3, 
                            FECHA_CANCELACION = :fecha_c, 
                            MOTIVO_CANCELACION = :motivo_c 
                        WHERE ID_CITA = :id_cita";
            
            $stmtCancel = $this->db->prepare($sqlCancel);
            $stmtCancel->bindParam(':fecha_c', $fecha_actual);
            $stmtCancel->bindParam(':motivo_c', $motivo_cancelacion);
            $stmtCancel->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmtCancel->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function obtenerHorariosDisponiblesPorDoctorYFecha($id_odontologo, $fecha) {
        $sql = "SELECT *
                FROM horario_disponibilidad 
                WHERE ODONTOLOGO_ID_ODONTOLOGO = :id_doc 
                  AND FECHA = :fecha 
                  AND ESTADO = 'Disponible'
                ORDER BY HORA_INICIO ASC";
                 
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_doc', $id_odontologo, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        $rawHorarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->expandirHorariosYFiltrarOcupados($rawHorarios);
    }
    public function obtenerHorariosDisponiblesPorFecha($fecha) {
        $sql = "SELECT h.ID_HORARIO, h.HORA_INICIO, h.ODONTOLOGO_ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_DOCTOR 
                FROM horario_disponibilidad h
                INNER JOIN odontologo o ON h.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                WHERE h.FECHA = :fecha 
                  AND h.ESTADO = 'Disponible'
                ORDER BY h.HORA_INICIO ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function actualizarReprogramacionCita($id_cita, $nuevo_id_horario, $nueva_fecha_hora, $motivo,$nuevo_id_odontologo) {
        try {
            // 1. Obtener la información actual de la cita antes de cambiarla (para la bitácora)
            $sqlViejo = "SELECT FECHA_HORA, HORARIO_DISPONIBILIDAD_ID_HORARIO FROM cita WHERE ID_CITA = :id_cita LIMIT 1";
            $stmtViejo = $this->db->prepare($sqlViejo);
            $stmtViejo->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmtViejo->execute();
            $citaVieja = $stmtViejo->fetch(PDO::FETCH_ASSOC);

            $this->db->beginTransaction();

            if ($citaVieja) {
                // 2. Liberar horario antiguo en la agenda de disponibilidad
                if (!empty($citaVieja['HORARIO_DISPONIBILIDAD_ID_HORARIO'])) {
                    $this->actualizarEstadoHorario($citaVieja['HORARIO_DISPONIBILIDAD_ID_HORARIO'], 'Disponible');
                }

                // 3. REGISTRAR EN EL HISTORIAL (Para auditoría y futuras notificaciones)
                $sqlHistorial = "INSERT INTO historial_cita (CITA_ID_CITA, ESTADO_CITA_ID, FECHA_HORA_ANTERIOR, FECHA_HORA_NUEVA, ACCION, MOTIVO) 
                                VALUES (:id_cita, 1, :fecha_ant, :fecha_nue, 'REPROGRAMACION', :motivo)";
                $stmtHist = $this->db->prepare($sqlHistorial);
                $stmtHist->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
                $stmtHist->bindParam(':fecha_ant', $citaVieja['FECHA_HORA']);
                $stmtHist->bindParam(':fecha_nue', $nueva_fecha_hora);
                $stmtHist->bindParam(':motivo', $motivo);
                $stmtHist->execute();
            }

            // 4. Ocupar el nuevo horario seleccionado en la agenda
            $this->actualizarEstadoHorario($nuevo_id_horario, 'Ocupado');

            // 5. Actualizar la cita principal con el nuevo bloque y fecha compuesto
            $sqlActualizar = "UPDATE cita 
                            SET FECHA_HORA = :fecha_hora, 
                                HORARIO_DISPONIBILIDAD_ID_HORARIO = :nuevo_horario,
                                ODONTOLOGO_ID_ODONTOLOGO = :nuevo_odontologo,
                                MOTIVO = :motivo
                            WHERE ID_CITA = :id_cita";
                            
            $stmt = $this->db->prepare($sqlActualizar);
            $stmt->bindParam(':fecha_hora', $nueva_fecha_hora);
            $stmt->bindParam(':nuevo_horario', $nuevo_id_horario, PDO::PARAM_INT);
            $stmt->bindParam(':nuevo_odontologo', $nuevo_id_odontologo, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function obtenerEstadisticasHistorial($id_paciente) {
        $stats = [
            'consultas' => 0,
            'tratamientos' => 0,
            'diagnosticos' => 0,
            'ultima_fecha' => '--/--/----',
            'ultima_especialidad' => 'Ninguna'
        ];

        try {
            // 1. Contar total de consultas asistidas (Estado Completada = 2 o según tu BD)
            $sql1 = "SELECT COUNT(*) FROM cita WHERE PACIENTE_ID_PACIENTE = :id AND ESTADO_CITA_ID = 2";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->bindParam(':id', $id_paciente, PDO::PARAM_INT);
            $stmt1->execute();
            $stats['consultas'] = $stmt1->fetchColumn();

            // 2. Contar total de tratamientos/procedimientos realizados al paciente (de ambas tablas)
            $sql2 = "SELECT (
                        SELECT COUNT(chp.PROCEDIMIENTO_ID_PROCEDIMIENTO) 
                        FROM cita_has_procedimiento chp
                        INNER JOIN cita c ON chp.CITA_ID_CITA = c.ID_CITA
                        WHERE c.PACIENTE_ID_PACIENTE = :id AND c.ESTADO_CITA_ID = 2
                     ) + (
                        SELECT COUNT(hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO)
                        FROM historia_clinica_has_procedimientos hcp
                        INNER JOIN historia_clinica hc ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                        WHERE hc.PACIENTE_ID_PACIENTE = :id
                     )";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->bindParam(':id', $id_paciente, PDO::PARAM_INT);
            $stmt2->execute();
            $stats['tratamientos'] = $stmt2->fetchColumn();

            // 3. Contar diagnósticos o valoraciones (puedes igualarlo a las consultas iniciales o completadas)
            $stats['diagnosticos'] = $stats['consultas']; 

            // 4. Obtener la fecha y especialidad de la última consulta completada
            $sql4 = "SELECT c.FECHA_HORA, GROUP_CONCAT(DISTINCT esp.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS NOMBRE_ESPECIALIDAD 
                     FROM cita c
                     INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                     INNER JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                     INNER JOIN especialidad esp ON oe.ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                     WHERE c.PACIENTE_ID_PACIENTE = :id AND c.ESTADO_CITA_ID = 2
                     GROUP BY c.ID_CITA
                     ORDER BY c.FECHA_HORA DESC LIMIT 1";
            $stmt4 = $this->db->prepare($sql4);
            $stmt4->bindParam(':id', $id_paciente, PDO::PARAM_INT);
            $stmt4->execute();
            $ultima = $stmt4->fetch(PDO::FETCH_ASSOC);

            if ($ultima) {
                $stats['ultima_fecha'] = date('d/m/Y', strtotime($ultima['FECHA_HORA']));
                $stats['ultima_especialidad'] = $ultima['NOMBRE_ESPECIALIDAD'];
            }
        } catch (\Exception $e) {
            // En caso de error, retorna los valores por defecto limpios
        }

        return $stats;
    }

    // =========================================================================
    // MÉTODO EXCLUSIVO PARA HISTORIAL CLÍNICO (NO AFECTA A MIS CITAS)
    // =========================================================================
    public function obtenerDetalleHistorialPorId($id_cita) {
        $sql = "SELECT 
                    c.ID_CITA,
                    c.FECHA_HORA,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT p.NOMBRE_PROCEDIMIENTO SEPARATOR ', '),
                        hc.TRATAMIENTO,
                        c.MOTIVO
                    ) AS LISTA_TRATAMIENTOS,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS NOMBRE_DOCTOR,
                    GROUP_CONCAT(DISTINCT esp.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS ESPECIALIDAD,
                    cons.NOMBRE AS CONSULTORIO,
                    e.NOMBRE_ESTADO AS ESTADO,
                    COALESCE(c.OBSERVACIONES_DOCTOR, hc.OBSERVACIONES, hc.DIAGNOSTICO) AS OBSERVACIONES_DOCTOR,
                    COALESCE(hc.TRATAMIENTO, hc.MOTIVO_CONSULTA) AS TRATAMIENTO,
                    (
                        SELECT GROUP_CONCAT(CONCAT('• ', p_odo.NOMBRE_PROCEDIMIENTO, ' (Pieza ', odo.PIEZA_DENTAL, ')') SEPARATOR '\n')
                        FROM odontograma_tratamientos odo
                        INNER JOIN procedimientos p_odo ON odo.PROCEDIMIENTO_ID_PROCEDIMIENTO = p_odo.ID_PROCEDIMIENTO
                        WHERE odo.PACIENTE_ID_PACIENTE = c.PACIENTE_ID_PACIENTE 
                          AND (odo.FECHA_REGISTRO = hc.FECHA_REGISTRO OR (TIME(odo.FECHA_REGISTRO) = '00:00:00' AND DATE(odo.FECHA_REGISTRO) = DATE(hc.FECHA_REGISTRO)))
                    ) AS TRATAMIENTOS_ODONTOGRAMA,
                    c.RECOMENDACIONES,
                    c.FECHA_ATENCION,
                    CONCAT(upac.NOMBRES, ' ', upac.APELLIDOS) AS NOMBRE_PACIENTE,
                    upac.NUMERO_DOCUMENTO AS DOC_PACIENTE
                FROM cita c
                INNER JOIN odontologo o ON c.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                INNER JOIN especialidad esp ON oe.ID_ESPECIALIDAD = esp.ID_ESPECIALIDAD
                LEFT JOIN consultorio cons ON o.CONSULTORIO_ID_CONSULTORIO = cons.ID_CONSULTORIO
                INNER JOIN estado_cita e ON c.ESTADO_CITA_ID = e.ID_ESTADO
                INNER JOIN paciente pac ON c.PACIENTE_ID_PACIENTE = pac.ID_PACIENTE
                INNER JOIN usuarios upac ON pac.USUARIOS_ID_USUARIOS = upac.ID_USUARIOS
                
                -- Usamos la tabla factura o el CITA_ID_CITA como puente seguro
                LEFT JOIN factura f ON c.ID_CITA = f.CITA_ID_CITA
                LEFT JOIN historia_clinica hc ON (hc.CITA_ID_CITA = c.ID_CITA OR f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA)
                
                LEFT JOIN historia_clinica_has_procedimientos hcp ON hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA = hc.ID_HISTORIA_CLINICA
                LEFT JOIN procedimientos p ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                
                WHERE c.ID_CITA = :id_cita
                GROUP BY c.ID_CITA";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Aseguramos que si no hay nada, el texto sea amigable
        if ($resultado) {
            if (empty($resultado['LISTA_TRATAMIENTOS'])) {
                $resultado['LISTA_TRATAMIENTOS'] = 'No se registraron procedimientos';
            }
            if (!empty($resultado['TRATAMIENTOS_ODONTOGRAMA'])) {
                $resultado['TRATAMIENTO'] = $resultado['TRATAMIENTOS_ODONTOGRAMA'];
            }
            
            // Limpiar las observaciones para que no muestren los dientes si están ahí
            if (!empty($resultado['OBSERVACIONES_DOCTOR'])) {
                $clean_obs = preg_replace('/(Piezas?(:)?\s*[0-9,\s]+\s*\|\s*)/i', '', $resultado['OBSERVACIONES_DOCTOR']);
                $resultado['OBSERVACIONES_DOCTOR'] = trim($clean_obs, " |");
            }
        }
        
        return $resultado;
    }
}