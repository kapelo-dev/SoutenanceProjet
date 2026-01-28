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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 100)->unique();
            $table->string('mot_de_passe', 255);
            $table->string('telephone', 20)->nullable();
            $table->string('photo_profil', 255)->nullable();
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->timestamp('dernier_connexion')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('email');
            $table->index('uid');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
