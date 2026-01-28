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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('reference', 50)->unique();
            $table->timestamp('date')->useCurrent();
            $table->decimal('montant', 15, 2);
            $table->enum('type', ['depot', 'retrait', 'transfert', 'paiement']);
            $table->foreignId('operateur_id')->constrained('operateurs')->onDelete('restrict');
            $table->foreignId('agent_id')->constrained('agents')->onDelete('restrict');
            $table->enum('statut', ['valide', 'en_attente', 'annule', 'echoue'])->default('valide');
            $table->text('description')->nullable();
            $table->decimal('commission', 15, 2)->nullable();
            $table->decimal('virtual_balance_after', 15, 2)->nullable();
            $table->string('operator_txn_id', 50)->nullable();
            $table->string('client_nom', 100)->nullable();
            $table->string('client_telephone', 20)->nullable();
            $table->timestamps();

            // Index
            $table->index('reference');
            $table->index('agent_id');
            $table->index('operateur_id');
            $table->index('date');
            $table->index('statut');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
