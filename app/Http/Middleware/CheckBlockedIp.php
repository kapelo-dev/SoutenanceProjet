<?php

namespace App\Http\Middleware;

use App\Services\IpBlockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function __construct(
        protected IpBlockService $ipBlockService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $block = $this->ipBlockService->getActiveBlock($ip);

        if (! $block) {
            return $next($request);
        }

        // Les admins sécurité peuvent toujours accéder (déblocage d'IP, etc.)
        if ($request->user()?->canAccessRoute('dashboard.securite')) {
            return $next($request);
        }

        $message = 'Votre adresse IP (' . $ip . ') est bloquée. Raison : ' . $block->reason;

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        if ($request->routeIs('login') || $request->is('login')) {
            return response()->view('auth.blocked-ip', [
                'ip' => $ip,
                'reason' => $block->reason,
            ], 403);
        }

        abort(403, $message);
    }
}
