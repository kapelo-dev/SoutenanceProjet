<?php

namespace Database\Seeders;

use App\Models\Profil;
use App\Models\Utilisateur;
use App\Support\DefaultProfilPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProfilSeeder::class,
            LienSeeder::class,
            TypeOperationSeeder::class,
            OperateurSeeder::class,
            LienSyncSeeder::class,
            // UtilisateurSeeder::class, // À décommenter si besoin
            // KiosqueSeeder::class, // À décommenter si besoin
        ]);

        // Créer un utilisateur administrateur par défaut
        $this->createDefaultUser();

        // Permissions de référence (migration 2026_06_08_200000 — profils absents au moment du migrate)
        DefaultProfilPermissions::apply();
        $this->command->info('✅ Permissions de référence appliquées (tous profils).');
    }

    /**
     * Créer un utilisateur administrateur par défaut
     */
    private function createDefaultUser(): void
    {
        // Vérifier si l'utilisateur existe déjà
        $existingUser = Utilisateur::where('email', 'admin@pdvconnect.com')->first();
        
        if (!$existingUser) {
            $user = Utilisateur::create([
                'nom' => 'Administrateur',
                'prenom' => 'Système',
                'email' => 'admin@pdvconnect.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '+22890123456',
                'statut' => 'actif',
                'email_verified_at' => now(),
            ]);

            // Assigner le profil "Super Admin" à l'admin système
            $superAdmin = Profil::where('libelle', 'Super Admin')->first();
            if ($superAdmin) {
                // Pivot soft-delete => on force deleted_at à null si existe déjà
                DB::table('user_profils')->updateOrInsert(
                    ['user_id' => $user->id, 'profil_id' => $superAdmin->id],
                    ['deleted_at' => null, 'updated_at' => now(), 'created_at' => now()]
                );
            }
            
            $this->command->info('Utilisateur administrateur créé avec succès !');
            $this->command->info('Email: admin@pdvconnect.com');
            $this->command->info('Mot de passe: password123');
        } else {
            $this->command->info('L\'utilisateur administrateur existe déjà.');
        }
    }

}
