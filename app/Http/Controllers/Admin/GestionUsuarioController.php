<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class GestionUsuarioController extends Controller
{
    // 1. Mostrar la interfaz (HTML)
    public function index()
    {
        return view('admin.gestion_usuario.index');
    }

    // 2. Punto de entrada para el JavaScript (El reemplazo de tu api_gestion_usuario)
    public function api(Request $request)
    {
        $op = $request->input('op', '');

        try {
            switch ($op) {
                case 'listar':
                    return $this->listar();
                case 'listar_especialidades':
                    return $this->listarEspecialidades();
                case 'listar_consultorios':
                    return $this->listarConsultorios();
                case 'crear':
                    return $this->crear($request);
                case 'actualizar_rol':
                    return $this->actualizarRol($request);
                case 'eliminar':
                    return $this->eliminar($request);
                default:
                    throw new Exception("Operación no reconocida: '$op'.");
            }
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 400);
        }
    }

    private function listar()
    {
        // Consulta sobre la VISTA SQL vista_usuarios
        $datos = DB::select("
            SELECT 
                ID_USUARIOS AS id,
                NOMBRE_COMPLETO AS nombre,
                CORREO AS email,
                ROLES_ID_ROLES AS rol_id,
                ROL_NOMBRE AS rol_nombre,
                ESTADO AS estado,
                GROUP_CONCAT(ID_ESPECIALIDAD SEPARATOR ',') AS especialidad_id,
                GROUP_CONCAT(NOMBRE_ESPECIALIDAD SEPARATOR ', ') AS especialidad_nombre,
                ID_CONSULTORIO AS consultorio,
                NOMBRE_CONSULTORIO AS consultorio_nombre
            FROM vista_usuarios
            GROUP BY ID_USUARIOS, NOMBRE_COMPLETO, CORREO, ROLES_ID_ROLES, ROL_NOMBRE, ESTADO, ID_CONSULTORIO, NOMBRE_CONSULTORIO
            ORDER BY ID_USUARIOS DESC
        ");

        // Convertimos los objetos stdClass a arrays para poder agregarles la imagen
        $datosArray = json_decode(json_encode($datos), true);

        foreach ($datosArray as &$u) {
            $u['img'] = 'https://ui-avatars.com/api/?name=' . urlencode($u['nombre']) . '&background=0D8ABC&color=fff';
        }

        return response()->json(['ok' => true, 'datos' => $datosArray]);
    }

    private function listarEspecialidades()
    {
        $datos = DB::table('especialidad')
            ->select('ID_ESPECIALIDAD AS id', 'NOMBRE_ESPECIALIDAD AS nombre')
            ->orderBy('nombre', 'asc')->get();
        return response()->json(['ok' => true, 'datos' => $datos]);
    }

    private function listarConsultorios()
    {
        $datos = DB::table('consultorio')
            ->select('ID_CONSULTORIO AS id', 'NOMBRE AS nombre')
            ->where('ESTADO', 'DISPONIBLE')
            ->orderBy('nombre', 'asc')->get();
        return response()->json(['ok' => true, 'datos' => $datos]);
    }

    private function crear(Request $request)
    {
        // Validación básica
        $nombre = trim($request->input('nombre', ''));
        $email = trim($request->input('email', ''));
        $telefono = trim($request->input('telefono', ''));
        $rolId = $request->input('rol_id');
        $especialidadId = $request->input('especialidad_id');
        $consultorioId = $request->input('consultorio');

        if (!$nombre || !$email || !$telefono || !$rolId) {
            throw new Exception("Nombre, correo, teléfono y rol son obligatorios.");
        }

        if (DB::table('usuarios')->where('CORREO', $email)->exists()) {
            throw new Exception("Ya existe un usuario registrado con ese correo.");
        }

        $partes = preg_split('/\s+/', $nombre, 2);
        $nombres = $partes[0] ?? $nombre;
        $apellidos = $partes[1] ?? '-';

        $passTemp = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 10);
        $numDocProvisional = (int) ('9' . substr((string) time(), -8));

        DB::beginTransaction();
        try {
            $userId = DB::table('usuarios')->insertGetId([
                'ROLES_ID_ROLES' => $rolId,
                'NOMBRES' => $nombres,
                'APELLIDOS' => $apellidos,
                'TIPO_DOCUMENTO' => 'C.C',
                'NUMERO_DOCUMENTO' => $numDocProvisional,
                'TELEFONO' => $telefono,
                'CORREO' => $email,
                'GENERO' => 'OTRO',
                'CONTRASEÑA' => Hash::make($passTemp),
                'ESTADO' => 'Activo'
            ]);

            if ($rolId == 3) { // Paciente
                DB::table('paciente')->insert(['USUARIOS_ID_USUARIOS' => $userId, 'EPS_ID_EPS' => 1]);
            } elseif ($rolId == 2) { // Odontologo
                $odoId = DB::table('odontologo')->insertGetId([
                    'USUARIOS_ID_USUARIOS' => $userId,
                    'CONSULTORIO_ID_CONSULTORIO' => $consultorioId
                ]);
                if ($especialidadId) {
                    $especialidades = is_array($especialidadId) ? $especialidadId : [$especialidadId];
                    foreach ($especialidades as $eId) {
                        DB::table('odontologo_has_especialidad')->insert([
                            'ODONTOLOGO_ID_ODONTOLOGO' => $odoId, 
                            'ESPECIALIDAD_ID_ESPECIALIDAD' => $eId
                        ]);
                    }
                }
                if ($consultorioId) {
                    DB::table('consultorio')->where('ID_CONSULTORIO', $consultorioId)->update(['ESTADO' => 'ASIGNADO']);
                }
            }
            DB::commit();

            $rolNombre = DB::table('roles')->where('ID_ROLES', $rolId)->value('ROL_NOMBRE');

            return response()->json([
                'ok' => true,
                'usuario' => [
                    'id' => $userId, 'nombre' => $nombre, 'email' => $email,
                    'rol_id' => $rolId, 'rol_nombre' => $rolNombre, 'estado' => 'Activo',
                    'img' => 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=0D8ABC&color=fff'
                ],
                'pass_temp' => $passTemp
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function actualizarRol(Request $request)
    {
        $id = $request->input('id');
        $rolId = $request->input('rol_id');
        $estado = $request->input('estado');

        if (!$id || !$rolId || !$estado) {
            throw new Exception("Faltan datos: id, rol_id y estado son requeridos.");
        }

        DB::table('usuarios')->where('ID_USUARIOS', $id)->update([
            'ROLES_ID_ROLES' => $rolId,
            'ESTADO' => $estado
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Usuario actualizado correctamente.']);
    }

    private function eliminar(Request $request)
    {
        $id = $request->input('id');
        if (!$id) throw new Exception("Falta el campo 'id'.");

        DB::table('usuarios')->where('ID_USUARIOS', $id)->update(['ESTADO' => 'Inactivo']);
        return response()->json(['ok' => true, 'mensaje' => 'Usuario desactivado correctamente.']);
    }
}
