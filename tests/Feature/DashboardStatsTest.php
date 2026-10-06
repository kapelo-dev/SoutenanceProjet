<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRoutePermission;
use App\Http\Middleware\RequirePasswordChange;
use App\Models\Agent;
use App\Models\Kiosque;
use App\Models\Operateur;
use App\Models\Transaction;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    private Operateur $yas;

    private Operateur $flooz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-15 14:00:00');
        $this->withoutMiddleware([CheckRoutePermission::class, RequirePasswordChange::class]);
        $this->actingAs(Utilisateur::forceCreate([
            'nom' => 'Admin',
            'prenom' => 'Dashboard',
            'email' => 'dashboard@test.local',
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
        ]));

        $this->yas = Operateur::create(['code' => 'YAS', 'libelle' => 'Yas', 'statut' => 'actif', 'ordre' => 1]);
        $this->flooz = Operateur::create(['code' => 'FLOOZ', 'libelle' => 'Flooz', 'statut' => 'actif', 'ordre' => 2]);

        $k1 = $this->kiosque('K1', 6.17, 1.23);
        $k2 = $this->kiosque('K2', null, null);
        $agentA = $this->agent('90000001', $k1);
        $agentB = $this->agent('90000002', $k2);
        $agentC = $this->agent('90000003', null);

        $this->tx($agentA, $this->yas, 'depot', 1000, 10, '2026-10-15 10:00:00');
        $this->tx($agentB, $this->flooz, 'retrait', 2000, 20, '2026-10-15 08:00:00');
        $this->tx($agentA, $this->yas, 'transfert', 4000, 40, '2026-10-14 12:00:00');
        $this->tx($agentC, $this->yas, 'paiement', 8000, 0, '2026-10-01 00:00:00');
        $this->tx($agentA, $this->yas, 'depot', 16000, 0, '2026-09-30 23:59:59');            // mois précédent
        $this->tx($agentA, $this->yas, 'depot', 32000, 0, '2026-10-15 09:00:00', 'annule');  // non valide
        $this->tx($agentA, $this->yas, 'depot', 64000, 0, '2025-10-15 09:00:00');            // même mois, année passée
    }

    private function kiosque(string $code, ?float $lat, ?float $lng): Kiosque
    {
        return Kiosque::forceCreate([
            'uid' => (string) Str::uuid(),
            'code' => $code,
            'nom' => $code,
            'quartier' => 'Tokoin',
            'latitude' => $lat,
            'longitude' => $lng,
            'statut' => 'actif',
            'capacite_agents' => 2,
        ]);
    }

    private function agent(string $telephone, ?Kiosque $kiosque): Agent
    {
        return Agent::create([
            'nom' => 'Agent',
            'prenom' => $telephone,
            'telephone' => $telephone,
            'statut' => 'actif',
            'kiosque_id' => $kiosque?->id,
        ]);
    }

    private function tx(Agent $agent, Operateur $op, string $type, float $montant, float $commission, string $date, string $statut = 'valide'): void
    {
        Transaction::create([
            'agent_id' => $agent->id,
            'operateur_id' => $op->id,
            'type' => $type,
            'montant' => $montant,
            'commission' => $commission,
            'statut' => $statut,
            'date' => $date,
        ]);
    }

    public function test_page_dashboard(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $response = $this->get(route('dashboard'))->assertOk();
        $requetesTransactions = collect(DB::getQueryLog())->pluck('query')
            ->filter(fn ($q) => str_contains($q, 'from `transactions`'))
            ->count();
        DB::disableQueryLog();

        $response->assertViewHas('stats', [
            'transactions_jour' => 2,
            'montant_jour' => 3000.0,
            'commission_jour' => 30.0,
            'transactions_mois' => 4,
            'montant_mois' => 15000.0,
            'commission_mois' => 70.0,
            'agents_actifs' => 3,
            'kiosques_actifs' => 2,
        ]);
        $response->assertViewHas('transactionsParType', [
            'depot' => 1000.0,
            'retrait' => 2000.0,
            'transfert' => 0.0,
            'paiement' => 0.0,
        ]);

        $operateurs = $response->viewData('operateurs')->keyBy(fn ($o) => $o['operateur']->code);
        $this->assertSame([3, 13000.0], [$operateurs['YAS']['transactions'], $operateurs['YAS']['montant']]);
        $this->assertSame([1, 2000.0], [$operateurs['FLOOZ']['transactions'], $operateurs['FLOOZ']['montant']]);

        $evolution = $response->viewData('evolutionTransactions')->values();
        $this->assertCount(7, $evolution);
        $this->assertSame('2026-10-09', $evolution[0]['date']);
        $this->assertSame(['2026-10-14', 1, 4000.0], [$evolution[5]['date'], $evolution[5]['count'], $evolution[5]['montant']]);
        $this->assertSame(['2026-10-15', 2, 3000.0], [$evolution[6]['date'], $evolution[6]['count'], $evolution[6]['montant']]);

        // Stats, opérateurs, dernières transactions, évolution : une requête chacune
        $this->assertSame(4, $requetesTransactions);
    }

    public function test_graphique_30_jours_et_12_mois(): void
    {
        $jours = collect($this->getJson('/api/dashboard/graphique-transactions?periode=30jours')->assertOk()->json());
        $this->assertCount(30, $jours);
        $this->assertSame(['2026-09-30', 1, 16000], [$jours->firstWhere('date', '2026-09-30')['date'], $jours->firstWhere('date', '2026-09-30')['count'], (int) $jours->firstWhere('date', '2026-09-30')['montant']]);

        $mois = collect($this->getJson('/api/dashboard/graphique-transactions?periode=12mois')->assertOk()->json());
        $this->assertCount(12, $mois);
        $this->assertSame('2025-11', $mois->first()['date']); // octobre 2025 hors période
        $this->assertSame([4, 15000], [$mois->last()['count'], (int) $mois->last()['montant']]);
        $this->assertSame([1, 16000], [$mois->firstWhere('date', '2026-09')['count'], (int) $mois->firstWhere('date', '2026-09')['montant']]);
    }

    public function test_stats_par_operateur_et_temps_reel(): void
    {
        $stats = collect($this->getJson('/api/dashboard/stats-par-operateur')->assertOk()->json())
            ->keyBy('operateur.code');
        $this->assertSame([1, 1000], [$stats['YAS']['jour']['count'], (int) $stats['YAS']['jour']['montant']]);
        $this->assertSame([3, 13000], [$stats['YAS']['mois']['count'], (int) $stats['YAS']['mois']['montant']]);
        $this->assertSame([1, 2000], [$stats['FLOOZ']['mois']['count'], (int) $stats['FLOOZ']['mois']['montant']]);

        $this->getJson('/api/dashboard/stats-temps-reel')
            ->assertOk()
            ->assertJson(['transactions_jour' => 2, 'montant_jour' => 3000, 'agents_en_ligne' => 3]);
    }

    public function test_carte_performance_par_zone(): void
    {
        $points = $this->getJson('/api/dashboard/carte-performance-mois')->assertOk()->json();

        $this->assertCount(1, $points); // agent sans kiosque ignoré
        $this->assertSame('Tokoin', $points[0]['zone']);
        $this->assertSame('Lomé', $points[0]['ville']);
        $this->assertSame(2, $points[0]['kiosques']);
        $this->assertSame(3, $points[0]['transactions']);
        $this->assertEquals(7000, $points[0]['montant']);
        $this->assertEquals(100, $points[0]['part_pct']);
        $this->assertFalse($points[0]['approximate']);
        $this->assertEqualsWithDelta(6.17, $points[0]['latitude'], 0.0001);
    }
}
