# Technologies Utilisées - Piscill POS

## Vue d'ensemble

Ce document présente l'ensemble des technologies, frameworks, bibliothèques et outils utilisés dans le projet Piscill POS.

---

## 🎯 Architecture Globale

**Type d'application :** Application Web Full-Stack avec Application Mobile  
**Pattern architectural :** MVC (Model-View-Controller)  
**Architecture :** Client-Serveur avec API REST

---

## 🔧 Backend

### Framework Principal
- **Laravel 12.0** - Framework PHP moderne et élégant
  - Version PHP requise : **PHP 8.2+**
  - Pattern MVC
  - Eloquent ORM pour la gestion de base de données
  - Blade comme moteur de templates
  - Middleware pour l'authentification et les permissions
  - Validation des données intégrée
  - Gestion des queues et jobs

### Langage Backend
- **PHP 8.2+**
  - Typage strict
  - Attributs PHP
  - Enums
  - Match expressions
  - Named arguments

### Packages Laravel Principaux

#### Génération de Documents
- **barryvdh/laravel-dompdf 3.1** - Génération de PDF
  - Export de rapports en PDF
  - Génération de factures
  - Documents de transactions

#### Export de Données
- **phpoffice/phpspreadsheet 5.4** - Manipulation de fichiers Excel
  - Export de transactions en Excel
  - Export de rapports financiers
  - Import/Export de données en masse

#### Développement
- **laravel/tinker 2.10.1** - REPL pour Laravel
- **laravel/pail 1.2.2** - Visualisation des logs en temps réel
- **laravel/sail 1.41** - Environnement Docker pour Laravel

#### Tests
- **PHPUnit 11.5.3** - Framework de tests unitaires
- **Mockery 1.6** - Mocking pour les tests
- **Faker 1.23** - Génération de données de test

#### Qualité de Code
- **Laravel Pint 1.13** - Formateur de code PHP
- **Collision 8.6** - Gestionnaire d'erreurs élégant

---

## 💾 Base de Données

### Système de Gestion de Base de Données
- **MySQL** (Production)
  - Version : MySQL 8.0+
  - Port : 3307
  - Moteur de stockage : InnoDB
  - Support des transactions ACID
  - Indexes optimisés pour les performances

### ORM et Migrations
- **Eloquent ORM** (intégré à Laravel)
  - Relations entre modèles (One-to-One, One-to-Many, Many-to-Many, Polymorphic)
  - Soft Deletes
  - Query Builder
  - Eager Loading pour optimisation
  - Accessors et Mutators

### Gestion des Queues
- **Queue Driver : Database**
  - Jobs asynchrones
  - Retry automatique en cas d'échec
  - Priorités de jobs

### Cache
- **Cache Driver : Database**
  - Mise en cache des requêtes fréquentes
  - Amélioration des performances

---

## 🎨 Frontend

### Framework CSS
- **Tailwind CSS 4.1.12**
  - Utility-first CSS framework
  - Design responsive
  - Dark mode support
  - Customisation via configuration

#### Plugins Tailwind
- **@tailwindcss/forms 0.5.7** - Styles pour les formulaires
- **@tailwindcss/typography 0.5.10** - Styles typographiques
- **@tailwindcss/vite 4.1.12** - Intégration Vite
- **@tailwindcss/postcss 4.1.12** - PostCSS plugin

### Framework JavaScript
- **Alpine.js 3.13.3**
  - Framework JavaScript léger et réactif
  - Interactivité côté client
  - Composants réactifs
  - Directives déclaratives (x-data, x-show, x-if, etc.)

### Bibliothèques JavaScript

#### HTTP Client
- **Axios 1.6.4**
  - Requêtes HTTP asynchrones
  - Intercepteurs de requêtes/réponses
  - Communication avec l'API backend

#### Visualisation de Données
- **ApexCharts** (via CDN)
  - Graphiques interactifs
  - Statistiques du dashboard
  - Rapports visuels

#### Cartographie
- **Leaflet.js** (via CDN)
  - Cartes interactives
  - Visualisation géographique des kiosques
  - Carte de performance mensuelle

### Moteur de Templates
- **Blade** (intégré à Laravel)
  - Templates côté serveur
  - Héritage de layouts
  - Composants réutilisables
  - Directives personnalisées

---

## 🛠️ Outils de Build et Développement

### Build Tool
- **Vite 7.3**
  - Build ultra-rapide
  - Hot Module Replacement (HMR)
  - Optimisation de production
  - Code splitting automatique

### Plugin Laravel
- **laravel-vite-plugin 2.0**
  - Intégration Vite avec Laravel
  - Gestion des assets
  - Versioning automatique

### PostCSS
- **PostCSS 8.4.32**
  - Transformation CSS
  - Autoprefixer
  - Minification

### Formatage de Code
- **Blade Formatter 1.42.2**
  - Formatage automatique des templates Blade
  - Cohérence du code

---

## 🎨 UI/UX Framework

### Theme
- **Metronic Tailwind**
  - Theme admin moderne
  - Composants UI pré-construits
  - Design system cohérent
  - Icons : Keenicons

### Composants UI
- **KTComponents** (Metronic)
  - Modals
  - Dropdowns
  - Tooltips
  - Accordions
  - Tabs
  - Datatables

---

## 🔐 Sécurité

### Authentification
- **Laravel Authentication** (natif)
  - Sessions sécurisées
  - CSRF Protection
  - Password Hashing (Bcrypt)
  - Remember Me functionality

### Autorisation
- **Système de Permissions Personnalisé**
  - Profils utilisateurs (Super Admin, Admin, Superviseur, Comptable, Agent)
  - Permissions granulaires par route
  - Middleware de vérification des permissions
  - Gestion des liens menu par profil

### API (pour Application Mobile)
- **JWT (JSON Web Token)** - Authentification API
- **API REST** - Communication backend-mobile

---

## 📱 Application Mobile

### Type
- **Service Automatisé**
  - Création automatique de transactions (dépôt/retrait)
  - Communication via API REST
  - Envoi de données au backend

### Technologies (supposées)
- **API REST Laravel**
- **JSON** pour l'échange de données
- **HTTP/HTTPS** pour la communication

---

## 📊 Fonctionnalités Avancées

### Système de Logs
- **SystemLog** (personnalisé)
  - Logging polymorphique
  - Traçabilité des actions utilisateurs
  - Audit trail complet
  - Export des logs

### Rapports et Exports
- **DomPDF** - Génération PDF
- **PhpSpreadsheet** - Export Excel
- **ApexCharts** - Graphiques interactifs

### Gestion des Fichiers
- **Laravel Storage**
  - Upload de photos de profil
  - Stockage local (public disk)
  - Gestion des médias

---

## 🌐 Navigation et UX

### Navigation AJAX
- **Système personnalisé** (`ajax-navigation.js`)
  - Chargement dynamique des pages
  - Historique de navigation (pushState)
  - Amélioration des performances
  - Gestion des scripts inline

### Permissions Dynamiques
- **Menu Permissions** (`menu-permissions.js`)
  - Affichage conditionnel des menus
  - Vérification des permissions côté client
  - Synchronisation avec le backend

### Initialisation de Page
- **Page Init** (`page-init.js`)
  - Initialisation des composants UI
  - Gestion des événements
  - Configuration des plugins

---

## 🗺️ Cartographie

### Bibliothèque
- **Leaflet.js**
  - Cartes interactives OpenStreetMap
  - Markers personnalisés
  - Popups informatifs
  - Clustering de points

### Implémentations
- **Dashboard Month Map** (`dashboard-month-map.js`)
  - Carte de performance mensuelle des kiosques
  - Visualisation des montants par kiosque
  - Cercles proportionnels aux montants

- **Kiosques Map** (`kiosques-map.js`)
  - Carte de tous les kiosques
  - Informations détaillées par kiosque
  - Navigation géographique

---

## 📦 Gestionnaires de Paquets

### Backend
- **Composer**
  - Gestion des dépendances PHP
  - Autoloading PSR-4
  - Scripts personnalisés

### Frontend
- **npm (Node Package Manager)**
  - Gestion des dépendances JavaScript
  - Scripts de build et développement

---

## 🚀 Environnement de Développement

### Serveur de Développement
- **PHP Built-in Server** (`php artisan serve`)
- **Vite Dev Server** (HMR)

### Outils de Développement
- **Laravel Pail** - Logs en temps réel
- **Laravel Tinker** - REPL interactif
- **Concurrently** - Exécution parallèle de commandes

### Conteneurisation (optionnel)
- **Laravel Sail** - Docker pour Laravel
  - MySQL container
  - Redis container (si utilisé)
  - Mailhog pour les emails de test

---

## 📝 Standards et Conventions

### Code Style
- **PSR-12** - Standard PHP
- **Laravel Pint** - Formatage automatique
- **Blade Formatter** - Formatage des templates

### Architecture
- **MVC** - Model-View-Controller
- **Repository Pattern** (partiel)
- **Service Layer** (pour logique métier complexe)

### Naming Conventions
- **Camel Case** - Variables et méthodes PHP
- **Snake Case** - Colonnes de base de données
- **Kebab Case** - URLs et routes
- **Pascal Case** - Classes PHP

---

## 🔄 Intégration Continue (potentiel)

### Tests
- **PHPUnit** - Tests unitaires et fonctionnels
- **Laravel Dusk** (potentiel) - Tests E2E

### Qualité de Code
- **Laravel Pint** - Vérification du style
- **PHPStan** (potentiel) - Analyse statique

---

## 📚 Documentation

### Formats
- **Markdown** - Documentation projet
- **PHPDoc** - Documentation du code
- **Blade Comments** - Documentation des templates

### Fichiers de Documentation
- `README.md` - Vue d'ensemble du projet
- `CONTROLLERS_README.md` - Documentation des contrôleurs
- `ANALYSE_DESCRIPTIVE.md` - Analyse du système
- `SYSTEM_LOGS_README.md` - Documentation du système de logs
- `DIAGRAMMES_CAS_UTILISATION.md` - Cas d'utilisation
- `desc_schema` - Schéma de base de données SQLite
- `nouveau_schema_mysql.md` - Schéma de base de données MySQL

---

## 🌍 Internationalisation

### Langue
- **Français** - Langue principale de l'application
- Support potentiel pour d'autres langues via Laravel Localization

---

## 📊 Monitoring et Performance

### Logging
- **Laravel Log** - Système de logs natif
- **SystemLog** - Logs personnalisés d'audit
- **Channels** : stack, single, daily, slack, etc.

### Performance
- **Query Optimization** - Eager loading, indexes
- **Caching** - Database cache driver
- **Asset Optimization** - Vite build, minification

---

## 🔗 APIs et Intégrations

### API Interne
- **RESTful API** - Pour l'application mobile
- **JSON** - Format d'échange de données

### Opérateurs Mobile Money
- Intégration avec différents opérateurs (Orange Money, MTN, Moov, etc.)
- Gestion des types d'opérations par opérateur

---

## 📱 Responsive Design

### Approche
- **Mobile-First** - Design adaptatif
- **Tailwind Breakpoints** - sm, md, lg, xl, 2xl
- **Flexbox et Grid** - Layouts modernes

---

## 🎯 Résumé des Technologies Clés

| Catégorie | Technologies |
|-----------|-------------|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Base de Données** | MySQL 8.0+ |
| **Frontend CSS** | Tailwind CSS 4.1.12 |
| **Frontend JS** | Alpine.js 3.13.3, Axios 1.6.4 |
| **Build Tool** | Vite 7.3 |
| **UI Framework** | Metronic Tailwind |
| **Charts** | ApexCharts |
| **Maps** | Leaflet.js |
| **PDF** | DomPDF 3.1 |
| **Excel** | PhpSpreadsheet 5.4 |
| **Templates** | Blade (Laravel) |
| **ORM** | Eloquent |
| **Tests** | PHPUnit 11.5.3 |
| **Package Managers** | Composer, npm |

---

## 🎓 Compétences Techniques Démontrées

1. **Développement Backend** - Laravel, PHP, API REST
2. **Développement Frontend** - Tailwind CSS, Alpine.js, JavaScript
3. **Base de Données** - MySQL, Eloquent ORM, Migrations
4. **Architecture** - MVC, Design Patterns, Clean Code
5. **Sécurité** - Authentication, Authorization, CSRF Protection
6. **Performance** - Caching, Query Optimization, Asset Bundling
7. **Intégration** - API REST, JSON, HTTP
8. **Tests** - PHPUnit, Tests unitaires et fonctionnels
9. **DevOps** - Vite, Composer, npm, Docker (Sail)
10. **Documentation** - Markdown, PHPDoc, Diagrammes UML

---

**Version:** 1.0  
**Dernière mise à jour:** 26 mars 2026  
**Auteur:** Système Piscill POS
