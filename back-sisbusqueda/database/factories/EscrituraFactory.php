<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Escritura>
 */
class EscrituraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private $v = 10;
    private $escr = 0;
    private $lib = 1;
    public function definition(): array
    {
        $anio = fake()->numberBetween(1950,2015);
        $mes = fake()->numberBetween(1,12);
        $dia = fake()->numberBetween(1,28);
        if($this->lib === 30) $this->lib = 1;
        return [
            'bien' => fake()->colorName(),
            'subserie_id' => fake()->numberBetween(1,10),
            'anio' => $anio,
            'mes' => $mes ,
            'dia' => $dia,
            'fecha' => strval($anio).'-'.str_pad($mes, 2, '0', STR_PAD_LEFT).'-'.str_pad($dia, 2, '0', STR_PAD_LEFT) ,
            'cod_escritura' => 'E-'.strval(++$this->escr),
            'cod_folioInicial' => 'F-'.strval(++$this->v).fake()->randomElement(array(' V','')),
            'cod_folioFinal' => 'F-'.strval(++$this->v).fake()->randomElement(array(' V','')),
            'libro_id' => $this->escr%3 === 0 ? ++$this->lib : $this->lib,
            // 'observaciones' => fake()->text(),
        ];
    }
}
