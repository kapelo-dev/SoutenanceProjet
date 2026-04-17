# Système de Logs - Documentation

## Vue d'ensemble

Le système de logs permet de tracer toutes les actions importantes effectuées sur l'application, incluant les connexions, les modifications de données, les suppressions, etc.

## Architecture

### 1. Table `system_logs`

**Fichier de migration :** `database/migrations/2026_03_25_212900_create_system_logs_table.php`

**Colonnes principales :**
- `uid` : Identifiant unique UUID
- `user_id` : Utilisateur ayant effectué l'action
- `action` : Type d'action (create, update, delete, login, logout, etc.)
- `model_type` : Type d'entité concernée (Agent, Kiosque, Transaction, etc.)
- `model_id` : ID de l'entité concernée
- `description` : Description de l'action
- `old_values` : Anciennes valeurs (JSON)
- `new_values` : Nouvelles valeurs (JSON)
- `ip_address` : Adresse IP de l'utilisateur
- `user_agent` : Navigateur utilisé
- `metadata` : Métadonnées supplémentaires (JSON)

### 2. Modèle `SystemLog`

**Fichier :** `app/Models/SystemLog.php`

**Relations :**
- `utilisateur()` : Utilisateur ayant effectué l'action
- `model()` : Relation polymorphique vers l'entité concernée

**Scopes disponibles :**
- `byUser($userId)` : Filtrer par utilisateur
- `byAction($action)` : Filtrer par type d'action
- `byModel($modelType, $modelId)` : Filtrer par type d'entité
- `today()` : Logs du jour
- `thisWeek()` : Logs de la semaine
- `thisMonth()` : Logs du mois

**Méthodes statiques pour créer des logs :**
```php
// Log générique
SystemLog::logAction($action, $description, $model, $oldValues, $newValues, $metadata);

// Log de connexion
SystemLog::logLogin($user, $success = true);

// Log de déconnexion
SystemLog::logLogout($user);

// Log de création
SystemLog::logCreate($model, $description = null);

// Log de modification
SystemLog::logUpdate($model, $oldValues, $description = null);

// Log de suppression
SystemLog::logDelete($model, $description = null);
```

### 3. Trait `LogsActivity`

**Fichier :** `app/Traits/LogsActivity.php`

Ce trait peut être ajouté à n'importe quel modèle pour logger automatiquement les actions CRUD.

**Utilisation :**
```php
use App\Traits\LogsActivity;

class Agent extends Model
{
    use LogsActivity;
    
    // Optionnel : désactiver certains logs
    protected $logCreation = true;  // Logger les créations
    protected $logUpdate = true;    // Logger les modifications
    protected $logDeletion = true;  // Logger les suppressions
}
```

**Méthode personnalisée :**
```php
$agent->logCustomAction('assign', 'Agent affecté au kiosque XYZ', ['kiosque_id' => 5]);
```

### 4. Contrôleur `SystemLogController`

**Fichier :** `app/Http/Controllers/SystemLogController.php`

**Méthodes disponibles :**
- `index()` : Liste des logs avec filtres
- `show($systemLog)` : Détails d'un log
- `exportExcel()` : Export Excel des logs
- `exportPdf()` : Export PDF des logs
- `clean()` : Nettoyer les anciens logs

## Routes

```php
// Liste des logs
GET /system-logs

// Détails d'un log
GET /system-logs/{systemLog}

// Export Excel
GET /system-logs/export/excel

// Export PDF
GET /system-logs/export/pdf

// Nettoyer les anciens logs
POST /system-logs/clean
```

## Vues

### Page principale
**Fichier :** `resources/views/pages/system_logs/index.blade.php`

**Fonctionnalités :**
- Statistiques (total, aujourd'hui, cette semaine, ce mois)
- Filtres avancés (utilisateur, action, type d'entité, dates, recherche)
- Liste paginée des logs
- Modal de détails
- Export Excel/PDF

### Modal de détails
**Fichier :** `resources/views/pages/system_logs/show.blade.php`

Affiche toutes les informations d'un log :
- Date et heure
- Utilisateur
- Action
- Entité concernée
- IP et navigateur
- Description
- Anciennes et nouvelles valeurs
- Métadonnées

## Types d'actions

| Action | Description | Couleur |
|--------|-------------|---------|
| `create` | Création d'une entité | success |
| `update` | Modification d'une entité | primary |
| `delete` | Suppression d'une entité | danger |
| `login` | Connexion réussie | info |
| `logout` | Déconnexion | secondary |
| `login_failed` | Tentative de connexion échouée | warning |
| `assign` | Affectation | success |
| `unassign` | Retrait d'affectation | warning |
| `validate` | Validation | success |
| `cancel` | Annulation | danger |
| `export` | Export de données | info |
| `import` | Import de données | info |
| `other` | Autre action | secondary |

## Utilisation dans les contrôleurs

### Exemple 1 : Logger une création manuelle

```php
use App\Models\SystemLog;

public function store(Request $request)
{
    $agent = Agent::create($validated);
    
    // Logger la création
    SystemLog::logCreate($agent, "Nouvel agent créé : {$agent->nom} {$agent->prenom}");
    
    return redirect()->route('agents.index');
}
```

### Exemple 2 : Logger une modification avec anciennes valeurs

```php
public function update(Request $request, Agent $agent)
{
    // Sauvegarder les anciennes valeurs
    $oldValues = $agent->getOriginal();
    
    $agent->update($validated);
    
    // Logger la modification
    SystemLog::logUpdate($agent, $oldValues, "Agent modifié : {$agent->nom} {$agent->prenom}");
    
    return redirect()->route('agents.index');
}
```

### Exemple 3 : Logger une action personnalisée

```php
public function assignerAgent(Request $request, Kiosque $kiosque)
{
    $agent = Agent::find($request->agent_id);
    
    // Effectuer l'affectation
    $agent->kiosque_id = $kiosque->id;
    $agent->save();
    
    // Logger l'affectation
    SystemLog::logAction(
        'assign',
        "Agent {$agent->nom} {$agent->prenom} affecté au kiosque {$kiosque->nom}",
        $agent,
        null,
        null,
        [
            'kiosque_id' => $kiosque->id,
            'kiosque_nom' => $kiosque->nom,
        ]
    );
    
    return response()->json(['success' => true]);
}
```

## Logs automatiques des connexions

Les connexions et déconnexions sont automatiquement loggées dans `AuthController` :

**Connexion réussie :**
```php
SystemLog::logLogin($utilisateur, true);
```

**Connexion échouée (email inexistant) :**
```php
SystemLog::create([
    'action' => 'login_failed',
    'description' => "Tentative de connexion échouée pour l'email : {$email}",
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'metadata' => ['email' => $email],
]);
```

**Connexion échouée (compte inactif) :**
```php
SystemLog::create([
    'user_id' => $utilisateur->id,
    'action' => 'login_failed',
    'description' => "Tentative de connexion sur compte {$utilisateur->statut}",
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'metadata' => ['statut' => $utilisateur->statut],
]);
```

**Déconnexion :**
```php
SystemLog::logLogout($user);
```

## Filtres disponibles

Sur la page `/system-logs`, vous pouvez filtrer par :
- **Utilisateur** : Sélectionner un utilisateur spécifique
- **Action** : Type d'action (création, modification, connexion, etc.)
- **Type d'entité** : Agent, Kiosque, Transaction, etc.
- **Date début** : Date de début de la période
- **Date fin** : Date de fin de la période
- **Recherche** : Recherche dans la description

## Exports

### Export Excel
Exporte tous les logs filtrés au format Excel avec les colonnes :
- Date/Heure
- Utilisateur
- Action
- Entité
- Description
- IP

### Export PDF
Exporte les logs filtrés au format PDF (limité à 500 entrées) avec les mêmes colonnes.

## Nettoyage des anciens logs

Pour nettoyer les logs de plus de X jours :

```javascript
// Depuis l'interface (à implémenter)
fetch('/system-logs/clean', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ days: 90 })
})
```

Ou via Artisan (à créer) :
```bash
php artisan logs:clean --days=90
```

## Bonnes pratiques

1. **Logger les actions sensibles** : Toujours logger les créations, modifications et suppressions d'entités importantes
2. **Ajouter du contexte** : Utiliser le champ `metadata` pour ajouter des informations supplémentaires
3. **Descriptions claires** : Écrire des descriptions compréhensibles pour faciliter l'audit
4. **Nettoyer régulièrement** : Mettre en place un nettoyage automatique des logs anciens
5. **Protéger l'accès** : Restreindre l'accès aux logs aux administrateurs uniquement

## Sécurité

- Les logs ne peuvent pas être modifiés ou supprimés individuellement (sauf nettoyage en masse)
- L'accès à la page des logs doit être restreint aux administrateurs
- Les données sensibles (mots de passe) ne doivent jamais être loggées
- Les adresses IP et user agents sont enregistrés pour traçabilité

## Performances

- Index sur `user_id`, `action`, `model_type`, `model_id` et `created_at`
- Pagination des résultats (50 par page)
- Possibilité de nettoyer les anciens logs pour réduire la taille de la table

## Intégration future

Pour ajouter le logging automatique à un modèle existant :

1. Ajouter le trait `LogsActivity` au modèle
2. (Optionnel) Configurer les propriétés `$logCreation`, `$logUpdate`, `$logDeletion`
3. Les logs seront créés automatiquement lors des opérations CRUD

Exemple :
```php
use App\Traits\LogsActivity;

class Kiosque extends Model
{
    use LogsActivity;
    
    // Tous les logs sont activés par défaut
}
```
