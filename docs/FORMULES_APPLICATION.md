# Formules de calcul — PDV Connect

Référence de **toutes les formules** utilisées dans l'application (backend Laravel + affichage Blade).

> **Convention importante**  
> Les transactions **Mobile Money (MM)** ont `type_operation_id = NULL` (scope `commerciale()`).  
> Les **opérations en agence** et **montants initiaux** ont `type_operation_id` renseigné (scope `operationAgence()`) — elles sont **exclues** des totaux MM, commissions agrégées et calcul de salaire.

---

## 1. Montant initial agent (création)

**Fichier :** `AgentController::storeWithKiosque`, `AgentController::storeMontantsInitiaux`

```
montant_initial_total = espece_initiale + Σ montant_virtuel_[operateur_id]
```

- Chaque montant > 0 crée une transaction d'agence (`apport_espece` ou `apport_virtuel`) + un enregistrement `soldes`.
- **Non inclus** dans les statistiques commerciales ni le salaire.

---

## 2. Soldes agent

### 2.1 Solde total agent

**Fichier :** `Agent::soldeTotal()`

```
solde_total = Σ montant des soldes actuels
```

Un solde est « actuel » = dernier enregistrement par couple `(agent_id, type, operateur_id)`.

### 2.2 Solde total affiché (page Soldes)

**Fichier :** `resources/views/pages/agents/solde/index.blade.php`

```
total_virtuel = Σ soldes WHERE type = 'virtuel'
solde_total   = solde_espece + total_virtuel
```

### 2.3 Commissions cumulées agent (page Soldes)

```
commissions = Σ transaction.commission
              WHERE statut = 'valide'
              AND type_operation_id IS NULL
```

---

## 3. Opérations en agence

**Fichier :** `OperationsAgenceController::store`

Mapping type d'opération → type transaction :

| Code type opération | Type transaction |
|---------------------|------------------|
| contient `apport`   | `depot`            |
| contient `retrait`  | `retrait`          |
| autre               | `paiement`         |

### Mise à jour du solde

```
ancien_montant = dernier solde (espece OU virtuel selon opérateur)

SI type = depot  → nouveau_montant = ancien_montant + montant
SI type = retrait → nouveau_montant = ancien_montant - montant
SI type = paiement → nouveau_montant = ancien_montant - montant
```

Types d'opération seedés : `reglement_commission`, `apport_espece`, `retrait_espece`, `apport_virtuel`, `retrait_virtuel`.

---

## 4. Transactions Mobile Money — impact sur les soldes

**Fichier :** `TransactionController::updateAgentBalance`

### Solde virtuel (par opérateur)

```
SI virtual_balance_after est renseigné (> 0)
    nouveau_virtuel = virtual_balance_after
SINON
    nouveau_virtuel = ancien_virtuel + montant
```

> La commission **n'impacte pas** le solde virtuel dans le code actuel.

### Solde espèce (caisse agent)

Uniquement si `type ∈ {depot, retrait}` :

```
nouveau_espece = max(0, ancien_espece - montant)
```

### Annulation d'une transaction

**Fichier :** `TransactionController::reverseAgentBalance`

```
nouveau_virtuel = max(0, ancien_virtuel - montant)

SI type ∈ {depot, retrait}
    nouveau_espece = ancien_espece + montant
```

---

## 5. Commission sur une transaction MM

La commission **n'est pas calculée automatiquement** par l'application.

Elle est :
- saisie manuellement (`TransactionController::store` / `update`), ou
- reçue depuis l'app Android / SMS (`storeFromSms`, champ `commission`).

```
commission_transaction = valeur saisie ou reçue (nullable, ≥ 0)
```

---

## 6. Statistiques transactions (agrégations)

**Filtre commun :** `statut = 'valide'` **ET** `type_operation_id IS NULL` (`commerciale()`)

Utilisé dans : Dashboard, Rapports, Transactions, Opérateurs, Agent dashboard, etc.

### Formules génériques

```
montant_total     = Σ montant
commission_total  = Σ commission
nb_transactions   = COUNT(*)
montant_moyen     = montant_total / nb_transactions   (affichage salaires)
```

### Par période

| Période   | Filtre date                          |
|-----------|--------------------------------------|
| Jour      | `DATE(date) = aujourd'hui`           |
| Mois      | `YEAR(date)` et `MONTH(date)` courants |
| Personnalisé | `date BETWEEN date_debut AND date_fin` |

### Par type de transaction (dashboard jour)

```
montant_depot     = Σ montant WHERE type = 'depot'
montant_retrait   = Σ montant WHERE type = 'retrait'
montant_transfert = Σ montant WHERE type = 'transfert'
montant_paiement  = Σ montant WHERE type = 'paiement'
```

### Top agents (dashboard)

```sql
SUM(transactions.montant) AS total_montant
COUNT(transactions.id)    AS total_transactions
-- JOIN agents, WHERE statut = 'valide', type_operation_id IS NULL, mois courant
ORDER BY total_montant DESC LIMIT 10
```

### Performance kiosque (carte dashboard)

```
montant_kiosque = COALESCE(SUM(transactions.montant), 0)
-- transactions valides, commerciales, mois courant, via agents du kiosque
```

### Affichage dashboard (format)

```
montant_jour_affiché = montant_jour / 1 000 000   → affiché en "M" (millions)
```

**Fichier :** `resources/views/pages/dashboard/index.blade.php`

---

## 7. Calcul des salaires

**Fichier :** `GestionEntrepriseController::genererSalaires`

### Données d'entrée (par agent, par période)

```
transactions = transactions commerciales valides
                 WHERE agent_id = agent
                 AND date BETWEEN date_debut AND date_fin

montant_transactions = Σ transactions.montant
commissions          = Σ transactions.commission
nb_transactions      = COUNT(transactions)
montant_fixe         = parametre.montant_fixe
taux_commission      = parametre.taux_commission   (%)
solde_final          = agent.soldeTotal()
objectif_atteint     = 0   (non implémenté, toujours 0)
```

### Mode A — Formule personnalisée (prioritaire)

Si `parametre.formule` n'est pas vide :

```
montant_total = evaluateFormule(formule, variables)
montant_commission = max(0, montant_total - montant_fixe)
```

**Variables remplaçables dans la formule :**

| Variable              | Description                                      |
|-----------------------|--------------------------------------------------|
| `montant_transactions`| Somme des montants MM de la période               |
| `nb_transactions`     | Nombre de transactions MM                         |
| `commissions`         | Somme des commissions des transactions MM         |
| `montant_fixe`        | Salaire fixe du paramètre                         |
| `taux_commission`     | Taux % du paramètre                               |
| `solde_final`         | Solde total actuel de l'agent                     |
| `objectif_atteint`    | Toujours `0` (réservé, non calculé)               |

**Exemples de formules valides :**

```
montant_fixe + commissions
montant_fixe + (montant_transactions * taux_commission / 100)
(montant_transactions * taux_commission / 100) + montant_fixe
```

**Évaluation :** expression mathématique (`+`, `-`, `*`, `/`, parenthèses) via `evaluateFormuleSalaire()`.

### Mode B — Calcul par défaut (sans formule)

Selon `parametre.type` :

#### Type `fixe`

```
montant_commission = 0
montant_total      = montant_fixe
```

#### Type `commission` ou `mixte`

```
SI base_calcul = 'commissions'
    montant_commission = commissions
SINON
    montant_commission = (montant_transactions × taux_commission) / 100

montant_total = montant_fixe + montant_commission
```

> **Note :** L'interface propose aussi `base_calcul = soldes` et `objectifs`, mais le backend **ne les implémente pas** — ils sont traités comme `transactions` (formule avec taux sur le montant total).

### Enregistrement du salaire

```
montant_bonus     = 0        (fixe)
montant_deduction = 0        (fixe)
montant_total     = selon modes A ou B ci-dessus
periode           = YYYY-MM (mois de date_debut)
statut            = 'en_attente'
```

### Statistiques page Salaires (affichage)

```
total_salaires  = Σ salaire.montant_total
salaire_moyen   = Σ montant_total / COUNT(salaires)
nb_payes        = COUNT WHERE statut = 'paye'
nb_en_attente   = COUNT WHERE statut = 'en_attente'
```

---

## 8. Trésorerie

**Fichier :** `GestionEntrepriseController::index`

Sur la période filtrée `[date_debut, date_fin]` :

```
entrees = Σ mouvement.montant WHERE type = 'entree'
sorties = Σ mouvement.montant WHERE type = 'sortie'
solde   = entrees - sorties
```

### Paiement d'un salaire

**Fichier :** `GestionEntrepriseController::payerSalaire`

Crée un mouvement :

```
type          = 'sortie'
categorie     = 'salaire'
montant       = salaire.montant_total
```

---

## 9. Kiosques

**Fichier :** `Kiosque` model

### Saturation

```
est_sature = (nombre_agents_assignés >= capacite_agents)
```

### Places disponibles

```
places_disponibles = max(0, capacite_agents - nombre_agents_assignés)
```

### Distance entre kiosques (Haversine)

```
angle = 2 × arcsin(√(sin²(Δlat/2) + cos(lat1)×cos(lat2)×sin²(Δlon/2)))
distance_km = angle × 6371
```

---

## 10. Agent dashboard (espace agent)

**Fichier :** `AgentDashboardController::index`

Sur les transactions commerciales de l'agent connecté :

```
today_count       = COUNT valides du jour
today_total       = Σ montant du jour
month_count       = COUNT valides du mois
month_total       = Σ montant du mois
month_commission  = Σ commission du mois
all_count         = COUNT toutes valides
all_total         = Σ montant toutes valides
```

---

## 11. Rapports & exports

Mêmes agrégations que §6, avec filtres optionnels :

- `agent_id`, `operateur_id`, `type`, `statut`, `kiosque_id`
- Période : `date_debut` → `date_fin` (défaut : mois courant)

```
stats_globales.montant_total    = Σ montant (valides, commerciales)
stats_globales.commission_total = Σ commission (valides, commerciales)
stats_par_operateur             = idem, groupé par opérateur
top_agents                      = idem, groupé par agent, tri DESC montant
```

---

## 12. Récapitulatif — quoi inclure où ?

| Calcul                         | Inclut ops agence ? | Inclut montants initiaux ? |
|--------------------------------|---------------------|----------------------------|
| Dashboard / Rapports           | Non                 | Non                        |
| Page Transactions              | Non                 | Non                        |
| Calcul salaire                 | Non                 | Non                        |
| Page Opérations en agence      | Oui (uniquement)    | Oui                        |
| Soldes agent (Solde model)     | Oui (toutes ops)    | Oui                        |
| Commissions page Soldes        | Non                 | Non                        |

---

## 13. Fichiers sources

| Domaine              | Fichier principal |
|----------------------|-------------------|
| Scope commerciale    | `app/Models/Transaction.php` |
| Soldes agent         | `app/Models/Agent.php` |
| Ops agence           | `app/Http/Controllers/OperationsAgenceController.php` |
| Transactions MM      | `app/Http/Controllers/TransactionController.php` |
| Salaires             | `app/Http/Controllers/GestionEntrepriseController.php` |
| Dashboard            | `app/Http/Controllers/DashboardController.php` |
| Rapports             | `app/Http/Controllers/RapportController.php` |
| Paramètres salaire UI| `resources/views/pages/gestion_entreprise/partials/parametres.blade.php` |
| Kiosques             | `app/Models/Kiosque.php` |

---

*Dernière mise à jour : juin 2026 — PDV Connect*
