<?php

namespace App\Http\Middleware;

use App\Services\RoutePermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    public function __construct(
        private readonly RoutePermissionService $routePermissionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($this->routePermissionService->userCanAccess($user, $request)) {
            return $next($request);
        }

        $message = 'Accès non autorisé.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        abort(403, $message);
    }
}
