<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index pour la gestion des salaires :
     * - détection des salaires qui chevauchent une période (agent + dates) et statistiques par statut ;
     * - agrégation des transactions d'un agent sur une période lors de la génération.
     */
    public function up(): void
    {
        Schema::table('salaires', function (Blueprint $table) {
            $table->index(['agent_id', 'date_debut', 'date_fin'], 'salaires_agent_periode_index');
            $table->index('statut', 'salaires_statut_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['agent_id', 'statut', 'date'], 'transactions_agent_statut_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('salaires', function (Blueprint $table) {
            // MySQL utilise l'index composé pour la clé étrangère agent_id : il en faut un autre avant de le supprimer
            $table->index('agent_id');
            $table->dropIndex('salaires_agent_periode_index');
            $table->dropIndex('salaires_statut_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_agent_statut_date_index');
        });
    }
};
