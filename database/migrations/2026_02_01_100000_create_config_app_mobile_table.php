<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configuration de l'application Android (endpoint, token, numéro filtre SMS).
     */
    public function up(): void
    {
        Schema::create('config_app_mobile', function (Blueprint $table) {
            $table->id();
            $table->string('api_base_url', 500)->nullable()->comment('URL de base de l\'API Laravel (ex: https://mon-domaine.com)');
            $table->string('api_token', 255)->nullable()->comment('Token Bearer pour authentifier l\'app Android');
            $table->string('numero_filtre_sms', 50)->nullable()->comment('Numéro de téléphone pour filtrer les SMS reçus');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_app_mobile');
    }
};
