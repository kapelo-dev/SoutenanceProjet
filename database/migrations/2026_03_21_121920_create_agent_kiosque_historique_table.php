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
        Schema::create('agent_kiosque_historique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('kiosque_id')->constrained('kiosques')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('type_mouvement', ['affectation', 'retrait'])->default('affectation');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->onDelete('set null');
            $table->timestamps();

            // Index
            $table->index('agent_id');
            $table->index('kiosque_id');
            $table->index('date_debut');
            $table->index('date_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_kiosque_historique');
    }
};
