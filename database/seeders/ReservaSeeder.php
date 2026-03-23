<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservas = [
            ['nombre_solicitante' => 'Juan Pérez',   'fecha' => '2026-03-24', 'hora_inicio' => '12:00', 'hora_fin' => '14:00', 'cantidad_personas' => 2,  'mesas' => [1]],
            ['nombre_solicitante' => 'María García', 'fecha' => '2026-03-24', 'hora_inicio' => '20:00', 'hora_fin' => '22:00', 'cantidad_personas' => 4,  'mesas' => [1, 2]],
            ['nombre_solicitante' => 'Carlos López', 'fecha' => '2026-03-25', 'hora_inicio' => '20:00', 'hora_fin' => '22:00', 'cantidad_personas' => 6,  'mesas' => [1, 2, 3]],
            ['nombre_solicitante' => 'Ana Martínez', 'fecha' => '2026-03-25', 'hora_inicio' => '20:00', 'hora_fin' => '22:00', 'cantidad_personas' => 4,  'mesas' => [4]],
        ];

        foreach ($reservas as $data) {
            $mesas = $data['mesas'];
            unset($data['mesas']);
            $id = DB::table('reservas')->insertGetId($data);
            foreach ($mesas as $mesaId) {
                DB::table('mesa_reserva')->insert(['reserva_id' => $id, 'mesa_id' => $mesaId]);
            }
        }
    }
}
