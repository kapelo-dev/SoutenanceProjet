<?php

namespace App\Services;

use App\Models\Lien;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoutePermissionService
{
    /**
     * null = route exemptée (accès autorisé)
     * false = route non mappée (accès refusé)
     * string = liens.route à vérifier
     */
    public function resolvePermissionRoute(Request $request): string|null|false
    {
        $routeName = $request->route()?->getName();

        if ($routeName && $this->isExempt($routeName)) {
            return null;
        }

        if ($routeName) {
            if ($this->lienExists($routeName)) {
                return $routeName;
            }

            $fromAlias = $this->resolveFromAliases($routeName);
            if ($fromAlias !== false) {
                return $fromAlias;
            }
        }

        return $this->resolveFromPath(trim($request->path(), '/'));
    }

    public function userCanAccess(Utilisateur $user, Request $request): bool
    {
        $permissionRoute = $this->resolvePermissionRoute($request);

        if ($permissionRoute === null) {
            return true;
        }

        if ($permissionRoute === false) {
            return false;
        }

        return $user->canAccessRoute($permissionRoute);
    }

    private function isExempt(string $routeName): bool
    {
        return in_array($routeName, config('permissions.exempt', []), true);
    }

    private function lienExists(string $routeName): bool
    {
        return Lien::where('route', $routeName)->whereNull('deleted_at')->exists();
    }

    /**
     * @return string|null|false  false = pas de correspondance
     */
    private function resolveFromAliases(string $routeName): string|null|false
    {
        foreach (config('permissions.route_aliases', []) as $pattern => $permissionRoute) {
            if (Str::is($pattern, $routeName)) {
                return $permissionRoute;
            }
        }

        return false;
    }

    /**
     * @return string|false
     */
    private function resolveFromPath(string $path): string|false
    {
        if ($path === '') {
            return 'dashboard';
        }

        $aliases = config('permissions.path_aliases', []);

        uksort($aliases, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($aliases as $prefix => $permissionRoute) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $permissionRoute;
            }
        }

        return false;
    }
}
