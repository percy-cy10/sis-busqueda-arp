<?php

namespace Database\Seeders;

use App\Models\Escritura;
use App\Models\Favorecido;
use App\Models\Libro;
use App\Models\Notario;
use App\Models\Otorgante;
use App\Models\RegistroBusqueda;
use App\Models\RegistroVerificacion;
use App\Models\Solicitante;
use App\Models\Solicitud;
use App\Models\SubSerie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class A_de_factoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // php artisan db:seed --class=A_de_factoriesSeeder
        // Notario::factory(10)->create();
        $archivoJson = 'C:\Users\himka\Downloads\Notarios_Arp.json';

        // Obtener el contenido del archivo JSON
        $jsonContenido = file_get_contents($archivoJson);

        // Decodificar el JSON en un array asociativo
        $data = json_decode($jsonContenido, true);
        // Verificar si la decodificación fue exitosa
        if ($data !== null) {
            foreach ($data as $value) {
                Notario::create([
                    'nombres'=> $value['nombres'],
                    'apellido_paterno'=> $value['apellido_paterno'],
                    'apellido_materno'=> $value['materno'],
                    'nombre_completo'=> $value['nombre_completo'],
                    'año_inicio'=> $value['año_inicio'],
                    'año_final'=> $value['año_final'],
                    'ubigeo_cod'=>  strval($value['ubigeo_cod']),
                ]);
            }
        }
        Libro::factory(30)->create();
        // $this->call(TransSubserieSeeder::class);
        SubSerie::factory(10)->create();
        Escritura::factory(100)->create();
        Favorecido::factory(60)->create();
        Otorgante::factory(60)->create();

        // DB::table('precios')->insert(['monto'=> 9.00 ,'vigente'=>1]);
        Solicitante::factory(100)->create();
        Solicitud::factory(50)->create();
        RegistroBusqueda::factory(50)->create();
        RegistroVerificacion::factory(50)->create();
    }
}
