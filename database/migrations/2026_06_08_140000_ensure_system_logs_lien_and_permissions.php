<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Garantit le lien Logs Système + permissions Super Admin / Admin.
     * (La migration initiale n'accordait pas les permissions profil_liens.)
     */
    public function up(): void
    {
        $configParent = DB::table('liens')
            ->where('libelle', 'Configuration')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->first();

        if (! $configParent) {
            return;
        }

        $lien = DB::table('liens')
            ->where('route', 'system-logs.index')
            ->whereNull('deleted_at')
            ->first();

        if (! $lien) {
            $maxOrdre = (int) DB::table('liens')
                ->where('parent_id', $configParent->id)
                ->whereNull('deleted_at')
                ->max('ordre');

            $lienId = DB::table('liens')->insertGetId([
                'libelle' => 'Logs Système',
                'route' => 'system-logs.index',
                'parent_id' => $configParent->id,
                'ordre' => $maxOrdre + 1,
                'visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $lienId = $lien->id;
        }

        $profils = DB::table('profils')
            ->whereIn('libelle', ['Super Admin', 'Admin'])
            ->whereNull('deleted_at')
            ->pluck('id');

        foreach ($profils as $profilId) {
            $exists = DB::table('profil_liens')
                ->where('profil_id', $profilId)
                ->where('lien_id', $lienId)
                ->exists();

            if ($exists) {
                DB::table('profil_liens')
                    ->where('profil_id', $profilId)
                    ->where('lien_id', $lienId)
                    ->update(['deleted_at' => null, 'updated_at' => now()]);
            } else {
                DB::table('profil_liens')->insert([
                    'profil_id' => $profilId,
                    'lien_id' => $lienId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Ne pas supprimer le lien : migration corrective uniquement
    }
};
