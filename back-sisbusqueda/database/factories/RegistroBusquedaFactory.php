<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistroBusqueda>
 */
class RegistroBusquedaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private $i=0;
    private $v=0;
    public function definition(): array
    {
        return [
            'solicitud_id' => ++$this->i,
            'estado' => $this->i%2===0 ? 1 : 0,
            'user_id' =>  1 ,
            'cod_protocolo' => 'P-'.strval(++$this->v),
            'cod_escritura' => 'E-'.strval(++$this->v),
            'cod_folioInicial' => 'F-'.strval(++$this->v),
            'cod_folioFinal' => 'F-'.strval(++$this->v),
            'observaciones' => fake()->text(),
        ];
    }
}
