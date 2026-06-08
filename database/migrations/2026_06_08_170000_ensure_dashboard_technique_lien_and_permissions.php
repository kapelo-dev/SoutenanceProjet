<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Garantit le lien Dashboard Technique + permissions Super Admin / Admin.
     * (En prod, la migration initiale pouvait échouer silencieusement si le menu Configuration n'existait pas encore.)
     */
    public function up(): void
    {
        $exists = DB::table('liens')
            ->where('route', 'dashboard.technique')
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            $lienId = DB::table('liens')
                ->where('route', 'dashboard.technique')
                ->whereNull('deleted_at')
                ->value('id');

            DB::table('liens')
                ->where('id', $lienId)
                ->update([
                    'libelle' => 'Dashboard Technique',
                    'icone' => 'ki-filled ki-chart-line-up-2',
                    'parent_id' => null,
                    'ordre' => 2,
                    'visible' => true,
                    'updated_at' => now(),
                ]);
        } else {
            $lienId = DB::table('liens')->insertGetId([
                'libelle' => 'Dashboard Technique',
                'route' => 'dashboard.technique',
                'icone' => 'ki-filled ki-chart-line-up-2',
                'parent_id' => null,
                'ordre' => 2,
                'visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['Super Admin', 'Admin'] as $profilLibelle) {
            $profil = DB::table('profils')
                ->where('libelle', $profilLibelle)
                ->whereNull('deleted_at')
                ->first();

            if (! $profil || ! $lienId) {
                continue;
            }

            $existsPermission = DB::table('profil_liens')
                ->where('profil_id', $profil->id)
                ->where('lien_id', $lienId)
                ->exists();

            if ($existsPermission) {
                DB::table('profil_liens')
                    ->where('profil_id', $profil->id)
                    ->where('lien_id', $lienId)
                    ->update(['deleted_at' => null, 'updated_at' => now()]);
            } else {
                DB::table('profil_liens')->insert([
                    'profil_id' => $profil->id,
                    'lien_id' => $lienId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Migration corrective — ne pas supprimer le lien en rollback
    }
};
