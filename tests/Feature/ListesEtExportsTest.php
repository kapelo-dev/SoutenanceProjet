<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRoutePermission;
use App\Http\Middleware\RequirePasswordChange;
use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Solde;
use App\Models\SystemLog;
use App\Models\Transaction;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ListesEtExportsTest extends TestCase
{
    use RefreshDatabase;

    private Operateur $yas;

    private Operateur $flooz;

    private Agent $agentA;

    private Agent $agentB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-15 14:00:00');
        $this->withoutMiddleware([CheckRoutePermission::class, RequirePasswordChange::class]);
        $this->actingAs(Utilisateur::forceCreate([
            'nom' => 'Admin',
            'prenom' => 'Listes',
            'email' => 'listes@test.local',
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
        ]));

        $this->yas = Operateur::create(['code' => 'YAS', 'libelle' => 'Yas', 'statut' => 'actif', 'ordre' => 1]);
        $this->flooz = Operateur::create(['code' => 'FLOOZ', 'libelle' => 'Flooz', 'statut' => 'actif', 'ordre' => 2]);
        $this->agentA = Agent::create(['nom' => 'Alpha', 'prenom' => 'A', 'telephone' => '90000001', 'statut' => 'actif']);
        $this->agentB = Agent::create(['nom' => 'Beta', 'prenom' => 'B', 'telephone' => '90000002', 'statut' => 'actif']);

        $this->tx($this->agentA, $this->yas, 'depot', 1000, 10, '2026-10-01 00:00:00');
        $this->tx($this->agentA, $this->yas, 'retrait', 2000, 20, '2026-10-10 23:59:59');
        $this->tx($this->agentB, $this->flooz, 'depot', 4000, 40, '2026-10-15 09:00:00');
        $this->tx($this->agentB, $this->flooz, 'depot', 8000, 80, '2026-10-15 10:00:00', 'annule');
        $this->tx($this->agentB, $this->yas, 'paiement', 16000, 160, '2026-09-30 23:59:59');
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

    private function lignesExcel($response, string $feuille, string $colonneCle = 'A'): array
    {
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getSheetByName($feuille)
            ?? IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();

        return $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow());
    }

    public function test_liste_transactions_filtre_dates_bornes_incluses(): void
    {
        $response = $this->get(route('transactions.index', ['date_debut' => '2026-10-01', 'date_fin' => '2026-10-10']))->assertOk();

        $this->assertSame(2, $response->viewData('transactions')->total());
        $this->assertEquals(['total' => 3000, 'count' => 2, 'commission' => 30], $response->viewData('stats'));
    }

    public function test_liste_transactions_stats_sans_filtre(): void
    {
        $response = $this->get(route('transactions.index'))->assertOk();

        $this->assertSame(5, $response->viewData('transactions')->total());
        $this->assertEquals(['total' => 23000, 'count' => 4, 'commission' => 230], $response->viewData('stats'));
    }

    public function test_api_statistiques_par_periode(): void
    {
        $mois = $this->getJson('/api/transactions/statistiques?periode=mois')->assertOk()->json();
        $this->assertSame(3, $mois['total_transactions']);
        $this->assertEquals(7000, $mois['montant_total']);
        $this->assertEquals(['count' => 2, 'montant' => 5000], $mois['par_type']['depot']);
        $this->assertEquals(['count' => 1, 'montant' => 2000], $mois['par_type']['retrait']);
        $this->assertEquals(['count' => 0, 'montant' => 0], $mois['par_type']['paiement']);
        $parOperateur = collect($mois['par_operateur'])->keyBy('operateur.code');
        $this->assertEquals([2, 3000], [$parOperateur['YAS']['count'], $parOperateur['YAS']['montant']]);
        $this->assertEquals([1, 4000], [$parOperateur['FLOOZ']['count'], $parOperateur['FLOOZ']['montant']]);

        $this->assertSame(1, $this->getJson('/api/transactions/statistiques?periode=jour')->json('total_transactions'));
        $this->assertSame(4, $this->getJson('/api/transactions/statistiques?periode=annee')->json('total_transactions'));
    }

    public function test_export_transactions_excel_respecte_les_filtres(): void
    {
        $response = $this->get(route('transactions.export', ['format' => 'excel', 'date_debut' => '2026-10-01', 'date_fin' => '2026-10-15']))->assertOk();

        $references = collect($this->lignesExcel($response, 'Transactions'))->pluck(0)
            ->filter(fn ($v) => is_string($v) && str_starts_with($v, 'TXN-'));
        $this->assertCount(4, $references); // annulée incluse, septembre exclu
    }

    public function test_page_et_export_des_soldes(): void
    {
        foreach ([[null, 'espece', 500], [null, 'espece', 700], [$this->yas->id, 'virtuel', 1000], [$this->flooz->id, 'virtuel', 3000]] as [$op, $type, $montant]) {
            Solde::create(['agent_id' => $this->agentA->id, 'operateur_id' => $op, 'type' => $type, 'montant' => $montant, 'date' => '2026-10-14 10:00:00']);
        }
        Solde::create(['agent_id' => $this->agentB->id, 'operateur_id' => $this->yas->id, 'type' => 'virtuel', 'montant' => 50, 'date' => '2026-10-12 10:00:00']);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $response = $this->get(route('agents.soldes'))->assertOk();
        $requetesSoldes = collect(DB::getQueryLog())->pluck('query')->filter(fn ($q) => str_contains($q, 'from `soldes`'))->count();
        DB::disableQueryLog();

        // Dernier solde espèce (700) + virtuels (1000 + 3000)
        $montants = $response->viewData('soldesParAgent')[$this->agentA->id]
            ->pluck('montant')->map(fn ($m) => (float) $m)->sort()->values()->all();
        $this->assertSame([700.0, 1000.0, 3000.0], $montants);
        $this->assertEquals(30, $response->viewData('commissionsParAgent')[$this->agentA->id]);
        $this->assertEquals(200, $response->viewData('commissionsParAgent')[$this->agentB->id]);
        $response->assertSee('700 FCFA'); // dernier solde espèce de l'agent A
        $this->assertLessThanOrEqual(2, $requetesSoldes); // soldes courants + date de dernière mise à jour

        $export = $this->get(route('agents.solde.export', ['format' => 'excel']))->assertOk();
        $ligneA = collect($this->lignesExcel($export, 'Soldes des Agents'))->first(fn ($l) => ($l[1] ?? null) === 'Alpha');
        $this->assertNotNull($ligneA);
        $this->assertSame('4 700 XOF', end($ligneA));
    }

    public function test_journal_systeme_compteurs_et_export(): void
    {
        foreach (['2026-10-15 08:00:00', '2026-10-13 08:00:00', '2026-10-02 08:00:00', '2026-09-20 08:00:00'] as $date) {
            $log = SystemLog::create(['action' => 'other', 'description' => 'Test ' . $date, 'ip_address' => '1.2.3.4']);
            $log->forceFill(['created_at' => $date])->saveQuietly();
        }
        $attendus = SystemLog::count();

        $stats = $this->get(route('system-logs.index'))->assertOk()->viewData('stats');
        $this->assertSame($attendus, $stats['total']);
        $this->assertSame(SystemLog::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->count(), $stats['today']);
        $this->assertSame(SystemLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(), $stats['this_week']);
        $this->assertSame(SystemLog::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(), $stats['this_month']);

        $export = $this->get(route('system-logs.export.excel', ['date_debut' => '2026-10-02', 'date_fin' => '2026-10-13']))->assertOk();
        $descriptions = collect($this->lignesExcel($export, 'Journal système'))->flatten()
            ->filter(fn ($v) => is_string($v) && str_starts_with($v, 'Test '));
        $this->assertCount(2, $descriptions);
    }
}
