<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Area::updateOrCreate([
            'nombre' => 'AREA DE CAJA',
        ]);
        Area::updateOrCreate([
            'nombre' => 'AREA DE BUSQUEDA',
        ]);
        Area::updateOrCreate([
            'nombre' => 'AREA DE VERIFICACION',
        ]);
        Area::updateOrCreate([
            'nombre' => 'AREA DE DIRECCION',
        ]);
        Area::updateOrCreate([
            'nombre' => 'AREA DE SUPERVISION',
        ]);
    }
}
