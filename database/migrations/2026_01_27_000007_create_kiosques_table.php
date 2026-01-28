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
        Schema::create('kiosques', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('code', 50)->unique()->nullable()->comment('Code du kiosque');
            $table->string('nom', 150);
            $table->text('adresse')->nullable();
            $table->string('quartier', 100)->nullable();
            $table->string('ville', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude GPS');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude GPS');
            $table->string('telephone', 20)->nullable();
            $table->string('photo', 255)->nullable();
            $table->enum('type', ['fixe', 'mobile'])->default('fixe');
            $table->enum('statut', ['actif', 'inactif', 'en_travaux'])->default('actif');
            $table->integer('capacite_agents')->default(1)->comment('Nombre d\'agents max');
            $table->time('horaire_ouverture')->nullable();
            $table->time('horaire_fermeture')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('code');
            $table->index('statut');
            $table->index('ville');
            $table->index('quartier');
            $table->index(['latitude', 'longitude'], 'idx_localisation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kiosques');
    }
};
