<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sigco:recordatorios-24h', function () {
    $desde = now()->addHours(23);
    $hasta = now()->addHours(25);

    $citas = DB::table('vista_citas')
        ->where('ESTADO_CITA_ID', 1)
        ->whereBetween('FECHA_HORA', [$desde, $hasta])
        ->get();

    $enviados = 0;
    foreach ($citas as $c) {
        $usuarioId = DB::table('paciente')->where('ID_PACIENTE', $c->PACIENTE_ID_PACIENTE)->value('USUARIOS_ID_USUARIOS');
        if (!$usuarioId) continue;

        $fechaFmt = date('d/m/Y h:i A', strtotime($c->FECHA_HORA));
        $msg = "Recordatorio: tienes una cita odontológica programada para mañana {$fechaFmt} con {$c->NOMBRE_ODONTOLOGO}.";

        $yaEnviado = DB::table('notificaciones')
            ->where('TIPO_NOTIFICACION_ID_TIPO_NOTIFICACION', 2)
            ->where('USUARIOS_ID_USUARIOS', $usuarioId)
            ->where('MENSAJE', $msg)
            ->exists();

        if (!$yaEnviado) {
            \App\Helpers\Notificador::insertar(2, $usuarioId, $msg);
            $enviados++;
        }
    }

    $this->info("Recordatorios 24h enviados: {$enviados}");
})->purpose('Envía recordatorios automáticos 24 horas antes de cada cita pendiente (RQF026)');

Artisan::command('sigco:backup-db', function () {
    $backupDir = storage_path('app/backups');
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $filename = 'sigco_' . date('Y-m-d_His') . '.sql';
    $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;
    $mysqldump = 'C:\\MAMP\\bin\\mysql\\bin\\mysqldump.exe';

    if (!file_exists($mysqldump)) {
        $this->error("No se encontró mysqldump en {$mysqldump}");
        return 1;
    }

    $cmd = sprintf(
        '"%s" --host=%s --port=%s --user=%s --password=%s --routines --triggers %s > "%s"',
        $mysqldump,
        escapeshellarg(config('database.connections.mysql.host')),
        escapeshellarg((string) config('database.connections.mysql.port')),
        escapeshellarg(config('database.connections.mysql.username')),
        escapeshellarg(config('database.connections.mysql.password')),
        escapeshellarg(config('database.connections.mysql.database')),
        $filepath
    );

    exec($cmd, $out, $code);
    if ($code === 0 && file_exists($filepath) && filesize($filepath) > 0) {
        $this->info("Respaldo generado correctamente: {$filepath}");
        return 0;
    }

    $this->error('Error al generar el respaldo de la base de datos.');
    return 1;
})->purpose('Genera copia de seguridad diaria de la base de datos sigco (RQNF001)');

Schedule::command('sigco:recordatorios-24h')->hourly();
Schedule::command('sigco:backup-db')->dailyAt('02:00');
