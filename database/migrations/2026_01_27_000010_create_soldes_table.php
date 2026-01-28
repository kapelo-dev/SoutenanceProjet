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
        Schema::create('soldes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('operateur_id')->nullable()->constrained('operateurs')->onDelete('cascade')->comment('NULL pour espèce');
            $table->decimal('montant', 15, 2)->default(0.00);
            $table->enum('type', ['espece', 'virtuel'])->comment('Type de solde');
            $table->timestamp('date')->useCurrent();
            $table->text('description')->nullable();
            $table->timestamps();

            // Index
            $table->index('agent_id');
            $table->index('operateur_id');
            $table->index('date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soldes');
    }
};
