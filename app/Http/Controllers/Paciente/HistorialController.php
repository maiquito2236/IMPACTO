<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class HistorialController extends Controller
{
    /**
     * Listado cronológico de citas y evoluciones del paciente
     */
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        // Pacientes autorizados: el titular + menores a su cargo
        $pacientesIds = [];
        if ($idPaciente) {
            $pacientesIds[] = $idPaciente;
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
                    OBSERVACIONES_DOCTOR,
                    RECOMENDACIONES,
                    FECHA_ATENCION
                FROM vista_citas
                WHERE PACIENTE_ID_PACIENTE IN ($placeholders)
                ORDER BY FECHA_HORA DESC
            ", $pacientesIds);

            $citas = json_decode(json_encode($citas), true);
        }

        return view('paciente.historial', compact('citas', 'idPaciente'));
    }

    /**
     * AJAX: Detalle de una evolución médica / cita clínica
     */
    public function detalleHistorialAjax(Request $request)
    {
        $idCita = intval($request->input('id_cita'));

        $detalle = DB::selectOne("
            SELECT 
                ID_CITA,
                PACIENTE_ID_PACIENTE,
                NOMBRE_PACIENTE,
                DOC_PACIENTE,
                ODONTOLOGO_ID_ODONTOLOGO,
                FECHA_HORA,
                MOTIVO,
                ESTADO_CITA_ID,
                ESTADO_CITA,
                NOMBRE_ODONTOLOGO,
                NOMBRE_ESPECIALIDAD,
                NOMBRE_PROCEDIMIENTO,
                NOMBRE_CONSULTORIO,
                OBSERVACIONES_DOCTOR,
                RECOMENDACIONES,
                FECHA_ATENCION
            FROM vista_citas
            WHERE ID_CITA = ?
        ", [$idCita]);

        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'No se encontraron detalles clínicos.']);
        }

        $res = (array) $detalle;
        if (!empty($res['FECHA_HORA'])) {
            $ts = strtotime($res['FECHA_HORA']);
            $res['SOLO_FECHA'] = date('d/m/Y', $ts);
            $res['SOLO_HORA']  = date('h:i A', $ts);
        }

        $res['NOMBRE_DOCTOR']      = $res['NOMBRE_ODONTOLOGO'] ?? '';
        $res['DOCTOR_NOMBRE']      = $res['NOMBRE_ODONTOLOGO'] ?? '';
        $res['ESPECIALIDAD']       = !empty($res['NOMBRE_ESPECIALIDAD']) ? $res['NOMBRE_ESPECIALIDAD'] : 'General';
        $res['CONSULTORIO']        = $res['NOMBRE_CONSULTORIO'] ?? '';
        $res['ESTADO']             = $res['ESTADO_CITA'] ?? '';
        $res['TRATAMIENTO']        = !empty($res['NOMBRE_PROCEDIMIENTO']) ? $res['NOMBRE_PROCEDIMIENTO'] : (!empty($res['MOTIVO']) ? $res['MOTIVO'] : 'Valoración General');
        $res['LISTA_TRATAMIENTOS'] = $res['TRATAMIENTO'];

        // Tratamientos registrados en odontograma en la misma fecha
        try {
            $piezas = DB::table('odontograma_tratamientos as ot')
                ->join('procedimientos as p', 'ot.PROCEDIMIENTO_ID_PROCEDIMIENTO', '=', 'p.ID_PROCEDIMIENTO')
                ->where('ot.PACIENTE_ID_PACIENTE', $res['PACIENTE_ID_PACIENTE'])
                ->whereRaw('DATE(ot.FECHA_REGISTRO) = DATE(?)', [$res['FECHA_HORA']])
                ->select('ot.PIEZA_DENTAL', 'p.NOMBRE_PROCEDIMIENTO')
                ->get();

            if ($piezas->isNotEmpty()) {
                $lineas = $piezas->map(fn($p) => "• {$p->NOMBRE_PROCEDIMIENTO} (Pieza {$p->PIEZA_DENTAL})")->implode("\n");
                $res['TRATAMIENTOS_ODONTOGRAMA'] = $lineas;
                $res['TRATAMIENTO'] = $lineas;
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        return response()->json(['success' => true, 'data' => $res]);
    }

    /**
     * AJAX: Datos del odontograma, alertas médicas y demográficos del paciente
     */
    public function datosOdontograma(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $pacienteSesion = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPacienteDefault = $pacienteSesion ? $pacienteSesion->ID_PACIENTE : 0;

        $pacienteId = intval($request->input('paciente_id') ?? $idPacienteDefault);
        $targetDate = $request->input('target_date');

        // 1. Demográficos
        $demograficos = DB::table('paciente as pac')
            ->join('usuarios as u', 'pac.USUARIOS_ID_USUARIOS', '=', 'u.ID_USUARIOS')
            ->where('pac.ID_PACIENTE', $pacienteId)
            ->select(
                'pac.ID_PACIENTE',
                'u.NUMERO_DOCUMENTO as documento',
                'u.CORREO as correo',
                DB::raw("CONCAT(u.NOMBRES, ' ', u.APELLIDOS) as paciente_nombre")
            )
            ->first();

        // 2. Alertas médicas (alergias, enfermedades, medicamentos)
        $alergias = [];
        $enfermedades = [];
        $medicamentos = [];
        try {
            $condiciones = DB::table('paciente_has_condicion_medica as phc')
                ->join('condicion_medica as cm', 'phc.CONDICION_MEDICA_ID_CONDICION_MEDICA', '=', 'cm.ID_CONDICION_MEDICA')
                ->where('phc.PACIENTE_ID_PACIENTE', $pacienteId)
                ->select('cm.TIPO', 'cm.NOMBRE_CONDICION')
                ->get();

            foreach ($condiciones as $c) {
                $tipoUpper = strtoupper($c->TIPO ?? '');
                if (str_contains($tipoUpper, 'ALERGIA')) {
                    $alergias[] = $c->NOMBRE_CONDICION;
                } elseif (str_contains($tipoUpper, 'ENFERMEDAD')) {
                    $enfermedades[] = $c->NOMBRE_CONDICION;
                } else {
                    $medicamentos[] = $c->NOMBRE_CONDICION;
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        // 3. Dientes y tratamientos en odontograma
        $toothData = [];
        try {
            $query = DB::table('odontograma_tratamientos as ot')
                ->join('procedimientos as p', 'ot.PROCEDIMIENTO_ID_PROCEDIMIENTO', '=', 'p.ID_PROCEDIMIENTO')
                ->leftJoin('odontologo as od', 'ot.ODONTOLOGO_ID_ODONTOLOGO', '=', 'od.ID_ODONTOLOGO')
                ->leftJoin('usuarios as u', 'od.USUARIOS_ID_USUARIOS', '=', 'u.ID_USUARIOS')
                ->where('ot.PACIENTE_ID_PACIENTE', $pacienteId)
                ->select(
                    'ot.PIEZA_DENTAL',
                    'p.NOMBRE_PROCEDIMIENTO as tratamiento',
                    'ot.ESTADO as estado',
                    DB::raw("DATE_FORMAT(ot.FECHA_REGISTRO, '%d %b %Y') as fecha"),
                    'ot.NOTAS as notas',
                    DB::raw("COALESCE(CONCAT(u.NOMBRES, ' ', u.APELLIDOS), 'Sin asignar') as doctor"),
                    'ot.FECHA_REGISTRO'
                );

            if ($targetDate) {
                $query->whereDate('ot.FECHA_REGISTRO', '<=', $targetDate);
            }

            $datosDientes = $query->get();

            foreach ($datosDientes as $row) {
                $pieza = $row->PIEZA_DENTAL;
                if (!isset($toothData[$pieza])) {
                    $toothData[$pieza] = [
                        'nombre'      => "Pieza Dental " . $pieza,
                        'estado'      => $row->estado,
                        'tratamiento' => $row->tratamiento,
                        'doctor'      => $row->doctor,
                        'fecha'       => $row->fecha,
                        'notas'       => $row->notas ?? '',
                        'es_nuevo'    => $targetDate ? (date('Y-m-d', strtotime($row->FECHA_REGISTRO)) === $targetDate) : true
                    ];
                } else {
                    $toothData[$pieza]['tratamiento'] .= ', ' . $row->tratamiento;
                    if (!empty($row->notas)) {
                        $toothData[$pieza]['notas'] .= ' | ' . $row->notas;
                    }
                    if ($row->estado === 'HECHO') {
                        $toothData[$pieza]['estado'] = 'HECHO';
                    }
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        return response()->json([
            'paciente' => $demograficos ?: [
                'paciente_nombre' => 'Paciente Registrado',
                'documento'       => '--'
            ],
            'alertas'  => [
                'ALERGIAS'     => !empty($alergias) ? implode(', ', $alergias) : 'Ninguna',
                'ENFERMEDADES' => !empty($enfermedades) ? implode(', ', $enfermedades) : 'Ninguna',
                'MEDICAMENTOS' => !empty($medicamentos) ? implode(', ', $medicamentos) : 'Ninguno',
            ],
            'dientes'  => $toothData
        ]);
    }

    /**
     * Exportación de registro clínico / evolución (PDF o Impresión)
     */
    public function exportar(Request $request)
    {
        $idCita = $request->input('id') ?? $request->input('id_cita');
        $formato = strtolower($request->input('formato') ?? ($request->input('mode') ?? 'pdf'));

        if (!$idCita) {
            abort(404, 'ID de cita no especificado.');
        }

        $idUsuario = Auth::id() ?? session('usuario_id');
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $idPaciente = $paciente ? $paciente->ID_PACIENTE : null;

        $pacientesIds = [];
        if ($idPaciente) {
            $pacientesIds[] = $idPaciente;
        }
        $hijosIds = DB::table('paciente_has_acudiente')
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->pluck('PACIENTE_ID_PACIENTE')
            ->toArray();
        $pacientesIds = array_unique(array_merge($pacientesIds, $hijosIds));

        // Consulta directa y limpia a vista_citas sin ningún alias
        $datos = DB::selectOne("
            SELECT 
                ID_CITA,
                PACIENTE_ID_PACIENTE,
                NOMBRE_PACIENTE,
                DOC_PACIENTE,
                TIPO_DOC_PACIENTE,
                ODONTOLOGO_ID_ODONTOLOGO,
                FECHA_HORA,
                MOTIVO,
                ESTADO_CITA_ID,
                ESTADO_CITA,
                NOMBRE_ODONTOLOGO,
                NOMBRE_ESPECIALIDAD,
                NOMBRE_PROCEDIMIENTO,
                NOMBRE_CONSULTORIO,
                OBSERVACIONES_DOCTOR,
                RECOMENDACIONES,
                FECHA_ATENCION
            FROM vista_citas
            WHERE ID_CITA = ?
        ", [$idCita]);

        if (!$datos) {
            abort(404, 'Registro clínico no encontrado.');
        }

        if (!empty($pacientesIds) && !in_array($datos->PACIENTE_ID_PACIENTE, $pacientesIds)) {
            abort(403, 'Acceso denegado a este registro médico.');
        }

        $datos = (array) $datos;

        $fecha_atencion = !empty($datos['FECHA_ATENCION']) 
            ? date('d/m/Y \a \l\a\s h:i A', strtotime($datos['FECHA_ATENCION'])) 
            : date('d/m/Y \a \l\a\s h:i A', strtotime($datos['FECHA_HORA']));
        $procedimiento = !empty($datos['NOMBRE_PROCEDIMIENTO']) ? $datos['NOMBRE_PROCEDIMIENTO'] : (!empty($datos['MOTIVO']) ? $datos['MOTIVO'] : 'Valoración / Control General');
        $observaciones = !empty($datos['OBSERVACIONES_DOCTOR']) ? $datos['OBSERVACIONES_DOCTOR'] : 'Sin observaciones registradas.';
        $recomendaciones = !empty($datos['RECOMENDACIONES']) ? $datos['RECOMENDACIONES'] : 'Sin recomendaciones médicas especificadas.';
        $fecha_emision = date('d/m/Y \a \l\a\s h:i A');

        $nombreDoctor = $datos['NOMBRE_ODONTOLOGO'] ?? 'Odontólogo Especialista';
        $consultorio = $datos['NOMBRE_CONSULTORIO'] ?? '--';
        $tipoDoc = $datos['TIPO_DOC_PACIENTE'] ?? 'C.C';
        $docPaciente = $datos['DOC_PACIENTE'] ?? '--';
        $nombrePaciente = $datos['NOMBRE_PACIENTE'] ?? 'Paciente Registrado';

        $cssAjusteCirculo = ($formato === 'pdf') ? 'line-height: 25px;' : 'line-height: 32px;';

        $cssBase = '
            body { font-family: "Helvetica", "Arial", sans-serif; color: #1e293b; margin: 0; padding: 20px; }
            .header-table { width: 100%; border-bottom: 3px solid #1a56db; padding-bottom: 15px; margin-bottom: 20px; }
            .logo-cell { width: 40%; }
            .logo-title { color: #1a56db; margin: 0; font-size: 24px; font-weight: bold; }
            .info-cell { width: 60%; text-align: right; font-size: 11px; color: #0f172a; line-height: 1.6; }
            .text-primary { color: #1a56db; }
            .title-container { text-align: center; margin-bottom: 20px; }
            .main-title { font-size: 22px; color: #1a56db; margin: 0; text-transform: uppercase; }
            .sub-title { font-size: 14px; color: #64748b; margin: 5px 0 0 0; }
            .info-box { width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; background-color: #f8fafc; margin-bottom: 25px; border-collapse: collapse; }
            .info-box td { padding: 15px; vertical-align: top; width: 50%; }
            .border-right { border-right: 1px solid #cbd5e1; }
            .info-label { font-size: 11px; color: #64748b; margin-bottom: 3px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
            .info-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 12px; display: block; }
            .badge-consultorio { background-color: #1a56db; color: #ffffff; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; }
            .content-block { width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; margin-bottom: 15px; border-collapse: collapse; }
            .content-block td { padding: 15px; vertical-align: middle; }
            .icon-col { width: 50px; text-align: center; border-right: 1px dashed #cbd5e1; }
            .icon-circle { width: 32px; height: 32px; border-radius: 50%; display: inline-block; text-align: center; font-weight: bold; font-size: 16px; font-family: "Helvetica", sans-serif; box-sizing: border-box; ' . $cssAjusteCirculo . ' }
            .block-title { font-size: 13px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
            .block-text { font-size: 13px; color: #334155; }
            .block-blue { border-color: #93c5fd; border-left: 4px solid #3b82f6; }
            .block-blue .block-title { color: #1d4ed8; }
            .block-blue .icon-circle { background-color: #eff6ff; border: 1px solid #bfdbfe; color: #3b82f6; }
            .block-green { border-color: #86efac; border-left: 4px solid #22c55e; }
            .block-green .block-title { color: #15803d; }
            .block-green .icon-circle { background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #22c55e; }
            .block-orange { border-color: #fdba74; border-left: 4px solid #f97316; }
            .block-orange .block-title { color: #c2410c; }
            .block-orange .icon-circle { background-color: #fff7ed; border: 1px solid #fed7aa; color: #f97316; }
            .footer { text-align: center; font-size: 11px; color: #64748b; margin-top: 40px; padding-top: 15px; border-top: 1px dashed #cbd5e1; }
            .footer img { display: block; margin: 0 auto 10px auto; max-width: 150px; }
            .footer .footer-text { display: block; font-weight: bold; }
        ';

        $htmlBase = '
            <table class="header-table">
                <tr>
                    <td class="logo-cell">{{LOGO}}</td>
                    <td class="info-cell">
                        <strong class="text-primary">NIT:</strong> 51936980 <br>
                        <strong class="text-primary">Dirección:</strong> Calle 10 #10-35<br>
                        <strong class="text-primary">Teléfono:</strong> 3115204752<br>
                        <strong class="text-primary">Correo:</strong> odontoestetica@gmail.com
                    </td>
                </tr>
            </table>

            <div class="title-container">
                <h1 class="main-title">Historia Clínica Nº ' . htmlspecialchars($datos['ID_CITA']) . '</h1>
                <p class="sub-title">Resumen del Registro Médico</p>
            </div>

            <table class="info-box">
                <tr>
                    <td class="border-right">
                        <span class="info-label">Paciente</span>
                        <span class="info-value">' . htmlspecialchars($nombrePaciente) . '</span>
                        <span class="info-label">Documento de Identidad</span>
                        <span class="info-value">' . htmlspecialchars($tipoDoc . ' ' . $docPaciente) . '</span>
                        <span class="info-label">Fecha y Hora de Cierre</span>
                        <span class="info-value">' . htmlspecialchars($fecha_atencion) . '</span>
                    </td>
                    <td>
                        <span class="info-label">Odontólogo Tratante</span>
                        <span class="info-value">Dr(a). ' . htmlspecialchars($nombreDoctor) . '</span>
                        <span class="info-label">Consultorio</span>
                        <span class="info-value">Consultorio ' . htmlspecialchars($consultorio) . '</span>
                    </td>
                </tr>
            </table>

            <table class="content-block block-blue">
                <tr>
                    <td class="icon-col"><div class="icon-circle">1</div></td>
                    <td><div class="block-title">Procedimiento Clínico</div><div class="block-text">' . htmlspecialchars($procedimiento) . '</div></td>
                </tr>
            </table>

            <table class="content-block block-green">
                <tr>
                    <td class="icon-col"><div class="icon-circle">2</div></td>
                    <td><div class="block-title">Observaciones del Diagnóstico</div><div class="block-text">' . htmlspecialchars($observaciones) . '</div></td>
                </tr>
            </table>

            <table class="content-block block-orange">
                <tr>
                    <td class="icon-col"><div class="icon-circle">3</div></td>
                    <td><div class="block-title">Recomendaciones Médicas</div><div class="block-text">' . htmlspecialchars($recomendaciones) . '</div></td>
                </tr>
            </table>

            <div class="footer">
                {{LOGO_FOOTER}}
                <span class="footer-text text-primary">Fecha de emisión: ' . $fecha_emision . '</span>
            </div>
        ';

        if ($formato === 'pdf') {
            $rutaLogo = public_path('img/logo_odontologia.png');
            $logo_src = '';
            if (file_exists($rutaLogo)) {
                $tipo_imagen = pathinfo($rutaLogo, PATHINFO_EXTENSION);
                $datos_imagen = file_get_contents($rutaLogo);
                $logo_src = 'data:image/' . $tipo_imagen . ';base64,' . base64_encode($datos_imagen);
            }

            $logoTop = !empty($logo_src) ? '<img src="'.$logo_src.'" alt="Logo" style="height: 80px; width: auto;">' : '<h2 class="logo-title">ODONTO ESTÉTICA</h2>';
            $logoFooter = !empty($logo_src) ? '<img src="'.$logo_src.'" alt="Logo" style="height: 60px; width: auto; display: block; margin: 0 auto 10px auto;">' : '';

            $htmlFinal = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>' . $cssBase . '</style></head><body>';
            $htmlFinal .= str_replace(['{{LOGO}}', '{{LOGO_FOOTER}}'], [$logoTop, $logoFooter], $htmlBase);
            $htmlFinal .= '</body></html>';

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlFinal);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"Ficha_Evolucion_N{$idCita}.pdf\"",
            ]);
        }

        // Impresión directa
        $logoTop = '<img src="' . asset('img/logo_odontologia.png') . '" alt="Logo Odonto Estética" style="height: 80px; object-fit: contain;">';
        $logoFooter = '<img src="' . asset('img/logo_odontologia.png') . '" alt="Logo Odonto Estética" style="height: 60px; object-fit: contain;">';

        $htmlFinal = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Imprimir Historial</title><style>';
        $htmlFinal .= '@media print { @page { margin: 0; } body { margin: 1.5cm; } } ';
        $htmlFinal .= $cssBase . '</style></head><body>';
        $htmlFinal .= str_replace(['{{LOGO}}', '{{LOGO_FOOTER}}'], [$logoTop, $logoFooter], $htmlBase);
        $htmlFinal .= '<script> window.onload = function() { window.print(); }; window.onafterprint = function() { window.close(); }; </script>';
        $htmlFinal .= '</body></html>';

        return response($htmlFinal);
    }
}