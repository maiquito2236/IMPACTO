<?php
namespace App\Models\odontologo;

use App\Config\Database;
use PDO;
use Exception;

class HistoriaClinicaModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerOdontogramaPaciente($pacienteId, $targetDate = null) {
      try {
          $sql = "SELECT ot.PIEZA_DENTAL, p.NOMBRE_PROCEDIMIENTO as tratamiento, ot.ESTADO as estado, 
                         DATE_FORMAT(ot.FECHA_REGISTRO, '%d %b %Y') as fecha, 
                         ot.NOTAS as notas,
                         COALESCE(CONCAT(u.NOMBRES, ' ', u.APELLIDOS), 'Sin asignar') as doctor";

          if ($targetDate) {
              $sql .= ", IF(DATE(ot.FECHA_REGISTRO) = :targetDate, 1, 0) as es_nuevo";
          } else {
              $sql .= ", 1 as es_nuevo";
          }

          $sql .= " FROM odontograma_tratamientos ot
                  INNER JOIN procedimientos p ON ot.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                  LEFT JOIN odontologo od ON ot.ODONTOLOGO_ID_ODONTOLOGO = od.ID_ODONTOLOGO
                  LEFT JOIN usuarios u ON od.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                  WHERE ot.PACIENTE_ID_PACIENTE = :pacienteId";

          if ($targetDate) {
              $sql .= " AND DATE(ot.FECHA_REGISTRO) <= :targetDate2";
          }
          
          $stmt = $this->db->prepare($sql);
          $stmt->bindValue(':pacienteId', $pacienteId, PDO::PARAM_INT);
          
          if ($targetDate) {
              $stmt->bindValue(':targetDate', $targetDate, PDO::PARAM_STR);
              $stmt->bindValue(':targetDate2', $targetDate, PDO::PARAM_STR);
          }

          $stmt->execute();
          return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
      } catch (Exception $e) {
          return [];
      }
  }

    public function obtenerDatosDemograficosPaciente($pacienteId) {
        try {
            $sql = "SELECT 
                        pac.ID_PACIENTE,
                        u.NUMERO_DOCUMENTO AS documento,
                        u.CORREO AS correo,
                        CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS paciente_nombre
                    FROM paciente pac
                    INNER JOIN usuarios u ON pac.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
                    WHERE pac.ID_PACIENTE = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pacienteId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: ['paciente_nombre' => 'Paciente Sin Nombre', 'documento' => 'S/N'];
        } catch (Exception $e) {
            return ['paciente_nombre' => 'Error al cargar nombre', 'documento' => 'S/N'];
        }
    }

    public function obtenerAlertasPaciente($pacienteId) {
        try {
            // Consultamos las condiciones reales del paciente
            $sql = "SELECT cm.TIPO, cm.NOMBRE_CONDICION 
                    FROM paciente_has_condicion_medica phc
                    JOIN condicion_medica cm ON phc.CONDICION_MEDICA_ID_CONDICION_MEDICA = cm.ID_CONDICION_MEDICA
                    WHERE phc.PACIENTE_ID_PACIENTE = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pacienteId]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $alergias = [];
            $enfermedades = [];

            foreach ($resultados as $row) {
                if ($row['TIPO'] === 'ALERGIA') {
                    $alergias[] = $row['NOMBRE_CONDICION'];
                } elseif ($row['TIPO'] === 'ENFERMEDAD') {
                    $enfermedades[] = $row['NOMBRE_CONDICION'];
                }
            }

            return [
                'ALERGIAS' => !empty($alergias) ? implode(', ', $alergias) : 'Ninguna',
                'ENFERMEDADES' => !empty($enfermedades) ? implode(', ', $enfermedades) : 'Ninguna',
                'MEDICAMENTOS' => 'Ninguno' // Lo dejamos por defecto hasta que tengas tabla de medicamentos
            ];
        } catch (Exception $e) {
            return ['ALERGIAS' => 'Ninguna', 'ENFERMEDADES' => 'Ninguna', 'MEDICAMENTOS' => 'Ninguno'];
        }
    }

    public function obtenerResumenFinanciero($pacienteId) {
        try {
            $sql = "SELECT COALESCE(SUM(p.COSTO), 0) as total_treatment
                    FROM odontograma_tratamientos ot
                    INNER JOIN procedimientos p ON ot.PROCEDIMIENTO_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                    WHERE ot.PACIENTE_ID_PACIENTE = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pacienteId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalTratamiento = $res ? floatval($res['total_treatment']) : 0;

            $sqlFactura = "SELECT COALESCE(SUM(TOTAL), 0) as total_pagado FROM factura WHERE HISTORIA_CLINICA_ID_HISTORIA_CLINICA IN (SELECT ID_HISTORIA_CLINICA FROM historia_clinica WHERE PACIENTE_ID_PACIENTE = ?)";
            $stmtFactura = $this->db->prepare($sqlFactura);
            $stmtFactura->execute([$pacienteId]);
            $resFactura = $stmtFactura->fetch(PDO::FETCH_ASSOC);
            
            $totalPagado = $resFactura ? floatval($resFactura['total_pagado']) : 0;
            $saldoPendiente = $totalTratamiento - $totalPagado;

            return ['total_treatment' => $totalTratamiento, 'total_pagado' => $totalPagado, 'saldo_pendiente' => max(0, $saldoPendiente)];
        } catch (Exception $e) {
            return ['total_treatment' => 0, 'total_pagado' => 0, 'saldo_pendiente' => 0];
        }
    }

    public function obtenerControlCitas($pacienteId) {
        try {
            $sql = "SELECT MOTIVO as sesion_hoy, DATE_FORMAT(FECHA_HORA, '%d de %b, %Y') as proxima_cita
                    FROM cita WHERE PACIENTE_ID_PACIENTE = ? ORDER BY FECHA_HORA DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pacienteId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: ['sesion_hoy' => 'Sin citas', 'proxima_cita' => 'No programada'];
        } catch (Exception $e) {
            return ['sesion_hoy' => 'Sin citas', 'proxima_cita' => 'No programada'];
        }
    }

    public function obtenerEvolucionesTimeline($pacienteId, $targetDate = null) {
          try {
              // Unimos con procedimientos para filtrar SOLO por 'EVOLUCION_FASES'
              $sql = "SELECT TIME_FORMAT(hc.FECHA_REGISTRO, '%h:%i %p') as hora,
                             DATE_FORMAT(hc.FECHA_REGISTRO, '%d %b %Y') as fecha,
                             DATE_FORMAT(hc.FECHA_REGISTRO, '%Y-%m-%d') as fecha_iso,
                             hc.MOTIVO_CONSULTA as motivo,
                             hc.DIAGNOSTICO as diagnostico,
                             hc.TRATAMIENTO as piezas,
                             hc.OBSERVACIONES as observaciones,
                             p.NOMBRE_PROCEDIMIENTO
                      FROM historia_clinica hc
                      INNER JOIN historia_clinica_has_procedimientos hchp 
                          ON hc.ID_HISTORIA_CLINICA = hchp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                      INNER JOIN procedimientos p 
                          ON hchp.PROCEDIMIENTOS_ID_PROCEDIMIENTO = p.ID_PROCEDIMIENTO
                      WHERE hc.PACIENTE_ID_PACIENTE = :pacienteId AND p.TIPO_SEGUIMIENTO = 'EVOLUCION_FASES'";
              
              if ($targetDate) {
                  $sql .= " AND DATE(hc.FECHA_REGISTRO) <= :targetDate";
              }
              $sql .= " ORDER BY hc.FECHA_REGISTRO DESC";

              $stmt = $this->db->prepare($sql);
              $stmt->bindValue(':pacienteId', $pacienteId, PDO::PARAM_INT);
              if ($targetDate) {
                  $stmt->bindValue(':targetDate', $targetDate, PDO::PARAM_STR);
              }
              $stmt->execute();
              return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
          } catch (Exception $e) {
              return [];
          }
      }

    public function completarCitaYTratamientos($citaId, $pacienteId) {
        try {
            $this->db->beginTransaction();

            // 1. Obtener estado anterior de la cita
            $sqlEstado = "SELECT ESTADO_CITA_ID FROM cita WHERE ID_CITA = ?";
            $stmtEstado = $this->db->prepare($sqlEstado);
            $stmtEstado->execute([$citaId]);
            $cita = $stmtEstado->fetch(PDO::FETCH_ASSOC);
            $estadoAnterior = $cita ? $cita['ESTADO_CITA_ID'] : 1;

            // 2. Buscar historias clínicas de este paciente que no tengan factura asociada
            $sqlHcUninvoiced = "
                SELECT hc.ID_HISTORIA_CLINICA 
                FROM historia_clinica hc
                LEFT JOIN factura f ON hc.ID_HISTORIA_CLINICA = f.HISTORIA_CLINICA_ID_HISTORIA_CLINICA
                WHERE hc.PACIENTE_ID_PACIENTE = ? AND f.ID_FACTURA IS NULL
            ";
            $stmtHc = $this->db->prepare($sqlHcUninvoiced);
            $stmtHc->execute([$pacienteId]);
            $hcRecords = $stmtHc->fetchAll(PDO::FETCH_ASSOC);

            foreach ($hcRecords as $hc) {
                $hcId = $hc['ID_HISTORIA_CLINICA'];

                // Calcular el total de los procedimientos de esta historia clínica
                $sqlTotal = "
                    SELECT COALESCE(SUM(PRECIO_APLICADO * CANTIDAD), 0) AS total_hc
                    FROM historia_clinica_has_procedimientos
                    WHERE HISTORIA_CLINICA_ID_HISTORIA_CLINICA = ?
                ";
                $stmtTotal = $this->db->prepare($sqlTotal);
                $stmtTotal->execute([$hcId]);
                $total = (float)$stmtTotal->fetchColumn();

                // Registrar automáticamente la factura asociada a la historia clínica y a la cita
                $sqlInsertFactura = "
                    INSERT INTO factura (HISTORIA_CLINICA_ID_HISTORIA_CLINICA, CITA_ID_CITA, FECHA_EMISION, TOTAL, ESTADO)
                    VALUES (?, ?, NOW(), ?, 'EMITIDA')
                ";
                $stmtInsert = $this->db->prepare($sqlInsertFactura);
                $stmtInsert->execute([$hcId, $citaId ?: 0, $total]);
            }

            // 3. Extraer las notas del último historial clínico para actualizar la cita
            $observacionesCita = '';
            if (!empty($hcRecords)) {
                $latestHcId = $hcRecords[count($hcRecords) - 1]['ID_HISTORIA_CLINICA'];
                $sqlLatestNotes = "SELECT DIAGNOSTICO, OBSERVACIONES FROM historia_clinica WHERE ID_HISTORIA_CLINICA = ?";
                $stmtLatestNotes = $this->db->prepare($sqlLatestNotes);
                $stmtLatestNotes->execute([$latestHcId]);
                $latestNotes = $stmtLatestNotes->fetch(PDO::FETCH_ASSOC);
                if ($latestNotes) {
                    $observacionesCita = !empty($latestNotes['OBSERVACIONES']) ? $latestNotes['OBSERVACIONES'] : $latestNotes['DIAGNOSTICO'];
                }
            }

            if ($estadoAnterior != 2) {
                // 4. Actualizar Cita a estado 2 (Completada), asentar observaciones y fecha de atención
                $sqlUpdateCita = "UPDATE cita SET ESTADO_CITA_ID = 2, FECHA_ATENCION = NOW(), OBSERVACIONES_DOCTOR = ? WHERE ID_CITA = ?";
                $stmtUpdateCita = $this->db->prepare($sqlUpdateCita);
                $stmtUpdateCita->execute([$observacionesCita, $citaId]);

                // 5. Registrar el cambio en historial_cita con fecha y hora actual
                $sqlHistorial = "INSERT INTO historial_cita (CITA_ID_CITA, ESTADO_CITA_ID, ACCION, MOTIVO) 
                                 VALUES (?, 2, 'Completada', 'Cita completada desde módulo de Historial Clínico y Factura Generada')";
                $stmtHistorial = $this->db->prepare($sqlHistorial);
                $stmtHistorial->execute([$citaId]);
            } else if (!empty($observacionesCita)) {
                // Si la cita ya estaba completada pero agregamos evolución, actualizamos observaciones
                $sqlUpdateCitaObs = "UPDATE cita SET OBSERVACIONES_DOCTOR = ? WHERE ID_CITA = ?";
                $stmtUpdateCitaObs = $this->db->prepare($sqlUpdateCitaObs);
                $stmtUpdateCitaObs->execute([$observacionesCita, $citaId]);
            }

            // 6. Actualizar todos los tratamientos del odontograma de este paciente a 'HECHO'
            $sqlUpdateOdonto = "UPDATE odontograma_tratamientos SET ESTADO = 'HECHO' 
                                WHERE PACIENTE_ID_PACIENTE = ? AND ESTADO != 'HECHO'";
            $stmtUpdateOdonto = $this->db->prepare($sqlUpdateOdonto);
            $stmtUpdateOdonto->execute([$pacienteId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}