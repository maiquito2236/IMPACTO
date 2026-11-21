<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\Notificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

class PerfilController extends Controller
{
    /**
     * Muestra la pantalla del perfil del administrador
     */
    public function index(Request $request)
    {
        $modo_edicion = $request->query('mode') === 'edit';
        $id = Auth::id() ?: 1;

        $usuario = DB::table('usuarios')->where('ID_USUARIOS', $id)->first();
        if (!$usuario) {
            $usuario = DB::table('usuarios')->where('ROLES_ID_ROLES', 1)->first();
        }

        return view('admin.perfil.index', compact('usuario', 'modo_edicion'));
    }

    /**
     * Procesa la actualización del perfil del administrador
     */
    public function actualizar(Request $request)
    {
        $id = Auth::id() ?: 1;

        $telefono = trim($request->input('telefono', ''));
        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            return redirect('/admin/perfil?mode=edit')->with('error_perfil', 'El teléfono debe tener exactamente 10 dígitos.');
        }

        $datos = [
            'NOMBRES'          => $request->input('nombres'),
            'APELLIDOS'        => $request->input('apellidos'),
            'TIPO_DOCUMENTO'   => $request->input('tipo_documento'),
            'TELEFONO'         => $telefono,
            'CORREO'           => $request->input('correo'),
            'GENERO'           => $request->input('genero'),
            'FECHA_NACIMIENTO' => $request->input('fecha_nacimiento') ?: null,
            'DIRECCION'        => $request->input('direccion')
        ];

        // Validación de contraseña si fue enviada
        if ($request->filled('password')) {
            $nuevaPass = $request->input('password');
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nuevaPass)) {
                return redirect('/admin/perfil?mode=edit')->with('error_perfil', 'La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.');
            }
            $datos['CONTRASEÑA'] = Hash::make($nuevaPass);
        }

        DB::table('usuarios')->where('ID_USUARIOS', $id)->update($datos);

        // Notificación de actualización
        Notificador::insertar(18, $id, 'Tus datos personales fueron actualizados con éxito.');

        return redirect('/admin/perfil')->with('exito_perfil', 'Datos actualizados correctamente.');
    }

    /**
     * Activa o desactiva las notificaciones para el administrador autenticado
     */
    public function toggleNotificaciones(Request $request)
    {
        $id = Auth::id() ?: 1;
        $activa = $request->boolean('activa') ? 1 : 0;

        DB::table('usuarios')
            ->where('ID_USUARIOS', $id)
            ->update(['NOTIFICACIONES_ACTIVAS' => $activa]);

        return response()->json([
            'success' => true,
            'activa'  => (bool) $activa,
        ]);
    }
}
