<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransNotarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notarioResults = DB::table('anterior')->select('notario')->whereNotNull('notario');
        $notarioResults->union(
            DB::table('anterior_2')->select('notario')->whereNotNull('notario')
        );
        $notarioResults->union(
            DB::table('nuevo')->select('notario')->whereNotNull('notario')
        );
        $notarioResults->union(
            DB::table('sia')->select('notario AS notario')->whereNotNull('notario')
        );
        $notarioResults->union(
            DB::table('sis2018_2')->select('notario')->whereNotNull('notario')
        );
        $notarioResults = $notarioResults->orderByDesc('notario')->get();

        $uniqueNotarios = [];
        foreach ($notarioResults as $key => $value) {
            $trimmedNotario = preg_replace('/\s+/', ' ', $value->notario);
            $trimmedNotario = trim($trimmedNotario);
            $uniqueNotarios[$trimmedNotario] = true; // Usar el nombre como clave para asegurar la unicidad
        }

        foreach (array_keys($uniqueNotarios) as $notario) {
            DB::table('notarios')->insert(['nombre_completo' => $notario]);
        }
    }
}
