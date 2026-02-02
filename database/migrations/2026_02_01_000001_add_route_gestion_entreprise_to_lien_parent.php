<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Met à jour le lien parent "Gestion d'Entreprise" pour qu'il soit soumis aux permissions
     * comme les autres menus (route utilisée par getMyPermissions / menu-permissions.js).
     */
    public function up(): void
    {
        DB::table('liens')
            ->where('libelle', 'Gestion d\'Entreprise')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->whereNull('route')
            ->update(['route' => 'gestion-entreprise.index', 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('liens')
            ->where('libelle', 'Gestion d\'Entreprise')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->where('route', 'gestion-entreprise.index')
            ->update(['route' => null, 'updated_at' => now()]);
    }
};
