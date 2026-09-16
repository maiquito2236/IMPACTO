<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OtrosController extends Controller
{
    /**
     * Muestra la vista principal de Otros Catálogos
     */
    public function index(Request $request)
    {
        $tabActiva = $request->query('tab', '');
        if (!in_array($tabActiva, ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'])) {
            $tabActiva = '';
        }

        // Procedimientos
        $procedimientosRaw = DB::select("
            SELECT 
                p.ID_PROCEDIMIENTO AS id,
                p.NOMBRE_PROCEDIMIENTO AS nombre,
                p.DESCRIPCION AS descripcion,
                p.TIEMPO_ESTIMADO AS duracion_num,
                CONCAT(p.TIEMPO_ESTIMADO, ' min') AS duracion,
                p.COSTO AS precio_num,
                CONCAT('$', FORMAT(p.COSTO, 0)) AS precio,
                p.ESTADO AS estado
            FROM procedimientos p
            ORDER BY p.ID_PROCEDIMIENTO DESC
        ");
        $procedimientos = array_map(function($proc) {
            $p = (array)$proc;
            $esActivo = (strtoupper($p['estado']) === 'ACTIVO');
            $p['css_estado'] = $esActivo ? 'est-activo' : 'est-inactivo';
            $p['estado'] = $esActivo ? 'Activo' : 'Inactivo';
            return $p;
        }, $procedimientosRaw);

        // Especialidades
        $especialidadesRaw = DB::select("
            SELECT ID_ESPECIALIDAD AS id, NOMBRE_ESPECIALIDAD AS nombre, ESTADO AS estado
            FROM especialidad
            ORDER BY ID_ESPECIALIDAD DESC
        ");
        $especialidades = array_map(function($esp) {
            $e = (array)$esp;
            $esActivo = (strtoupper($e['estado']) === 'ACTIVO');
            $e['css_estado'] = $esActivo ? 'est-activo' : 'est-inactivo';
            $e['estado'] = $esActivo ? 'Activo' : 'Inactivo';
            return $e;
        }, $especialidadesRaw);

        // EPS
        $epsRaw = DB::select("
            SELECT ID_EPS AS id, NOMBRE_EPS AS nombre, ESTADO AS estado
            FROM eps
            ORDER BY ID_EPS DESC
        ");
        $listaEps = array_map(function($eps) {
            $ep = (array)$eps;
            $esActivo = (strtoupper($ep['estado']) === 'ACTIVO');
            $ep['css_estado'] = $esActivo ? 'est-activo' : 'est-inactivo';
            $ep['estado'] = $esActivo ? 'Activo' : 'Inactivo';
            return $ep;
        }, $epsRaw);

        // Alergias
        $alergiasRaw = DB::select("
            SELECT ID_CONDICION_MEDICA AS id, NOMBRE_CONDICION AS nombre, DESCRIPCION AS descripcion, ESTADO AS estado
            FROM condicion_medica
            WHERE TIPO = 'ALERGIA'
            ORDER BY ID_CONDICION_MEDICA DESC
        ");
        $alergias = array_map(function($alg) {
            $a = (array)$alg;
            $esActivo = (strtoupper($a['estado']) === 'ACTIVA' || strtoupper($a['estado']) === 'ACTIVO');
            $a['css_estado'] = $esActivo ? 'est-activo' : 'est-inactivo';
            $a['estado'] = $esActivo ? 'Activo' : 'Inactivo';
            return $a;
        }, $alergiasRaw);

        // Enfermedades
        $enfermedadesRaw = DB::select("
            SELECT ID_CONDICION_MEDICA AS id, NOMBRE_CONDICION AS nombre, DESCRIPCION AS descripcion, ESTADO AS estado
            FROM condicion_medica
            WHERE TIPO = 'ENFERMEDAD'
            ORDER BY ID_CONDICION_MEDICA DESC
        ");
        $enfermedades = array_map(function($enf) {
            $ef = (array)$enf;
            $esActivo = (strtoupper($ef['estado']) === 'ACTIVA' || strtoupper($ef['estado']) === 'ACTIVO');
            $ef['css_estado'] = $esActivo ? 'est-activo' : 'est-inactivo';
            $ef['estado'] = $esActivo ? 'Activo' : 'Inactivo';
            return $ef;
        }, $enfermedadesRaw);

        return view('admin.otros.index', compact('procedimientos', 'especialidades', 'listaEps', 'alergias', 'enfermedades', 'tabActiva'));
    }

    /**
     * Enrutador centralizado de API para Otros Catálogos
     */
    public function api(Request $request, $op)
    {
        $data = $request->json()->all() ?: $request->all();

        try {
            switch ($op) {
                // PROCEDIMIENTOS
                case 'guardar_procedimiento':
                    if (empty($data['nombre_procedimiento']) || empty($data['costo'])) {
                        throw new Exception("Faltan datos obligatorios para el procedimiento.");
                    }
                    $id = DB::table('procedimientos')->insertGetId([
                        'NOMBRE_PROCEDIMIENTO' => $data['nombre_procedimiento'],
                        'DESCRIPCION' => $data['descripcion'] ?? '',
                        'COSTO' => $data['costo'],
                        'TIEMPO_ESTIMADO' => $data['tiempo_estimado'] ?? 0,
                        'ESPECIALIDAD_ID_ESPECIALIDAD' => 1,
                        'ESTADO' => strtoupper($data['estado'] ?? 'ACTIVO'),
                        'TIPO_COBRO' => 1
                    ]);
                    return response()->json(['status' => 'success', 'id' => $id]);

                case 'editar_procedimiento':
                    if (empty($data['id_procedimiento']) || empty($data['nombre_procedimiento']) || empty($data['costo'])) {
                        throw new Exception("Faltan datos obligatorios para actualizar el procedimiento.");
                    }
                    DB::table('procedimientos')->where('ID_PROCEDIMIENTO', $data['id_procedimiento'])->update([
                        'NOMBRE_PROCEDIMIENTO' => $data['nombre_procedimiento'],
                        'DESCRIPCION' => $data['descripcion'] ?? '',
                        'COSTO' => $data['costo'],
                        'TIEMPO_ESTIMADO' => $data['tiempo_estimado'] ?? 0,
                        'ESTADO' => strtoupper($data['estado'] ?? 'ACTIVO')
                    ]);
                    return response()->json(['status' => 'success']);

                case 'eliminar_procedimiento':
                    if (empty($data['id_procedimiento'])) throw new Exception("Falta el ID del procedimiento.");
                    DB::table('procedimientos')->where('ID_PROCEDIMIENTO', $data['id_procedimiento'])->update(['ESTADO' => 'INACTIVO']);
                    return response()->json(['status' => 'success']);

                // ESPECIALIDADES
                case 'guardar_especialidad':
                    if (empty($data['nombre_especialidad'])) throw new Exception("El nombre de la especialidad es obligatorio.");
                    $id = DB::table('especialidad')->insertGetId([
                        'NOMBRE_ESPECIALIDAD' => $data['nombre_especialidad'],
                        'ESTADO' => strtoupper($data['estado'] ?? 'ACTIVO')
                    ]);
                    return response()->json(['status' => 'success', 'id' => $id]);

                case 'editar_especialidad':
                    if (empty($data['id_especialidad']) || empty($data['nombre_especialidad'])) {
                        throw new Exception("Faltan datos obligatorios para editar la especialidad.");
                    }
                    DB::table('especialidad')->where('ID_ESPECIALIDAD', $data['id_especialidad'])->update([
                        'NOMBRE_ESPECIALIDAD' => $data['nombre_especialidad'],
                        'ESTADO' => strtoupper($data['estado'] ?? 'ACTIVO')
                    ]);
                    return response()->json(['status' => 'success']);

                case 'eliminar_especialidad':
                    if (empty($data['id_especialidad'])) throw new Exception("Falta el ID de la especialidad.");
                    DB::table('especialidad')->where('ID_ESPECIALIDAD', $data['id_especialidad'])->update(['ESTADO' => 'INACTIVO']);
                    return response()->json(['status' => 'success']);

                // EPS
                case 'guardar_eps':
                    if (empty($data['nombre_eps'])) throw new Exception("El nombre de la EPS es obligatorio.");
                    $id = DB::table('eps')->insertGetId([
                        'NOMBRE_EPS' => $data['nombre_eps'],
                        'ESTADO' => strtoupper($data['estado'] ?? 'ACTIVO')
                    ]);
                    return response()->json(['status' => 'success', 'id' => $id]);

                case 'editar_eps':
                    if (empty($data['id_eps']) || empty($data['nombre_eps'])) {
                        throw new Exception("Faltan datos obligatorios para editar la EPS.");
                    }
                    DB::table('eps')->where('ID_EPS', $data['id_eps'])->update([
                        'NOMBRE_EPS' => $data['nombre_eps'],
                        'ESTADO' => strtoupper($data['estado'] ?? 'ACTIVO')
                    ]);
                    return response()->json(['status' => 'success']);

                case 'eliminar_eps':
                    if (empty($data['id_eps'])) throw new Exception("Falta el ID de la EPS.");
                    DB::table('eps')->where('ID_EPS', $data['id_eps'])->update(['ESTADO' => 'INACTIVO']);
                    return response()->json(['status' => 'success']);

                // ALERGIAS
                case 'guardar_alergia':
                    if (empty($data['nombre_alergia'])) throw new Exception("El nombre de la alergia es obligatorio.");
                    $estado = (strtoupper($data['estado'] ?? '') === 'INACTIVO' || strtoupper($data['estado'] ?? '') === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
                    $id = DB::table('condicion_medica')->insertGetId([
                        'NOMBRE_CONDICION' => $data['nombre_alergia'],
                        'TIPO' => 'ALERGIA',
                        'DESCRIPCION' => $data['descripcion'] ?? '',
                        'ESTADO' => $estado
                    ]);
                    return response()->json(['status' => 'success', 'id' => $id]);

                case 'editar_alergia':
                    if (empty($data['id_alergia']) || empty($data['nombre_alergia'])) {
                        throw new Exception("Faltan datos obligatorios para editar la alergia.");
                    }
                    $estado = (strtoupper($data['estado'] ?? '') === 'INACTIVO' || strtoupper($data['estado'] ?? '') === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
                    DB::table('condicion_medica')->where('ID_CONDICION_MEDICA', $data['id_alergia'])->where('TIPO', 'ALERGIA')->update([
                        'NOMBRE_CONDICION' => $data['nombre_alergia'],
                        'DESCRIPCION' => $data['descripcion'] ?? '',
                        'ESTADO' => $estado
                    ]);
                    return response()->json(['status' => 'success']);

                case 'eliminar_alergia':
                    if (empty($data['id_alergia'])) throw new Exception("Falta el ID de la alergia.");
                    DB::table('condicion_medica')->where('ID_CONDICION_MEDICA', $data['id_alergia'])->where('TIPO', 'ALERGIA')->update(['ESTADO' => 'INACTIVA']);
                    return response()->json(['status' => 'success']);

                // ENFERMEDADES
                case 'guardar_enfermedad':
                    if (empty($data['nombre_enfermedad'])) throw new Exception("El nombre de la enfermedad es obligatorio.");
                    $estado = (strtoupper($data['estado'] ?? '') === 'INACTIVO' || strtoupper($data['estado'] ?? '') === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
                    $id = DB::table('condicion_medica')->insertGetId([
                        'NOMBRE_CONDICION' => $data['nombre_enfermedad'],
                        'TIPO' => 'ENFERMEDAD',
                        'DESCRIPCION' => $data['descripcion'] ?? '',
                        'ESTADO' => $estado
                    ]);
                    return response()->json(['status' => 'success', 'id' => $id]);

                case 'editar_enfermedad':
                    if (empty($data['id_enfermedad']) || empty($data['nombre_enfermedad'])) {
                        throw new Exception("Faltan datos obligatorios para editar la enfermedad.");
                    }
                    $estado = (strtoupper($data['estado'] ?? '') === 'INACTIVO' || strtoupper($data['estado'] ?? '') === 'INACTIVA') ? 'INACTIVA' : 'ACTIVA';
                    DB::table('condicion_medica')->where('ID_CONDICION_MEDICA', $data['id_enfermedad'])->where('TIPO', 'ENFERMEDAD')->update([
                        'NOMBRE_CONDICION' => $data['nombre_enfermedad'],
                        'DESCRIPCION' => $data['descripcion'] ?? '',
                        'ESTADO' => $estado
                    ]);
                    return response()->json(['status' => 'success']);

                case 'eliminar_enfermedad':
                    if (empty($data['id_enfermedad'])) throw new Exception("Falta el ID de la enfermedad.");
                    DB::table('condicion_medica')->where('ID_CONDICION_MEDICA', $data['id_enfermedad'])->where('TIPO', 'ENFERMEDAD')->update(['ESTADO' => 'INACTIVA']);
                    return response()->json(['status' => 'success']);

                default:
                    throw new Exception("Operación no válida: $op");
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
