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
        return DB::select("SELECT * FROM vista_consultorios ORDER BY ID_CONSULTORIO ASC");
    }

    private function obtenerConsultorioPorId($id) {
        $res = DB::select("SELECT * FROM vista_consultorios WHERE ID_CONSULTORIO = ?", [$id]);
        return empty($res) ? null : $res[0];
    }

    private function obtenerOdontologosSinConsultorio() {
        return DB::select("SELECT ID_ODONTOLOGO, NOMBRE_COMPLETO AS doctor_nombre, NOMBRE_ESPECIALIDAD AS especialidad FROM vista_odontologos WHERE ID_CONSULTORIO IS NULL ORDER BY NOMBRES ASC");
    }

    private function obtenerTodosOdontologos() {
        return DB::select("SELECT ID_ODONTOLOGO, NOMBRE_COMPLETO AS doctor_nombre, NOMBRE_ESPECIALIDAD AS especialidad, ID_CONSULTORIO AS CONSULTORIO_ID_CONSULTORIO FROM vista_odontologos ORDER BY NOMBRES ASC");
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
        return DB::select("SELECT * FROM vista_historial_consultorio ORDER BY FECHA_ASIGNACION DESC");
    }

    private function obtenerResumenKPIs() {
        $sql = "SELECT (SELECT COUNT(*) FROM consultorio) AS total_consultorios, (SELECT COUNT(*) FROM consultorio WHERE ESTADO = 'ASIGNADO') AS asignados, (SELECT COUNT(*) FROM consultorio WHERE ESTADO = 'DISPONIBLE') AS disponibles, (SELECT COUNT(*) FROM odontologo WHERE CONSULTORIO_ID_CONSULTORIO IS NOT NULL) AS odontologos_con_consultorio";
        $res = DB::select($sql);
        return empty($res) ? null : $res[0];
    }
}
