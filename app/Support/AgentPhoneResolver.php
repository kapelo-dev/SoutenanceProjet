<?php

namespace App\Support;

use App\Models\Agent;

class AgentPhoneResolver
{
    public static function resolve(?string $telephone): ?Agent
    {
        $telephone = trim((string) $telephone);
        if ($telephone === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $telephone);
        if ($digits === '') {
            return null;
        }

        $last8 = strlen($digits) >= 8 ? substr($digits, -8) : $digits;

        return Agent::query()
            ->where(function ($q) use ($telephone, $digits, $last8) {
                $q->where('telephone', $telephone)
                    ->orWhere('telephone', $digits)
                    ->orWhere('telephone', 'like', '%'.$last8);
            })
            ->where('statut', 'actif')
            ->first();
    }
}
