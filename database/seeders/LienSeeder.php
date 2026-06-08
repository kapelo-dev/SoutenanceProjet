<?php

namespace Database\Seeders;

use App\Models\Lien;
use Illuminate\Database\Seeder;

class LienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si les liens existent déjà pour éviter les doublons
        if (Lien::where('libelle', 'Dashboard')->exists()) {
            $this->command->info('⚠️  Les liens existent déjà. Utilisez php artisan migrate:fresh --seed pour réinitialiser.');
            return;
        }

        // Menus principaux
        $dashboard = Lien::create([
            'libelle' => 'Dashboard',
            'route' => 'dashboard',
            'icone' => 'ki-filled ki-element-11',
            'ordre' => 1,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Métrique serveur',
            'route' => 'dashboard.technique',
            'icone' => 'ki-filled ki-chart-line-up-2',
            'ordre' => 2,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Dashboard Sécurité',
            'route' => 'dashboard.securite',
            'icone' => 'ki-filled ki-shield-search',
            'ordre' => 3,
            'visible' => true,
        ]);

        $transactions = Lien::create([
            'libelle' => 'Transactions',
            'route' => 'transactions.index',
            'icone' => 'ki-filled ki-chart-line',
            'ordre' => 2,
            'visible' => true,
        ]);

        // Menu Agents avec sous-menus
        $menuAgents = Lien::create([
            'libelle' => 'Agents',
            'route' => null,
            'icone' => 'ki-filled ki-people',
            'ordre' => 3,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Mon Dashboard',
            'route' => 'agent.dashboard',
            'parent_id' => $menuAgents->id,
            'ordre' => 1,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Liste des Agents',
            'route' => 'agents.index',
            'parent_id' => $menuAgents->id,
            'ordre' => 2,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Soldes des Agents',
            'route' => 'agents.soldes',
            'parent_id' => $menuAgents->id,
            'ordre' => 3,
            'visible' => true,
        ]);

        // Menu Kiosques avec sous-menus
        $menuKiosques = Lien::create([
            'libelle' => 'Kiosques',
            'route' => null,
            'icone' => 'ki-filled ki-shop',
            'ordre' => 4,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Liste des Kiosques',
            'route' => 'kiosques.index',
            'parent_id' => $menuKiosques->id,
            'ordre' => 1,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Carte des Kiosques',
            'route' => 'kiosques.carte',
            'parent_id' => $menuKiosques->id,
            'ordre' => 2,
            'visible' => true,
        ]);

        $utilisateurs = Lien::create([
            'libelle' => 'Utilisateurs',
            'route' => 'utilisateurs.index',
            'icone' => 'ki-filled ki-profile-user',
            'ordre' => 5,
            'visible' => true,
        ]);

        $rapports = Lien::create([
            'libelle' => 'Rapports',
            'route' => 'rapports.index',
            'icone' => 'ki-filled ki-chart',
            'ordre' => 6,
            'visible' => true,
        ]);

        $operationsAgence = Lien::create([
            'libelle' => 'Opérations en Agence',
            'route' => 'operations-agence.index',
            'icone' => 'ki-filled ki-dollar',
            'ordre' => 7,
            'visible' => true,
        ]);

        // Menu Gestion d'Entreprise avec sous-menus (soumis aux permissions comme les autres)
        $menuEntreprise = Lien::create([
            'libelle' => 'Gestion d\'Entreprise',
            'route' => 'gestion-entreprise.index',
            'icone' => 'ki-filled ki-office-bag',
            'ordre' => 8,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Salaires',
            'url' => '/gestion-entreprise?onglet=salaires',
            'parent_id' => $menuEntreprise->id,
            'ordre' => 1,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Paramètres Salaire',
            'url' => '/gestion-entreprise?onglet=parametres',
            'parent_id' => $menuEntreprise->id,
            'ordre' => 2,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Trésorerie',
            'url' => '/gestion-entreprise?onglet=tresorerie',
            'parent_id' => $menuEntreprise->id,
            'ordre' => 3,
            'visible' => true,
        ]);

        // Menu Configuration avec sous-menus
        $menuConfig = Lien::create([
            'libelle' => 'Configuration',
            'route' => null,
            'icone' => 'ki-filled ki-setting-2',
            'ordre' => 9,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Gestion des Rôles',
            'route' => 'roles-et-permissions.gestion-roles',
            'parent_id' => $menuConfig->id,
            'ordre' => 1,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Gestion des Permissions',
            'route' => 'roles-et-permissions.gestion-permissions',
            'parent_id' => $menuConfig->id,
            'ordre' => 2,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Gestion des Routes',
            'route' => 'roles-et-permissions.gestion-routes',
            'parent_id' => $menuConfig->id,
            'ordre' => 3,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Opérateurs Mobile Money',
            'route' => 'operateurs.index',
            'parent_id' => $menuConfig->id,
            'ordre' => 4,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Configuration App Mobile',
            'route' => 'parametres-app-mobile.index',
            'parent_id' => $menuConfig->id,
            'ordre' => 5,
            'visible' => true,
        ]);

        Lien::create([
            'libelle' => 'Logs Système',
            'route' => 'system-logs.index',
            'parent_id' => $menuConfig->id,
            'ordre' => 6,
            'visible' => true,
        ]);

        $this->command->info('✅ Liens créés avec succès!');
    }
}
