<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permissions de base profil ↔ lien (extrait de la BDD de référence).
     * Chaque entrée est résolue par route, url ou libellé — pas par ID.
     */
    private function permissions(): array
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

    public function up(): void
    {
        foreach ($this->permissions() as $profilLibelle => $liens) {
            $profilId = DB::table('profils')
                ->where('libelle', $profilLibelle)
                ->whereNull('deleted_at')
                ->value('id');

            if (! $profilId) {
                continue;
            }

            foreach ($liens as $lienRef) {
                $lienId = $this->resolveLienId($lienRef);

                if (! $lienId) {
                    continue;
                }

                DB::table('profil_liens')->updateOrInsert(
                    ['profil_id' => $profilId, 'lien_id' => $lienId],
                    ['deleted_at' => null, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        // Permissions de référence — pas de rollback destructif.
    }

    private function resolveLienId(array $ref): ?int
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
};
