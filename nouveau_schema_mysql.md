# Nouveau Schéma de Base de Données MySQL - Piscill POS

## Vue d'ensemble

Ce document décrit le nouveau schéma complet de la base de données MySQL pour l'application Piscill POS. Le schéma inclut :
- Gestion dynamique des opérateurs de mobile money
- Gestion des kiosques avec géolocalisation (latitude/longitude)
- Association agents-kiosques
- Système de gestion des routes, liens et permissions
- Support complet pour Laravel avec migrations
- Optimisation MySQL avec index appropriés

**Version** : 2.2  
**Date de mise à jour** : 26 mars 2026  
**Base de données** : MySQL 8.0+  
**ORM** : Laravel Eloquent  
**Nouveautés v2.2** : Ajout de la table system_logs pour le logging complet des actions système  
**Nouveautés v2.1** : Ajout gestion des kiosques avec géolocalisation GPS

---

## Diagramme des Relations

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│utilisateurs │────┬───►│ user_profils │◄────────┤   profils   │
└──────┬──────┘    │    └──────────────┘         └──────┬──────┘
       │ 1         │                                     │ 1
       │           │                                     │
       │ 0..1      │                                     │ N
       ▼           │                              ┌──────▼──────┐
  ┌─────────┐     │          1                   │profil_liens │
  │  agents │◄────┼──────────────┐               └──────┬──────┘
  └────┬────┘     │              │                      │ N
       │ 1        │         ┌────┴──────┐              │
       │          │ N       │ kiosques  │              ▼
       │ N        │         └───────────┘       ┌───────────┐
       ▼          │                              │   liens   │
┌──────────────┐  │        ┌──────────────┐    └───────────┘
│transactions  │  └───────►│  operateurs  │
└──────┬───────┘     N     └──────┬───────┘
       │ 1                         │ 1
       │                           │
       │ 0..1                      │ N
       ▼                           ▼
┌─────────────┐             ┌─────────────┐
│   audits    │             │   soldes    │
└─────────────┘             └─────────────┘
```

---

## Tables Principales

### 1. **operateurs** - Réseaux de Mobile Money

Table pour gérer dynamiquement les opérateurs de mobile money (YAS, Flooz, Orange Money, etc.)

#### Structure SQL

```sql
CREATE TABLE operateurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Code unique ex: YAS, FLOOZ, ORANGE',
    libelle VARCHAR(100) NOT NULL COMMENT 'Nom complet ex: Mixx by YAS, Flooz, Orange Money',
    logo VARCHAR(255) NULL COMMENT 'Chemin vers le logo',
    couleur VARCHAR(7) NULL COMMENT 'Couleur hexadécimale ex: #FF5733',
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    ordre INT DEFAULT 0 COMMENT 'Ordre d\'affichage',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_statut (statut),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Identifiant unique |
| `code` | VARCHAR(50) | UNIQUE, NOT NULL | Code unique (ex: YAS, FLOOZ) |
| `libelle` | VARCHAR(100) | NOT NULL | Nom complet de l'opérateur |
| `logo` | VARCHAR(255) | NULL | Chemin vers le fichier logo |
| `couleur` | VARCHAR(7) | NULL | Code couleur hexadécimal |
| `statut` | ENUM | DEFAULT 'actif' | Statut de l'opérateur |
| `ordre` | INT | DEFAULT 0 | Ordre d'affichage |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique (soft delete) |

#### Données d'exemple

```sql
INSERT INTO operateurs (code, libelle, logo, couleur, ordre) VALUES
('YAS', 'Mixx by YAS', 'logos/operateurs/yas.png', '#FF6B00', 1),
('FLOOZ', 'Flooz', 'logos/operateurs/flooz.png', '#00A651', 2),
('ORANGE', 'Orange Money', 'logos/operateurs/orange.png', '#FF7900', 3);
```

---

### 2. **utilisateurs** - Comptes Utilisateurs

Table principale pour gérer tous les utilisateurs du système.

#### Structure SQL

```sql
CREATE TABLE utilisateurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL COMMENT 'UUID v4',
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NULL,
    photo_profil VARCHAR(255) NULL,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    dernier_connexion TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_uid (uid),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique auto-incrémenté |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 pour référence externe |
| `nom` | VARCHAR(100) | NOT NULL | Nom de famille |
| `prenom` | VARCHAR(100) | NOT NULL | Prénom |
| `email` | VARCHAR(100) | UNIQUE, NOT NULL | Email (identifiant de connexion) |
| `mot_de_passe` | VARCHAR(255) | NOT NULL | Mot de passe hashé (bcrypt) |
| `telephone` | VARCHAR(20) | NULL | Numéro de téléphone |
| `photo_profil` | VARCHAR(255) | NULL | Chemin vers la photo de profil |
| `statut` | ENUM | DEFAULT 'actif' | Statut du compte |
| `dernier_connexion` | TIMESTAMP | NULL | Date de la dernière connexion |
| `email_verified_at` | TIMESTAMP | NULL | Date de vérification de l'email |
| `remember_token` | VARCHAR(100) | NULL | Token de session Laravel |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

---

### 3. **profils** - Rôles/Profils Utilisateurs

Table pour gérer les différents profils/rôles d'utilisateurs (Admin, Agent, Superviseur, etc.).

#### Structure SQL

```sql
CREATE TABLE profils (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL COMMENT 'Ex: Admin, Agent, Superviseur',
    description TEXT NULL,
    niveau INT DEFAULT 0 COMMENT 'Niveau hiérarchique',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `libelle` | VARCHAR(100) | NOT NULL | Nom du profil |
| `description` | TEXT | NULL | Description du profil |
| `niveau` | INT | DEFAULT 0 | Niveau hiérarchique (0=plus élevé) |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

#### Données d'exemple

```sql
INSERT INTO profils (libelle, description, niveau) VALUES
('Super Admin', 'Accès complet au système', 0),
('Admin', 'Administrateur de l\'application', 1),
('Superviseur', 'Supervision des agents', 2),
('Agent', 'Agent de terrain', 3),
('Comptable', 'Gestion comptable', 2);
```

---

### 4. **liens** - Routes et Menus

Table pour gérer les liens, routes et structure de menu de l'application.

#### Structure SQL

```sql
CREATE TABLE liens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL COMMENT 'Nom du lien/menu',
    route VARCHAR(100) NULL COMMENT 'Route Laravel ex: dashboard, agents.index',
    url VARCHAR(255) NULL COMMENT 'URL si route externe',
    icone VARCHAR(50) NULL COMMENT 'Classe icône ex: ki-filled ki-home',
    parent_id BIGINT UNSIGNED NULL COMMENT 'Pour les sous-menus',
    ordre INT DEFAULT 0,
    visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES liens(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id),
    INDEX idx_ordre (ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `libelle` | VARCHAR(100) | NOT NULL | Nom du lien/menu |
| `route` | VARCHAR(100) | NULL | Route Laravel |
| `url` | VARCHAR(255) | NULL | URL complète (si externe) |
| `icone` | VARCHAR(50) | NULL | Classe CSS de l'icône |
| `parent_id` | BIGINT UNSIGNED | FK → liens.id, NULL | ID du menu parent |
| `ordre` | INT | DEFAULT 0 | Ordre d'affichage |
| `visible` | BOOLEAN | DEFAULT TRUE | Visibilité du lien |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

#### Données d'exemple

```sql
-- Menus principaux
INSERT INTO liens (libelle, route, icone, ordre) VALUES
('Dashboard', 'dashboard', 'ki-filled ki-element-11', 1),
('Transactions', 'transactions.index', 'ki-filled ki-chart-line', 2),
('Agents', NULL, 'ki-filled ki-people', 3),
('Utilisateurs', 'utilisateurs.index', 'ki-filled ki-profile-user', 4);

-- Sous-menus pour Agents
INSERT INTO liens (libelle, route, parent_id, ordre) VALUES
('Liste des Agents', 'agents.index', 3, 1),
('Soldes des Agents', 'agents.soldes', 3, 2);
```

---

### 5. **user_profils** - Association Utilisateur-Profil

Table pivot pour lier les utilisateurs aux profils (un utilisateur peut avoir plusieurs profils).

#### Structure SQL

```sql
CREATE TABLE user_profils (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    profil_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (profil_id) REFERENCES profils(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_profil (user_id, profil_id),
    INDEX idx_user (user_id),
    INDEX idx_profil (profil_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `user_id` | BIGINT UNSIGNED | FK → utilisateurs.id, NOT NULL | ID utilisateur |
| `profil_id` | BIGINT UNSIGNED | FK → profils.id, NOT NULL | ID profil |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

---

### 6. **profil_liens** - Permissions Profil-Lien

Table pivot pour gérer les permissions (quels profils ont accès à quels liens).

#### Structure SQL

```sql
CREATE TABLE profil_liens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profil_id BIGINT UNSIGNED NOT NULL,
    lien_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (profil_id) REFERENCES profils(id) ON DELETE CASCADE,
    FOREIGN KEY (lien_id) REFERENCES liens(id) ON DELETE CASCADE,
    UNIQUE KEY unique_profil_lien (profil_id, lien_id),
    INDEX idx_profil (profil_id),
    INDEX idx_lien (lien_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `profil_id` | BIGINT UNSIGNED | FK → profils.id, NOT NULL | ID profil |
| `lien_id` | BIGINT UNSIGNED | FK → liens.id, NOT NULL | ID lien |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

---

### 7. **kiosques** - Points de Vente/Kiosques

Table pour gérer les kiosques (points de vente) où opèrent les agents, avec géolocalisation.

#### Structure SQL

```sql
CREATE TABLE kiosques (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL COMMENT 'UUID v4',
    code VARCHAR(50) UNIQUE NULL COMMENT 'Code du kiosque ex: K001, K002',
    nom VARCHAR(150) NOT NULL COMMENT 'Nom du kiosque',
    adresse TEXT NULL COMMENT 'Adresse complète',
    quartier VARCHAR(100) NULL COMMENT 'Quartier/Zone',
    ville VARCHAR(100) NULL COMMENT 'Ville',
    latitude DECIMAL(10,8) NULL COMMENT 'Latitude GPS',
    longitude DECIMAL(11,8) NULL COMMENT 'Longitude GPS',
    telephone VARCHAR(20) NULL COMMENT 'Téléphone du kiosque',
    photo VARCHAR(255) NULL COMMENT 'Photo du kiosque',
    type ENUM('fixe', 'mobile') DEFAULT 'fixe' COMMENT 'Type de kiosque',
    statut ENUM('actif', 'inactif', 'en_travaux') DEFAULT 'actif',
    capacite_agents INT DEFAULT 1 COMMENT 'Nombre d\'agents max',
    horaire_ouverture TIME NULL COMMENT 'Heure d\'ouverture',
    horaire_fermeture TIME NULL COMMENT 'Heure de fermeture',
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_code (code),
    INDEX idx_statut (statut),
    INDEX idx_ville (ville),
    INDEX idx_quartier (quartier),
    INDEX idx_localisation (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 |
| `code` | VARCHAR(50) | UNIQUE, NULL | Code unique du kiosque |
| `nom` | VARCHAR(150) | NOT NULL | Nom du kiosque |
| `adresse` | TEXT | NULL | Adresse complète |
| `quartier` | VARCHAR(100) | NULL | Quartier ou zone |
| `ville` | VARCHAR(100) | NULL | Ville |
| `latitude` | DECIMAL(10,8) | NULL | Coordonnée GPS latitude |
| `longitude` | DECIMAL(11,8) | NULL | Coordonnée GPS longitude |
| `telephone` | VARCHAR(20) | NULL | Numéro de téléphone |
| `photo` | VARCHAR(255) | NULL | Photo du kiosque |
| `type` | ENUM | DEFAULT 'fixe' | Type de kiosque |
| `statut` | ENUM | DEFAULT 'actif' | Statut du kiosque |
| `capacite_agents` | INT | DEFAULT 1 | Nombre d'agents max |
| `horaire_ouverture` | TIME | NULL | Heure d'ouverture |
| `horaire_fermeture` | TIME | NULL | Heure de fermeture |
| `description` | TEXT | NULL | Description additionnelle |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

#### Relations

- **1:N** avec `agents` (un kiosque peut avoir plusieurs agents)

#### Données d'exemple

```sql
INSERT INTO kiosques (uid, code, nom, adresse, quartier, ville, latitude, longitude, type) VALUES
(UUID(), 'K001', 'Kiosque Agoè Centre', 'Avenue de la Paix, près du marché', 'Agoè', 'Lomé', 6.1667, 1.2167, 'fixe'),
(UUID(), 'K002', 'Kiosque Tokoin', 'Carrefour Tokoin Casablanca', 'Tokoin', 'Lomé', 6.1733, 1.2309, 'fixe'),
(UUID(), 'K003', 'Kiosque Mobile Zone', 'Variable', 'Variable', 'Lomé', NULL, NULL, 'mobile');
```

#### Notes

- Les coordonnées GPS permettent la géolocalisation et le calcul de distances
- Un kiosque "mobile" peut ne pas avoir de coordonnées fixes
- La capacité d'agents permet de gérer la saturation d'un kiosque

---

### 8. **agents** - Agents de Mobile Money

Table pour gérer les agents de mobile money sur le terrain.

#### Structure SQL

```sql
CREATE TABLE agents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL,
    code_agent VARCHAR(50) UNIQUE NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    montant_initial_total DECIMAL(15,2) DEFAULT 0.00,
    espece_initiale DECIMAL(15,2) DEFAULT 0.00,
    kiosque_id BIGINT UNSIGNED NULL COMMENT 'Kiosque assigné',
    statut ENUM('actif', 'inactif', 'suspendu', 'en_attente') DEFAULT 'actif',
    user_id BIGINT UNSIGNED NULL COMMENT 'Lien vers utilisateur',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (kiosque_id) REFERENCES kiosques(id) ON DELETE SET NULL,
    INDEX idx_code (code_agent),
    INDEX idx_telephone (telephone),
    INDEX idx_user (user_id),
    INDEX idx_kiosque (kiosque_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 |
| `code_agent` | VARCHAR(50) | UNIQUE, NULL | Code agent (ex: AG001) |
| `nom` | VARCHAR(100) | NOT NULL | Nom de famille |
| `prenom` | VARCHAR(100) | NOT NULL | Prénom |
| `telephone` | VARCHAR(20) | UNIQUE, NOT NULL | Numéro de téléphone |
| `montant_initial_total` | DECIMAL(15,2) | DEFAULT 0.00 | Montant initial total |
| `espece_initiale` | DECIMAL(15,2) | DEFAULT 0.00 | Espèces initiales |
| `kiosque_id` | BIGINT UNSIGNED | FK → kiosques.id, NULL | Kiosque assigné |
| `statut` | ENUM | DEFAULT 'actif' | Statut de l'agent |
| `user_id` | BIGINT UNSIGNED | FK → utilisateurs.id, NULL | Lien vers compte utilisateur |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |
| `deleted_at` | TIMESTAMP | NULL | Suppression logique |

---

### 8. **transactions** - Transactions Mobile Money

Table pour enregistrer toutes les transactions de mobile money.

#### Structure SQL

```sql
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL,
    reference VARCHAR(50) UNIQUE NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(15,2) NOT NULL,
    type ENUM('depot', 'retrait', 'transfert', 'paiement') NOT NULL,
    operateur_id BIGINT UNSIGNED NOT NULL COMMENT 'Référence vers operateurs',
    agent_id BIGINT UNSIGNED NOT NULL,
    statut ENUM('valide', 'en_attente', 'annule', 'echoue') DEFAULT 'valide',
    description TEXT NULL,
    commission DECIMAL(15,2) NULL,
    virtual_balance_after DECIMAL(15,2) NULL,
    operator_txn_id VARCHAR(50) NULL,
    client_nom VARCHAR(100) NULL,
    client_telephone VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id) ON DELETE RESTRICT,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE RESTRICT,
    INDEX idx_reference (reference),
    INDEX idx_agent (agent_id),
    INDEX idx_operateur (operateur_id),
    INDEX idx_date (date),
    INDEX idx_statut (statut),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 |
| `reference` | VARCHAR(50) | UNIQUE, NOT NULL | Référence unique transaction |
| `date` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de la transaction |
| `montant` | DECIMAL(15,2) | NOT NULL | Montant de la transaction |
| `type` | ENUM | NOT NULL | Type de transaction |
| `operateur_id` | BIGINT UNSIGNED | FK → operateurs.id, NOT NULL | Opérateur utilisé |
| `agent_id` | BIGINT UNSIGNED | FK → agents.id, NOT NULL | Agent effectuant la transaction |
| `statut` | ENUM | DEFAULT 'valide' | Statut de la transaction |
| `description` | TEXT | NULL | Description/notes |
| `commission` | DECIMAL(15,2) | NULL | Commission perçue |
| `virtual_balance_after` | DECIMAL(15,2) | NULL | Solde virtuel après transaction |
| `operator_txn_id` | VARCHAR(50) | NULL | ID transaction opérateur |
| `client_nom` | VARCHAR(100) | NULL | Nom du client |
| `client_telephone` | VARCHAR(20) | NULL | Téléphone du client |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |

---

### 9. **soldes** - Soldes des Agents (Refactorisée)

Table refactorisée pour tracker les soldes de manière flexible (un enregistrement par agent/opérateur).

#### Structure SQL

```sql
CREATE TABLE soldes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL,
    agent_id BIGINT UNSIGNED NOT NULL,
    operateur_id BIGINT UNSIGNED NULL COMMENT 'NULL pour espèce',
    montant DECIMAL(15,2) DEFAULT 0.00,
    type ENUM('espece', 'virtuel') NOT NULL COMMENT 'Type de solde',
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id) ON DELETE CASCADE,
    INDEX idx_agent (agent_id),
    INDEX idx_operateur (operateur_id),
    INDEX idx_date (date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 |
| `agent_id` | BIGINT UNSIGNED | FK → agents.id, NOT NULL | Agent concerné |
| `operateur_id` | BIGINT UNSIGNED | FK → operateurs.id, NULL | Opérateur (NULL pour espèce) |
| `montant` | DECIMAL(15,2) | DEFAULT 0.00 | Montant du solde |
| `type` | ENUM | NOT NULL | Type : 'espece' ou 'virtuel' |
| `date` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de l'enregistrement |
| `description` | TEXT | NULL | Description/notes |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |

#### Avantages de cette structure

- **Flexibilité** : Permet d'ajouter des opérateurs sans modifier le schéma
- **Historique** : Conserve l'historique des soldes dans le temps
- **Simplicité** : Une ligne par type de solde (espèce, YAS, Flooz, Orange, etc.)

#### Exemple de requête : Soldes actuels d'un agent

```sql
SELECT 
    a.nom,
    a.prenom,
    s.type,
    o.libelle as operateur,
    s.montant
FROM agents a
JOIN soldes s ON a.id = s.agent_id
LEFT JOIN operateurs o ON s.operateur_id = o.id
WHERE a.id = 1
  AND s.id IN (
    SELECT MAX(id) FROM soldes 
    WHERE agent_id = a.id 
    GROUP BY operateur_id, type
  );
```

---

### 10. **system_logs** - Logs Système

Table pour enregistrer tous les logs système (connexions, actions CRUD, modifications, etc.).

#### Structure SQL

```sql
CREATE TABLE system_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL COMMENT 'UUID v4',
    user_id BIGINT UNSIGNED NULL COMMENT 'Utilisateur ayant effectué l\'action',
    action ENUM('create', 'update', 'delete', 'login', 'logout', 'login_failed', 
                'assign', 'unassign', 'validate', 'cancel', 'export', 'import', 'other') NOT NULL,
    model_type VARCHAR(255) NULL COMMENT 'Type de modèle concerné ex: App\\Models\\Agent',
    model_id BIGINT UNSIGNED NULL COMMENT 'ID de l\'entité concernée',
    description TEXT NOT NULL COMMENT 'Description de l\'action',
    old_values JSON NULL COMMENT 'Valeurs avant modification',
    new_values JSON NULL COMMENT 'Valeurs après modification',
    ip_address VARCHAR(45) NULL COMMENT 'Adresse IP de l\'utilisateur',
    user_agent TEXT NULL COMMENT 'User agent du navigateur',
    metadata JSON NULL COMMENT 'Métadonnées supplémentaires',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_model (model_type, model_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 |
| `user_id` | BIGINT UNSIGNED | FK → utilisateurs.id, NULL | Utilisateur ayant effectué l'action |
| `action` | ENUM | NOT NULL | Type d'action effectuée |
| `model_type` | VARCHAR(255) | NULL | Type de modèle concerné (namespace complet) |
| `model_id` | BIGINT UNSIGNED | NULL | ID de l'entité concernée |
| `description` | TEXT | NOT NULL | Description lisible de l'action |
| `old_values` | JSON | NULL | Valeurs avant modification (format JSON) |
| `new_values` | JSON | NULL | Valeurs après modification (format JSON) |
| `ip_address` | VARCHAR(45) | NULL | Adresse IP (IPv4 ou IPv6) |
| `user_agent` | TEXT | NULL | User agent du navigateur |
| `metadata` | JSON | NULL | Métadonnées supplémentaires |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création du log |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |

#### Types d'actions

- **create** : Création d'une entité (Agent, Kiosque, Transaction, etc.)
- **update** : Modification d'une entité
- **delete** : Suppression d'une entité
- **login** : Connexion réussie d'un utilisateur
- **logout** : Déconnexion d'un utilisateur
- **login_failed** : Tentative de connexion échouée
- **assign** : Affectation (ex: agent à un kiosque)
- **unassign** : Retrait d'affectation
- **validate** : Validation d'une opération
- **cancel** : Annulation d'une opération
- **export** : Export de données (Excel, PDF, etc.)
- **import** : Import de données
- **other** : Autre type d'action

#### Relations

- **N:0..1** avec `utilisateurs` (plusieurs logs peuvent être créés par un utilisateur)

#### Fonctionnalités

1. **Logging automatique** : Les modèles utilisant le trait `LogsActivity` enregistrent automatiquement les actions CRUD
2. **Masquage des données sensibles** : Les mots de passe et tokens sont automatiquement masqués avec '********'
3. **Traçabilité complète** : IP, user agent, et métadonnées pour chaque action
4. **Filtrage avancé** : Filtres par utilisateur, action, type d'entité, et dates
5. **Export** : Possibilité d'exporter les logs en Excel ou PDF

#### Notes

- Les champs `old_values` et `new_values` stockent les données au format JSON
- Les champs sensibles (mot_de_passe, remember_token, api_token) sont masqués automatiquement
- L'index composite sur `model_type` et `model_id` permet de retrouver rapidement tous les logs d'une entité
- Essentiel pour la sécurité, l'audit, la conformité et le debugging

---

### 11. **audits** - Historique des Modifications

Table pour l'audit trail (traçabilité des modifications).

#### Structure SQL

```sql
CREATE TABLE audits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) UNIQUE NOT NULL,
    transaction_id BIGINT UNSIGNED NULL,
    ancien_montant DECIMAL(15,2) NULL,
    nouveau_montant DECIMAL(15,2) NULL,
    operateur_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    raison TEXT NULL,
    type_modification ENUM('correction', 'annulation', 'ajustement') NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_transaction (transaction_id),
    INDEX idx_user (user_id),
    INDEX idx_date (date_modification)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Colonnes

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY | Identifiant unique |
| `uid` | CHAR(36) | UNIQUE, NOT NULL | UUID v4 |
| `transaction_id` | BIGINT UNSIGNED | FK → transactions.id, NULL | Transaction modifiée |
| `ancien_montant` | DECIMAL(15,2) | NULL | Montant avant modification |
| `nouveau_montant` | DECIMAL(15,2) | NULL | Montant après modification |
| `operateur_id` | BIGINT UNSIGNED | FK → operateurs.id, NULL | Opérateur concerné |
| `user_id` | BIGINT UNSIGNED | FK → utilisateurs.id, NULL | Utilisateur ayant modifié |
| `date_modification` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de la modification |
| `raison` | TEXT | NULL | Raison de la modification |
| `type_modification` | ENUM | NOT NULL | Type de modification |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Date de modification |

---

## Règles de Gestion

### Gestion des Opérateurs

1. Chaque opérateur DOIT avoir un code unique
2. Le logo est optionnel mais recommandé
3. L'ordre permet de contrôler l'affichage dans l'interface
4. Seuls les opérateurs actifs sont utilisables pour les nouvelles transactions

### Gestion des Utilisateurs et Profils

1. Un utilisateur PEUT avoir plusieurs profils
2. Un profil PEUT être assigné à plusieurs utilisateurs
3. Les permissions sont gérées au niveau du profil, pas de l'utilisateur
4. Un utilisateur sans profil n'a aucun accès

### Gestion des Liens et Permissions

1. Un lien PEUT avoir un parent (pour les sous-menus)
2. Un lien sans parent est un menu de niveau 1
3. Les permissions sont définies par la table `profil_liens`
4. Si un profil a accès à un sous-menu, il DOIT avoir accès au menu parent

### Gestion des Kiosques

1. Chaque kiosque DOIT avoir un nom unique dans une même ville
2. Les coordonnées GPS (latitude/longitude) sont optionnelles mais recommandées
3. Un kiosque de type 'fixe' DEVRAIT avoir des coordonnées GPS
4. Un kiosque de type 'mobile' peut avoir des coordonnées variables ou nulles
5. La capacité d'agents définit le nombre maximum d'agents pouvant être assignés
6. Un kiosque ne peut pas être supprimé s'il a des agents actifs assignés

### Gestion des Agents

1. Un agent DOIT avoir un téléphone unique
2. Un agent PEUT être lié à un compte utilisateur
3. Un agent PEUT être assigné à un kiosque
4. Un agent sans kiosque est considéré comme "non assigné"
5. Un agent peut avoir plusieurs enregistrements de solde (historique)
6. Le dernier solde enregistré est le solde actuel
7. Le nombre d'agents actifs dans un kiosque ne doit pas dépasser sa capacité

### Gestion des Transactions

1. Chaque transaction DOIT être liée à un opérateur existant
2. Chaque transaction DOIT être liée à un agent existant
3. La référence DOIT être unique dans tout le système
4. Les transactions validées ne peuvent plus être modifiées (seulement via audit)

### Gestion des Soldes

1. Un solde de type 'espece' a `operateur_id` NULL
2. Un solde de type 'virtuel' DOIT avoir un `operateur_id`
3. Les soldes sont mis à jour après chaque transaction
4. L'historique complet des soldes est conservé

---

## Requêtes Utiles

### Obtenir tous les liens accessibles par un utilisateur

```sql
SELECT DISTINCT l.*
FROM liens l
JOIN profil_liens pl ON l.id = pl.lien_id
JOIN user_profils up ON pl.profil_id = up.profil_id
WHERE up.user_id = ? -- ID de l'utilisateur
  AND l.visible = TRUE
  AND l.deleted_at IS NULL
  AND pl.deleted_at IS NULL
ORDER BY l.ordre;
```

### Solde total actuel d'un agent

```sql
SELECT 
    a.id,
    a.nom,
    a.prenom,
    SUM(s.montant) as solde_total,
    SUM(CASE WHEN s.type = 'espece' THEN s.montant ELSE 0 END) as espece,
    SUM(CASE WHEN s.type = 'virtuel' THEN s.montant ELSE 0 END) as virtuel
FROM agents a
JOIN soldes s ON a.id = s.agent_id
WHERE a.id = ? -- ID de l'agent
  AND s.id IN (
    SELECT MAX(id) FROM soldes 
    WHERE agent_id = a.id 
    GROUP BY COALESCE(operateur_id, 0), type
  )
GROUP BY a.id;
```

### Statistiques des transactions par opérateur (30 derniers jours)

```sql
SELECT 
    o.libelle as operateur,
    o.couleur,
    COUNT(t.id) as nombre_transactions,
    SUM(t.montant) as montant_total,
    AVG(t.montant) as montant_moyen,
    SUM(t.commission) as commission_totale
FROM operateurs o
LEFT JOIN transactions t ON o.id = t.operateur_id 
    AND t.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    AND t.statut = 'valide'
WHERE o.statut = 'actif'
GROUP BY o.id
ORDER BY montant_total DESC;
```

### Vérifier les permissions d'un utilisateur pour une route

```sql
SELECT COUNT(*) > 0 as has_access
FROM liens l
JOIN profil_liens pl ON l.id = pl.lien_id
JOIN user_profils up ON pl.profil_id = up.profil_id
WHERE up.user_id = ? -- ID utilisateur
  AND l.route = ? -- Nom de la route
  AND l.deleted_at IS NULL
  AND pl.deleted_at IS NULL
  AND up.deleted_at IS NULL;
```

### Liste des agents avec leur solde actuel par opérateur

```sql
SELECT 
    a.id,
    a.code_agent,
    a.nom,
    a.prenom,
    a.telephone,
    a.statut,
    s.type,
    o.libelle as operateur,
    o.couleur,
    s.montant,
    s.date as derniere_maj
FROM agents a
LEFT JOIN (
    SELECT * FROM soldes s1
    WHERE id IN (
        SELECT MAX(id) FROM soldes 
        GROUP BY agent_id, COALESCE(operateur_id, 0), type
    )
) s ON a.id = s.agent_id
LEFT JOIN operateurs o ON s.operateur_id = o.id
WHERE a.deleted_at IS NULL
ORDER BY a.nom, a.prenom, s.type, o.libelle;
```

### Liste des kiosques avec le nombre d'agents assignés

```sql
SELECT 
    k.id,
    k.code,
    k.nom,
    k.quartier,
    k.ville,
    k.capacite_agents,
    k.statut,
    COUNT(a.id) as nombre_agents,
    (k.capacite_agents - COUNT(a.id)) as places_disponibles
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id 
    AND a.statut = 'actif'
    AND a.deleted_at IS NULL
WHERE k.deleted_at IS NULL
GROUP BY k.id
ORDER BY k.ville, k.nom;
```

### Trouver les kiosques à proximité d'une position GPS (rayon en km)

```sql
-- Formule de Haversine pour calculer la distance
SELECT 
    k.id,
    k.code,
    k.nom,
    k.adresse,
    k.quartier,
    k.ville,
    k.latitude,
    k.longitude,
    k.telephone,
    (
        6371 * ACOS(
            COS(RADIANS(?)) * COS(RADIANS(k.latitude)) *
            COS(RADIANS(k.longitude) - RADIANS(?)) +
            SIN(RADIANS(?)) * SIN(RADIANS(k.latitude))
        )
    ) AS distance_km
FROM kiosques k
WHERE k.latitude IS NOT NULL
  AND k.longitude IS NOT NULL
  AND k.statut = 'actif'
  AND k.deleted_at IS NULL
HAVING distance_km <= ? -- Rayon en km (ex: 5)
ORDER BY distance_km ASC
LIMIT 10;

-- Paramètres : latitude, longitude, latitude (répété), rayon_km
```

### Agents par kiosque avec leur localisation

```sql
SELECT 
    k.nom as kiosque,
    k.code as code_kiosque,
    k.quartier,
    k.ville,
    k.latitude,
    k.longitude,
    a.code_agent,
    a.nom,
    a.prenom,
    a.telephone,
    a.statut,
    u.email
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id
LEFT JOIN utilisateurs u ON a.user_id = u.id
WHERE k.deleted_at IS NULL
  AND (a.deleted_at IS NULL OR a.id IS NULL)
ORDER BY k.nom, a.nom, a.prenom;
```

### Statistiques de transactions par kiosque

```sql
SELECT 
    k.nom as kiosque,
    k.quartier,
    k.ville,
    COUNT(t.id) as total_transactions,
    SUM(t.montant) as montant_total,
    SUM(t.commission) as commission_totale,
    COUNT(DISTINCT a.id) as nombre_agents
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id
LEFT JOIN transactions t ON a.id = t.agent_id
    AND t.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    AND t.statut = 'valide'
WHERE k.deleted_at IS NULL
GROUP BY k.id
ORDER BY montant_total DESC;
```

### Carte des kiosques (tous les kiosques avec coordonnées GPS)

```sql
SELECT 
    k.id,
    k.code,
    k.nom,
    k.quartier,
    k.ville,
    k.latitude,
    k.longitude,
    k.type,
    k.statut,
    COUNT(a.id) as nombre_agents,
    k.capacite_agents
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id 
    AND a.statut = 'actif'
    AND a.deleted_at IS NULL
WHERE k.latitude IS NOT NULL
  AND k.longitude IS NOT NULL
  AND k.deleted_at IS NULL
GROUP BY k.id
ORDER BY k.ville, k.quartier;
```

---

## Migrations Laravel

### Ordre de création des migrations

1. `create_utilisateurs_table`
2. `create_profils_table`
3. `create_user_profils_table`
4. `create_liens_table`
5. `create_profil_liens_table`
6. `create_operateurs_table`
7. `create_kiosques_table` ⭐ **NOUVEAU**
8. `create_agents_table` (dépend de kiosques)
9. `create_transactions_table`
10. `create_soldes_table`
11. `create_audits_table`

### Commandes Laravel

```bash
# Créer toutes les migrations
php artisan make:migration create_utilisateurs_table
php artisan make:migration create_profils_table
php artisan make:migration create_user_profils_table
php artisan make:migration create_liens_table
php artisan make:migration create_profil_liens_table
php artisan make:migration create_operateurs_table
php artisan make:migration create_kiosques_table
php artisan make:migration create_agents_table
php artisan make:migration create_transactions_table
php artisan make:migration create_soldes_table
php artisan make:migration create_audits_table

# Exécuter les migrations
php artisan migrate

# Créer les modèles Eloquent
php artisan make:model Utilisateur
php artisan make:model Profil
php artisan make:model Lien
php artisan make:model Operateur
php artisan make:model Kiosque
php artisan make:model Agent
php artisan make:model Transaction
php artisan make:model Solde
php artisan make:model Audit
```

---

## Index Recommandés pour Performances

### Index composites additionnels

```sql
-- Pour les recherches de soldes actuels
CREATE INDEX idx_soldes_agent_date ON soldes(agent_id, date DESC);
CREATE INDEX idx_soldes_agent_operateur ON soldes(agent_id, operateur_id, type);

-- Pour les statistiques de transactions
CREATE INDEX idx_transactions_date_statut ON transactions(date, statut);
CREATE INDEX idx_transactions_operateur_date ON transactions(operateur_id, date DESC);

-- Pour les permissions
CREATE INDEX idx_profil_liens_profil ON profil_liens(profil_id, lien_id);
CREATE INDEX idx_user_profils_user ON user_profils(user_id, profil_id);

-- Pour les audits
CREATE INDEX idx_audits_transaction_date ON audits(transaction_id, date_modification DESC);

-- Pour les kiosques et géolocalisation
CREATE INDEX idx_kiosques_ville_quartier ON kiosques(ville, quartier);
CREATE INDEX idx_kiosques_statut ON kiosques(statut, deleted_at);
CREATE INDEX idx_agents_kiosque ON agents(kiosque_id, statut);
```

---

## Sécurité et Bonnes Pratiques

### Chiffrement et Hachage

- **Mots de passe** : Utiliser `bcrypt` avec un coût de 12 minimum
- **Tokens** : Utiliser des tokens aléatoires sécurisés (256 bits minimum)
- **UUID** : Utiliser UUID v4 pour les identifiants externes

### Validation des Données

- **Montants** : Toujours positifs, maximum 2 décimales
- **Téléphones** : Format E.164 recommandé
- **Emails** : Validation RFC 5322
- **Dates** : Toujours avec timezone (UTC recommandé)
- **Latitude** : Entre -90 et 90 degrés
- **Longitude** : Entre -180 et 180 degrés
- **Coordonnées GPS** : Pour le Togo, latitude ~6° N, longitude ~1° E

### Sauvegardes

1. Sauvegardes automatiques quotidiennes
2. Rétention de 30 jours minimum
3. Test de restauration mensuel
4. Sauvegarde avant chaque migration

### Logs et Monitoring

- Logs d'authentification
- Logs des modifications de permissions
- Logs des transactions importantes (> seuil)
- Alertes pour activités suspectes

---

## Cas d'Usage - Géolocalisation

### 1. Afficher les kiosques sur une carte

**Objectif** : Afficher tous les kiosques actifs sur une carte interactive (Google Maps, OpenStreetMap, etc.)

```sql
SELECT 
    id,
    nom,
    quartier,
    ville,
    latitude,
    longitude,
    telephone,
    (SELECT COUNT(*) FROM agents WHERE kiosque_id = k.id AND statut = 'actif') as agents_actifs
FROM kiosques k
WHERE latitude IS NOT NULL
  AND longitude IS NOT NULL
  AND statut = 'actif'
  AND deleted_at IS NULL;
```

**Utilisation** : Les résultats peuvent être affichés avec des marqueurs sur une carte web.

### 2. Trouver le kiosque le plus proche

**Objectif** : Trouver le kiosque le plus proche d'un utilisateur mobile basé sur sa position GPS.

```sql
-- Exemple: Position utilisateur : Latitude 6.1667, Longitude 1.2167
-- Rayon de recherche : 10 km

SELECT 
    id,
    code,
    nom,
    adresse,
    quartier,
    telephone,
    latitude,
    longitude,
    (
        6371 * ACOS(
            COS(RADIANS(6.1667)) * COS(RADIANS(latitude)) *
            COS(RADIANS(longitude) - RADIANS(1.2167)) +
            SIN(RADIANS(6.1667)) * SIN(RADIANS(latitude))
        )
    ) AS distance_km
FROM kiosques
WHERE latitude IS NOT NULL
  AND longitude IS NOT NULL
  AND statut = 'actif'
  AND deleted_at IS NULL
HAVING distance_km <= 10
ORDER BY distance_km
LIMIT 5;
```

### 3. Optimisation des tournées d'agents

**Objectif** : Grouper les kiosques par quartier pour optimiser les visites de supervision.

```sql
SELECT 
    quartier,
    ville,
    COUNT(*) as nombre_kiosques,
    COUNT(DISTINCT a.id) as nombre_agents,
    AVG(latitude) as lat_centre,
    AVG(longitude) as long_centre
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id AND a.statut = 'actif'
WHERE k.statut = 'actif'
  AND k.deleted_at IS NULL
  AND k.latitude IS NOT NULL
GROUP BY quartier, ville
ORDER BY ville, quartier;
```

### 4. Analyse de couverture géographique

**Objectif** : Identifier les zones sans kiosque actif (zones blanches).

```sql
-- Liste des quartiers sans kiosque
SELECT DISTINCT quartier, ville
FROM (
    SELECT 'Agoè' as quartier, 'Lomé' as ville
    UNION SELECT 'Tokoin', 'Lomé'
    UNION SELECT 'Bè', 'Lomé'
    -- Ajouter d'autres quartiers...
) AS tous_quartiers
WHERE (quartier, ville) NOT IN (
    SELECT quartier, ville 
    FROM kiosques 
    WHERE statut = 'actif' 
      AND deleted_at IS NULL
);
```

### 5. Capacité et saturation des kiosques

**Objectif** : Identifier les kiosques saturés ou sous-utilisés.

```sql
SELECT 
    k.code,
    k.nom,
    k.quartier,
    k.capacite_agents,
    COUNT(a.id) as agents_assignes,
    (k.capacite_agents - COUNT(a.id)) as places_restantes,
    CASE 
        WHEN COUNT(a.id) >= k.capacite_agents THEN 'SATURÉ'
        WHEN COUNT(a.id) = 0 THEN 'VIDE'
        WHEN COUNT(a.id) < k.capacite_agents * 0.5 THEN 'SOUS-UTILISÉ'
        ELSE 'NORMAL'
    END as statut_capacite
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id 
    AND a.statut = 'actif' 
    AND a.deleted_at IS NULL
WHERE k.statut = 'actif'
  AND k.deleted_at IS NULL
GROUP BY k.id
ORDER BY statut_capacite DESC, k.nom;
```

### 6. Performance par zone géographique

**Objectif** : Comparer les performances des différentes zones.

```sql
SELECT 
    k.ville,
    k.quartier,
    COUNT(DISTINCT k.id) as nombre_kiosques,
    COUNT(DISTINCT a.id) as nombre_agents,
    COUNT(t.id) as total_transactions,
    SUM(t.montant) as chiffre_affaires,
    SUM(t.commission) as commissions_totales,
    AVG(t.montant) as ticket_moyen
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id AND a.deleted_at IS NULL
LEFT JOIN transactions t ON a.id = t.agent_id 
    AND t.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    AND t.statut = 'valide'
WHERE k.deleted_at IS NULL
GROUP BY k.ville, k.quartier
ORDER BY chiffre_affaires DESC;
```

### 7. Alerte de zones à risque (sans agent actif)

**Objectif** : Identifier les kiosques sans agent actif assigné.

```sql
SELECT 
    k.id,
    k.code,
    k.nom,
    k.quartier,
    k.ville,
    k.telephone,
    k.latitude,
    k.longitude
FROM kiosques k
LEFT JOIN agents a ON k.id = a.kiosque_id 
    AND a.statut = 'actif' 
    AND a.deleted_at IS NULL
WHERE k.statut = 'actif'
  AND k.deleted_at IS NULL
  AND a.id IS NULL
ORDER BY k.ville, k.quartier;
```

---

## Migration depuis l'ancien schéma SQLite

### Script de migration

```sql
-- 1. Copier les utilisateurs
INSERT INTO utilisateurs (uid, nom, prenom, email, mot_de_passe, telephone, photo_profil, statut, created_at)
SELECT uid, nom, prenom, email, mot_de_passe, telephone, photo_profil, statut, date_creation
FROM old_utilisateurs;

-- 2. Créer les opérateurs de base
INSERT INTO operateurs (code, libelle, ordre) VALUES
('YAS', 'Mixx by YAS', 1),
('FLOOZ', 'Flooz', 2);

-- 3. Créer les kiosques (NOUVEAU - données à collecter)
-- Note: Les kiosques n'existaient pas dans l'ancien schéma
-- Il faut créer les kiosques manuellement ou via import CSV
INSERT INTO kiosques (uid, code, nom, ville, statut) VALUES
(UUID(), 'K001', 'Kiosque Central', 'Lomé', 'actif'),
(UUID(), 'K002', 'Kiosque Tokoin', 'Lomé', 'actif');

-- 4. Copier les agents (sans assignation de kiosque initialement)
INSERT INTO agents (uid, code_agent, nom, prenom, telephone, montant_initial_total, espece_initiale, statut, created_at)
SELECT uid, code_agent, nom, prenom, telephone, montant_initial_total, espece_initiale, statut, date_creation
FROM old_agents;

-- 5. Optionnel: Assigner les agents aux kiosques
-- UPDATE agents SET kiosque_id = (SELECT id FROM kiosques WHERE code = 'K001') WHERE id IN (...);

-- 6. Migrer les soldes (structure refactorisée)
INSERT INTO soldes (uid, agent_id, operateur_id, montant, type, date)
SELECT 
    UUID(),
    a.id,
    (SELECT id FROM operateurs WHERE code = 'YAS'),
    os.tmoney,
    'virtuel',
    os.date
FROM old_soldes os
JOIN agents a ON os.agent_uid = a.uid
WHERE os.tmoney > 0;

-- Répéter pour Flooz et espèces...
```

---

## Glossary / Glossaire

| Terme | Signification |
|-------|---------------|
| **Opérateur** | Fournisseur de service mobile money (YAS, Flooz, Orange) |
| **Kiosque** | Point de vente physique où opèrent les agents |
| **Kiosque Fixe** | Kiosque avec une localisation permanente |
| **Kiosque Mobile** | Kiosque qui peut se déplacer (coordonnées GPS variables) |
| **Géolocalisation** | Coordonnées GPS (latitude/longitude) d'un kiosque |
| **Profil** | Rôle ou groupe d'utilisateurs avec des permissions spécifiques |
| **Lien** | Route ou menu de l'application |
| **Solde Virtuel** | Montant disponible sur un compte mobile money |
| **Solde Espèce** | Montant en cash physique |
| **Soft Delete** | Suppression logique (enregistrement marqué comme supprimé) |
| **UUID** | Identifiant unique universel (format: 550e8400-e29b-41d4-a716-446655440000) |
| **Haversine** | Formule mathématique pour calculer la distance entre deux points GPS |

---

## Support et Documentation

- **Modèles Eloquent** : `/app/Models`
- **Migrations** : `/database/migrations`
- **Seeders** : `/database/seeders`
- **Documentation API** : À venir

---

**Fin du document**
