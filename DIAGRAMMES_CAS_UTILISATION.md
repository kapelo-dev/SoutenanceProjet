# Diagrammes de Cas d'Utilisation - PDV CONNECT

## Vue d'ensemble

Ce document présente les diagrammes de cas d'utilisation pour chaque acteur du système PDV CONNECT.

## Acteurs du système

### Acteurs humains
1. **Super Admin** - Accès complet au système
2. **Admin** - Administrateur de l'application
3. **Superviseur** - Supervision des agents et kiosques
4. **Comptable** - Gestion comptable et rapports
5. **Agent** - Agent de terrain

### Acteur système
6. **Application Mobile** - Système automatisé de création de transactions (acteur secondaire)

---

## 1. Diagramme de Cas d'Utilisation - Super Admin

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam linetype ortho
skinparam nodesep 80
skinparam ranksep 60

rectangle "Système PDV CONNECT" {
  
  actor "Super Admin" as SuperAdmin #e74c3c
  
  usecase "Authentification\n• Connexion\n• Déconnexion" as UC_Auth
  
  usecase "CRUD Utilisateurs\n• Créer • Modifier\n• Supprimer • Consulter\n• Activer/Désactiver" as UC_Users
  
  usecase "CRUD Profils\n• Créer • Modifier\n• Supprimer profil" as UC_Profils
  
  usecase "Gérer Permissions\n• Assigner permissions\n• Gérer liens menu" as UC_Perms
  
  usecase "CRUD Agents\n• Créer • Modifier\n• Supprimer • Consulter" as UC_Agents
  
  usecase "Gérer Soldes\nAgents" as UC_Soldes
  
  usecase "CRUD Kiosques\n• Créer • Modifier\n• Supprimer • Consulter" as UC_Kiosques
  
  usecase "Affecter Agent\nà Kiosque" as UC_Affect
  
  usecase "Gérer Transactions\n• Consulter\n• Exporter" as UC_Trans
  
  usecase "CRUD Opérateurs\n• Créer • Modifier\n• Supprimer" as UC_Oper
  
  usecase "Gérer Types\nd'Opérations" as UC_TypeOper
  
  usecase "Paramètres Salaire\n• Créer • Modifier" as UC_ParamSal
  
  usecase "Gestion Salaires\n• Générer • Payer" as UC_Salaires
  
  usecase "Gérer\nTrésorerie" as UC_Treso
  
  usecase "Consulter Rapports\n• Dashboard • Transactions\n• Agents • Kiosques\n• Statistiques" as UC_Rapports
  
  usecase "Configuration\n• App mobile\n• Opérations agence" as UC_Config
  
  usecase "Consulter Logs\nSystème" as UC_Logs
}

SuperAdmin --> UC_Auth
SuperAdmin --> UC_Users
SuperAdmin --> UC_Profils
SuperAdmin --> UC_Perms
SuperAdmin --> UC_Agents
SuperAdmin --> UC_Soldes
SuperAdmin --> UC_Kiosques
SuperAdmin --> UC_Affect
SuperAdmin --> UC_Trans
SuperAdmin --> UC_Oper
SuperAdmin --> UC_TypeOper
SuperAdmin --> UC_ParamSal
SuperAdmin --> UC_Salaires
SuperAdmin --> UC_Treso
SuperAdmin --> UC_Rapports
SuperAdmin --> UC_Config
SuperAdmin --> UC_Logs

UC_Auth -[hidden]right-> UC_Users
UC_Users -[hidden]right-> UC_Profils
UC_Profils -[hidden]right-> UC_Perms
UC_Perms -[hidden]down-> UC_Agents
UC_Agents -[hidden]right-> UC_Soldes
UC_Soldes -[hidden]right-> UC_Kiosques
UC_Kiosques -[hidden]right-> UC_Affect
UC_Affect -[hidden]right-> UC_Trans
UC_Trans -[hidden]down-> UC_Oper
UC_Oper -[hidden]right-> UC_TypeOper
UC_TypeOper -[hidden]right-> UC_ParamSal
UC_ParamSal -[hidden]down-> UC_Salaires
UC_Salaires -[hidden]right-> UC_Treso
UC_Treso -[hidden]right-> UC_Rapports
UC_Rapports -[hidden]down-> UC_Config
UC_Config -[hidden]right-> UC_Logs

@enduml
```

---

## 2. Diagramme de Cas d'Utilisation - Admin

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam linetype ortho
skinparam nodesep 80
skinparam ranksep 60

rectangle "Système PDV CONNECT" {
  
  actor "Admin" as Admin #3498db
  
  usecase "Authentification\n• Connexion\n• Déconnexion" as UC_Auth
  
  usecase "CRUD Utilisateurs\n• Créer • Modifier\n• Consulter\n• Activer/Désactiver" as UC_Users
  
  usecase "CRUD Agents\n• Créer • Modifier\n• Supprimer • Consulter" as UC_Agents
  
  usecase "Gérer Soldes\nAgents" as UC_Soldes
  
  usecase "CRUD Kiosques\n• Créer • Modifier\n• Supprimer • Consulter" as UC_Kiosques
  
  usecase "Affecter Agent\nà Kiosque" as UC_Affect
  
  usecase "Gérer Transactions\n• Consulter\n• Exporter" as UC_Trans
  
  usecase "CRUD Opérateurs\n• Créer • Modifier" as UC_Oper
  
  usecase "Gérer Types\nd'Opérations" as UC_TypeOper
  
  usecase "Paramètres Salaire\n• Créer • Modifier" as UC_ParamSal
  
  usecase "Gestion Salaires\n• Générer • Payer" as UC_Salaires
  
  usecase "Gérer\nTrésorerie" as UC_Treso
  
  usecase "Consulter Rapports\n• Dashboard • Transactions\n• Agents • Kiosques\n• Statistiques" as UC_Rapports
  
  usecase "Configuration\n• App mobile\n• Opérations agence" as UC_Config
  
  usecase "Consulter Logs\nSystème" as UC_Logs
}

Admin --> UC_Auth
Admin --> UC_Users
Admin --> UC_Agents
Admin --> UC_Soldes
Admin --> UC_Kiosques
Admin --> UC_Affect
Admin --> UC_Trans
Admin --> UC_Oper
Admin --> UC_TypeOper
Admin --> UC_ParamSal
Admin --> UC_Salaires
Admin --> UC_Treso
Admin --> UC_Rapports
Admin --> UC_Config
Admin --> UC_Logs

UC_Auth -[hidden]right-> UC_Users
UC_Users -[hidden]right-> UC_Agents
UC_Agents -[hidden]right-> UC_Soldes
UC_Soldes -[hidden]down-> UC_Kiosques
UC_Kiosques -[hidden]right-> UC_Affect
UC_Affect -[hidden]right-> UC_Trans
UC_Trans -[hidden]right-> UC_Oper
UC_Oper -[hidden]down-> UC_TypeOper
UC_TypeOper -[hidden]right-> UC_ParamSal
UC_ParamSal -[hidden]right-> UC_Salaires
UC_Salaires -[hidden]down-> UC_Treso
UC_Treso -[hidden]right-> UC_Rapports
UC_Rapports -[hidden]right-> UC_Config
UC_Config -[hidden]down-> UC_Logs

@enduml
```

---

## 3. Diagramme de Cas d'Utilisation - Superviseur

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam linetype ortho
skinparam nodesep 80
skinparam ranksep 60

rectangle "Système PDV CONNECT" {
  
  actor "Superviseur" as Superviseur #9b59b6
  
  usecase "Authentification\n• Connexion\n• Déconnexion" as UC_Auth
  
  usecase "Consulter\nAgents" as UC_ConsultAgents
  
  usecase "Modifier\nAgent" as UC_ModifAgent
  
  usecase "Consulter\nSoldes Agents" as UC_Soldes
  
  usecase "Consulter\nKiosques" as UC_ConsultKiosques
  
  usecase "Modifier\nKiosque" as UC_ModifKiosque
  
  usecase "Affecter Agent\nà Kiosque" as UC_Affect
  
  usecase "Gérer Transactions\n• Consulter\n• Exporter" as UC_Trans
  
  usecase "Suivre Performance\n• Agents\n• Kiosques" as UC_Perf
  
  usecase "Alertes et\nNotifications" as UC_Alertes
  
  usecase "Consulter Rapports\n• Dashboard supervision\n• Transactions • Agents\n• Kiosques • Statistiques" as UC_Rapports
  
  usecase "Opérations\nAgence" as UC_Operations
}

Superviseur --> UC_Auth
Superviseur --> UC_ConsultAgents
Superviseur --> UC_ModifAgent
Superviseur --> UC_Soldes
Superviseur --> UC_ConsultKiosques
Superviseur --> UC_ModifKiosque
Superviseur --> UC_Affect
Superviseur --> UC_Trans
Superviseur --> UC_Perf
Superviseur --> UC_Alertes
Superviseur --> UC_Rapports
Superviseur --> UC_Operations

UC_Auth -[hidden]right-> UC_ConsultAgents
UC_ConsultAgents -[hidden]right-> UC_ModifAgent
UC_ModifAgent -[hidden]right-> UC_Soldes
UC_Soldes -[hidden]down-> UC_ConsultKiosques
UC_ConsultKiosques -[hidden]right-> UC_ModifKiosque
UC_ModifKiosque -[hidden]right-> UC_Affect
UC_Affect -[hidden]right-> UC_Trans
UC_Trans -[hidden]down-> UC_Perf
UC_Perf -[hidden]right-> UC_Alertes
UC_Alertes -[hidden]right-> UC_Rapports
UC_Rapports -[hidden]down-> UC_Operations

@enduml
```

---

## 4. Diagramme de Cas d'Utilisation - Comptable

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam linetype ortho
skinparam nodesep 80
skinparam ranksep 60

rectangle "Système PDV CONNECT" {
  
  actor "Comptable" as Comptable #16a085
  
  usecase "Authentification\n• Connexion\n• Déconnexion" as UC_Auth
  
  usecase "Consulter Données\n• Transactions\n• Agents • Kiosques\n• Soldes" as UC_Consult
  
  usecase "Consulter\nSalaires" as UC_ConsultSal
  
  usecase "Valider Paiements\nSalaires" as UC_ValiderSal
  
  usecase "Gérer Trésorerie\n• Enregistrer mouvement\n• Consulter mouvements" as UC_Treso
  
  usecase "Rapports Financiers\n• Transactions • Salaires\n• Trésorerie • Commissions\n• Bilan financier" as UC_Rapports
  
  usecase "Exporter Données\n• Excel • PDF\n• Transactions • Rapports" as UC_Export
  
  usecase "Dashboard Financier\n• Statistiques\n• Indicateurs" as UC_Dashboard
  
  usecase "Consulter\nOpérations Agence" as UC_Operations
}

Comptable --> UC_Auth
Comptable --> UC_Consult
Comptable --> UC_ConsultSal
Comptable --> UC_ValiderSal
Comptable --> UC_Treso
Comptable --> UC_Rapports
Comptable --> UC_Export
Comptable --> UC_Dashboard
Comptable --> UC_Operations

UC_Auth -[hidden]right-> UC_Consult
UC_Consult -[hidden]right-> UC_ConsultSal
UC_ConsultSal -[hidden]right-> UC_ValiderSal
UC_ValiderSal -[hidden]down-> UC_Treso
UC_Treso -[hidden]right-> UC_Rapports
UC_Rapports -[hidden]right-> UC_Export
UC_Export -[hidden]right-> UC_Dashboard
UC_Dashboard -[hidden]down-> UC_Operations

@enduml
```

---

## 5. Diagramme de Cas d'Utilisation - Agent

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam linetype ortho
skinparam nodesep 80
skinparam ranksep 60

rectangle "Système PDV CONNECT" {
  
  actor "Agent" as Agent #f39c12
  
  usecase "Authentification\n• Connexion\n• Déconnexion" as UC_Auth
  
  usecase "Gérer Profil\n• Consulter\n• Modifier\n• Changer mot de passe" as UC_Profil
  
  usecase "Créer Transactions\n• Dépôt • Retrait\n• Transfert" as UC_CreateTrans
  
  usecase "Consulter Transactions\n• Mes transactions\n• Historique" as UC_ConsultTrans
  
  usecase "Consulter Soldes\n• Espèces • Virtuels\n• Total • Historique" as UC_Soldes
  
  usecase "Opérations Clients\n• Recharge compte\n• Retrait • Transfert\n• Paiement facture" as UC_Operations
  
  usecase "Dashboard Agent\n• Statistiques\n• Commissions\n• Mon kiosque" as UC_Dashboard
  
  usecase "Consulter Salaires\n• Mes salaires\n• Historique paiements" as UC_Salaires
  
  usecase "Générer Rapports\n• Journalier\n• Mensuel" as UC_Rapports
}

Agent --> UC_Auth
Agent --> UC_Profil
Agent --> UC_CreateTrans
Agent --> UC_ConsultTrans
Agent --> UC_Soldes
Agent --> UC_Operations
Agent --> UC_Dashboard
Agent --> UC_Salaires
Agent --> UC_Rapports

UC_Auth -[hidden]right-> UC_Profil
UC_Profil -[hidden]right-> UC_CreateTrans
UC_CreateTrans -[hidden]right-> UC_ConsultTrans
UC_ConsultTrans -[hidden]down-> UC_Soldes
UC_Soldes -[hidden]right-> UC_Operations
UC_Operations -[hidden]right-> UC_Dashboard
UC_Dashboard -[hidden]right-> UC_Salaires
UC_Salaires -[hidden]down-> UC_Rapports

@enduml
```

---

## 6. Diagramme de Cas d'Utilisation - Application Mobile (Service Automatisé)

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam linetype ortho
skinparam nodesep 80
skinparam ranksep 60

rectangle "Système PDV CONNECT" {
  
  actor "Application\nMobile" as AppMobile #34495e
  
  usecase "Créer Transaction\nDépôt (Auto)" as UC_Depot
  
  usecase "Créer Transaction\nRetrait (Auto)" as UC_Retrait
  
  usecase "Envoyer Données\nau Backend" as UC_Send
}

AppMobile --> UC_Depot
AppMobile --> UC_Retrait
AppMobile --> UC_Send

UC_Depot -[hidden]right-> UC_Retrait
UC_Retrait -[hidden]right-> UC_Send

@enduml
```

### Caractéristiques de l'Application Mobile

**Type d'acteur:** Service automatisé (acteur secondaire)

**Rôle principal:** 
- Service dédié à la création automatique de transactions de **dépôt** et **retrait**
- Envoie les données de transaction au backend Laravel

**Fonctionnement:**
- Crée automatiquement les transactions lorsqu'un agent effectue un dépôt ou un retrait
- Transmet les informations au système backend via API
- Simplifie le processus de saisie pour les agents sur le terrain

---

## Matrice des Cas d'Utilisation par Acteur

| Cas d'Utilisation | Super Admin | Admin | Superviseur | Comptable | Agent | App Mobile |
|-------------------|:-----------:|:-----:|:-----------:|:---------:|:-----:|:----------:|
| **Gestion Utilisateurs** |
| Créer utilisateur | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Modifier utilisateur | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Supprimer utilisateur | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Consulter utilisateurs | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Gestion Profils** |
| Créer profil | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Modifier profil | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assigner permissions | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Gestion Agents** |
| Créer agent | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Modifier agent | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Supprimer agent | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Consulter agents | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Gestion Kiosques** |
| Créer kiosque | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Modifier kiosque | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Supprimer kiosque | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Consulter kiosques | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Gestion Transactions** |
| Créer transaction | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Créer transaction dépôt (auto) | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Créer transaction retrait (auto) | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Envoyer données au backend | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Consulter transactions | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Exporter transactions | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Gestion Opérateurs** |
| Créer opérateur | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Modifier opérateur | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Supprimer opérateur | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Gestion Salaires** |
| Créer paramètre salaire | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Générer salaires | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Payer salaires | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Consulter salaires | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| **Gestion Trésorerie** |
| Gérer trésorerie | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Enregistrer mouvement | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| **Rapports** |
| Dashboard général | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Dashboard agent | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Rapports transactions | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Rapports financiers | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| **Configuration** |
| Config app mobile | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Logs système | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Opérations agence | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## Légende

- ✅ : Accès complet
- ⚠️ : Accès limité (consultation uniquement ou actions restreintes)
- ❌ : Pas d'accès

---

## Relations entre Cas d'Utilisation

### Relations d'inclusion (<<include>>)

- **Créer transaction** <<include>> Vérifier solde agent
- **Créer transaction** <<include>> Mettre à jour solde
- **Générer salaires** <<include>> Calculer commissions
- **Payer salaires** <<include>> Créer mouvement trésorerie

### Relations d'extension (<<extend>>)

- **Créer agent** <<extend>> Créer kiosque (optionnel)
- **Consulter transactions** <<extend>> Exporter transactions
- **Consulter rapports** <<extend>> Exporter PDF/Excel

---

## Notes

### Acteurs humains
1. **Super Admin** a un accès complet à toutes les fonctionnalités du système
2. **Admin** a un accès similaire au Super Admin mais ne peut pas supprimer certaines entités critiques
3. **Superviseur** se concentre sur la supervision des agents et kiosques
4. **Comptable** gère les aspects financiers (salaires, trésorerie, rapports)
5. **Agent** a un accès limité aux fonctionnalités liées à ses opérations quotidiennes

### Acteur système
6. **Application Mobile** est un service automatisé (acteur secondaire) qui :
   - Crée automatiquement les transactions de **dépôt** et **retrait**
   - Envoie les données de transaction au backend Laravel
   - Simplifie le processus de saisie pour les agents sur le terrain

### Relations entre acteurs
- **Agent** utilise l'**Application Mobile** pour créer rapidement des transactions de dépôt et retrait
- L'**Application Mobile** envoie les données au backend géré par les **Admins**
- Les **Superviseurs** et **Comptables** consultent les transactions créées via l'**Application Mobile**
- La **Configuration App Mobile** est gérée par les **Super Admin** et **Admin**

---

**Version:** 1.1  
**Dernière mise à jour:** 26 mars 2026  
**Auteur:** Système PDV CONNECT
