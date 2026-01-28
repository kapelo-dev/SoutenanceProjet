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
        Schema::create('liens', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 100);
            $table->string('route', 100)->nullable()->comment('Route Laravel');
            $table->string('url', 255)->nullable()->comment('URL si externe');
            $table->string('icone', 50)->nullable()->comment('Classe icône');
            $table->foreignId('parent_id')->nullable()->constrained('liens')->onDelete('cascade');
            $table->integer('ordre')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('parent_id');
            $table->index('ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liens');
    }
};
