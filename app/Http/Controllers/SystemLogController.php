<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\Utilisateur;
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

        // Filtres
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $logs = $query->paginate(50);

        // Statistiques
        $stats = [
            'total' => SystemLog::count(),
            'today' => SystemLog::today()->count(),
            'this_week' => SystemLog::thisWeek()->count(),
            'this_month' => SystemLog::thisMonth()->count(),
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

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $logs = $query->get();

        $headers = ['Date/Heure', 'Utilisateur', 'Action', 'Entité', 'Description', 'IP'];

        $data = $logs->map(function ($log) {
            return [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->utilisateur ? $log->utilisateur->nom . ' ' . $log->utilisateur->prenom : 'Système',
                $log->action_label,
                $log->model_name ?? '-',
                $log->description,
                $log->ip_address ?? '-',
            ];
        });

        return $this->exportToExcel($headers, $data, 'logs_systeme_' . now()->format('Y-m-d'));
    }

    /**
     * Exporter les logs en PDF
     */
    public function exportPdf(Request $request)
    {
        $query = SystemLog::with('utilisateur')->latest();

        // Appliquer les mêmes filtres
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

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
        });

        return $this->exportToPdf(
            $headers,
            $data,
            'Logs Système',
            'logs_systeme_' . now()->format('Y-m-d'),
            'landscape'
        );
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
