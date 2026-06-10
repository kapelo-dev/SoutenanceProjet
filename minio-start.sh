#!/usr/bin/env bash
# Démarre MinIO (docker-compose.minio.yml) — VPS 2
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

COMPOSE_FILE="docker-compose.minio.yml"
ENV_FILE=".env.minio"

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

prompt() {
  local key="$1"
  local label="$2"
  local default="${3:-}"
  local current
  current="$(get_env "$key")"
  [[ -z "$current" || "$current" == change-me* ]] && current="$default"
  local hint=""
  [[ -n "$current" ]] && hint=" [$current]"
  read -rp "${label}${hint}: " REPLY
  REPLY="${REPLY:-$current}"
  set_env "$key" "$REPLY"
}

if [[ ! -f "$ENV_FILE" ]]; then
  if [[ ! -f .env.minio.example ]]; then
    echo "Erreur : .env.minio.example introuvable." >&2
    exit 1
  fi
  cp .env.minio.example "$ENV_FILE"
  echo "→ .env.minio créé depuis .env.minio.example"
fi

echo ""
echo "=== MinIO (VPS 2) — sauvegardes S3 ==="
echo "Ces identifiants doivent être identiques à MINIO_ACCESS_KEY / MINIO_SECRET_KEY sur le VPS 1."
echo ""

prompt "MINIO_ROOT_USER" "MINIO_ROOT_USER (clé d'accès)" "pdvconnect-minio"
prompt "MINIO_ROOT_PASSWORD" "MINIO_ROOT_PASSWORD (secret)" "change-me-minio-secret"
prompt "MINIO_BUCKET" "MINIO_BUCKET" "pdvconnect-backups"
prompt "MINIO_API_PORT" "Port API S3" "9000"
prompt "MINIO_CONSOLE_PORT" "Port console web" "9001"

echo ""
echo "=== Démarrage Docker ==="
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d

echo ""
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps

API_PORT="$(get_env MINIO_API_PORT)"
CONSOLE_PORT="$(get_env MINIO_CONSOLE_PORT)"
HOST_IP="$(hostname -I 2>/dev/null | awk '{print $1}' || echo localhost)"

echo ""
echo "MinIO démarré."
echo "  API S3  : http://${HOST_IP}:${API_PORT:-9000}  ← MINIO_ENDPOINT (pas le port 9001)"
echo "  Console : http://${HOST_IP}:${CONSOLE_PORT:-9001}  (navigateur uniquement)"
echo "  Bucket  : $(get_env MINIO_BUCKET)"
echo ""
echo "Sur le VPS 1, utilisez dans ./docker-start.sh :"
echo "  MINIO_ENDPOINT=http://${HOST_IP}:${API_PORT:-9000}"
echo "  MINIO_ACCESS_KEY=$(get_env MINIO_ROOT_USER)"
echo "  MINIO_SECRET_KEY=$(get_env MINIO_ROOT_PASSWORD)"
echo "  MINIO_BUCKET=$(get_env MINIO_BUCKET)"
