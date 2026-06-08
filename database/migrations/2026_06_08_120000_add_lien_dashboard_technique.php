<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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

        $exists = DB::table('liens')
            ->where('parent_id', $configParent->id)
            ->where('route', 'dashboard.technique')
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            $lienId = DB::table('liens')
                ->where('parent_id', $configParent->id)
                ->where('route', 'dashboard.technique')
                ->whereNull('deleted_at')
                ->value('id');
        } else {
            $maxOrdre = (int) DB::table('liens')
                ->where('parent_id', $configParent->id)
                ->whereNull('deleted_at')
                ->max('ordre');

            $lienId = DB::table('liens')->insertGetId([
                'libelle' => 'Dashboard Technique',
                'route' => 'dashboard.technique',
                'parent_id' => $configParent->id,
                'ordre' => $maxOrdre + 1,
                'visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $superAdmin = DB::table('profils')
            ->where('libelle', 'Super Admin')
            ->whereNull('deleted_at')
            ->first();

        if (! $superAdmin || ! $lienId) {
            return;
        }

        $permissionExists = DB::table('profil_liens')
            ->where('profil_id', $superAdmin->id)
            ->where('lien_id', $lienId)
            ->whereNull('deleted_at')
            ->exists();

        if (! $permissionExists) {
            DB::table('profil_liens')->insert([
                'profil_id' => $superAdmin->id,
                'lien_id' => $lienId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $lien = DB::table('liens')
            ->where('route', 'dashboard.technique')
            ->whereNull('deleted_at')
            ->first();

        if ($lien) {
            DB::table('profil_liens')->where('lien_id', $lien->id)->delete();
            DB::table('liens')->where('id', $lien->id)->delete();
        }
    }
};
