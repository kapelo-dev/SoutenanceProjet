# Documentation des Contrôleurs - Piscill POS

## 📋 Vue d'ensemble

Ce document décrit tous les contrôleurs créés pour l'application Piscill POS et leurs méthodes disponibles.

---

## 🎯 Contrôleurs Disponibles

### 1. **DashboardController**

Gère le tableau de bord principal avec statistiques et graphiques.

#### Méthodes

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `index()` | `/dashboard` | GET | Afficher le tableau de bord |
| `statsTempsReel()` | `/api/dashboard/stats-temps-reel` | GET | Stats en temps réel (AJAX) |
| `graphiqueTransactions()` | `/api/dashboard/graphique-transactions` | GET | Données pour graphiques |
| `statsParOperateur()` | `/api/dashboard/stats-par-operateur` | GET | Stats par opérateur |

#### Exemple d'utilisation

```php
// Appel AJAX pour stats temps réel
fetch('/api/dashboard/stats-temps-reel')
    .then(response => response.json())
    .then(data => {
        console.log(data.transactions_jour);
        console.log(data.montant_jour);
    });

// Graphique des transactions
fetch('/api/dashboard/graphique-transactions?periode=7jours')
    .then(response => response.json())
    .then(data => {
        // data contient les données pour Chart.js
    });
```

---

### 2. **OperateurController**

Gère les opérateurs de mobile money (YAS, Flooz, Orange, etc.).

#### Méthodes CRUD

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `index()` | `/operateurs` | GET | Liste des opérateurs |
| `create()` | `/operateurs/create` | GET | Formulaire de création |
| `store()` | `/operateurs` | POST | Enregistrer un opérateur |
| `show()` | `/operateurs/{id}` | GET | Voir un opérateur |
| `edit()` | `/operateurs/{id}/edit` | GET | Formulaire d'édition |
| `update()` | `/operateurs/{id}` | PUT/PATCH | Mettre à jour |
| `destroy()` | `/operateurs/{id}` | DELETE | Supprimer (soft delete) |

#### Méthodes Additionnelles

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `toggleStatus()` | `/operateurs/{id}/toggle-status` | POST | Activer/Désactiver |
| `statistiques()` | `/api/operateurs/{id}/statistiques` | GET | Stats d'un opérateur |

#### Exemple d'utilisation

```php
// Créer un opérateur
$data = [
    'code' => 'MOOV',
    'libelle' => 'Moov Money',
    'logo' => $request->file('logo'), // Upload
    'couleur' => '#0033A0',
    'statut' => 'actif',
    'ordre' => 4,
];

// Toggle status via AJAX
fetch('/operateurs/1/toggle-status', {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': token}
})
.then(response => response.json())
.then(data => console.log(data.statut)); // 'actif' ou 'inactif'
```

---

### 3. **KiosqueController** ⭐ NOUVEAU

Gère les kiosques avec géolocalisation GPS.

#### Méthodes CRUD

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `index()` | `/kiosques` | GET | Liste des kiosques + filtres |
| `create()` | `/kiosques/create` | GET | Formulaire de création |
| `store()` | `/kiosques` | POST | Enregistrer un kiosque |
| `show()` | `/kiosques/{id}` | GET | Voir un kiosque |
| `edit()` | `/kiosques/{id}/edit` | GET | Formulaire d'édition |
| `update()` | `/kiosques/{id}` | PUT/PATCH | Mettre à jour |
| `destroy()` | `/kiosques/{id}` | DELETE | Supprimer (soft delete) |

#### Méthodes Additionnelles

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `carte()` | `/kiosques-carte` | GET | Afficher la carte interactive |
| `proximite()` | `/api/kiosques/proximite` | GET | Kiosques à proximité (GPS) |
| `carteData()` | `/api/kiosques/carte-data` | GET | Données JSON pour carte |
| `assignerAgent()` | `/kiosques/{id}/assigner-agent` | POST | Assigner un agent |
| `retirerAgent()` | `/kiosques/{id}/agents/{agent}` | DELETE | Retirer un agent |

#### Exemple d'utilisation

```php
// Filtres disponibles
?ville=Lomé&quartier=Agoè&statut=actif&type=fixe

// Trouver kiosques à proximité
fetch('/api/kiosques/proximite?latitude=6.1667&longitude=1.2167&rayon=5')
    .then(response => response.json())
    .then(kiosques => {
        kiosques.forEach(k => {
            console.log(`${k.nom} - ${k.distance_km.toFixed(2)} km`);
        });
    });

// Assigner un agent
fetch('/kiosques/1/assigner-agent', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({agent_id: 5})
})
.then(response => response.json())
.then(data => console.log(data.places_disponibles));
```

---

### 4. **AgentController**

Gère les agents de terrain.

#### Méthodes CRUD

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `index()` | `/agents` | GET | Liste des agents + filtres |
| `create()` | `/agents/create` | GET | Formulaire de création |
| `store()` | `/agents` | POST | Enregistrer un agent |
| `show()` | `/agents/{id}` | GET | Voir un agent |
| `edit()` | `/agents/{id}/edit` | GET | Formulaire d'édition |
| `update()` | `/agents/{id}` | PUT/PATCH | Mettre à jour |
| `destroy()` | `/agents/{id}` | DELETE | Supprimer (soft delete) |

#### Méthodes Additionnelles

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `soldes()` | `/agents-soldes` | GET | Page des soldes |
| `updateSolde()` | `/agents/{id}/update-solde` | POST | Mettre à jour le solde |
| `getSoldes()` | `/api/agents/{id}/soldes` | GET | Obtenir les soldes (API) |
| `changeStatut()` | `/agents/{id}/change-statut` | POST | Changer le statut |

#### Exemple d'utilisation

```php
// Filtres disponibles
?statut=actif&kiosque_id=1&search=John

// Mettre à jour le solde
fetch('/agents/1/update-solde', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({
        type: 'virtuel',
        operateur_id: 1,
        montant: 50000,
        description: 'Rechargement'
    })
});

// Obtenir les soldes
fetch('/api/agents/1/soldes')
    .then(response => response.json())
    .then(data => {
        console.log('Solde total:', data.total);
        data.soldes.forEach(s => {
            console.log(`${s.type} - ${s.operateur?.libelle}: ${s.montant} FCFA`);
        });
    });
```

---

### 5. **TransactionController**

Gère les transactions de mobile money.

#### Méthodes CRUD

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `index()` | `/transactions` | GET | Liste + filtres |
| `create()` | `/transactions/create` | GET | Formulaire de création |
| `store()` | `/transactions` | POST | Enregistrer une transaction |
| `show()` | `/transactions/{id}` | GET | Voir une transaction |
| `edit()` | `/transactions/{id}/edit` | GET | Formulaire d'édition |
| `update()` | `/transactions/{id}` | PUT/PATCH | Mettre à jour |

#### Méthodes Additionnelles

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `annuler()` | `/transactions/{id}/annuler` | POST | Annuler une transaction |
| `statistiques()` | `/api/transactions/statistiques` | GET | Stats par période |
| `export()` | `/transactions/export` | GET | Exporter en CSV |

#### Exemple d'utilisation

```php
// Filtres disponibles
?statut=valide&type=depot&operateur_id=1&date_debut=2026-01-01&date_fin=2026-01-31&search=TXN

// Créer une transaction
$data = [
    'montant' => 5000,
    'type' => 'depot',
    'operateur_id' => 1,
    'agent_id' => 1,
    'statut' => 'valide',
    'commission' => 50,
    'client_nom' => 'Marie Kouassi',
    'client_telephone' => '+228 90 99 88 77',
];

// Annuler une transaction
fetch('/transactions/123/annuler', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({
        raison: 'Erreur de saisie'
    })
});

// Stats par période
fetch('/api/transactions/statistiques?periode=mois')
    .then(response => response.json())
    .then(stats => {
        console.log('Total:', stats.montant_total);
        console.log('Par type:', stats.par_type);
        console.log('Par opérateur:', stats.par_operateur);
    });
```

---

### 6. **UtilisateurController**

Gère les utilisateurs du système.

#### Méthodes CRUD

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `index()` | `/utilisateurs` | GET | Liste + filtres |
| `create()` | `/utilisateurs/create` | GET | Formulaire de création |
| `store()` | `/utilisateurs` | POST | Enregistrer un utilisateur |
| `show()` | `/utilisateurs/{id}` | GET | Voir un utilisateur |
| `edit()` | `/utilisateurs/{id}/edit` | GET | Formulaire d'édition |
| `update()` | `/utilisateurs/{id}` | PUT/PATCH | Mettre à jour |
| `destroy()` | `/utilisateurs/{id}` | DELETE | Supprimer (soft delete) |

#### Méthodes Additionnelles

| Méthode | Route | Type | Description |
|---------|-------|------|-------------|
| `changeStatut()` | `/utilisateurs/{id}/change-statut` | POST | Changer le statut |
| `liensAccessibles()` | `/api/utilisateurs/{id}/liens` | GET | Liens accessibles (menus) |
| `resetPassword()` | `/utilisateurs/{id}/reset-password` | POST | Réinitialiser mot de passe |

#### Exemple d'utilisation

```php
// Créer un utilisateur
$data = [
    'nom' => 'Doe',
    'prenom' => 'John',
    'email' => 'john@example.com',
    'mot_de_passe' => 'password123',
    'mot_de_passe_confirmation' => 'password123',
    'telephone' => '+228 90 12 34 56',
    'photo_profil' => $request->file('photo'),
    'statut' => 'actif',
    'profils' => [1, 2], // IDs des profils
];

// Obtenir les liens accessibles
fetch('/api/utilisateurs/1/liens')
    .then(response => response.json())
    .then(liens => {
        // Construire le menu dynamiquement
    });

// Réinitialiser mot de passe
fetch('/utilisateurs/1/reset-password', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({
        nouveau_mot_de_passe: 'newpassword123',
        nouveau_mot_de_passe_confirmation: 'newpassword123'
    })
});
```

---

## 🔒 Validation des Données

Tous les contrôleurs incluent une validation robuste des données :

### Opérateur
```php
'code' => 'required|string|max:50|unique:operateurs,code',
'couleur' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
```

### Kiosque
```php
'latitude' => 'nullable|numeric|between:-90,90',
'longitude' => 'nullable|numeric|between:-180,180',
'capacite_agents' => 'required|integer|min:1|max:20',
```

### Agent
```php
'telephone' => 'required|string|max:20|unique:agents,telephone',
'kiosque_id' => 'nullable|exists:kiosques,id',
```

### Transaction
```php
'montant' => 'required|numeric|min:0.01',
'type' => 'required|in:depot,retrait,transfert,paiement',
```

---

## 📊 Gestion des Soldes

### Logique de Mise à Jour Automatique

Lorsqu'une transaction est validée, le solde de l'agent est automatiquement mis à jour :

```php
// TransactionController@updateAgentBalance
switch ($transaction->type) {
    case 'depot':
        $nouveauMontant = $ancienMontant + $transaction->montant;
        break;
    case 'retrait':
        $nouveauMontant = $ancienMontant - $transaction->montant;
        break;
}
```

---

## 🗺️ Fonctionnalités Géolocalisation

### Calcul de Distance (Formule de Haversine)

```sql
SELECT *,
    (6371 * ACOS(
        COS(RADIANS(?)) * COS(RADIANS(latitude)) *
        COS(RADIANS(longitude) - RADIANS(?)) +
        SIN(RADIANS(?)) * SIN(RADIANS(latitude))
    )) AS distance_km
FROM kiosques
HAVING distance_km <= ?
ORDER BY distance_km
```

### API Endpoints Géolocalisation

```javascript
// Trouver kiosques dans un rayon de 10 km
GET /api/kiosques/proximite?latitude=6.1667&longitude=1.2167&rayon=10

// Données pour carte interactive
GET /api/kiosques/carte-data
```

---

## 📤 Export de Données

### Export CSV des Transactions

```php
GET /transactions/export?statut=valide&date_debut=2026-01-01&date_fin=2026-01-31
```

Le fichier CSV contient :
- Référence
- Date
- Type
- Montant
- Opérateur
- Agent
- Client
- Commission
- Statut

---

## 🔐 Sécurité

### Protection CSRF

Toutes les requêtes POST/PUT/DELETE nécessitent un token CSRF :

```javascript
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

### Upload de Fichiers

- **Taille max** : 2 MB
- **Types autorisés** : JPEG, PNG, JPG, GIF
- **Stockage** : `/storage/app/public/`

### Soft Delete

Tous les modèles utilisent le soft delete. Les enregistrements ne sont jamais vraiment supprimés.

---

## 📝 Exemples Complets

### Exemple 1 : Créer un Kiosque avec GPS

```php
POST /kiosques
{
    "code": "K005",
    "nom": "Kiosque Hédzranawoé",
    "adresse": "Route principale",
    "quartier": "Hédzranawoé",
    "ville": "Lomé",
    "latitude": 6.1445,
    "longitude": 1.2415,
    "telephone": "+228 90 12 34 56",
    "type": "fixe",
    "statut": "actif",
    "capacite_agents": 3,
    "horaire_ouverture": "08:00",
    "horaire_fermeture": "18:00"
}
```

### Exemple 2 : Transaction Complète

```php
POST /transactions
{
    "montant": 10000,
    "type": "depot",
    "operateur_id": 1,
    "agent_id": 5,
    "statut": "valide",
    "commission": 100,
    "client_nom": "Kofi Mensah",
    "client_telephone": "+228 90 88 77 66",
    "description": "Dépôt compte principal"
}
```

### Exemple 3 : Dashboard Stats (AJAX)

```javascript
// Mettre à jour les stats toutes les 30 secondes
setInterval(() => {
    fetch('/api/dashboard/stats-temps-reel')
        .then(response => response.json())
        .then(data => {
            document.getElementById('transactions-jour').textContent = data.transactions_jour;
            document.getElementById('montant-jour').textContent = data.montant_jour + ' FCFA';
        });
}, 30000);
```

---

## 🚀 Commandes Utiles

```bash
# Lister toutes les routes
php artisan route:list

# Filtrer par contrôleur
php artisan route:list --name=kiosques

# Voir les routes API
php artisan route:list --path=api

# Tester dans Tinker
php artisan tinker
>>> app(App\Http\Controllers\KiosqueController::class)->index(request())
```

---

## 📞 Support

Pour toute question :
- Voir `MIGRATIONS_README.md` pour les modèles
- Voir `nouveau_schema_mysql.md` pour le schéma complet
- Documentation Laravel : https://laravel.com/docs/11.x/controllers

---

**Version** : 2.1  
**Date** : 27 janvier 2026  
**Auteur** : Piscill POS Development Team
