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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            
            // Utilisateur qui a effectué l'action
            $table->foreignId('user_id')->nullable()->constrained('utilisateurs')->onDelete('set null');
            
            // Type d'action
            $table->enum('action', [
                'create', 'update', 'delete', 
                'login', 'logout', 'login_failed',
                'assign', 'unassign',
                'validate', 'cancel',
                'export', 'import',
                'other'
            ]);
            
            // Entité concernée
            $table->string('model_type')->nullable(); // Ex: App\Models\Agent
            $table->unsignedBigInteger('model_id')->nullable(); // ID de l'entité
            
            // Description de l'action
            $table->text('description');
            
            // Données avant/après (JSON)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Informations de connexion
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Métadonnées supplémentaires
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
