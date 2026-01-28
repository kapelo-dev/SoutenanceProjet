<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OperateurSeeder::class,
            ProfilSeeder::class,
            LienSeeder::class,
            // UtilisateurSeeder::class, // À décommenter si besoin
            // KiosqueSeeder::class, // À décommenter si besoin
        ]);
    }
}
