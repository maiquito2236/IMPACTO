<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;
use Exception;

class ConsultorioAdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ==========================================
    // LISTADOS Y CONSULTAS
    // ==========================================

    public function listarConsultorios() {
        $sql = "SELECT 
                    c.ID_CONSULTORIO,
                    c.NOMBRE,
                    c.UBICACION,
                    c.DESCRIPCION,
                    c.ESTADO,
                    o.ID_ODONTOLOGO,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre,
                    e.NOMBRE_ESPECIALIDAD AS especialidad
                FROM consultorio c
                LEFT JOIN odontologo o ON o.CONSULTORIO_ID_CONSULTORIO = c.ID_CONSULTORIO
                LEFT JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD
                ORDER BY c.ID_CONSULTORIO ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerConsultorioPorId($id) {
        $sql = "SELECT 
                    c.ID_CONSULTORIO,
                    c.NOMBRE,
                    c.UBICACION,
                    c.DESCRIPCION,
                    c.ESTADO,
                    o.ID_ODONTOLOGO,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre,
                    e.NOMBRE_ESPECIALIDAD AS especialidad
                FROM consultorio c
                LEFT JOIN odontologo o ON o.CONSULTORIO_ID_CONSULTORIO = c.ID_CONSULTORIO
                LEFT JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD
                WHERE c.ID_CONSULTORIO = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerOdontologosSinConsultorio() {
        $sql = "SELECT 
                    o.ID_ODONTOLOGO,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre,
                    e.NOMBRE_ESPECIALIDAD AS especialidad
                FROM odontologo o
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD
                WHERE o.CONSULTORIO_ID_CONSULTORIO IS NULL
                ORDER BY u.NOMBRES ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosOdontologos() {
        $sql = "SELECT 
                    o.ID_ODONTOLOGO,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre,
                    e.NOMBRE_ESPECIALIDAD AS especialidad,
                    o.CONSULTORIO_ID_CONSULTORIO
                FROM odontologo o
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD
                ORDER BY u.NOMBRES ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // CRUD CONSULTORIOS
    // ==========================================

    public function crearConsultorio($data) {
        $sql = "INSERT INTO consultorio (NOMBRE, UBICACION, DESCRIPCION, ESTADO) 
                VALUES (:nombre, :ubicacion, :descripcion, 'DISPONIBLE')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function actualizarConsultorio($id, $data) {
        $sql = "UPDATE consultorio SET NOMBRE = :nombre, UBICACION = :ubicacion, DESCRIPCION = :descripcion 
                WHERE ID_CONSULTORIO = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarConsultorio($id) {
        // Solo se puede eliminar si está DISPONIBLE (sin doctor asignado)
        $check = $this->db->prepare("SELECT ESTADO FROM consultorio WHERE ID_CONSULTORIO = ?");
        $check->execute([$id]);
        $estado = $check->fetchColumn();
        
        if ($estado !== 'DISPONIBLE') {
            throw new Exception("Solo se pueden eliminar consultorios disponibles (sin asignación).");
        }

        $sql = "DELETE FROM consultorio WHERE ID_CONSULTORIO = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function cambiarEstado($id, $estado) {
        // Solo se puede cambiar entre DISPONIBLE y MANTENIMIENTO
        $check = $this->db->prepare("SELECT ESTADO FROM consultorio WHERE ID_CONSULTORIO = ?");
        $check->execute([$id]);
        $estadoActual = $check->fetchColumn();

        if ($estadoActual === 'ASIGNADO') {
            throw new Exception("No se puede cambiar el estado de un consultorio asignado.");
        }

        $sql = "UPDATE consultorio SET ESTADO = :estado WHERE ID_CONSULTORIO = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==========================================
    // ASIGNACIÓN / DESASIGNACIÓN
    // ==========================================

    public function asignarOdontologo($consultorioId, $odontologoId) {
        $this->db->beginTransaction();
        try {
            // Verificar que el consultorio esté disponible
            $check = $this->db->prepare("SELECT ESTADO FROM consultorio WHERE ID_CONSULTORIO = ?");
            $check->execute([$consultorioId]);
            $estado = $check->fetchColumn();
            if ($estado === 'ASIGNADO') {
                throw new Exception("Este consultorio ya tiene un odontólogo asignado.");
            }

            // Verificar que el odontólogo no tenga consultorio
            $checkDoc = $this->db->prepare("SELECT CONSULTORIO_ID_CONSULTORIO FROM odontologo WHERE ID_ODONTOLOGO = ?");
            $checkDoc->execute([$odontologoId]);
            $consActual = $checkDoc->fetchColumn();
            if (!empty($consActual)) {
                throw new Exception("Este odontólogo ya tiene un consultorio asignado.");
            }

            // 1. Asignar consultorio al odontólogo
            $sql1 = "UPDATE odontologo SET CONSULTORIO_ID_CONSULTORIO = :cid WHERE ID_ODONTOLOGO = :oid";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([':cid' => $consultorioId, ':oid' => $odontologoId]);

            // 2. Cambiar estado del consultorio a ASIGNADO
            $sql2 = "UPDATE consultorio SET ESTADO = 'ASIGNADO' WHERE ID_CONSULTORIO = :cid";
            $this->db->prepare($sql2)->execute([':cid' => $consultorioId]);

            // 3. Registrar en historial
            $sql3 = "INSERT INTO historial_asignacion_consultorio (CONSULTORIO_ID, ODONTOLOGO_ID, FECHA_ASIGNACION) 
                      VALUES (:cid, :oid, NOW())";
            $this->db->prepare($sql3)->execute([':cid' => $consultorioId, ':oid' => $odontologoId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function desasignarOdontologo($consultorioId, $motivo = null) {
        $this->db->beginTransaction();
        try {
            // Encontrar al odontólogo que está en ese consultorio
            $find = $this->db->prepare("SELECT ID_ODONTOLOGO FROM odontologo WHERE CONSULTORIO_ID_CONSULTORIO = ?");
            $find->execute([$consultorioId]);
            $odontologoId = $find->fetchColumn();

            if (!$odontologoId) {
                // Si el consultorio dice asignado pero no hay doctor vinculado, simplemente liberamos el consultorio.
                $sqlFix = "UPDATE consultorio SET ESTADO = 'DISPONIBLE' WHERE ID_CONSULTORIO = :cid";
                $this->db->prepare($sqlFix)->execute([':cid' => $consultorioId]);
                $this->db->commit();
                return true;
            }

            // 1. Quitar consultorio del odontólogo
            $sql1 = "UPDATE odontologo SET CONSULTORIO_ID_CONSULTORIO = NULL WHERE ID_ODONTOLOGO = :oid";
            $this->db->prepare($sql1)->execute([':oid' => $odontologoId]);

            // 2. Cambiar estado del consultorio a DISPONIBLE
            $sql2 = "UPDATE consultorio SET ESTADO = 'DISPONIBLE' WHERE ID_CONSULTORIO = :cid";
            $this->db->prepare($sql2)->execute([':cid' => $consultorioId]);

            // 3. Actualizar historial: cerrar la asignación activa
            $sql3 = "UPDATE historial_asignacion_consultorio 
                      SET FECHA_DESASIGNACION = NOW(), MOTIVO_CAMBIO = :motivo 
                      WHERE CONSULTORIO_ID = :cid AND ODONTOLOGO_ID = :oid AND FECHA_DESASIGNACION IS NULL";
            $this->db->prepare($sql3)->execute([
                ':cid' => $consultorioId, 
                ':oid' => $odontologoId,
                ':motivo' => $motivo
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ==========================================
    // HISTORIAL
    // ==========================================

    public function obtenerHistorialAsignaciones() {
        $sql = "SELECT 
                    h.ID_HISTORIAL,
                    c.NOMBRE AS consultorio_nombre,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre,
                    h.FECHA_ASIGNACION,
                    h.FECHA_DESASIGNACION,
                    h.MOTIVO_CAMBIO
                FROM historial_asignacion_consultorio h
                INNER JOIN consultorio c ON h.CONSULTORIO_ID = c.ID_CONSULTORIO
                INNER JOIN odontologo o ON h.ODONTOLOGO_ID = o.ID_ODONTOLOGO
                INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                ORDER BY h.FECHA_ASIGNACION DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // KPIs / RESUMEN
    // ==========================================

    public function obtenerResumenKPIs() {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM consultorio) AS total_consultorios,
                    (SELECT COUNT(*) FROM consultorio WHERE ESTADO = 'ASIGNADO') AS asignados,
                    (SELECT COUNT(*) FROM consultorio WHERE ESTADO = 'DISPONIBLE') AS disponibles,
                    (SELECT COUNT(*) FROM odontologo WHERE CONSULTORIO_ID_CONSULTORIO IS NOT NULL) AS odontologos_con_consultorio";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
}
