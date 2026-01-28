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
        Schema::create('operateurs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code unique ex: YAS, FLOOZ');
            $table->string('libelle', 100);
            $table->string('logo', 255)->nullable()->comment('Chemin vers le logo');
            $table->string('couleur', 7)->nullable()->comment('Couleur hexadécimale');
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('statut');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operateurs');
    }
};
