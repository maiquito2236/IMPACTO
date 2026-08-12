<?php
namespace App\Models\paciente;

use App\Config\Database; 
use PDO;

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT NOMBRES, APELLIDOS, CORREO, ROLES_ID_ROLES FROM usuarios";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM usuarios WHERE ID_USUARIOS = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarContrasena($id, $nuevaPassHash) {
        $query = "UPDATE usuarios SET CONTRASEÑA = :contrasena WHERE ID_USUARIOS = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':contrasena', $nuevaPassHash, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualiza todos los datos del perfil principal
     */
    public function actualizarDatosPerfil($id, $datos) {
        $query = "UPDATE usuarios SET
                NOMBRES = :nombres,
                APELLIDOS = :apellidos,
                TIPO_DOCUMENTO = :tipo_doc,
                NUMERO_DOCUMENTO = :num_doc,
                TELEFONO = :telefono,
                GENERO = :genero,
                FECHA_NACIMIENTO = :fecha_nacimiento,
                DIRECCION = :direccion,
                RH = :rh,
                CORREO = :correo
            WHERE ID_USUARIOS = :id";
        
        $stmt = $this->db->prepare($query);
        $resultado = $stmt->execute([
            ':nombres' => $datos['nombres'],
            ':apellidos' => $datos['apellidos'],
            ':tipo_doc' => $datos['tipo_documento'],
            ':num_doc' => $datos['numero_documento'],
            ':telefono' => $datos['telefono'],
            ':genero' => $datos['genero'],
            ':fecha_nacimiento' => $datos['fecha_nacimiento'],
            ':direccion' => $datos['direccion'],
            ':rh' => $datos['rh'],
            ':correo' => $datos['correo'],
            ':id' => $id
        ]);

        if (!$resultado) {
            echo "<pre>";
            print_r($stmt->errorInfo());
            echo "</pre>";
            exit;
        }

        return $resultado;
    }

    public function obtenerEPS() {
        $query = "SELECT ID_EPS, NOMBRE_EPS
                FROM eps
                ORDER BY NOMBRE_EPS";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEnfermedades() {
        $query = "SELECT ID_CONDICION_MEDICA, NOMBRE_CONDICION
                FROM condicion_medica
                WHERE TIPO = 'ENFERMEDAD'
                AND ESTADO = 'ACTIVA'
                ORDER BY NOMBRE_CONDICION";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerAlergias() {
        $query = "SELECT ID_CONDICION_MEDICA, NOMBRE_CONDICION
                FROM condicion_medica
                WHERE TIPO = 'ALERGIA'
                AND ESTADO = 'ACTIVA'
                ORDER BY NOMBRE_CONDICION";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPacientePorUsuario($idUsuario) {
        $query = "SELECT *
                FROM paciente
                WHERE USUARIOS_ID_USUARIOS = :idUsuario
                LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarPaciente($idUsuario, $datos) {
        $paciente = $this->obtenerPacientePorUsuario($idUsuario);

        if ($paciente) {
            $query = "UPDATE paciente
                    SET EPS_ID_EPS = :eps,
                        NOMBRE_CONTACTO_EMERGENCIA = :nombre,
                        NUMERO_CONTACTO_EMERGENCIA = :numero
                    WHERE USUARIOS_ID_USUARIOS = :idUsuario";

            $stmt = $this->db->prepare($query);

            $resultado = $stmt->execute([
                ':eps' => $datos['eps'],
                ':nombre' => $datos['nombre_contacto_emergencia'],
                ':numero' => $datos['numero_contacto_emergencia'],
                ':idUsuario' => $idUsuario
            ]);

        } else {
            $query = "INSERT INTO paciente
            (
                USUARIOS_ID_USUARIOS,
                EPS_ID_EPS,
                NOMBRE_CONTACTO_EMERGENCIA,
                NUMERO_CONTACTO_EMERGENCIA
            )
            VALUES
            (
                :idUsuario,
                :eps,
                :nombre,
                :numero
            )";

            $stmt = $this->db->prepare($query);

            $resultado = $stmt->execute([
                ':idUsuario' => $idUsuario,
                ':eps' => (int)$datos['eps'],
                ':nombre' => $datos['nombre_contacto_emergencia'],
                ':numero' => $datos['numero_contacto_emergencia']
            ]);

            if (!$resultado) {
                echo "<pre>";
                print_r($stmt->errorInfo());
                echo "</pre>";
                exit;
            }

            return true;
        }
    }

    public function guardarCondicionesPaciente($idUsuario, $idEnfermedad, $idAlergia) {
        $paciente = $this->obtenerPacientePorUsuario($idUsuario);

        if (!$paciente) {
            return false;
        }

        $idPaciente = $paciente['ID_PACIENTE'];

        // Eliminar registros anteriores
        $sqlDelete = "DELETE FROM paciente_has_condicion_medica
                    WHERE PACIENTE_ID_PACIENTE = :idPaciente";

        $stmtDelete = $this->db->prepare($sqlDelete);

        $stmtDelete->execute([
            ':idPaciente' => $idPaciente
        ]);

        // Guardar enfermedad
        if (!empty($idEnfermedad)) {
            $sqlInsert = "INSERT INTO paciente_has_condicion_medica
            (
                PACIENTE_ID_PACIENTE,
                CONDICION_MEDICA_ID_CONDICION_MEDICA
            )
            VALUES
            (
                :idPaciente,
                :idCondicion
            )";

            $stmtInsert = $this->db->prepare($sqlInsert);

            $stmtInsert->execute([
                ':idPaciente' => $idPaciente,
                ':idCondicion' => $idEnfermedad
            ]);
        }

        // Guardar alergia
        if (!empty($idAlergia)) {
            $sqlInsert = "INSERT INTO paciente_has_condicion_medica
            (
                PACIENTE_ID_PACIENTE,
                CONDICION_MEDICA_ID_CONDICION_MEDICA
            )
            VALUES
            (
                :idPaciente,
                :idCondicion
            )";

            $stmtInsert = $this->db->prepare($sqlInsert);

            $stmtInsert->execute([
                ':idPaciente' => $idPaciente,
                ':idCondicion' => $idAlergia
            ]);
        }

        return true;
    }

    public function obtenerCondicionesPaciente($idUsuario) {
        $query = "SELECT cm.TIPO, cm.ID_CONDICION_MEDICA
                FROM paciente p
                INNER JOIN paciente_has_condicion_medica phcm
                    ON phcm.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN condicion_medica cm
                    ON cm.ID_CONDICION_MEDICA = phcm.CONDICION_MEDICA_ID_CONDICION_MEDICA
                WHERE p.USUARIOS_ID_USUARIOS = :idUsuario";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $condiciones = [
            'enfermedad' => '',
            'alergia' => ''
        ];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($fila['TIPO'] === 'ENFERMEDAD') {
                $condiciones['enfermedad'] = $fila['ID_CONDICION_MEDICA'];
            }

            if ($fila['TIPO'] === 'ALERGIA') {
                $condiciones['alergia'] = $fila['ID_CONDICION_MEDICA'];
            }
        }

        return $condiciones;
    }
}