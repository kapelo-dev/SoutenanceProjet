<?php

namespace Database\Seeders;

use App\Models\Kiosque;
use Illuminate\Database\Seeder;

class KiosqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kiosques = [
            [
                'code' => 'K001',
                'nom' => 'Kiosque Agoè Centre',
                'adresse' => 'Avenue de la Paix, près du marché',
                'quartier' => 'Agoè',
                'ville' => 'Lomé',
                'latitude' => 6.1667,
                'longitude' => 1.2167,
                'telephone' => '+228 90 12 34 56',
                'type' => 'fixe',
                'statut' => 'actif',
                'capacite_agents' => 3,
                'horaire_ouverture' => '08:00:00',
                'horaire_fermeture' => '18:00:00',
            ],
            [
                'code' => 'K002',
                'nom' => 'Kiosque Tokoin',
                'adresse' => 'Carrefour Tokoin Casablanca',
                'quartier' => 'Tokoin',
                'ville' => 'Lomé',
                'latitude' => 6.1733,
                'longitude' => 1.2309,
                'telephone' => '+228 90 23 45 67',
                'type' => 'fixe',
                'statut' => 'actif',
                'capacite_agents' => 5,
                'horaire_ouverture' => '07:30:00',
                'horaire_fermeture' => '19:00:00',
            ],
            [
                'code' => 'K003',
                'nom' => 'Kiosque Bè-Kpota',
                'adresse' => 'Marché de Bè-Kpota',
                'quartier' => 'Bè',
                'ville' => 'Lomé',
                'latitude' => 6.1289,
                'longitude' => 1.2158,
                'telephone' => '+228 90 34 56 78',
                'type' => 'fixe',
                'statut' => 'actif',
                'capacite_agents' => 2,
                'horaire_ouverture' => '08:00:00',
                'horaire_fermeture' => '17:00:00',
            ],
            [
                'code' => 'K004',
                'nom' => 'Kiosque Mobile Zone',
                'adresse' => 'Variable',
                'quartier' => 'Variable',
                'ville' => 'Lomé',
                'latitude' => null,
                'longitude' => null,
                'telephone' => '+228 90 45 67 89',
                'type' => 'mobile',
                'statut' => 'actif',
                'capacite_agents' => 1,
            ],
        ];

        foreach ($kiosques as $kiosque) {
            Kiosque::create($kiosque);
        }

        $this->command->info('✅ Kiosques créés avec succès!');
    }
}
