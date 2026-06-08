<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_alert_resolutions', function (Blueprint $table) {
            $table->id();
            $table->string('alert_key', 80)->unique();
            $table->foreignId('resolved_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('resolved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alert_resolutions');
    }
};
