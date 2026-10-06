<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ParametreSalaire;
use App\Models\Profil;
use App\Models\Salaire;
use App\Models\MouvementTresorerie;
use App\Models\Transaction;
use App\Support\FormuleSalaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class GestionEntrepriseController extends Controller
{
    /**
     * Page principale de gestion d'entreprise
     */
    public function index(Request $request)
    {
        try {
            $onglet = $request->get('onglet', 'salaires');
            $dateDebut = $request->get('date_debut', now()->startOfMonth()->format('Y-m-d'));
            $dateFin = $request->get('date_fin', now()->endOfMonth()->format('Y-m-d'));

            // Valeurs par défaut : seules les données de l'onglet affiché sont chargées
            $salaires = $mouvements = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $salaireStats = ['total' => 0, 'payes' => 0, 'en_attente' => 0, 'moyenne' => 0];
            $stats = ['entrees' => 0, 'sorties' => 0, 'solde' => 0];
            $parametres = $profils = $agents = collect();

            if ($onglet === 'salaires' && $this->hasTable('salaires')) {
                $perPageSalaires = (int) $request->get('per_page_salaires', 15);
                $perPageSalaires = in_array($perPageSalaires, [10, 15, 25, 50], true) ? $perPageSalaires : 15;

                $salaires = Salaire::with(['agent.utilisateur', 'parametreSalaire'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPageSalaires)
                    ->withQueryString();

                // Statistiques sur tous les salaires (pas seulement la page affichée), en une requête
                $row = Salaire::query()
                    ->selectRaw("COALESCE(SUM(CASE WHEN statut != 'annule' THEN montant_total END), 0) as total")
                    ->selectRaw("AVG(CASE WHEN statut != 'annule' THEN montant_total END) as moyenne")
                    ->selectRaw("COUNT(CASE WHEN statut = 'paye' THEN 1 END) as payes")
                    ->selectRaw("COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as en_attente")
                    ->toBase()
                    ->first();
                $salaireStats = [
                    'total' => (float) $row->total,
                    'payes' => (int) $row->payes,
                    'en_attente' => (int) $row->en_attente,
                    'moyenne' => (float) $row->moyenne,
                ];

                $agents = Agent::with('utilisateur')->where('statut', 'actif')->get();
            }

            if ($onglet === 'parametres' && $this->hasTable('parametres_salaire')) {
                $parametres = ParametreSalaire::with('profils')
                    ->orderBy('actif', 'desc')
                    ->orderBy('nom')
                    ->get();
                $profils = Profil::ordreParNiveau()->get();
            }

            if ($onglet === 'tresorerie' && $this->hasTable('mouvements_tresorerie')) {
                $perPageTresorerie = (int) $request->get('per_page_tresorerie', 20);
                $perPageTresorerie = in_array($perPageTresorerie, [10, 15, 20, 25, 50], true) ? $perPageTresorerie : 20;

                $mouvements = MouvementTresorerie::with(['agent.utilisateur', 'salaire', 'utilisateur'])
                    ->whereBetween('date_mouvement', [$dateDebut, $dateFin])
                    ->orderBy('date_mouvement', 'desc')
                    ->paginate($perPageTresorerie)
                    ->withQueryString();

                $row = MouvementTresorerie::query()
                    ->whereBetween('date_mouvement', [$dateDebut, $dateFin])
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'entree' THEN montant END), 0) as entrees")
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'sortie' THEN montant END), 0) as sorties")
                    ->toBase()
                    ->first();
                $stats['entrees'] = (float) $row->entrees;
                $stats['sorties'] = (float) $row->sorties;
                $stats['solde'] = $stats['entrees'] - $stats['sorties'];
            }

            return $this->ajaxView('pages.gestion_entreprise.index', compact(
                'onglet',
                'salaires',
                'salaireStats',
                'parametres',
                'profils',
                'agents',
                'mouvements',
                'stats',
                'dateDebut',
                'dateFin'
            ));
        } catch (\Throwable $e) {
            Log::error('Erreur dans GestionEntrepriseController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de charger la page salaires.',
                ], 500);
            }

            abort(500, 'Impossible de charger la page salaires.');
        }
    }

    private array $tablesExistantes = [];

    private function hasTable(string $table): bool
    {
        return $this->tablesExistantes[$table] ??= Schema::hasTable($table);
    }

    /**
     * Créer un nouveau paramètre de salaire
     */
    public function storeParametre(Request $request)
    {
        $validated = $this->validateParametre($request);

        if ($erreur = $this->erreurFormule($validated['formule'] ?? null)) {
            return $this->retourParametres('error', $erreur);
        }

        $parametre = ParametreSalaire::create($validated);
        $parametre->profils()->sync($request->input('profil_ids', []));

        return redirect()->route('gestion-entreprise.index', ['onglet' => 'parametres'])
            ->with('success', 'Paramètre de salaire créé avec succès.');
    }

    /**
     * Mettre à jour un paramètre de salaire
     */
    public function updateParametre(Request $request, ParametreSalaire $parametre)
    {
        $validated = $this->validateParametre($request, $parametre);

        if ($erreur = $this->erreurFormule($validated['formule'] ?? null)) {
            return $this->retourParametres('error', $erreur);
        }

        $parametre->update($validated);
        $parametre->profils()->sync($request->input('profil_ids', []));

        return redirect()->route('gestion-entreprise.index', ['onglet' => 'parametres'])
            ->with('success', 'Paramètre mis à jour avec succès.');
    }

    private function validateParametre(Request $request, ?ParametreSalaire $parametre = null): array
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:parametres_salaire,nom' . ($parametre ? ',' . $parametre->id : ''),
            'type' => 'required|in:fixe,commission,mixte',
            'montant_fixe' => 'nullable|numeric|min:0',
            'taux_commission' => 'nullable|numeric|min:0|max:100',
            'base_calcul' => 'nullable|string',
            'formule' => 'nullable|string|max:1000',
            'conditions' => 'nullable|json',
            'profil_ids' => 'nullable|array',
            'profil_ids.*' => 'exists:profils,id',
        ]);

        // Case décochée = champ absent de la requête : sans ceci un paramètre ne pouvait jamais être désactivé
        $validated['actif'] = $request->boolean('actif');

        return $validated;
    }

    private function erreurFormule(?string $formule): ?string
    {
        if ($formule === null || trim($formule) === '') {
            return null;
        }

        $erreur = FormuleSalaire::erreur($formule);

        return $erreur ? "Formule invalide : {$erreur}" : null;
    }

    private function retourParametres(string $type, string $message)
    {
        return redirect()->route('gestion-entreprise.index', ['onglet' => 'parametres'])
            ->withInput()
            ->with($type, $message);
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

        // Bornes incluses sur toute la journée : transactions.date est un timestamp
        $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
        $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
        $periode = $this->libellePeriode($dateDebut, $dateFin);

        // Empêche deux générations simultanées (double clic) de créer des doublons
        $lock = Cache::lock('gestion-entreprise:generer-salaires', 120);
        if (! $lock->get()) {
            return $this->retourSalaires('error', 'Une génération de salaires est déjà en cours. Réessayez dans un instant.');
        }

        try {
            return $this->genererSalairesPourPeriode($validated['agent_ids'] ?? [], $dateDebut, $dateFin, $periode);
        } finally {
            $lock->release();
        }
    }

    private function genererSalairesPourPeriode(array $agentIds, Carbon $dateDebut, Carbon $dateFin, string $periode)
    {
        // Agents actifs + agents ayant travaillé sur la période (ex. désactivés en cours de mois)
        $query = Agent::with(['utilisateur.profils']);
        if (! empty($agentIds)) {
            $query->whereIn('id', $agentIds);
        } else {
            $query->where(function ($q) use ($dateDebut, $dateFin) {
                $q->where('statut', 'actif')
                    ->orWhereHas('transactions', function ($t) use ($dateDebut, $dateFin) {
                        $t->commerciale()->valide()->whereBetween('date', [$dateDebut, $dateFin]);
                    });
            });
        }
        $agents = $query->get();

        $parametresActifs = ParametreSalaire::where('actif', true)->with('profils')->orderBy('nom')->get();

        // Agents ayant déjà un salaire non annulé qui chevauche la période (une seule requête)
        $agentsDejaPayes = Salaire::whereIn('agent_id', $agents->pluck('id'))
            ->where('statut', '!=', 'annule')
            ->where('date_debut', '<=', $dateFin->toDateString())
            ->where('date_fin', '>=', $dateDebut->toDateString())
            ->distinct()
            ->pluck('agent_id')
            ->flip();

        // Totaux des transactions agrégés par agent en base (une seule requête, aucune ligne chargée)
        $totauxParAgent = Transaction::query()
            ->whereIn('agent_id', $agents->pluck('id'))
            ->commerciale()
            ->valide()
            ->whereBetween('date', [$dateDebut, $dateFin])
            ->groupBy('agent_id')
            ->selectRaw('agent_id, COUNT(*) as nb, COALESCE(SUM(montant), 0) as total, COALESCE(SUM(commission), 0) as commissions')
            ->get()
            ->keyBy('agent_id');

        $salairesCreates = 0;
        $dejaGeneres = 0;

        DB::beginTransaction();
        try {
            foreach ($agents as $agent) {
                if ($agentsDejaPayes->has($agent->id)) {
                    $dejaGeneres++;
                    continue;
                }

                $parametre = $this->parametrePourAgent($agent, $parametresActifs);

                $totaux = $totauxParAgent->get($agent->id);
                $nbTransactions = (int) ($totaux->nb ?? 0);
                $totalTransactions = (float) ($totaux->total ?? 0);
                $commissions = (float) ($totaux->commissions ?? 0); // Somme des colonnes commission de chaque transaction

                $montantCommission = 0;
                $montantFixe = $parametre ? (float) $parametre->montant_fixe : 0;

                if ($parametre && ! empty(trim((string) $parametre->formule))) {
                    try {
                        $montantTotal = FormuleSalaire::evaluer($parametre->formule, [
                            'montant_transactions' => $totalTransactions,
                            'nb_transactions' => $nbTransactions,
                            'commissions' => $commissions,
                            'montant_fixe' => $montantFixe,
                            'taux_commission' => (float) $parametre->taux_commission,
                            // Calcul coûteux : uniquement si la formule l'utilise
                            'solde_final' => FormuleSalaire::utilise($parametre->formule, 'solde_final') ? $agent->soldeTotal() : 0,
                            'objectif_atteint' => 0,
                        ]);
                    } catch (\InvalidArgumentException $e) {
                        // Ne jamais enregistrer un salaire à 0 en silence : on annule toute la génération
                        throw new \RuntimeException("Formule du paramètre « {$parametre->nom} » invalide : {$e->getMessage()}");
                    }
                    $montantTotal = max(0, $montantTotal);
                    $montantCommission = max(0, $montantTotal - $montantFixe);
                } else {
                    if ($parametre && $parametre->type !== 'fixe') {
                        if ($parametre->base_calcul === 'commissions') {
                            $montantCommission = $commissions;
                        } else {
                            $montantCommission = ($totalTransactions * $parametre->taux_commission) / 100;
                        }
                    }
                    $montantTotal = $montantFixe + $montantCommission;
                }

                Salaire::create([
                    'agent_id' => $agent->id,
                    'parametre_salaire_id' => $parametre ? $parametre->id : null,
                    'periode' => $periode,
                    'date_debut' => $dateDebut->toDateString(),
                    'date_fin' => $dateFin->toDateString(),
                    'montant_fixe' => $montantFixe,
                    'montant_commission' => $montantCommission,
                    'montant_bonus' => 0,
                    'montant_deduction' => 0,
                    'montant_total' => $montantTotal,
                    'details_calcul' => [
                        'transactions_count' => $nbTransactions,
                        'transactions_total' => $totalTransactions,
                        'commissions' => $commissions,
                        'taux_commission' => $parametre ? $parametre->taux_commission : 0,
                        'formule' => $parametre?->formule,
                        // Montant avant bonus/déductions, base du recalcul lors d'un ajustement
                        'montant_base' => $montantTotal,
                    ],
                    'statut' => 'en_attente',
                ]);

                $salairesCreates++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur génération salaires: ' . $e->getMessage());

            $message = $e instanceof \RuntimeException ? $e->getMessage() : 'Erreur lors de la génération des salaires.';

            return $this->retourSalaires('error', $message . ' Aucun salaire n\'a été généré.');
        }

        $message = "$salairesCreates salaire(s) généré(s) pour la période $periode.";
        if ($dejaGeneres > 0) {
            $message .= " $dejaGeneres agent(s) ignoré(s) : salaire déjà généré sur une période qui chevauche celle-ci.";
        }

        return $this->retourSalaires('success', $message);
    }

    /**
     * Paramètre destiné à l'un des profils de l'agent, sinon paramètre global (sans profil).
     */
    private function parametrePourAgent(Agent $agent, $parametresActifs): ?ParametreSalaire
    {
        $agentProfilIds = $agent->utilisateur ? $agent->utilisateur->profils->pluck('id')->all() : [];

        $specifique = $parametresActifs->first(
            fn ($p) => $p->profils->isNotEmpty() && array_intersect($agentProfilIds, $p->profils->pluck('id')->all())
        );

        return $specifique ?? $parametresActifs->first(fn ($p) => $p->profils->isEmpty());
    }

    /**
     * "2026-01" pour un mois calendaire complet, sinon "01/01/2026 - 15/01/2026".
     */
    private function libellePeriode(Carbon $dateDebut, Carbon $dateFin): string
    {
        $moisComplet = $dateDebut->isSameDay($dateDebut->copy()->startOfMonth())
            && $dateFin->isSameDay($dateDebut->copy()->endOfMonth());

        return $moisComplet
            ? $dateDebut->format('Y-m')
            : $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
    }

    /**
     * Ajuster un salaire en attente (bonus, déduction, notes)
     */
    public function updateSalaire(Request $request, Salaire $salaire)
    {
        $validated = $request->validate([
            'montant_bonus' => 'required|numeric|min:0',
            'montant_deduction' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($salaire, $validated) {
            $salaire = Salaire::whereKey($salaire->id)->lockForUpdate()->first();

            if ($salaire->statut !== 'en_attente') {
                return $this->retourSalaires('error', 'Seul un salaire en attente peut être modifié.');
            }

            $base = (float) ($salaire->details_calcul['montant_base']
                ?? ($salaire->montant_fixe + $salaire->montant_commission));
            $total = $base + (float) $validated['montant_bonus'] - (float) $validated['montant_deduction'];

            if ($total < 0) {
                return $this->retourSalaires('error', 'La déduction ne peut pas dépasser le salaire brut (' . number_format($base, 0, ',', ' ') . ' FCFA).');
            }

            $details = $salaire->details_calcul ?? [];
            $details['montant_base'] = $base;

            $salaire->update([
                'montant_bonus' => $validated['montant_bonus'],
                'montant_deduction' => $validated['montant_deduction'],
                'montant_total' => $total,
                'details_calcul' => $details,
                'notes' => $validated['notes'] ?? $salaire->notes,
            ]);

            return $this->retourSalaires('success', 'Salaire mis à jour.');
        });
    }

    /**
     * Annuler un salaire en attente (généré par erreur) : la période pourra être régénérée
     */
    public function annulerSalaire(Salaire $salaire)
    {
        return DB::transaction(function () use ($salaire) {
            $salaire = Salaire::whereKey($salaire->id)->lockForUpdate()->first();

            if ($salaire->statut !== 'en_attente') {
                return $this->retourSalaires('error', 'Seul un salaire en attente peut être annulé.');
            }

            $salaire->update(['statut' => 'annule']);

            return $this->retourSalaires('success', 'Salaire annulé.');
        });
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

        try {
            return DB::transaction(function () use ($salaire, $validated) {
                // Verrou + revérification : empêche un double paiement (double clic, renvoi du formulaire)
                $salaire = Salaire::with('agent.utilisateur')->whereKey($salaire->id)->lockForUpdate()->first();

                if ($salaire->statut !== 'en_attente') {
                    return $this->retourSalaires('error', $salaire->statut === 'paye'
                        ? 'Ce salaire a déjà été payé.'
                        : 'Ce salaire ne peut pas être payé (statut : ' . $salaire->statut . ').');
                }

                $salaire->update([
                    'statut' => 'paye',
                    'date_paiement' => $validated['date_paiement'],
                    'notes' => $validated['notes'] ?? $salaire->notes,
                ]);

                $nomAgent = $salaire->agent?->utilisateur?->nom_complet ?? ('Agent #' . $salaire->agent_id);

                MouvementTresorerie::create([
                    'type' => 'sortie',
                    'categorie' => 'salaire',
                    'montant' => $salaire->montant_total,
                    'date_mouvement' => $validated['date_paiement'],
                    'agent_id' => $salaire->agent_id,
                    'salaire_id' => $salaire->id,
                    'description' => "Paiement salaire {$salaire->periode} - {$nomAgent}",
                    'mode_paiement' => $validated['mode_paiement'],
                    'utilisateur_id' => auth()->id(),
                ]);

                return $this->retourSalaires('success', 'Salaire marqué comme payé.');
            });
        } catch (\Throwable $e) {
            Log::error('Erreur paiement salaire #' . $salaire->id . ': ' . $e->getMessage());

            return $this->retourSalaires('error', 'Erreur lors du paiement du salaire.');
        }
    }

    private function retourSalaires(string $type, string $message)
    {
        return redirect()->route('gestion-entreprise.index', ['onglet' => 'salaires'])->with($type, $message);
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
