<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Transaction;
use App\Models\Operateur;
use App\Models\Kiosque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord principal
     */
    public function index()
    {
        // Statistiques globales
        $stats = [
            // Transactions du jour
            'transactions_jour' => Transaction::valide()->duJour()->count(),
            'montant_jour' => Transaction::valide()->duJour()->sum('montant'),
            'commission_jour' => Transaction::valide()->duJour()->sum('commission'),
            
            // Transactions du mois
            'transactions_mois' => Transaction::valide()->duMois()->count(),
            'montant_mois' => Transaction::valide()->duMois()->sum('montant'),
            'commission_mois' => Transaction::valide()->duMois()->sum('commission'),
            
            // Agents et kiosques
            'agents_actifs' => Agent::actif()->count(),
            'agents_total' => Agent::count(),
            'kiosques_actifs' => Kiosque::actif()->count(),
            'kiosques_satures' => Kiosque::actif()->get()->filter(fn($k) => $k->estSature())->count(),
        ];

        // Transactions par type (du jour)
        $transactionsParType = [
            'depot' => Transaction::valide()->depot()->duJour()->sum('montant'),
            'retrait' => Transaction::valide()->retrait()->duJour()->sum('montant'),
            'transfert' => Transaction::valide()->where('type', 'transfert')->duJour()->sum('montant'),
            'paiement' => Transaction::valide()->where('type', 'paiement')->duJour()->sum('montant'),
        ];

        // Transactions par opérateur (du mois)
        $operateurs = Operateur::actif()->get()->map(function($operateur) {
            return [
                'operateur' => $operateur,
                'transactions' => Transaction::valide()
                    ->where('operateur_id', $operateur->id)
                    ->duMois()
                    ->count(),
                'montant' => Transaction::valide()
                    ->where('operateur_id', $operateur->id)
                    ->duMois()
                    ->sum('montant'),
            ];
        });

        // Top 10 agents du mois
        $topAgents = Agent::select([
                'agents.id',
                'agents.uid',
                'agents.code_agent',
                'agents.nom',
                'agents.prenom',
                'agents.telephone',
                'agents.kiosque_id',
                'agents.user_id',
                DB::raw('SUM(transactions.montant) as total_montant'),
                DB::raw('COUNT(transactions.id) as total_transactions')
            ])
            ->join('transactions', 'agents.id', '=', 'transactions.agent_id')
            ->where('transactions.statut', 'valide')
            ->whereMonth('transactions.date', now()->month)
            ->groupBy([
                'agents.id',
                'agents.uid',
                'agents.code_agent',
                'agents.nom',
                'agents.prenom',
                'agents.telephone',
                'agents.kiosque_id',
                'agents.user_id'
            ])
            ->with('kiosque', 'utilisateur')
            ->orderBy('total_montant', 'desc')
            ->limit(10)
            ->get();

        // Dernières transactions
        $dernieresTransactions = Transaction::with(['agent', 'operateur'])
            ->latest('date')
            ->limit(10)
            ->get();

        // Évolution des transactions (7 derniers jours)
        $evolutionTransactions = collect(range(6, 0))->map(function($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date' => $date->format('Y-m-d'),
                'jour' => $date->locale('fr')->isoFormat('dddd'),
                'count' => Transaction::valide()->whereDate('date', $date)->count(),
                'montant' => Transaction::valide()->whereDate('date', $date)->sum('montant'),
            ];
        });

        // Kiosques nécessitant attention (sans agent ou saturés)
        $kiosquesAttention = Kiosque::actif()
            ->with(['agentsActifs'])
            ->get()
            ->filter(function($kiosque) {
                return $kiosque->agentsActifs->count() === 0 || $kiosque->estSature();
            });

        return $this->ajaxView('pages.dashboard.index', compact(
            'stats',
            'transactionsParType',
            'operateurs',
            'topAgents',
            'dernieresTransactions',
            'evolutionTransactions',
            'kiosquesAttention'
        ));
    }

    /**
     * API pour les statistiques temps réel
     */
    public function statsTempsReel()
    {
        $stats = [
            'transactions_jour' => Transaction::valide()->duJour()->count(),
            'montant_jour' => Transaction::valide()->duJour()->sum('montant'),
            'agents_en_ligne' => Agent::actif()->count(),
            'derniere_transaction' => Transaction::with(['agent', 'operateur'])
                ->latest('date')
                ->first(),
        ];

        return response()->json($stats);
    }

    /**
     * API pour le graphique des transactions
     */
    public function graphiqueTransactions(Request $request)
    {
        $periode = $request->get('periode', '7jours'); // 7jours, 30jours, 12mois

        switch ($periode) {
            case '7jours':
                $data = collect(range(6, 0))->map(function($daysAgo) {
                    $date = now()->subDays($daysAgo);
                    return [
                        'label' => $date->locale('fr')->isoFormat('DD MMM'),
                        'date' => $date->format('Y-m-d'),
                        'montant' => Transaction::valide()->whereDate('date', $date)->sum('montant'),
                        'count' => Transaction::valide()->whereDate('date', $date)->count(),
                    ];
                });
                break;

            case '30jours':
                $data = collect(range(29, 0))->map(function($daysAgo) {
                    $date = now()->subDays($daysAgo);
                    return [
                        'label' => $date->format('d/m'),
                        'date' => $date->format('Y-m-d'),
                        'montant' => Transaction::valide()->whereDate('date', $date)->sum('montant'),
                        'count' => Transaction::valide()->whereDate('date', $date)->count(),
                    ];
                });
                break;

            case '12mois':
                $data = collect(range(11, 0))->map(function($monthsAgo) {
                    $date = now()->subMonths($monthsAgo);
                    return [
                        'label' => $date->locale('fr')->isoFormat('MMM YYYY'),
                        'date' => $date->format('Y-m'),
                        'montant' => Transaction::valide()
                            ->whereYear('date', $date->year)
                            ->whereMonth('date', $date->month)
                            ->sum('montant'),
                        'count' => Transaction::valide()
                            ->whereYear('date', $date->year)
                            ->whereMonth('date', $date->month)
                            ->count(),
                    ];
                });
                break;

            default:
                return response()->json(['error' => 'Période invalide'], 400);
        }

        return response()->json($data);
    }

    /**
     * API pour les statistiques par opérateur
     */
    public function statsParOperateur()
    {
        $stats = Operateur::actif()->get()->map(function($operateur) {
            return [
                'operateur' => $operateur->only(['id', 'code', 'libelle', 'couleur']),
                'jour' => [
                    'count' => Transaction::valide()
                        ->where('operateur_id', $operateur->id)
                        ->duJour()
                        ->count(),
                    'montant' => Transaction::valide()
                        ->where('operateur_id', $operateur->id)
                        ->duJour()
                        ->sum('montant'),
                ],
                'mois' => [
                    'count' => Transaction::valide()
                        ->where('operateur_id', $operateur->id)
                        ->duMois()
                        ->count(),
                    'montant' => Transaction::valide()
                        ->where('operateur_id', $operateur->id)
                        ->duMois()
                        ->sum('montant'),
                ],
            ];
        });

        return response()->json($stats);
    }

    /**
     * API pour la carte de performance du mois (chiffre d'affaires par kiosque)
     */
    public function cartePerformanceMois()
    {
        try {
            Log::info('[Dashboard] cartePerformanceMois appelée', [
                'timestamp' => now()->toDateTimeString(),
                'ip' => request()->ip(),
            ]);

            // Agréger le montant total des transactions valides du mois par kiosque (via les agents)
            $points = Kiosque::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->leftJoin('agents', 'agents.kiosque_id', '=', 'kiosques.id')
                ->leftJoin('transactions', function ($join) {
                    $join->on('transactions.agent_id', '=', 'agents.id')
                        ->where('transactions.statut', 'valide')
                        ->whereBetween('transactions.date', [now()->startOfMonth(), now()->endOfMonth()]);
                })
                ->groupBy('kiosques.id', 'kiosques.nom', 'kiosques.latitude', 'kiosques.longitude')
                ->select([
                    'kiosques.id',
                    'kiosques.nom',
                    'kiosques.latitude',
                    'kiosques.longitude',
                    DB::raw('COALESCE(SUM(transactions.montant), 0) as montant'),
                ])
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'nom' => $row->nom,
                        'latitude' => (float) $row->latitude,
                        'longitude' => (float) $row->longitude,
                        'montant' => (float) $row->montant,
                    ];
                });

            Log::info('[Dashboard] cartePerformanceMois points calculés', [
                'count' => $points->count(),
                'sample' => $points->first(),
                'all_points' => $points->toArray(),
            ]);

            return response()->json($points);
        } catch (\Exception $e) {
            Log::error('[Dashboard] Erreur dans cartePerformanceMois', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Erreur lors du calcul des données'], 500);
        }
    }
}
