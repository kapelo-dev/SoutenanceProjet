<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ParametreSalaire;
use App\Models\Salaire;
use App\Models\MouvementTresorerie;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GestionEntrepriseController extends Controller
{
    /**
     * Page principale de gestion d'entreprise
     */
    public function index(Request $request)
    {
        $onglet = $request->get('onglet', 'salaires');

        // Données pour l'onglet Salaires
        $salaires = Salaire::with(['agent.utilisateur', 'parametreSalaire'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Données pour l'onglet Paramètres
        $parametres = ParametreSalaire::orderBy('actif', 'desc')
            ->orderBy('nom')
            ->get();

        $agents = Agent::with('utilisateur')->where('statut', 'actif')->get();

        // Données pour l'onglet Trésorerie
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', now()->endOfMonth()->format('Y-m-d'));

        $mouvements = MouvementTresorerie::with(['agent.utilisateur', 'salaire', 'utilisateur'])
            ->whereBetween('date_mouvement', [$dateDebut, $dateFin])
            ->orderBy('date_mouvement', 'desc')
            ->paginate(20);

        // Statistiques trésorerie
        $stats = [
            'entrees' => MouvementTresorerie::where('type', 'entree')
                ->whereBetween('date_mouvement', [$dateDebut, $dateFin])
                ->sum('montant'),
            'sorties' => MouvementTresorerie::where('type', 'sortie')
                ->whereBetween('date_mouvement', [$dateDebut, $dateFin])
                ->sum('montant'),
        ];
        $stats['solde'] = $stats['entrees'] - $stats['sorties'];

        return view('pages.gestion_entreprise.index', compact(
            'onglet',
            'salaires',
            'parametres',
            'agents',
            'mouvements',
            'stats',
            'dateDebut',
            'dateFin'
        ));
    }

    /**
     * Créer un nouveau paramètre de salaire
     */
    public function storeParametre(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:parametres_salaire,nom',
            'type' => 'required|in:fixe,commission,mixte',
            'montant_fixe' => 'nullable|numeric|min:0',
            'taux_commission' => 'nullable|numeric|min:0|max:100',
            'base_calcul' => 'nullable|string',
            'formule' => 'nullable|string',
            'conditions' => 'nullable|json',
            'actif' => 'boolean',
        ]);

        $parametre = ParametreSalaire::create($validated);

        return redirect()->route('gestion-entreprise.index', ['onglet' => 'parametres'])
            ->with('success', 'Paramètre de salaire créé avec succès.');
    }

    /**
     * Mettre à jour un paramètre de salaire
     */
    public function updateParametre(Request $request, ParametreSalaire $parametre)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:parametres_salaire,nom,' . $parametre->id,
            'type' => 'required|in:fixe,commission,mixte',
            'montant_fixe' => 'nullable|numeric|min:0',
            'taux_commission' => 'nullable|numeric|min:0|max:100',
            'base_calcul' => 'nullable|string',
            'formule' => 'nullable|string',
            'conditions' => 'nullable|json',
            'actif' => 'boolean',
        ]);

        $parametre->update($validated);

        return redirect()->route('gestion-entreprise.index', ['onglet' => 'parametres'])
            ->with('success', 'Paramètre mis à jour avec succès.');
    }

    /**
     * Supprimer un paramètre de salaire
     */
    public function deleteParametre(ParametreSalaire $parametre)
    {
        $parametre->delete();

        return redirect()->route('gestion-entreprise.index', ['onglet' => 'parametres'])
            ->with('success', 'Paramètre supprimé avec succès.');
    }

    /**
     * Calculer et générer les salaires pour une période
     */
    public function genererSalaires(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'exists:agents,id',
        ]);

        $dateDebut = Carbon::parse($validated['date_debut']);
        $dateFin = Carbon::parse($validated['date_fin']);
        $periode = $dateDebut->format('Y-m');

        // Sélectionner les agents
        $query = Agent::with('utilisateur')->where('statut', 'actif');
        if (!empty($validated['agent_ids'])) {
            $query->whereIn('id', $validated['agent_ids']);
        }
        $agents = $query->get();

        $salairesCreates = 0;

        DB::beginTransaction();
        try {
            foreach ($agents as $agent) {
                // Vérifier si un salaire existe déjà pour cette période
                $salaireExistant = Salaire::where('agent_id', $agent->id)
                    ->where('periode', $periode)
                    ->first();

                if ($salaireExistant) {
                    continue; // Skip si déjà créé
                }

                // Récupérer le paramètre de salaire actif (ou créer une logique pour assigner)
                $parametre = ParametreSalaire::where('actif', true)->first();

                // Calculer les commissions basées sur les transactions de l'agent
                $transactions = Transaction::where('agent_id', $agent->id)
                    ->whereBetween('created_at', [$dateDebut, $dateFin])
                    ->get();

                $montantCommission = 0;
                if ($parametre && $parametre->type !== 'fixe') {
                    $totalTransactions = $transactions->sum('montant');
                    $montantCommission = ($totalTransactions * $parametre->taux_commission) / 100;
                }

                $montantFixe = $parametre ? $parametre->montant_fixe : 0;
                $montantTotal = $montantFixe + $montantCommission;

                // Créer le salaire
                Salaire::create([
                    'agent_id' => $agent->id,
                    'parametre_salaire_id' => $parametre ? $parametre->id : null,
                    'periode' => $periode,
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                    'montant_fixe' => $montantFixe,
                    'montant_commission' => $montantCommission,
                    'montant_bonus' => 0,
                    'montant_deduction' => 0,
                    'montant_total' => $montantTotal,
                    'details_calcul' => [
                        'transactions_count' => $transactions->count(),
                        'transactions_total' => $transactions->sum('montant'),
                        'taux_commission' => $parametre ? $parametre->taux_commission : 0,
                    ],
                    'statut' => 'en_attente',
                ]);

                $salairesCreates++;
            }

            DB::commit();

            return redirect()->route('gestion-entreprise.index', ['onglet' => 'salaires'])
                ->with('success', "$salairesCreates salaire(s) généré(s) avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('gestion-entreprise.index', ['onglet' => 'salaires'])
                ->with('error', 'Erreur lors de la génération des salaires: ' . $e->getMessage());
        }
    }

    /**
     * Marquer un salaire comme payé
     */
    public function payerSalaire(Request $request, Salaire $salaire)
    {
        $validated = $request->validate([
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|in:espece,virement,cheque',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Mettre à jour le salaire
            $salaire->update([
                'statut' => 'paye',
                'date_paiement' => $validated['date_paiement'],
                'notes' => $validated['notes'] ?? $salaire->notes,
            ]);

            // Créer un mouvement de trésorerie
            MouvementTresorerie::create([
                'type' => 'sortie',
                'categorie' => 'salaire',
                'montant' => $salaire->montant_total,
                'date_mouvement' => $validated['date_paiement'],
                'agent_id' => $salaire->agent_id,
                'salaire_id' => $salaire->id,
                'description' => "Paiement salaire {$salaire->periode} - {$salaire->agent->utilisateur->nom_complet}",
                'mode_paiement' => $validated['mode_paiement'],
                'utilisateur_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('gestion-entreprise.index', ['onglet' => 'salaires'])
                ->with('success', 'Salaire marqué comme payé.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('gestion-entreprise.index', ['onglet' => 'salaires'])
                ->with('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Ajouter un mouvement de trésorerie
     */
    public function storeMouvement(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:entree,sortie',
            'categorie' => 'required|string',
            'montant' => 'required|numeric|min:0',
            'date_mouvement' => 'required|date',
            'description' => 'required|string',
            'mode_paiement' => 'nullable|in:espece,virement,cheque',
            'reference' => 'nullable|string',
        ]);

        $validated['utilisateur_id'] = auth()->id();

        MouvementTresorerie::create($validated);

        return redirect()->route('gestion-entreprise.index', ['onglet' => 'tresorerie'])
            ->with('success', 'Mouvement enregistré avec succès.');
    }
}
