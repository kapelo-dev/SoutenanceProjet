<?php

namespace App\Http\Controllers;

use App\Support\ServerMetrics;

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

    protected function authorizeRoute(string $routeName): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessRoute($routeName)) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
