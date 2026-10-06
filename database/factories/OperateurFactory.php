<?php

namespace Database\Factories;

use App\Models\Operateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Operateur>
 */
class OperateurFactory extends Factory
{
    protected $model = Operateur::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('OP???')),
            'libelle' => fake()->company(),
            'couleur' => fake()->hexColor(),
            'statut' => 'actif',
            'ordre' => 0,
        ];
    }
}
