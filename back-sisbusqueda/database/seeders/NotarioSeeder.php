<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notario;
use Illuminate\Support\Facades\File;

class NotarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = public_path('Notarios_Arp.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("El archivo Notarios_Arp.json no se encuentra en la carpeta public.");
            return;
        }

        $json = File::get($jsonPath);
        $notarios = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Error al decodificar el JSON: " . json_last_error_msg());
            return;
        }

        foreach ($notarios as $notario) {
            Notario::create([
                'nombres' => $notario['nombres'] ?? null,
                'apellido_paterno' => $notario['apellido_paterno'] ?? null,
                'apellido_materno' => $notario['apellido_materno'] ?? null,
                'nombre_completo' => $notario['nombre_completo'] ?? null,
                'año_inicio' => $notario['año_inicio'] ?? null,
                'año_final' => $notario['año_final'] ?? null,
                'ubigeo_cod' => $notario['ubigeo_cod'] ?? null,
            ]);
        }

        $this->command->info(count($notarios) . " notarios insertados correctamente.");
    }
}
