<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Transaction commerciale (Mobile Money), valide par défaut.
 *
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'operateur_id' => Operateur::factory(),
            'montant' => fake()->numberBetween(1, 500) * 100,
            'commission' => fake()->numberBetween(0, 50) * 10,
            'type' => fake()->randomElement(['depot', 'retrait', 'transfert', 'paiement']),
            'statut' => 'valide',
            'date' => now(),
        ];
    }
}
