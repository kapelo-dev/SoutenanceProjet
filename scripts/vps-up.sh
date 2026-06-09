#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
  cp .env.vps.example .env
  echo "→ Fichier .env créé depuis .env.vps.example"
  echo "→ Éditez .env (APP_KEY, mots de passe, MINIO_ENDPOINT = IP VPS 2) puis relancez :"
  echo "  bash scripts/vps-up.sh"
  exit 1
fi

docker compose -f docker-compose.vps.yml up -d --build
docker compose -f docker-compose.vps.yml ps

echo ""
echo "PDV Connect démarré."
echo "  App   : http://$(hostname -I 2>/dev/null | awk '{print $1}' || echo localhost):${APP_PORT:-80}"
echo "  Logs  : docker compose -f docker-compose.vps.yml logs -f app"
