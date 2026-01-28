<?php

namespace Database\Seeders;

use App\Models\Operateur;
use Illuminate\Database\Seeder;

class OperateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operateurs = [
            [
                'code' => 'YAS',
                'libelle' => 'Mixx by YAS',
                'logo' => 'logos/operateurs/yas.png',
                'couleur' => '#FF6B00',
                'statut' => 'actif',
                'ordre' => 1,
            ],
            [
                'code' => 'FLOOZ',
                'libelle' => 'Flooz',
                'logo' => 'logos/operateurs/flooz.png',
                'couleur' => '#00A651',
                'statut' => 'actif',
                'ordre' => 2,
            ],
            [
                'code' => 'ORANGE',
                'libelle' => 'Orange Money',
                'logo' => 'logos/operateurs/orange.png',
                'couleur' => '#FF7900',
                'statut' => 'actif',
                'ordre' => 3,
            ],
        ];

        foreach ($operateurs as $operateur) {
            Operateur::create($operateur);
        }

        $this->command->info('✅ Opérateurs créés avec succès!');
    }
}
