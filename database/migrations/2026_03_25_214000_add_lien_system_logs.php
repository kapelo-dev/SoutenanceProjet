<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute le lien "Logs Système" dans le menu Configuration.
     */
    public function up(): void
    {
        $configParent = DB::table('liens')
            ->where('libelle', 'Configuration')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->first();

        if (!$configParent) {
            return;
        }

        $exists = DB::table('liens')
            ->where('parent_id', $configParent->id)
            ->where('route', 'system-logs.index')
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
            'libelle' => 'Logs Système',
            'route' => 'system-logs.index',
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
            ->where('route', 'system-logs.index')
            ->whereNull('deleted_at')
            ->delete();
    }
};
