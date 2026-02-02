<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute le lien "Configuration App Mobile" dans le menu Configuration.
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

        $exists = DB::table('liens')
            ->where('parent_id', $configParent->id)
            ->where('route', 'parametres-app-mobile.index')
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return;
        }

        $maxOrdre = (int) DB::table('liens')
            ->where('parent_id', $configParent->id)
            ->whereNull('deleted_at')
            ->max('ordre');

        DB::table('liens')->insert([
            'libelle' => 'Configuration App Mobile',
            'route' => 'parametres-app-mobile.index',
            'parent_id' => $configParent->id,
            'ordre' => $maxOrdre + 1,
            'visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('liens')
            ->where('route', 'parametres-app-mobile.index')
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }
};
