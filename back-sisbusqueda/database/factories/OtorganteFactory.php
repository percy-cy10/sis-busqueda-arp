<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Otorgante>
 */
class OtorganteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = fake()->randomElement(['natural', 'juridica']);

        return [
            'tipo' => $tipo,
            'nombre' => $tipo === 'natural' ? fake()->firstName() : null,
            'apellido_paterno' => $tipo === 'natural' ? fake()->lastName() : null,
            'apellido_materno' => $tipo === 'natural' ? fake()->lastName() : null,
            'nombre_completo' => $tipo === 'natural'
                ? fake()->firstName() . ' ' . fake()->lastName() . ' ' . fake()->lastName()
                : fake()->company(),
            'razon_social' => $tipo === 'juridica' ? fake()->company() : null,
            'user_id' => User::factory(), // Relación con el modelo User
        ];
    }
}
