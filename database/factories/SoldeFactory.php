<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Solde;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Solde espèce par défaut (operateur_id null) ; ->virtuel($operateurId) pour un solde opérateur.
 *
 * @extends Factory<Solde>
 */
class SoldeFactory extends Factory
{
    protected $model = Solde::class;

    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'operateur_id' => null,
            'type' => 'espece',
            'montant' => fake()->numberBetween(0, 1000) * 100,
            'date' => now(),
        ];
    }

    public function virtuel(int $operateurId): static
    {
        return $this->state(fn () => ['type' => 'virtuel', 'operateur_id' => $operateurId]);
    }
}
