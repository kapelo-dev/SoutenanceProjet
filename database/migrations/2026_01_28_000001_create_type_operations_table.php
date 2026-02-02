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
        Schema::create('type_operations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code unique ex: reglement_commission, apport_espece');
            $table->string('libelle', 100)->comment('Libellé affiché ex: Règlement commission, Apport espèce');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('actif');
            $table->index('ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_operations');
    }
};
