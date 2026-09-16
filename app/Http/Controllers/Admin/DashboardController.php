<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Necesario para agrupar por fechas
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Pago;
use App\Models\Notificacion;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // 1. KPIs
        $citasHoy = Cita::whereDate('FECHA_HORA', $hoy)->whereIn('ESTADO_CITA_ID', [1, 2])->count();
        $pacientesAtendidos = Cita::whereDate('FECHA_HORA', $hoy)->where('ESTADO_CITA_ID', 2)->count();
        $ingresosHoy = Pago::whereDate('FECHA_PAGO', $hoy)->whereRaw("UPPER(ESTADO) = 'PAGADO'")->sum('MONTO');

        // 2. Gráfica de Dona (Estado Citas Hoy)
        $estadoCitasHoy = Cita::join('estado_cita', 'cita.ESTADO_CITA_ID', '=', 'estado_cita.ID_ESTADO')
            ->whereDate('cita.FECHA_HORA', $hoy)
            ->select('estado_cita.NOMBRE_ESTADO', DB::raw('COUNT(cita.ID_CITA) as cantidad'))
            ->groupBy('estado_cita.NOMBRE_ESTADO')
            ->get();

        // 3. Actividad (Líneas SVG)
        $fechaInicio = Carbon::today()->subDays(6);
        
        $resCitas = Cita::select(DB::raw('DATE(FECHA_HORA) as fecha'), DB::raw('COUNT(*) as cantidad'))
            ->whereBetween(DB::raw('DATE(FECHA_HORA)'), [$fechaInicio->format('Y-m-d'), $hoy->format('Y-m-d')])
            ->whereIn('ESTADO_CITA_ID', [1, 2])->groupBy(DB::raw('DATE(FECHA_HORA)'))->get();

        $resPacientes = Cita::select(DB::raw('DATE(FECHA_HORA) as fecha'), DB::raw('COUNT(*) as cantidad'))
            ->whereBetween(DB::raw('DATE(FECHA_HORA)'), [$fechaInicio->format('Y-m-d'), $hoy->format('Y-m-d')])
            ->where('ESTADO_CITA_ID', 2)->groupBy(DB::raw('DATE(FECHA_HORA)'))->get();

        $resIngresos = Pago::select(DB::raw('DATE(FECHA_PAGO) as fecha'), DB::raw('SUM(MONTO) as cantidad'))
            ->whereBetween(DB::raw('DATE(FECHA_PAGO)'), [$fechaInicio->format('Y-m-d'), $hoy->format('Y-m-d')])
            ->whereRaw("UPPER(ESTADO) = 'PAGADO'")->groupBy(DB::raw('DATE(FECHA_PAGO)'))->get();

        // Matemáticas para los SVG (idéntico a tu original)
        $diasStr = []; $dataCitas = []; $dataPacientes = []; $dataIngresos = [];
        $maxVal = 1; 
        
        for ($i = 0; $i < 7; $i++) {
            $fechaCur = Carbon::parse($fechaInicio)->addDays($i)->format('Y-m-d');
            $diasStr[] = Carbon::parse($fechaCur)->translatedFormat('d M'); // Ej: 15 Sep
            
            $valCitas = $resCitas->firstWhere('fecha', $fechaCur)->cantidad ?? 0;
            $valPacientes = $resPacientes->firstWhere('fecha', $fechaCur)->cantidad ?? 0;
            $valIngresos = $resIngresos->firstWhere('fecha', $fechaCur)->cantidad ?? 0;
            
            $dataCitas[] = $valCitas; $dataPacientes[] = $valPacientes; $dataIngresos[] = $valIngresos;
            
            if ($valCitas > $maxVal) $maxVal = $valCitas;
            if ($valPacientes > $maxVal) $maxVal = $valPacientes;
        }
        
        $maxIngresos = max(array_merge($dataIngresos, [1]));
        $stepX = 570 / 6; 
        $pathCitas = ""; $pathPacientes = ""; $pathIngresos = ""; $puntosExtraCitas = "";
        
        for ($i = 0; $i < 7; $i++) {
            $x = 15 + ($i * $stepX);
            $yCitas = 150 - (($dataCitas[$i] / $maxVal) * 100);
            $yPacientes = 150 - (($dataPacientes[$i] / $maxVal) * 100);
            $yIngresos = 150 - (($dataIngresos[$i] / $maxIngresos) * 100);
            
            $prefix = $i === 0 ? 'M' : 'L';
            $pathCitas .= "$prefix $x $yCitas ";
            $pathPacientes .= "$prefix $x $yPacientes ";
            $pathIngresos .= "$prefix $x $yIngresos ";
            
            if ($dataCitas[$i] > 0) {
                $puntosExtraCitas .= '<circle cx="'.$x.'" cy="'.$yCitas.'" r="4" fill="#0061ff" stroke="white" stroke-width="1"/>';
            }
        }

        // 4. Próximas Citas y Recordatorios
        $proximasCitas = Cita::join('paciente', 'cita.PACIENTE_ID_PACIENTE', '=', 'paciente.ID_PACIENTE')
            ->join('usuarios', 'paciente.USUARIOS_ID_USUARIOS', '=', 'usuarios.ID_USUARIOS')
            ->select('cita.ID_CITA', DB::raw("DATE_FORMAT(cita.FECHA_HORA, '%d %b') as fecha_corta"), 'cita.MOTIVO', DB::raw("CONCAT(usuarios.NOMBRES, ' ', usuarios.APELLIDOS) AS paciente_nombre"))
            ->where('cita.FECHA_HORA', '>=', now())->whereIn('cita.ESTADO_CITA_ID', [1, 2])
            ->orderBy('cita.FECHA_HORA', 'ASC')->take(4)->get();

        $recordatorios = Notificacion::where('ESTADO', 'NO_LEIDA')->orderBy('FECHA_ENVIO', 'DESC')->take(4)->get();

        return view('admin.inicio', compact(
            'citasHoy', 'pacientesAtendidos', 'ingresosHoy',
            'estadoCitasHoy', 'proximasCitas', 'recordatorios',
            'diasStr', 'pathCitas', 'pathPacientes', 'pathIngresos', 'puntosExtraCitas'
        ));
    }
}