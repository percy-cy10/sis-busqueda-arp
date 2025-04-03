<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistroVerificacion>
 */
class RegistroVerificacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private $i=0;
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'RB_id_derivado' => ++$this->i,
            'estado' => fake()->boolean(),
            'observaciones' => fake()->text(),
        ];
    }
}
