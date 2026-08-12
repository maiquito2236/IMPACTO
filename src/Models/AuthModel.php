<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class AuthModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // =========================================================
    // 1. BUSCAR USUARIO PARA LOGIN Y RECUPERACIÓN
    // =========================================================
    public function buscarPorCorreo($correo) {
        // Quitamos el AND ESTADO = 'Activo' para poder traer la info y evaluarla luego
        $sql = "SELECT * FROM usuarios WHERE CORREO = :correo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // 2. REGISTRO PÚBLICO (CREA USUARIO + PERFIL PACIENTE)
    // =========================================================
    public function registrar($tipoDoc, $numDoc, $nombres, $apellidos, $telefono, $correo, $contrasena, $genero) {
        try {
            // Iniciamos la transacción (Si algo falla, no guarda datos a medias)
            $this->db->beginTransaction();

            // 1. Insertamos en USUARIOS (Rol 3 = Paciente por defecto)
            $sqlUser = "INSERT INTO usuarios (ROLES_ID_ROLES, NOMBRES, APELLIDOS, TIPO_DOCUMENTO, NUMERO_DOCUMENTO, TELEFONO, CORREO, GENERO, CONTRASEÑA, ESTADO) 
                        VALUES (3, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([$nombres, $apellidos, $tipoDoc, $numDoc, $telefono, $correo, $genero, $contrasena]);

            // Capturamos el ID del usuario recién creado
            $idUsuarioCreado = $this->db->lastInsertId();

            // 2. Insertamos en PACIENTE (Asignamos EPS 1 por defecto para cumplir la regla NOT NULL de la BD)
            $sqlPaciente = "INSERT INTO paciente (USUARIOS_ID_USUARIOS, EPS_ID_EPS, NOMBRE_CONTACTO_EMERGENCIA, NUMERO_CONTACTO_EMERGENCIA) 
                            VALUES (?, 1, '', '')";
            $stmtPaciente = $this->db->prepare($sqlPaciente);
            $stmtPaciente->execute([$idUsuarioCreado]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // Si hay error (ej. correo duplicado), deshace todo y evita usuarios fantasma
            $this->db->rollBack();
            return false;
        }
    }

    // =========================================================
    // 3. UTILIDAD PARA CONSTRUIR LA SESIÓN DE PACIENTE EN LOGIN
    // =========================================================
    public function obtenerPacientePorUsuario($idUsuario) {
        $sql = "SELECT ID_PACIENTE FROM paciente WHERE USUARIOS_ID_USUARIOS = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerOdontologoPorUsuario($idUsuario) {
        $sql = "SELECT ID_ODONTOLOGO FROM odontologo WHERE USUARIOS_ID_USUARIOS = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // 4. RECUPERACIÓN DE CONTRASEÑA (TOKENS OTP)
    // =========================================================
    public function guardarTokenRecuperacion($correo, $token) {
        $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $sql = "UPDATE usuarios SET TOKEN_RECUPERACION = :token, TOKEN_EXPIRACION = :expiracion WHERE CORREO = :correo";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':token' => $token,
            ':expiracion' => $fechaExpiracion,
            ':correo' => $correo
        ]);
    }

    public function verificarToken($token) {
        $fechaActual = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM usuarios WHERE TOKEN_RECUPERACION = :token AND TOKEN_EXPIRACION >= :actual LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':token' => $token,
            ':actual' => $fechaActual
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarContrasenaPorToken($token, $nuevaContrasena) {
        $sql = "UPDATE usuarios SET CONTRASEÑA = :pass, TOKEN_RECUPERACION = NULL, TOKEN_EXPIRACION = NULL WHERE TOKEN_RECUPERACION = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':pass' => $nuevaContrasena,
            ':token' => $token
        ]);
    }
}