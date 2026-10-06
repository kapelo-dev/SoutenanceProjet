<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\MobileAgentController;
use App\Models\Utilisateur;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentification des routes API par le token délivré à la connexion mobile
 * (POST /api/mobile/agent/login), envoyé en "Authorization: Bearer <token>".
 */
class AuthenticateMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $payload = $token ? Cache::get(MobileAgentController::cacheKey($token)) : null;

        if (! is_array($payload) || empty($payload['user_id'])) {
            return $this->unauthorized('Session expirée. Veuillez vous reconnecter.');
        }

        $utilisateur = Utilisateur::find($payload['user_id']);

        // Un compte désactivé après la connexion perd immédiatement l'accès
        if (! $utilisateur || $utilisateur->statut !== 'actif') {
            Cache::forget(MobileAgentController::cacheKey($token));

            return $this->unauthorized('Compte désactivé ou introuvable.');
        }

        // Mot de passe temporaire : seul le changement de mot de passe est autorisé
        if ($utilisateur->doitChangerMotDePasse() && ! $request->is('api/mobile/agent/change-password')) {
            return response()->json([
                'success' => false,
                'code' => 'MOT_DE_PASSE_A_CHANGER',
                'message' => 'Vous devez changer votre mot de passe avant de continuer.',
            ], 403);
        }

        // Utilisateur disponible via auth()->user() pour la durée de la requête (sans session)
        Auth::setUser($utilisateur);
        $request->attributes->set('mobile_agent_id', $payload['agent_id'] ?? null);

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json(['success' => false, 'message' => $message], 401);
    }
}
