<?php

namespace App\Http\Controllers;

use App\Models\SecurityAlertResolution;
use App\Models\SystemLog;
use App\Support\SecurityMetrics;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    public function index()
    {
        $this->authorizeRoute('dashboard.securite');

        $metrics = SecurityMetrics::collect();

        return $this->ajaxView('pages.dashboard.securite', compact('metrics'));
    }

    public function metrics()
    {
        $this->authorizeRoute('dashboard.securite');

        return response()->json([
            'success' => true,
            'data' => SecurityMetrics::collect(),
        ]);
    }

    public function resolveAlert(Request $request)
    {
        $this->authorizeRoute('dashboard.securite');

        $validated = $request->validate([
            'alert_key' => 'required|string|in:' . implode(',', SecurityMetrics::validAlertKeys()),
            'note' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $metrics = SecurityMetrics::collect();

        SecurityAlertResolution::resolve(
            $validated['alert_key'],
            $user->id,
            $validated['note'] ?? null,
            ['stats' => $metrics['stats'] ?? []],
        );

        SystemLog::logAction(
            'security_alert_resolved',
            'Alerte sécurité levée : ' . $validated['alert_key'],
            null,
            null,
            ['alert_key' => $validated['alert_key'], 'note' => $validated['note'] ?? null],
        );

        return response()->json([
            'success' => true,
            'message' => 'Alerte marquée comme résolue.',
            'data' => SecurityMetrics::collect(),
        ]);
    }

    protected function authorizeRoute(string $routeName): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessRoute($routeName)) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
