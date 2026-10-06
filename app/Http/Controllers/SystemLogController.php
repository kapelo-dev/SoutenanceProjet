<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Traits\Exportable;

class SystemLogController extends Controller
{
    use Exportable;

    /**
     * Afficher la liste des logs système
     */
    public function index(Request $request)
    {
        $query = SystemLog::with('utilisateur')->latest();

        $this->appliquerFiltres($query, $request);

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $logs = $query->paginate(50);

        // Statistiques
        // Compteurs en une requête (au lieu de 4)
        $compteurs = SystemLog::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN created_at >= ? THEN 1 END) as today', [now()->startOfDay()])
            ->selectRaw('COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as this_week', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('COUNT(CASE WHEN created_at >= ? THEN 1 END) as this_month', [now()->startOfMonth()])
            ->toBase()
            ->first();
        $stats = [
            'total' => (int) $compteurs->total,
            'today' => (int) $compteurs->today,
            'this_week' => (int) $compteurs->this_week,
            'this_month' => (int) $compteurs->this_month,
        ];

        // Utilisateurs pour le filtre
        $utilisateurs = Utilisateur::orderBy('nom')->get();

        // Actions disponibles
        $actions = [
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'login_failed' => 'Connexion échouée',
            'assign' => 'Affectation',
            'unassign' => 'Retrait',
            'validate' => 'Validation',
            'cancel' => 'Annulation',
            'export' => 'Export',
            'import' => 'Import',
        ];

        // Types de modèles
        $modelTypes = [
            'App\Models\Agent' => 'Agent',
            'App\Models\Kiosque' => 'Kiosque',
            'App\Models\Transaction' => 'Transaction',
            'App\Models\Utilisateur' => 'Utilisateur',
            'App\Models\Operateur' => 'Opérateur',
            'App\Models\AgentKiosqueHistorique' => 'Affectation',
        ];

        return $this->ajaxView('pages.system_logs.index', compact(
            'logs',
            'stats',
            'utilisateurs',
            'actions',
            'modelTypes'
        ));
    }

    /**
     * Afficher les détails d'un log
     */
    public function show(SystemLog $systemLog)
    {
        $systemLog->load('utilisateur');

        return $this->ajaxView('pages.system_logs.show', compact('systemLog'));
    }

    /**
     * Exporter les logs en Excel
     */
    public function exportExcel(Request $request)
    {
        $query = SystemLog::with('utilisateur')->latest();

        $this->appliquerFiltres($query, $request);

        $headers = ['Date/Heure', 'Utilisateur', 'Action', 'Entité', 'Description', 'IP'];

        // lazy() : logs hydratés par lots (le journal peut contenir des centaines de milliers de lignes)
        $data = $query->latest('id')->lazy(1000)->map(function ($log) { // id : ordre stable entre les lots
            return [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->utilisateur ? $log->utilisateur->nom . ' ' . $log->utilisateur->prenom : 'Système',
                $log->action_label,
                $log->model_name ?? '-',
                $log->description,
                $log->ip_address ?? '-',
            ];
        });

        return $this->exportToExcel($headers, $data->all(), $this->excelFilename('logs_systeme_' . now()->format('Y-m-d_His')), 'Journal système', 'Historique des actions et événements');
    }

    /**
     * Exporter les logs en PDF
     */
    public function exportPdf(Request $request)
    {
        $query = SystemLog::with('utilisateur')->latest();

        $this->appliquerFiltres($query, $request);

        $logs = $query->limit(500)->get(); // Limiter pour le PDF

        $headers = ['Date/Heure', 'Utilisateur', 'Action', 'Entité', 'Description'];

        $data = $logs->map(function ($log) {
            return [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->utilisateur ? $log->utilisateur->nom . ' ' . $log->utilisateur->prenom : 'Système',
                $log->action_label,
                $log->model_name ?? '-',
                substr($log->description, 0, 100) . (strlen($log->description) > 100 ? '...' : ''),
            ];
        })->toArray();

        return $this->exportToPdf(
            'Logs Système',
            $headers,
            $data,
            'logs_systeme_' . now()->format('Y-m-d_His') . '.pdf',
            'portrait',
            $request,
            [
                'subtitle' => 'Journal d\'audit et traçabilité',
                'filtersText' => $this->buildLogExportFilters($request),
            ]
        );
    }

    /**
     * Filtres communs à la liste et aux exports.
     * Dates en bornes explicites (et non whereDate) pour que MySQL utilise l'index sur created_at.
     */
    private function appliquerFiltres(Builder $query, Request $request): void
    {
        foreach (['user_id', 'action', 'model_type'] as $champ) {
            if ($request->filled($champ)) {
                $query->where($champ, $request->input($champ));
            }
        }

        if ($request->filled('date_debut')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_debut)->startOfDay());
        }

        if ($request->filled('date_fin')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_fin)->endOfDay());
        }
    }

    private function buildLogExportFilters(Request $request): ?string
    {
        $parts = [];
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $parts[] = 'Période : ' . ($request->date_debut ?? '…') . ' — ' . ($request->date_fin ?? '…');
        }
        if ($request->filled('action')) {
            $parts[] = 'Action : ' . $request->action;
        }

        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * Nettoyer les anciens logs
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $date = now()->subDays($request->days);
        $count = SystemLog::where('created_at', '<', $date)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} log(s) supprimé(s) avec succès.",
        ]);
    }
}
