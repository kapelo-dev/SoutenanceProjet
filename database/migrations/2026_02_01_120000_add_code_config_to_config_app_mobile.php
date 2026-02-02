<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Code pour accéder à la page de configuration dans l'application mobile.
     */
    public function up(): void
    {
        Schema::table('config_app_mobile', function (Blueprint $table) {
            $table->string('code_config', 50)->nullable()->after('filtres_sms')
                ->comment('Code pour accéder à la page de configuration dans l\'app mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_app_mobile', function (Blueprint $table) {
            $table->dropColumn('code_config');
        });
    }
};
