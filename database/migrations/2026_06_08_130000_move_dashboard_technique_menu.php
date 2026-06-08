<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('liens')
            ->where('route', 'dashboard.technique')
            ->whereNull('deleted_at')
            ->update([
                'parent_id' => null,
                'ordre' => 2,
                'icone' => 'ki-filled ki-chart-line-up-2',
                'libelle' => 'Dashboard Technique',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $configParent = DB::table('liens')
            ->where('libelle', 'Configuration')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->first();

        if (! $configParent) {
            return;
        }

        DB::table('liens')
            ->where('route', 'dashboard.technique')
            ->whereNull('deleted_at')
            ->update([
                'parent_id' => $configParent->id,
                'ordre' => 99,
                'updated_at' => now(),
            ]);
    }
};
