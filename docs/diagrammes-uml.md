# Diagrammes UML — PDV.Connect

Documentation des diagrammes du projet (diagrammes de classe, cas d'utilisation, activités et séquences).  
Rendu possible avec [Mermaid Live](https://mermaid.live), GitHub, GitLab ou tout éditeur supportant Mermaid.

---

## 1. Diagramme de classes

Modèle de domaine principal : utilisateurs, profils, agents, kiosques, transactions, opérateurs, soldes, salaires et trésorerie.

```mermaid
classDiagram
    direction TB

    class Utilisateur {
        +uuid uid
        +string nom
        +string prenom
        +string email
        +string telephone
        +string photo_profil
        +string statut
        +datetime dernier_connexion
        -string mot_de_passe
        +getNomComplet()
        +getAuthPassword()
    }

    class Profil {
        +string libelle
        +string description
        +int niveau
    }

    class Agent {
        +uuid uid
        +string code_agent
        +string nom
        +string prenom
        +string telephone
        +decimal montant_initial_total
        +decimal espece_initiale
        +string statut
        +getNomComplet()
        +soldeActuel()
        +soldesActuels()
        +soldeTotal()
    }

    class Kiosque {
        +uuid uid
        +string code
        +string nom
        +string adresse
        +string ville
        +decimal latitude
        +decimal longitude
        +int capacite_agents
        +string statut
        +distanceVers()
        +estSature()
        +placesDisponibles()
    }

    class Operateur {
        +string code
        +string libelle
        +string logo
        +string statut
        +int ordre
    }

    class Transaction {
        +uuid uid
        +string reference
        +datetime date
        +decimal montant
        +string type
        +string statut
        +decimal commission
        +string client_nom
        +string client_telephone
    }

    class Solde {
        +uuid uid
        +decimal montant
        +string type
        +datetime date
        +string description
    }

    class TypeOperation {
        +string code
        +string libelle
        +bool actif
        +bool requiert_operateur
    }

    class Lien {
        +string libelle
        +string route
        +string url
        +int ordre
        +bool visible
        +int parent_id
    }

    class Salaire {
        +string periode
        +date date_debut
        +date date_fin
        +decimal montant_total
        +string statut
    }

    class ParametreSalaire {
        +string nom
        +string type
        +decimal montant_fixe
        +decimal taux_commission
        +bool actif
    }

    class MouvementTresorerie {
        +string type
        +string categorie
        +decimal montant
        +date date_mouvement
        +string reference
    }

    class Audit {
        +uuid uid
        +decimal ancien_montant
        +decimal nouveau_montant
        +datetime date_modification
        +string raison
        +string type_modification
    }

    Utilisateur "1" -- "*" Profil : profils (n..n)
    Utilisateur "1" -- "0..1" Agent : agent
    Utilisateur "1" -- "*" Audit : audits
    Utilisateur "1" -- "*" MouvementTresorerie : mouvements

    Agent "1" -- "*" Transaction : transactions
    Agent "1" -- "*" Solde : soldes
    Agent "1" -- "*" Salaire : salaires
    Agent "n" -- "1" Kiosque : kiosque
    Agent "1" -- "1" Utilisateur : utilisateur

    Kiosque "1" -- "*" Agent : agents

    Operateur "1" -- "*" Transaction : transactions
    Operateur "1" -- "*" Solde : soldes
    Operateur "1" -- "*" Audit : audits

    Transaction "1" -- "*" Audit : audits
    Transaction "n" -- "1" TypeOperation : typeOperation
    Transaction "n" -- "1" Operateur : operateur

    Solde "n" -- "1" Agent : agent
    Solde "n" -- "0..1" Operateur : operateur

    Profil "n" -- "*" Lien : liens (n..n)
    Profil "n" -- "*" ParametreSalaire : parametresSalaire (n..n)

    Lien "1" -- "*" Lien : parent / enfants

    ParametreSalaire "1" -- "*" Salaire : salaires
    Salaire "n" -- "1" Agent : agent
    Salaire "1" -- "*" MouvementTresorerie : mouvementsTresorerie

    MouvementTresorerie "n" -- "0..1" Transaction : transaction
    MouvementTresorerie "n" -- "0..1" Salaire : salaire

    Audit "n" -- "0..1" Transaction : transaction
    Audit "n" -- "0..1" Operateur : operateur
```

---

## 2. Diagramme de cas d'utilisation

Acteurs et cas d'usage principaux du portail PDV.Connect.

```mermaid
flowchart TB
    subgraph Acteurs
        Admin((Administrateur))
        User((Utilisateur connecté))
        Agent((Agent PDV))
    end

    subgraph Authentification
        UC1[Se connecter]
        UC2[Changer mot de passe]
        UC3[Se déconnecter]
    end

    subgraph Profil
        UC4[Consulter mon profil]
        UC5[Modifier mon profil]
    end

    subgraph Gestion_utilisateurs
        UC6[Créer / modifier utilisateur]
        UC7[Assigner profils]
        UC8[Réinitialiser mot de passe]
        UC9[Activer / suspendre compte]
    end

    subgraph Gestion_agents
        UC10[Créer / modifier agent]
        UC11[Assigner agent à un kiosque]
        UC12[Gérer soldes agent]
        UC13[Changer statut agent]
    end

    subgraph Gestion_kiosques
        UC14[Créer / modifier kiosque]
        UC15[Voir carte kiosques]
        UC16[Assigner agent à kiosque]
    end

    subgraph Transactions
        UC17[Consulter transactions]
        UC18[Exporter transactions]
        UC19[Annuler transaction]
    end

    subgraph Tableau de bord
        UC20[Voir dashboard]
        UC21[Stats temps réel]
    end

    subgraph Rôles et permissions
        UC22[Gérer profils]
        UC23[Gérer permissions]
        UC24[Gérer routes / liens]
    end

    subgraph Entreprise
        UC25[Paramètres salaire]
        UC26[Générer / payer salaires]
        UC27[Mouvements trésorerie]
    end

    Admin --> UC1 & UC2 & UC3 & UC4 & UC5 & UC6 & UC7 & UC8 & UC9
    Admin --> UC10 & UC11 & UC12 & UC13 & UC14 & UC15 & UC16
    Admin --> UC17 & UC18 & UC19 & UC20 & UC21 & UC22 & UC23 & UC24
    Admin --> UC25 & UC26 & UC27

    User --> UC1 & UC2 & UC3 & UC4 & UC5 & UC20 & UC21
    Agent --> UC1 & UC2 & UC3 & UC4 & UC5 & UC20
```

---

## 3. Diagrammes d'activité

### 3.1 Connexion (login)

```mermaid
flowchart TD
    A([Début]) --> B[Afficher formulaire login]
    B --> C{S déjà connecté ?}
    C -->|Oui| D[Rediriger dashboard]
    C -->|Non| E[Utilisateur saisit email / mot de passe]
    E --> F[Soumettre formulaire]
    F --> G{Email valide ?}
    G -->|Non| H[Erreur : identifiants incorrects]
    H --> E
    G -->|Oui| I{Utilisateur trouvé ?}
    I -->|Non| H
    I -->|Oui| J{Statut = actif ?}
    J -->|Non| K[Erreur : compte désactivé]
    K --> E
    J -->|Oui| L{Mot de passe correct ?}
    L -->|Non| H
    L -->|Oui| M[Authentifier et régénérer session]
    M --> N{Première connexion ?<br/>dernier_connexion null}
    N -->|Oui| O[Rediriger changement mot de passe]
    N -->|Non| P[Mettre à jour dernier_connexion]
    P --> Q[Rediriger dashboard]
    O --> R([Fin])
    Q --> R
    D --> R
```

### 3.2 Changement de mot de passe

```mermaid
flowchart TD
    A([Début]) --> B{Connecté ?}
    B -->|Non| C[Rediriger login]
    B -->|Oui| D[Afficher formulaire changement MDP]
    D --> E[Utilisateur saisit nouveau MDP + confirmation]
    E --> F[Soumettre]
    F --> G{MDP ≥ 8 car. et confirmé ?}
    G -->|Non| H[Affichage erreurs validation]
    H --> E
    G -->|Oui| I[Mettre à jour mot de passe]
    I --> J{Première connexion ?}
    J -->|Oui| K[Mettre à jour dernier_connexion]
    K --> L[Rediriger dashboard]
    J -->|Non| M[Rediriger page profil + succès]
    L --> N([Fin])
    M --> N
    C --> N
```

### 3.3 Création d’une transaction (flux métier simplifié)

```mermaid
flowchart TD
    A([Début]) --> B[Sélectionner agent]
    B --> C[Choisir type : dépôt / retrait]
    C --> D[Choisir opérateur]
    D --> E[Saisir montant et infos client]
    E --> F{Données valides ?}
    F -->|Non| G[Affichage erreurs]
    G --> E
    F -->|Oui| H[Enregistrer transaction]
    H --> I[Mettre à jour soldes agent / opérateur]
    I --> J[Statut : valide ou en_attente]
    J --> K([Fin])
```

### 3.4 Mise à jour du profil utilisateur

```mermaid
flowchart TD
    A([Début]) --> B[Accéder à Mon profil]
    B --> C[Afficher formulaire avec données actuelles]
    C --> D[Modifier nom, prénom, email, téléphone, photo]
    D --> E[Soumettre]
    E --> F{Validation OK ?}
    F -->|Non| G[Affichage erreurs]
    G --> D
    F -->|Oui| H{Photo fournie ?}
    H -->|Oui| I[Supprimer ancienne photo si existante]
    I --> J[Stocker nouvelle photo]
    J --> K[Mettre à jour utilisateur]
    H -->|Non| K
    K --> L[Succès : afficher profil à jour]
    L --> M([Fin])
```

---

## 4. Diagrammes de séquence

### 4.1 Connexion (login)

```mermaid
sequenceDiagram
    actor U as Utilisateur
    participant V as Vue Login
    participant A as AuthController
    participant M as Modèle Utilisateur
    participant Auth as Auth (Guard)

    U->>V: Saisit email / mot de passe
    U->>V: Clique "Se connecter"
    V->>A: POST /login (email, password)
    A->>M: where('email', email)->first()
    M-->>A: Utilisateur ou null

    alt Utilisateur non trouvé
        A-->>V: back() + erreur
        V-->>U: Affiche message erreur
    else Utilisateur trouvé
        A->>A: Vérifier statut === actif
        alt Statut inactif
            A-->>V: back() + erreur compte désactivé
        else Statut actif
            A->>A: Hash::check(password, mot_de_passe)
            alt Mot de passe invalide
                A-->>V: back() + erreur
            else Mot de passe valide
                A->>Auth: login(utilisateur, remember)
                Auth-->>A: OK
                A->>A: session()->regenerate()
                alt Premier connexion (dernier_connexion null)
                    A-->>V: redirect(password.change)
                    V-->>U: Formulaire changement MDP
                else Connexion habituelle
                    A->>M: update(dernier_connexion = now())
                    A-->>V: redirect()->intended(dashboard)
                    V-->>U: Page dashboard
                end
            end
        end
    end
```

### 4.2 Changement de mot de passe (première connexion)

```mermaid
sequenceDiagram
    actor U as Utilisateur
    participant V as Vue Change Password
    participant A as AuthController
    participant M as Modèle Utilisateur

    U->>V: Saisit nouveau MDP + confirmation
    U->>V: Clique "Soumettre"
    V->>A: POST /password/change (password, password_confirmation)
    A->>A: Vérifier auth

    alt Non connecté
        A-->>V: redirect(login)
    else Connecté
        A->>A: validate(min:8, confirmed)
        alt Validation échouée
            A-->>V: back() + erreurs
        else Validation OK
            A->>M: update(mot_de_passe = Hash::make(...))
            M-->>A: OK
            alt Première connexion
                A->>M: update(dernier_connexion = now())
                A-->>V: redirect(dashboard)
            else Depuis profil
                A-->>V: redirect(profil.index) + success
            end
            V-->>U: Redirection + message
        end
    end
```

### 4.3 Mise à jour du profil (avec photo)

```mermaid
sequenceDiagram
    actor U as Utilisateur
    participant V as Vue Profil
    participant P as ProfilController
    participant M as Modèle Utilisateur
    participant S as Storage

    U->>V: Modifie nom, prénom, email, photo
    U->>V: Soumet formulaire
    V->>P: PUT /profil (nom, prenom, email, photo_profil)
    P->>P: auth()->user()
    P->>P: validate(...)

    alt Validation échouée
        P-->>V: back() + erreurs
    else Validation OK
        alt Fichier photo présent
            P->>M: user->photo_profil existe ?
            P->>S: delete(ancienne photo) si existante
            P->>S: store('photos/utilisateurs', file)
            S-->>P: path
            P->>M: update(..., photo_profil: path)
        else Pas de photo
            P->>M: update(nom, prenom, email, telephone)
        end
        M-->>P: OK
        P-->>V: redirect(profil.index) + success
        V-->>U: Page profil à jour + message succès
    end
```

### 4.4 Consultation du dashboard (stats temps réel)

```mermaid
sequenceDiagram
    actor U as Utilisateur
    participant V as Vue Dashboard
    participant D as DashboardController
    participant T as Transaction
    participant Ag as Agent
    participant O as Operateur

    U->>V: Accède au dashboard
    V->>D: GET /dashboard
    D->>D: Vérifier auth + require.password.change
    D-->>V: Vue dashboard (layout + contenu)

    V->>D: GET /api/dashboard/stats-temps-reel
    D->>T: Requêtes agrégées (count, sum, période)
    T-->>D: Données
    D->>Ag: Stats par agent si besoin
    D->>O: Stats par opérateur
    D-->>V: JSON (stats)
    V-->>U: Mise à jour des cartes / graphiques
```

---

## Légende et conventions

| Élément | Signification |
|--------|----------------|
| **Diagramme de classes** | Une flèche `A -- B` = association ; `1`, `*`, `n` = cardinalités. |
| **Cas d'utilisation** | Les cas sont des actions du système ; les acteurs sont à l’extérieur. |
| **Activité** | Losanges = décisions ; rectangles = actions ; `([ ])` = début/fin. |
| **Séquence** | Ordre chronologique des messages entre acteurs et composants. |

Pour exporter en PNG/SVG : coller le code Mermaid dans [mermaid.live](https://mermaid.live) puis exporter.
