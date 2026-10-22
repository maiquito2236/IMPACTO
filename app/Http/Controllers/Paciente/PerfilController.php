<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Helpers\Notificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Dompdf\Dompdf;
use Dompdf\Options;

class PerfilController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $usuario = DB::table('usuarios')->where('ID_USUARIOS', $idUsuario)->first();
        $paciente = DB::table('paciente as p')
            ->leftJoin('eps as e', 'p.EPS_ID_EPS', '=', 'e.ID_EPS')
            ->where('p.USUARIOS_ID_USUARIOS', $idUsuario)
            ->select('p.*', 'e.NOMBRE_EPS')
            ->first();
        $epsLista = DB::table('eps')->orderBy('NOMBRE_EPS', 'asc')->get();

        // Enfermedades y Alergias
        $enfermedadesLista = DB::table('condicion_medica')->where('TIPO', 'ENFERMEDAD')->orderBy('NOMBRE_CONDICION', 'asc')->get();
        $alergiasLista = DB::table('condicion_medica')->where('TIPO', 'ALERGIA')->orderBy('NOMBRE_CONDICION', 'asc')->get();

        // Condiciones asignadas al paciente (usando vista_antecedentes_medicos)
        $condicionesPaciente = [];
        $alergiasPaciente = [];
        $idEnfermedadPaciente = null;
        $idAlergiaPaciente = null;
        if ($paciente) {
            $rawCond = DB::table('vista_antecedentes_medicos')
                ->where('ID_PACIENTE', $paciente->ID_PACIENTE)
                ->select('ID_CONDICION_MEDICA', 'NOMBRE_CONDICION', 'TIPO', 'OBSERVACION')
                ->get();
            $condicionesPaciente = json_decode(json_encode($rawCond), true);
            foreach ($condicionesPaciente as $c) {
                if (($c['TIPO'] ?? '') === 'ALERGIA') {
                    $alergiasPaciente[] = $c['NOMBRE_CONDICION'];
                    $idAlergiaPaciente = $c['ID_CONDICION_MEDICA'];
                }
                if (($c['TIPO'] ?? '') === 'ENFERMEDAD') {
                    $idEnfermedadPaciente = $c['ID_CONDICION_MEDICA'];
                }
            }
        }
        $textoAlergias = !empty($alergiasPaciente) ? implode(', ', $alergiasPaciente) : 'Ninguna';

        // Hijos y menores a cargo vinculados a este usuario acudiente
        $hijos = DB::table('paciente_has_acudiente as pha')
            ->join('paciente as p', 'pha.PACIENTE_ID_PACIENTE', '=', 'p.ID_PACIENTE')
            ->join('usuarios as u', 'p.USUARIOS_ID_USUARIOS', '=', 'u.ID_USUARIOS')
            ->leftJoin('eps as e', 'p.EPS_ID_EPS', '=', 'e.ID_EPS')
            ->where('pha.USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->select(
                'pha.ID_PACIENTE_HAS_ACUDIENTE',
                'pha.PARENTESCO',
                'pha.CONTACTO_PRINCIPAL',
                'pha.AUTORIZA_TRATAMIENTOS',
                'pha.FECHA_VINCULACION',
                'p.ID_PACIENTE',
                'u.ID_USUARIOS',
                'u.NOMBRES',
                'u.APELLIDOS',
                'u.TIPO_DOCUMENTO',
                'u.NUMERO_DOCUMENTO',
                'u.GENERO',
                'u.FECHA_NACIMIENTO',
                'u.RH',
                'e.NOMBRE_EPS',
                'p.EPS_ID_EPS'
            )
            ->orderBy('pha.ID_PACIENTE_HAS_ACUDIENTE', 'desc')
            ->get();

        $usuarioArray = (array) $usuario;
        $pacienteArray = (array) $paciente;

        return view('paciente.perfil', [
            'usuario'              => $usuarioArray,
            'paciente'             => $pacienteArray,
            'epsLista'             => $epsLista,
            'enfermedadesLista'    => $enfermedadesLista,
            'alergiasLista'        => $alergiasLista,
            'condicionesPaciente'  => $condicionesPaciente,
            'idEnfermedadPaciente' => $idEnfermedadPaciente,
            'idAlergiaPaciente'    => $idAlergiaPaciente,
            'textoAlergias'        => $textoAlergias,
            'hijos'                => $hijos
        ]);
    }

    public function actualizar(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        $nombres   = trim($request->input('nombres'));
        $apellidos = trim($request->input('apellidos'));
        $tipoDoc   = $request->input('tipo_documento');
        $numDoc    = trim($request->input('numero_documento'));
        $telefono  = trim($request->input('telefono'));
        $genero    = $request->input('genero');
        $fechaNac  = $request->input('fecha_nacimiento');
        $direccion = trim($request->input('direccion'));
        $correo    = trim($request->input('correo'));
        $eps       = $request->input('eps');
        $rh        = $request->input('rh');
        $nombreEmergencia = trim($request->input('nombre_contacto_emergencia') ?? '');
        $numEmergencia    = trim($request->input('numero_contacto_emergencia') ?? '');

        if (empty($eps)) {
            return redirect('/paciente/perfil?mode=edit')->with('ERROR_PERFIL', 'Debe seleccionar una EPS.');
        }

        if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
            return redirect('/paciente/perfil?mode=edit')->with('ERROR_PERFIL', 'El teléfono debe tener exactamente 10 dígitos.');
        }

        if (!empty($numEmergencia) && !preg_match('/^[0-9]{10}$/', $numEmergencia)) {
            return redirect('/paciente/perfil?mode=edit')->with('ERROR_PERFIL', 'El contacto de emergencia debe tener exactamente 10 dígitos.');
        }

        $datosUsuario = [
            'NOMBRES'          => $nombres,
            'APELLIDOS'        => $apellidos,
            'TIPO_DOCUMENTO'   => $tipoDoc,
            'NUMERO_DOCUMENTO' => $numDoc,
            'TELEFONO'         => $telefono,
            'GENERO'           => $genero,
            'FECHA_NACIMIENTO' => $fechaNac ?: null,
            'DIRECCION'        => $direccion,
            'CORREO'           => $correo,
            'RH'               => $rh
        ];

        // Cambio de contraseña si se especificó
        if ($request->filled('password')) {
            $nuevaPass = $request->input('password');
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPass)) {
                return redirect('/paciente/perfil?mode=edit')->with('ERROR_PERFIL', 'La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.');
            }
            $datosUsuario['CONTRASEÑA'] = Hash::make($nuevaPass);
        }

        DB::table('usuarios')->where('ID_USUARIOS', $idUsuario)->update($datosUsuario);

        session(['usuario_nome' => "$nombres $apellidos"]);

        // Actualizar paciente
        DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->update([
            'EPS_ID_EPS'                   => $eps,
            'NOMBRE_CONTACTO_EMERGENCIA'   => $nombreEmergencia,
            'NUMERO_CONTACTO_EMERGENCIA'   => $numEmergencia
        ]);

        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();

        // Condiciones médicas
        if ($paciente) {
            DB::table('paciente_has_condicion_medica')->where('PACIENTE_ID_PACIENTE', $paciente->ID_PACIENTE)->delete();

            $enfermedad = $request->input('enfermedad');
            if (!empty($enfermedad)) {
                DB::table('paciente_has_condicion_medica')->insert([
                    'PACIENTE_ID_PACIENTE'                 => $paciente->ID_PACIENTE,
                    'CONDICION_MEDICA_ID_CONDICION_MEDICA' => $enfermedad
                ]);
            }

            $alergia = $request->input('alergia');
            if (!empty($alergia)) {
                DB::table('paciente_has_condicion_medica')->insert([
                    'PACIENTE_ID_PACIENTE'                 => $paciente->ID_PACIENTE,
                    'CONDICION_MEDICA_ID_CONDICION_MEDICA' => $alergia
                ]);
            }
        }

        // Notificaciones
        Notificador::enviarAUsuario($idUsuario, 16, "Tus datos personales fueron actualizados con éxito.");
        Notificador::enviarAAdmin(18, "El paciente $nombres $apellidos ha actualizado sus datos personales.");

        return redirect('/paciente/perfil')->with('EXITO_PERFIL', 'Datos actualizados correctamente.');
    }

    public function exportar(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $formato = strtolower(trim($request->input('formato') ?? 'excel'));

        $u = DB::table('vista_pacientes')
            ->where('ID_USUARIOS', $idUsuario)
            ->first();

        if (!$u) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Datos de paciente no encontrados para exportar.');
        }

        // ==========================================
        // 1. MÓDULO EXCEL: ESTRUCTURA NATIVA CON METADATOS Y UTF-8 BOM
        // ==========================================
        if ($formato === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="Mis_Datos_Completos.xls"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            // 🚀 UTF-8 BOM para evitar problemas de tildes o caracteres especiales en Excel
            echo "\xEF\xBB\xBF";
            echo '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <style>
                    .header-title { background-color: #0b57d0; color: #ffffff; font-weight: bold; font-size: 14px; text-align: center; padding: 12px; }
                    .label-cell { background-color: #f1f5f9; font-weight: bold; text-align: left; padding: 6px; }
                    .data-cell { text-align: left; padding: 6px; }
                </style>
            </head>
            <body>
            <table border="1">
                <thead>
                    <tr><th colspan="2" class="header-title">ODONTO ESTÉTICA - DATOS PERSONALES</th></tr>
                </thead>
                <tbody>
                    <tr><td class="label-cell">Tipo de Documento:</td><td class="data-cell">' . htmlspecialchars($u->TIPO_DOCUMENTO ?? '') . '</td></tr>
                    <tr><td class="label-cell">Número de Documento:</td><td class="data-cell" style="mso-number-format:\'\@\';">' . htmlspecialchars($u->NUMERO_DOCUMENTO ?? '') . '</td></tr>
                    <tr><td class="label-cell">Nombres:</td><td class="data-cell">' . htmlspecialchars($u->NOMBRES ?? '') . '</td></tr>
                    <tr><td class="label-cell">Apellidos:</td><td class="data-cell">' . htmlspecialchars($u->APELLIDOS ?? '') . '</td></tr>
                    <tr><td class="label-cell">Teléfono celular:</td><td class="data-cell" style="mso-number-format:\'\@\';">' . htmlspecialchars($u->TELEFONO ?? '') . '</td></tr>
                    <tr><td class="label-cell">Correo electrónico:</td><td class="data-cell">' . htmlspecialchars($u->CORREO ?? '') . '</td></tr>
                    <tr><td class="label-cell">Género:</td><td class="data-cell">' . htmlspecialchars($u->GENERO ?? '') . '</td></tr>
                    <tr><td class="label-cell">Fecha de Nacimiento:</td><td class="data-cell">' . htmlspecialchars(!empty($u->FECHA_NACIMIENTO) ? date('d/m/Y', strtotime($u->FECHA_NACIMIENTO)) : '') . '</td></tr>
                    <tr><td class="label-cell">Dirección de residencia:</td><td class="data-cell">' . htmlspecialchars($u->DIRECCION ?? '') . '</td></tr>
                    <tr><td class="label-cell">Factor de sangre (RH):</td><td class="data-cell">' . htmlspecialchars($u->RH ?? '') . '</td></tr>
                    <tr><td class="label-cell">Entidad de salud (EPS):</td><td class="data-cell">' . htmlspecialchars($u->NOMBRE_EPS ?? '') . '</td></tr>
                    <tr><td class="label-cell">Contacto de Emergencia:</td><td class="data-cell">' . htmlspecialchars($u->NOMBRE_CONTACTO_EMERGENCIA ?? '') . '</td></tr>
                    <tr><td class="label-cell">Teléfono de Emergencia:</td><td class="data-cell" style="mso-number-format:\'\@\';">' . htmlspecialchars($u->NUMERO_CONTACTO_EMERGENCIA ?? '') . '</td></tr>
                </tbody>
            </table>
            </body>
            </html>';
            exit;
        }

        // ==========================================
        // 2. MÓDULO PDF: RENDERIZACIÓN Y DESCARGA NATIVA (DOMPDF)
        // ==========================================
        if ($formato === 'pdf') {
            $options = new Options();
            $options->set('defaultFont', 'Helvetica');
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', public_path());

            $dompdf = new Dompdf($options);

            $logoPath = public_path('img/logo_odontologia.png');
            $logoSrc = '';
            if (extension_loaded('gd') && file_exists($logoPath)) {
                $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }

            $fechaNac = !empty($u->FECHA_NACIMIENTO) ? date('d/m/Y', strtotime($u->FECHA_NACIMIENTO)) : '';

            $html = '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <title>Mis Datos Personales</title>
                <style>
                    body { font-family: "Helvetica", sans-serif; color: #1e293b; padding: 30px; }
                    .header { text-align: center; border-bottom: 3px solid #0b57d0; padding-bottom: 15px; margin-bottom: 30px; }
                    .logo { width: 180px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
                    .info-box { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    .info-box td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
                    .label { font-weight: bold; color: #64748b; width: 35%; }
                    .footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 60px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
                </style>
            </head>
            <body>
                <div class="header">';
            if ($logoSrc) {
                $html .= '<img src="' . $logoSrc . '" class="logo" alt="Logo">';
            }
            $html .= '
                    <div style="color: #64748b; font-size: 12px; margin-top: 8px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Resumen Clínico Informativo de Perfil</div>
                </div>

                <h2 style="color: #1e293b; font-size: 18px; margin-bottom: 20px;">Información del Paciente</h2>

                <table class="info-box">
                    <tr><td class="label">Documento de Identidad:</td><td>' . htmlspecialchars(($u->TIPO_DOCUMENTO ?? '') . ' ' . ($u->NUMERO_DOCUMENTO ?? '')) . '</td></tr>
                    <tr><td class="label">Nombres Completos:</td><td>' . htmlspecialchars(($u->NOMBRES ?? '') . ' ' . ($u->APELLIDOS ?? '')) . '</td></tr>
                    <tr><td class="label">Teléfono de Contacto:</td><td>' . htmlspecialchars($u->TELEFONO ?? '') . '</td></tr>
                    <tr><td class="label">Correo Electrónico:</td><td>' . htmlspecialchars($u->CORREO ?? '') . '</td></tr>
                    <tr><td class="label">Género:</td><td>' . htmlspecialchars($u->GENERO ?? '') . '</td></tr>
                    <tr><td class="label">Fecha de Nacimiento:</td><td>' . htmlspecialchars($fechaNac) . '</td></tr>
                    <tr><td class="label">Dirección Registrada:</td><td>' . htmlspecialchars($u->DIRECCION ?? '') . '</td></tr>
                    <tr><td class="label">Grupo Sanguíneo (RH):</td><td>' . htmlspecialchars($u->RH ?? '') . '</td></tr>
                    <tr><td class="label">Entidad de Salud (EPS):</td><td>' . htmlspecialchars($u->NOMBRE_EPS ?? '') . '</td></tr>
                    <tr><td class="label">Contacto de Emergencia:</td><td>' . htmlspecialchars($u->NOMBRE_CONTACTO_EMERGENCIA ?? '') . '</td></tr>
                    <tr><td class="label">Teléfono de Emergencia:</td><td>' . htmlspecialchars($u->NUMERO_CONTACTO_EMERGENCIA ?? '') . '</td></tr>
                </table>

                <div class="footer">
                    Este documento es un extracto informativo generado automáticamente por el usuario autenticado. &copy; 2026.
                </div>
            </body>
            </html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("Mis_Datos_Personales.pdf", ["Attachment" => true]);
            exit;
        }

        // ==========================================
        // 3. MÓDULO IMPRIMIR: INTERFAZ DE HARDWARE LOCAL
        // ==========================================
        return view('paciente.exports.perfil_imprimir', compact('u'));
    }

    /**
     * Registra un nuevo menor de edad y lo vincula al acudiente autenticado
     */
    public function registrarHijo(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        $nombres              = trim($request->input('nombres', ''));
        $apellidos            = trim($request->input('apellidos', ''));
        $tipoDoc              = $request->input('tipo_documento', 'R.C');
        $numDoc               = trim($request->input('numero_documento', ''));
        $genero               = $request->input('genero', 'MASCULINO');
        $fechaNac             = $request->input('fecha_nacimiento');
        $rh                   = $request->input('rh');
        $epsId                = (int) $request->input('eps_id', 1);
        $parentesco           = $request->input('parentesco', 'Madre');
        $contactoPrincipal    = $request->input('contacto_principal', 'Si');
        $autorizaTratamientos = $request->input('autoriza_tratamientos', 'Si');

        // Validaciones básicas
        if (empty($nombres) || empty($apellidos) || empty($numDoc) || empty($fechaNac)) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Todos los campos obligatorios del menor deben ser diligenciados.');
        }

        // Validar que sea menor de 18 años
        $edad = \Carbon\Carbon::parse($fechaNac)->age;
        if ($edad >= 18) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'El paciente a registrar debe ser menor de 18 años (Edad calculada: ' . $edad . ' años).');
        }

        // Validar que el número de documento no exista ya en el sistema
        $existeDoc = DB::table('usuarios')->where('NUMERO_DOCUMENTO', $numDoc)->exists();
        if ($existeDoc) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', "El documento de identidad $tipoDoc $numDoc ya está registrado en el sistema.");
        }

        try {
            // Ejecutar el procedimiento almacenado
            DB::statement("CALL sp_registrar_menor_con_acudiente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @nuevo_usr, @nuevo_pac)", [
                $nombres,
                $apellidos,
                $tipoDoc,
                $numDoc,
                $genero,
                $fechaNac,
                $rh,
                $epsId,
                $idUsuario,
                $parentesco,
                $contactoPrincipal,
                $autorizaTratamientos
            ]);

            // Notificaciones
            Notificador::enviarAUsuario($idUsuario, 16, "Has vinculado exitosamente a tu hijo/a $nombres $apellidos ($parentesco). Ya puedes agendar citas a su nombre.");
            Notificador::enviarAAdmin(18, "El acudiente " . (session('usuario_nome') ?? 'Paciente') . " registró a su familiar menor $nombres $apellidos.");

            return redirect('/paciente/perfil')->with('EXITO_PERFIL', "¡Menor $nombres $apellidos registrado y vinculado con éxito!");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en registrarHijo: " . $e->getMessage());
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Ocurrió un error al registrar el menor: ' . $e->getMessage());
        }
    }

    /**
     * Desvincula un menor del acudiente autenticado
     */
    public function desvincularHijo($id)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');

        $relacion = DB::table('paciente_has_acudiente')
            ->where('ID_PACIENTE_HAS_ACUDIENTE', $id)
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->first();

        if (!$relacion) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'No se encontró la vinculación solicitada o no tienes permisos sobre este registro.');
        }

        DB::table('paciente_has_acudiente')
            ->where('ID_PACIENTE_HAS_ACUDIENTE', $id)
            ->delete();

        return redirect('/paciente/perfil')->with('EXITO_PERFIL', 'Menor desvinculado de tu cuenta de acudiente.');
    }

    /**
     * Actualiza los datos personales de un menor a cargo
     */
    public function actualizarHijo(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $idRelacion = (int) $request->input('id_relacion');

        $relacion = DB::table('paciente_has_acudiente')
            ->where('ID_PACIENTE_HAS_ACUDIENTE', $idRelacion)
            ->where('USUARIOS_ID_ACUDIENTE', $idUsuario)
            ->first();

        if (!$relacion) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'No tienes permisos para modificar este paciente o el registro no existe.');
        }

        $nombres              = trim($request->input('nombres', ''));
        $apellidos            = trim($request->input('apellidos', ''));
        $genero               = $request->input('genero', 'MASCULINO');
        $fechaNac             = $request->input('fecha_nacimiento');
        $rh                   = $request->input('rh');
        $epsId                = (int) $request->input('eps_id', 1);
        $parentesco           = $request->input('parentesco', 'Madre');
        $contactoPrincipal    = $request->input('contacto_principal', 'Si');
        $autorizaTratamientos = $request->input('autoriza_tratamientos', 'Si');

        if (empty($nombres) || empty($apellidos) || empty($fechaNac)) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Los campos de nombres, apellidos y fecha de nacimiento son obligatorios.');
        }

        $edad = \Carbon\Carbon::parse($fechaNac)->age;
        if ($edad >= 18) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'El paciente a editar debe ser menor de 18 años (' . $edad . ' años detectados).');
        }

        $paciente = DB::table('paciente')->where('ID_PACIENTE', $relacion->PACIENTE_ID_PACIENTE)->first();
        if (!$paciente) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Paciente no encontrado.');
        }

        try {
            DB::beginTransaction();

            $updateUsuario = [
                'NOMBRES' => $nombres,
                'APELLIDOS' => $apellidos,
                'GENERO' => $genero,
                'FECHA_NACIMIENTO' => $fechaNac,
            ];
            if (!empty($rh)) {
                $updateUsuario['RH'] = $rh;
            }

            DB::table('usuarios')->where('ID_USUARIOS', $paciente->USUARIOS_ID_USUARIOS)->update($updateUsuario);

            DB::table('paciente')->where('ID_PACIENTE', $paciente->ID_PACIENTE)->update([
                'EPS_ID_EPS' => $epsId
            ]);

            DB::table('paciente_has_acudiente')->where('ID_PACIENTE_HAS_ACUDIENTE', $idRelacion)->update([
                'PARENTESCO' => $parentesco,
                'CONTACTO_PRINCIPAL' => $contactoPrincipal,
                'AUTORIZA_TRATAMIENTOS' => $autorizaTratamientos
            ]);

            DB::commit();

            Notificador::enviarAUsuario($idUsuario, 16, "Has actualizado exitosamente los datos de tu familiar menor $nombres $apellidos.");

            return redirect('/paciente/perfil')->with('EXITO_PERFIL', "Datos del menor $nombres $apellidos actualizados con éxito.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error en actualizarHijo: " . $e->getMessage());
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Error al actualizar los datos del menor: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza la contraseña del paciente desde el modal de seguridad
     */
    public function cambiarPassword(Request $request)
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $nuevaPass = $request->input('password');
        $confirmPass = $request->input('password_confirmation');

        if (empty($nuevaPass)) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Debe ingresar una nueva contraseña.');
        }

        if ($nuevaPass !== $confirmPass) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'Las contraseñas ingresadas no coinciden.');
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPass)) {
            return redirect('/paciente/perfil')->with('ERROR_PERFIL', 'La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.');
        }

        DB::table('usuarios')->where('ID_USUARIOS', $idUsuario)->update([
            'CONTRASEÑA' => Hash::make($nuevaPass)
        ]);

        Notificador::enviarAUsuario($idUsuario, 16, "Tu contraseña ha sido actualizada exitosamente.");

        return redirect('/paciente/perfil')->with('EXITO_PERFIL', '¡Contraseña actualizada correctamente!');
    }
}