<?php

namespace Tests\Feature;

use App\Http\Middleware\RequirePasswordChange;
use App\Models\Lien;
use App\Models\Profil;
use App\Models\Utilisateur;
use App\Support\PermissionCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissionsAccesCacheTest extends TestCase
{
    use RefreshDatabase;

    private Utilisateur $user;

    private Profil $profil;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(RequirePasswordChange::class);

        $this->profil = Profil::create(['libelle' => 'Test acces', 'niveau' => 0]);
        $this->user = $this->creerUtilisateur('acces@test.local', $this->profil);
    }

    private function creerUtilisateur(string $email, Profil $profil): Utilisateur
    {
        $user = Utilisateur::forceCreate([
            'nom' => 'Test',
            'prenom' => 'Acces',
            'email' => $email,
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
        ]);
        $user->profils()->attach($profil->id);

        return $user;
    }

    private function autoriser(Profil $profil, ?string $route, ?string $url = null): Lien
    {
        $lien = Lien::where('route', $route)->whereNotNull('route')->first()
            ?? Lien::create(['libelle' => $route ?? $url, 'route' => $route, 'url' => $url, 'visible' => true]);

        DB::table('profil_liens')->updateOrInsert(
            ['profil_id' => $profil->id, 'lien_id' => $lien->id],
            ['deleted_at' => null, 'created_at' => now(), 'updated_at' => now()]
        );
        PermissionCache::flush();

        return $lien;
    }

    public function test_acces_selon_les_liens_du_profil(): void
    {
        $this->autoriser($this->profil, 'gestion-entreprise.index', '/gestion-entreprise');

        $this->assertTrue($this->user->canAccessRoute('gestion-entreprise.index'));
        $this->assertFalse($this->user->canAccessRoute('agents.index'));
    }

    public function test_heritage_du_role_parent(): void
    {
        $parent = Profil::create(['libelle' => 'Parent', 'niveau' => 0]);
        $this->profil->update(['parent_id' => $parent->id]);
        $this->autoriser($parent, 'agents.index', '/agents');

        $user = $this->user->fresh();
        $this->assertContains($parent->id, $user->effectiveProfilIds());
        $this->assertTrue($user->canAccessRoute('agents.index'));

        // Un parent supprimé ne transmet plus ses permissions
        $parent->delete();
        PermissionCache::flush();
        $this->assertFalse($user->fresh()->canAccessRoute('agents.index'));
    }

    public function test_onglets_gestion_entreprise_par_url(): void
    {
        $this->autoriser($this->profil, null, '/gestion-entreprise?onglet=tresorerie');

        $this->assertTrue($this->user->canAccessGestionEntrepriseOnglet('tresorerie'));
        $this->assertFalse($this->user->canAccessGestionEntrepriseOnglet('salaires'));
        $this->assertTrue($this->user->canAccessRoute('gestion-entreprise.index'));
    }

    public function test_revocation_appliquee_immediatement(): void
    {
        $lien = $this->autoriser($this->profil, 'gestion-entreprise.index', '/gestion-entreprise');
        $admin = $this->creerUtilisateur('admin@test.local', $this->profil);
        $this->autoriser($this->profil, 'permissions.toggle');

        $this->actingAs($this->user)->get(route('gestion-entreprise.index'))->assertOk();

        // L'admin retire la permission via l'écran des permissions
        $this->actingAs($admin)
            ->postJson(route('permissions.toggle'), ['profil_id' => $this->profil->id, 'lien_id' => $lien->id])
            ->assertOk();

        $this->actingAs($this->user)->get(route('gestion-entreprise.index'))->assertForbidden();
    }

    public function test_aucune_requete_de_permission_quand_le_cache_est_chaud(): void
    {
        $this->autoriser($this->profil, 'gestion-entreprise.index', '/gestion-entreprise');
        $this->actingAs($this->user)->get(route('gestion-entreprise.index'))->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->user)->get(route('gestion-entreprise.index'))->assertOk();
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $permissionQueries = $queries->filter(fn ($q) => preg_match('/`(liens|profil_liens|profils|user_profils)`/', $q));
        $this->assertCount(0, $permissionQueries, $permissionQueries->implode("\n"));
    }
}
