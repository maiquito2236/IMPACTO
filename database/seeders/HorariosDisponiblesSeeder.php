<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class HorariosDisponiblesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            12 => ['name' => 'Mariana Wilches', 'days' => [1, 3, 5]],       // Lunes, Miércoles, Viernes
            2  => ['name' => 'Martha Romero',   'days' => [2, 4, 6]],       // Martes, Jueves, Sábado
            6  => ['name' => 'Manuel Turizo',   'days' => [1, 2, 3, 4, 5]], // Lunes a Viernes
            14 => ['name' => 'Oscar Torres',    'days' => [1, 2, 3, 4, 5]], // Lunes a Viernes
            15 => ['name' => 'Lis Pena',        'days' => [1, 2, 3, 4, 5]], // Lunes a Viernes
            10 => ['name' => 'Dylan Castañeda', 'days' => [2, 4]],          // Martes, Jueves
            18 => ['name' => 'Maria Mar',       'days' => [1, 3, 5]],       // Lunes, Miércoles, Viernes
            13 => ['name' => 'German Gomez',    'days' => [1, 3]],          // Lunes, Miércoles
        ];

        $slotsMorning = [
            ['08:00:00', '09:00:00', 'Mañana'],
            ['09:00:00', '10:00:00', 'Mañana'],
            ['10:00:00', '11:00:00', 'Mañana'],
            ['11:00:00', '12:00:00', 'Mañana'],
        ];

        $slotsAfternoon = [
            ['14:00:00', '15:00:00', 'Tarde'],
            ['15:00:00', '16:00:00', 'Tarde'],
            ['16:00:00', '17:00:00', 'Tarde'],
            ['17:00:00', '18:00:00', 'Tarde'],
        ];

        $startDate = new DateTime('now');
        $endDate = (clone $startDate)->modify('+60 days');

        $inserted = 0;
        $curr = clone $startDate;

        while ($curr <= $endDate) {
            $dow = (int) $curr->format('N'); // 1 = Mon, 7 = Sun
            $fechaStr = $curr->format('Y-m-d');
            $dayNames = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
            $dayName = $dayNames[$dow - 1];

            if ($dow !== 7) { // No domingos
                foreach ($doctors as $docId => $docInfo) {
                    if (in_array($dow, $docInfo['days'])) {
                        $daySlots = $slotsMorning;
                        if ($dow !== 6) { // Sábados solo mañana
                            $daySlots = array_merge($daySlots, $slotsAfternoon);
                        }

                        foreach ($daySlots as $s) {
                            $exists = DB::table('horario_disponibilidad')
                                ->where('ODONTOLOGO_ID_ODONTOLOGO', $docId)
                                ->where('FECHA', $fechaStr)
                                ->where('HORA_INICIO', $s[0])
                                ->exists();

                            if (!$exists) {
                                DB::table('horario_disponibilidad')->insert([
                                    'ODONTOLOGO_ID_ODONTOLOGO' => $docId,
                                    'FECHA' => $fechaStr,
                                    'HORA_INICIO' => $s[0],
                                    'HORA_FIN' => $s[1],
                                    'ESTADO' => 'Disponible',
                                    'JORNADA' => $s[2],
                                    'DIA' => $dayName,
                                ]);
                                $inserted++;
                            }
                        }
                    }
                }
            }
            $curr->modify('+1 day');
        }

        $this->command->info("Seeder completado. Se insertaron {$inserted} nuevos horarios disponibles.");
    }
}
