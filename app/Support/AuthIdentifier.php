<?php

namespace App\Support;

use App\Models\Agent;
use App\Models\Utilisateur;

class AuthIdentifier
{
    public static function resolveUtilisateur(string $identifiant): ?Utilisateur
    {
        $identifiant = trim($identifiant);

        if ($identifiant === '') {
            return null;
        }

        if (str_contains($identifiant, '@')) {
            return Utilisateur::where('email', $identifiant)->first();
        }

        $agent = Agent::where('code_agent', $identifiant)->first();

        if (! $agent?->user_id) {
            return null;
        }

        return Utilisateur::find($agent->user_id);
    }
}
