<?php
namespace App\Controllers\administrador;

use App\Models\administrador\DashboardAdminModel;

class DashboardAdminController {

    private $modelo;

    public function __construct() {
        $this->modelo = new DashboardAdminModel();
    }

    public function index() {
        if (
            !isset($_SESSION['usuario_id']) ||
            !in_array($_SESSION['usuario_rol'], [1, 4])
        ) {
            header("Location: /LOGIN_ORIGINAL/login");
            exit;
        }

        $hoy = date('Y-m-d');

        // Consultas al modelo
        $citasHoy            = $this->modelo->contarCitasHoy($hoy);
        $pacientesAtendidos  = $this->modelo->contarPacientesAtendidosHoy($hoy);
        $tratamientosHoy     = $this->modelo->contarTratamientosHoy($hoy);
        $ingresosHoy         = $this->modelo->obtenerIngresosHoy($hoy);
        
        $proximasCitas       = $this->modelo->obtenerProximasCitas($hoy, 4);
        $recordatorios       = $this->modelo->obtenerRecordatorios(4);
        $estadoCitasHoy      = $this->modelo->obtenerEstadoCitasHoy($hoy);

        $actividad           = $this->modelo->obtenerActividadUltimos7Dias($hoy);

        // Formatear datos para el gráfico SVG
        $diasStr = [];
        $puntosCitas = [];
        $puntosPacientes = [];
        $puntosIngresos = [];
        
        $maxVal = 1; // Para evitar división por cero
        $dataCitas = [];
        $dataPacientes = [];
        $dataIngresos = [];

        for ($i = 0; $i < 7; $i++) {
            $fechaCur = date('Y-m-d', strtotime("+$i days", strtotime($actividad['inicio'])));
            $diasStr[] = date('d M', strtotime($fechaCur));
            
            $valCitas = 0; $valPacientes = 0; $valIngresos = 0;
            foreach ($actividad['citas'] as $c) if ($c['fecha'] === $fechaCur) $valCitas = $c['cantidad'];
            foreach ($actividad['pacientes'] as $p) if ($p['fecha'] === $fechaCur) $valPacientes = $p['cantidad'];
            foreach ($actividad['ingresos'] as $ing) if ($ing['fecha'] === $fechaCur) $valIngresos = $ing['cantidad'];
            
            // Ingresos pueden ser números muy grandes (miles), necesitamos normalizarlos para compararlos visualmente con citas, 
            // o simplemente usar su valor base (e.g. dividido por un factor, pero aquí los maxVal controlan la altura).
            $dataCitas[] = $valCitas;
            $dataPacientes[] = $valPacientes;
            $dataIngresos[] = $valIngresos;
            
            if ($valCitas > $maxVal) $maxVal = $valCitas;
            if ($valPacientes > $maxVal) $maxVal = $valPacientes;
            // Para que ingresos no aplaste a los demás, calculamos su maxVal por separado y lo normalizamos
        }
        
        $maxIngresos = max(array_merge($dataIngresos, [1]));

        // Calcular puntos (SVG width: 600, height: 160, padding Y: 30 a 140 -> 110px de uso real)
        $svgWidth = 570; // Espacio entre puntos (0 a 570, inicio en 15)
        $stepX = $svgWidth / 6; 
        
        $pathCitas = "";
        $pathPacientes = "";
        $pathIngresos = "";
        $puntosExtraCitas = ""; // Para los círculos
        
        for ($i = 0; $i < 7; $i++) {
            $x = 15 + ($i * $stepX);
            
            // Y = 150 - (valor / maxVal * 100)  (rango de Y es de 50 a 150 aprox)
            $yCitas = 150 - (($dataCitas[$i] / $maxVal) * 100);
            $yPacientes = 150 - (($dataPacientes[$i] / $maxVal) * 100);
            $yIngresos = 150 - (($dataIngresos[$i] / $maxIngresos) * 100);
            
            $prefix = $i === 0 ? 'M' : 'L';
            // Simple line paths for now (simpler than bezier curves Q/T)
            $pathCitas .= "$prefix $x $yCitas ";
            $pathPacientes .= "$prefix $x $yPacientes ";
            $pathIngresos .= "$prefix $x $yIngresos ";
            
            if ($dataCitas[$i] > 0) {
                $puntosExtraCitas .= '<circle cx="'.$x.'" cy="'.$yCitas.'" r="4" fill="#0061ff" stroke="white" stroke-width="1"/>';
            }
        }

        // Cargar la vista pasándole los datos extraídos
        require_once __DIR__ . '/../../Views/administrador/dashboard.php';
    }
}
