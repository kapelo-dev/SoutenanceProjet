<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    /**
     * Dashboard dédié à l'agent connecté
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $agent = $user?->agent;

        if (!$user || !$agent) {
            abort(403, 'Accès réservé aux agents.');
        }

        $baseQuery = Transaction::with(['operateur'])
            ->where('agent_id', $agent->id);

        $todayQuery = (clone $baseQuery)->duJour()->valide();
        $monthQuery = (clone $baseQuery)->duMois()->valide();
        $allValidQuery = (clone $baseQuery)->valide();

        $stats = [
            'today_count' => $todayQuery->count(),
            'today_total' => $todayQuery->sum('montant'),
            'month_count' => $monthQuery->count(),
            'month_total' => $monthQuery->sum('montant'),
            'month_commission' => $monthQuery->sum('commission'),
            'all_count' => $allValidQuery->count(),
            'all_total' => $allValidQuery->sum('montant'),
        ];

        $latestTransactions = (clone $baseQuery)
            ->latest('date')
            ->limit(20)
            ->get();

        return $this->ajaxView('pages.agents.dashboard.index', [
            'agent' => $agent->load(['kiosque', 'utilisateur']),
            'stats' => $stats,
            'transactions' => $latestTransactions,
        ]);
    }
}

