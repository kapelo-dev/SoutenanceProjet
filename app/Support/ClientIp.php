<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientIp
{
    /**
     * IP réelle du client derrière Render / Cloudflare.
     * Priorité aux en-têtes posés par l'infrastructure (non spoofables côté edge).
     */
    public static function from(Request $request): string
    {
        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP'] as $header) {
            $ip = trim((string) $request->header($header));
            if (self::isUsableIp($ip) && ! self::isPrivateOrReserved($ip)) {
                return $ip;
            }
        }

        $forwarded = $request->header('X-Forwarded-For');
        if ($forwarded) {
            foreach (array_map('trim', explode(',', $forwarded)) as $ip) {
                if (self::isUsableIp($ip) && ! self::isPrivateOrReserved($ip)) {
                    return $ip;
                }
            }
        }

        $ip = $request->ip();
        if (self::isUsableIp($ip) && ! self::isPrivateOrReserved($ip)) {
            return $ip;
        }

        return $ip && self::isUsableIp($ip) ? $ip : '0.0.0.0';
    }

    private static function isUsableIp(string $ip): bool
    {
        return $ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    private static function isPrivateOrReserved(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
