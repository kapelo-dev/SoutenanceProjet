<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (Auth::check()) {
            $user = Auth::user();
            
            // Vérifier si c'est la première connexion (dernier_connexion est null)
            if (is_null($user->dernier_connexion)) {
                // Si l'utilisateur n'est pas déjà sur la page de changement de mot de passe, le rediriger
                if (!$request->routeIs('password.change')) {
                    return redirect()->route('password.change');
                }
            }
        }

        return $next($request);
    }
}
