<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mesas')->insert([
            ['ubicacion' => 'A', 'numero' => 1, 'cantidad_personas' => 2],
            ['ubicacion' => 'A', 'numero' => 2, 'cantidad_personas' => 2],
            ['ubicacion' => 'A', 'numero' => 3, 'cantidad_personas' => 4],

            ['ubicacion' => 'B', 'numero' => 1, 'cantidad_personas' => 4],
            ['ubicacion' => 'B', 'numero' => 2, 'cantidad_personas' => 6],

            ['ubicacion' => 'C', 'numero' => 1, 'cantidad_personas' => 6],
            ['ubicacion' => 'C', 'numero' => 2, 'cantidad_personas' => 8],

            ['ubicacion' => 'D', 'numero' => 1, 'cantidad_personas' => 8],
        ]);
    }
}
