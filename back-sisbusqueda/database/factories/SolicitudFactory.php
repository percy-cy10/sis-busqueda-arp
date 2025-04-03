<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solicitud>
 */
class SolicitudFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private $v = 10;
    private $pro = 0;
    public function definition(): array
    {
        $anio = fake()->numberBetween(1950,2015);
        $mes = fake()->numberBetween(1,12);
        $dia = fake()->numberBetween(1,28);
        return [
            'notario_id' => fake()->numberBetween(1,10),
            'subserie_id' => fake()->numberBetween(1,10),
            'solicitante_id' => fake()->numberBetween(1,100),
            'otorgantes' => fake()->name().' '.fake()->lastName(),
            'favorecidos' => fake()->name().' '.fake()->lastName(),
            'anio' => $anio,
            'mes' => $mes ,
            'dia' => $dia,
            'fecha' => strval($anio).'-'.str_pad($mes, 2, '0', STR_PAD_LEFT).'-'.str_pad($dia, 2, '0', STR_PAD_LEFT) ,
            'bien' => fake()->colorName(),
            'mas_datos' => 'P-'.strval(++$this->pro).'|'.'E-'.strval(++$this->v).'|'.'F-'.strval(++$this->v).'|'.fake()->text(),
            'pago_busqueda' => 9,
            'area_id' => 2,
            'ubigeo_cod' => fake()->randomElement(array('210101','210102','210103','210104','210105','210106')),
            'estado' => 'Busqueda',
            'user_id' => 1,
            'created_at' => \now(),
            'updated_at' => \now(),
        ];
    }
}
