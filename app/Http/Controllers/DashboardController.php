<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Transaction;
use App\Models\Operateur;
use App\Models\Kiosque;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord principal
     */
    public function index()
    {
        $debutJour = now()->startOfDay();
        $debutMois = now()->startOfMonth();

        // Statistiques du jour et du mois en une seule requête (le jour est inclus dans le mois)
        $totaux = Transaction::commerciale()->valide()
            ->whereBetween('date', [$debutMois, now()->endOfMonth()])
            ->selectRaw('COUNT(*) as transactions_mois')
            ->selectRaw('COALESCE(SUM(montant), 0) as montant_mois')
            ->selectRaw('COALESCE(SUM(commission), 0) as commission_mois')
            ->selectRaw('COUNT(CASE WHEN date >= ? THEN 1 END) as transactions_jour', [$debutJour])
            ->selectRaw('COALESCE(SUM(CASE WHEN date >= ? THEN montant END), 0) as montant_jour', [$debutJour])
            ->selectRaw('COALESCE(SUM(CASE WHEN date >= ? THEN commission END), 0) as commission_jour', [$debutJour])
            ->selectRaw("COALESCE(SUM(CASE WHEN date >= ? AND type = 'depot' THEN montant END), 0) as depot_jour", [$debutJour])
            ->selectRaw("COALESCE(SUM(CASE WHEN date >= ? AND type = 'retrait' THEN montant END), 0) as retrait_jour", [$debutJour])
            ->selectRaw("COALESCE(SUM(CASE WHEN date >= ? AND type = 'transfert' THEN montant END), 0) as transfert_jour", [$debutJour])
            ->selectRaw("COALESCE(SUM(CASE WHEN date >= ? AND type = 'paiement' THEN montant END), 0) as paiement_jour", [$debutJour])
            ->toBase()
            ->first();

        $stats = [
            'transactions_jour' => (int) $totaux->transactions_jour,
            'montant_jour' => (float) $totaux->montant_jour,
            'commission_jour' => (float) $totaux->commission_jour,
            'transactions_mois' => (int) $totaux->transactions_mois,
            'montant_mois' => (float) $totaux->montant_mois,
            'commission_mois' => (float) $totaux->commission_mois,
            'agents_actifs' => Agent::actif()->count(),
            'kiosques_actifs' => Kiosque::actif()->count(),
        ];

        $transactionsParType = [
            'depot' => (float) $totaux->depot_jour,
            'retrait' => (float) $totaux->retrait_jour,
            'transfert' => (float) $totaux->transfert_jour,
            'paiement' => (float) $totaux->paiement_jour,
        ];

        // Transactions par opérateur (du mois)
        $parOperateur = $this->totauxParOperateur($debutMois, now()->endOfMonth());
        $operateurs = Operateur::actif()->get()->map(fn ($operateur) => [
            'operateur' => $operateur,
            'transactions' => (int) ($parOperateur[$operateur->id]->nb ?? 0),
            'montant' => (float) ($parOperateur[$operateur->id]->montant ?? 0),
        ]);

        // Dernières transactions
        $dernieresTransactions = Transaction::commerciale()->with(['agent', 'operateur'])
            ->latest('date')
            ->limit(10)
            ->get();

        // Évolution des transactions (7 derniers jours)
        $evolutionTransactions = $this->serieJournaliere(7)->map(fn (array $jour) => [
            'date' => $jour['date']->format('Y-m-d'),
            'jour' => $jour['date']->locale('fr')->isoFormat('dddd'),
            'count' => $jour['count'],
            'montant' => $jour['montant'],
        ]);

        return $this->ajaxView('pages.dashboard.index', compact(
            'stats',
            'transactionsParType',
            'operateurs',
            'dernieresTransactions',
            'evolutionTransactions'
        ));
    }

    /**
     * API pour les statistiques temps réel
     */
    public function statsTempsReel()
    {
        $jour = Transaction::commerciale()->valide()->duJour()
            ->selectRaw('COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant')
            ->toBase()
            ->first();

        $stats = [
            'transactions_jour' => (int) $jour->nb,
            'montant_jour' => (float) $jour->montant,
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
                $data = $this->serieJournaliere(7)->map(fn (array $jour) => [
                    'label' => $jour['date']->locale('fr')->isoFormat('DD MMM'),
                    'date' => $jour['date']->format('Y-m-d'),
                    'montant' => $jour['montant'],
                    'count' => $jour['count'],
                ]);
                break;

            case '30jours':
                $data = $this->serieJournaliere(30)->map(fn (array $jour) => [
                    'label' => $jour['date']->format('d/m'),
                    'date' => $jour['date']->format('Y-m-d'),
                    'montant' => $jour['montant'],
                    'count' => $jour['count'],
                ]);
                break;

            case '12mois':
                $data = $this->serieMensuelle(12)->map(fn (array $mois) => [
                    'label' => $mois['date']->locale('fr')->isoFormat('MMM YYYY'),
                    'date' => $mois['date']->format('Y-m'),
                    'montant' => $mois['montant'],
                    'count' => $mois['count'],
                ]);
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
        $jour = $this->totauxParOperateur(now()->startOfDay(), now()->endOfDay());
        $mois = $this->totauxParOperateur(now()->startOfMonth(), now()->endOfMonth());

        $stats = Operateur::actif()->get()->map(fn ($operateur) => [
            'operateur' => $operateur->only(['id', 'code', 'libelle', 'couleur']),
            'jour' => [
                'count' => (int) ($jour[$operateur->id]->nb ?? 0),
                'montant' => (float) ($jour[$operateur->id]->montant ?? 0),
            ],
            'mois' => [
                'count' => (int) ($mois[$operateur->id]->nb ?? 0),
                'montant' => (float) ($mois[$operateur->id]->montant ?? 0),
            ],
        ]);

        return response()->json($stats);
    }

    /**
     * Nombre et montant des transactions valides par opérateur sur une période (une requête).
     */
    private function totauxParOperateur(Carbon $debut, Carbon $fin): Collection
    {
        return Transaction::commerciale()->valide()
            ->whereBetween('date', [$debut, $fin])
            ->whereNotNull('operateur_id')
            ->groupBy('operateur_id')
            ->selectRaw('operateur_id, COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant')
            ->toBase()
            ->get()
            ->keyBy('operateur_id');
    }

    /**
     * Nombre et montant des transactions valides pour chacun des $jours derniers jours (une requête),
     * jours sans transaction inclus.
     *
     * @return Collection<int, array{date: Carbon, count: int, montant: float}>
     */
    private function serieJournaliere(int $jours): Collection
    {
        $debut = now()->subDays($jours - 1)->startOfDay();

        $parJour = Transaction::commerciale()->valide()
            ->whereBetween('date', [$debut, now()->endOfDay()])
            ->groupByRaw('DATE(date)')
            ->selectRaw('DATE(date) as jour, COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant')
            ->toBase()
            ->get()
            ->keyBy('jour');

        return collect(range($jours - 1, 0))->map(function (int $joursAvant) use ($parJour) {
            $date = now()->subDays($joursAvant);
            $ligne = $parJour[$date->format('Y-m-d')] ?? null;

            return [
                'date' => $date,
                'count' => (int) ($ligne->nb ?? 0),
                'montant' => (float) ($ligne->montant ?? 0),
            ];
        });
    }

    /**
     * Même chose par mois, sur les $mois derniers mois (mois en cours inclus).
     *
     * @return Collection<int, array{date: Carbon, count: int, montant: float}>
     */
    private function serieMensuelle(int $mois): Collection
    {
        $debut = now()->startOfMonth()->subMonths($mois - 1);

        $parMois = Transaction::commerciale()->valide()
            ->whereBetween('date', [$debut, now()->endOfMonth()])
            ->groupByRaw('YEAR(date), MONTH(date)')
            ->selectRaw('YEAR(date) as annee, MONTH(date) as mois, COUNT(*) as nb, COALESCE(SUM(montant), 0) as montant')
            ->toBase()
            ->get()
            ->keyBy(fn ($ligne) => sprintf('%04d-%02d', $ligne->annee, $ligne->mois));

        return collect(range($mois - 1, 0))->map(function (int $moisAvant) use ($parMois) {
            $date = now()->startOfMonth()->subMonths($moisAvant);
            $ligne = $parMois[$date->format('Y-m')] ?? null;

            return [
                'date' => $date,
                'count' => (int) ($ligne->nb ?? 0),
                'montant' => (float) ($ligne->montant ?? 0),
            ];
        });
    }

    /**
     * API pour la carte de performance du mois (chiffre d'affaires par zone / quartier)
     */
    public function cartePerformanceMois()
    {
        try {
            // Totaux du mois par kiosque calculés en base (au lieu de charger toutes les transactions)
            $parKiosque = Transaction::query()
                ->commerciale()
                ->valide()
                ->duMois()
                ->join('agents', 'agents.id', '=', 'transactions.agent_id')
                ->join('kiosques', 'kiosques.id', '=', 'agents.kiosque_id')
                ->whereNull('agents.deleted_at')
                ->whereNull('kiosques.deleted_at')
                ->groupBy('kiosques.id', 'kiosques.quartier', 'kiosques.ville', 'kiosques.latitude', 'kiosques.longitude')
                ->select('kiosques.id', 'kiosques.quartier', 'kiosques.ville', 'kiosques.latitude', 'kiosques.longitude')
                ->selectRaw('COUNT(*) as nb, COALESCE(SUM(transactions.montant), 0) as montant')
                ->toBase()
                ->get();

            $groups = [];

            foreach ($parKiosque as $kiosque) {
                $zone = trim((string) ($kiosque->quartier ?? '')) ?: 'Non renseignée';
                $ville = trim((string) ($kiosque->ville ?? '')) ?: 'Lomé';
                $key = $zone.'|'.$ville;

                if (! isset($groups[$key])) {
                    $groups[$key] = [
                        'zone' => $zone,
                        'ville' => $ville,
                        'kiosque_ids' => [],
                        'lat_somme' => 0.0,
                        'lng_somme' => 0.0,
                        'nb_geolocalisees' => 0,
                        'montant' => 0.0,
                        'transactions' => 0,
                    ];
                }

                $nb = (int) $kiosque->nb;
                $groups[$key]['kiosque_ids'][$kiosque->id] = true;
                // Position moyenne pondérée par le nombre de transactions (comme le calcul transaction par transaction)
                if ($kiosque->latitude !== null && $kiosque->longitude !== null) {
                    $groups[$key]['lat_somme'] += (float) $kiosque->latitude * $nb;
                    $groups[$key]['lng_somme'] += (float) $kiosque->longitude * $nb;
                    $groups[$key]['nb_geolocalisees'] += $nb;
                }
                $groups[$key]['montant'] += (float) $kiosque->montant;
                $groups[$key]['transactions'] += $nb;
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
                $lat = $row['nb_geolocalisees'] > 0 ? $row['lat_somme'] / $row['nb_geolocalisees'] : null;
                $lng = $row['nb_geolocalisees'] > 0 ? $row['lng_somme'] / $row['nb_geolocalisees'] : null;
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
