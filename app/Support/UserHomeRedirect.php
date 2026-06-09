<?php

namespace App\Support;

use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class UserHomeRedirect
{
    /**
     * Chemin relatif du premier menu accessible (préserve le port en local).
     */
    public static function pathFor(?Utilisateur $user): string
    {
        if (! $user) {
            return route('login', [], false);
        }

        $profilIds = $user->profils()
            ->whereNull('user_profils.deleted_at')
            ->pluck('profils.id')
            ->toArray();

        if ($profilIds === []) {
            return route('dashboard', [], false);
        }

        $lien = DB::table('liens')
            ->leftJoin('liens as parent', 'liens.parent_id', '=', 'parent.id')
            ->join('profil_liens', 'liens.id', '=', 'profil_liens.lien_id')
            ->whereIn('profil_liens.profil_id', $profilIds)
            ->whereNull('profil_liens.deleted_at')
            ->whereNull('liens.deleted_at')
            ->where('liens.visible', true)
            ->where(function ($query) {
                $query->whereNotNull('liens.route')
                    ->orWhereNotNull('liens.url');
            })
            ->orderByRaw('COALESCE(parent.ordre, liens.ordre) ASC')
            ->orderByRaw('CASE WHEN liens.parent_id IS NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('liens.ordre')
            ->orderBy('liens.id')
            ->select('liens.route', 'liens.url')
            ->first();

        if (! $lien) {
            return route('dashboard', [], false);
        }

        if (! empty($lien->url)) {
            $path = $lien->url;

            return str_starts_with($path, '/') ? $path : '/'.$path;
        }

        if (! empty($lien->route) && Route::has($lien->route)) {
            return route($lien->route, [], false);
        }

        return route('dashboard', [], false);
    }

    /** @deprecated Préférer pathFor() pour les redirections internes */
    public static function urlFor(?Utilisateur $user): string
    {
        return self::pathFor($user);
    }
}
