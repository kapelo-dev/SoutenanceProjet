<?php

namespace Database\Seeders;

use App\Models\Profil;
use Illuminate\Database\Seeder;

class ProfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profils = [
            [
                'libelle' => 'Super Admin',
                'description' => 'Accès complet au système',
                'niveau' => 0,
            ],
            [
                'libelle' => 'Admin',
                'description' => 'Administrateur de l\'application',
                'niveau' => 1,
            ],
            [
                'libelle' => 'Superviseur',
                'description' => 'Supervision des agents et kiosques',
                'niveau' => 2,
            ],
            [
                'libelle' => 'Comptable',
                'description' => 'Gestion comptable et rapports',
                'niveau' => 2,
            ],
            [
                'libelle' => 'Agent',
                'description' => 'Agent de terrain',
                'niveau' => 3,
            ],
        ];

        foreach ($profils as $profil) {
            Profil::create($profil);
        }

        $this->command->info('✅ Profils créés avec succès!');
    }
}
