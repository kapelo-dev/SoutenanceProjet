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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class RapportTest extends TestCase
{
    use RefreshDatabase;

    private Operateur $yas;

    private Operateur $flooz;

    private Kiosque $k2;

    private Agent $agentA;

    private Agent $agentB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-15 14:00:00');
        $this->withoutMiddleware([CheckRoutePermission::class, RequirePasswordChange::class]);
        $this->actingAs(Utilisateur::forceCreate([
            'nom' => 'Admin',
            'prenom' => 'Rapport',
            'email' => 'rapport@test.local',
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
        ]));

        $this->yas = Operateur::create(['code' => 'YAS', 'libelle' => 'Yas', 'statut' => 'actif', 'ordre' => 1]);
        $this->flooz = Operateur::create(['code' => 'FLOOZ', 'libelle' => 'Flooz', 'statut' => 'actif', 'ordre' => 2]);

        $k1 = $this->kiosque('K1');
        $this->k2 = $this->kiosque('K2');
        $this->agentA = $this->agent('90000001', $k1);
        $this->agentB = $this->agent('90000002', $k1);
        $agentC = $this->agent('90000003', $this->k2);

        for ($i = 0; $i < 60; $i++) {
            $this->tx($this->agentA, $this->yas, 'depot', 100, '2026-10-10 10:00:00');
        }
        for ($i = 0; $i < 5; $i++) {
            $this->tx($this->agentB, $this->flooz, 'retrait', 1000, '2026-10-11 10:00:00');
        }
        $this->tx($agentC, $this->yas, 'depot', 5000, '2026-10-12 10:00:00', 'annule');
        $this->tx($agentC, $this->yas, 'depot', 50, '2026-09-01 10:00:00'); // hors période par défaut
    }

    private function kiosque(string $code): Kiosque
    {
        return Kiosque::forceCreate(['uid' => (string) Str::uuid(), 'code' => $code, 'nom' => $code, 'statut' => 'actif']);
    }

    private function agent(string $telephone, Kiosque $kiosque): Agent
    {
        return Agent::create([
            'nom' => 'Agent',
            'prenom' => $telephone,
            'telephone' => $telephone,
            'statut' => 'actif',
            'kiosque_id' => $kiosque->id,
        ]);
    }

    private function tx(Agent $agent, Operateur $op, string $type, float $montant, string $date, string $statut = 'valide'): void
    {
        Transaction::create([
            'agent_id' => $agent->id,
            'operateur_id' => $op->id,
            'type' => $type,
            'montant' => $montant,
            'commission' => 1,
            'statut' => $statut,
            'date' => $date,
        ]);
    }

    public function test_page_rapport_stats_top_agents_et_pagination(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $response = $this->get(route('rapports.index'))->assertOk();
        $requetesTransactions = collect(DB::getQueryLog())->pluck('query')
            ->filter(fn ($q) => str_contains($q, 'from `transactions`'))
            ->count();
        DB::disableQueryLog();

        $response->assertViewHas('statsGlobales', [
            'total_transactions' => 65,
            'montant_total' => 11000.0,
            'commission_total' => 65.0,
            'nombre_agents' => 2,
        ]);

        $operateurs = collect($response->viewData('statsOperateurs'))->keyBy(fn ($s) => $s['operateur']->code);
        $this->assertSame([60, 6000.0, 60.0], [$operateurs['YAS']['nombre_transactions'], $operateurs['YAS']['montant_total'], $operateurs['YAS']['commission_total']]);
        $this->assertSame([5, 5000.0], [$operateurs['FLOOZ']['nombre_transactions'], $operateurs['FLOOZ']['montant_total']]);

        $top = $response->viewData('topAgents');
        $this->assertSame([$this->agentA->id, $this->agentB->id], $top->pluck('agent.id')->all()); // agent C : seulement une transaction annulée
        $this->assertSame(60, $top[0]['nombre_transactions']);

        $transactions = $response->viewData('transactions');
        $this->assertSame(66, $transactions->total()); // annulée incluse, septembre exclue
        $this->assertCount(50, $transactions->items());
        $response->assertSee('66 transactions');

        // Stats, opérateurs, top agents, page + comptage de la pagination
        $this->assertLessThanOrEqual(5, $requetesTransactions);
    }

    public function test_case_tous_equivaut_a_aucun_filtre(): void
    {
        $response = $this->get(route('rapports.index', [
            'statut' => ['tous', 'valide'],
            'kiosque_id' => ['tous'],
            'operateur_id' => ['tous'],
        ]))->assertOk();

        $this->assertSame(65, $response->viewData('transactions')->total());
        $this->assertCount(2, $response->viewData('statsOperateurs'));
    }

    public function test_filtre_kiosque(): void
    {
        $response = $this->get(route('rapports.index', ['kiosque_id' => [$this->k2->id]]))->assertOk();

        $this->assertSame(1, $response->viewData('transactions')->total());
        $this->assertSame(0, $response->viewData('statsGlobales')['total_transactions']);
    }

    public function test_export_excel_contient_toutes_les_transactions(): void
    {
        // « Tous » coché pour le statut : avant, l'export filtrait sur statut = 'tous' et sortait vide
        $response = $this->get(route('rapports.export', ['format' => 'excel', 'statut' => ['tous']]))->assertOk();

        $fichier = $response->baseResponse->getFile()->getPathname();
        $feuille = IOFactory::load($fichier)->getSheetByName('Transactions');
        $references = collect($feuille->rangeToArray('A1:A' . $feuille->getHighestRow()))
            ->flatten()
            ->filter(fn ($v) => is_string($v) && str_starts_with($v, 'TXN-'));

        $this->assertCount(66, $references);
    }

    public function test_export_pdf(): void
    {
        $this->get(route('rapports.export'))->assertOk();
    }
}
