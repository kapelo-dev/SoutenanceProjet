<?php

namespace Database\Seeders;

use App\Models\TypeOperation;
use Illuminate\Database\Seeder;

class TypeOperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Types génériques : pas d'opérateur figé dans le libellé. On choisit l'opérateur à part (uniquement pour types "virtuel").
        $types = [
            ['code' => 'reglement_commission', 'libelle' => 'Règlement commission', 'ordre' => 1, 'requiert_operateur' => false],
            ['code' => 'apport_espece', 'libelle' => 'Apport espèce', 'ordre' => 2, 'requiert_operateur' => false],
            ['code' => 'retrait_espece', 'libelle' => 'Retrait espèce', 'ordre' => 3, 'requiert_operateur' => false],
            ['code' => 'apport_virtuel', 'libelle' => 'Apport virtuel', 'ordre' => 4, 'requiert_operateur' => true],
            ['code' => 'retrait_virtuel', 'libelle' => 'Retrait virtuel', 'ordre' => 5, 'requiert_operateur' => true],
        ];

        foreach ($types as $type) {
            TypeOperation::updateOrCreate(
                ['code' => $type['code']],
                [
                    'libelle' => $type['libelle'],
                    'ordre' => $type['ordre'],
                    'actif' => true,
                    'requiert_operateur' => $type['requiert_operateur'],
                ]
            );
        }

        // Désactiver les anciens types avec opérateur figé dans le nom (remplacés par types génériques + choix opérateur)
        TypeOperation::whereIn('code', [
            'apport_virtuel_tmoney', 'retrait_virtuel_tmoney',
            'apport_virtuel_flooz', 'retrait_virtuel_flooz',
        ])->update(['actif' => false]);

        $this->command->info('✅ Types d\'opération créés avec succès !');
    }
}
