<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class PacienteModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPacientes() {
        $sql = "SELECT 
                    u.NOMBRES, 
                    u.APELLIDOS, 
                    u.NUMERO_DOCUMENTO, 
                    u.CORREO, 
                    u.TELEFONO 
                FROM paciente p
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                ORDER BY u.NOMBRES ASC";
                
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}