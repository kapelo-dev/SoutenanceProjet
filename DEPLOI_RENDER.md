# Déploiement Render — gratuit (sans carte bancaire)

## MySQL sur Render en gratuit ?

**Non.** Un MySQL fiable sur Render nécessite :
- un service **privé** (`pserv`) — plan payant (à partir de ~6 $/mois)
- un **disque persistant** (`/var/lib/mysql`) — payant aussi

Sans disque, les données MySQL sont **effacées** à chaque redémarrage → inutilisable.

Render propose du **Postgres gratuit** (30 jours), pas du MySQL — et ton app est en MySQL.

**Conclusion :** on laisse tomber MySQL sur Render. Base gratuite = **AlwaysData** (config actuelle).

## Pourquoi pas MinIO + Cron sur Render ?

Même raison : services privés + disque ou cron = **carte bancaire**.

Le plan **free** ne couvre qu’**un Web Service** (qui s’endort après inactivité).

## Architecture gratuite

| Composant | Solution |
|-----------|----------|
| **App** | Render Web `plan: free` |
| **MySQL** | AlwaysData (gratuit) — déjà dans `render.yaml` |
| **Sauvegardes MinIO** | Désactivées (`BACKUP_ENABLED=false`) |
| **Cron / scheduler** | Non (pas de plan free) |

Pour MySQL + MinIO + cron **sans carte** → utilise le **VPS Docker** : [`DEPLOI_VPS.md`](DEPLOI_VPS.md)

## Déploiement (une fois)

1. [dashboard.render.com](https://dashboard.render.com) → **New** → **Blueprint**
2. Connecter le repo
3. Render demande **une seule valeur** : `DB_PASSWORD` (mot de passe AlwaysData)
4. Valider — **pas de carte** si seul le service web free est créé

## Après le 1er déploiement

Chaque **`git push`** redéploie l’app (`autoDeployTrigger: commit`).

Au démarrage : migrations + seed → `admin@pdvconnect.com` / `password123`

## AlwaysData — prérequis

- Base `kapelo_pdvconnect` existante
- **Accès MySQL distant** activé (hôte autorisé ou `%`)
- Hôte Render : souvent `%` ou l’IP sortante Render (voir logs si connexion refusée)

## Limites du free Render

- Cold start ~30–60 s après inactivité
- Pas de sauvegarde auto vers MinIO
- Sessions/cache en **fichier** (pas de table `sessions` requise)

## Changer la base MySQL

Éditer dans `render.yaml` : `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, puis push.

---

## Application mobile Android (APK public)

Render définit automatiquement `APP_URL` via `RENDER_EXTERNAL_URL`.

| Page | URL |
|------|-----|
| Installation | `https://VOTRE-SERVICE.onrender.com/app-mobile` |
| APK direct | `https://VOTRE-SERVICE.onrender.com/downloads/pdv-connect.apk` |

### Inclure l'APK dans le déploiement Render

L'APK doit être **dans le dépôt Git** au moment du build Docker :

```bash
cd android-app && ./build-apk.sh
git add -f public/downloads/pdv-connect.apk
git commit -m "Publish mobile APK for /app-mobile"
git push
```

Render reconstruit l'image → l'APK est copié dans `public/downloads/pdv-connect.apk`.

> **Important :** sans ce fichier dans Git, la page `/app-mobile` affichera « APK non disponible ».

### Mettre à jour l'APK sur Render

1. Rebuild : `cd android-app && ./build-apk.sh`
2. `git add -f public/downloads/pdv-connect.apk`
3. `git push` → redéploiement auto

Optionnel : incrémenter `MOBILE_APK_VERSION` dans `render.yaml`.
