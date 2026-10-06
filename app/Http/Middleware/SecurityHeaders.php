<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité HTTP sur les réponses web.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Interdit l'affichage du site dans une iframe d'un autre domaine (clickjacking) ;
            // les iframes du site lui-même (aperçu PDF) restent autorisées.
            'X-Frame-Options' => 'SAMEORIGIN',
            // Empêche le navigateur de deviner un autre type MIME que celui annoncé
            'X-Content-Type-Options' => 'nosniff',
            // N'envoie que l'origine (pas le chemin ni les paramètres) aux sites externes
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];

        foreach ($headers as $nom => $valeur) {
            // Ne remplace pas un en-tête déjà défini par une réponse particulière
            if (! $response->headers->has($nom)) {
                $response->headers->set($nom, $valeur);
            }
        }

        return $response;
    }
}
