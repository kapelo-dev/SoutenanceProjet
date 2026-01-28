# Système de Navigation AJAX

Ce document explique comment fonctionne le système de chargement des données sans actualisation de page.

## Vue d'ensemble

Le système permet de charger les pages et les données via AJAX, améliorant ainsi l'expérience utilisateur en évitant les rechargements complets de page.

## Fonctionnalités

### 1. Navigation AJAX automatique

Tous les liens internes sont automatiquement interceptés et chargés via AJAX. Le contenu est injecté dans le conteneur `main#content` sans recharger la page.

**Comment ça marche :**
- Les clics sur les liens internes sont interceptés
- La page est chargée via AJAX
- Seul le contenu du `main` est remplacé
- L'URL est mise à jour dans le navigateur (pushState)
- Les composants JavaScript sont réinitialisés

### 2. Formulaires AJAX

Les formulaires GET (recherche, filtres) et POST sont automatiquement gérés via AJAX.

**Pour désactiver AJAX sur un formulaire :**
```html
<form data-ajax="false" method="post">
    <!-- contenu du formulaire -->
</form>
```

### 3. Pagination AJAX

Les liens de pagination sont automatiquement chargés via AJAX.

## Utilisation dans les contrôleurs

### Méthode `ajaxView()`

Utilisez la méthode `ajaxView()` au lieu de `view()` dans vos contrôleurs :

```php
public function index()
{
    $data = ['items' => Item::all()];
    
    // Au lieu de : return view('pages.items.index', $data);
    return $this->ajaxView('pages.items.index', $data);
}
```

**Avantages :**
- Détecte automatiquement les requêtes AJAX
- Retourne seulement le contenu (sans layout) pour les requêtes AJAX
- Retourne la vue complète pour les requêtes normales
- Compatible avec les navigateurs qui désactivent JavaScript

### Méthodes utilitaires

#### `ajaxResponse()`
Retourne une réponse JSON standardisée :

```php
return $this->ajaxResponse(true, ['data' => $data], 'Succès');
```

#### `ajaxSuccess()`
Réponse de succès :

```php
return $this->ajaxSuccess(['user' => $user], 'Utilisateur créé avec succès');
```

#### `ajaxError()`
Réponse d'erreur :

```php
return $this->ajaxError('Erreur de validation', $errors, 422);
```

#### `ajaxRedirect()`
Redirection pour les requêtes AJAX :

```php
return $this->ajaxRedirect(route('dashboard'), 'Redirection...');
```

## Configuration JavaScript

Le système est automatiquement initialisé via `resources/js/app.js`.

### Désactiver AJAX sur un lien

```html
<a href="/page" data-ajax="false">Lien normal</a>
```

### Événements personnalisés

Un événement `ajax-content-loaded` est déclenché après chaque chargement AJAX :

```javascript
document.addEventListener('ajax-content-loaded', function() {
    // Votre code ici
    console.log('Nouveau contenu chargé');
});
```

## Indicateur de chargement

Un indicateur de chargement s'affiche automatiquement pendant les requêtes AJAX. Il utilise les classes Tailwind CSS.

## Gestion des erreurs

En cas d'erreur, une notification d'erreur s'affiche automatiquement en haut à droite de l'écran.

## Compatibilité navigateur

- Supporte tous les navigateurs modernes
- Dégradation gracieuse : si JavaScript est désactivé, les liens fonctionnent normalement
- Supporte le bouton retour du navigateur (popstate)

## Notes importantes

1. **Token CSRF** : Le token CSRF est automatiquement ajouté aux formulaires POST
2. **Réinitialisation** : Les composants Metronic (drawers, menus, modals) sont automatiquement réinitialisés après chaque chargement
3. **Alpine.js** : Alpine.js scanne automatiquement le nouveau contenu
4. **URL** : L'URL est mise à jour sans recharger la page (pushState)

## Exemples

### Exemple 1 : Liste avec filtres

```php
public function index(Request $request)
{
    $query = Item::query();
    
    if ($request->filled('search')) {
        $query->where('name', 'like', "%{$request->search}%");
    }
    
    $items = $query->paginate(20);
    
    return $this->ajaxView('pages.items.index', compact('items'));
}
```

### Exemple 2 : Formulaire POST avec AJAX

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);
    
    $item = Item::create($validated);
    
    if ($request->ajax()) {
        return $this->ajaxSuccess(['item' => $item], 'Item créé avec succès');
    }
    
    return redirect()->route('items.index')->with('success', 'Item créé');
}
```

## Dépannage

### Le contenu ne se charge pas via AJAX

1. Vérifiez que le conteneur `main#content` existe dans votre layout
2. Vérifiez la console du navigateur pour les erreurs JavaScript
3. Assurez-vous que le script `ajax-navigation.js` est bien chargé

### Les formulaires ne fonctionnent pas

1. Vérifiez que le meta tag CSRF est présent dans le head
2. Vérifiez que le formulaire n'a pas `data-ajax="false"`
3. Vérifiez la console pour les erreurs

### Les composants ne se réinitialisent pas

1. Vérifiez que `MetronicCore` est disponible globalement
2. Vérifiez que les composants sont bien dans le contenu chargé
3. Utilisez l'événement `ajax-content-loaded` pour réinitialiser manuellement
