<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRoutePermission;
use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Transaction;
use App\Models\Utilisateur;
use App\Rules\TelephoneValide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CorrectifsSecuriteEtPagesTest extends TestCase
{
    use RefreshDatabase;

    // --- Connexion : plus de comparaison en clair -----------------------------------------------

    public function test_le_hash_stocke_ne_permet_pas_de_se_connecter(): void
    {
        $utilisateur = Utilisateur::factory()->create(['email' => 'hash@test.local']);

        $this->post('/login', [
            'identifiant' => 'hash@test.local',
            'password' => $utilisateur->mot_de_passe, // le hash lui-même
        ])->assertSessionHasErrors('identifiant');

        $this->assertGuest();
    }

    public function test_mot_de_passe_en_clair_en_base_ne_provoque_pas_d_erreur(): void
    {
        $utilisateur = Utilisateur::factory()->create(['email' => 'clair@test.local']);
        DB::table('utilisateurs')->where('id', $utilisateur->id)->update(['mot_de_passe' => 'secret123']);

        $this->post('/login', ['identifiant' => 'clair@test.local', 'password' => 'secret123'])
            ->assertSessionHasErrors('identifiant');

        $this->assertGuest();
    }

    public function test_la_migration_hache_les_mots_de_passe_en_clair(): void
    {
        $clair = Utilisateur::factory()->create(['email' => 'ancien@test.local']);
        $hache = Utilisateur::factory()->create();
        $hashAvant = $hache->mot_de_passe;
        DB::table('utilisateurs')->where('id', $clair->id)->update(['mot_de_passe' => 'ancien-mdp']);

        $migration = require database_path('migrations/2026_10_06_120000_hash_remaining_plaintext_passwords.php');
        $migration->up();

        $this->assertTrue(Hash::check('ancien-mdp', $clair->fresh()->mot_de_passe));
        $this->assertSame($hashAvant, $hache->fresh()->mot_de_passe); // hash existant inchangé

        // Le compte migré se connecte toujours avec son mot de passe
        $this->post('/login', ['identifiant' => 'ancien@test.local', 'password' => 'ancien-mdp']);
        $this->assertAuthenticatedAs($clair->fresh());
    }

    // --- Téléphone des agents -------------------------------------------------------------------

    public function test_regle_telephone(): void
    {
        $valide = fn ($numero) => Validator::make(['t' => $numero], ['t' => [new TelephoneValide]])->passes();

        foreach (['90123456', '+228 90 12 34 56', '90-12-34-56', '228.90.12.34.56'] as $numero) {
            $this->assertTrue($valide($numero), $numero);
        }
        foreach (['123', '9012345', 'abcdefgh', '90 12 34 5a', '+', '1234567890123456', '++22890123456'] as $numero) {
            $this->assertFalse($valide($numero), $numero);
        }
    }

    public function test_mise_a_jour_agent_refuse_un_telephone_trop_court(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $this->actingAs(Utilisateur::factory()->create());
        $agent = Agent::factory()->create();

        $this->putJson(route('agents.update', $agent), [
            'code_agent' => 'AG9999',
            'nom' => 'Test',
            'prenom' => 'Agent',
            'telephone' => '123',
            'statut' => 'actif',
        ])->assertJsonValidationErrors('telephone');
    }

    // --- Pages des transactions -----------------------------------------------------------------

    private function connecter(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $this->actingAs(Utilisateur::factory()->create());
    }

    public function test_page_detail_transaction(): void
    {
        $this->connecter();
        $transaction = Transaction::factory()->create([
            'client_nom' => '<script>alert(1)</script>',
            'statut' => 'valide',
        ]);

        $this->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee($transaction->reference)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee(route('transactions.edit', $transaction)); // validée : pas de lien Modifier
    }

    public function test_page_creation_et_enregistrement(): void
    {
        $this->connecter();
        $agent = Agent::factory()->create();
        $operateur = Operateur::factory()->create();

        $this->get(route('transactions.create'))->assertOk()->assertSee('Nouvelle transaction');

        $response = $this->post(route('transactions.store'), [
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'type' => 'depot',
            'statut' => 'en_attente',
            'montant' => 2500,
        ]);

        $transaction = Transaction::sole();
        $response->assertRedirect(route('transactions.show', $transaction));
        $this->get(route('transactions.show', $transaction))->assertOk()->assertSee('2 500 FCFA');
    }

    public function test_modification_transaction_en_attente(): void
    {
        $this->connecter();
        $transaction = Transaction::factory()->create(['statut' => 'en_attente', 'montant' => 1000]);

        $this->get(route('transactions.edit', $transaction))->assertOk()->assertSee('Modifier la transaction');

        $this->put(route('transactions.update', $transaction), [
            'agent_id' => $transaction->agent_id,
            'operateur_id' => $transaction->operateur_id,
            'type' => $transaction->type,
            'statut' => 'en_attente',
            'montant' => 1500,
        ])->assertRedirect(route('transactions.show', $transaction));

        $this->assertEquals(1500, $transaction->fresh()->montant);
    }

    public function test_transaction_validee_non_modifiable(): void
    {
        $this->connecter();
        $transaction = Transaction::factory()->create(['statut' => 'valide']);

        $this->get(route('transactions.edit', $transaction))
            ->assertRedirect(route('transactions.show', $transaction))
            ->assertSessionHas('error');
    }

    public function test_navigation_ajax_renvoie_le_contenu_seul(): void
    {
        $this->connecter();
        $transaction = Transaction::factory()->create();

        $response = $this->get(route('transactions.show', $transaction), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $this->assertStringNotContainsString('<!DOCTYPE', $response->getContent());
        $this->assertStringContainsString($transaction->reference, $response->getContent());
    }
}
