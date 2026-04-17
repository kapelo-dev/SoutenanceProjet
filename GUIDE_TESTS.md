# Guide Complet des Tests - PDV CONNECT

## 📋 Table des matières
1. [Introduction](#introduction)
2. [Configuration de l'environnement de test](#configuration)
3. [Tests Unitaires](#tests-unitaires)
4. [Tests de Sécurité](#tests-sécurité)
5. [Exécution des tests](#exécution)
6. [Bonnes pratiques](#bonnes-pratiques)
7. [Couverture de code](#couverture)

---

## 🎯 Introduction

Ce guide vous explique comment réaliser des **tests unitaires** et des **tests de sécurité** sur l'application PDV CONNECT.

### Types de tests implémentés

- **Tests Unitaires** : Testent les modèles, les méthodes et la logique métier
- **Tests Feature** : Testent les fonctionnalités complètes (routes, contrôleurs, vues)
- **Tests de Sécurité** : Testent l'authentification, les autorisations, CSRF, XSS, SQL injection

---

## ⚙️ Configuration de l'environnement de test

### 1. Fichier de configuration PHPUnit

Le fichier `phpunit.xml` est déjà configuré pour utiliser :
- **Base de données SQLite en mémoire** (`:memory:`) pour les tests
- **Cache en array** pour éviter les effets de bord
- **Sessions en array** pour les tests d'authentification

### 2. Variables d'environnement de test

Les variables suivantes sont automatiquement définies lors des tests :
```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### 3. Installation des dépendances

Assurez-vous que PHPUnit est installé :
```bash
composer install
```

---

## 🧪 Tests Unitaires

### Qu'est-ce qu'un test unitaire ?

Un test unitaire vérifie qu'une **unité de code** (méthode, fonction, classe) fonctionne correctement de manière isolée.

### Exemples de tests unitaires créés

#### 1. **TransactionTest.php** - Tests du modèle Transaction

**Localisation** : `tests/Unit/TransactionTest.php`

**Tests implémentés** :
- ✅ Génération automatique de la référence (TXN-XXXXXXXXXX)
- ✅ Génération automatique de l'UUID
- ✅ Scope `valide()` filtre les transactions validées
- ✅ Scope `depot()` filtre les dépôts
- ✅ Relation avec Agent
- ✅ Relation avec Operateur
- ✅ Validation du montant positif

**Exemple de test** :
```php
public function test_transaction_genere_reference_automatiquement()
{
    $agent = Agent::factory()->create();
    $operateur = Operateur::factory()->create();

    $transaction = Transaction::create([
        'montant' => 10000,
        'type' => 'depot',
        'agent_id' => $agent->id,
        'operateur_id' => $operateur->id,
        'statut' => 'valide',
    ]);

    $this->assertNotNull($transaction->reference);
    $this->assertStringStartsWith('TXN-', $transaction->reference);
}
```

#### 2. **AgentTest.php** - Tests du modèle Agent

**Localisation** : `tests/Unit/AgentTest.php`

**Tests implémentés** :
- ✅ Génération automatique du code agent (AG0001, AG0002...)
- ✅ Relation avec Utilisateur
- ✅ Relation avec Kiosque
- ✅ Scope `actif()` filtre les agents actifs
- ✅ Validation du numéro de téléphone
- ✅ Relation avec les soldes

---

## 🔒 Tests de Sécurité

### Qu'est-ce qu'un test de sécurité ?

Un test de sécurité vérifie que l'application est **protégée contre les vulnérabilités** courantes (OWASP Top 10).

### Exemples de tests de sécurité créés

#### 1. **TransactionSecurityTest.php** - Sécurité des transactions

**Localisation** : `tests/Feature/TransactionSecurityTest.php`

**Tests implémentés** :
- ✅ **Authentification** : Utilisateurs non authentifiés redirigés vers login
- ✅ **Protection CSRF** : Tokens CSRF requis pour les formulaires
- ✅ **Injection SQL** : Recherche protégée contre les injections SQL
- ✅ **XSS (Cross-Site Scripting)** : Échappement des balises HTML
- ✅ **Autorisation** : Seul le propriétaire peut annuler sa transaction
- ✅ **Validation** : Montants négatifs refusés
- ✅ **Validation** : Types de transaction invalides refusés
- ✅ **Rate Limiting** : Limitation du nombre de requêtes API
- ✅ **Logique métier** : Transactions annulées ne peuvent pas être ré-annulées
- ✅ **En-têtes de sécurité** : X-Frame-Options, X-Content-Type-Options

**Exemple de test XSS** :
```php
public function test_champs_transaction_proteges_contre_xss()
{
    $scriptMalveillant = '<script>alert("XSS")</script>';

    $response = $this->post('/transactions', [
        'description' => $scriptMalveillant,
    ]);

    $transaction = Transaction::first();
    
    // Le script est stocké tel quel
    $this->assertEquals($scriptMalveillant, $transaction->description);
    
    // Mais échappé à l'affichage par Blade
    $viewResponse = $this->get('/transactions/' . $transaction->id);
    $viewResponse->assertSee('&lt;script&gt;', false);
}
```

#### 2. **AuthenticationSecurityTest.php** - Sécurité de l'authentification

**Localisation** : `tests/Feature/AuthenticationSecurityTest.php`

**Tests implémentés** :
- ✅ **Accès page login** : Accessible sans authentification
- ✅ **Connexion valide** : Identifiants corrects acceptés
- ✅ **Connexion invalide** : Mot de passe incorrect refusé
- ✅ **Rate Limiting** : Protection contre force brute
- ✅ **Déconnexion** : Session détruite correctement
- ✅ **Hashage** : Mots de passe hashés (bcrypt)
- ✅ **Validation email** : Format email vérifié
- ✅ **Expiration session** : Sessions expirent après inactivité
- ✅ **Régénération session** : Protection contre fixation de session

**Exemple de test d'authentification** :
```php
public function test_connexion_avec_identifiants_valides()
{
    $utilisateur = Utilisateur::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($utilisateur);
    $response->assertRedirect('/dashboard');
}
```

---

## 🚀 Exécution des tests

### Commandes de base

#### Exécuter tous les tests
```bash
php artisan test
```

#### Exécuter uniquement les tests unitaires
```bash
php artisan test --testsuite=Unit
```

#### Exécuter uniquement les tests feature
```bash
php artisan test --testsuite=Feature
```

#### Exécuter un fichier de test spécifique
```bash
php artisan test tests/Unit/TransactionTest.php
```

#### Exécuter un test spécifique
```bash
php artisan test --filter test_transaction_genere_reference_automatiquement
```

### Options utiles

#### Afficher plus de détails
```bash
php artisan test --verbose
```

#### Arrêter au premier échec
```bash
php artisan test --stop-on-failure
```

#### Exécuter en parallèle (plus rapide)
```bash
php artisan test --parallel
```

---

## ✅ Bonnes pratiques

### 1. Nommage des tests

- Utilisez des noms descriptifs en français ou anglais
- Préfixez avec `test_`
- Exemple : `test_transaction_genere_reference_automatiquement()`

### 2. Structure AAA (Arrange, Act, Assert)

```php
public function test_exemple()
{
    // Arrange : Préparer les données
    $agent = Agent::factory()->create();
    
    // Act : Exécuter l'action
    $transaction = Transaction::create([...]);
    
    // Assert : Vérifier le résultat
    $this->assertNotNull($transaction->reference);
}
```

### 3. Utiliser RefreshDatabase

Toujours utiliser le trait `RefreshDatabase` pour réinitialiser la base de données entre chaque test :

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonTest extends TestCase
{
    use RefreshDatabase;
}
```

### 4. Factories pour les données de test

Utilisez les factories pour créer des données de test :

```php
$agent = Agent::factory()->create();
$transactions = Transaction::factory()->count(5)->create();
```

### 5. Tests indépendants

Chaque test doit être **indépendant** et pouvoir s'exécuter seul.

### 6. Un test = Une assertion principale

Chaque test doit vérifier **une seule chose** pour faciliter le débogage.

---

## 📊 Couverture de code

### Générer un rapport de couverture

#### Avec Xdebug (HTML)
```bash
XDEBUG_MODE=coverage php artisan test --coverage-html coverage
```

Le rapport sera dans le dossier `coverage/index.html`.

#### Avec PCOV (plus rapide)
```bash
php artisan test --coverage
```

### Objectif de couverture

- **Minimum** : 70% de couverture
- **Recommandé** : 80-90% de couverture
- **Critique** : 100% pour les fonctions de sécurité et paiement

---

## 🛡️ Checklist de sécurité

### Tests à implémenter pour chaque fonctionnalité

- [ ] **Authentification** : Seuls les utilisateurs connectés peuvent accéder
- [ ] **Autorisation** : Vérifier les permissions (qui peut faire quoi)
- [ ] **CSRF** : Tokens CSRF sur tous les formulaires POST/PUT/DELETE
- [ ] **XSS** : Échappement de toutes les entrées utilisateur
- [ ] **SQL Injection** : Utiliser Eloquent ou requêtes préparées
- [ ] **Validation** : Valider toutes les entrées (type, longueur, format)
- [ ] **Rate Limiting** : Limiter les tentatives de connexion et API
- [ ] **HTTPS** : Forcer HTTPS en production
- [ ] **En-têtes de sécurité** : X-Frame-Options, CSP, etc.
- [ ] **Logs** : Logger les actions sensibles (connexion, modifications)

---

## 📝 Créer vos propres tests

### Créer un test unitaire

```bash
php artisan make:test NomDuTest --unit
```

### Créer un test feature

```bash
php artisan make:test NomDuTest
```

### Template de test

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonTest extends TestCase
{
    use RefreshDatabase;

    public function test_exemple()
    {
        // Arrange
        $data = [...];
        
        // Act
        $result = maFonction($data);
        
        // Assert
        $this->assertEquals($expected, $result);
    }
}
```

---

## 🔍 Assertions courantes

### Assertions de base
```php
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);
$this->assertNull($value);
$this->assertNotNull($value);
```

### Assertions de base de données
```php
$this->assertDatabaseHas('transactions', ['reference' => 'TXN-123']);
$this->assertDatabaseMissing('transactions', ['statut' => 'invalide']);
$this->assertDatabaseCount('transactions', 5);
```

### Assertions HTTP
```php
$response->assertStatus(200);
$response->assertRedirect('/dashboard');
$response->assertJson(['success' => true]);
$response->assertSessionHasErrors('email');
```

### Assertions d'authentification
```php
$this->assertAuthenticated();
$this->assertGuest();
$this->assertAuthenticatedAs($user);
```

---

## 🎓 Ressources supplémentaires

- [Documentation Laravel Testing](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)

---

## 📞 Support

Pour toute question sur les tests, consultez :
1. Ce guide
2. La documentation Laravel
3. Les exemples de tests dans `tests/Unit` et `tests/Feature`

---

**Bonne chance avec vos tests ! 🚀**
