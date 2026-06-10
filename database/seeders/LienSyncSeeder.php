<?php

namespace Database\Seeders;

use App\Models\Lien;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LienSyncSeeder extends Seeder
{
    /**
     * Synchronise les liens ajoutés après le seed initial (migrations ultérieures).
     */
    public function run(): void
    {
        $configParent = Lien::where('libelle', 'Configuration')->whereNull('parent_id')->first();
        if (! $configParent) {
            $this->command->warn('Menu Configuration introuvable, sync liens ignorée.');
            return;
        }

        $topLevel = [
            ['libelle' => 'Métrique serveur', 'route' => 'dashboard.technique', 'icone' => 'ki-filled ki-chart-line-up-2', 'ordre' => 2],
            ['libelle' => 'Dashboard Sécurité', 'route' => 'dashboard.securite', 'icone' => 'ki-filled ki-shield-search', 'ordre' => 3],
        ];

        foreach ($topLevel as $item) {
            $lien = Lien::updateOrCreate(
                ['route' => $item['route']],
                [
                    'libelle' => $item['libelle'],
                    'icone' => $item['icone'],
                    'parent_id' => null,
                    'ordre' => $item['ordre'],
                    'visible' => true,
                ]
            );
            $this->grantToAdminProfil($lien->id);
        }

        $children = [
            ['libelle' => 'Logs Système', 'route' => 'system-logs.index', 'ordre' => 6],
        ];

        foreach ($children as $child) {
            $lien = Lien::updateOrCreate(
                ['route' => $child['route']],
                [
                    'libelle' => $child['libelle'],
                    'parent_id' => $configParent->id,
                    'ordre' => $child['ordre'],
                    'visible' => true,
                ]
            );

            $this->grantToAdminProfil($lien->id);
        }

        $this->command->info('✅ Liens synchronisés (Logs Système, etc.).');
    }

    /** Super Admin : permissions via DefaultProfilPermissions uniquement. */
    private function grantToAdminProfil(int $lienId): void
    {
        $profilId = DB::table('profils')
            ->where('libelle', 'Admin')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->value('id');

        if (! $profilId) {
            return;
        }

        DB::table('profil_liens')->updateOrInsert(
            ['profil_id' => $profilId, 'lien_id' => $lienId],
            ['deleted_at' => null, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
