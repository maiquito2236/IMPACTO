<?php

namespace App\Http\Controllers\Odontologo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class PerfilController extends Controller
{
    private function getUsuarioId()
    {
        if (Auth::check()) {
            return Auth::id();
        }
        $odontologo = DB::table('odontologo')->first();
        return $odontologo ? $odontologo->USUARIOS_ID_USUARIOS : 2;
    }

    public function index(Request $request)
    {
        $idUsuario = $this->getUsuarioId();

        $perfil = DB::table('vista_odontologos')
            ->where('ID_USUARIOS', $idUsuario)
            ->first();

        $perfil = $perfil ? (array)$perfil : [];

        $odontologo = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
        if ($odontologo) {
            $espIds = DB::table('odontologo_has_especialidad')
                ->where('ODONTOLOGO_ID_ODONTOLOGO', $odontologo->ID_ODONTOLOGO)
                ->pluck('ESPECIALIDAD_ID_ESPECIALIDAD')
                ->toArray();
            if (empty($espIds) && !empty($odontologo->ESPECIALIDAD_ID_ESPECIALIDAD)) {
                $espIds = [$odontologo->ESPECIALIDAD_ID_ESPECIALIDAD];
            }
            $perfil['especialidad_id'] = implode(',', $espIds);

            // Nombres de especialidades para vista
            $espNombres = DB::table('especialidad')
                ->whereIn('ID_ESPECIALIDAD', $espIds)
                ->pluck('NOMBRE_ESPECIALIDAD')
                ->toArray();
            if (!empty($espNombres)) {
                $perfil['especialidad_nombre'] = implode(', ', $espNombres);
            }
        }

        $especialidades = DB::table('especialidad')
            ->select('ID_ESPECIALIDAD', 'NOMBRE_ESPECIALIDAD')
            ->orderBy('NOMBRE_ESPECIALIDAD', 'ASC')
            ->get()
            ->map(fn($item) => (array)$item)
            ->toArray();

        return view('odontologo.perfil', compact('perfil', 'especialidades'));
    }

    public function actualizarPerfil(Request $request)
    {
        try {
            $idUsuario = $this->getUsuarioId();

            $correo = trim($request->input('correo'));
            $nombres = trim($request->input('nombres'));
            $apellidos = trim($request->input('apellidos'));
            $tipoDoc = $request->input('tipo_documento');
            $numDoc = trim($request->input('numero_documento'));
            $genero = $request->input('genero');
            $fechaNac = $request->input('fecha_nacimiento');
            $direccion = trim($request->input('direccion'));
            $telefono = trim($request->input('telefono'));
            $especialidades = $request->input('especialidad_id', []);
            $passNueva = $request->input('pass_nueva');

            // Validar correo único
            $existeOtro = DB::table('usuarios')
                ->where('CORREO', $correo)
                ->where('ID_USUARIOS', '!=', $idUsuario)
                ->exists();

            if ($existeOtro) {
                throw new Exception("El correo ingresado ya está registrado por otro usuario.");
            }

            DB::beginTransaction();

            // 1. Actualizar usuarios
            DB::table('usuarios')
                ->where('ID_USUARIOS', $idUsuario)
                ->update([
                    'NOMBRES' => $nombres,
                    'APELLIDOS' => $apellidos,
                    'TIPO_DOCUMENTO' => $tipoDoc,
                    'NUMERO_DOCUMENTO' => $numDoc,
                    'GENERO' => $genero,
                    'FECHA_NACIMIENTO' => $fechaNac,
                    'DIRECCION' => $direccion,
                    'TELEFONO' => $telefono,
                    'CORREO' => $correo,
                ]);

            if (!is_array($especialidades) && !empty($especialidades)) {
                $especialidades = [$especialidades];
            }
            $primeraEsp = !empty($especialidades) ? (int)$especialidades[0] : 1;

            // 2. Odontologo y especialidades
            $odontologo = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $idUsuario)->first();
            if ($odontologo) {
                $idOdontologo = $odontologo->ID_ODONTOLOGO;
                DB::table('odontologo')
                    ->where('ID_ODONTOLOGO', $idOdontologo)
                    ->update(['ESPECIALIDAD_ID_ESPECIALIDAD' => $primeraEsp]);
            } else {
                $idOdontologo = DB::table('odontologo')->insertGetId([
                    'USUARIOS_ID_USUARIOS'         => $idUsuario,
                    'ESPECIALIDAD_ID_ESPECIALIDAD' => $primeraEsp,
                    'CONSULTORIO_ID_CONSULTORIO'   => 1
                ]);
            }

            DB::table('odontologo_has_especialidad')->where('ODONTOLOGO_ID_ODONTOLOGO', $idOdontologo)->delete();
            if (!empty($especialidades)) {
                $pivotData = [];
                foreach ($especialidades as $eId) {
                    if (!empty($eId)) {
                        $pivotData[] = [
                            'ODONTOLOGO_ID_ODONTOLOGO' => $idOdontologo,
                            'ESPECIALIDAD_ID_ESPECIALIDAD' => (int)$eId
                        ];
                    }
                }
                if (!empty($pivotData)) {
                    DB::table('odontologo_has_especialidad')->insertOrIgnore($pivotData);
                }
            }

            // 3. Contraseña
            if (!empty($passNueva)) {
                DB::table('usuarios')
                    ->where('ID_USUARIOS', $idUsuario)
                    ->update(['CONTRASEÑA' => Hash::make($passNueva)]);
            }

            // 4. Notificaciones
            try {
                DB::table('notificaciones')->insert([
                    'USUARIOS_ID_USUARIOS' => $idUsuario,
                    'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 17,
                    'MENSAJE' => "Tus datos personales fueron actualizados con éxito.",
                    'FECHA_ENVIO' => now(),
                    'ESTADO' => 'NO_LEIDA'
                ]);

                $admin = DB::table('usuarios')->where('ROLES_ID_ROLES', 1)->first();
                if ($admin) {
                    DB::table('notificaciones')->insert([
                        'USUARIOS_ID_USUARIOS' => $admin->ID_USUARIOS,
                        'TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION' => 18,
                        'MENSAJE' => "El odontólogo {$nombres} {$apellidos} ha actualizado sus datos personales.",
                        'FECHA_ENVIO' => now(),
                        'ESTADO' => 'NO_LEIDA'
                    ]);
                }
            } catch (Exception $ign) {
                // Notificaciones opcionales
            }

            DB::commit();

            return redirect('/odontologo/perfil?success=1');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect('/odontologo/perfil?mode=edit&error=' . urlencode($e->getMessage()));
        }
    }
}
