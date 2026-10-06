<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Kiosque;
use App\Traits\Exportable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RapportController extends Controller
{
    use Exportable;

    private function afficherTopAgents(Request $request): bool
    {
        return $request->input('afficher_top_agents', '1') !== '0';
    }

    /**
     * Afficher la page des rapports
     */
    public function index(Request $request)
    {
        $afficherTopAgents = $this->afficherTopAgents($request);
        [$dateDebut, $dateFin] = $this->periode($request);
        $filtres = $this->filtres($request);

        $query = $this->requeteFiltree($filtres, $dateDebut, $dateFin);

        $statsOperateurs = $this->statsOperateurs($query, $filtres);

        $topAgents = $afficherTopAgents
            ? $this->buildTopAgents($filtres, $dateDebut, $dateFin)
            : collect();

        // Transactions filtrées, paginées (le PDF/Excel exporte toujours la totalité)
        $transactions = (clone $query)
            ->with(['agent', 'operateur'])
            ->latest('date')
            ->latest('id') // ordre stable d'une page à l'autre
            ->paginate(50)
            ->withQueryString();

        $statsGlobales = $this->statsGlobales($query);

        // Opérateurs pour le formulaire de filtres (tous les opérateurs actifs)
        $operateurs = Operateur::actif()->get();
        
        // Données pour les filtres
        $agents = Agent::actif()->with('utilisateur')->orderBy('nom')->get();
        $kiosques = Kiosque::actif()->orderBy('nom')->get();
        
        // Agents pour la recherche dans le filtre (format JSON)
        $agentsJson = $agents->map(function ($a) {
            $p = $a->utilisateur->prenom ?? '';
            $n = $a->utilisateur->nom ?? '';
            return [
                'id' => $a->id,
                'nom' => $n,
                'prenom' => $p,
                'libelle' => trim($p . ' ' . $n),
                'code_agent' => $a->code_agent,
            ];
        })->values()->toArray();
        
        return $this->ajaxView('pages.rapports.index', compact(
            'statsOperateurs',
            'topAgents',
            'transactions',
            'statsGlobales',
            'operateurs',
            'agents',
            'agentsJson',
            'kiosques',
            'dateDebut',
            'dateFin',
            'afficherTopAgents'
        ));
    }

    /**
     * Exporter le rapport en PDF avec tous les filtres appliqués
     */
    public function export(Request $request)
    {
        $afficherTopAgents = $this->afficherTopAgents($request);
        [$dateDebut, $dateFin] = $this->periode($request);
        $filtres = $this->filtres($request);

        $query = $this->requeteFiltree($filtres, $dateDebut, $dateFin);

        $statsGlobales = $this->statsGlobales($query);
        $statsOperateurs = $this->statsOperateurs($query, $filtres);

        $topAgents = $afficherTopAgents
            ? $this->buildTopAgents($filtres, $dateDebut, $dateFin)
            : collect();

        // Préparer les données pour le PDF
        $headers = ['Référence', 'Date', 'Type', 'Montant (XOF)', 'Opérateur', 'Agent', 'Client', 'Téléphone Client', 'Commission (XOF)', 'Statut'];
        
        // Logo (base64) calculé une fois par opérateur, et non une fois par transaction
        $cellulesOperateur = [];

        // lazy() : les transactions sont hydratées par lots au lieu d'être toutes chargées en modèles
        $data = (clone $query)
            ->with(['agent', 'operateur'])
            ->latest('date')
            ->latest('id')
            ->lazy(1000)
            ->map(function ($transaction) use (&$cellulesOperateur) {
                $operateurCell = '-';
                if ($transaction->operateur) {
                    $operateurCell = $cellulesOperateur[$transaction->operateur->id]
                        ??= $this->celluleOperateurPdf($transaction->operateur);
                }

                return [
                    $transaction->reference ?? '-',
                    $transaction->date ? $transaction->date->format('d/m/Y H:i') : '-',
                    ucfirst($transaction->type ?? '-'),
                    number_format($transaction->montant ?? 0, 0, ',', ' ') . ' XOF',
                    $operateurCell,
                    ($transaction->agent) ? (($transaction->agent->prenom ?? '') . ' ' . ($transaction->agent->nom ?? '')) : '-',
                    $transaction->client_nom ?? '-',
                    $transaction->client_telephone ?? '-',
                    number_format($transaction->commission ?? 0, 0, ',', ' ') . ' XOF',
                    ucfirst($transaction->statut ?? '-'),
                ];
            })
            ->all();

        // Générer le titre avec les filtres
        $filtresText = [];
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $filtresText[] = 'Période: ' . $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
        }
        $agentIds = $filtres['agent_id'];
        if ($agentIds !== []) {
            if (count($agentIds) < 5) {
                $agentNames = Agent::whereIn('id', $agentIds)->with('utilisateur')->get()->map(function($a) {
                    return $a->nomComplet;
                })->implode(', ');
                $filtresText[] = 'Agents: ' . $agentNames;
            }
        }
        $operateurIds = $filtres['operateur_id'];
        if ($operateurIds !== []) {
            if (count($operateurIds) < 5) {
                $operateurNames = Operateur::whereIn('id', $operateurIds)->pluck('libelle')->implode(', ');
                $filtresText[] = 'Opérateurs: ' . $operateurNames;
            }
        }

        $title = 'Rapport des Transactions';
        
        $filename = 'rapport_transactions_' . now()->format('Y-m-d_His');
        $filtersText = !empty($filtresText) ? implode(' · ', $filtresText) : null;

        if ($this->wantsExcelExport($request)) {
            return $this->exportRapportToExcel(
                $filename,
                $headers,
                $data,
                $statsGlobales,
                $statsOperateurs,
                $topAgents,
                $dateDebut,
                $dateFin,
                $filtersText
            );
        }

        return $this->exportRapportToPdf($title, $headers, $data, $filename . '.pdf', [
            'statsGlobales' => $statsGlobales,
            'statsOperateurs' => $statsOperateurs,
            'topAgents' => $topAgents,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'filtersText' => $filtersText,
        ], $request);
    }

    /**
     * Période du rapport : du début du mois à aujourd'hui par défaut, date de fin incluse.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periode(Request $request): array
    {
        return [
            $request->filled('date_debut') ? Carbon::parse($request->date_debut) : Carbon::now()->startOfMonth(),
            $request->filled('date_fin') ? Carbon::parse($request->date_fin)->endOfDay() : Carbon::now()->endOfDay(),
        ];
    }

    /**
     * Filtres multi-valeurs du formulaire, sans les valeurs vides ni « tous » (case « Tous »).
     *
     * @return array<string, array<int, string>>
     */
    private function filtres(Request $request): array
    {
        $filtres = [];
        foreach (['agent_id', 'operateur_id', 'type', 'statut', 'kiosque_id'] as $champ) {
            $valeurs = (array) $request->input($champ, []);
            $filtres[$champ] = array_values(array_filter(
                $valeurs,
                fn ($v) => $v !== null && $v !== '' && $v !== 'tous'
            ));
        }

        return $filtres;
    }

    private function requeteFiltree(array $filtres, Carbon $dateDebut, Carbon $dateFin): Builder
    {
        $query = Transaction::commerciale()->whereBetween('date', [$dateDebut, $dateFin]);

        foreach (['agent_id', 'operateur_id', 'type', 'statut'] as $champ) {
            if ($filtres[$champ] !== []) {
                $query->whereIn($champ, $filtres[$champ]);
            }
        }

        if ($filtres['kiosque_id'] !== []) {
            $query->whereHas('agent', fn ($q) => $q->whereIn('kiosque_id', $filtres['kiosque_id']));
        }

        return $query;
    }

    /**
     * Totaux des transactions valides (une requête).
     */
    private function statsGlobales(Builder $query): array
    {
        $totaux = (clone $query)->valide()
            ->selectRaw('COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant, COALESCE(SUM(commission), 0) as commission')
            ->selectRaw('COUNT(DISTINCT agent_id) as agents')
            ->toBase()
            ->first();

        return [
            'total_transactions' => (int) $totaux->nb,
            'montant_total' => (float) $totaux->montant,
            'commission_total' => (float) $totaux->commission,
            'nombre_agents' => (int) $totaux->agents,
        ];
    }

    /**
     * Totaux des transactions valides par opérateur actif (limités aux opérateurs filtrés), en une requête.
     */
    private function statsOperateurs(Builder $query, array $filtres): array
    {
        $parOperateur = (clone $query)->valide()
            ->groupBy('operateur_id')
            ->selectRaw('operateur_id, COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant, COALESCE(SUM(commission), 0) as commission')
            ->toBase()
            ->get()
            ->keyBy('operateur_id');

        return Operateur::actif()
            ->when($filtres['operateur_id'] !== [], fn ($q) => $q->whereIn('id', $filtres['operateur_id']))
            ->get()
            ->map(fn (Operateur $operateur) => [
                'operateur' => $operateur,
                'montant_total' => (float) ($parOperateur[$operateur->id]->montant ?? 0),
                'nombre_transactions' => (int) ($parOperateur[$operateur->id]->nb ?? 0),
                'commission_total' => (float) ($parOperateur[$operateur->id]->commission ?? 0),
            ])
            ->all();
    }

    /**
     * 10 meilleurs agents (montant des transactions valides) sur la période, en une requête groupée.
     * Comme auparavant, seuls les filtres opérateur, type et statut s'appliquent (pas agent ni kiosque).
     */
    private function buildTopAgents(array $filtres, Carbon $dateDebut, Carbon $dateFin)
    {
        $classement = Transaction::commerciale()->valide()
            ->whereBetween('date', [$dateDebut, $dateFin])
            ->when($filtres['operateur_id'] !== [], fn ($q) => $q->whereIn('operateur_id', $filtres['operateur_id']))
            ->when($filtres['type'] !== [], fn ($q) => $q->whereIn('type', $filtres['type']))
            ->when($filtres['statut'] !== [], fn ($q) => $q->whereIn('statut', $filtres['statut']))
            ->whereHas('agent')
            ->groupBy('agent_id')
            ->selectRaw('agent_id, COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant, COALESCE(SUM(commission), 0) as commission')
            ->orderByDesc('montant')
            ->limit(10)
            ->toBase()
            ->get();

        $agents = Agent::with(['utilisateur', 'kiosque'])
            ->whereIn('id', $classement->pluck('agent_id'))
            ->get()
            ->keyBy('id');

        return $classement
            ->filter(fn ($ligne) => $agents->has($ligne->agent_id))
            ->map(fn ($ligne) => [
                'agent' => $agents[$ligne->agent_id],
                'nombre_transactions' => (int) $ligne->nb,
                'montant_total' => (float) $ligne->montant,
                'commission_total' => (float) $ligne->commission,
            ])
            ->values();
    }

    /**
     * Cellule « Opérateur » du PDF : libellé + logo en base64, ou badge coloré si pas de logo.
     */
    private function celluleOperateurPdf(Operateur $operateur): array
    {
        $badge = [
            'couleur' => $operateur->couleur ?? '#3b82f6',
            'code' => strtoupper(substr($operateur->libelle, 0, 2)),
        ];

        $logo = $badge;
        if ($operateur->logo) {
            // Le logo est stocké dans storage/app/public/
            $logoPath = storage_path('app/public/' . $operateur->logo);
            if (file_exists($logoPath)) {
                $mimeType = getimagesize($logoPath)['mime'] ?? 'image/png';
                $logo = ['base64' => 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoPath))];
            }
        }

        return [
            'libelle' => $operateur->libelle ?? '-',
            'logo' => $logo,
        ];
    }
}
