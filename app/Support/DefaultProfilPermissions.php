<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Permissions de référence profil ↔ lien (BDD de référence).
 * Utilisé par la migration 2026_06_08_200000 et le seed (profils créés après migrate).
 */
class DefaultProfilPermissions
{
    public static function definitions(): array
    {
        return [
            'Super Admin' => [
                ['route' => 'roles-et-permissions.gestion-roles'],
                ['route' => 'dashboard.technique'],
                ['route' => 'roles-et-permissions.gestion-permissions'],
                ['route' => 'roles-et-permissions.gestion-routes'],
                ['route' => 'dashboard.securite'],
                ['route' => 'parametres-app-mobile.index'],
                ['route' => 'system-logs.index'],
                ['libelle' => 'Configuration'],
                ['route' => 'utilisateurs.index'],
            ],
            'Admin' => [
                ['url' => '/gestion-entreprise?onglet=salaires'],
                ['route' => 'kiosques.index'],
                ['route' => 'dashboard'],
                ['route' => 'kiosques.carte'],
                ['route' => 'transactions.index'],
                ['url' => '/gestion-entreprise?onglet=parametres'],
                ['route' => 'agents.index'],
                ['libelle' => 'Agents'],
                ['libelle' => 'Kiosques'],
                ['route' => 'utilisateurs.index'],
                ['route' => 'rapports.index'],
                ['route' => 'operations-agence.index'],
                ['route' => 'gestion-entreprise.index'],
            ],
            'Superviseur' => [
                ['route' => 'kiosques.index'],
                ['route' => 'dashboard'],
                ['url' => '/gestion-entreprise?onglet=salaires'],
                ['route' => 'transactions.index'],
                ['url' => '/gestion-entreprise?onglet=parametres'],
                ['route' => 'agents.index'],
                ['route' => 'kiosques.carte'],
                ['libelle' => 'Agents'],
                ['libelle' => 'Kiosques'],
                ['route' => 'utilisateurs.index'],
                ['route' => 'rapports.index'],
                ['route' => 'operations-agence.index'],
                ['route' => 'gestion-entreprise.index'],
            ],
            'Comptable' => [
                ['url' => '/gestion-entreprise?onglet=salaires'],
                ['url' => '/gestion-entreprise?onglet=parametres'],
                ['url' => '/gestion-entreprise?onglet=tresorerie'],
                ['route' => 'rapports.index'],
            ],
            'Agent' => [
                ['route' => 'agent.dashboard'],
            ],
        ];
    }

    public static function apply(): void
    {
        foreach (self::definitions() as $profilLibelle => $liens) {
            $profilId = DB::table('profils')
                ->where('libelle', $profilLibelle)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->value('id');

            if (! $profilId) {
                continue;
            }

            $allowedLienIds = [];

            foreach ($liens as $lienRef) {
                $lienId = self::resolveLienId($lienRef);

                if (! $lienId) {
                    continue;
                }

                $allowedLienIds[] = $lienId;

                DB::table('profil_liens')->updateOrInsert(
                    ['profil_id' => $profilId, 'lien_id' => $lienId],
                    ['deleted_at' => null, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            if ($allowedLienIds !== []) {
                DB::table('profil_liens')
                    ->where('profil_id', $profilId)
                    ->whereNotIn('lien_id', $allowedLienIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    private static function resolveLienId(array $ref): ?int
    {
        $query = DB::table('liens')->whereNull('deleted_at');

        if (! empty($ref['route'])) {
            return $query->where('route', $ref['route'])->value('id');
        }

        if (! empty($ref['url'])) {
            return $query->where('url', $ref['url'])->value('id');
        }

        if (! empty($ref['libelle'])) {
            return $query
                ->where('libelle', $ref['libelle'])
                ->whereNull('parent_id')
                ->value('id');
        }

        return null;
    }
}
