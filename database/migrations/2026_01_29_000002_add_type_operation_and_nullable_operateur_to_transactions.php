<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('type_operation_id')->nullable()->after('type')->constrained('type_operations')->onDelete('set null');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['operateur_id']);
        });
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE transactions MODIFY operateur_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('operateur_id')->nullable()->change();
            });
        }
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('operateur_id')->references('id')->on('operateurs')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['type_operation_id']);
            $table->dropColumn('type_operation_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['operateur_id']);
        });
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE transactions MODIFY operateur_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('operateur_id')->nullable(false)->change();
            });
        }
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('operateur_id')->references('id')->on('operateurs')->onDelete('restrict');
        });
    }
};
