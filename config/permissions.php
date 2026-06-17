<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes toujours accessibles (utilisateur authentifié)
    |--------------------------------------------------------------------------
    */
    'exempt' => [
        'api.my-permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sous-routes → route de permission (liens.route)
    | Patterns Laravel Str::is() — ordre : du plus spécifique au plus général
    |--------------------------------------------------------------------------
    */
    'route_aliases' => [
        'dashboard.technique*' => 'dashboard.technique',
        'dashboard.securite*' => 'dashboard.securite',
        'dashboard.securite.alerts.*' => 'dashboard.securite',
        'blocked-ips.*' => 'dashboard.securite',

        'agents.solde*' => 'agents.soldes',
        'agents.soldes*' => 'agents.soldes',
        'agents.liste-agents' => 'agents.index',
        'agents.*' => 'agents.index',
        'agent.dashboard' => 'agent.dashboard',

        'kiosques.carte*' => 'kiosques.carte',
        'kiosques.*' => 'kiosques.index',

        'transactions.*' => 'transactions.index',
        'utilisateurs.*' => 'utilisateurs.index',
        'api.utilisateurs.*' => 'utilisateurs.index',
        'operateurs.*' => 'operateurs.index',

        'roles-et-permissions.gestion-roles*' => 'roles-et-permissions.gestion-roles',
        'roles.*' => 'roles-et-permissions.gestion-roles',
        'permissions.*' => 'roles-et-permissions.gestion-permissions',
        'routes.*' => 'roles-et-permissions.gestion-routes',

        'rapports.*' => 'rapports.index',
        'operations-agence.*' => 'operations-agence.index',
        'gestion-entreprise.*' => 'gestion-entreprise.index',
        'parametres-app-mobile.*' => 'parametres-app-mobile.index',
        'system-logs.*' => 'system-logs.index',
    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixes URL (sans nom de route) → route de permission
    |--------------------------------------------------------------------------
    */
    'path_aliases' => [
        'api/dashboard/technique' => 'dashboard.technique',
        'dashboard/technique' => 'dashboard.technique',
        'api/dashboard/securite' => 'dashboard.securite',
        'dashboard/securite' => 'dashboard.securite',
        'api/blocked-ips' => 'dashboard.securite',
        'api/dashboard' => 'dashboard',
        'dashboard' => 'dashboard',
        'agents/soldes' => 'agents.soldes',
        'agents-soldes' => 'agents.soldes',
        'agents' => 'agents.index',
        'agent/dashboard' => 'agent.dashboard',
        'kiosques-carte' => 'kiosques.carte',
        'api/kiosques' => 'kiosques.index',
        'kiosques' => 'kiosques.index',
        'transactions' => 'transactions.index',
        'utilisateurs' => 'utilisateurs.index',
        'api/utilisateurs' => 'utilisateurs.index',
        'operateurs' => 'operateurs.index',
        'roles-et-permissions/gestion-roles' => 'roles-et-permissions.gestion-roles',
        'roles-et-permissions/gestion-permissions' => 'roles-et-permissions.gestion-permissions',
        'roles-et-permissions/gestion-routes' => 'roles-et-permissions.gestion-routes',
        'rapports' => 'rapports.index',
        'operations-agence' => 'operations-agence.index',
        'gestion-entreprise' => 'gestion-entreprise.index',
        'parametres-app-mobile' => 'parametres-app-mobile.index',
        'system-logs' => 'system-logs.index',
    ],

];
