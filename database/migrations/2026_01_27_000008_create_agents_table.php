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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('code_agent', 50)->unique()->nullable();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('telephone', 20)->unique();
            $table->decimal('montant_initial_total', 15, 2)->default(0.00);
            $table->decimal('espece_initiale', 15, 2)->default(0.00);
            $table->foreignId('kiosque_id')->nullable()->constrained('kiosques')->onDelete('set null');
            $table->enum('statut', ['actif', 'inactif', 'suspendu', 'en_attente'])->default('actif');
            $table->foreignId('user_id')->nullable()->constrained('utilisateurs')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('code_agent');
            $table->index('telephone');
            $table->index('user_id');
            $table->index('kiosque_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
