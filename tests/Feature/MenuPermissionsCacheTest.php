<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRoutePermission;
use App\Http\Middleware\RequirePasswordChange;
use App\Models\Lien;
use App\Models\Profil;
use App\Models\Utilisateur;
use App\Support\PermissionCache;
use App\Support\UserMenuPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MenuPermissionsCacheTest extends TestCase
{
    use RefreshDatabase;

    private Utilisateur $user;

    private Profil $profil;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profil = Profil::create(['libelle' => 'Test cache menu', 'niveau' => 0]);
        $this->user = Utilisateur::forceCreate([
            'nom' => 'Test',
            'prenom' => 'Menu',
            'email' => 'menu@test.local',
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
        ]);
        $this->user->profils()->attach($this->profil->id);
    }

    private function donnerAcces(string $route, string $url): Lien
    {
        $lien = Lien::create(['libelle' => $route, 'route' => $route, 'url' => $url, 'visible' => true]);
        DB::table('profil_liens')->insert([
            'profil_id' => $this->profil->id,
            'lien_id' => $lien->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $lien;
    }

    /**
     * Nombre de requêtes « calcul des liens du menu » (seule celle-ci sélectionne « liens.id as lien_id ») exécutées pendant $callback.
     */
    private function compterCalculsMenu(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        return $queries->filter(fn ($q) => str_contains($q, 'as `lien_id`'))->count();
    }

    public function test_permissions_mises_en_cache(): void
    {
        $this->donnerAcces('gestion-entreprise.index', '/gestion-entreprise');

        $this->assertSame(1, $this->compterCalculsMenu(fn () => UserMenuPermissions::forUser($this->user)));
        $this->assertSame(0, $this->compterCalculsMenu(fn () => UserMenuPermissions::forUser($this->user)));
        $this->assertContains('gestion-entreprise.index', UserMenuPermissions::forUser($this->user)['route_names']);
    }

    public function test_modification_des_permissions_invalide_le_cache(): void
    {
        $lien = Lien::create(['libelle' => 'Agents', 'route' => 'agents.index', 'url' => '/agents', 'visible' => true]);

        $this->assertNotContains('agents.index', UserMenuPermissions::forUser($this->user)['route_names']);

        $this->actingAs($this->user)
            ->withoutMiddleware([CheckRoutePermission::class, RequirePasswordChange::class])
            ->postJson(route('permissions.toggle'), ['profil_id' => $this->profil->id, 'lien_id' => $lien->id])
            ->assertOk();

        $this->assertContains('agents.index', UserMenuPermissions::forUser($this->user)['route_names']);
    }

    public function test_page_ne_calcule_le_menu_qu_une_fois(): void
    {
        $this->donnerAcces('gestion-entreprise.index', '/gestion-entreprise');
        PermissionCache::flush();

        $calculs = $this->compterCalculsMenu(function () {
            $this->actingAs($this->user)
                ->withoutMiddleware([CheckRoutePermission::class, RequirePasswordChange::class])
                ->get(route('gestion-entreprise.index'))
                ->assertOk()
                ->assertSee('menu-permissions-data', false);
        });

        $this->assertSame(1, $calculs);
    }
}
