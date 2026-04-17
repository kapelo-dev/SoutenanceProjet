<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\Agent;
use App\Models\Operateur;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de création d'une transaction avec génération automatique de référence
     */
    public function test_transaction_genere_reference_automatiquement()
    {
        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create();

        $transaction = Transaction::create([
            'montant' => 10000,
            'type' => 'depot',
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'statut' => 'valide',
        ]);

        $this->assertNotNull($transaction->reference);
        $this->assertStringStartsWith('TXN-', $transaction->reference);
        $this->assertEquals(14, strlen($transaction->reference)); // TXN- + 10 caractères
    }

    /**
     * Test de création d'une transaction avec génération automatique d'UUID
     */
    public function test_transaction_genere_uuid_automatiquement()
    {
        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create();

        $transaction = Transaction::create([
            'montant' => 5000,
            'type' => 'retrait',
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'statut' => 'valide',
        ]);

        $this->assertNotNull($transaction->uid);
        $this->assertEquals(36, strlen($transaction->uid)); // Format UUID standard
    }

    /**
     * Test du scope valide
     */
    public function test_scope_valide_filtre_transactions_validees()
    {
        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create();

        Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'statut' => 'valide'
        ]);
        Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'statut' => 'annule'
        ]);
        Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'statut' => 'en_attente'
        ]);

        $validees = Transaction::valide()->get();

        $this->assertCount(1, $validees);
        $this->assertEquals('valide', $validees->first()->statut);
    }

    /**
     * Test du scope depot
     */
    public function test_scope_depot_filtre_depots()
    {
        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create();

        Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'type' => 'depot'
        ]);
        Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'type' => 'retrait'
        ]);

        $depots = Transaction::depot()->get();

        $this->assertCount(1, $depots);
        $this->assertEquals('depot', $depots->first()->type);
    }

    /**
     * Test de la relation avec Agent
     */
    public function test_transaction_appartient_a_un_agent()
    {
        $agent = Agent::factory()->create(['nom' => 'Dupont']);
        $operateur = Operateur::factory()->create();

        $transaction = Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
        ]);

        $this->assertInstanceOf(Agent::class, $transaction->agent);
        $this->assertEquals('Dupont', $transaction->agent->nom);
    }

    /**
     * Test de la relation avec Operateur
     */
    public function test_transaction_appartient_a_un_operateur()
    {
        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create(['libelle' => 'YAS']);

        $transaction = Transaction::factory()->create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
        ]);

        $this->assertInstanceOf(Operateur::class, $transaction->operateur);
        $this->assertEquals('YAS', $transaction->operateur->libelle);
    }

    /**
     * Test de validation du montant minimum
     */
    public function test_montant_doit_etre_positif()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create();

        Transaction::create([
            'montant' => -1000, // Montant négatif
            'type' => 'depot',
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'statut' => 'valide',
        ]);
    }
}
