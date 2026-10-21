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

        $reponse = $this->postJson(route('utilisateurs.reset-password', $agent->utilisateur))
            ->assertOk()
            ->assertJsonPath('utilisateur.identifiant', $agent->code_agent);
        $motDePasse = $reponse->json('utilisateur.mot_de_passe');

        $this->assertNotSame('MonVraiMdp2026', $motDePasse);
        $this->assertTrue($agent->utilisateur->fresh()->doitChangerMotDePasse());
        $this->loginMobile($agent, 'MonVraiMdp2026')->assertUnauthorized();
        $this->loginMobile($agent, $motDePasse)->assertJson(['doit_changer_mot_de_passe' => true]);
    }

    public function test_regeneration_impose_le_changement_a_un_utilisateur_web_deja_connecte(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $admin = Utilisateur::factory()->create();
        $utilisateur = Utilisateur::factory()->create(['email' => 'web@test.local']);

        $this->actingAs($admin)
            ->postJson(route('utilisateurs.reset-password', $utilisateur))
            ->assertOk()
            ->assertJsonPath('utilisateur.identifiant', 'web@test.local');

        // Sa session déjà ouverte est renvoyée vers le changement de mot de passe
        $this->actingAs($utilisateur->fresh())->get('/dashboard')->assertRedirect(route('password.change'));
    }

    public function test_creation_utilisateur_genere_un_mot_de_passe_temporaire(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $profil = Profil::create(['libelle' => 'Test creation', 'niveau' => 0]);

        $reponse = $this->actingAs(Utilisateur::factory()->create())->postJson(route('utilisateurs.store'), [
            'nom' => 'Nouvel',
            'prenom' => 'Utilisateur',
            'email' => 'nouvel@test.local',
            'statut' => 'actif',
            'profil_id' => $profil->id,
            'mot_de_passe' => 'Choisi2345ab', // ignoré : plus choisi par l'administrateur
            'mot_de_passe_confirmation' => 'Choisi2345ab',
        ])->assertOk()->assertJsonPath('utilisateur.identifiant', 'nouvel@test.local');

        $cree = Utilisateur::where('email', 'nouvel@test.local')->firstOrFail();
        $this->assertTrue(Hash::check($reponse->json('utilisateur.mot_de_passe'), $cree->mot_de_passe));
        $this->assertFalse(Hash::check('Choisi2345ab', $cree->mot_de_passe));
        $this->assertTrue($cree->doitChangerMotDePasse());
    }

    public function test_modifier_un_utilisateur_ne_change_pas_son_mot_de_passe(): void
    {
        $this->withoutMiddleware(CheckRoutePermission::class);
        $utilisateur = Utilisateur::factory()->create(['mot_de_passe' => Hash::make('Garde2345ab')]);
        $profil = Profil::create(['libelle' => 'Test modif', 'niveau' => 0]);

        $this->actingAs(Utilisateur::factory()->create())->put(route('utilisateurs.update', $utilisateur), [
            'nom' => 'Nouveau',
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            'statut' => 'actif',
            'profils' => [$profil->id],
            'mot_de_passe' => 'Pirate2345ab',
            'mot_de_passe_confirmation' => 'Pirate2345ab',
        ])->assertRedirect(route('utilisateurs.index'));

        $this->assertSame('Nouveau', $utilisateur->fresh()->nom);
        $this->assertTrue(Hash::check('Garde2345ab', $utilisateur->fresh()->mot_de_passe));
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
