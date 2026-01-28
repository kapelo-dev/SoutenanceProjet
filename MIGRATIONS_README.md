# Guide des Migrations et Modèles - Piscill POS

## 📋 Vue d'ensemble

Ce guide explique comment utiliser les migrations et modèles Eloquent créés pour l'application Piscill POS.

## 🗂️ Structure des Fichiers

### Migrations (dans `/database/migrations/`)

1. `2026_01_27_000001_create_utilisateurs_table.php`
2. `2026_01_27_000002_create_profils_table.php`
3. `2026_01_27_000003_create_user_profils_table.php`
4. `2026_01_27_000004_create_liens_table.php`
5. `2026_01_27_000005_create_profil_liens_table.php`
6. `2026_01_27_000006_create_operateurs_table.php`
7. `2026_01_27_000007_create_kiosques_table.php` ⭐ **NOUVEAU**
8. `2026_01_27_000008_create_agents_table.php`
9. `2026_01_27_000009_create_transactions_table.php`
10. `2026_01_27_000010_create_soldes_table.php`
11. `2026_01_27_000011_create_audits_table.php`

### Modèles (dans `/app/Models/`)

- `Utilisateur.php`
- `Profil.php`
- `Lien.php`
- `Operateur.php`
- `Kiosque.php` ⭐ **NOUVEAU**
- `Agent.php`
- `Transaction.php`
- `Solde.php`
- `Audit.php`

### Seeders (dans `/database/seeders/`)

- `DatabaseSeeder.php` (fichier principal)
- `OperateurSeeder.php`
- `ProfilSeeder.php`
- `LienSeeder.php`
- `KiosqueSeeder.php`

---

## 🚀 Commandes d'Installation

### 1. Configuration de la Base de Données

Mettez à jour votre fichier `.env` avec les informations de connexion MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=piscill_pos
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 2. Créer la Base de Données

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE piscill_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 3. Exécuter les Migrations

```bash
# Exécuter toutes les migrations
php artisan migrate

# Si vous voulez recommencer depuis zéro
php artisan migrate:fresh

# Avec les seeders en même temps
php artisan migrate:fresh --seed
```

### 4. Exécuter les Seeders

```bash
# Exécuter tous les seeders
php artisan db:seed

# Exécuter un seeder spécifique
php artisan db:seed --class=OperateurSeeder
php artisan db:seed --class=ProfilSeeder
php artisan db:seed --class=LienSeeder
php artisan db:seed --class=KiosqueSeeder
```

---

## 📊 Données Initiales Insérées

### Opérateurs

- **Mixx by YAS** (code: YAS, couleur: #FF6B00)
- **Flooz** (code: FLOOZ, couleur: #00A651)
- **Orange Money** (code: ORANGE, couleur: #FF7900)

### Profils

- **Super Admin** (niveau 0)
- **Admin** (niveau 1)
- **Superviseur** (niveau 2)
- **Comptable** (niveau 2)
- **Agent** (niveau 3)

### Liens (Menus)

- Dashboard
- Transactions
- Agents (avec sous-menus)
  - Liste des Agents
  - Soldes des Agents
- **Kiosques** ⭐ (avec sous-menus)
  - Liste des Kiosques
  - Carte des Kiosques
- Utilisateurs
- Rapports
- Opérations en Agence
- Configuration (avec sous-menus)
  - Gestion des Rôles
  - Gestion des Permissions
  - Gestion des Routes
  - Opérateurs Mobile Money

### Kiosques (Exemples)

- **K001** - Kiosque Agoè Centre (6.1667, 1.2167)
- **K002** - Kiosque Tokoin (6.1733, 1.2309)
- **K003** - Kiosque Bè-Kpota (6.1289, 1.2158)
- **K004** - Kiosque Mobile Zone (mobile, sans coordonnées fixes)

---

## 💡 Exemples d'Utilisation des Modèles

### Opérateurs

```php
use App\Models\Operateur;

// Récupérer tous les opérateurs actifs
$operateurs = Operateur::actif()->get();

// Créer un nouvel opérateur
$operateur = Operateur::create([
    'code' => 'MOOV',
    'libelle' => 'Moov Money',
    'logo' => 'logos/operateurs/moov.png',
    'couleur' => '#0033A0',
    'statut' => 'actif',
    'ordre' => 4,
]);

// Récupérer les transactions d'un opérateur
$transactions = $operateur->transactions()->valide()->get();
```

### Kiosques

```php
use App\Models\Kiosque;

// Récupérer tous les kiosques actifs avec coordonnées GPS
$kiosques = Kiosque::actif()->avecCoordonnees()->get();

// Créer un nouveau kiosque
$kiosque = Kiosque::create([
    'code' => 'K005',
    'nom' => 'Kiosque Hédzranawoé',
    'quartier' => 'Hédzranawoé',
    'ville' => 'Lomé',
    'latitude' => 6.1445,
    'longitude' => 1.2415,
    'type' => 'fixe',
    'statut' => 'actif',
    'capacite_agents' => 3,
]);

// Calculer la distance entre le kiosque et une position
$distance = $kiosque->distanceVers(6.1667, 1.2167); // en km

// Vérifier si le kiosque est saturé
if ($kiosque->estSature()) {
    echo "Kiosque plein !";
}

// Nombre de places disponibles
$places = $kiosque->placesDisponibles();

// Récupérer les agents du kiosque
$agents = $kiosque->agentsActifs;
```

### Agents

```php
use App\Models\Agent;

// Créer un agent et l'assigner à un kiosque
$agent = Agent::create([
    'nom' => 'Doe',
    'prenom' => 'John',
    'telephone' => '+228 90 12 34 56',
    'code_agent' => 'AG001',
    'kiosque_id' => 1, // ID du kiosque
    'statut' => 'actif',
]);

// Récupérer le kiosque de l'agent
$kiosque = $agent->kiosque;

// Récupérer tous les agents d'une ville
$agentsLome = Agent::actif()
    ->whereHas('kiosque', function($query) {
        $query->where('ville', 'Lomé');
    })
    ->get();

// Agents sans kiosque
$agentsSansKiosque = Agent::actif()->sansKiosque()->get();

// Solde actuel de l'agent
$soldes = $agent->soldesActuels();
$soldeTotal = $agent->soldeTotal();
```

### Transactions

```php
use App\Models\Transaction;

// Créer une transaction
$transaction = Transaction::create([
    'montant' => 5000,
    'type' => 'depot',
    'operateur_id' => 1, // YAS
    'agent_id' => 1,
    'statut' => 'valide',
    'commission' => 50,
    'client_nom' => 'Marie Kouassi',
    'client_telephone' => '+228 90 99 88 77',
]);

// Transactions du jour
$transactionsDuJour = Transaction::valide()->duJour()->get();

// Transactions par opérateur
$transactionsYas = Transaction::where('operateur_id', 1)
    ->duMois()
    ->sum('montant');

// Statistiques
$stats = [
    'total' => Transaction::valide()->duJour()->sum('montant'),
    'depot' => Transaction::valide()->depot()->duJour()->sum('montant'),
    'retrait' => Transaction::valide()->retrait()->duJour()->sum('montant'),
    'commission' => Transaction::valide()->duJour()->sum('commission'),
];
```

### Utilisateurs et Profils

```php
use App\Models\Utilisateur;
use App\Models\Profil;

// Créer un utilisateur
$user = Utilisateur::create([
    'nom' => 'Admin',
    'prenom' => 'Super',
    'email' => 'admin@piscillpos.com',
    'mot_de_passe' => bcrypt('password'),
    'statut' => 'actif',
]);

// Assigner un profil
$profil = Profil::where('libelle', 'Super Admin')->first();
$user->profils()->attach($profil->id);

// Récupérer les profils d'un utilisateur
$profils = $user->profils;

// Récupérer les liens accessibles par un utilisateur
$liens = Lien::whereHas('profils', function($query) use ($user) {
    $query->whereIn('profil_id', $user->profils->pluck('id'));
})->visible()->menuPrincipal()->get();
```

### Recherche de Kiosques à Proximité

```php
// Trouver les kiosques dans un rayon de 5 km
$latitude = 6.1667;
$longitude = 1.2167;
$rayon = 5; // km

$kiosquesProches = Kiosque::selectRaw("
    *,
    (6371 * ACOS(
        COS(RADIANS(?)) * COS(RADIANS(latitude)) *
        COS(RADIANS(longitude) - RADIANS(?)) +
        SIN(RADIANS(?)) * SIN(RADIANS(latitude))
    )) AS distance_km
", [$latitude, $longitude, $latitude])
    ->actif()
    ->avecCoordonnees()
    ->having('distance_km', '<=', $rayon)
    ->orderBy('distance_km')
    ->get();
```

---

## 🔍 Vérifications Post-Migration

### Vérifier les tables créées

```bash
php artisan migrate:status
```

### Tester en Tinker

```bash
php artisan tinker

# Dans Tinker:
>>> \App\Models\Operateur::count()
=> 3

>>> \App\Models\Profil::all()
=> Collection {...}

>>> \App\Models\Kiosque::actif()->avecCoordonnees()->count()
=> 3

>>> $kiosque = \App\Models\Kiosque::first()
>>> $kiosque->estSature()
=> false

>>> $kiosque->placesDisponibles()
=> 3
```

---

## 🛠️ Commandes Utiles

### Rollback

```bash
# Annuler la dernière migration
php artisan migrate:rollback

# Annuler les 3 dernières migrations
php artisan migrate:rollback --step=3

# Tout réinitialiser
php artisan migrate:reset
```

### Refresh (Rollback + Migrate)

```bash
# Réinitialiser et réexécuter toutes les migrations
php artisan migrate:refresh

# Avec les seeders
php artisan migrate:refresh --seed
```

### Fresh (Drop + Migrate)

```bash
# Supprimer toutes les tables et recréer
php artisan migrate:fresh

# Avec les seeders
php artisan migrate:fresh --seed
```

---

## 📝 Notes Importantes

### UUID Automatique

Les modèles suivants génèrent automatiquement un UUID lors de la création :
- `Utilisateur`
- `Kiosque`
- `Agent`
- `Transaction`
- `Solde`
- `Audit`

Vous n'avez pas besoin de fournir le champ `uid` lors de la création.

### Relations

Toutes les relations sont correctement définies avec les clés étrangères. Laravel gérera automatiquement les suppressions en cascade ou les mises à NULL selon la configuration.

### Soft Deletes

Les modèles suivants utilisent le soft delete :
- `Utilisateur`
- `Profil`
- `Lien`
- `Operateur`
- `Kiosque`
- `Agent`

Les enregistrements ne sont jamais vraiment supprimés, seulement marqués avec `deleted_at`.

### Géolocalisation

Les coordonnées GPS utilisent le type `DECIMAL(10,8)` pour la latitude et `DECIMAL(11,8)` pour la longitude, permettant une précision d'environ 1 mètre.

---

## 🎯 Prochaines Étapes

1. ✅ Migrations créées
2. ✅ Modèles Eloquent créés
3. ✅ Seeders créés
4. 🔜 Créer les contrôleurs
5. 🔜 Créer les routes
6. 🔜 Créer les vues
7. 🔜 Implémenter l'authentification
8. 🔜 Implémenter les permissions

---

## 📞 Support

Pour toute question ou problème, référez-vous à :
- La documentation Laravel : https://laravel.com/docs
- Le fichier `nouveau_schema_mysql.md` pour le schéma complet

---

**Version** : 2.1  
**Date** : 27 janvier 2026  
**Auteur** : Piscill POS Development Team
