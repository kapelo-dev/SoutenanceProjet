<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason');
            $table->enum('source', ['auto', 'manual'])->default('auto');
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->foreignId('blocked_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('unblocked_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('unblocked_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Null = blocage jusqu\'au déblocage manuel');
            $table->timestamps();

            $table->index('unblocked_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
