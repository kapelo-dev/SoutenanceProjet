<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('profils', 'parent_id')) {
            Schema::table('profils', function (Blueprint $table) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('profils')
                    ->nullOnDelete();
            });
        }

        $profils = DB::table('profils')
            ->whereNull('deleted_at')
            ->pluck('id', 'libelle');

        $parents = [
            'Admin' => 'Super Admin',
            'Superviseur' => 'Admin',
            'Comptable' => 'Admin',
            'Agent' => 'Superviseur',
        ];

        foreach ($parents as $libelle => $parentLibelle) {
            if (! isset($profils[$libelle], $profils[$parentLibelle])) {
                continue;
            }

            DB::table('profils')
                ->where('id', $profils[$libelle])
                ->whereNull('parent_id')
                ->update(['parent_id' => $profils[$parentLibelle]]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('profils', 'parent_id')) {
            Schema::table('profils', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }
    }
};
