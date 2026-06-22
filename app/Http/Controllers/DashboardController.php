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
            'transactions_jour' => Transaction::commerciale()->valide()->duJour()->count(),
            'montant_jour' => Transaction::commerciale()->valide()->duJour()->sum('montant'),
            'commission_jour' => Transaction::commerciale()->valide()->duJour()->sum('commission'),
            
            // Transactions du mois
            'transactions_mois' => Transaction::commerciale()->valide()->duMois()->count(),
            'montant_mois' => Transaction::commerciale()->valide()->duMois()->sum('montant'),
            'commission_mois' => Transaction::commerciale()->valide()->duMois()->sum('commission'),
            
            // Agents et kiosques
            'agents_actifs' => Agent::actif()->count(),
            'agents_total' => Agent::count(),
            'kiosques_actifs' => Kiosque::actif()->count(),
            'kiosques_satures' => Kiosque::actif()->get()->filter(fn($k) => $k->estSature())->count(),
        ];

        // Transactions par type (du jour)
        $transactionsParType = [
            'depot' => Transaction::commerciale()->valide()->depot()->duJour()->sum('montant'),
            'retrait' => Transaction::commerciale()->valide()->retrait()->duJour()->sum('montant'),
            'transfert' => Transaction::commerciale()->valide()->where('type', 'transfert')->duJour()->sum('montant'),
            'paiement' => Transaction::commerciale()->valide()->where('type', 'paiement')->duJour()->sum('montant'),
        ];

        // Transactions par opérateur (du mois)
        $operateurs = Operateur::actif()->get()->map(function($operateur) {
            return [
                'operateur' => $operateur,
                'transactions' => Transaction::commerciale()->valide()
                    ->where('operateur_id', $operateur->id)
                    ->duMois()
                    ->count(),
                'montant' => Transaction::commerciale()->valide()
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
            ->whereNull('transactions.type_operation_id')
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
        $dernieresTransactions = Transaction::commerciale()->with(['agent', 'operateur'])
            ->latest('date')
            ->limit(10)
            ->get();

        // Évolution des transactions (7 derniers jours)
        $evolutionTransactions = collect(range(6, 0))->map(function($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date' => $date->format('Y-m-d'),
                'jour' => $date->locale('fr')->isoFormat('dddd'),
                'count' => Transaction::commerciale()->valide()->whereDate('date', $date)->count(),
                'montant' => Transaction::commerciale()->valide()->whereDate('date', $date)->sum('montant'),
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
            'transactions_jour' => Transaction::commerciale()->valide()->duJour()->count(),
            'montant_jour' => Transaction::commerciale()->valide()->duJour()->sum('montant'),
            'agents_en_ligne' => Agent::actif()->count(),
            'derniere_transaction' => Transaction::commerciale()->with(['agent', 'operateur'])
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
                        'montant' => Transaction::commerciale()->valide()->whereDate('date', $date)->sum('montant'),
                        'count' => Transaction::commerciale()->valide()->whereDate('date', $date)->count(),
                    ];
                });
                break;

            case '30jours':
                $data = collect(range(29, 0))->map(function($daysAgo) {
                    $date = now()->subDays($daysAgo);
                    return [
                        'label' => $date->format('d/m'),
                        'date' => $date->format('Y-m-d'),
                        'montant' => Transaction::commerciale()->valide()->whereDate('date', $date)->sum('montant'),
                        'count' => Transaction::commerciale()->valide()->whereDate('date', $date)->count(),
                    ];
                });
                break;

            case '12mois':
                $data = collect(range(11, 0))->map(function($monthsAgo) {
                    $date = now()->subMonths($monthsAgo);
                    return [
                        'label' => $date->locale('fr')->isoFormat('MMM YYYY'),
                        'date' => $date->format('Y-m'),
                        'montant' => Transaction::commerciale()->valide()
                            ->whereYear('date', $date->year)
                            ->whereMonth('date', $date->month)
                            ->sum('montant'),
                        'count' => Transaction::commerciale()->valide()
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
                    'count' => Transaction::commerciale()->valide()
                        ->where('operateur_id', $operateur->id)
                        ->duJour()
                        ->count(),
                    'montant' => Transaction::commerciale()->valide()
                        ->where('operateur_id', $operateur->id)
                        ->duJour()
                        ->sum('montant'),
                ],
                'mois' => [
                    'count' => Transaction::commerciale()->valide()
                        ->where('operateur_id', $operateur->id)
                        ->duMois()
                        ->count(),
                    'montant' => Transaction::commerciale()->valide()
                        ->where('operateur_id', $operateur->id)
                        ->duMois()
                        ->sum('montant'),
                ],
            ];
        });

        return response()->json($stats);
    }

    /**
     * API pour la carte de performance du mois (chiffre d'affaires par zone / quartier)
     */
    public function cartePerformanceMois()
    {
        try {
            $transactions = Transaction::query()
                ->commerciale()
                ->valide()
                ->duMois()
                ->with(['agent.kiosque'])
                ->get();

            $groups = [];

            foreach ($transactions as $transaction) {
                $kiosque = $transaction->agent?->kiosque;
                if (! $kiosque) {
                    continue;
                }

                $zone = trim((string) ($kiosque->quartier ?? '')) ?: 'Non renseignée';
                $ville = trim((string) ($kiosque->ville ?? '')) ?: 'Lomé';
                $key = $zone.'|'.$ville;

                if (! isset($groups[$key])) {
                    $groups[$key] = [
                        'zone' => $zone,
                        'ville' => $ville,
                        'kiosque_ids' => [],
                        'latitudes' => [],
                        'longitudes' => [],
                        'montant' => 0.0,
                        'transactions' => 0,
                    ];
                }

                $groups[$key]['kiosque_ids'][$kiosque->id] = true;
                if ($kiosque->latitude !== null && $kiosque->longitude !== null) {
                    $groups[$key]['latitudes'][] = (float) $kiosque->latitude;
                    $groups[$key]['longitudes'][] = (float) $kiosque->longitude;
                }
                $groups[$key]['montant'] += (float) $transaction->montant;
                $groups[$key]['transactions']++;
            }

            $rows = collect($groups)
                ->filter(fn (array $row) => $row['montant'] > 0)
                ->sortByDesc('montant')
                ->values();

            $totalMois = (float) $rows->sum('montant');

            $points = $rows->map(function (array $row, int $index) use ($totalMois) {
                $montant = (float) $row['montant'];
                $zone = $row['zone'];
                $ville = $row['ville'];
                $lat = $row['latitudes'] !== [] ? array_sum($row['latitudes']) / count($row['latitudes']) : null;
                $lng = $row['longitudes'] !== [] ? array_sum($row['longitudes']) / count($row['longitudes']) : null;
                $coords = $this->resolveZoneCoordinates($zone, $ville, $lat, $lng);

                return [
                    'id' => md5($zone.'|'.$ville),
                    'zone' => $zone,
                    'ville' => $ville,
                    'nom' => $zone.($ville ? ', '.$ville : ''),
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude'],
                    'approximate' => $coords['approximate'],
                    'kiosques' => count($row['kiosque_ids']),
                    'montant' => $montant,
                    'transactions' => (int) $row['transactions'],
                    'rang' => $index + 1,
                    'part_pct' => $totalMois > 0 ? round(($montant / $totalMois) * 100, 1) : 0,
                ];
            });

            return response()->json($points->values());
        } catch (\Exception $e) {
            Log::error('[Dashboard] Erreur dans cartePerformanceMois', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erreur lors du calcul des données'], 500);
        }
    }

    /**
     * Coordonnées GPS d'une zone : kiosque géolocalisé, quartier connu, ou position approximative autour de Lomé.
     *
     * @return array{latitude: float, longitude: float, approximate: bool}
     */
    private function resolveZoneCoordinates(string $zone, string $ville, ?float $lat, ?float $lng): array
    {
        if ($lat && $lng) {
            return [
                'latitude' => $lat,
                'longitude' => $lng,
                'approximate' => false,
            ];
        }

        $known = [
            'agoè' => [6.1667, 1.2167],
            'agoe' => [6.1667, 1.2167],
            'tokoin' => [6.1733, 1.2309],
            'bè' => [6.1289, 1.2158],
            'be' => [6.1289, 1.2158],
            'be-kpota' => [6.1289, 1.2158],
        ];
        $key = strtolower(trim($zone));
        if (isset($known[$key])) {
            return [
                'latitude' => $known[$key][0],
                'longitude' => $known[$key][1],
                'approximate' => true,
            ];
        }

        $hash = crc32($zone.'|'.$ville);
        $angle = ($hash % 360) * (M_PI / 180);
        $radius = 0.008 + (($hash >> 8) % 100) / 10000;

        return [
            'latitude' => 6.1375 + ($radius * cos($angle)),
            'longitude' => 1.2123 + ($radius * sin($angle)),
            'approximate' => true,
        ];
    }
}
