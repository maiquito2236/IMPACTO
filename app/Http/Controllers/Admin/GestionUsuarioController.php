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
                case 'carga_masiva':
                    return $this->cargaMasiva($request);
                case 'descargar_plantilla':
                    return $this->descargarPlantillaCsv();
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
            $u['email'] = $u['email'] ?? '';
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
                $especialidades = is_array($especialidadId) ? $especialidadId : ($especialidadId ? [$especialidadId] : [1]);
                $especialidadPrincipal = (int)($especialidades[0] ?? 1);

                $odoId = DB::table('odontologo')->insertGetId([
                    'USUARIOS_ID_USUARIOS'         => $userId,
                    'ESPECIALIDAD_ID_ESPECIALIDAD' => $especialidadPrincipal,
                    'CONSULTORIO_ID_CONSULTORIO'   => $consultorioId ?: null
                ]);

                foreach ($especialidades as $eId) {
                    if (!empty($eId)) {
                        DB::table('odontologo_has_especialidad')->insert([
                            'ODONTOLOGO_ID_ODONTOLOGO'    => $odoId, 
                            'ESPECIALIDAD_ID_ESPECIALIDAD' => (int)$eId
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
        $especialidadId = $request->input('especialidad_id');
        $consultorioId = $request->input('consultorio');

        if (!$id || !$rolId || !$estado) {
            throw new Exception("Faltan datos: id, rol_id y estado son requeridos.");
        }

        DB::table('usuarios')->where('ID_USUARIOS', $id)->update([
            'ROLES_ID_ROLES' => $rolId,
            'ESTADO' => $estado
        ]);

        if ($rolId == 2) {
            $especialidades = is_array($especialidadId) ? $especialidadId : ($especialidadId ? [$especialidadId] : [1]);
            $especialidadPrincipal = (int)($especialidades[0] ?? 1);

            $odo = DB::table('odontologo')->where('USUARIOS_ID_USUARIOS', $id)->first();
            if ($odo) {
                DB::table('odontologo')->where('ID_ODONTOLOGO', $odo->ID_ODONTOLOGO)->update([
                    'ESPECIALIDAD_ID_ESPECIALIDAD' => $especialidadPrincipal,
                    'CONSULTORIO_ID_CONSULTORIO'   => $consultorioId ?: null
                ]);
                $odoId = $odo->ID_ODONTOLOGO;
            } else {
                $odoId = DB::table('odontologo')->insertGetId([
                    'USUARIOS_ID_USUARIOS'         => $id,
                    'ESPECIALIDAD_ID_ESPECIALIDAD' => $especialidadPrincipal,
                    'CONSULTORIO_ID_CONSULTORIO'   => $consultorioId ?: null
                ]);
            }

            DB::table('odontologo_has_especialidad')->where('ODONTOLOGO_ID_ODONTOLOGO', $odoId)->delete();
            foreach ($especialidades as $eId) {
                if (!empty($eId)) {
                    DB::table('odontologo_has_especialidad')->insert([
                        'ODONTOLOGO_ID_ODONTOLOGO'    => $odoId,
                        'ESPECIALIDAD_ID_ESPECIALIDAD' => (int)$eId
                    ]);
                }
            }
            if ($consultorioId) {
                DB::table('consultorio')->where('ID_CONSULTORIO', $consultorioId)->update(['ESTADO' => 'ASIGNADO']);
            }
        }

        return response()->json(['ok' => true, 'mensaje' => 'Usuario actualizado correctamente.']);
    }

    private function eliminar(Request $request)
    {
        $id = $request->input('id');
        if (!$id) throw new Exception("Falta el campo 'id'.");

        DB::table('usuarios')->where('ID_USUARIOS', $id)->update(['ESTADO' => 'Inactivo']);
        return response()->json(['ok' => true, 'mensaje' => 'Usuario desactivado correctamente.']);
    }

    public function cargaMasiva(Request $request)
    {
        if ($request->has('descargar_plantilla')) {
            return $this->descargarPlantillaCsv();
        }

        if ($request->hasFile('archivo_csv')) {
            $file = fopen($request->file('archivo_csv')->getRealPath(), 'r');
            $line = fgets($file);
            $separator = (strpos($line, ';') !== false) ? ';' : ',';
            rewind($file);
            $header = fgetcsv($file, 0, $separator);

            $successCount = 0; $errorCount = 0; $erroresDetalle = [];
            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                if (count($row) == 1 && empty(trim($row[0]))) continue;
                if (count($row) < 8) { $errorCount++; continue; }
                $numDoc = trim($row[3]);
                if (strtoupper($numDoc) === 'NUMERO_DOCUMENTO') continue;

                $dataUsuario = [
                    'nombres'          => trim($row[0]),
                    'apellidos'        => trim($row[1]),
                    'tipo_documento'   => trim($row[2]),
                    'numero_documento' => $numDoc,
                    'telefono'         => trim($row[4]),
                    'correo'           => trim($row[5]),
                    'genero'           => trim($row[6]),
                    'estado'           => 'Activo',
                    'rol'              => strtoupper(trim($row[7])) === 'ODONTOLOGO' ? 2 : 3
                ];

                try {
                    $this->registrarUsuarioMasivo(
                        $dataUsuario,
                        isset($row[8]) ? trim($row[8]) : null,
                        isset($row[9]) ? trim($row[9]) : null
                    );
                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    $erroresDetalle[] = "Documento $numDoc: " . $e->getMessage();
                }
            }
            fclose($file);
            return response()->json([
                'success'        => true,
                'successCount'   => $successCount,
                'errorCount'     => $errorCount,
                'erroresDetalle' => $erroresDetalle
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se subió ningún archivo'], 400);
    }

    public function descargarPlantillaCsv()
    {
        return response()->streamDownload(function() {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['NOMBRES', 'APELLIDOS', 'TIPO_DOCUMENTO', 'NUMERO_DOCUMENTO', 'TELEFONO', 'CORREO', 'GENERO', 'ROL', 'ESPECIALIDAD_ID_SI_ES_ODONTOLOGO', 'EPS_ID_SI_ES_PACIENTE']);
            fputcsv($output, ['Juan', 'Perez', 'C.C', '123456789', '3001234567', 'juan@example.com', 'MASCULINO', 'PACIENTE', '', '1']);
            fputcsv($output, ['Maria', 'Gomez', 'C.E', '987654321', '3009876543', 'maria@example.com', 'FEMENINO', 'ODONTOLOGO', '1', '']);
            fclose($output);
        }, 'plantilla_usuarios.csv', [
            'Content-Type' => 'text/csv; charset=utf-8'
        ]);
    }

    private function registrarUsuarioMasivo($dataUsuario, $especialidades = null, $epsId = null)
    {
        $numDoc = $dataUsuario['numero_documento'];
        $existe = DB::table('usuarios')->where('NUMERO_DOCUMENTO', $numDoc)->first();
        if ($existe) {
            throw new Exception("El usuario con documento $numDoc ya está registrado.");
        }

        DB::beginTransaction();
        try {
            $id = DB::table('usuarios')->insertGetId([
                'NOMBRES'          => $dataUsuario['nombres'],
                'APELLIDOS'        => $dataUsuario['apellidos'],
                'TIPO_DOCUMENTO'   => $dataUsuario['tipo_documento'] ?: 'C.C',
                'NUMERO_DOCUMENTO' => $numDoc,
                'TELEFONO'         => $dataUsuario['telefono'] ?: null,
                'CORREO'           => $dataUsuario['correo'],
                'GENERO'           => $dataUsuario['genero'] ?: 'MASCULINO',
                'CONTRASEÑA'       => Hash::make($numDoc),
                'ESTADO'           => $dataUsuario['estado'] ?: 'Activo',
                'ROLES_ID_ROLES'   => $dataUsuario['rol']
            ]);

            if ($dataUsuario['rol'] == 2) {
                $espId = (int)($especialidades ?: 1);
                $odoId = DB::table('odontologo')->insertGetId([
                    'USUARIOS_ID_USUARIOS'         => $id,
                    'ESPECIALIDAD_ID_ESPECIALIDAD' => $espId,
                    'CONSULTORIO_ID_CONSULTORIO'   => null
                ]);

                DB::table('odontologo_has_especialidad')->insert([
                    'ODONTOLOGO_ID_ODONTOLOGO'    => $odoId,
                    'ESPECIALIDAD_ID_ESPECIALIDAD' => $espId
                ]);
            } elseif ($dataUsuario['rol'] == 3) {
                DB::table('paciente')->insert([
                    'USUARIOS_ID_USUARIOS' => $id,
                    'EPS_ID_EPS'           => (int)($epsId ?: 1)
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
