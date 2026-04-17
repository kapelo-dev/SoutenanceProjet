<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DU MENU LOGS SYSTÈME ===\n\n";

// 1. Vérifier que le lien existe dans la base de données
echo "1. Vérification du lien dans la base de données:\n";
$lien = DB::table('liens')->where('route', 'system-logs.index')->first();
if ($lien) {
    echo "   ✅ Lien trouvé (ID: {$lien->id})\n";
    echo "   - Libellé: {$lien->libelle}\n";
    echo "   - Route: {$lien->route}\n";
    echo "   - Parent ID: {$lien->parent_id}\n";
    echo "   - Visible: " . ($lien->visible ? 'OUI' : 'NON') . "\n";
} else {
    echo "   ❌ Lien NON trouvé\n";
    exit(1);
}

echo "\n2. Vérification de la permission pour Super Admin:\n";
$superAdmin = DB::table('profils')->where('libelle', 'Super Admin')->first();
if ($superAdmin) {
    echo "   ✅ Profil Super Admin trouvé (ID: {$superAdmin->id})\n";
    
    $permission = DB::table('profil_liens')
        ->where('profil_id', $superAdmin->id)
        ->where('lien_id', $lien->id)
        ->whereNull('deleted_at')
        ->first();
    
    if ($permission) {
        echo "   ✅ Permission existe dans profil_liens\n";
    } else {
        echo "   ❌ Permission NON trouvée dans profil_liens\n";
        echo "   Ajout de la permission...\n";
        DB::table('profil_liens')->insert([
            'profil_id' => $superAdmin->id,
            'lien_id' => $lien->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "   ✅ Permission ajoutée\n";
    }
} else {
    echo "   ❌ Profil Super Admin NON trouvé\n";
}

echo "\n3. Vérification de la route Laravel:\n";
if (Route::has('system-logs.index')) {
    echo "   ✅ Route 'system-logs.index' existe\n";
    echo "   - URL: " . route('system-logs.index') . "\n";
} else {
    echo "   ❌ Route 'system-logs.index' NON trouvée\n";
}

echo "\n4. Test de l'API getMyPermissions:\n";
$user = DB::table('utilisateurs')->where('email', 'admin@admin.com')->first();
if (!$user) {
    $user = DB::table('utilisateurs')->first();
}

if ($user) {
    echo "   Test avec l'utilisateur: {$user->email}\n";
    
    // Simuler la requête
    $profilIds = DB::table('user_profils')
        ->where('user_id', $user->id)
        ->whereNull('deleted_at')
        ->pluck('profil_id')
        ->toArray();
    
    echo "   - Profils de l'utilisateur: " . implode(', ', $profilIds) . "\n";
    
    $liens = DB::table('profil_liens')
        ->join('liens', 'profil_liens.lien_id', '=', 'liens.id')
        ->whereIn('profil_liens.profil_id', $profilIds)
        ->whereNull('profil_liens.deleted_at')
        ->whereNull('liens.deleted_at')
        ->where('liens.visible', true)
        ->where('liens.route', 'system-logs.index')
        ->select('liens.route', 'liens.url', 'liens.id as lien_id')
        ->get();
    
    if ($liens->count() > 0) {
        echo "   ✅ Le lien 'system-logs.index' est retourné par la requête\n";
    } else {
        echo "   ❌ Le lien 'system-logs.index' n'est PAS retourné par la requête\n";
    }
} else {
    echo "   ❌ Aucun utilisateur trouvé\n";
}

echo "\n=== FIN DU TEST ===\n";
echo "\nSi tous les tests sont ✅, le menu devrait apparaître après:\n";
echo "1. Vider le cache du navigateur (Ctrl+Shift+R)\n";
echo "2. Se déconnecter et se reconnecter\n";
echo "3. Vérifier dans Configuration > Logs Système\n";
