<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use App\Support\ServerMetrics;
use Illuminate\Http\Request;

class TechnicalDashboardController extends Controller
{
    public function index()
    {
        $this->authorizeRoute('dashboard.technique');

        $metrics = ServerMetrics::collect();

        return $this->ajaxView('pages.dashboard.technique', compact('metrics'));
    }

    public function metrics(Request $request)
    {
        $this->authorizeRoute('dashboard.technique');

        return response()->json([
            'success' => true,
            'data' => ServerMetrics::collect(),
        ]);
    }

    public function runBackup(DatabaseBackupService $backupService)
    {
        $this->authorizeRoute('dashboard.technique');

        try {
            $backup = $backupService->run('api');

            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde réussie : ' . $backup->filename,
                'data' => ServerMetrics::collect(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => ServerMetrics::collect(),
            ], 422);
        }
    }

    protected function authorizeRoute(string $routeName): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessRoute($routeName)) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
