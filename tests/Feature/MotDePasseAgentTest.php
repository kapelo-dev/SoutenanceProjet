<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRoutePermission;
use App\Models\Agent;
use App\Models\Profil;
use App\Models\Utilisateur;
use App\Support\MotDePasseTemporaire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MotDePasseAgentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Agent avec compte utilisateur ; $temporaire = mot de passe pas encore changé.
     */
    private function agentAvecCompte(string $motDePasse, bool $temporaire): Agent
    {
        $utilisateur = Utilisateur::factory()->create([
            'mot_de_passe' => Hash::make($motDePasse),
            'dernier_connexion' => $temporaire ? null : now()->subDay(),
        ]);

        return Agent::factory()->create(['code_agent' => 'AG' . $utilisateur->id, 'user_id' => $utilisateur->id]);
    }

    private function loginMobile(Agent $agent, string $motDePasse)
    {
        return $this->postJson('/api/mobile/agent/login', [
            'identifiant' => $agent->code_agent,
            'password' => $motDePasse,
        ]);
    }

    public function test_generateur_de_mot_de_passe_temporaire(): void
    {
        $mots = collect(range(1, 200))->map(fn () => MotDePasseTemporaire::generer());

        $this->assertCount(200, $mots->unique());
        foreach ($mots as $mot) {
            $this->assertSame(10, strlen($mot));
            $this->assertMatchesRegularExpression('/^[a-km-zA-HJ-NP-Z2-9]+$/', $mot); // ni 0/O ni 1/l/I
            $this->assertMatchesRegularExpression('/[2-9]/', $mot);
            $this->assertMatchesRegularExpression('/[a-zA-Z]/', $mot);
        }
    }

    public function test_creation_agent_genere_un_mot_de_passe_unique_a_changer(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $this->actingAs(Utilisateur::factory()->create());
        Profil::create(['libelle' => 'Agent', 'niveau' => 0]);

        $creer = fn (string $code, string $telephone) => $this->postJson(route('agents.store-with-kiosque'), [
            'code_agent' => $code,
            'nom' => 'Kodjo',
            'prenom' => 'Ama',
            'telephone' => $telephone,
            'statut' => 'actif',
        ])->assertOk()->json('utilisateur');

        $premier = $creer('AG0101', '90000101');
        $second = $creer('AG0102', '90000102');

        $this->assertNotSame('changeMe', $premier['mot_de_passe']);
        $this->assertNotSame($premier['mot_de_passe'], $second['mot_de_passe']);

        $utilisateur = Agent::where('code_agent', 'AG0101')->sole()->utilisateur;
        $this->assertTrue(Hash::check($premier['mot_de_passe'], $utilisateur->mot_de_passe));
        $this->assertTrue($utilisateur->doitChangerMotDePasse());
    }

    public function test_premiere_connexion_mobile_impose_le_changement(): void
    {
        $agent = $this->agentAvecCompte('Temp2345ab', temporaire: true);

        $login = $this->loginMobile($agent, 'Temp2345ab')
            ->assertOk()
            ->assertJson(['success' => true, 'doit_changer_mot_de_passe' => true, 'dashboard' => null]);
        $entetes = ['Authorization' => 'Bearer ' . $login->json('token')];

        // Toutes les autres routes sont bloquées tant que le mot de passe n'est pas changé
        $this->getJson('/api/mobile/agent/dashboard', $entetes)
            ->assertForbidden()
            ->assertJson(['code' => 'MOT_DE_PASSE_A_CHANGER']);
        $this->postJson('/api/mobile/agent/transactions/999/annuler', [], $entetes)
            ->assertForbidden()
            ->assertJson(['code' => 'MOT_DE_PASSE_A_CHANGER']);

        // Garder le mot de passe temporaire est refusé
        $this->postJson('/api/mobile/agent/change-password', [
            'current_password' => 'Temp2345ab',
            'password' => 'Temp2345ab',
            'password_confirmation' => 'Temp2345ab',
        ], $entetes)->assertJsonValidationErrors('password');

        $this->postJson('/api/mobile/agent/change-password', [
            'current_password' => 'Temp2345ab',
            'password' => 'MonVraiMdp2026',
            'password_confirmation' => 'MonVraiMdp2026',
        ], $entetes)
            ->assertOk()
            ->assertJson(['success' => true, 'doit_changer_mot_de_passe' => false])
            ->assertJsonStructure(['dashboard' => ['stats']]);

        // Débloqué, avec le même token
        $this->getJson('/api/mobile/agent/dashboard', $entetes)->assertOk();
        $this->assertFalse($agent->utilisateur->fresh()->doitChangerMotDePasse());

        // Même état côté web : pas de second changement imposé
        $this->post('/login', ['identifiant' => $agent->code_agent, 'password' => 'MonVraiMdp2026'])
            ->assertRedirect();
        $this->get(route('password.change'))->assertRedirect(); // déjà changé : renvoyé vers l'accueil
    }

    public function test_connexion_mobile_normale(): void
    {
        $agent = $this->agentAvecCompte('MonVraiMdp2026', temporaire: false);
        $avant = $agent->utilisateur->dernier_connexion;

        $this->loginMobile($agent, 'MonVraiMdp2026')
            ->assertOk()
            ->assertJson(['doit_changer_mot_de_passe' => false])
            ->assertJsonStructure(['dashboard' => ['stats']]);

        $this->assertTrue($agent->utilisateur->fresh()->dernier_connexion->gt($avant));
    }

    public function test_reinitialisation_par_admin_impose_le_changement(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $this->actingAs(Utilisateur::factory()->create());
        $agent = $this->agentAvecCompte('MonVraiMdp2026', temporaire: false);

        $this->postJson(route('utilisateurs.reset-password', $agent->utilisateur), [
            'nouveau_mot_de_passe' => 'Reset2345ab',
            'nouveau_mot_de_passe_confirmation' => 'Reset2345ab',
        ])->assertOk();

        $this->loginMobile($agent, 'Reset2345ab')->assertJson(['doit_changer_mot_de_passe' => true]);
    }

    public function test_changement_web_refuse_le_meme_mot_de_passe(): void
    {
        $utilisateur = Utilisateur::factory()->premiereConnexion()->create(['mot_de_passe' => Hash::make('Temp2345ab')]);

        $this->actingAs($utilisateur)
            ->post(route('password.change'), ['password' => 'Temp2345ab', 'password_confirmation' => 'Temp2345ab'])
            ->assertSessionHasErrors('password');

        $this->assertTrue($utilisateur->fresh()->doitChangerMotDePasse());
    }

    public function test_migration_force_le_changement_des_agents_encore_en_changeme(): void
    {
        $parDefaut = $this->agentAvecCompte('changeMe', temporaire: false);
        $change = $this->agentAvecCompte('MonVraiMdp2026', temporaire: false);
        $nonAgent = Utilisateur::factory()->create(['mot_de_passe' => Hash::make('changeMe')]);

        $migration = require database_path('migrations/2026_10_06_130000_force_password_change_for_default_agent_passwords.php');
        $migration->up();

        $this->assertTrue($parDefaut->utilisateur->fresh()->doitChangerMotDePasse());
        $this->assertFalse($change->utilisateur->fresh()->doitChangerMotDePasse());
        $this->assertFalse($nonAgent->fresh()->doitChangerMotDePasse()); // seuls les comptes d'agents
    }
}
