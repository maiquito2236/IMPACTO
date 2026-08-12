<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;

class TratamientoModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Trae el listado completo de atenciones médicas
    public function listarHistoricoClinico() {
        $sql = "SELECT MAX(hc.ID_HISTORIA_CLINICA) as ID_HISTORIA_CLINICA, 
                       DATE_FORMAT(hc.FECHA_REGISTRO, '%d/%m/%Y %h:%i %p') as fecha,
                       hc.FECHA_REGISTRO as fecha_raw,
                       u.NUMERO_DOCUMENTO as documento,
                       CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente,
                       COALESCE(NULLIF((SELECT c.MOTIVO FROM cita c WHERE c.PACIENTE_ID_PACIENTE = hc.PACIENTE_ID_PACIENTE ORDER BY ABS(TIMESTAMPDIFF(SECOND, c.FECHA_HORA, hc.FECHA_REGISTRO)) ASC LIMIT 1), ''), MAX(hc.MOTIVO_CONSULTA)) as motivo,
                       MAX(hc.DIAGNOSTICO) as diagnostico,
                       GROUP_CONCAT(hc.DIAGNOSTICO SEPARATOR '||') as diagnosticos_raw,
                       GROUP_CONCAT(DISTINCT proc.NOMBRE_PROCEDIMIENTO SEPARATOR ', ') as tratamiento,
                       COALESCE(CONCAT(MAX(u_doc.NOMBRES), ' ', MAX(u_doc.APELLIDOS)), 'Sin asignar') as doctor,
                       hc.PACIENTE_ID_PACIENTE as id_paciente
                FROM historia_clinica hc
                INNER JOIN paciente p ON hc.PACIENTE_ID_PACIENTE = p.ID_PACIENTE
                INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                LEFT JOIN odontologo o ON hc.ODONTOLOGO_ID_ODONTOLOGO = o.ID_ODONTOLOGO
                LEFT JOIN usuarios u_doc ON o.USUARIOS_ID_USUARIOS = u_doc.ID_USUARIOS
                LEFT JOIN historia_clinica_has_procedimientos hcp ON hc.ID_HISTORIA_CLINICA = hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                LEFT JOIN procedimientos proc ON hcp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = proc.ID_PROCEDIMIENTO
                GROUP BY hc.PACIENTE_ID_PACIENTE, hc.FECHA_REGISTRO
                ORDER BY hc.FECHA_REGISTRO DESC";
                
        $resultados = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as &$row) {
            $dientes = [];
            if (!empty($row['diagnosticos_raw'])) {
                $partes = explode('||', $row['diagnosticos_raw']);
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
                $row['dientes_tratados'] = implode(', ', array_unique($dientes));
            } else {
                $row['dientes_tratados'] = 'General / No especificado';
            }
            
            // Limpiar el texto de diagnóstico para no mostrar "| Piezas: X |" en la interfaz
            if (!empty($row['diagnostico'])) {
                $clean_diag = preg_replace('/(Piezas?(:)?\s*[0-9,\s]+\s*\|\s*)/i', '', $row['diagnostico']);
                $row['diagnostico'] = trim($clean_diag, " |");
            }
        }
        
        return $resultados;
    }
}