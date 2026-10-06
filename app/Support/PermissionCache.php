<?php

namespace App\Support;

use Closure;
use Throwable;
use Illuminate\Support\Facades\Cache;

/**
 * Cache des permissions (menu et contrôle d'accès).
 *
 * Toutes les clés incluent un numéro de version : flush() l'incrémente, ce qui invalide d'un coup
 * le cache de tous les utilisateurs (les anciennes entrées expirent d'elles-mêmes).
 * Le store "memo" évite de relire le même cache plusieurs fois au cours d'une requête.
 */
class PermissionCache
{
    private const VERSION_KEY = 'permissions:version';

    private const TTL_SECONDES = 600;

    public static function remember(string $cle, Closure $callback): mixed
    {
        try {
            return Cache::memo()->remember(
                'permissions:' . self::version() . ':' . $cle,
                self::TTL_SECONDES,
                $callback
            );
        } catch (Throwable $e) {
            // Cache inaccessible (droits sur storage/, Redis arrêté…) : la page reste utilisable, sans cache
            report($e);

            return $callback();
        }
    }

    /**
     * À appeler après toute modification des rôles, permissions, routes (liens) ou profils d'un utilisateur.
     */
    public static function flush(): void
    {
        try {
            Cache::memo()->forever(self::VERSION_KEY, self::version() + 1);
        } catch (Throwable $e) {
            // Sans invalidation possible, les entrées en cache expirent d'elles-mêmes (TTL)
            report($e);
        }
    }

    private static function version(): int
    {
        return (int) Cache::memo()->rememberForever(self::VERSION_KEY, fn () => 1);
    }
}
