<?php
namespace App\Models\administrador;

use App\Config\Database;
use PDO;
use Exception;

/**
 * Modelo de Gestión de Usuarios (panel del Administrador).
 * Trabaja sobre la tabla `usuarios` (rol 1=Administrador, 2=Odontólogo,
 * 3=Paciente, 4=Administrador Jefe), la tabla `roles` y, cuando el rol
 * asignado es Odontólogo, también sobre la tabla `odontologo`
 * (especialidad + consultorio).
 */
class GestionUsuarioModel {

    private $db;

    // Rol protegido: el Administrador Jefe no se puede editar ni eliminar
    const ROL_ADMIN_JEFE = 4;

    // Rol que obliga a tener especialidad y consultorio asignados
    const ROL_ODONTOLOGO = 2;

    // Otros roles
    const ROL_PACIENTE = 3;
    const ROL_ADMIN = 1;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Devuelve todos los usuarios con su rol legible, listos para la tabla
     * del panel (id, nombre completo, email, rol_id, rol_nombre, estado).
     */
    public function listarTodos() {
        $query = "SELECT 
                    u.ID_USUARIOS        AS id,
                    CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS nombre,
                    u.CORREO              AS email,
                    u.ROLES_ID_ROLES      AS rol_id,
                    r.ROL_NOMBRE          AS rol_nombre,
                    u.ESTADO              AS estado,
                    GROUP_CONCAT(e.ID_ESPECIALIDAD SEPARATOR ',') AS especialidad_id,
                    GROUP_CONCAT(e.NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS especialidad_nombre,
                    o.CONSULTORIO_ID_CONSULTORIO   AS consultorio,
                    c.NOMBRE              AS consultorio_nombre
                FROM usuarios u
                INNER JOIN roles r ON r.ID_ROLES = u.ROLES_ID_ROLES
                LEFT JOIN (
                    SELECT od1.*
                    FROM odontologo od1
                    WHERE od1.ID_ODONTOLOGO = (
                        SELECT MAX(od2.ID_ODONTOLOGO)
                        FROM odontologo od2
                        WHERE od2.USUARIOS_ID_USUARIOS = od1.USUARIOS_ID_USUARIOS
                    )
                ) o ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN odontologo_especialidad oe ON oe.ID_ODONTOLOGO = o.ID_ODONTOLOGO
                LEFT JOIN especialidad e ON e.ID_ESPECIALIDAD = oe.ID_ESPECIALIDAD
                LEFT JOIN consultorio c ON c.ID_CONSULTORIO = o.CONSULTORIO_ID_CONSULTORIO
                GROUP BY u.ID_USUARIOS
                ORDER BY u.ID_USUARIOS DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Devuelve el catálogo de especialidades (para el <select> del panel).
     */
    public function listarEspecialidades() {
        $query = "SELECT ID_ESPECIALIDAD AS id, NOMBRE_ESPECIALIDAD AS nombre 
                  FROM especialidad 
                  ORDER BY nombre ASC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve la lista de consultorios para el select.
     */
    public function listarConsultorios() {
        $query = "SELECT ID_CONSULTORIO AS id, NOMBRE AS nombre 
                  FROM consultorio 
                  WHERE ESTADO = 'DISPONIBLE' 
                  ORDER BY nombre ASC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si ya existe un usuario registrado con ese correo.
     */
    public function existeCorreo($correo) {
        $query = "SELECT ID_USUARIOS FROM usuarios WHERE CORREO = :correo LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un usuario rápido desde el panel admin (solo nombre, correo y rol).
     * Genera una contraseña temporal (se devuelve en texto plano UNA sola vez
     * para que el admin se la comunique al usuario) y completa con valores
     * por defecto los campos obligatorios que el formulario rápido no pide.
     *
     * Devuelve un arreglo con los datos del usuario creado + la contraseña temporal.
     */
    public function crear($nombreCompleto, $correo, $telefono, $rolId, $especialidadId = null, $consultorioId = null) {

        if ($this->existeCorreo($correo)) {
            throw new Exception("Ya existe un usuario registrado con ese correo.");
        }

        // Separar "Nombre Apellido(s)" -> NOMBRES / APELLIDOS
        $partes     = preg_split('/\s+/', trim($nombreCompleto), 2);
        $nombres    = $partes[0] ?? $nombreCompleto;
        $apellidos  = $partes[1] ?? '-';

        // Contraseña temporal aleatoria (el usuario deberá cambiarla al ingresar)
        $passTemp     = $this->generarPasswordTemporal();
        $passHash     = password_hash($passTemp, PASSWORD_BCRYPT);

        // Documento provisional único (el admin/usuario lo podrá editar después
        // desde "Perfil"); evita choques con la restricción NOT NULL de la tabla.
        $numeroDocProvisional = (int) ('9' . substr((string) time(), -8));

        $query = "INSERT INTO usuarios 
                    (ROLES_ID_ROLES, NOMBRES, APELLIDOS, TIPO_DOCUMENTO, NUMERO_DOCUMENTO, 
                     TELEFONO, CORREO, GENERO, CONTRASEÑA, ESTADO)
                  VALUES 
                    (:rol_id, :nombres, :apellidos, 'C.C', :num_doc, 
                     :telefono, :correo, 'OTRO', :pass, 'Activo')";

        $stmt = $this->db->prepare($query);
        $resultado = $stmt->execute([
            ':rol_id'    => (int) $rolId,
            ':nombres'   => $nombres,
            ':apellidos' => $apellidos,
            ':num_doc'   => $numeroDocProvisional,
            ':telefono'  => $telefono,
            ':correo'    => $correo,
            ':pass'      => $passHash,
        ]);

        if (!$resultado) {
            throw new Exception("No se pudo crear el usuario en la base de datos.");
        }

        $nuevoId = (int) $this->db->lastInsertId();

        // Si es paciente, insertarlo en la tabla paciente de una vez
        if ((int) $rolId === self::ROL_PACIENTE) {
            $stmtPac = $this->db->prepare("INSERT INTO paciente (USUARIOS_ID_USUARIOS, EPS_ID_EPS) VALUES (:id, 1)");
            $stmtPac->execute([':id' => $nuevoId]);
            } else if ((int) $rolId === self::ROL_ODONTOLOGO) {
            if ($especialidadId !== null && $consultorioId !== null) {
                $this->asignarEspecialidadYConsultorio($nuevoId, $especialidadId, $consultorioId);
            }
        }

        return [
            'id'         => $nuevoId,
            'nombre'     => $nombres . ' ' . $apellidos,
            'email'      => $correo,
            'rol_id'     => (int) $rolId,
            'rol_nombre' => $this->obtenerNombreRol((int) $rolId),
            'estado'     => 'Activo',
            'pass_temp'  => $passTemp,
        ];
    }

    /**
     * Actualiza el rol y el estado de un usuario.
     */
    public function actualizarRol($id, $rolId, $estado, $especialidadId = null, $consultorio = null) {
        $usuario = $this->obtenerRolActual($id);

        if (!$usuario) {
            throw new Exception("El usuario indicado no existe.");
        }

        if ((int) $usuario['ROLES_ID_ROLES'] === self::ROL_ADMIN_JEFE) {
            throw new Exception("El Administrador Jefe no puede ser modificado.");
        }

        $rolId = (int) $rolId;
        $esOdontologo = ($rolId === self::ROL_ODONTOLOGO);

        // ── Validación obligatoria de especialidad y consultorio ──────
        if ($esOdontologo) {
            if (empty($especialidadId) || $consultorio === null || $consultorio === '') {
                throw new Exception("Para asignar el rol de Odontólogo es obligatorio indicar al menos una especialidad y un consultorio.");
            }

            if (!is_array($especialidadId)) {
                $especialidadId = [$especialidadId];
            }

            foreach ($especialidadId as $eId) {
                if (!ctype_digit((string) $eId) || (int) $eId <= 0) {
                    throw new Exception("Una especialidad indicada no es válida.");
                }
                if (!$this->especialidadExiste((int)$eId)) {
                    throw new Exception("Una especialidad seleccionada no existe en el sistema.");
                }
            }

            if (!ctype_digit((string) $consultorio) || (int) $consultorio <= 0) {
                throw new Exception("El consultorio indicado no es válido. Debe ser un número entero positivo.");
            }

            $consultorio = (int) $consultorio;
        }

        $this->db->beginTransaction();
        try {
            $query = "UPDATE usuarios 
                      SET ROLES_ID_ROLES = :rol_id, ESTADO = :estado 
                      WHERE ID_USUARIOS = :id";

            $stmt = $this->db->prepare($query);
            $resultado = $stmt->execute([
                ':rol_id' => $rolId,
                ':estado' => $estado,
                ':id'     => (int) $id,
            ]);

            if (!$resultado) {
                throw new Exception("No se pudo actualizar el usuario.");
            }

            if ($esOdontologo) {
                $this->asignarEspecialidadYConsultorio((int) $id, $especialidadId, $consultorio);
                try {
                    $this->db->prepare("DELETE FROM paciente WHERE USUARIOS_ID_USUARIOS = :id")->execute([':id' => $id]);
                } catch (Exception $e) { /* ignorar constraint */ }
            } elseif ($rolId === self::ROL_PACIENTE) {
                $stmt = $this->db->prepare("SELECT ID_PACIENTE FROM paciente WHERE USUARIOS_ID_USUARIOS = :id");
                $stmt->execute([':id' => $id]);
                if (!$stmt->fetch()) {
                    $this->db->prepare("INSERT INTO paciente (USUARIOS_ID_USUARIOS, EPS_ID_EPS) VALUES (:id, 1)")->execute([':id' => $id]);
                }
                try {
                    $this->db->prepare("DELETE FROM odontologo WHERE USUARIOS_ID_USUARIOS = :id")->execute([':id' => $id]);
                } catch (Exception $e) { /* ignorar constraint */ }
            } else {
                try { $this->db->prepare("DELETE FROM paciente WHERE USUARIOS_ID_USUARIOS = :id")->execute([':id' => $id]); } catch(Exception $e) {}
                try { $this->db->prepare("DELETE FROM odontologo WHERE USUARIOS_ID_USUARIOS = :id")->execute([':id' => $id]); } catch(Exception $e) {}
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return true;
    }

    public function eliminar($id) {
        $usuario = $this->obtenerRolActual($id);
        if (!$usuario) throw new Exception("El usuario indicado no existe.");
        if ((int) $usuario['ROLES_ID_ROLES'] === self::ROL_ADMIN_JEFE) throw new Exception("El Administrador Jefe no puede ser eliminado del sistema.");

        $query = "UPDATE usuarios SET ESTADO = 'Inactivo' WHERE ID_USUARIOS = :id";
        $stmt  = $this->db->prepare($query);
        $resultado = $stmt->execute([':id' => (int) $id]);

        if (!$resultado) throw new Exception("No se pudo desactivar el usuario.");
        return true;
    }

    public function obtenerRolActual($id) {
        $query = "SELECT ID_USUARIOS, ROLES_ID_ROLES FROM usuarios WHERE ID_USUARIOS = :id LIMIT 1";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function especialidadExiste($especialidadId) {
        $query = "SELECT ID_ESPECIALIDAD FROM especialidad WHERE ID_ESPECIALIDAD = :id LIMIT 1";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $especialidadId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function asignarEspecialidadYConsultorio($usuarioId, $especialidades, $consultorio) {
        if (!is_array($especialidades)) $especialidades = [$especialidades];

        $query = "SELECT ID_ODONTOLOGO AS id, CONSULTORIO_ID_CONSULTORIO AS old_consultorio 
                  FROM odontologo 
                  WHERE USUARIOS_ID_USUARIOS = :usuario_id 
                  ORDER BY ID_ODONTOLOGO DESC LIMIT 1";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $idOdontologoExistente = $fila['id'] ?? null;
        $oldConsultorio = $fila['old_consultorio'] ?? null;

        if ($idOdontologoExistente) {
            $query = "UPDATE odontologo 
                      SET CONSULTORIO_ID_CONSULTORIO = :consultorio 
                      WHERE ID_ODONTOLOGO = :id_odontologo";
            $stmt = $this->db->prepare($query);
            $resultado = $stmt->execute([
                ':consultorio'     => $consultorio,
                ':id_odontologo'   => $idOdontologoExistente,
            ]);
        } else {
            $query = "INSERT INTO odontologo (USUARIOS_ID_USUARIOS, CONSULTORIO_ID_CONSULTORIO) 
                      VALUES (:usuario_id, :consultorio)";
            $stmt = $this->db->prepare($query);
            $resultado = $stmt->execute([
                ':usuario_id'      => $usuarioId,
                ':consultorio'     => $consultorio,
            ]);
            $idOdontologoExistente = $this->db->lastInsertId();
        }

        if (!$resultado) throw new Exception("No se pudo asignar el consultorio al odontólogo.");

        // Manage pivot table for multiple specialties
        $this->db->prepare("DELETE FROM odontologo_especialidad WHERE ID_ODONTOLOGO = :id")->execute([':id' => $idOdontologoExistente]);
        $stmtPivot = $this->db->prepare("INSERT IGNORE INTO odontologo_especialidad (ID_ODONTOLOGO, ID_ESPECIALIDAD) VALUES (?, ?)");
        foreach ($especialidades as $eId) {
            $stmtPivot->execute([$idOdontologoExistente, (int)$eId]);
        }

        if ($oldConsultorio != $consultorio) {
            if ($oldConsultorio) {
                $this->db->prepare("UPDATE consultorio SET ESTADO = 'DISPONIBLE' WHERE ID_CONSULTORIO = :cid")->execute([':cid' => $oldConsultorio]);
                $this->db->prepare("UPDATE historial_asignacion_consultorio SET FECHA_DESASIGNACION = NOW(), MOTIVO_CAMBIO = 'Cambio de Odontólogo' WHERE CONSULTORIO_ID = :cid AND ODONTOLOGO_ID = :oid AND FECHA_DESASIGNACION IS NULL")->execute([':cid' => $oldConsultorio, ':oid' => $idOdontologoExistente]);
            }
            if ($consultorio) {
                $this->db->prepare("UPDATE consultorio SET ESTADO = 'ASIGNADO' WHERE ID_CONSULTORIO = :cid")->execute([':cid' => $consultorio]);
                $this->db->prepare("INSERT INTO historial_asignacion_consultorio (CONSULTORIO_ID, ODONTOLOGO_ID, FECHA_ASIGNACION) VALUES (:cid, :oid, NOW())")->execute([':cid' => $consultorio, ':oid' => $idOdontologoExistente]);
            }
        }
    }
    
    private function obtenerNombreRol($rolId) {
        $query = "SELECT ROL_NOMBRE FROM roles WHERE ID_ROLES = :id LIMIT 1";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $rolId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila['ROL_NOMBRE'] : null;
    }

    private function generarPasswordTemporal($longitud = 10) {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $pass = '';
        for ($i = 0; $i < $longitud; $i++) $pass .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        return $pass;
    }

    public function registrarUsuarioMasivo($dataUsuario, $especialidades = null, $epsId = null) {
        $dataUsuario['contrasena'] = password_hash($dataUsuario['numero_documento'], PASSWORD_DEFAULT);
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("INSERT INTO usuarios (NOMBRES, APELLIDOS, TIPO_DOCUMENTO, NUMERO_DOCUMENTO, TELEFONO, CORREO, GENERO, CONTRASEÑA, ESTADO, ROLES_ID_ROLES) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $dataUsuario['nombres'],
                $dataUsuario['apellidos'],
                $dataUsuario['tipo_documento'],
                $dataUsuario['numero_documento'],
                $dataUsuario['telefono'],
                $dataUsuario['correo'],
                $dataUsuario['genero'],
                $dataUsuario['contrasena'],
                $dataUsuario['estado'],
                $dataUsuario['rol']
            ]);
            $idUsuario = $this->db->lastInsertId();
            
            if ($dataUsuario['rol'] == 2) { 
                $stmt = $this->db->prepare("INSERT INTO odontologo (USUARIOS_ID_USUARIOS) VALUES (?)");
                $stmt->execute([$idUsuario]);
                $idOdontologoExistente = $this->db->lastInsertId();

                if (empty($especialidades)) $especialidades = [1];
                if (!is_array($especialidades)) $especialidades = [$especialidades];
                $stmtPivot = $this->db->prepare("INSERT IGNORE INTO odontologo_especialidad (ID_ODONTOLOGO, ID_ESPECIALIDAD) VALUES (?, ?)");
                foreach ($especialidades as $eId) {
                    $stmtPivot->execute([$idOdontologoExistente, (int)$eId]);
                }
            } else if ($dataUsuario['rol'] == 3) { 
                $stmt = $this->db->prepare("INSERT INTO paciente (USUARIOS_ID_USUARIOS, EPS_ID_EPS) VALUES (?, ?)");
                $stmt->execute([$idUsuario, $epsId ?: 1]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}