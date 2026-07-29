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
                'parent_id' => null,
            ],
            [
                'libelle' => 'Admin',
                'description' => 'Administrateur de l\'application',
                'parent_libelle' => 'Super Admin',
            ],
            [
                'libelle' => 'Superviseur',
                'description' => 'Supervision des agents et kiosques',
                'parent_libelle' => 'Admin',
            ],
            [
                'libelle' => 'Comptable',
                'description' => 'Gestion comptable et rapports',
                'parent_libelle' => 'Admin',
            ],
            [
                'libelle' => 'Agent',
                'description' => 'Agent de terrain',
                'parent_libelle' => 'Superviseur',
            ],
        ];

        foreach ($profils as $profil) {
            $parentId = null;
            if (! empty($profil['parent_libelle'])) {
                $parentId = Profil::where('libelle', $profil['parent_libelle'])->value('id');
            }

            Profil::updateOrCreate(
                ['libelle' => $profil['libelle']],
                [
                    'description' => $profil['description'],
                    'parent_id' => $parentId,
                ]
            );
        }

        $this->command->info('✅ Profils créés/mis à jour avec succès!');
    }
}
