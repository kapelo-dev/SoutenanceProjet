<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pivot : associer un paramètre de salaire à un ou plusieurs profils (rôles).
     */
    public function up(): void
    {
        Schema::create('parametre_salaire_profil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parametre_salaire_id')->constrained('parametres_salaire')->onDelete('cascade');
            $table->foreignId('profil_id')->constrained('profils')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['parametre_salaire_id', 'profil_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametre_salaire_profil');
    }
};
