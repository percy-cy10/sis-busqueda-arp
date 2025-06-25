<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nivel;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Nivel::create(['nombre' => 'Archivo Central']);
        Nivel::create(['nombre' => 'Archivo Intermedio']);
        Nivel::create(['nombre' => 'Archivo Histórico']);
    }
}
