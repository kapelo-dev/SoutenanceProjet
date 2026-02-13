# Cahier des charges — Plateforme de gestion PDV Connect

**Version :** 1.0  
**Date :** 1er février 2026  
**Statut :** Document de référence technique et fonctionnel

---

## 1. Contexte et objectifs

### 1.1 Contexte

La plateforme **PDV Connect** est une solution de gestion pour points de vente (PDV) et réseaux d’agents, centrée sur les **transactions Mobile Money**, la **gestion des agents et kiosques**, la **trésorerie** et la **paie**. Elle vise à centraliser les opérations, suivre les performances et assurer la traçabilité des flux financiers.

### 1.2 Objectifs principaux

- Centraliser la saisie et le suivi des transactions (Mobile Money, opérations en agence).
- Gérer les agents, leurs soldes et leur répartition sur les kiosques.
- Piloter la trésorerie et la paie (paramètres de salaire, formules, mouvements).
- Offrir un tableau de bord et des rapports pour le pilotage.
- Sécuriser l’accès par rôles et permissions (RBAC).
- Permettre l’alimentation automatique des transactions via SMS (service Android dédié).

### 1.3 Périmètre du projet

| Composant | Description |
|-----------|-------------|
| **Application web (back-office)** | Interface d’administration (Laravel), utilisée par les gestionnaires, superviseurs et administrateurs. |
| **Application Android (service)** | Service en arrière-plan, sans interface utilisateur (hors paramètres de confidentialité), chargé de lire les SMS de transaction et d’envoyer les données vers l’API de la plateforme. |

---

## 2. Application web — Fonctionnalités détaillées

### 2.1 Authentification et sécurité

| Fonctionnalité | Description |
|----------------|-------------|
| Connexion / Déconnexion | Authentification par email et mot de passe (session Laravel). |
| Changement de mot de passe obligatoire | À la première connexion, redirection vers un formulaire de changement de mot de passe avant accès au reste de l’application. |
| Contrôle d’accès (RBAC) | Gestion des **profils** (rôles) et des **permissions** par lien (menu / écran). Les menus du sidebar sont affichés ou masqués selon les droits de l’utilisateur. |
| Protection des routes | Middleware d’authentification et de changement de mot de passe ; vérification des permissions côté serveur et côté client (menu dynamique). |

### 2.2 Tableau de bord

- Indicateurs en temps réel (statistiques, KPIs).
- Graphiques (transactions, évolution).
- Carte de performance (ex. carte mensuelle par zone / kiosque).
- Statistiques par opérateur Mobile Money.
- Appels API dédiés : stats temps réel, graphiques, stats par opérateur, carte performance.

### 2.3 Transactions

- Liste et détail des transactions.
- Création, modification, annulation de transactions.
- Filtres et recherche.
- Export des données en PDF (avec logo PDV Connect et logos des opérateurs).
- Statistiques sur les transactions.
- Typologie des opérations (types configurables, lien éventuel avec opérateur).

### 2.4 Agents

- **Liste des agents** : consultation, création, édition, affectation aux kiosques.
- **Soldes des agents** : suivi des soldes, mise à jour manuelle si applicable.
- **Dashboard agent** : vue dédiée pour un agent connecté (mon dashboard).
- Gestion du statut (actif / inactif), changement de statut.
- Association agent ↔ kiosque(s) (assignation / retrait).
- Historique des soldes (API).
- Export PDF de la liste des agents (avec logo PDV Connect).
- Export PDF des soldes des agents (avec logo PDV Connect).

### 2.5 Kiosques

- **Liste des kiosques** : CRUD, localisation, informations de contact.
- **Carte des kiosques** : visualisation géographique (carte interactive).
- API proximité et données carte pour alimentation de la carte.
- Assignation / retrait d’agents sur un kiosque.

### 2.6 Utilisateurs

- Gestion des comptes utilisateurs (CRUD).
- Association utilisateur ↔ profil(s).
- Gestion du statut (actif / inactif).
- Réinitialisation du mot de passe.
- API : liens accessibles par utilisateur (pour menu dynamique).

### 2.7 Opérateurs Mobile Money

- Gestion des opérateurs (CRUD).
- Activation / désactivation (toggle statut).
- Statistiques par opérateur (API).
- Utilisation dans les transactions et les rapports.

### 2.8 Opérations en agence

- Saisie et suivi des opérations effectuées en agence.
- Formulaire dédié (sélection agent, type d’opération, opérateur, etc.).
- Stockage et consultation des opérations.

### 2.9 Gestion d’entreprise

- **Onglet Salaires**  
  - Génération des salaires par période (date début / fin, choix des agents).  
  - Calcul selon paramètres (fixe, commission, mixte).  
  - Liste des salaires, statut (en attente / payé), paiement (date, mode).  
  - Modal « Payer le salaire ».
- **Onglet Paramètres de salaire**  
  - Paramètres (nom, type : fixe / commission / mixte, montant fixe, taux commission, base de calcul).  
  - Formule personnalisée (constructeur dynamique + champ JSON pour conditions avancées).  
  - Conditions optionnelles en JSON.  
  - CRUD paramètres, activation / désactivation.
- **Onglet Trésorerie**  
  - Mouvements de trésorerie (entrées / sorties).  
  - Filtrage par période (date début / fin).  
  - Statistiques : entrées, sorties, solde.  
  - Liste paginée des mouvements.

### 2.10 Rapports

- Page dédiée aux rapports (consultation, filtres avancés, statistiques par opérateur, top agents).
- Données issues des transactions, agents, kiosques, trésorerie, salaires.
- Statistiques globales et par opérateur.
- Top agents par montant et nombre de transactions.

### 2.11 Configuration — Rôles et permissions

- **Gestion des rôles (profils)** : CRUD des profils, attribution des droits par lien.
- **Gestion des permissions** : association profil ↔ liens (écrans / routes), activation/désactivation par case à cocher.
- **Gestion des routes (liens)** : catalogue des routes/menus (liens), visibilité, ordre, hiérarchie (parent / enfants).
- Modèle de données : tables `profils`, `liens`, `profil_liens`, `user_profils` (utilisateurs multi-profils).

### 2.12 Pages publiques (sans authentification)

- Documentation.
- FAQ.
- Support.
- Licence.

### 2.13 Expérience utilisateur (UX) — Web

- Navigation AJAX : chargement des pages dans le contenu principal sans rechargement complet, avec mise à jour de l’URL (history).
- Menu latéral (sidebar) dynamique selon permissions (chargement des droits via API, masquage des entrées non autorisées).
- Sidebar repliable ; au survol en mode collapse, la largeur du sidebar s’étend et le contenu se décale (pas de chevauchement).
- Formulaires soumis en AJAX (ex. modals paramètres salaire, génération salaires) avec gestion du token CSRF et fermeture correcte des modals/backdrop après succès.
- Thème responsive (Metronic / Tailwind), cartes interactives (Leaflet), graphiques (ApexCharts selon assets).

---

## 3. Application Android — Service de transfert SMS vers l’API

### 3.1 Rôle de l’application

L’application Android est un **service d’acquisition de données** : elle n’a **pas d’interface métier** (pas d’écrans de gestion des transactions, des agents, etc.). Elle se limite à :

1. Lire les **SMS entrants** (un numéro précis ou une conversation précise).
2. Filtrer et parser les SMS considérés comme des **notifications de transaction** (Mobile Money ou autre).
3. Envoyer les données structurées vers un **endpoint API** de la plateforme web (création ou enregistrement de transaction).

### 3.2 Fonctionnalités techniques

| Fonctionnalité | Description |
|----------------|-------------|
| Écoute des SMS | Réception des SMS (BroadcastReceiver ou équivalent) sur l’appareil où l’application est installée. |
| Filtrage par expéditeur | Prise en compte des SMS provenant d’un **numéro précis** (ex. shortcode opérateur). |
| Ou filtrage par conversation | Prise en compte des SMS issus d’une **conversation précise** (thread / numéro). |
| Parsing des SMS | Extraction des champs utiles (montant, référence, date, type, etc.) selon un format défini (regex ou protocole documenté). |
| Envoi vers l’API | Appel HTTP (POST) vers l’endpoint API de la plateforme (ex. `POST /api/transactions` ou endpoint dédié « ingestion SMS ») avec authentification (token, API key, etc.). |
| Gestion des erreurs | Retry, file d’attente locale si hors ligne, log des échecs. |
| Pas d’interface métier | Aucun écran de liste de transactions, de saisie manuelle, de tableau de bord. |

### 3.3 Interface utilisateur — Paramètres de confidentialité

L’application dispose **uniquement** d’une interface minimale pour :

- **Paramètres de confidentialité** :  
  - Explication du fait que l’app lit les SMS pour extraire les transactions.  
  - Consentement utilisateur (case à cocher / bouton d’acceptation).  
  - Option d’activation / désactivation du service.  
  - Information sur les données envoyées (serveur, chiffrement).  
  - Politique de confidentialité (texte ou lien).  

Aucun autre écran métier (liste de transactions, statistiques, etc.) n’est prévu dans le périmètre.

### 3.4 Contraintes techniques Android

- Permission **Lire les SMS** (et éventuellement **Recevoir les SMS**) selon le niveau d’API.
- Service en arrière-plan (Foreground Service recommandé pour Android 8+) pour fiabilité.
- Gestion des restrictions d’économie de batterie et des politiques de restriction des applications en arrière-plan (Doze, App Standby).
- Sécurité : communication avec l’API en HTTPS ; stockage sécurisé des identifiants / token (ex. Keystore, EncryptedSharedPreferences).
- Pas de conservation inutile du contenu des SMS : après envoi réussi, les SMS peuvent être marqués lus ou ne pas être stockés en clair au-delà du traitement.

### 3.5 API côté plateforme web

- Exposer un **endpoint API** dédié à l’ingestion des transactions issues des SMS (ex. `POST /api/transactions/from-sms` ou `POST /api/integrations/sms-transactions`).
- Authentification de l’application Android (token applicatif, API key, ou OAuth2 client credentials) pour éviter les abus.
- Payload attendu : champs normalisés (montant, devise, référence, date/heure, type, opérateur, numéro, etc.) et éventuellement identifiant du device ou de l’agent associé.
- Réponse : statut (201 Created, 400 Bad Request, 401 Unauthorized, 422 Validation) et identifiant de la transaction créée si applicable.

---

## 4. Technologies utilisées et recommandées

### 4.1 Application web (existante)

| Couche | Technologie |
|--------|-------------|
| Back-end | **PHP 8.2+**, **Laravel 12** |
| Base de données | **MySQL** ou **PostgreSQL** (migrations Laravel, Eloquent ORM) |
| Front-end | **HTML5**, **Blade**, **Tailwind CSS 4**, **Alpine.js**, **Vite 7** |
| Thème / UI | **Metronic** (Tailwind) — composants (modals, sidebar, formulaires, boutons) |
| Cartes | **Leaflet** (carte kiosques, carte performance dashboard) |
| Graphiques | **ApexCharts** (assets du thème) |
| Build / Outils | **Composer**, **npm**, **Vite** |
| Conteneurisation | **Docker** (docker-compose pour environnement de dev/déploiement) |
| Authentification | Session Laravel, middleware personnalisé (changement mot de passe obligatoire) |
| API (internes / futures) | Routes API Laravel (REST), éventuellement **Laravel Sanctum** pour tokens API |

### 4.2 Application Android (à développer)

| Composant | Technologie recommandée |
|-----------|-------------------------|
| Langage | **Kotlin** |
| Min SDK | **API 24** (Android 7.0) ou **API 26** (Android 8.0) selon contraintes foreground service |
| Architecture | **MVVM** ou **MVI** (même avec peu d’écrans, pour clarté et tests) |
| Écoute SMS | **BroadcastReceiver** (SMS_RECEIVED), **ContentObserver** sur `content://sms` si besoin lecture conversation |
| Service | **WorkManager** et/ou **Foreground Service** (pour fiabilité en arrière-plan) |
| Réseau | **Retrofit** + **OkHttp** (HTTPS, intercepteurs pour auth, logging) |
| Sécurité | **Android Keystore**, **EncryptedSharedPreferences** (credentials, token API) |
| UI minimale | **Jetpack Compose** ou **XML Layouts** (écran paramètres / confidentialité uniquement) |
| Gestion des données locales | **Room** ou simple **SharedPreferences** pour préférences (activation service, URL endpoint, etc.) |
| Build | **Gradle** (Kotlin DSL), **Android Studio** |

### 4.3 API et intégration

- **Protocole** : REST (JSON).
- **Sécurité** : HTTPS obligatoire ; authentification API par token ou clé pour l’app Android.
- **Documentation** : OpenAPI (Swagger) ou document interne pour l’endpoint d’ingestion SMS.

---

## 5. Modèle de données (résumé)

- **Utilisateurs** : compte (email, mot de passe, etc.), liaison aux **profils** via `user_profils`.
- **Profils** : rôles (ex. Super Admin, Gestionnaire, Agent).
- **Liens** : routes / menus (libellé, route, url, icône, parent_id, ordre, visible).
- **profil_liens** : permissions (quel profil a accès à quel lien).
- **Agents** : liaison avec utilisateur, statut, kiosque(s).
- **Kiosques** : informations, localisation, agents assignés.
- **Transactions** : montant, type, opérateur, agent, date, statut, etc.
- **Opérateurs** : opérateurs Mobile Money.
- **Paramètres de salaire** : type (fixe/commission/mixte), formule, conditions JSON.
- **Salaires** : période, agent, paramètre, montants (fixe, commission, total), statut, date paiement.
- **Mouvements de trésorerie** : type (entrée/sortie), montant, date, lien éventuel avec salaire/agent.
- **Types d’opération** : typologie des transactions (requiert opérateur ou non).
- **Soldes** : historique des soldes par agent.
- **Audits** : traçabilité des actions sensibles (selon schéma existant).

---

## 6. Contraintes et hypothèses

### 6.1 Contraintes

- Conformité RGPD / loi locale : collecte et traitement des données personnelles (utilisateurs, agents, transactions) avec base légale et information des personnes.
- Données financières : intégrité, traçabilité, droits d’accès stricts (RBAC).
- Disponibilité du service Android : dépendance à la réception SMS et à la connectivité réseau pour l’envoi vers l’API.
- Android : respect des politiques Google Play (permissions SMS, arrière-plan, politique de confidentialité).

### 6.2 Hypothèses

- La plateforme web est hébergée sur un serveur accessible depuis Internet (ou VPN) pour que l’app Android puisse appeler l’API.
- Le format des SMS de transaction (opérateurs) est connu ou documenté pour le parsing.
- Un seul endpoint API (ou un nombre limité) suffit pour l’ingestion des transactions SMS ; les règles métier (doublons, validation) sont gérées côté serveur.
- L’application Android est installée sur un appareil dédié (ou un numéro dédié) pour recevoir les SMS de transaction.

---

## 7. Livrables attendus

### 7.1 Application web

- Code source (Laravel, front-end) versionné et déployable.
- Base de données : migrations, seeders (profils, liens, utilisateur admin, types d’opération).
- Documentation technique (README, déploiement, variables d’environnement).
- Définition et implémentation de l’endpoint API d’ingestion des transactions (pour l’app Android).

### 7.2 Application Android

- Code source (Kotlin) versionné.
- Service d’écoute SMS et d’envoi vers l’API.
- Écran unique (ou minimal) : paramètres de confidentialité et activation/désactivation du service.
- Documentation : configuration (URL API, auth), permissions, politique de confidentialité.
- Package APK / AAB pour distribution (tests internes ou Play Store).

### 7.3 Cahier des charges

- Présent document, à faire évoluer en cas de changements de périmètre ou de choix techniques.

---

## 8. Glossaire

| Terme | Définition |
|-------|------------|
| RBAC | Role-Based Access Control — contrôle d’accès par rôles (profils) et permissions (liens). |
| PDV | Point de vente. |
| Mobile Money | Services de paiement / transfert via mobile (opérateurs télécom). |
| Lien | Élément de menu (route ou URL) auquel un profil peut être autorisé. |
| Profil | Rôle métier (ex. Super Admin, Gestionnaire) regroupant des permissions. |
| Endpoint API | URL d’un service REST (ex. POST /api/transactions/from-sms). |

---

*Document rédigé à partir de l’analyse de la codebase et des échanges fonctionnels. À valider par le maître d’ouvrage et à mettre à jour en cas d’évolution du périmètre.*
