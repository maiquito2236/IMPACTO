<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;
use Exception;

class OtrosAdminModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista todos los procedimientos activos unidos con su especialidad.
     */
    public function listarProcedimientos() {
        $sql = "SELECT 
                    p.ID_PROCEDIMIENTO AS id,
                    p.NOMBRE_PROCEDIMIENTO AS nombre,
                    p.DESCRIPCION AS descripcion,
                    p.TIEMPO_ESTIMADO AS duracion_num,
                    CONCAT(p.TIEMPO_ESTIMADO, ' min') AS duracion,
                    p.COSTO AS precio_num,
                    CONCAT('$', FORMAT(p.COSTO, 0)) AS precio,
                    p.ESTADO AS estado
                FROM procedimientos p
                ORDER BY p.ID_PROCEDIMIENTO DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas las especialidades.
     */
    public function listarEspecialidades() {
        $sql = "SELECT 
                    ID_ESPECIALIDAD AS id,
                    NOMBRE_ESPECIALIDAD AS nombre,
                    ESTADO AS estado
                FROM especialidad
                ORDER BY ID_ESPECIALIDAD DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas las EPS.
     */
    public function listarEps() {
        $sql = "SELECT 
                    ID_EPS AS id,
                    NOMBRE_EPS AS nombre,
                    ESTADO AS estado
                FROM eps
                ORDER BY ID_EPS DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda un nuevo procedimiento en la base de datos.
     */
    public function guardarProcedimiento($nombre, $descripcion, $costo, $tiempo, $estado) {
        $this->db->beginTransaction();
        try {
            // Asignamos NULL (o 1 si es obligatorio) a ESPECIALIDAD_ID_ESPECIALIDAD ya que se removió de UI
            $sql = "INSERT INTO procedimientos 
                        (NOMBRE_PROCEDIMIENTO, DESCRIPCION, COSTO, TIEMPO_ESTIMADO, ESPECIALIDAD_ID_ESPECIALIDAD, ESTADO, TIPO_COBRO) 
                    VALUES (?, ?, ?, ?, 1, ?, 1)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, 
                $descripcion, 
                $costo, 
                $tiempo, 
                strtoupper($estado)
            ]);
            
            $id_insertado = $this->db->lastInsertId();
            $this->db->commit();
            
            return $id_insertado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un procedimiento existente en la base de datos.
     */
    public function actualizarProcedimiento($id, $nombre, $descripcion, $costo, $tiempo, $estado) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE procedimientos 
                    SET NOMBRE_PROCEDIMIENTO = ?, 
                        DESCRIPCION = ?, 
                        COSTO = ?, 
                        TIEMPO_ESTIMADO = ?, 
                        ESTADO = ?
                    WHERE ID_PROCEDIMIENTO = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, 
                $descripcion, 
                $costo, 
                $tiempo, 
                strtoupper($estado),
                $id
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un procedimiento inactivándolo para proteger el historial clínico.
     */
    public function eliminarProcedimiento($id_procedimiento) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE procedimientos SET ESTADO = 'INACTIVO' WHERE ID_PROCEDIMIENTO = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_procedimiento]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function guardarEspecialidad($nombre, $estado) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO especialidad (NOMBRE_ESPECIALIDAD, ESTADO) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, strtoupper($estado)]);
            
            $id_insertado = $this->db->lastInsertId();
            $this->db->commit();
            
            return $id_insertado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarEspecialidad($id, $nombre, $estado) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE especialidad 
                    SET NOMBRE_ESPECIALIDAD = ?, 
                        ESTADO = ?
                    WHERE ID_ESPECIALIDAD = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, 
                strtoupper($estado),
                $id
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminarEspecialidad($id_especialidad) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE especialidad SET ESTADO = 'INACTIVO' WHERE ID_ESPECIALIDAD = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_especialidad]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function guardarEps($nombre, $estado) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO eps (NOMBRE_EPS, ESTADO) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, strtoupper($estado)]);
            
            $id_insertado = $this->db->lastInsertId();
            $this->db->commit();
            
            return $id_insertado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarEps($id, $nombre, $estado) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE eps 
                    SET NOMBRE_EPS = ?, 
                        ESTADO = ?
                    WHERE ID_EPS = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, 
                strtoupper($estado),
                $id
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminarEps($id_eps) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE eps SET ESTADO = 'INACTIVO' WHERE ID_EPS = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_eps]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lista todas las alergias desde condicion_medica.
     */
    public function listarAlergias() {
        $sql = "SELECT 
                    ID_CONDICION_MEDICA AS id,
                    NOMBRE_CONDICION AS nombre,
                    DESCRIPCION AS descripcion,
                    ESTADO AS estado
                FROM condicion_medica
                WHERE TIPO = 'ALERGIA'
                ORDER BY ID_CONDICION_MEDICA DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardarAlergia($nombre, $descripcion, $estado) {
        $this->db->beginTransaction();
        try {
            $estadoDB = (strtoupper($estado) === 'INACTIVO' || strtoupper($estado) === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
            $sql = "INSERT INTO condicion_medica (NOMBRE_CONDICION, TIPO, DESCRIPCION, ESTADO) VALUES (?, 'ALERGIA', ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $estadoDB]);
            
            $id_insertado = $this->db->lastInsertId();
            $this->db->commit();
            
            return $id_insertado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarAlergia($id, $nombre, $descripcion, $estado) {
        $this->db->beginTransaction();
        try {
            $estadoDB = (strtoupper($estado) === 'INACTIVO' || strtoupper($estado) === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
            $sql = "UPDATE condicion_medica 
                    SET NOMBRE_CONDICION = ?, 
                        DESCRIPCION = ?, 
                        ESTADO = ?
                    WHERE ID_CONDICION_MEDICA = ? AND TIPO = 'ALERGIA'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, 
                $descripcion,
                $estadoDB,
                $id
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminarAlergia($id_alergia) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE condicion_medica SET ESTADO = 'INACTIVA' WHERE ID_CONDICION_MEDICA = ? AND TIPO = 'ALERGIA'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_alergia]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lista todas las enfermedades desde condicion_medica.
     */
    public function listarEnfermedades() {
        $sql = "SELECT 
                    ID_CONDICION_MEDICA AS id,
                    NOMBRE_CONDICION AS nombre,
                    DESCRIPCION AS descripcion,
                    ESTADO AS estado
                FROM condicion_medica
                WHERE TIPO = 'ENFERMEDAD'
                ORDER BY ID_CONDICION_MEDICA DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardarEnfermedad($nombre, $descripcion, $estado) {
        $this->db->beginTransaction();
        try {
            $estadoDB = (strtoupper($estado) === 'INACTIVO' || strtoupper($estado) === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
            $sql = "INSERT INTO condicion_medica (NOMBRE_CONDICION, TIPO, DESCRIPCION, ESTADO) VALUES (?, 'ENFERMEDAD', ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $estadoDB]);
            
            $id_insertado = $this->db->lastInsertId();
            $this->db->commit();
            
            return $id_insertado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarEnfermedad($id, $nombre, $descripcion, $estado) {
        $this->db->beginTransaction();
        try {
            $estadoDB = (strtoupper($estado) === 'INACTIVO' || strtoupper($estado) === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
            $sql = "UPDATE condicion_medica 
                    SET NOMBRE_CONDICION = ?, 
                        DESCRIPCION = ?, 
                        ESTADO = ?
                    WHERE ID_CONDICION_MEDICA = ? AND TIPO = 'ENFERMEDAD'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, 
                $descripcion,
                $estadoDB,
                $id
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminarEnfermedad($id_enfermedad) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE condicion_medica SET ESTADO = 'INACTIVA' WHERE ID_CONDICION_MEDICA = ? AND TIPO = 'ENFERMEDAD'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_enfermedad]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}