<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index composés pour les requêtes les plus fréquentes.
     * (L'index transactions (agent_id, statut, date) est créé par 2026_10_06_100000_add_salaires_performance_indexes.)
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Dashboard, rapports, graphiques : statut = 'valide' AND type_operation_id IS NULL AND date BETWEEN ...
            $table->index(['statut', 'type_operation_id', 'date'], 'transactions_statut_type_op_date_index');
            // Pages et stats par opérateur sur une période
            $table->index(['operateur_id', 'statut', 'date'], 'transactions_operateur_statut_date_index');
        });

        Schema::table('soldes', function (Blueprint $table) {
            // Dernier solde d'un agent par opérateur et type (soldesActuels, soldeActuel, opérations d'agence)
            $table->index(['agent_id', 'operateur_id', 'type', 'date'], 'soldes_agent_operateur_type_date_index');
        });

        Schema::table('system_logs', function (Blueprint $table) {
            // Tableau de bord sécurité : connexions (échouées) sur une période
            $table->index(['action', 'created_at'], 'system_logs_action_created_at_index');
            // Blocage automatique : échecs de connexion d'une IP sur 24 h (à chaque échec de connexion)
            $table->index(['action', 'ip_address', 'created_at'], 'system_logs_action_ip_created_at_index');
        });

        Schema::table('mouvements_tresorerie', function (Blueprint $table) {
            // Onglet trésorerie : liste et totaux filtrés par période
            $table->index('date_mouvement', 'mouvements_tresorerie_date_mouvement_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_statut_type_op_date_index');
            $table->dropIndex('transactions_operateur_statut_date_index');
        });

        Schema::table('soldes', function (Blueprint $table) {
            $table->dropIndex('soldes_agent_operateur_type_date_index');
        });

        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropIndex('system_logs_action_created_at_index');
            $table->dropIndex('system_logs_action_ip_created_at_index');
        });

        Schema::table('mouvements_tresorerie', function (Blueprint $table) {
            $table->dropIndex('mouvements_tresorerie_date_mouvement_index');
        });
    }
};
