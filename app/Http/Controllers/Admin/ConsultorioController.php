<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ConsultorioController extends Controller
{
    // Retorna la vista HTML (con los tabs y modales)
    public function index()
    {
        return view('admin.consultorios.index');
    }

    // Enrutador de API centralizado
    public function api(Request $request, $op)
    {
        try {
            switch ($op) {
                case 'listar':
                    return response()->json(['status' => 'success', 'data' => $this->listarConsultorios()]);

                case 'detalle':
                    $data = $this->obtenerConsultorioPorId($request->query('id', 0));
                    if (!$data) throw new Exception("Consultorio no encontrado.");
                    return response()->json(['status' => 'success', 'data' => $data]);

                case 'odontologos-disponibles':
                    return response()->json(['status' => 'success', 'data' => $this->obtenerOdontologosSinConsultorio()]);

                case 'crear':
                    $input = $request->json()->all();
                    if (empty($input['nombre'])) throw new Exception("El nombre es obligatorio.");
                    $id = $this->crearConsultorio($input);
                    return response()->json(['status' => 'success', 'message' => 'Consultorio creado correctamente.', 'id' => $id]);

                case 'actualizar':
                    $input = $request->json()->all();
                    if (empty($input['id']) || empty($input['nombre'])) throw new Exception("Datos incompletos.");
                    $this->actualizarConsultorio((int)$input['id'], $input);
                    return response()->json(['status' => 'success', 'message' => 'Consultorio actualizado correctamente.']);

                case 'eliminar':
                    $input = $request->json()->all();
                    if (empty($input['id'])) throw new Exception("ID no proporcionado.");
                    $this->eliminarConsultorio((int)$input['id']);
                    return response()->json(['status' => 'success', 'message' => 'Consultorio eliminado correctamente.']);

                case 'asignar':
                    $input = $request->json()->all();
                    if (empty($input['consultorio_id']) || empty($input['odontologo_id'])) {
                        throw new Exception("Selecciona un consultorio y un odontólogo.");
                    }
                    $this->asignarOdontologo((int)$input['consultorio_id'], (int)$input['odontologo_id']);
                    return response()->json(['status' => 'success', 'message' => 'Odontólogo asignado correctamente.']);

                case 'desasignar':
                    $input = $request->json()->all();
                    if (empty($input['consultorio_id'])) throw new Exception("ID de consultorio no proporcionado.");
                    $motivo = $input['motivo'] ?? 'Sin motivo especificado';
                    $this->desasignarOdontologo((int)$input['consultorio_id'], $motivo);
                    return response()->json(['status' => 'success', 'message' => 'Odontólogo desasignado correctamente.']);

                case 'estado':
                    $input = $request->json()->all();
                    if (empty($input['id']) || empty($input['estado'])) throw new Exception("ID o estado no proporcionado.");
                    $this->cambiarEstado((int)$input['id'], $input['estado']);
                    return response()->json(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);

                case 'historial':
                    return response()->json(['status' => 'success', 'data' => $this->obtenerHistorialAsignaciones()]);

                case 'kpis':
                    return response()->json(['status' => 'success', 'data' => $this->obtenerResumenKPIs()]);

                default:
                    return response()->json(['status' => 'error', 'message' => 'Operación no válida'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // --- MÉTODOS PRIVADOS DE BASE DE DATOS ---

    private function listarConsultorios() {
        $sql = "SELECT c.ID_CONSULTORIO, c.NOMBRE, c.UBICACION, c.DESCRIPCION, c.ESTADO, o.ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre, e.NOMBRE_ESPECIALIDAD AS especialidad FROM consultorio c LEFT JOIN odontologo o ON o.CONSULTORIO_ID_CONSULTORIO = c.ID_CONSULTORIO LEFT JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD ORDER BY c.ID_CONSULTORIO ASC";
        return DB::select($sql);
    }

    private function obtenerConsultorioPorId($id) {
        $sql = "SELECT c.ID_CONSULTORIO, c.NOMBRE, c.UBICACION, c.DESCRIPCION, c.ESTADO, o.ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre, e.NOMBRE_ESPECIALIDAD AS especialidad FROM consultorio c LEFT JOIN odontologo o ON o.CONSULTORIO_ID_CONSULTORIO = c.ID_CONSULTORIO LEFT JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS LEFT JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD WHERE c.ID_CONSULTORIO = ?";
        $res = DB::select($sql, [$id]);
        return empty($res) ? null : $res[0];
    }

    private function obtenerOdontologosSinConsultorio() {
        $sql = "SELECT o.ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre, e.NOMBRE_ESPECIALIDAD AS especialidad FROM odontologo o INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD WHERE o.CONSULTORIO_ID_CONSULTORIO IS NULL ORDER BY u.NOMBRES ASC";
        return DB::select($sql);
    }

    private function obtenerTodosOdontologos() {
        $sql = "SELECT o.ID_ODONTOLOGO, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre, e.NOMBRE_ESPECIALIDAD AS especialidad, o.CONSULTORIO_ID_CONSULTORIO FROM odontologo o INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS INNER JOIN especialidad e ON o.ESPECIALIDAD_ID_ESPECIALIDAD = e.ID_ESPECIALIDAD ORDER BY u.NOMBRES ASC";
        return DB::select($sql);
    }

    private function crearConsultorio($data) {
        return DB::table('consultorio')->insertGetId([
            'NOMBRE' => $data['nombre'],
            'UBICACION' => $data['ubicacion'] ?? '',
            'DESCRIPCION' => $data['descripcion'] ?? '',
            'ESTADO' => 'DISPONIBLE'
        ]);
    }

    private function actualizarConsultorio($id, $data) {
        return DB::table('consultorio')->where('ID_CONSULTORIO', $id)->update([
            'NOMBRE' => $data['nombre'],
            'UBICACION' => $data['ubicacion'] ?? '',
            'DESCRIPCION' => $data['descripcion'] ?? ''
        ]);
    }

    private function eliminarConsultorio($id) {
        $estado = DB::table('consultorio')->where('ID_CONSULTORIO', $id)->value('ESTADO');
        if ($estado !== 'DISPONIBLE') {
            throw new Exception("Solo se pueden eliminar consultorios disponibles (sin asignación).");
        }
        return DB::table('consultorio')->where('ID_CONSULTORIO', $id)->delete();
    }

    private function cambiarEstado($id, $estado) {
        $estadoActual = DB::table('consultorio')->where('ID_CONSULTORIO', $id)->value('ESTADO');
        if ($estadoActual === 'ASIGNADO') {
            throw new Exception("No se puede cambiar el estado de un consultorio asignado.");
        }
        return DB::table('consultorio')->where('ID_CONSULTORIO', $id)->update(['ESTADO' => $estado]);
    }

    private function asignarOdontologo($consultorioId, $odontologoId) {
        DB::beginTransaction();
        try {
            $estado = DB::table('consultorio')->where('ID_CONSULTORIO', $consultorioId)->value('ESTADO');
            if ($estado === 'ASIGNADO') throw new Exception("Este consultorio ya tiene un odontólogo asignado.");

            $consActual = DB::table('odontologo')->where('ID_ODONTOLOGO', $odontologoId)->value('CONSULTORIO_ID_CONSULTORIO');
            if (!empty($consActual)) throw new Exception("Este odontólogo ya tiene un consultorio asignado.");

            DB::table('odontologo')->where('ID_ODONTOLOGO', $odontologoId)->update(['CONSULTORIO_ID_CONSULTORIO' => $consultorioId]);
            DB::table('consultorio')->where('ID_CONSULTORIO', $consultorioId)->update(['ESTADO' => 'ASIGNADO']);
            DB::table('historial_asignacion_consultorio')->insert([
                'CONSULTORIO_ID' => $consultorioId,
                'ODONTOLOGO_ID' => $odontologoId,
                'FECHA_ASIGNACION' => now()
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function desasignarOdontologo($consultorioId, $motivo = null) {
        DB::beginTransaction();
        try {
            $odontologoId = DB::table('odontologo')->where('CONSULTORIO_ID_CONSULTORIO', $consultorioId)->value('ID_ODONTOLOGO');

            if (!$odontologoId) {
                DB::table('consultorio')->where('ID_CONSULTORIO', $consultorioId)->update(['ESTADO' => 'DISPONIBLE']);
                DB::commit();
                return true;
            }

            DB::table('odontologo')->where('ID_ODONTOLOGO', $odontologoId)->update(['CONSULTORIO_ID_CONSULTORIO' => null]);
            DB::table('consultorio')->where('ID_CONSULTORIO', $consultorioId)->update(['ESTADO' => 'DISPONIBLE']);
            DB::table('historial_asignacion_consultorio')
                ->where('CONSULTORIO_ID', $consultorioId)
                ->where('ODONTOLOGO_ID', $odontologoId)
                ->whereNull('FECHA_DESASIGNACION')
                ->update(['FECHA_DESASIGNACION' => now(), 'MOTIVO_CAMBIO' => $motivo]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function obtenerHistorialAsignaciones() {
        $sql = "SELECT h.ID_HISTORIAL, c.NOMBRE AS consultorio_nombre, CONCAT(u.NOMBRES, ' ', u.APELLIDOS) AS doctor_nombre, h.FECHA_ASIGNACION, h.FECHA_DESASIGNACION, h.MOTIVO_CAMBIO FROM historial_asignacion_consultorio h INNER JOIN consultorio c ON h.CONSULTORIO_ID = c.ID_CONSULTORIO INNER JOIN odontologo o ON h.ODONTOLOGO_ID = o.ID_ODONTOLOGO INNER JOIN usuarios u ON o.USUARIOS_ID_USUARIOS = u.ID_USUARIOS ORDER BY h.FECHA_ASIGNACION DESC";
        return DB::select($sql);
    }

    private function obtenerResumenKPIs() {
        $sql = "SELECT (SELECT COUNT(*) FROM consultorio) AS total_consultorios, (SELECT COUNT(*) FROM consultorio WHERE ESTADO = 'ASIGNADO') AS asignados, (SELECT COUNT(*) FROM consultorio WHERE ESTADO = 'DISPONIBLE') AS disponibles, (SELECT COUNT(*) FROM odontologo WHERE CONSULTORIO_ID_CONSULTORIO IS NOT NULL) AS odontologos_con_consultorio";
        $res = DB::select($sql);
        return empty($res) ? null : $res[0];
    }
}
