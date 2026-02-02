<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('type_operations', function (Blueprint $table) {
            $table->boolean('requiert_operateur')->default(false)->after('actif')
                ->comment('Si true, le champ Opérateur (T-Money, Flooz…) doit être choisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_operations', function (Blueprint $table) {
            $table->dropColumn('requiert_operateur');
        });
    }
};
