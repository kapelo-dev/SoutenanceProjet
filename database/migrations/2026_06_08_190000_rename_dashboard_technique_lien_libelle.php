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
                'libelle' => 'Métrique serveur',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('liens')
            ->where('route', 'dashboard.technique')
            ->whereNull('deleted_at')
            ->update([
                'libelle' => 'Dashboard Technique',
                'updated_at' => now(),
            ]);
    }
};
