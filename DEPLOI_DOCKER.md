# Déployer l’application Laravel en Docker (gratuit ou low-cost)

Tu peux **dockeriser** l’application complète (Laravel + MySQL) et la mettre en **production gratuitement** sur plusieurs plateformes.

---

## 1. Ce qui est en place

- **Dockerfile** : image PHP 8.2 + Apache, build des assets (Vite/Tailwind), Composer, migrations au démarrage.
- **docker-compose.prod.yml** : services `app` (Laravel) et `mysql`, avec healthcheck.
- **.dockerignore** : réduit la taille du contexte de build.
- **docker/entrypoint.sh** : attente de MySQL, `key:generate` si besoin, migrations, cache config.

---

## 2. Lancer en production (sur un VPS ou ta machine)

```bash
# 1. Copier et configurer l’environnement
cp .env.example .env
# Éditer .env : APP_KEY, APP_URL, DB_PASSWORD, SMS_API_TOKEN, etc.
php artisan key:generate   # si tu génères la clé en local

# 2. Build et démarrage
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d

# 3. L’app est accessible sur http://localhost (ou APP_PORT si défini)
# Logs : docker compose -f docker-compose.prod.yml logs -f app
```

Variables **.env** utiles pour la prod :

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://ton-domaine.com`
- `DB_PASSWORD=...` (fort, utilisé pour MySQL root et utilisateur Laravel)
- `SMS_API_TOKEN=...` (optionnel, voir ci‑dessous)

**Token API SMS (app mobile)** : tu peux faire **soit** en base, **soit** en .env :
- **En base** : après déploiement, connecte‑toi à l’app → **Configuration App Mobile** → génère un token et enregistre. Ce token (table `config_app_mobile`) est **prioritaire**.
- **En .env** : définir `SMS_API_TOKEN=ton-token-secret`. Utilisé uniquement si aucun token n’est configuré en base. Pratique pour la prod (pas besoin d’ouvrir l’interface).

---

## 3. Hébergement gratuit ou quasi gratuit

Tu peux déployer cette stack Docker sur des offres **gratuites** (souvent avec limites) :

| Plateforme | Offre gratuite | Docker | Idée |
|------------|----------------|--------|------|
| **Railway** | Crédits gratuits / mois | Oui (Dockerfile ou Nixpacks) | Déployer le service `app` + un MySQL Railway ou externe. |
| **Render** | Free tier (spin down après inactivité) | Oui (Dockerfile) | Web Service + base MySQL (payante) ou DB externe gratuite. |
| **Fly.io** | Machines partagées gratuites | Oui (Dockerfile) | `fly launch` puis ajouter une Postgres/MySQL (ou externe). |
| **Oracle Cloud (Always Free)** | 2 VM toujours gratuites | Oui (tu installes Docker) | VPS gratuit : tu fais `docker compose -f docker-compose.prod.yml up -d`. |
| **Coolify** (self‑hosted) | Gratuit (sur ton serveur) | Oui | Tu héberges Coolify sur un VPS, il gère Docker et déploiements. |

En résumé :

- **Le plus simple “tout gratuit”** : une VM **Oracle Cloud Always Free** (ou un autre VPS gratuit), avec Docker installé, puis `docker compose -f docker-compose.prod.yml up -d`.
- **Sans serveur à gérer** : **Railway** ou **Render** avec un Dockerfile (ton app) + une base de données (leur MySQL/Postgres ou une DB gratuite externe).

---

## 4. Exemple : déploiement sur Oracle Cloud (Always Free)

1. Créer un compte Oracle Cloud et une VM Always Free (Ubuntu).
2. Sur la VM :
   ```bash
   sudo apt update && sudo apt install -y docker.io docker-compose-v2
   sudo usermod -aG docker $USER
   # Se reconnecter puis cloner le projet
   git clone <ton-repo> && cd metronic-tailwind-laravel
   cp .env.example .env
   # Éditer .env (APP_KEY, APP_URL, DB_PASSWORD, etc.)
   sudo docker compose -f docker-compose.prod.yml up -d
   ```
3. Ouvrir le port 80 (et 443 si HTTPS) dans le pare-feu Oracle.
4. Optionnel : nom de domaine et certificat (Let’s Encrypt avec Nginx en reverse proxy devant le conteneur).

---

## 5. Exemple : déploiement sur Railway

1. Créer un projet sur [railway.app](https://railway.app).
2. Ajouter un service **MySQL** (ou utiliser une DB externe).
3. Connecter le repo Git, choisir **Dockerfile** comme build.
4. Variables d’environnement : `APP_KEY`, `APP_URL`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (fournis par le service MySQL Railway), `SMS_API_TOKEN`, etc.
5. Pour un **seul** service (Laravel), Railway ne lance que le conteneur `app` : il faut donc une base **externe** (Railway MySQL ou autre) et définir `DB_HOST` / `DB_PORT` en conséquence.

Tu peux aussi déployer **uniquement** l’image buildée avec `docker-compose.prod.yml` sur un service Railway, et utiliser leur MySQL.

---

## 6. Sécurité et prod

- Ne jamais commiter `.env` (déjà dans `.gitignore`).
- En prod : `APP_DEBUG=false`, `APP_ENV=production`.
- Utiliser HTTPS (reverse proxy avec Nginx/Caddy + Let’s Encrypt).
- Changer `DB_PASSWORD` et `SMS_API_TOKEN` en valeurs fortes et uniques.

---

En résumé : **oui**, tu peux dockeriser ton application Laravel complète et la mettre en production **gratuitement** en utilisant le Dockerfile et `docker-compose.prod.yml` fournis, soit sur un VPS gratuit (ex. Oracle Cloud), soit sur des plateformes comme Railway/Render/Fly.io en utilisant leur offre gratuite et une base de données gérée ou externe.
