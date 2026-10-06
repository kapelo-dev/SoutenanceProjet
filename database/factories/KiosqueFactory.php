<?php

namespace Database\Factories;

use App\Models\Kiosque;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kiosque>
 */
class KiosqueFactory extends Factory
{
    protected $model = Kiosque::class;

    public function definition(): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'code' => 'K' . fake()->unique()->numerify('#####'),
            'nom' => 'Kiosque ' . fake()->streetName(),
            'quartier' => fake()->randomElement(['Tokoin', 'Bè', 'Agoè', 'Adidogomé']),
            'ville' => 'Lomé',
            'statut' => 'actif',
            'capacite_agents' => 2,
        ];
    }
}
