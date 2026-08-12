<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;

class PerfilAdminModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Trae únicamente la información de la tabla usuarios
    public function obtenerPorId($id) {
        $query = "SELECT * FROM usuarios WHERE ID_USUARIOS = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarDatosPerfil($id, $datos) {
        $query = "UPDATE usuarios SET 
                    NOMBRES = :nombres, 
                    APELLIDOS = :apellidos, 
                    TIPO_DOCUMENTO = :tipo_doc,
                    NUMERO_DOCUMENTO = :num_doc, 
                    TELEFONO = :telefono, 
                    CORREO = :correo,
                    GENERO = :genero, 
                    FECHA_NACIMIENTO = :fecha_nac, 
                    DIRECCION = :direccion, 
                    RH = :rh
                  WHERE ID_USUARIOS = :id";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':nombres'   => $datos['nombres'],
            ':apellidos' => $datos['apellidos'],
            ':tipo_doc'  => $datos['tipo_documento'],
            ':num_doc'   => $datos['numero_documento'],
            ':telefono'  => $datos['telefono'],
            ':correo'    => $datos['correo'],
            ':genero'    => $datos['genero'],
            ':fecha_nac' => $datos['fecha_nacimiento'],
            ':direccion' => $datos['direccion'],
            ':rh'        => $datos['rh'],
            ':id'        => $id
        ]);
    }

    public function actualizarContrasena($id, $hash) {
        $query = "UPDATE usuarios SET CONTRASEÑA = :hash WHERE ID_USUARIOS = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':hash' => $hash, ':id' => $id]);
    }
}