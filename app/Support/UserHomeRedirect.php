<?php

namespace App\Support;

use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class UserHomeRedirect
{
    /**
     * URL du premier menu accessible (ordre sidebar / table liens).
     */
    public static function urlFor(?Utilisateur $user): string
    {
        if (! $user) {
            return route('login');
        }

        $profilIds = $user->profils()
            ->whereNull('user_profils.deleted_at')
            ->pluck('profils.id')
            ->toArray();

        if ($profilIds === []) {
            return route('dashboard');
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
            return route('dashboard');
        }

        if (! empty($lien->url)) {
            $url = $lien->url;

            return str_starts_with($url, '/') ? url($url) : $url;
        }

        if (! empty($lien->route) && Route::has($lien->route)) {
            return route($lien->route);
        }

        return route('dashboard');
    }
}
