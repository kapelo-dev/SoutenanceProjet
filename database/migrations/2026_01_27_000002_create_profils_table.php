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
        Schema::create('profils', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->integer('niveau')->default(0)->comment('Niveau hiérarchique');
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('libelle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils');
    }
};
