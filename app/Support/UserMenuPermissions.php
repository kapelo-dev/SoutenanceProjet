<?php

namespace App\Support;

use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class UserMenuPermissions
{
    public static function forUser(?Utilisateur $user): array
    {
        if (! $user) {
            return [
                'success' => false,
                'profils' => [],
                'routes' => [],
                'route_names' => [],
                'permissions' => [],
            ];
        }

        $profils = $user->profils()->whereNull('user_profils.deleted_at')->get();

        if ($profils->isEmpty()) {
            return [
                'success' => true,
                'profils' => [],
                'routes' => [],
                'route_names' => [],
                'permissions' => [],
            ];
        }

        $profilIds = $profils->pluck('id')->toArray();

        $liens = DB::table('profil_liens')
            ->join('liens', 'profil_liens.lien_id', '=', 'liens.id')
            ->whereIn('profil_liens.profil_id', $profilIds)
            ->whereNull('profil_liens.deleted_at')
            ->whereNull('liens.deleted_at')
            ->where('liens.visible', true)
            ->select('liens.route', 'liens.url', 'liens.id as lien_id')
            ->distinct()
            ->get();

        $allowedUrls = [];
        $allowedRouteNames = [];
        $permissionsByLien = [];

        foreach ($liens as $lien) {
            $permissionsByLien[$lien->lien_id] = $lien->route ?: $lien->url;

            if (! empty($lien->url)) {
                $url = $lien->url;
                if (! str_starts_with($url, '/')) {
                    $url = '/'.$url;
                }
                if (strlen($url) > 1 && str_ends_with($url, '/')) {
                    $url = rtrim($url, '/');
                }
                $allowedUrls[] = $url;
            }

            if (! empty($lien->route)) {
                $allowedRouteNames[] = $lien->route;

                if (Route::has($lien->route)) {
                    $path = parse_url(route($lien->route), PHP_URL_PATH);
                    if (! empty($path)) {
                        if (strlen($path) > 1 && str_ends_with($path, '/')) {
                            $path = rtrim($path, '/');
                        }
                        $allowedUrls[] = $path;
                    }
                }
            }
        }

        return [
            'success' => true,
            'profils' => $profils->map(fn ($profil) => [
                'id' => $profil->id,
                'libelle' => $profil->libelle,
                'niveau' => $profil->niveau,
            ])->values()->all(),
            'routes' => array_values(array_unique(array_filter($allowedUrls))),
            'route_names' => array_values(array_unique(array_filter($allowedRouteNames))),
            'permissions' => $permissionsByLien,
        ];
    }
}
