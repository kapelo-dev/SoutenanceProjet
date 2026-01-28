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
        Schema::create('user_profils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('profil_id')->constrained('profils')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Contrainte unique
            $table->unique(['user_id', 'profil_id'], 'unique_user_profil');
            
            // Index
            $table->index('user_id');
            $table->index('profil_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profils');
    }
};
