<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Kiosque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RapportController extends Controller
{
    /**
     * Afficher la page des rapports
     */
    public function index(Request $request)
    {
        // Récupérer les paramètres de filtres
        $dateDebut = $request->filled('date_debut') ? Carbon::parse($request->date_debut) : Carbon::now()->startOfMonth();
        $dateFin = $request->filled('date_fin') ? Carbon::parse($request->date_fin)->endOfDay() : Carbon::now()->endOfDay();
        
        // Construire la requête de base
        $query = Transaction::with(['agent.utilisateur', 'operateur', 'agent.kiosque'])
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
            $statuts = array_filter($statuts); // Enlever les valeurs vides
            if (!empty($statuts)) {
                $query->whereIn('statut', $statuts);
            }
        }
        
        if ($request->filled('kiosque_id')) {
            $kiosqueIds = is_array($request->kiosque_id) ? $request->kiosque_id : [$request->kiosque_id];
            $kiosqueIds = array_filter($kiosqueIds); // Enlever les valeurs vides
            if (!empty($kiosqueIds)) {
                $query->whereHas('agent', function($q) use ($kiosqueIds) {
                    $q->whereIn('kiosque_id', $kiosqueIds);
                });
            }
        }
        
        // Statistiques par opérateur (pour les 4 cartes)
        $operateurs = Operateur::actif()->get();
        $statsOperateurs = [];
        
        foreach ($operateurs as $operateur) {
            $operateurQuery = clone $query;
            $operateurQuery->where('operateur_id', $operateur->id);
            
            $statsOperateurs[] = [
                'operateur' => $operateur,
                'montant_total' => $operateurQuery->valide()->sum('montant'),
                'nombre_transactions' => $operateurQuery->valide()->count(),
                'commission_total' => $operateurQuery->valide()->sum('commission'),
            ];
        }
        
        // Top agents (par nombre de transactions ou montant)
        $topAgents = Agent::with(['utilisateur', 'kiosque'])
            ->whereHas('transactions', function($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('date', [$dateDebut, $dateFin]);
            })
            ->get()
            ->map(function($agent) use ($request, $dateDebut, $dateFin) {
                $agentQuery = Transaction::whereBetween('date', [$dateDebut, $dateFin])
                    ->where('agent_id', $agent->id);
                
                // Appliquer les mêmes filtres que la requête principale
                if ($request->filled('operateur_id')) {
                    $operateurIds = is_array($request->operateur_id) ? $request->operateur_id : [$request->operateur_id];
                    $operateurIds = array_filter($operateurIds, function($id) {
                        return $id !== 'tous' && $id !== '';
                    });
                    if (!empty($operateurIds)) {
                        $agentQuery->whereIn('operateur_id', $operateurIds);
                    }
                }
                if ($request->filled('type')) {
                    $types = is_array($request->type) ? $request->type : [$request->type];
                    $types = array_filter($types, function($type) {
                        return $type !== 'tous' && $type !== '';
                    });
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
            ->filter(function($item) {
                return $item['nombre_transactions'] > 0;
            })
            ->sortByDesc('montant_total')
            ->take(3)
            ->values();
        
        // Dernières transactions
        $dernieresTransactions = (clone $query)
            ->with(['agent.utilisateur', 'operateur'])
            ->latest('date')
            ->take(5)
            ->get();
        
        // Statistiques globales
        $statsGlobales = [
            'total_transactions' => (clone $query)->valide()->count(),
            'montant_total' => (clone $query)->valide()->sum('montant'),
            'commission_total' => (clone $query)->valide()->sum('commission'),
            'nombre_agents' => (clone $query)->valide()->distinct('agent_id')->count('agent_id'),
        ];
        
        // Données pour les filtres
        $agents = Agent::actif()->with('utilisateur')->orderBy('nom')->get();
        $kiosques = Kiosque::actif()->orderBy('nom')->get();
        
        return view('pages.rapports.index', compact(
            'statsOperateurs',
            'topAgents',
            'dernieresTransactions',
            'statsGlobales',
            'operateurs',
            'agents',
            'kiosques',
            'dateDebut',
            'dateFin'
        ));
    }
}
