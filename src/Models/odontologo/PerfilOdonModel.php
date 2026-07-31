<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;
use Exception;

class PerfilOdonModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Trae el perfil cruzando usuarios, odontologo y especialidad
    public function obtenerPerfil($idUsuario) {
        $sql = "SELECT u.*, 
                       GROUP_CONCAT(e.ID_ESPECIALIDAD SEPARATOR ',') AS especialidad_id, 
                       GROUP_CONCAT(e.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS especialidad_nombre
                FROM usuarios u 
                LEFT JOIN odontologo o ON u.ID_USUARIOS = o.USUARIOS_ID_USUARIOS 
                LEFT JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                LEFT JOIN especialidad e ON oe.ID_ESPECIALIDAD = e.ID_ESPECIALIDAD 
                WHERE u.ID_USUARIOS = ?
                GROUP BY u.ID_USUARIOS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Trae todas las especialidades para llenar el form
    public function obtenerEspecialidades() {
        $sql = "SELECT ID_ESPECIALIDAD, NOMBRE_ESPECIALIDAD FROM especialidad ORDER BY NOMBRE_ESPECIALIDAD ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarPerfil($idUsuario, $datos, $passActual, $passNueva) {
        $this->db->beginTransaction();
        
        try {
            // Validar si el correo ya pertenece a otra persona
            $stmt = $this->db->prepare("SELECT ID_USUARIOS FROM usuarios WHERE CORREO = ? AND ID_USUARIOS != ?");
            $stmt->execute([$datos['correo'], $idUsuario]);
            if ($stmt->fetch()) {
                throw new Exception("El correo ingresado ya está registrado por otro usuario.");
            }

            // 1. Actualizar tabla USUARIOS (Quitamos el RH para que coincida con el formulario)
            $sqlUsuarios = "UPDATE usuarios SET 
                            NOMBRES = ?, APELLIDOS = ?, TIPO_DOCUMENTO = ?, NUMERO_DOCUMENTO = ?, 
                            GENERO = ?, FECHA_NACIMIENTO = ?, DIRECCION = ?, 
                            TELEFONO = ?, CORREO = ? 
                            WHERE ID_USUARIOS = ?";
            $stmt = $this->db->prepare($sqlUsuarios);
            $stmt->execute([
                $datos['nombres'], $datos['apellidos'], $datos['tipo_documento'], $datos['numero_documento'],
                $datos['genero'], $datos['fecha_nacimiento'], $datos['direccion'], 
                $datos['telefono'], $datos['correo'], $idUsuario
            ]);

            // 2. Actualizar o Insertar tabla ODONTOLOGO y su Especialidad
            if (!empty($datos['especialidad_id'])) {
                // Verificamos si el odontólogo ya está registrado en la tabla odontologo
                $stmtCheck = $this->db->prepare("SELECT ID_ODONTOLOGO FROM odontologo WHERE USUARIOS_ID_USUARIOS = ?");
                $stmtCheck->execute([$idUsuario]);
                $existeOdon = $stmtCheck->fetch();

                if ($existeOdon) {
                    $idOdontologoExistente = $existeOdon['ID_ODONTOLOGO'];
                } else {
                    // Si NO existe, insertamos su registro por primera vez
                    $sqlOdon = "INSERT INTO odontologo (USUARIOS_ID_USUARIOS, CONSULTORIO_ID_CONSULTORIO) VALUES (?, 1)";
                    $stmtOdon = $this->db->prepare($sqlOdon);
                    $stmtOdon->execute([$idUsuario]);
                    $idOdontologoExistente = $this->db->lastInsertId();
                }

                $especialidades = is_array($datos['especialidad_id']) ? $datos['especialidad_id'] : [$datos['especialidad_id']];
                
                $this->db->prepare("DELETE FROM odontologo_especialidad WHERE ID_ODONTOLOGO = ?")->execute([$idOdontologoExistente]);
                
                $stmtPivot = $this->db->prepare("INSERT IGNORE INTO odontologo_especialidad (ID_ODONTOLOGO, ID_ESPECIALIDAD) VALUES (?, ?)");
                foreach ($especialidades as $eId) {
                    $stmtPivot->execute([$idOdontologoExistente, (int)$eId]);
                }
            }

            // 3. Cambiar contraseña si ingresó una nueva
            if (!empty($passNueva)) {
                $nuevoHash = password_hash($passNueva, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE usuarios SET CONTRASEÑA = ? WHERE ID_USUARIOS = ?");
                $stmt->execute([$nuevoHash, $idUsuario]);
            }

            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}