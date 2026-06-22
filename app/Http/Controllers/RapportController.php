<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Kiosque;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // Récupérer les paramètres de filtres
        $dateDebut = $request->filled('date_debut') ? Carbon::parse($request->date_debut) : Carbon::now()->startOfMonth();
        $dateFin = $request->filled('date_fin') ? Carbon::parse($request->date_fin)->endOfDay() : Carbon::now()->endOfDay();
        
        // Construire la requête de base
        $query = Transaction::commerciale()->with(['agent.utilisateur', 'operateur', 'agent.kiosque'])
            ->whereBetween('date', [$dateDebut, $dateFin]);
        
        // Appliquer les filtres
        if ($request->filled('agent_id')) {
            $agentIds = is_array($request->agent_id) ? $request->agent_id : [$request->agent_id];
            $agentIds = array_filter($agentIds, function($id) {
                return $id !== 'tous' && $id !== '';
            }); // Enlever les valeurs vides et "tous"
            if (!empty($agentIds)) {
                $query->whereIn('agent_id', $agentIds);
            }
        }
        
        if ($request->filled('operateur_id')) {
            $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
            $operateurIds = array_filter($operateurIds, function($id) {
                return $id !== 'tous' && $id !== '';
            }); // Enlever les valeurs vides et "tous"
            if (!empty($operateurIds)) {
                $query->whereIn('operateur_id', $operateurIds);
            }
        }
        
        if ($request->filled('type')) {
            $types = is_array($request->type) ? $request->type : [$request->type];
            $types = array_filter($types, function($type) {
                return $type !== 'tous' && $type !== '';
            }); // Enlever les valeurs vides et "tous"
            if (!empty($types)) {
                $query->whereIn('type', $types);
            }
        }
        
        if ($request->filled('statut')) {
            $statuts = is_array($request->statut) ? $request->statut : [$request->statut];
            $statuts = array_filter($statuts, function($s) {
                return $s !== 'tous' && $s !== '';
            }); // Enlever les valeurs vides et "tous"
            if (!empty($statuts)) {
                $query->whereIn('statut', $statuts);
            }
        }
        
        if ($request->filled('kiosque_id')) {
            $kiosqueIds = is_array($request->kiosque_id) ? $request->kiosque_id : [$request->kiosque_id];
            $kiosqueIds = array_filter($kiosqueIds, function($id) {
                return $id !== 'tous' && $id !== '';
            }); // Enlever les valeurs vides et "tous"
            if (!empty($kiosqueIds)) {
                $query->whereHas('agent', function($q) use ($kiosqueIds) {
                    $q->whereIn('kiosque_id', $kiosqueIds);
                });
            }
        }
        
        // Statistiques par opérateur (filtrées si un filtre opérateur est sélectionné)
        $operateursQuery = Operateur::actif();
        
        // Si un filtre opérateur est sélectionné, ne garder que ces opérateurs
        if ($request->filled('operateur_id')) {
            $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
            $operateurIds = array_filter($operateurIds, function($id) {
                return $id !== 'tous' && $id !== '';
            });
            if (!empty($operateurIds)) {
                $operateursQuery->whereIn('id', $operateurIds);
            }
        }
        
        $operateursFiltres = $operateursQuery->get();
        $statsOperateurs = [];
        
        foreach ($operateursFiltres as $operateur) {
            $operateurQuery = clone $query;
            $operateurQuery->where('operateur_id', $operateur->id);
            
            $statsOperateurs[] = [
                'operateur' => $operateur,
                'montant_total' => $operateurQuery->valide()->sum('montant'),
                'nombre_transactions' => $operateurQuery->valide()->count(),
                'commission_total' => $operateurQuery->valide()->sum('commission'),
            ];
        }
        
        $topAgents = $afficherTopAgents
            ? $this->buildTopAgents($request, $dateDebut, $dateFin)
            : collect();
        
        // Toutes les transactions filtrées (pas seulement les 5 dernières, comme dans le PDF)
        $transactions = (clone $query)
            ->with(['agent.utilisateur', 'operateur'])
            ->latest('date')
            ->get();
        
        // Statistiques globales
        $statsGlobales = [
            'total_transactions' => (clone $query)->valide()->count(),
            'montant_total' => (clone $query)->valide()->sum('montant'),
            'commission_total' => (clone $query)->valide()->sum('commission'),
            'nombre_agents' => (clone $query)->valide()->distinct('agent_id')->count('agent_id'),
        ];
        
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

        // Récupérer les paramètres de filtres (même logique que index)
        $dateDebut = $request->filled('date_debut') ? Carbon::parse($request->date_debut) : Carbon::now()->startOfMonth();
        $dateFin = $request->filled('date_fin') ? Carbon::parse($request->date_fin)->endOfDay() : Carbon::now()->endOfDay();
        
        // Construire la requête de base
        $query = Transaction::commerciale()->with(['agent.utilisateur', 'operateur', 'agent.kiosque'])
            ->whereBetween('date', [$dateDebut, $dateFin]);
        
        // Appliquer les filtres (même logique que index)
        if ($request->filled('agent_id')) {
            $agentIds = is_array($request->agent_id) ? $request->agent_id : [$request->agent_id];
            $agentIds = array_filter($agentIds, function($id) {
                return $id !== 'tous' && $id !== '';
            });
            if (!empty($agentIds)) {
                $query->whereIn('agent_id', $agentIds);
            }
        }
        
        if ($request->filled('operateur_id')) {
            $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
            $operateurIds = array_filter($operateurIds, function($id) {
                return $id !== 'tous' && $id !== '';
            });
            if (!empty($operateurIds)) {
                $query->whereIn('operateur_id', $operateurIds);
            }
        }
        
        if ($request->filled('type')) {
            $types = is_array($request->type) ? $request->type : [$request->type];
            $types = array_filter($types, function($type) {
                return $type !== 'tous' && $type !== '';
            });
            if (!empty($types)) {
                $query->whereIn('type', $types);
            }
        }
        
        if ($request->filled('statut')) {
            $statuts = is_array($request->statut) ? $request->statut : [$request->statut];
            $statuts = array_filter($statuts);
            if (!empty($statuts)) {
                $query->whereIn('statut', $statuts);
            }
        }
        
        if ($request->filled('kiosque_id')) {
            $kiosqueIds = is_array($request->kiosque_id) ? $request->kiosque_id : [$request->kiosque_id];
            $kiosqueIds = array_filter($kiosqueIds);
            if (!empty($kiosqueIds)) {
                $query->whereHas('agent', function($q) use ($kiosqueIds) {
                    $q->whereIn('kiosque_id', $kiosqueIds);
                });
            }
        }

        // Récupérer toutes les transactions filtrées (pas seulement les 5 dernières)
        $transactions = (clone $query)
            ->with(['agent.utilisateur', 'operateur'])
            ->latest('date')
            ->get();

        // Statistiques globales
        $statsGlobales = [
            'total_transactions' => (clone $query)->valide()->count(),
            'montant_total' => (clone $query)->valide()->sum('montant'),
            'commission_total' => (clone $query)->valide()->sum('commission'),
            'nombre_agents' => (clone $query)->valide()->distinct('agent_id')->count('agent_id'),
        ];

        // Statistiques par opérateur (filtrées si un filtre opérateur est sélectionné)
        $operateursQuery = Operateur::actif();
        
        // Si un filtre opérateur est sélectionné, ne garder que ces opérateurs
        if ($request->filled('operateur_id')) {
            $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
            $operateurIds = array_filter($operateurIds, function($id) {
                return $id !== 'tous' && $id !== '';
            });
            if (!empty($operateurIds)) {
                $operateursQuery->whereIn('id', $operateurIds);
            }
        }
        
        $operateursFiltres = $operateursQuery->get();
        $statsOperateurs = [];
        
        foreach ($operateursFiltres as $operateur) {
            $operateurQuery = clone $query;
            $operateurQuery->where('operateur_id', $operateur->id);
            
            $statsOperateurs[] = [
                'operateur' => $operateur,
                'montant_total' => $operateurQuery->valide()->sum('montant'),
                'nombre_transactions' => $operateurQuery->valide()->count(),
                'commission_total' => $operateurQuery->valide()->sum('commission'),
            ];
        }

        $topAgents = $afficherTopAgents
            ? $this->buildTopAgents($request, $dateDebut, $dateFin)
            : collect();

        // Préparer les données pour le PDF
        $headers = ['Référence', 'Date', 'Type', 'Montant (XOF)', 'Opérateur', 'Agent', 'Client', 'Téléphone Client', 'Commission (XOF)', 'Statut'];
        
        $data = $transactions->map(function($transaction) {
            $operateurCell = '-';
            
            if ($transaction->operateur) {
                $operateurLibelle = $transaction->operateur->libelle ?? '-';
                $operateurLogo = null;
                
                if ($transaction->operateur->logo) {
                    // Le logo est stocké dans storage/app/public/
                    $logoPath = storage_path('app/public/' . $transaction->operateur->logo);
                    if (file_exists($logoPath)) {
                        // Convertir l'image en base64 pour le PDF
                        $imageData = file_get_contents($logoPath);
                        $imageInfo = getimagesize($logoPath);
                        $mimeType = $imageInfo['mime'] ?? 'image/png';
                        $base64 = base64_encode($imageData);
                        
                        $operateurLogo = [
                            'base64' => 'data:' . $mimeType . ';base64,' . $base64
                        ];
                    } else {
                        // Fallback si le fichier n'existe pas
                        $operateurLogo = [
                            'couleur' => $transaction->operateur->couleur ?? '#3b82f6',
                            'code' => strtoupper(substr($transaction->operateur->libelle, 0, 2))
                        ];
                    }
                } else {
                    // Pas de logo, utiliser un badge coloré
                    $operateurLogo = [
                        'couleur' => $transaction->operateur->couleur ?? '#3b82f6',
                        'code' => strtoupper(substr($transaction->operateur->libelle, 0, 2))
                    ];
                }
                
                $operateurCell = [
                    'libelle' => $operateurLibelle,
                    'logo' => $operateurLogo,
                ];
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
        })->toArray();

        // Générer le titre avec les filtres
        $filtresText = [];
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $filtresText[] = 'Période: ' . $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
        }
        if ($request->filled('agent_id')) {
            $agentIds = is_array($request->agent_id) ? $request->agent_id : [$request->agent_id];
            $agentIds = array_filter($agentIds, function($id) {
                return $id !== 'tous' && $id !== '';
            });
            if (!empty($agentIds) && count($agentIds) < 5) {
                $agentNames = Agent::whereIn('id', $agentIds)->with('utilisateur')->get()->map(function($a) {
                    return $a->nomComplet;
                })->implode(', ');
                $filtresText[] = 'Agents: ' . $agentNames;
            }
        }
        if ($request->filled('operateur_id')) {
            $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
            $operateurIds = array_filter($operateurIds, function($id) {
                return $id !== 'tous' && $id !== '';
            });
            if (!empty($operateurIds) && count($operateurIds) < 5) {
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

    private function buildTopAgents(Request $request, Carbon $dateDebut, Carbon $dateFin)
    {
        return Agent::with(['utilisateur', 'kiosque'])
            ->whereHas('transactions', function ($q) use ($dateDebut, $dateFin) {
                $q->commerciale()->whereBetween('date', [$dateDebut, $dateFin]);
            })
            ->get()
            ->map(function ($agent) use ($request, $dateDebut, $dateFin) {
                $agentQuery = Transaction::commerciale()->whereBetween('date', [$dateDebut, $dateFin])
                    ->where('agent_id', $agent->id);

                if ($request->filled('operateur_id')) {
                    $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
                    $operateurIds = array_filter($operateurIds, fn ($id) => $id !== 'tous' && $id !== '');
                    if (!empty($operateurIds)) {
                        $agentQuery->whereIn('operateur_id', $operateurIds);
                    }
                }
                if ($request->filled('type')) {
                    $types = is_array($request->type) ? $request->type : [$request->type];
                    $types = array_filter($types, fn ($type) => $type !== 'tous' && $type !== '');
                    if (!empty($types)) {
                        $agentQuery->whereIn('type', $types);
                    }
                }
                if ($request->filled('statut')) {
                    $statuts = is_array($request->statut) ? $request->statut : [$request->statut];
                    $statuts = array_filter($statuts);
                    if (!empty($statuts)) {
                        $agentQuery->whereIn('statut', $statuts);
                    }
                }

                return [
                    'agent' => $agent,
                    'nombre_transactions' => $agentQuery->valide()->count(),
                    'montant_total' => $agentQuery->valide()->sum('montant'),
                    'commission_total' => $agentQuery->valide()->sum('commission'),
                ];
            })
            ->filter(fn ($item) => $item['nombre_transactions'] > 0)
            ->sortByDesc('montant_total')
            ->take(10)
            ->values();
    }
}
