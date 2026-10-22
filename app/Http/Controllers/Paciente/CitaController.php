<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Helpers\Notificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    /**
     * Actualiza automáticamente las citas en estado Pendiente (1)
     * cuya fecha_hora tenga más de 6 horas en el pasado al estado 'No asistió' (4).
     */
    public static function actualizarCitasVencidas()
    {
        try {
            DB::table('cita')
                ->where('ESTADO_CITA_ID', 1)
                ->whereRaw('FECHA_HORA < DATE_SUB(NOW(), INTERVAL 6 HOUR)')
                ->update([
                    'ESTADO_CITA_ID' => 4 // No asistió
                ]);
        } catch (\Exception $e) {
            // Silencioso en caso de bloqueo momentáneo
        }
    }

    /**
     * Muestra la lista de citas del paciente
     */
    public function index()
    {
        self::actualizarCitasVencidas();

        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPacienteTitular = $paciente ? $paciente->ID_PACIENTE : null;

        // Pacientes autorizados: el titular + menores a su cargo
        $pacientesIds = [];
        if ($idPacienteTitular) {
            $pacientesIds[] = $idPacienteTitular;
        }
        $hijosIds = DB::table('paciente_has_acudiente')
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->pluck('PACIENTE_ID_PACIENTE')
            ->toArray();
        $pacientesIds = array_unique(array_merge($pacientesIds, $hijosIds));

        $citas = [];
        if (!empty($pacientesIds)) {
            $placeholders = implode(',', array_fill(0, count($pacientesIds), '?'));
            $citas = DB::select("
                SELECT 
                    ID_CITA,
                    PACIENTE_ID_PACIENTE,
                    NOMBRE_PACIENTE,
                    ODONTOLOGO_ID_ODONTOLOGO,
                    FECHA_HORA,
                    MOTIVO,
                    ESTADO_CITA_ID,
                    ESTADO_CITA,
                    NOMBRE_ODONTOLOGO,
                    NOMBRE_ESPECIALIDAD,
                    NOMBRE_PROCEDIMIENTO,
                    NOMBRE_CONSULTORIO,
                    FECHA_CANCELACION,
                    MOTIVO_CANCELACION,
                    OBSERVACIONES_DOCTOR,
                    RECOMENDACIONES,
                    FECHA_ATENCION
                FROM vista_citas
                WHERE PACIENTE_ID_PACIENTE IN ($placeholders)
                ORDER BY FECHA_HORA DESC
            ", $pacientesIds);

            $citas = json_decode(json_encode($citas), true);
        }

        return view('paciente.citas.index', compact('citas'));
    }

    /**
     * Muestra la vista del formulario para pedir/agendar cita
     */
    public function pedirCita(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $usuario = DB::table('usuarios')->where('ID_USUARIOS', $idUsuario)->first();
        $pacienteTitular = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPacienteTitular = $pacienteTitular ? $pacienteTitular->ID_PACIENTE : null;

        // Lista de pacientes para los cuales puede agendar cita
        $pacientesDisponibles = [];
        if ($idPacienteTitular) {
            $pacientesDisponibles[] = [
                'id'     => $idPacienteTitular,
                'nombre' => $usuario ? ($usuario->NOMBRES . ' ' . $usuario->APELLIDOS) : 'Titular',
                'label'  => 'Para mí (' . ($usuario ? $usuario->NOMBRES : 'Titular') . ')',
                'tipo'   => 'Titular'
            ];
        }

        // Hijos / Menores vinculados en paciente_has_acudiente
        $hijos = DB::table('paciente_has_acudiente as pha')
            ->join('paciente as p', 'pha.PACIENTE_ID_PACIENTE', '=', 'p.ID_PACIENTE')
            ->join('usuarios as u', 'p.USUARIOS_ID_USUARIOS', '=', 'u.ID_USUARIOS')
            ->where('pha.USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->select('p.ID_PACIENTE', 'u.NOMBRES', 'u.APELLIDOS', 'pha.PARENTESCO', 'u.FECHA_NACIMIENTO')
            ->get();

        foreach ($hijos as $hijo) {
            $edad = $hijo->FECHA_NACIMIENTO ? (\Carbon\Carbon::parse($hijo->FECHA_NACIMIENTO)->age . ' años') : 'Menor';
            $pacientesDisponibles[] = [
                'id'     => $hijo->ID_PACIENTE,
                'nombre' => $hijo->NOMBRES . ' ' . $hijo->APELLIDOS,
                'label'  => "{$hijo->PARENTESCO}: {$hijo->NOMBRES} {$hijo->APELLIDOS} ($edad)",
                'tipo'   => $hijo->PARENTESCO
            ];
        }

        $pacienteSeleccionadoId = (int) $request->query('paciente_id', $idPacienteTitular);

        // 1. Procedimientos activos
        $procedimientos = DB::table('procedimientos')
            ->where('ESTADO', 'Activo')
            ->select('ID_PROCEDIMIENTO', 'NOMBRE_PROCEDIMIENTO', 'DESCRIPCION', 'COSTO', 'TIEMPO_ESTIMADO', 'ESPECIALIDAD_ID_ESPECIALIDAD')
            ->orderBy('NOMBRE_PROCEDIMIENTO', 'asc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        // 2. Horarios disponibles futuros
        $horarios = DB::select("
            SELECT 
                ID_HORARIO,
                DIA,
                HORA_INICIO,
                HORA_FIN,
                ESTADO_HORARIO AS ESTADO,
                ODONTOLOGO_ID_ODONTOLOGO,
                NOMBRE_ODONTOLOGO AS NOMBRE_DOCTOR,
                ID_ESPECIALIDAD,
                NOMBRE_ESPECIALIDAD
            FROM vista_horarios_disponibilidad
            WHERE ESTADO_HORARIO = 'Disponible'
              AND DIA >= CURDATE()
            ORDER BY DIA ASC, HORA_INICIO ASC
        ");

        $horarios = json_decode(json_encode($horarios), true);

        return view('paciente.citas.pedir', compact('procedimientos', 'horarios', 'pacientesDisponibles', 'pacienteSeleccionadoId'));
    }

    /**
     * Guarda la nueva cita agendada
     */
    public function guardarCita(Request $request)
    {
        $idProcedimiento = $request->input('procedimiento_id');
        $idHorario       = $request->input('horario_id');
        $fechaCita       = $request->input('fecha_cita');
        $horaCita        = $request->input('hora_cita');

        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPacienteDefault = $paciente ? $paciente->ID_PACIENTE : session('paciente_id');

        // Paciente destino: puede ser el titular o un menor vinculado
        $idPaciente = (int) $request->input('paciente_id', $idPacienteDefault);

        // Validar permisos de asignación
        $esValido = false;
        if ($idPaciente === $idPacienteDefault) {
            $esValido = true;
        } else {
            $esValido = DB::table('paciente_has_acudiente')
                ->where('PACIENTE_ID_PACIENTE', $idPaciente)
                ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
                ->exists();
        }

        if (!$idProcedimiento || !$idHorario || !$idPaciente || !$esValido) {
            return redirect()->route('paciente.citas.pedir')->with('mensaje_error', 'Por favor selecciona un paciente válido y completa todos los datos.');
        }

        $horarioInfo = DB::table('horario_disponibilidad')->where('ID_HORARIO', $idHorario)->first();

        if (!$horarioInfo) {
            return redirect()->route('paciente.citas.pedir')->with('mensaje_error', 'El horario seleccionado no existe.');
        }

        $idOdontologo = $horarioInfo->ODONTOLOGO_ID_ODONTOLOGO;
        if (empty($horaCita)) {
            $horaCita = $horarioInfo->HORA_INICIO;
        }

        $fechaLimpia = date('Y-m-d', strtotime(str_replace('/', '-', $fechaCita)));
        $fechaHoraFormato = $fechaLimpia . ' ' . $horaCita;

        $procedimiento = DB::table('procedimientos')->where('ID_PROCEDIMIENTO', $idProcedimiento)->first();
        $nombreProcedimiento = $procedimiento ? $procedimiento->NOMBRE_PROCEDIMIENTO : 'Cita odontológica';

        try {
            DB::beginTransaction();

            $idCitaNueva = DB::table('cita')->insertGetId([
                'PACIENTE_ID_PACIENTE'              => $idPaciente,
                'ODONTOLOGO_ID_ODONTOLOGO'          => $idOdontologo,
                'FECHA_HORA'                        => $fechaHoraFormato,
                'ESTADO_CITA_ID'                    => 1,
                'MOTIVO'                            => $nombreProcedimiento,
                'HORARIO_DISPONIBILIDAD_ID_HORARIO' => $idHorario,
                'FECHA_CREACION'                    => now()
            ]);

            DB::table('cita_has_procedimiento')->insert([
                'CITA_ID_CITA'                      => $idCitaNueva,
                'PROCEDIMIENTO_ID_PROCEDIMIENTO'    => $idProcedimiento
            ]);

            DB::table('horario_disponibilidad')
                ->where('ID_HORARIO', $idHorario)
                ->update(['ESTADO' => 'Ocupado']);

            DB::commit();

            // Mensaje de notificación según si es para el titular o para su hijo
            $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($fechaHoraFormato));
            if ($idPaciente === $idPacienteDefault) {
                $msgPaciente = "Tu cita médica ha sido agendada para el $fecha_fmt.";
                $msgPersonal = "El paciente " . (session('usuario_nome') ?? 'Titular') . " ha agendado una cita médica para el $fecha_fmt. Procedimiento: $nombreProcedimiento";
            } else {
                $menor = DB::table('paciente as p')
                    ->join('usuarios as u', 'p.USUARIOS_ID_USUARIOS', '=', 'u.ID_USUARIOS')
                    ->where('p.ID_PACIENTE', $idPaciente)
                    ->first();
                $nombreMenor = $menor ? ($menor->NOMBRES . ' ' . $menor->APELLIDOS) : 'menor a cargo';
                $msgPaciente = "Has agendado la cita de $nombreMenor para el $fecha_fmt.";
                $msgPersonal = "El acudiente " . (session('usuario_nome') ?? 'Paciente') . " agendó cita para el menor $nombreMenor para el $fecha_fmt. Procedimiento: $nombreProcedimiento";
            }

            Notificador::enviarAUsuario($idUsuario, 1, $msgPaciente);
            Notificador::enviarAOdontologo($idOdontologo, 21, $msgPersonal);
            Notificador::enviarAAdmin(22, $msgPersonal);

            return redirect()->route('paciente.citas.index')->with('mensaje_success', 'Cita agendada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('paciente.citas.pedir')->with('mensaje_error', 'Error al guardar la cita: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Devuelve horas disponibles para una fecha
     */
    public function horasDisponiblesAjax(Request $request)
    {
        $fechaRecibida = $request->input('fecha');
        $fechaMysql = date('Y-m-d', strtotime(str_replace('/', '-', $fechaRecibida)));

        $horarios = DB::select("
            SELECT 
                ID_HORARIO,
                DIA,
                HORA_INICIO,
                HORA_FIN,
                ODONTOLOGO_ID_ODONTOLOGO,
                NOMBRE_ODONTOLOGO as NOMBRE_DOCTOR
            FROM vista_horarios_disponibilidad
            WHERE (DIA = ? OR FECHA = ?) AND ESTADO_HORARIO = 'Disponible'
            ORDER BY HORA_INICIO ASC
        ", [$fechaMysql, $fechaMysql]);

        return response()->json([
            'success' => true,
            'data'    => $horarios
        ]);
    }

    /**
     * AJAX: Devuelve el detalle de una cita con bitácora
     */
    public function detalleAjax(Request $request)
    {
        self::actualizarCitasVencidas();

        $idCita = intval($request->input('id_cita'));

        $detalle = DB::selectOne("
            SELECT 
                ID_CITA,
                HORARIO_DISPONIBILIDAD_ID_HORARIO,
                FECHA_HORA,
                MOTIVO,
                ESTADO_CITA_ID,
                ESTADO_CITA,
                FECHA_CREACION,
                FECHA_CANCELACION,
                MOTIVO_CANCELACION,
                OBSERVACIONES_DOCTOR,
                RECOMENDACIONES,
                FECHA_ATENCION,
                ODONTOLOGO_ID_ODONTOLOGO,
                NOMBRE_ODONTOLOGO,
                NOMBRE_ESPECIALIDAD,
                NOMBRE_CONSULTORIO,
                NOMBRE_PROCEDIMIENTO
            FROM vista_citas
            WHERE ID_CITA = ?
        ", [$idCita]);

        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'No se encontraron detalles de la cita.']);
        }

        $res = (array) $detalle;
        $res['CREADA_EL']          = $res['FECHA_CREACION'] ?? null;
        $res['NOMBRE_DOCTOR']      = $res['NOMBRE_ODONTOLOGO'] ?? '';
        $res['DOCTOR_NOMBRE']      = $res['NOMBRE_ODONTOLOGO'] ?? '';
        $res['ID_ODONTOLOGO']      = $res['ODONTOLOGO_ID_ODONTOLOGO'] ?? null;
        $res['ESPECIALIDAD']       = !empty($res['NOMBRE_ESPECIALIDAD']) ? $res['NOMBRE_ESPECIALIDAD'] : 'General';
        $res['CONSULTORIO']        = $res['NOMBRE_CONSULTORIO'] ?? '';
        $res['CONSULTORIO_NOMBRE'] = $res['NOMBRE_CONSULTORIO'] ?? '';
        $res['ESTADO']             = $res['ESTADO_CITA'] ?? 'Pendiente';
        $res['NOMBRE_ESTADO']      = $res['ESTADO_CITA'] ?? 'Pendiente';
        $res['LISTA_TRATAMIENTOS'] = !empty($res['NOMBRE_PROCEDIMIENTO']) ? $res['NOMBRE_PROCEDIMIENTO'] : (!empty($res['MOTIVO']) ? $res['MOTIVO'] : 'No se registraron procedimientos');

        if (!empty($res['FECHA_HORA'])) {
            $ts = strtotime($res['FECHA_HORA']);
            $res['SOLO_FECHA'] = date('d/m/Y', $ts);
            $res['SOLO_HORA']  = date('h:i A', $ts);
        }
        if (!empty($res['CREADA_EL'])) {
            $res['CREADA_EL_FORMATO'] = date('d/m/Y - h:i A', strtotime($res['CREADA_EL']));
        }
        if (!empty($res['FECHA_CANCELACION'])) {
            $res['FECHA_CANCELACION_FORMATO'] = date('d/m/Y - h:i A', strtotime($res['FECHA_CANCELACION']));
        }
        if (!empty($res['FECHA_ATENCION'])) {
            $res['FECHA_ATENCION_FORMATO'] = date('d/m/Y - h:i A', strtotime($res['FECHA_ATENCION']));
        }

        $res['DIENTES_TRATADOS'] = 'General / No especificado';
        try {
            $dientes = DB::table('historia_clinica as hc')
                ->join('historia_clinica_has_procedimientos as hcp', 'hc.ID_HISTORIA_CLINICA', '=', 'hcp.HISTORIA_CLINICA_ID_HISTORIA_CLINICA')
                ->where('hc.CITA_ID_CITA', $idCita)
                ->whereNotNull('hcp.DIENTE')
                ->pluck('hcp.DIENTE')
                ->toArray();
            if (!empty($dientes)) {
                $res['DIENTES_TRATADOS'] = 'Pieza(s): ' . implode(', ', array_unique($dientes));
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        // Historial / Bitácora de reprogramaciones
        $historialRaw = [];
        try {
            $historialRaw = DB::select("
                SELECT * FROM historial_reprogramacion_citas
                WHERE CITA_ID_CITA = ?
                ORDER BY CREADO_EL DESC
            ", [$idCita]);
        } catch (\Exception $e) {
            $historialRaw = [];
        }

        $historialFormateado = [];
        foreach ($historialRaw as $hist) {
            $h = (array) $hist;
            $h['ACCION']             = $h['ACCION'] ?? 'REPROGRAMACION';
            $h['FECHA_ANTERIOR_FMT'] = !empty($h['FECHA_HORA_ANTERIOR']) ? date('d/m/Y - h:i A', strtotime($h['FECHA_HORA_ANTERIOR'])) : '--';
            $h['FECHA_NUEVA_FMT']    = !empty($h['FECHA_HORA_NUEVA']) ? date('d/m/Y - h:i A', strtotime($h['FECHA_HORA_NUEVA'])) : '--';
            $h['REGISTRO_FMT']       = !empty($h['CREADO_EL']) ? date('d/m/Y - h:i A', strtotime($h['CREADO_EL'])) : '--';
            $h['MOTIVO']             = $h['MOTIVO_CAMBIO'] ?? ($h['MOTIVO'] ?? 'Reprogramación solicitada');
            $historialFormateado[]   = $h;
        }
        $res['HISTORIAL'] = $historialFormateado;

        return response()->json(['success' => true, 'data' => $res]);
    }

    /**
     * Cancela una cita
     */
    public function cancelarCita(Request $request)
    {
        $idCita = intval($request->input('id_cita'));
        $motivo = $request->input('motivo_cancelacion');
        $observaciones = trim($request->input('observaciones_paciente') ?? '');

        if (!$idCita || !$motivo) {
            return redirect()->route('paciente.citas.index')->with('mensaje_error', 'Datos insuficientes para cancelar la cita.');
        }

        $motivoFinal = !empty($observaciones) ? "$motivo - Observaciones: $observaciones" : $motivo;

        $cita = DB::table('cita')->where('ID_CITA', $idCita)->first();
        if (!$cita) {
            return redirect()->route('paciente.citas.index')->with('mensaje_error', 'La cita no existe.');
        }

        // Liberar horario si existía
        if ($cita->HORARIO_DISPONIBILIDAD_ID_HORARIO) {
            DB::table('horario_disponibilidad')
                ->where('ID_HORARIO', $cita->HORARIO_DISPONIBILIDAD_ID_HORARIO)
                ->update(['ESTADO' => 'Disponible']);
        }

        DB::table('cita')->where('ID_CITA', $idCita)->update([
            'ESTADO_CITA_ID'     => 3, // Cancelada
            'MOTIVO_CANCELACION' => $motivoFinal,
            'FECHA_CANCELACION'  => now()
        ]);

        // Notificaciones
        $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($cita->FECHA_HORA));
        $msgPaciente = "Tu cita médica del $fecha_fmt ha sido cancelada. Motivo: $motivoFinal";
        $msgPersonal = "El paciente ha cancelado su cita del $fecha_fmt. Motivo: $motivoFinal";

        $idUsuario = Auth::id() ?? session('usuario_id');
        Notificador::enviarAUsuario($idUsuario, 7, $msgPaciente);
        if ($cita->ODONTOLOGO_ID_ODONTOLOGO) {
            Notificador::enviarAOdontologo($cita->ODONTOLOGO_ID_ODONTOLOGO, 8, $msgPersonal);
        }
        Notificador::enviarAAdmin(9, $msgPersonal);

        return redirect()->route('paciente.citas.index')->with('mensaje_success', 'La cita fue cancelada correctamente.');
    }

    /**
     * Reprograma una cita
     */
    public function reprogramarCita(Request $request)
    {
        $idCita = intval($request->input('id_cita'));
        $idHorarioNuevo = intval($request->input('nuevo_id_horario'));
        $nuevaFecha = $request->input('nueva_fecha');
        $motivo = trim($request->input('motivo') ?? '');

        if (!$idCita || !$idHorarioNuevo || !$nuevaFecha) {
            return redirect()->route('paciente.citas.index')->with('mensaje_error', 'Datos insuficientes para reprogramar la cita.');
        }

        $horarioInfo = DB::table('horario_disponibilidad')->where('ID_HORARIO', $idHorarioNuevo)->first();
        if (!$horarioInfo) {
            return redirect()->route('paciente.citas.index')->with('mensaje_error', 'Horario no válido.');
        }

        $citaOriginal = DB::table('cita')->where('ID_CITA', $idCita)->first();
        if (!$citaOriginal) {
            return redirect()->route('paciente.citas.index')->with('mensaje_error', 'La cita original no existe.');
        }

        $nuevaFechaHora = $nuevaFecha . ' ' . $horarioInfo->HORA_INICIO;
        $nuevoDoctor = $horarioInfo->ODONTOLOGO_ID_ODONTOLOGO;
        $motivoFinal = !empty($motivo) ? $motivo : 'Reprogramación solicitada por el paciente.';

        try {
            DB::beginTransaction();

            // Liberar horario anterior
            if ($citaOriginal->HORARIO_DISPONIBILIDAD_ID_HORARIO) {
                DB::table('horario_disponibilidad')
                    ->where('ID_HORARIO', $citaOriginal->HORARIO_DISPONIBILIDAD_ID_HORARIO)
                    ->update(['ESTADO' => 'Disponible']);
            }

            // Ocupar nuevo horario
            DB::table('horario_disponibilidad')
                ->where('ID_HORARIO', $idHorarioNuevo)
                ->update(['ESTADO' => 'Ocupado']);

            // Actualizar cita
            DB::table('cita')->where('ID_CITA', $idCita)->update([
                'HORARIO_DISPONIBILIDAD_ID_HORARIO' => $idHorarioNuevo,
                'FECHA_HORA'                        => $nuevaFechaHora,
                'ODONTOLOGO_ID_ODONTOLOGO'          => $nuevoDoctor,
                'ESTADO_CITA_ID'                    => 1 // Pendiente
            ]);

            // Registrar en historial_reprogramacion_citas si la tabla existe
            try {
                DB::table('historial_reprogramacion_citas')->insert([
                    'CITA_ID_CITA'         => $idCita,
                    'FECHA_HORA_ANTERIOR'  => $citaOriginal->FECHA_HORA,
                    'FECHA_HORA_NUEVA'     => $nuevaFechaHora,
                    'MOTIVO_CAMBIO'        => $motivoFinal,
                    'CREADO_EL'            => now()
                ]);
            } catch (\Exception $exHist) {
                // Silencioso si no está la tabla de historial
            }

            DB::commit();

            // Notificaciones
            $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($nuevaFechaHora));
            $msgPaciente = "Tu cita médica ha sido reprogramada para el $fecha_fmt.";
            $msgPersonal = "El paciente ha reprogramado su cita para el $fecha_fmt.";

            $idUsuario = Auth::id() ?? session('usuario_id');
            Notificador::enviarAUsuario($idUsuario, 13, $msgPaciente);
            Notificador::enviarAOdontologo($nuevoDoctor, 14, $msgPersonal);
            Notificador::enviarAAdmin(15, $msgPersonal);

            return redirect()->route('paciente.citas.index')->with('mensaje_success', 'Cita reprogramada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('paciente.citas.index')->with('mensaje_error', 'Error al reprogramar: ' . $e->getMessage());
        }
    }
}