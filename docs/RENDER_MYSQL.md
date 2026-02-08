# Déployer MySQL sur Render et connecter l’app Laravel

Sur Render, tu crées **deux services** : un pour MySQL (base de données), un pour Laravel (Web Service). Ensuite tu connectes l’app à la base avec les variables d’environnement.

---

## Étape 1 : Créer le service MySQL sur Render

1. Va sur [dashboard.render.com](https://dashboard.render.com).
2. **New +** → **Private Service** (ou **Background Worker** selon l’interface ; ce qu’il faut : un service de type **Docker** qui restera toujours sur le même réseau privé).
3. En pratique, Render propose aussi **MySQL** directement :
   - **New +** → **MySQL** (si disponible dans la liste).
   - Ou **New +** → **Web Service** avec **Docker** pour déployer une image MySQL.

### Option A : MySQL géré par Render (si disponible)

- Choisis **Add MySQL** (ou équivalent) dans ton projet.
- Render crée la base et te donne une **Internal Database URL** ou des variables (host, port, user, password, database). Note-les.

### Option B : MySQL via Docker (même repo que Laravel)

Ce projet contient un Dockerfile MySQL dans **`docker/mysql/Dockerfile`**. Tu peux déployer un second service Render qui build cette image :

1. **New +** → **Private Service** (ou **Web Service** en mode Docker, selon ce que Render propose pour un service “base de données”).
2. **Connect repository** : le **même** repo que ton app Laravel (ex. `soutenance_project`).
3. **Settings** :
   - **Environment** : Docker.
   - **Root Directory** : vide (ou le sous-dossier du repo si ton code est dedans).
   - **Dockerfile Path** : `docker/mysql/Dockerfile`.
4. **Environment Variables** (onglet Environment) :
   - `MYSQL_DATABASE` = `laravel`
   - `MYSQL_USER` = `laravel`
   - `MYSQL_PASSWORD` = *génère un mot de passe fort (ex. 32 caractères aléatoires)*
   - `MYSQL_ROOT_PASSWORD` = *autre mot de passe fort*
5. **Advanced** → **Add Disk** :
   - **Mount Path** : `/var/lib/mysql`
   - **Size** : 10 GB minimum (obligatoire pour que les données persistent).
6. **Nom du service** : ex. `pdvconnect-mysql` (ce nom sera le hostname interne).
7. Créer le service et attendre le premier déploiement.
4. **Environment Variables** (à ajouter dans l’onglet **Environment** du service MySQL) :
   - `MYSQL_DATABASE` = `laravel`
   - `MYSQL_USER` = `laravel`
   - `MYSQL_PASSWORD` = *mot de passe fort (génère un secret)*
   - `MYSQL_ROOT_PASSWORD` = *autre mot de passe fort*
5. **Advanced** → **Add Disk** :
   - **Mount Path** : `/var/lib/mysql`
   - **Size** : 10 GB (ou plus si besoin).
6. Donne un **nom** au service, ex. **`pdvconnect-mysql`**. Ce nom sera le **hostname interne** (ex. `pdvconnect-mysql` ou `pdvconnect-mysql.onrender.com` selon Render).
7. **Create Private Service** et attends le premier déploiement.

Une fois le service créé, Render affiche une **Internal URL** (hostname interne), du type :
- `pdvconnect-mysql` ou  
- `pdvconnect-mysql.xxxxx`  
sur le port **3306**. C’est ce hostname que tu utiliseras pour `DB_HOST`.

---

## Étape 2 : Récupérer les infos de connexion MySQL

Dans le dashboard Render, ouvre le service **MySQL** :

- **Internal Hostname** (ou “Internal URL”) : quelque chose comme `pdvconnect-mysql` ou l’URL complète sans `mysql://`.
- **Port** : en général **3306**.
- **Database** : la valeur de `MYSQL_DATABASE` (ex. `laravel`).
- **User** : la valeur de `MYSQL_USER` (ex. `laravel`).
- **Password** : la valeur de `MYSQL_PASSWORD` que tu as définie.

Note ces 5 valeurs pour l’étape 3.

---

## Étape 3 : Configurer le Web Service Laravel

1. Ouvre ton **Web Service** Laravel (celui qui utilise ton image Docker ou ton repo).
2. Va dans **Environment** (variables d’environnement).
3. Ajoute ou modifie :

| Variable       | Valeur |
|----------------|--------|
| `DB_CONNECTION`| `mysql` |
| `DB_HOST`      | **Internal hostname** du service MySQL (ex. `pdvconnect-mysql`) |
| `DB_PORT`      | `3306` |
| `DB_DATABASE`  | même que `MYSQL_DATABASE` (ex. `laravel`) |
| `DB_USERNAME`  | même que `MYSQL_USER` (ex. `laravel`) |
| `DB_PASSWORD`  | même que `MYSQL_PASSWORD` (le mot de passe défini sur le service MySQL) |

Exemple :

```env
DB_CONNECTION=mysql
DB_HOST=pdvconnect-mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=le_mot_de_passe_que_tu_as_met_sur_le_service_mysql
```

4. Ajoute aussi les autres variables nécessaires à Laravel : `APP_KEY`, `APP_URL`, `APP_ENV=production`, etc.
5. **Save Changes** puis **Manual Deploy** (ou attends un redeploy automatique).

---

## Étape 4 : Vérifier que les deux services sont sur le même “groupe” / réseau

Sur Render, les services d’un même **account/team** et d’un même **environnement** peuvent communiquer via le **réseau privé**. L’**Internal Hostname** du service MySQL est alors résolvable depuis le Web Service.

- Si ton MySQL et ton Laravel sont dans le **même projet / même équipe**, utiliser `DB_HOST` = Internal Hostname du MySQL suffit.
- Si Render te demande d’“ajouter un service au groupe” ou “link”, associe le Web Service au service MySQL pour que le hostname interne soit accessible.

---

## Résumé

1. Créer un **service MySQL** sur Render (option MySQL gérée ou Docker avec disque `/var/lib/mysql`).
2. Noter **Internal Hostname**, port **3306**, database, user, password.
3. Dans le **Web Service** Laravel, définir `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
4. Redéployer l’app Laravel.

Après déploiement, Laravel utilisera cette base MySQL via les variables d’env ; les migrations s’exécutent au démarrage du conteneur (entrypoint) si tu as gardé la logique actuelle.
