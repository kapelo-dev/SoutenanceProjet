<?php

namespace App\Http\Controllers;

use App\Support\SecurityMetrics;

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

    protected function authorizeRoute(string $routeName): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessRoute($routeName)) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
