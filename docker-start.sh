#!/usr/bin/env bash
# Démarre PDV Connect (docker-compose.vps.yml) et configure MinIO dans .env
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

COMPOSE_FILE="docker-compose.vps.yml"
ENV_FILE=".env"

# --- helpers ---

get_env() {
  local key="$1"
  if [[ -f "$ENV_FILE" ]] && grep -q "^${key}=" "$ENV_FILE"; then
    grep "^${key}=" "$ENV_FILE" | head -1 | cut -d= -f2-
  fi
}

set_env() {
  local key="$1"
  local val="$2"
  if [[ ! -f "$ENV_FILE" ]]; then
    touch "$ENV_FILE"
  fi
  awk -v k="$key" -v v="$val" '
    BEGIN { done = 0 }
    $0 ~ "^" k "=" { print k "=" v; done = 1; next }
    { print }
    END { if (!done) print k "=" v }
  ' "$ENV_FILE" > "${ENV_FILE}.tmp" && mv "${ENV_FILE}.tmp" "$ENV_FILE"
}

normalize_minio_endpoint() {
  local url="${1%/}"
  if [[ "$url" =~ :[0-9]+$ ]]; then
    echo "$url"
  else
    echo "${url}:9000"
  fi
}

prompt() {
  local key="$1"
  local label="$2"
  local default="${3:-}"
  local current
  current="$(get_env "$key")"
  [[ -z "$current" || "$current" == change-me* || "$current" == *VPS2_IP* ]] && current="$default"
  local hint=""
  [[ -n "$current" ]] && hint=" [$current]"
  read -rp "${label}${hint}: " REPLY
  REPLY="${REPLY:-$current}"
  set_env "$key" "$REPLY"
}

# --- .env de base ---

if [[ ! -f "$ENV_FILE" ]]; then
  if [[ ! -f .env.vps.example ]]; then
    echo "Erreur : .env.vps.example introuvable." >&2
    exit 1
  fi
  cp .env.vps.example "$ENV_FILE"
  echo "→ .env créé depuis .env.vps.example"
fi

echo ""
echo "=== PDV Connect — configuration MinIO (sauvegardes) ==="
echo "Laissez vide pour garder la valeur entre crochets."
echo "Tapez « skip » sur MINIO_ENDPOINT pour désactiver les sauvegardes distantes."
echo "API S3 = port 9000 (pas 9001 qui est la console web)."
echo ""

DEFAULT_ENDPOINT="$(get_env MINIO_ENDPOINT)"
if [[ -z "$DEFAULT_ENDPOINT" || "$DEFAULT_ENDPOINT" == *VPS2_IP* ]]; then
  if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx 'pdvconnect-minio'; then
    DEFAULT_ENDPOINT="http://host.docker.internal:9000"
  else
    DEFAULT_ENDPOINT="http://localhost:9000"
  fi
fi

read -rp "MINIO_ENDPOINT (API S3, port 9000) [$DEFAULT_ENDPOINT]: " MINIO_ENDPOINT
MINIO_ENDPOINT="${MINIO_ENDPOINT:-$DEFAULT_ENDPOINT}"

if [[ "${MINIO_ENDPOINT,,}" == "skip" ]]; then
  set_env "BACKUP_ENABLED" "false"
  set_env "MINIO_ENDPOINT" ""
  echo "→ Sauvegardes MinIO désactivées (BACKUP_ENABLED=false)."
else
  MINIO_ENDPOINT="$(normalize_minio_endpoint "$MINIO_ENDPOINT")"
  set_env "MINIO_ENDPOINT" "$MINIO_ENDPOINT"
  set_env "BACKUP_ENABLED" "true"
  echo "→ MINIO_ENDPOINT=$MINIO_ENDPOINT"

  prompt "MINIO_ACCESS_KEY" "MINIO_ACCESS_KEY (clé d'accès)" "pdvconnect-minio"
  prompt "MINIO_SECRET_KEY" "MINIO_SECRET_KEY (secret)" "change-me-minio-secret"
  prompt "MINIO_BUCKET" "MINIO_BUCKET (nom du bucket)" "pdvconnect-backups"
  prompt "MINIO_REGION" "MINIO_REGION" "us-east-1"
  prompt "MINIO_USE_PATH_STYLE" "MINIO_USE_PATH_STYLE (true/false)" "true"

  MINIO_URL_CURRENT="$(get_env MINIO_URL)"
  read -rp "MINIO_URL (URL publique optionnelle) [${MINIO_URL_CURRENT:-vide}]: " MINIO_URL
  MINIO_URL="${MINIO_URL:-$MINIO_URL_CURRENT}"
  set_env "MINIO_URL" "$MINIO_URL"
fi

echo ""
echo "=== Réseau local ==="
prompt "APP_PORT" "Port HTTP de l'application" "8088"
prompt "APP_URL" "URL publique de l'app" "http://localhost:$(get_env APP_PORT)"

# Mots de passe DB si encore par défaut (uniquement 1er déploiement)
if [[ "$(get_env DB_PASSWORD)" == change-me* ]]; then
  echo ""
  echo "=== MySQL (conteneur Docker) ==="
  if docker volume ls -q 2>/dev/null | grep -qE 'mysql_data|pdvconnect.*mysql'; then
    echo "⚠️  Volume MySQL déjà existant : gardez les mots de passe d'origine"
    echo "   (change-me-strong-db-password / change-me-root-password) ou supprimez le volume."
  fi
  prompt "DB_PASSWORD" "Mot de passe utilisateur MySQL (DB_USERNAME)" "change-me-strong-db-password"
  prompt "MYSQL_ROOT_PASSWORD" "Mot de passe root MySQL" "change-me-root-password"
fi

echo ""
echo "=== Application mobile (APK) ==="
if bash scripts/publish-mobile-apk.sh 2>/dev/null; then
  :
else
  echo "→ APK non copié (générez-le : cd android-app && ./build-apk.sh)"
fi

echo ""
echo "=== Démarrage Docker ==="
docker compose -f "$COMPOSE_FILE" up -d --build

echo ""
docker compose -f "$COMPOSE_FILE" ps

APP_PORT="$(get_env APP_PORT)"
APP_URL="$(get_env APP_URL)"

echo ""
echo "PDV Connect est démarré."
echo "  App    : ${APP_URL:-http://localhost:${APP_PORT:-80}}"
echo "  Mobile : ${APP_URL:-http://localhost:${APP_PORT:-80}}/app-mobile"
echo "  APK    : ${APP_URL:-http://localhost:${APP_PORT:-80}}/downloads/pdv-connect.apk"
echo "  Login  : admin@pdvconnect.com / password123 (1ère connexion → changer le mot de passe)"
echo "  Logs   : docker compose -f $COMPOSE_FILE logs -f app"
if [[ "$(get_env BACKUP_ENABLED)" == "true" ]]; then
  echo "  MinIO  : $(get_env MINIO_ENDPOINT) / bucket $(get_env MINIO_BUCKET)"
fi
