<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sql = "SELECT c.ODONTOLOGO_ID_ODONTOLOGO, DATE_FORMAT(f.FECHA_EMISION, '%Y-%m') as mes, COUNT(f.ID_FACTURA) as cant, SUM(f.TOTAL) as total
FROM factura f
INNER JOIN cita c ON f.CITA_ID_CITA = c.ID_CITA
GROUP BY c.ODONTOLOGO_ID_ODONTOLOGO, mes
ORDER BY mes DESC";

print_r(\Illuminate\Support\Facades\DB::select($sql));
