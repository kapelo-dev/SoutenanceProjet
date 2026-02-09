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
                'libelle' => 'Mixx by yas',
                // Chemin relatif depuis storage/app/public (utilisé avec asset('storage/'.$logo))
                'logo' => 'logos/operateurs/mixxbyyas.jpg',
                'couleur' => '#865e3c',
                'statut' => 'actif',
                'ordre' => 1,
            ],
            [
                'code' => 'FLOOZ',
                'libelle' => 'Flooz MONEY',
                'logo' => 'logos/operateurs/moovmoney.png',
                'couleur' => '#1a5fb4',
                'statut' => 'actif',
                'ordre' => 2,
            ],
        ];

        foreach ($operateurs as $operateurData) {
            Operateur::updateOrCreate(
                ['code' => $operateurData['code']], // Recherche par code unique
                $operateurData // Données à créer/mettre à jour
            );
        }

        $this->command->info('✅ Opérateurs créés/mis à jour avec succès!');
    }
}
