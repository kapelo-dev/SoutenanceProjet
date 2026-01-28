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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->decimal('ancien_montant', 15, 2)->nullable();
            $table->decimal('nouveau_montant', 15, 2)->nullable();
            $table->foreignId('operateur_id')->nullable()->constrained('operateurs')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('utilisateurs')->onDelete('set null');
            $table->timestamp('date_modification')->useCurrent();
            $table->text('raison')->nullable();
            $table->enum('type_modification', ['correction', 'annulation', 'ajustement']);
            $table->timestamps();

            // Index
            $table->index('transaction_id');
            $table->index('user_id');
            $table->index('date_modification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
