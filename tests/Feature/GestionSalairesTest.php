<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRoutePermission;
use App\Http\Middleware\RequirePasswordChange;
use App\Models\Agent;
use App\Models\MouvementTresorerie;
use App\Models\ParametreSalaire;
use App\Models\Salaire;
use App\Models\Transaction;
use App\Models\Utilisateur;
use App\Support\FormuleSalaire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GestionSalairesTest extends TestCase
{
    use RefreshDatabase;

    private Utilisateur $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([CheckRoutePermission::class, RequirePasswordChange::class]);

        $this->admin = $this->creerUtilisateur('admin@test.local');
        $this->actingAs($this->admin);
    }

    private function creerUtilisateur(string $email): Utilisateur
    {
        return Utilisateur::forceCreate([
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => $email,
            'mot_de_passe' => Hash::make('password'),
            'statut' => 'actif',
        ]);
    }

    private function creerAgent(string $telephone, string $statut = 'actif'): Agent
    {
        return Agent::create([
            'nom' => 'Agent',
            'prenom' => $telephone,
            'telephone' => $telephone,
            'statut' => $statut,
        ]);
    }

    private function creerTransaction(Agent $agent, float $montant, float $commission, string $date, string $statut = 'valide'): Transaction
    {
        return Transaction::create([
            'agent_id' => $agent->id,
            'montant' => $montant,
            'commission' => $commission,
            'type' => 'depot',
            'statut' => $statut,
            'date' => $date,
        ]);
    }

    private function generer(string $debut = '2026-09-01', string $fin = '2026-09-30')
    {
        return $this->post(route('gestion-entreprise.generer-salaires'), [
            'date_debut' => $debut,
            'date_fin' => $fin,
        ]);
    }

    public function test_generation_agrege_les_transactions_valides_de_la_periode(): void
    {
        ParametreSalaire::create([
            'nom' => 'Mixte',
            'type' => 'mixte',
            'montant_fixe' => 50000,
            'taux_commission' => 10,
            'base_calcul' => 'transactions',
            'actif' => true,
        ]);

        $agent = $this->creerAgent('90000001');
        $this->creerTransaction($agent, 100000, 500, '2026-09-01 08:00:00');
        $this->creerTransaction($agent, 200000, 700, '2026-09-30 23:30:00'); // dernier jour inclus
        $this->creerTransaction($agent, 999999, 999, '2026-10-01 00:00:00'); // hors période
        $this->creerTransaction($agent, 888888, 888, '2026-09-15 10:00:00', 'annule'); // non valide

        $this->generer()->assertSessionHas('success');

        $salaire = Salaire::where('agent_id', $agent->id)->sole();
        $this->assertSame('2026-09', $salaire->periode);
        $this->assertEquals(50000, $salaire->montant_fixe);
        $this->assertEquals(30000, $salaire->montant_commission); // 10 % de 300 000
        $this->assertEquals(80000, $salaire->montant_total);
        $this->assertSame(2, $salaire->details_calcul['transactions_count']);
        $this->assertEquals(1200, $salaire->details_calcul['commissions']);
    }

    public function test_agent_sans_transaction_recoit_le_fixe(): void
    {
        ParametreSalaire::create(['nom' => 'Fixe', 'type' => 'fixe', 'montant_fixe' => 40000, 'actif' => true]);
        $agent = $this->creerAgent('90000002');

        $this->generer()->assertSessionHas('success');

        $salaire = Salaire::where('agent_id', $agent->id)->sole();
        $this->assertEquals(40000, $salaire->montant_total);
        $this->assertSame(0, $salaire->details_calcul['transactions_count']);
    }

    public function test_generation_ignore_les_periodes_qui_chevauchent(): void
    {
        ParametreSalaire::create(['nom' => 'Fixe', 'type' => 'fixe', 'montant_fixe' => 40000, 'actif' => true]);
        $agent = $this->creerAgent('90000003');

        $this->generer();
        $this->generer('2026-09-15', '2026-10-15')->assertSessionHas('success');

        $this->assertSame(1, Salaire::where('agent_id', $agent->id)->count());
    }

    public function test_salaire_annule_peut_etre_regenere(): void
    {
        ParametreSalaire::create(['nom' => 'Fixe', 'type' => 'fixe', 'montant_fixe' => 40000, 'actif' => true]);
        $agent = $this->creerAgent('90000004');

        $this->generer();
        $salaire = Salaire::where('agent_id', $agent->id)->sole();
        $this->post(route('gestion-entreprise.salaires.annuler', $salaire))->assertSessionHas('success');

        $this->generer();

        $this->assertSame(1, Salaire::where('agent_id', $agent->id)->where('statut', 'en_attente')->count());
    }

    public function test_formule_personnalisee(): void
    {
        ParametreSalaire::create([
            'nom' => 'Formule',
            'type' => 'mixte',
            'montant_fixe' => 10000,
            'formule' => 'montant_fixe + commissions * 2 + nb_transactions * 100',
            'actif' => true,
        ]);
        $agent = $this->creerAgent('90000005');
        $this->creerTransaction($agent, 50000, 1000, '2026-09-10 12:00:00');
        $this->creerTransaction($agent, 50000, 500, '2026-09-11 12:00:00');

        $this->generer()->assertSessionHas('success');

        // 10 000 + 1 500 * 2 + 2 * 100
        $this->assertEquals(13200, Salaire::where('agent_id', $agent->id)->sole()->montant_total);
    }

    public function test_paiement_cree_un_mouvement_et_refuse_le_double_paiement(): void
    {
        ParametreSalaire::create(['nom' => 'Fixe', 'type' => 'fixe', 'montant_fixe' => 40000, 'actif' => true]);
        $agent = $this->creerAgent('90000006');
        $this->generer();
        $salaire = Salaire::where('agent_id', $agent->id)->sole();

        $payer = fn () => $this->post(route('gestion-entreprise.salaires.payer', $salaire), [
            'date_paiement' => '2026-10-02',
            'mode_paiement' => 'espece',
        ]);

        $payer()->assertSessionHas('success');
        $payer()->assertSessionHas('error');

        $this->assertSame('paye', $salaire->fresh()->statut);
        $this->assertSame(1, MouvementTresorerie::where('salaire_id', $salaire->id)->count());
    }

    public function test_page_salaires_affiche_les_statistiques(): void
    {
        $agent = $this->creerAgent('90000007');
        foreach ([['en_attente', 100000], ['paye', 50000], ['annule', 999999]] as [$statut, $montant]) {
            Salaire::create([
                'agent_id' => $agent->id,
                'periode' => '2026-09',
                'date_debut' => '2026-09-01',
                'date_fin' => '2026-09-30',
                'montant_total' => $montant,
                'statut' => $statut,
            ]);
        }

        $response = $this->get(route('gestion-entreprise.index', ['onglet' => 'salaires']));

        $response->assertOk();
        $response->assertViewHas('salaireStats', [
            'total' => 150000.0,
            'payes' => 1,
            'en_attente' => 1,
            'moyenne' => 75000.0,
        ]);
    }

    public function test_onglets_parametres_et_tresorerie_se_chargent(): void
    {
        ParametreSalaire::create(['nom' => 'Fixe', 'type' => 'fixe', 'montant_fixe' => 40000, 'actif' => true]);

        $this->get(route('gestion-entreprise.index', ['onglet' => 'parametres']))
            ->assertOk()
            ->assertSee('Fixe');

        $this->get(route('gestion-entreprise.index', ['onglet' => 'tresorerie']))
            ->assertOk()
            ->assertViewHas('stats', ['entrees' => 0.0, 'sorties' => 0.0, 'solde' => 0.0]);
    }

    public function test_formule_detecte_les_variables_utilisees(): void
    {
        $this->assertTrue(FormuleSalaire::utilise('montant_fixe + solde_final * 0.01', 'solde_final'));
        $this->assertFalse(FormuleSalaire::utilise('montant_fixe + commissions', 'solde_final'));
        $this->assertFalse(FormuleSalaire::utilise('x_solde_final_y', 'solde_final'));
    }
}
