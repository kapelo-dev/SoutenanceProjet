# Déploiement sur 2 VPS — PDV Connect

| VPS | Rôle | Fichier Docker |
|-----|------|----------------|
| **VPS 1** | Application Laravel + MySQL + scheduler | `docker-compose.vps.yml` |
| **VPS 2** | MinIO (sauvegardes S3) | `docker-compose.minio.yml` |

**Ordre recommandé :** démarrer le **VPS 2 (MinIO)** en premier, puis le **VPS 1 (app)**.

---

## Prérequis (sur chaque VPS)

```bash
sudo apt update && sudo apt install -y docker.io docker-compose-v2 git
sudo usermod -aG docker $USER
# Se déconnecter / reconnecter pour le groupe docker
git clone <votre-repo> && cd metronic-tailwind-laravel
```

---

## VPS 2 — MinIO (sauvegardes)

**Commande à lancer :**

```bash
./minio-start.sh
```

Le script crée `.env.minio` si besoin, demande les identifiants MinIO, puis démarre Docker.

Les identifiants `MINIO_ROOT_USER` / `MINIO_ROOT_PASSWORD` doivent être **identiques** à `MINIO_ACCESS_KEY` / `MINIO_SECRET_KEY` saisis sur le VPS 1.

*(Alternative manuelle : `cp .env.minio.example .env.minio` puis `docker compose -f docker-compose.minio.yml --env-file .env.minio up -d`)*

- **API S3** (sauvegardes Laravel) : `http://IP_VPS_2:9000` → `MINIO_ENDPOINT`
- **Console** (navigateur) : `http://IP_VPS_2:9001` — ne pas utiliser pour `MINIO_ENDPOINT`

En local (MinIO sur la même machine) : `MINIO_ENDPOINT=http://host.docker.internal:9000`

**Pare-feu (exemple UFW) :**

```bash
sudo ufw allow from IP_VPS_1 to any port 9000
sudo ufw allow 9001/tcp   # console — restreindre à ton IP admin si possible
```

---

## VPS 1 — Application

**Commande à lancer :**

```bash
./docker-start.sh
```

Le script :
- crée `.env` si besoin ;
- demande les variables **MinIO** (`MINIO_ENDPOINT` = `http://IP_VPS_2:9000`, clés, bucket…) ;
- configure le port / l’URL de l’app ;
- lance `docker compose -f docker-compose.vps.yml up -d --build` (migrations + seed automatiques).

**Connexion par défaut :**

| Champ | Valeur |
|-------|--------|
| Identifiant | `admin@pdvconnect.com` |
| Mot de passe | `password123` |

→ Changement de mot de passe obligatoire à la première connexion.

**Logs :**

```bash
docker compose -f docker-compose.vps.yml logs -f app
```

---

## Récap — les 2 commandes

```bash
# VPS 2 (MinIO)
./minio-start.sh

# VPS 1 (App + MySQL)
./docker-start.sh
```

---

## Vérification sauvegardes

Sur le VPS 1, dans `.env` :

```env
MINIO_ENDPOINT=http://IP_VPS_2:9000
MINIO_ACCESS_KEY=...        # = MINIO_ROOT_USER du VPS 2
MINIO_SECRET_KEY=...        # = MINIO_ROOT_PASSWORD du VPS 2
MINIO_BUCKET=pdvconnect-backups
BACKUP_ENABLED=true
```

Puis recréer le conteneur app si `.env` a changé :

```bash
docker compose -f docker-compose.vps.yml up -d app
```

---

## Application mobile Android (APK public)

### URLs (remplacer par votre domaine ou IP)

| Page | URL |
|------|-----|
| Installation (instructions + bouton) | `https://VOTRE_DOMAINE/app-mobile` |
| Téléchargement direct APK | `https://VOTRE_DOMAINE/downloads/pdv-connect.apk` |

Avec `APP_URL=http://IP_VPS:8088` :

- `http://IP_VPS:8088/app-mobile`
- `http://IP_VPS:8088/downloads/pdv-connect.apk`

### Publier / mettre à jour l'APK sur le VPS

```bash
# 1) Générer l'APK (sur une machine avec Android SDK)
cd android-app && ./build-apk.sh

# 2) Copier vers public/downloads (fait automatiquement par build-apk.sh)
#    ou manuellement :
bash scripts/publish-mobile-apk.sh --force

# 3) Sur le VPS : le volume Docker sert ./public/downloads — pas de rebuild nécessaire
docker compose -f docker-compose.vps.yml up -d app
```

Le conteneur monte `./public/downloads` en lecture seule : une fois l'APK copié sur le VPS, il est immédiatement téléchargeable.

### Pare-feu

Le port HTTP de l'app (`APP_PORT`, souvent `80` ou `8088`) doit être ouvert :

```bash
sudo ufw allow 80/tcp
# ou
sudo ufw allow 8088/tcp
```
