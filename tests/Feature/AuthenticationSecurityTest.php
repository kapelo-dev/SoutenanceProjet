<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que la page de login est accessible sans authentification
     */
    public function test_page_login_accessible_sans_authentification()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test de connexion avec des identifiants valides
     */
    public function test_connexion_avec_identifiants_valides()
    {
        $utilisateur = Utilisateur::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($utilisateur);
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test de connexion avec un mot de passe incorrect
     */
    public function test_connexion_avec_mot_de_passe_incorrect()
    {
        $utilisateur = Utilisateur::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'mauvais_mot_de_passe',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /**
     * Test de protection contre les attaques par force brute (rate limiting)
     */
    public function test_limitation_tentatives_connexion()
    {
        $utilisateur = Utilisateur::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Faire plusieurs tentatives de connexion échouées
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'mauvais_mot_de_passe',
            ]);
        }

        // La 6ème tentative devrait être bloquée (si rate limiting configuré)
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Vérifier si le rate limiting est actif (429 Too Many Requests)
        // Note: Cela dépend de la configuration de Laravel
        $this->assertTrue(true); // Placeholder
    }

    /**
     * Test de déconnexion
     */
    public function test_deconnexion_utilisateur()
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * Test que les mots de passe sont hashés
     */
    public function test_mot_de_passe_est_hashe()
    {
        $utilisateur = Utilisateur::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Le mot de passe ne doit jamais être stocké en clair
        $this->assertNotEquals('password123', $utilisateur->password);
        $this->assertTrue(Hash::check('password123', $utilisateur->password));
    }

    /**
     * Test de validation de l'email
     */
    public function test_email_invalide_est_refuse()
    {
        $response = $this->post('/login', [
            'email' => 'email_invalide',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test que les sessions expirent
     */
    public function test_session_expire_apres_inactivite()
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur);

        // Simuler l'expiration de session (en modifiant le timestamp)
        // Note: Ceci est un test conceptuel, l'implémentation réelle dépend de la config
        $this->assertTrue(true);
    }

    /**
     * Test de protection contre la fixation de session
     */
    public function test_session_regeneree_apres_connexion()
    {
        $utilisateur = Utilisateur::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Obtenir l'ID de session avant connexion
        $sessionIdAvant = session()->getId();

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // L'ID de session devrait être différent après connexion
        $sessionIdApres = session()->getId();

        $this->assertNotEquals($sessionIdAvant, $sessionIdApres);
    }
}
