<?php

namespace Database\Factories;

use App\Models\Notario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Libro>
 */
class LibroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'protocolo' => 'P-' . fake()->unique()->numerify('######'),
            'estado' => fake()->boolean(),
            'user_id' => 1,
            'notario_id' => Notario::factory(),
        ];
    }
}
