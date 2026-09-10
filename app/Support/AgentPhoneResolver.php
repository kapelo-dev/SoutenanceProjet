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

    /**
     * Résout l'agent d'une transaction SMS : SIM d'abord.
     * Si le SMS contient un code agent, il doit correspondre à l'agent de la SIM.
     *
     * @return array{agent: ?Agent, error: ?string, status: ?int}
     */
    public static function resolveForSmsTransaction(?string $telephone, ?string $smsAgentCode): array
    {
        $smsAgentCode = trim((string) $smsAgentCode);
        $agentBySim = self::resolve($telephone);

        if ($smsAgentCode !== '') {
            if (! $agentBySim) {
                return [
                    'agent' => null,
                    'error' => 'SIM non reconnue : impossible de valider le code agent indiqué dans le SMS.',
                    'status' => 422,
                ];
            }

            if (! self::agentCodeMatches($agentBySim, $smsAgentCode)) {
                return [
                    'agent' => null,
                    'error' => 'Le code agent du SMS ne correspond pas à l\'agent de la SIM.',
                    'status' => 422,
                ];
            }
        }

        return [
            'agent' => $agentBySim,
            'error' => null,
            'status' => null,
        ];
    }

    public static function agentCodeMatches(Agent $agent, string $smsCode): bool
    {
        $stored = trim((string) $agent->code_agent);
        $sms = trim($smsCode);

        if ($stored === '' || $sms === '') {
            return false;
        }

        if (strcasecmp($stored, $sms) === 0) {
            return true;
        }

        $storedDigits = preg_replace('/\D/', '', $stored);
        $smsDigits = preg_replace('/\D/', '', $sms);

        return $storedDigits !== '' && $smsDigits !== '' && $storedDigits === $smsDigits;
    }
}
