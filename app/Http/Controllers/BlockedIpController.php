<?php

namespace App\Http\Controllers;

use App\Models\BlockedIp;
use App\Services\IpBlockService;
use Illuminate\Http\Request;

class BlockedIpController extends Controller
{
    public function __construct(
        protected IpBlockService $ipBlockService
    ) {}

    public function store(Request $request)
    {
        $this->authorizeSecurity();

        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($this->ipBlockService->isBlocked($validated['ip_address'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette IP est déjà bloquée.',
            ], 422);
        }

        $this->ipBlockService->block(
            $validated['ip_address'],
            $validated['reason'] ?? 'Blocage manuel depuis le dashboard sécurité',
            'manual',
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'IP bloquée avec succès.',
            'blocked_ips' => $this->ipBlockService->listActive(),
        ]);
    }

    public function destroy(BlockedIp $blockedIp)
    {
        $this->authorizeSecurity();

        if (! $blockedIp->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette IP n\'est pas bloquée actuellement.',
            ], 404);
        }

        $this->ipBlockService->unblock($blockedIp->ip_address, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'IP débloquée avec succès.',
            'blocked_ips' => $this->ipBlockService->listActive(),
        ]);
    }

    protected function authorizeSecurity(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessRoute('dashboard.securite')) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
