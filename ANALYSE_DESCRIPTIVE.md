# 1.2. ANALYSE DESCRIPTIVE

## 1.2.1. Présentation du thème

Dans un contexte marqué par l'expansion rapide des services de mobile money au Togo et la nécessité croissante d'optimiser la gestion opérationnelle des agents et des points de vente, une société spécialisée dans les services financiers mobiles a exprimé le besoin de moderniser ses outils de gestion afin d'améliorer l'efficacité de ses opérations et la traçabilité de ses activités. C'est dans cette dynamique que s'inscrit notre projet de stage, qui porte sur la **« CONCEPTION ET RÉALISATION D'UNE PLATEFORME WEB DE GESTION DE MOBILE MONEY : RÉALISÉ AU SEIN DE SOS SOFT OPTIMUM SERVICE »**.

Le projet consiste au développement d'une application web de gestion permettant à la société cliente de centraliser la gestion de ses agents, de ses kiosques, de ses transactions et de ses opérations de mobile money, tout en offrant des outils d'analyse et de suivi en temps réel. Cette plateforme constitue ainsi un outil stratégique de pilotage et d'aide à la décision pour l'institution.

L'objectif principal de cette plateforme est de faciliter la gestion quotidienne des activités de mobile money, d'optimiser le suivi des affectations des agents dans les kiosques, de garantir la traçabilité complète des transactions, de sécuriser les données sensibles et de fournir des tableaux de bord analytiques permettant une vision globale de la performance. Cette initiative répond à la volonté de la société cliente de moderniser ses processus internes et d'améliorer la qualité de ses services à travers les outils numériques.

## 1.2.2. Problématique

La gestion efficace des opérations de mobile money au sein d'une institution financière revêt une importance capitale. Elle constitue un véritable levier de performance aussi bien pour la direction que pour les agents de terrain. Un système de gestion performant permet à l'institution de renforcer le contrôle de ses opérations, de mieux comprendre les besoins de ses agents et d'y répondre de manière plus ciblée et efficace.

Dans un contexte marqué par une évolution rapide des technologies de l'information et de la communication, la digitalisation devient un levier stratégique incontournable pour les acteurs du mobile money. Elle contribue non seulement à améliorer la traçabilité et la sécurité des opérations, mais également à optimiser les processus internes, notamment ceux liés à la gestion des agents, au suivi des affectations dans les kiosques, à la gestion des soldes et à l'analyse des performances.

C'est dans cette optique que se pose la problématique suivante :

**Comment concevoir et mettre en œuvre une plateforme web de gestion de mobile money permettant à la société cliente d'optimiser la gestion de ses agents et kiosques, de garantir la traçabilité complète des transactions, de suivre l'historique des affectations, de sécuriser les données sensibles, tout en fournissant des outils d'analyse et de pilotage en temps réel ?**

## 1.2.3. Objectifs visés

### 1.2.3.1. Objectif général

L'objectif général de ce projet est de concevoir, développer et mettre en œuvre une plateforme web de gestion de mobile money performante et sécurisée pour la société cliente, destinée à centraliser la gestion des agents, des kiosques, des transactions et des opérations financières. Cette plateforme vise à améliorer l'efficacité opérationnelle, à garantir la traçabilité complète des activités, à optimiser le suivi des affectations des agents dans les kiosques, et à fournir des outils d'analyse et de pilotage permettant une prise de décision éclairée, afin d'accompagner efficacement la stratégie de croissance et d'inclusion financière de l'institution.

### 1.2.3.2. Objectifs spécifiques

De manière spécifique, ce projet vise à :

- **Mettre en place un système de gestion des agents** permettant l'enregistrement, la modification et le suivi des agents de mobile money, incluant leurs informations personnelles, leurs soldes et leurs performances ;

- **Développer un module de gestion des kiosques** permettant de créer, localiser géographiquement (via coordonnées GPS) et gérer les points de vente, avec suivi de leur capacité et de leur statut ;

- **Implémenter un système de gestion des affectations** permettant d'assigner les agents aux kiosques et de tracer l'historique complet des affectations (date de début, date de fin, durée) ;

- **Mettre en œuvre un module de gestion des transactions** permettant d'enregistrer, de valider et de suivre toutes les opérations de mobile money (dépôts, retraits, transferts) avec calcul automatique des commissions ;

- **Développer un système de gestion des soldes** permettant de suivre en temps réel les soldes des agents (espèces, soldes virtuels par opérateur) et de gérer les opérations en agence (apports, retraits) ;

- **Intégrer des cartes géographiques interactives** permettant de visualiser la localisation des kiosques et d'analyser les performances par zone géographique ;

- **Mettre en place des tableaux de bord analytiques** permettant de suivre les indicateurs clés de performance (nombre de transactions, chiffres d'affaires, commissions, agents actifs, etc.) ;

- **Développer un système de gestion des utilisateurs et des rôles** assurant l'administration des comptes, la gestion des profils et des permissions d'accès ;

- **Assurer la sécurité, la performance et la fiabilité du système**, en conformité avec les exigences d'une institution financière traitant des données sensibles.

## 1.2.4. Résultats attendus

Les résultats attendus à la fin de ce projet sont les suivants :

- **Gestion complète des agents** : Création, modification, consultation et suivi des agents avec leurs informations et statistiques ;

- **Gestion des kiosques** : Enregistrement des kiosques avec localisation GPS, gestion de la capacité et visualisation sur carte ;

- **Historique des affectations** : Traçabilité complète des affectations des agents dans les kiosques avec dates de début et de fin ;

- **Gestion des transactions** : Enregistrement et suivi de toutes les opérations de mobile money avec validation et calcul des commissions ;

- **Gestion des soldes** : Suivi en temps réel des soldes des agents (espèces et virtuels) par opérateur ;

- **Tableaux de bord analytiques** : Visualisation des performances globales (transactions du jour/mois, chiffres d'affaires, agents actifs, kiosques actifs) ;

- **Cartes géographiques** : Visualisation de la répartition géographique des kiosques et analyse des performances par zone ;

- **Système d'authentification sécurisé** : Gestion des utilisateurs avec profils et permissions différenciés ;

- **Exports et rapports** : Génération de rapports et exports de données (PDF, Excel) pour analyse et archivage.

## 1.2.5. Étude de l'existant

Afin de mieux comprendre le fonctionnement actuel de la société cliente et d'identifier les insuffisances du système en place, une série d'entretiens a été réalisée auprès des responsables opérationnels et des agents de terrain. Cette analyse a permis de mettre en évidence les pratiques actuelles suivantes :

### Gestion des agents

Les informations relatives aux agents (identité, contacts, affectations) sont enregistrées dans des fichiers Excel dispersés entre différents services. Cette méthode ne permet pas une centralisation efficace des données ni un suivi en temps réel des performances.

### Gestion des kiosques

Les kiosques sont répertoriés dans des documents Word ou Excel sans système de géolocalisation. Il n'existe pas de vue cartographique permettant de visualiser leur répartition géographique ni d'analyser les zones à forte ou faible activité.

### Affectation des agents

Les affectations des agents dans les kiosques sont gérées manuellement, sans historique structuré. Il est difficile de retracer les affectations passées d'un agent ou de connaître la durée moyenne d'affectation dans un kiosque.

### Gestion des transactions

Les transactions sont enregistrées dans des cahiers physiques ou des fichiers Excel par chaque agent. La consolidation des données se fait manuellement en fin de journée ou de semaine, ce qui entraîne des risques d'erreurs et de pertes d'informations.

### Suivi des soldes

Les soldes des agents (espèces et virtuels) sont suivis manuellement dans des cahiers ou des fichiers Excel. Il n'existe pas de système automatisé permettant de calculer les soldes en temps réel après chaque transaction.

### Analyse des performances

Les rapports de performance sont élaborés manuellement à partir des données collectées, ce qui nécessite un temps considérable et limite la réactivité de la direction dans la prise de décision.

## 1.2.6. Critique de l'existant

L'analyse du fonctionnement actuel de la société cliente met en évidence plusieurs insuffisances qui freinent l'optimisation de la gestion opérationnelle et la prise de décision stratégique.

### Absence de centralisation des données

Les informations relatives aux agents, aux kiosques et aux transactions sont dispersées dans de multiples fichiers Excel et documents Word, sans base de données centralisée. Cette situation rend difficile l'accès à l'information, augmente les risques de perte de données et complique la consolidation des rapports.

### Manque de traçabilité des affectations

Il n'existe pas de système permettant de suivre l'historique des affectations des agents dans les kiosques. Cette lacune empêche d'analyser la stabilité des agents, d'identifier les kiosques à forte rotation et de prendre des décisions éclairées en matière de gestion des ressources humaines.

### Gestion manuelle des transactions

L'enregistrement manuel des transactions dans des cahiers ou des fichiers Excel est source d'erreurs, de fraudes potentielles et de pertes d'informations. La consolidation des données est longue et fastidieuse, retardant la production des rapports de performance.

### Absence de géolocalisation des kiosques

Sans système de cartographie, il est impossible de visualiser la répartition géographique des kiosques, d'identifier les zones sous-desservies ou saturées, et d'optimiser le déploiement des ressources sur le terrain.

### Suivi des soldes non automatisé

Le calcul manuel des soldes après chaque transaction est sujet à erreurs et ne permet pas un contrôle en temps réel. Cette situation expose l'institution à des risques de découverts non détectés ou de fraudes.

### Absence d'outils d'analyse et de pilotage

Il n'existe pas de tableaux de bord permettant de suivre en temps réel les indicateurs clés de performance (nombre de transactions, chiffres d'affaires, commissions, agents actifs). La direction doit attendre les rapports manuels pour avoir une vision de l'activité, ce qui limite la réactivité.

### Risques de sécurité des données

Les données sensibles (informations personnelles des agents, montants des transactions, soldes) sont stockées dans des fichiers non sécurisés, accessibles à plusieurs personnes sans contrôle d'accès strict, ce qui expose l'institution à des risques de fuites ou de manipulations.

## 1.2.7. Proposition de solutions

### 1.2.7.1. Évaluation technique des solutions

#### 1.2.7.1.1. Première solution : Utilisation d'un logiciel de gestion existant (ERP générique)

La première solution proposée consiste à déployer un logiciel de gestion existant de type ERP (Enterprise Resource Planning) générique, tel qu'Odoo ou Dolibarr, en l'adaptant aux besoins spécifiques de la gestion de mobile money.

##### Description

Odoo et Dolibarr sont des solutions ERP open source modulaires permettant de gérer différents aspects d'une entreprise (comptabilité, ressources humaines, ventes, stocks, etc.). Ces solutions offrent une certaine flexibilité grâce à leurs modules et peuvent être personnalisées via des plugins ou des développements complémentaires.

##### Avantages

- **Déploiement relativement rapide** : Les modules de base sont déjà développés et peuvent être configurés en quelques semaines ;
- **Coût initial modéré** : Solutions open source gratuites, avec des coûts limités aux licences de modules premium et à l'hébergement ;
- **Fonctionnalités de base disponibles** : Gestion des utilisateurs, des contacts, des transactions financières de base ;
- **Communauté active** : Support et documentation disponibles en ligne.

##### Inconvénients

- **Inadaptation aux spécificités du mobile money** : Les ERP généralistes ne sont pas conçus pour gérer les particularités du mobile money (gestion des soldes virtuels par opérateur, commissions spécifiques, affectations dans les kiosques) ;
- **Absence de géolocalisation avancée** : Pas de cartographie interactive pour visualiser les kiosques et analyser les performances par zone ;
- **Personnalisation complexe et coûteuse** : L'adaptation aux besoins spécifiques nécessite des développements complémentaires importants, augmentant les coûts et les délais ;
- **Lourdeur du système** : Les ERP sont souvent surdimensionnés pour les besoins réels, avec de nombreuses fonctionnalités inutiles qui alourdissent l'interface ;
- **Dépendance aux mises à jour** : Les mises à jour de l'ERP peuvent rendre incompatibles les personnalisations développées ;
- **Absence de traçabilité spécifique des affectations** : Pas de module dédié au suivi de l'historique des affectations des agents dans les kiosques.

#### 1.2.7.1.2. Deuxième solution : Développement d'une plateforme web sur mesure

La seconde solution proposée consiste au développement d'une plateforme web sur mesure, conçue spécifiquement pour répondre aux besoins métiers de la société cliente dans le domaine du mobile money.

##### Avantages

La solution applicative développée permettra de répondre de manière efficace et ciblée aux besoins de la société cliente. Elle offrira les fonctionnalités suivantes :

- **Gestion complète des agents** : Module dédié permettant d'enregistrer, de modifier et de suivre les agents avec leurs informations personnelles, leurs soldes, leurs statistiques de performance et leur historique d'affectations ;

- **Gestion des kiosques avec géolocalisation** : Module permettant de créer et gérer les kiosques avec leurs coordonnées GPS, leur capacité, leur statut, et de les visualiser sur une carte interactive (Leaflet/OpenStreetMap) ;

- **Historique complet des affectations** : Système de traçabilité permettant d'enregistrer automatiquement chaque affectation d'agent dans un kiosque avec date de début, date de fin, durée et commentaires ;

- **Gestion des transactions** : Module permettant d'enregistrer toutes les transactions (dépôts, retraits, transferts) avec calcul automatique des commissions, validation et suivi en temps réel ;

- **Gestion des soldes** : Système de suivi automatisé des soldes des agents (espèces et soldes virtuels par opérateur) avec mise à jour en temps réel après chaque transaction ;

- **Tableaux de bord analytiques** : Dashboards interactifs affichant les indicateurs clés (transactions du jour/mois, chiffres d'affaires, commissions, agents actifs, kiosques actifs) avec graphiques et cartes ;

- **Cartes de performance** : Visualisation géographique des kiosques avec cercles proportionnels au chiffre d'affaires, permettant d'identifier les zones performantes ;

- **Système de gestion des utilisateurs et des rôles** : Gestion fine des permissions d'accès selon les profils (administrateur, superviseur, agent, etc.) ;

- **Exports et rapports** : Génération automatique de rapports (PDF, Excel) pour analyse et archivage ;

- **Sécurité renforcée** : Authentification sécurisée, chiffrement des données sensibles, journalisation des actions critiques.

##### Inconvénients

Dans un souci d'équilibre, il est important de reconnaître que toute solution, aussi efficace soit-elle, comporte également des inconvénients. Dans le cas présent, les défis à considérer sont :

- **Coût de développement initial plus élevé** : Le développement sur mesure nécessite un investissement initial supérieur à l'utilisation d'un logiciel existant ;
- **Délai de développement** : La conception et le développement d'une solution sur mesure prennent plus de temps qu'une simple configuration d'ERP ;
- **Nécessité de compétences techniques spécialisées** : Maintenance et évolutions futures nécessitent des développeurs maîtrisant les technologies utilisées (Laravel, MySQL, JavaScript) ;
- **Besoin de formation des utilisateurs** : Le personnel devra être formé à l'utilisation de la nouvelle plateforme.

### 1.2.7.2. Évaluation financière des solutions proposées

#### 1.2.7.2.1. Première solution : ERP générique

##### Coût du logiciel

| Aspect | Description | Coût estimé (FCFA) |
|--------|-------------|-------------------|
| Hébergement web | Serveur VPS professionnel | 100 000 - 300 000 / an |
| Licences modules premium | Modules spécialisés pour personnalisation | 150 000 - 500 000 / an |
| Nom de domaine | Enregistrement et renouvellement | 10 000 - 20 000 / an |

##### Coût de personnalisation et maintenance

| Aspect | Description | Coût estimé (FCFA) |
|--------|-------------|-------------------|
| Personnalisation | Adaptation aux besoins spécifiques mobile money | 1 500 000 - 3 000 000 |
| Formation des utilisateurs | Formation du personnel | 300 000 - 600 000 |
| Support technique | Assistance annuelle | 200 000 - 400 000 / an |
| Maintenance et mises à jour | Mises à jour et corrections | 300 000 - 500 000 / an |

##### Coût total estimé (première solution)

| Désignation | Montant (FCFA) |
|-------------|----------------|
| Hébergement annuel | 150 000 |
| Licences modules premium | 300 000 |
| Personnalisation | 2 000 000 |
| Formation | 400 000 |
| Maintenance annuelle | 400 000 |
| **TOTAL (première année)** | **3 250 000 F CFA** |

#### 1.2.7.2.2. Deuxième solution : Développement sur mesure

##### Coût de la conception et du développement

| Description | Coût Horaire (FCFA) | Nombre d'heures/Jours | Nombre de développeurs | Montant (FCFA) |
|-------------|---------------------|----------------------|------------------------|----------------|
| Conception et développement | 5 000 | 7h x 90j | 1 | 3 150 000 |

##### Coût d'hébergement

| Désignation | Description | Coût mensuel (FCFA) | Coût annuel (FCFA) |
|-------------|-------------|---------------------|-------------------|
| Hébergement | OVHcloud VPS | 15 000 | 180 000 |

##### Coût de la formation

| Description | Coût Horaire (FCFA) | Nombre d'heures | Montant (FCFA) |
|-------------|---------------------|-----------------|----------------|
| Formation du personnel | 10 000 | 12 | 120 000 |

##### Coût matériel

La société cliente dispose déjà des équipements nécessaires (ordinateurs, routeurs, imprimantes). Le coût matériel sera donc de **0 FCFA**.

##### Coût total estimé (deuxième solution)

| Désignation | Montant (FCFA) |
|-------------|----------------|
| Conception et développement | 3 150 000 |
| Hébergement annuel | 180 000 |
| Formation | 120 000 |
| Coût matériel | 0 |
| **TOTAL** | **3 450 000 F CFA** |

## 1.2.8. Justification et description de la solution retenue

Après analyse comparative des options, la **deuxième solution (développement sur mesure)** est jugée la plus pertinente. Bien que son coût initial soit légèrement supérieur, elle offre des avantages décisifs :

- **Adéquation parfaite aux besoins métiers** : Toutes les fonctionnalités sont conçues spécifiquement pour la gestion de mobile money ;
- **Flexibilité et évolutivité** : Possibilité d'ajouter de nouvelles fonctionnalités selon les besoins futurs ;
- **Performance optimale** : Système léger et rapide, sans fonctionnalités inutiles ;
- **Sécurité renforcée** : Contrôle total sur la sécurité et la protection des données ;
- **Indépendance technologique** : Pas de dépendance à un éditeur tiers pour les mises à jour.

### Spécifications fonctionnelles

| ACTEURS | DESCRIPTION | ACTIONS |
|---------|-------------|---------|
| **Administrateur** | Responsable informatique avec vue globale sur le système | • S'authentifier<br>• Gestion des utilisateurs et des profils<br>• Gestion des agents<br>• Gestion des kiosques<br>• Gestion des transactions<br>• Gestion des opérateurs<br>• Consultation des tableaux de bord<br>• Génération de rapports<br>• Toutes les fonctionnalités du système |
| **Superviseur** | Responsable opérationnel | • S'authentifier<br>• Consultation des agents et kiosques<br>• Affectation des agents aux kiosques<br>• Consultation des transactions<br>• Consultation des tableaux de bord<br>• Génération de rapports |
| **Gestionnaire** | Personnel de gestion quotidienne | • S'authentifier<br>• Enregistrement des transactions<br>• Gestion des soldes des agents<br>• Consultation de l'historique des affectations<br>• Consultation des statistiques |
| **Agent** | Agent de terrain (dashboard spécifique) | • S'authentifier<br>• Consultation de ses propres transactions<br>• Consultation de son solde<br>• Consultation de son historique d'affectations |

## 1.2.9. Planning prévisionnel de réalisation

L'établissement d'un planning est indispensable pour la réalisation d'un tel projet. Le tableau suivant présente les différentes phases :

| ACTIVITÉS | DATE DÉBUT | DATE FIN | DURÉE (EN JOURS) |
|-----------|------------|----------|------------------|
| Prise de contact avec SOS Soft Optimum Service | 01/09/2025 | 02/09/2025 | 2 |
| Prise de connaissance du thème et du contexte client | 03/09/2025 | 05/09/2025 | 3 |
| Compréhension du thème et identification des besoins fonctionnels | 08/09/2025 | 15/09/2025 | 8 |
| Élaboration et validation du cahier des charges | 16/09/2025 | 30/09/2025 | 15 |
| Analyse, conception et modélisation du système (UML) | 01/10/2025 | 20/10/2025 | 20 |
| Apprentissage des outils et technologies (Laravel, Leaflet, etc.) | 21/10/2025 | 10/11/2025 | 21 |
| Développement de la plateforme | 11/11/2025 | 25/01/2026 | 76 |
| Tests fonctionnels et corrections | 26/01/2026 | 05/02/2026 | 11 |
| Formation des utilisateurs | 06/02/2026 | 10/02/2026 | 5 |
| Déploiement en production | 11/02/2026 | 15/02/2026 | 5 |
| Suivi post-déploiement et ajustements | 16/02/2026 | 28/02/2026 | 13 |
