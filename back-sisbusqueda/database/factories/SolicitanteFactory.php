<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solicitante>
 */
class SolicitanteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed2
     */
    protected $v=2561;
    public function definition(): array
    {
        $tipoDoc = fake()->randomElement(array('DNI','RUC'));
        return [
            'nombres' => fake()->name(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'nombre_completo' => fake()->name().' '.fake()->lastName(),
            'asunto' => fake()->company().' '.fake()->lastName(),
            'tipo_documento' => $tipoDoc,
            'num_documento' => $tipoDoc==='DNI' ?'67'.str_pad(++$this->v, 6, '0', STR_PAD_LEFT):'10'.str_pad(++$this->v, 11, '0', STR_PAD_LEFT),
            'direccion' => fake()->address(),
            'correo' => fake()->email(),
            'celular' => fake()->phoneNumber(),
            'ubigeo_cod' => fake()->randomElement(array('210101','210102','210103','210104','210105','210106')),
        ];
    }
}
