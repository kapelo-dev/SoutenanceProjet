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
        Schema::create('parametres_salaire', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique(); // ex: "Salaire Agent Standard"
            $table->string('type')->default('fixe'); // fixe, commission, mixte
            $table->decimal('montant_fixe', 15, 2)->default(0); // Salaire de base
            $table->decimal('taux_commission', 5, 2)->default(0); // % de commission
            $table->string('base_calcul')->nullable(); // transactions, soldes, objectifs
            $table->text('formule')->nullable(); // Formule personnalisée
            $table->json('conditions')->nullable(); // Conditions d'application
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres_salaire');
    }
};
