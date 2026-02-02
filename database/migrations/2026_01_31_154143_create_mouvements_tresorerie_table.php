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
        Schema::create('mouvements_tresorerie', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // entree, sortie
            $table->string('categorie'); // salaire, commission, fourniture, loyer, etc.
            $table->decimal('montant', 15, 2);
            $table->date('date_mouvement');
            $table->string('reference')->nullable(); // Référence externe
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->foreignId('salaire_id')->nullable()->constrained('salaires')->onDelete('set null');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->text('description');
            $table->string('mode_paiement')->nullable(); // espece, virement, cheque
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade'); // Qui a créé
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements_tresorerie');
    }
};
