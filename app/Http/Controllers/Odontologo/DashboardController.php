<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarioId = Auth::id() ?? 2;
        $odontologo = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $usuarioId)->first()
                   ?? DB::table('odontologo')->first();
        $idOdontologo = $odontologo ? $odontologo->ID_ODONTOLOGO : 1;

        $docUser = DB::table('usuarios')->where('ID_USUARIOS', $odontologo?->USUARIOS_ID_USUARIOS)->first();
        $doctorNombre = $docUser ? ($docUser->NOMBRES . ' ' . $docUser->APELLIDOS) : 'Odontólogo';

        $hoy = date('Y-m-d');

        // 1. Citas de Hoy (Pendientes)
        $citasHoy = (int) DB::table('cita')
            ->whereDate('FECHA_HORA', $hoy)
            ->where('ESTADO_CITA_ID', 1)
            ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
            ->count();

        // 2. Próxima Cita
        $proximaCitaRow = DB::selectOne("
            SELECT TIME_FORMAT(FECHA_HORA, '%h:%i %p') AS hora 
            FROM cita 
            WHERE DATE(FECHA_HORA) = ? AND FECHA_HORA >= NOW() AND ESTADO_CITA_ID = 1 AND ODONTOLOGO_ID_ODONTOLOGO = ?
            ORDER BY FECHA_HORA ASC LIMIT 1
        ", [$hoy, $idOdontologo]);
        $proximaCita = $proximaCitaRow ? $proximaCitaRow->hora : 'No hay más citas';

        // 3. Pacientes Activos
        $pacientesActivosRes = DB::selectOne("
            SELECT COUNT(DISTINCT p.ID_PACIENTE) AS total 
            FROM paciente p
            INNER JOIN usuarios u ON p.USUARIOS_ID_USUARIOS = u.ID_USUARIOS
            WHERE u.ESTADO = 'Activo' 
            AND (
                p.ID_PACIENTE IN (SELECT PACIENTE_ID_PACIENTE FROM cita WHERE ODONTOLOGO_ID_ODONTOLOGO = ? AND ESTADO_CITA_ID IN (1, 2))
                OR 
                p.ID_PACIENTE IN (SELECT PACIENTE_ID_PACIENTE FROM historia_clinica WHERE ODONTOLOGO_ID_ODONTOLOGO = ?)
            )
        ", [$idOdontologo, $idOdontologo]);
        $pacientesActivos = $pacientesActivosRes ? (int)$pacientesActivosRes->total : 0;

        // 4. Tratamientos Registrados (Historias Clínicas)
        $tratamientosActivos = (int) DB::table('historia_clinica')
            ->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)
            ->count();

        // 5. Agenda de Hoy (usando vista_citas)
        $agendaHoyRaw = DB::select("
            SELECT ID_CITA, TIME_FORMAT(FECHA_HORA, '%h:%i %p') AS hora, MOTIVO, ESTADO_CITA AS ESTADO, 
                   NOMBRE_PACIENTE AS paciente_nombre
            FROM vista_citas
            WHERE DATE(FECHA_HORA) = ? AND ODONTOLOGO_ID_ODONTOLOGO = ?
            ORDER BY FECHA_HORA ASC
        ", [$hoy, $idOdontologo]);
        $agendaHoy = array_map(fn($item) => (array)$item, $agendaHoyRaw);

        // 6. Pacientes Recientes (usando vista_procedimientos_hc)
        $pacientesRecientesRaw = DB::select("
            SELECT ID_PACIENTE, NOMBRE_PACIENTE AS paciente_nombre, TELEFONO_PACIENTE AS TELEFONO, FECHA_NACIMIENTO, 
                   GROUP_CONCAT(DISTINCT NOMBRE_PROCEDIMIENTO SEPARATOR ', ') AS MOTIVO_CONSULTA, 
                   DATE_FORMAT(MAX(FECHA_REGISTRO), '%d %b, %Y') AS fecha
            FROM vista_procedimientos_hc
            WHERE ODONTOLOGO_ID_ODONTOLOGO = ?
            GROUP BY ID_PACIENTE, NOMBRE_PACIENTE, TELEFONO_PACIENTE, FECHA_NACIMIENTO, DATE_FORMAT(FECHA_REGISTRO, '%Y-%m-%d')
            ORDER BY MAX(FECHA_REGISTRO) DESC LIMIT 2
        ", [$idOdontologo]);
        $pacientesRecientes = array_map(fn($item) => (array)$item, $pacientesRecientesRaw);

        // 7. Resumen de Tratamientos Dinámico (usando vista_procedimientos_hc)
        $resTotal = DB::selectOne("
            SELECT COUNT(*) AS total 
            FROM vista_procedimientos_hc
            WHERE ODONTOLOGO_ID_ODONTOLOGO = ?
        ", [$idOdontologo]);
        $totalProcedimientos = ($resTotal && $resTotal->total > 0) ? (int)$resTotal->total : 1;

        $resultProc = DB::select("
            SELECT NOMBRE_PROCEDIMIENTO, COUNT(PROCEDIMIENTOS_ID_PROCEDIMIENTO) AS cantidad 
            FROM vista_procedimientos_hc
            WHERE NOMBRE_PROCEDIMIENTO IS NOT NULL AND NOMBRE_PROCEDIMIENTO != '' AND ODONTOLOGO_ID_ODONTOLOGO = ?
            GROUP BY PROCEDIMIENTOS_ID_PROCEDIMIENTO, NOMBRE_PROCEDIMIENTO 
            ORDER BY cantidad DESC
        ", [$idOdontologo]);

        $tratamientosDinamicos = [];
        foreach ($resultProc as $row) {
            $porcentaje = round(($row->cantidad / $totalProcedimientos) * 100);
            if ($porcentaje > 0) {
                $tratamientosDinamicos[] = [
                    'nombre' => $row->NOMBRE_PROCEDIMIENTO,
                    'porcentaje' => $porcentaje
                ];
            }
        }

        // 8. Alertas y Recordatorios (usando vista_notificaciones)
        $alertasRaw = DB::select("
            SELECT ID_NOTIFICACIONES, MENSAJE, FECHA_ENVIO, ESTADO, TIPO_NOTIFICACION AS TIPO_NOMBRE
            FROM vista_notificaciones
            WHERE ID_USUARIOS = ?
            ORDER BY FECHA_ENVIO DESC LIMIT 4
        ", [$docUser?->ID_USUARIOS ?? $usuarioId]);
        $alertas = array_map(fn($item) => (array)$item, $alertasRaw);

        $cleanUtf8 = function ($item) use (&$cleanUtf8) {
            if (is_array($item)) {
                return array_map($cleanUtf8, $item);
            }
            if (is_string($item) && strpos($item, 'Ã') !== false) {
                return mb_convert_encoding($item, 'ISO-8859-1', 'UTF-8');
            }
            return $item;
        };

        $agendaHoy = array_map($cleanUtf8, $agendaHoy);
        $pacientesRecientes = array_map($cleanUtf8, $pacientesRecientes);
        $tratamientosDinamicos = array_map($cleanUtf8, $tratamientosDinamicos);
        $alertas = array_map($cleanUtf8, $alertas);
        $doctorNombre = $cleanUtf8($doctorNombre);

        return view('odontologo.dashboard', compact(
            'citasHoy',
            'proximaCita',
            'pacientesActivos',
            'tratamientosActivos',
            'agendaHoy',
            'pacientesRecientes',
            'tratamientosDinamicos',
            'alertas',
            'doctorNombre'
        ));
    }
}
