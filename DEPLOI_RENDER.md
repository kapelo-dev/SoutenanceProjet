# Déploiement Render — push automatique

## Une seule fois (liaison du repo)

1. [dashboard.render.com](https://dashboard.render.com) → **New** → **Blueprint**
2. Connecter le repo Git → Render lit `render.yaml`
3. Valider la création des 4 services (aucune variable à saisir)

Render génère automatiquement : `APP_KEY`, mots de passe MySQL, secret MinIO, `SMS_API_TOKEN`.

## Ensuite : chaque `git push`

Render redéploie automatiquement (`autoDeployTrigger: commit`) :

| Service | Rôle |
|---------|------|
| `pdvconnect-mysql` | MySQL + disque 10 Go |
| `pdvconnect-minio` | Sauvegardes S3 |
| `pdvconnect` | App Laravel (migrate + seed au démarrage) |
| `pdvconnect-scheduler` | Cron (backup 02:00 UTC) |

**Login par défaut** : `admin@pdvconnect.com` / `password123`

## Ce qui est automatique au démarrage du conteneur app

- `APP_KEY` (Render ou entrypoint)
- `APP_URL` via `RENDER_EXTERNAL_URL`
- Migrations + seed (`admin`, profils, permissions)
- Cache config / vues

## Limites

- **Premier déploiement** : lier le Blueprint une fois (impossible à éviter)
- **Plans payants** : MySQL, MinIO, Cron = starter minimum
- **Web free** : peut s'endormir après inactivité (cold start ~30 s)
- **Carte bancaire** : requise sur Render pour les services starter

## Vérifier après le 1er déploiement

```bash
# Logs Render Dashboard → pdvconnect → Logs
# Chercher : Migrations OK. / Seed OK.
```

URL de l'app : `https://pdvconnect.onrender.com` (ou l'URL affichée par Render).
