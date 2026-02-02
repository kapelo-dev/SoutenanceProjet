<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remplace numero_filtre_sms par filtres_sms (JSON) : plusieurs numéros et/ou noms de discussions.
     */
    public function up(): void
    {
        Schema::table('config_app_mobile', function (Blueprint $table) {
            $table->json('filtres_sms')->nullable()->after('api_token')->comment('Numéros ou noms de discussions (ex: FLOOZ), un par entrée');
        });

        // Migrer l'ancienne valeur unique vers le tableau
        $rows = DB::table('config_app_mobile')->get();
        foreach ($rows as $row) {
            $filtres = [];
            if (! empty($row->numero_filtre_sms)) {
                $filtres[] = trim($row->numero_filtre_sms);
            }
            DB::table('config_app_mobile')
                ->where('id', $row->id)
                ->update(['filtres_sms' => json_encode($filtres)]);
        }

        Schema::table('config_app_mobile', function (Blueprint $table) {
            $table->dropColumn('numero_filtre_sms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_app_mobile', function (Blueprint $table) {
            $table->string('numero_filtre_sms', 50)->nullable()->after('api_token');
        });

        $rows = DB::table('config_app_mobile')->get();
        foreach ($rows as $row) {
            $first = null;
            if (! empty($row->filtres_sms)) {
                $arr = json_decode($row->filtres_sms, true);
                if (is_array($arr) && count($arr) > 0) {
                    $first = $arr[0];
                }
            }
            DB::table('config_app_mobile')
                ->where('id', $row->id)
                ->update(['numero_filtre_sms' => $first]);
        }

        Schema::table('config_app_mobile', function (Blueprint $table) {
            $table->dropColumn('filtres_sms');
        });
    }
};
