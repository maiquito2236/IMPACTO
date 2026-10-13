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
     * Muestra la lista de citas del paciente
     */
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        $citas = [];
        if ($idPaciente) {
            $citas = DB::select("
                SELECT 
                    ID_CITA,
                    FECHA_HORA,
                    MOTIVO,
                    ESTADO_CITA_ID,
                    ESTADO_CITA AS NOMBRE_ESTADO,
                    NOMBRE_ODONTOLOGO AS DOCTOR_NOMBRE,
                    NOMBRE_ESPECIALIDAD,
                    FECHA_CANCELACION,
                    MOTIVO_CANCELACION,
                    OBSERVACIONES_DOCTOR,
                    RECOMENDACIONES,
                    FECHA_ATENCION
                FROM vista_citas
                WHERE PACIENTE_ID_PACIENTE = ?
                ORDER BY FECHA_HORA DESC
            ", [$idPaciente]);

            $citas = json_decode(json_encode($citas), true);
        }

        return view('paciente.citas.index', compact('citas'));
    }

    /**
     * Muestra la vista del formulario para pedir/agendar cita
     */
    public function pedirCita()
    {
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

        return view('paciente.citas.pedir', compact('procedimientos', 'horarios'));
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
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : session('paciente_id');

        if (!$idProcedimiento || !$idHorario || !$idPaciente) {
            return redirect()->route('paciente.citas.pedir')->with('mensaje_error', 'Por favor completa todos los datos.');
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

            // Notificaciones
            $fecha_fmt = date('d/m/Y \a \l\a\s h:i A', strtotime($fechaHoraFormato));
            $msgPaciente = "Tu cita médica ha sido agendada para el $fecha_fmt.";
            $msgPersonal = "El paciente ha agendado una nueva cita médica para el $fecha_fmt. Procedimiento: $nombreProcedimiento";

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
        $idCita = intval($request->input('id_cita'));

        $detalle = DB::selectOne("
            SELECT 
                ID_CITA,
                HORARIO_DISPONIBILIDAD_ID_HORARIO,
                FECHA_HORA,
                MOTIVO,
                ESTADO_CITA_ID,
                ESTADO_CITA AS NOMBRE_ESTADO,
                FECHA_CREACION AS CREADA_EL,
                FECHA_CANCELACION,
                MOTIVO_CANCELACION,
                OBSERVACIONES_DOCTOR,
                RECOMENDACIONES,
                FECHA_ATENCION,
                ODONTOLOGO_ID_ODONTOLOGO AS ID_ODONTOLOGO,
                NOMBRE_ODONTOLOGO AS DOCTOR_NOMBRE,
                NOMBRE_ESPECIALIDAD,
                NOMBRE_CONSULTORIO AS CONSULTORIO_NOMBRE,
                NOMBRE_PROCEDIMIENTO
            FROM vista_citas
            WHERE ID_CITA = ?
        ", [$idCita]);

        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'No se encontraron detalles de la cita.']);
        }

        $res = (array) $detalle;
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

        // Historial / Bitácora
        $historialRaw = DB::select("
            SELECT * FROM historial_reprogramacion_citas
            WHERE CITA_ID_CITA = ?
            ORDER BY CREADO_EL DESC
        ", [$idCita]);

        $historialFormateado = [];
        foreach ($historialRaw as $hist) {
            $h = (array) $hist;
            $h['FECHA_ANTERIOR_FMT'] = !empty($h['FECHA_HORA_ANTERIOR']) ? date('d/m/Y - h:i A', strtotime($h['FECHA_HORA_ANTERIOR'])) : '--';
            $h['FECHA_NUEVA_FMT']    = !empty($h['FECHA_HORA_NUEVA']) ? date('d/m/Y - h:i A', strtotime($h['FECHA_HORA_NUEVA'])) : '--';
            $h['REGISTRO_FMT']       = !empty($h['CREADO_EL']) ? date('d/m/Y - h:i A', strtotime($h['CREADO_EL'])) : '--';
            $historialFormateado[] = $h;
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