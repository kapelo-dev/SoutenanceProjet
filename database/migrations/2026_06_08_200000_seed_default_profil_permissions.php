<?php

use App\Support\DefaultProfilPermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DefaultProfilPermissions::apply();
    }

    public function down(): void
    {
        // Permissions de référence — pas de rollback destructif.
    }
};
