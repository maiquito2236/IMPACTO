<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Helpers\Notificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $idUsuario = Auth::id() ?? session('usuario_id');
        $usuario = DB::table('usuarios')->where('ID_USUARIOS', $idUsuario)->first();
        $paciente = DB::table('paciente')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        $epsLista = DB::table('eps')->orderBy('NOMBRE_EPS', 'asc')->get();

        // Enfermedades y Alergias
        $enfermedadesLista = DB::table('condicion_medica')->where('TIPO', 'ENFERMEDAD')->orderBy('NOMBRE_CONDICION', 'asc')->get();
        $alergiasLista = DB::table('condicion_medica')->where('TIPO', 'ALERGIA')->orderBy('NOMBRE_CONDICION', 'asc')->get();

        // Condiciones asignadas al paciente (usando vista_antecedentes_medicos)
        $condicionesPaciente = [];
        if ($paciente) {
            $rawCond = DB::table('vista_antecedentes_medicos')
                ->where('ID_PACIENTE', $paciente->ID_PACIENTE)
                ->select('ID_CONDICION_MEDICA', 'NOMBRE_CONDICION', 'TIPO', 'OBSERVACION')
                ->get();
            $condicionesPaciente = json_decode(json_encode($rawCond), true);
        }

        $usuarioArray = (array) $usuario;
        $pacienteArray = (array) $paciente;

        return view('paciente.perfil', [
            'usuario'             => $usuarioArray,
            'paciente'            => $pacienteArray,
            'epsLista'            => $epsLista,
            'enfermedadesLista'   => $enfermedadesLista,
            'alergiasLista'       => $alergiasLista,
            'condicionesPaciente' => $condicionesPaciente
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

        if ($formato === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="Mis_Datos_Completos.xls"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            echo "<table border='1'>";
            echo "<tr><th colspan='2' style='background:#0F62FE;color:white;'>DATOS DEL PACIENTE</th></tr>";
            echo "<tr><td><b>Nombres:</b></td><td>{$u->NOMBRES} {$u->APELLIDOS}</td></tr>";
            echo "<tr><td><b>Documento:</b></td><td>{$u->TIPO_DOCUMENTO} {$u->NUMERO_DOCUMENTO}</td></tr>";
            echo "<tr><td><b>Correo:</b></td><td>{$u->CORREO}</td></tr>";
            echo "<tr><td><b>Teléfono:</b></td><td>{$u->TELEFONO}</td></tr>";
            echo "<tr><td><b>EPS:</b></td><td>{$u->NOMBRE_EPS}</td></tr>";
            echo "</table>";
            exit;
        }

        return view('paciente.exports.perfil_imprimir', compact('u'));
    }
}