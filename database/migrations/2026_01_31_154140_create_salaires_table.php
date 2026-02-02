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
        Schema::create('salaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('parametre_salaire_id')->nullable()->constrained('parametres_salaire')->onDelete('set null');
            $table->string('periode'); // ex: "2026-01", "Janvier 2026"
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('montant_fixe', 15, 2)->default(0);
            $table->decimal('montant_commission', 15, 2)->default(0);
            $table->decimal('montant_bonus', 15, 2)->default(0);
            $table->decimal('montant_deduction', 15, 2)->default(0);
            $table->decimal('montant_total', 15, 2);
            $table->json('details_calcul')->nullable(); // Détails du calcul
            $table->string('statut')->default('en_attente'); // en_attente, payé, annulé
            $table->date('date_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaires');
    }
};
