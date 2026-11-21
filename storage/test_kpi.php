<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sql = "SELECT 
    (SELECT IFNULL(SUM(MONTO), 0) FROM pago WHERE DATE(FECHA_PAGO) = CURDATE() AND ESTADO = 'PAGADO') as ingresos_dia,
    (SELECT IFNULL(SUM(MONTO), 0) FROM pago WHERE MONTH(FECHA_PAGO) = MONTH(CURDATE()) AND YEAR(FECHA_PAGO) = YEAR(CURDATE()) AND ESTADO = 'PAGADO') as ingresos_mes,
    (SELECT IFNULL(SUM(p.MONTO), 0) 
     FROM pago p 
     JOIN factura f ON p.FACTURA_ID_FACTURA = f.ID_FACTURA 
     WHERE MONTH(p.FECHA_PAGO) = MONTH(CURDATE()) 
     AND YEAR(p.FECHA_PAGO) = YEAR(CURDATE()) 
     AND p.ESTADO = 'PAGADO'
     AND p.MONTO < f.TOTAL) as abonos_mes,
    (SELECT IFNULL(SUM(f.TOTAL - IFNULL((SELECT SUM(MONTO) FROM pago p2 WHERE p2.FACTURA_ID_FACTURA = f.ID_FACTURA AND p2.ESTADO = 'PAGADO'), 0)), 0) 
     FROM factura f
     WHERE f.TOTAL > IFNULL((SELECT SUM(MONTO) FROM pago p3 WHERE p3.FACTURA_ID_FACTURA = f.ID_FACTURA AND p3.ESTADO = 'PAGADO'), 0)) as deudas_pendientes";

print_r(\Illuminate\Support\Facades\DB::select($sql));
