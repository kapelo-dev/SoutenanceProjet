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
        Schema::create('profil_liens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->constrained('profils')->onDelete('cascade');
            $table->foreignId('lien_id')->constrained('liens')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Contrainte unique
            $table->unique(['profil_id', 'lien_id'], 'unique_profil_lien');
            
            // Index
            $table->index('profil_id');
            $table->index('lien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_liens');
    }
};
