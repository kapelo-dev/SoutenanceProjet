# Documentation - Mise à Jour des Vues Blade avec Données Dynamiques

## 📋 Vue d'ensemble

Ce document liste toutes les vues Blade qui ont été mises à jour pour afficher les vraies données depuis la base de données via les contrôleurs Laravel.

**Date de mise à jour** : 27 janvier 2026  
**Version** : 2.1

---

## ✅ Vues Mises à Jour

### 1. **Dashboard** (`pages/dashboard/index.blade.php`)

**Contrôleur** : `DashboardController@index`  
**Route** : `/dashboard`

#### Données Affichées

- **Stats temps réel** :
  - Transactions du jour (count + montant)
  - Agents actifs
  - Kiosques actifs
  - Commissions du jour

- **Transactions par type** :
  - Dépôts (aujourd'hui)
  - Retraits (aujourd'hui)
  - Transferts (aujourd'hui)
  - Paiements (aujourd'hui)

- **Transactions par opérateur** (du mois) :
  - Pour chaque opérateur (YAS, Flooz, Orange)
  - Montant total + nombre de transactions

- **Top 10 Agents du mois** :
  - Nom, code, kiosque
  - Nombre de transactions
  - Montant total traité

- **Dernières transactions** :
  - 10 dernières transactions avec tous les détails

#### Variables Disponibles

```php
$stats = [
    'transactions_jour' => int,
    'montant_jour' => float,
    'commission_jour' => float,
    'transactions_mois' => int,
    'montant_mois' => float,
    'agents_actifs' => int,
    'kiosques_actifs' => int,
    'kiosques_satures' => int,
];

$transactionsParType = [
    'depot' => float,
    'retrait' => float,
    'transfert' => float,
    'paiement' => float,
];

$operateurs = Collection; // Avec stats par opérateur
$topAgents = Collection; // Top 10 agents
$dernieresTransactions = Collection; // 10 dernières
$evolutionTransactions = Collection; // 7 derniers jours
$kiosquesAttention = Collection; // Kiosques nécessitant attention
```

---

### 2. **Transactions** (`pages/transactions/index.blade.php`)

**Contrôleur** : `TransactionController@index`  
**Route** : `/transactions`

#### Fonctionnalités

✅ Liste de toutes les transactions avec pagination  
✅ Filtres avancés :
  - Par statut (valide, en_attente, annulé, échoué)
  - Par type (dépôt, retrait, transfert, paiement)
  - Par opérateur
  - Par agent
  - Par période (date_debut, date_fin)
  - Recherche par référence, nom client, téléphone

✅ Statistiques de la période filtrée :
  - Nombre total de transactions
  - Montant total
  - Commissions totales

✅ Actions disponibles :
  - Voir détail
  - Modifier (si en attente)
  - Annuler (avec raison via AJAX)

#### Variables Disponibles

```php
$transactions = LengthAwarePaginator; // Transactions paginées
$operateurs = Collection; // Pour le filtre
$agents = Collection; // Pour le filtre
$stats = [
    'total' => float,
    'count' => int,
    'commission' => float,
];
```

#### Exemple de Boucle Blade

```blade
@forelse($transactions as $transaction)
    <tr>
        <td>{{ $transaction->reference }}</td>
        <td>{{ $transaction->agent->nomComplet }}</td>
        <td>{{ $transaction->operateur->libelle }}</td>
        <td>{{ number_format($transaction->montant) }} FCFA</td>
        <td>
            @if($transaction->statut == 'valide')
                <span class="kt-badge kt-badge-success">Validée</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8">Aucune transaction trouvée</td>
    </tr>
@endforelse
```

---

### 3. **Liste des Agents** (`pages/agents/liste_agents/index.blade.php`)

**Contrôleur** : `AgentController@index`  
**Route** : `/agents`

#### Fonctionnalités

✅ Liste de tous les agents avec pagination  
✅ Filtres :
  - Par statut (actif, inactif, suspendu, en_attente)
  - Par kiosque
  - Recherche par nom, prénom, code, téléphone

✅ Affichage :
  - Photo de profil (ou avatar par défaut)
  - Nom complet + email
  - Code agent
  - Téléphone
  - Kiosque assigné (avec quartier, ville)
  - Statut avec badge coloré
  - Date d'ajout

✅ Actions disponibles :
  - Voir détail
  - Modifier
  - Supprimer (avec confirmation)

#### Variables Disponibles

```php
$agents = LengthAwarePaginator; // Agents paginés avec relations
$kiosques = Collection; // Pour le filtre
```

#### Exemple d'Affichage

```blade
@forelse($agents as $agent)
    <tr>
        <td>
            <div class="flex items-center gap-2.5">
                <img src="{{ $agent->utilisateur->photo_profil ?? asset('assets/media/avatars/blank.png') }}" 
                     class="h-9 rounded-full"/>
                <div>
                    <a href="{{ route('agents.show', $agent->id) }}">
                        {{ $agent->nomComplet }}
                    </a>
                    <span class="text-xs">{{ $agent->utilisateur->email }}</span>
                </div>
            </div>
        </td>
        <td>{{ $agent->code_agent }}</td>
        <td>{{ $agent->telephone }}</td>
        <td>
            @if($agent->kiosque)
                {{ $agent->kiosque->nom }}
                <span>{{ $agent->kiosque->quartier }}, {{ $agent->kiosque->ville }}</span>
            @else
                Non assigné
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7">Aucun agent trouvé</td>
    </tr>
@endforelse
```

---

### 4. **Soldes des Agents** (`pages/agents/solde/index.blade.php`)

**Contrôleur** : `AgentController@soldes`  
**Route** : `/agents-soldes`

#### Fonctionnalités

✅ Vue d'ensemble des soldes de tous les agents  
✅ Filtres :
  - Recherche par nom/prénom/code
  - Afficher uniquement soldes positifs (checkbox)

✅ Colonnes affichées :
  - Agent (photo + nom + code)
  - Montant initial
  - Solde espèce
  - Solde par opérateur (YAS, Flooz, Orange, etc.)
  - Total virtuel (somme de tous les opérateurs)
  - Solde total (espèce + virtuel)

✅ Ligne de total en bas du tableau  
✅ Coloration dynamique selon l'opérateur  
✅ Export et impression

#### Variables Disponibles

```php
$agents = Collection; // Agents avec relations soldes
$operateurs = Collection; // Tous les opérateurs actifs
```

#### Calculs dans la Vue

```blade
@php
    $soldeEspece = $agent->soldes->where('type', 'espece')->first();
    $soldesVirtuels = $agent->soldes->where('type', 'virtuel');
    $totalVirtuel = $soldesVirtuels->sum('montant');
    $soldeTotal = ($soldeEspece ? $soldeEspece->montant : 0) + $totalVirtuel;
@endphp
```

#### Affichage Dynamique des Opérateurs

```blade
@foreach($operateurs as $operateur)
    <th>
        @if($operateur->logo)
            <img src="{{ asset($operateur->logo) }}" class="h-5 w-5"/>
        @endif
        {{ $operateur->code }}
    </th>
@endforeach
```

---

## 🔗 Relations Eloquent Utilisées

### Dans les Vues

#### Transaction
```php
$transaction->agent // BelongsTo
$transaction->operateur // BelongsTo
$transaction->audits // HasMany
```

#### Agent
```php
$agent->utilisateur // BelongsTo
$agent->kiosque // BelongsTo
$agent->soldes // HasMany
$agent->transactions // HasMany
$agent->nomComplet // Accessor
```

#### Opérateur
```php
$operateur->transactions // HasMany
$operateur->soldes // HasMany
$operateur->logo_url // Accessor
```

#### Kiosque
```php
$kiosque->agents // HasMany
$kiosque->agentsActifs // HasMany avec scope
$kiosque->estSature() // Méthode
$kiosque->placesDisponibles() // Méthode
```

---

## 🎨 Classes CSS Utilisées

### Badges de Statut

```blade
<!-- Succès (Validé, Actif) -->
<span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">

<!-- Warning (En attente) -->
<span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">

<!-- Danger (Annulé, Suspendu) -->
<span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-danger">

<!-- Secondary (Inactif) -->
<span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-secondary">
```

### Montants

```blade
<!-- Positif (Dépôt) -->
<span class="text-success">+{{ number_format($montant) }} FCFA</span>

<!-- Négatif (Retrait) -->
<span class="text-destructive">-{{ number_format($montant) }} FCFA</span>

<!-- Primaire (Total virtuel) -->
<span class="text-primary">{{ number_format($montant) }} FCFA</span>
```

---

## 🔧 Helpers et Formatage

### Nombres

```blade
<!-- Format simple -->
{{ number_format($montant) }}

<!-- Format avec séparateurs -->
{{ number_format($montant, 0, ',', ' ') }}

<!-- Avec devise -->
{{ number_format($montant, 0, ',', ' ') }} FCFA
```

### Dates

```blade
<!-- Format court -->
{{ $date->format('d/m/Y') }}

<!-- Format long avec heure -->
{{ $date->format('d/m/Y à H:i') }}

<!-- Relative (il y a X minutes) -->
{{ $date->diffForHumans() }}
```

### Assets

```blade
<!-- Logo/Image -->
{{ asset($operateur->logo) }}

<!-- Photo avec fallback -->
{{ $agent->utilisateur->photo_profil ?? asset('assets/media/avatars/blank.png') }}
```

---

## 📱 Interactivité AJAX

### Annulation de Transaction

```javascript
function annulerTransaction(id) {
    const raison = prompt('Raison de l\'annulation :');
    if (!raison) return;

    fetch(`/transactions/${id}/annuler`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ raison })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    });
}
```

### Stats Temps Réel (Dashboard)

```javascript
// Mise à jour automatique toutes les 30 secondes
setInterval(() => {
    fetch('/api/dashboard/stats-temps-reel')
        .then(response => response.json())
        .then(data => {
            document.getElementById('transactions-jour').textContent = data.transactions_jour;
            // ...
        });
}, 30000);
```

---

## 🔍 Filtres et Recherche

### Exemple de Formulaire de Filtre

```blade
<form method="GET" action="{{ route('transactions.index') }}">
    <!-- Recherche -->
    <input name="search" value="{{ request('search') }}"/>
    
    <!-- Statut -->
    <select name="statut">
        <option value="">Tous</option>
        <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>
            Validée
        </option>
    </select>
    
    <!-- Submit -->
    <button type="submit">Filtrer</button>
    
    <!-- Reset -->
    <a href="{{ route('transactions.index') }}">Réinitialiser</a>
</form>
```

---

## 📄 Pagination

### Affichage de la Pagination

```blade
@if($transactions->hasPages())
<div class="kt-card-footer">
    <div class="flex items-center justify-between">
        <div class="text-sm text-secondary-foreground">
            Affichage de {{ $transactions->firstItem() }} à {{ $transactions->lastItem() }} 
            sur {{ $transactions->total() }} transactions
        </div>
        <div>
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endif
```

### Maintenir les Filtres dans la Pagination

```php
// Dans le contrôleur
$transactions = $query->paginate(20)->withQueryString();
```

---

## ⚙️ Configuration Requise

### Dans `layouts/demo1/base.blade.php`

Assurez-vous d'avoir :

```blade
<head>
    <!-- ... -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
```

### Messages Flash

```blade
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
```

---

## 🚀 Prochaines Étapes

### Vues À Créer

- [ ] Formulaires de création/édition (create, edit)
- [ ] Vues de détail (show)
- [ ] Page de gestion des kiosques
- [ ] Carte interactive des kiosques
- [ ] Page des utilisateurs
- [ ] Gestion des rôles et permissions

### Améliorations Possibles

- [ ] Composants Blade réutilisables
- [ ] Pagination avec Alpine.js (sans rechargement)
- [ ] Notifications toast pour les succès/erreurs
- [ ] Graphiques interactifs (Chart.js)
- [ ] Export Excel/PDF
- [ ] Filtres avancés avec date picker

---

## 📞 Support

Pour toute question :
- Voir `CONTROLLERS_README.md` pour la documentation des contrôleurs
- Voir `MIGRATIONS_README.md` pour les modèles et migrations
- Voir `nouveau_schema_mysql.md` pour le schéma complet

---

**Version** : 2.1  
**Dernière mise à jour** : 27 janvier 2026
