<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Agent;
use App\Models\Kiosque;
use App\Models\Utilisateur;
use App\Models\Solde;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de création d'un agent avec génération automatique du code
     */
    public function test_agent_genere_code_automatiquement()
    {
        $utilisateur = Utilisateur::factory()->create();
        $kiosque = Kiosque::factory()->create();

        $agent = Agent::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '90123456',
            'utilisateur_id' => $utilisateur->id,
            'kiosque_id' => $kiosque->id,
            'statut' => 'actif',
        ]);

        $this->assertNotNull($agent->code_agent);
        $this->assertStringStartsWith('AG', $agent->code_agent);
    }

    /**
     * Test de la relation avec Utilisateur
     */
    public function test_agent_appartient_a_un_utilisateur()
    {
        $utilisateur = Utilisateur::factory()->create(['nom' => 'Martin']);
        $agent = Agent::factory()->create(['utilisateur_id' => $utilisateur->id]);

        $this->assertInstanceOf(Utilisateur::class, $agent->utilisateur);
        $this->assertEquals('Martin', $agent->utilisateur->nom);
    }

    /**
     * Test de la relation avec Kiosque
     */
    public function test_agent_appartient_a_un_kiosque()
    {
        $kiosque = Kiosque::factory()->create(['nom' => 'Kiosque Centre']);
        $agent = Agent::factory()->create(['kiosque_id' => $kiosque->id]);

        $this->assertInstanceOf(Kiosque::class, $agent->kiosque);
        $this->assertEquals('Kiosque Centre', $agent->kiosque->nom);
    }

    /**
     * Test du scope actif
     */
    public function test_scope_actif_filtre_agents_actifs()
    {
        Agent::factory()->create(['statut' => 'actif']);
        Agent::factory()->create(['statut' => 'inactif']);
        Agent::factory()->create(['statut' => 'suspendu']);

        $actifs = Agent::actif()->get();

        $this->assertCount(1, $actifs);
        $this->assertEquals('actif', $actifs->first()->statut);
    }

    /**
     * Test de validation du numéro de téléphone
     */
    public function test_telephone_doit_etre_valide()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $utilisateur = Utilisateur::factory()->create();
        $kiosque = Kiosque::factory()->create();

        Agent::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '123', // Téléphone trop court
            'utilisateur_id' => $utilisateur->id,
            'kiosque_id' => $kiosque->id,
            'statut' => 'actif',
        ]);
    }

    /**
     * Test de la relation avec les soldes
     */
    public function test_agent_a_plusieurs_soldes()
    {
        $agent = Agent::factory()->create();

        Solde::factory()->count(3)->create(['agent_id' => $agent->id]);

        $this->assertCount(3, $agent->soldes);
    }
}
