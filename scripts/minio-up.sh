#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ENV_FILE="${1:-.env.minio}"

if [ ! -f "$ENV_FILE" ]; then
  cp .env.minio.example "$ENV_FILE"
  echo "→ Fichier $ENV_FILE créé depuis .env.minio.example"
  echo "→ Éditez les mots de passe puis relancez :"
  echo "  bash scripts/minio-up.sh"
  exit 1
fi

docker compose -f docker-compose.minio.yml --env-file "$ENV_FILE" up -d
docker compose -f docker-compose.minio.yml --env-file "$ENV_FILE" ps

echo ""
echo "MinIO démarré."
echo "  API S3  : port ${MINIO_API_PORT:-9000}"
echo "  Console : port ${MINIO_CONSOLE_PORT:-9001}"
echo "  Pare-feu : autoriser le port 9000 uniquement depuis l'IP du VPS 1"
