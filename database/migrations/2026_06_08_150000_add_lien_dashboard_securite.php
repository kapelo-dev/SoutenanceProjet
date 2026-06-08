<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('liens')
            ->where('route', 'dashboard.securite')
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            $lienId = DB::table('liens')
                ->where('route', 'dashboard.securite')
                ->whereNull('deleted_at')
                ->value('id');
        } else {
            $lienId = DB::table('liens')->insertGetId([
                'libelle' => 'Dashboard Sécurité',
                'route' => 'dashboard.securite',
                'icone' => 'ki-filled ki-shield-search',
                'parent_id' => null,
                'ordre' => 3,
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

            DB::table('profil_liens')->updateOrInsert(
                ['profil_id' => $profil->id, 'lien_id' => $lienId],
                ['deleted_at' => null, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $lien = DB::table('liens')
            ->where('route', 'dashboard.securite')
            ->whereNull('deleted_at')
            ->first();

        if ($lien) {
            DB::table('profil_liens')->where('lien_id', $lien->id)->delete();
            DB::table('liens')->where('id', $lien->id)->delete();
        }
    }
};
