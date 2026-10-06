#!/usr/bin/env bash
# Lance la suite de tests sur un MySQL 8 jetable (Docker), comme en production.
# (SQLite ne convient pas : certaines migrations et requêtes sont propres à MySQL.)
#
# Usage : ./scripts/run-tests.sh [arguments de "php artisan test", ex. --filter=RapportTest]
set -euo pipefail

cd "$(dirname "$0")/.."

NETWORK=pdv-tests
DB=pdv-tests-mysql
IMAGE=pdv-tests-php

cleanup() {
  docker rm -f "$DB" >/dev/null 2>&1 || true
  docker network rm "$NETWORK" >/dev/null 2>&1 || true
}
trap cleanup EXIT

docker network create "$NETWORK" >/dev/null 2>&1 || true
docker run -d --rm --name "$DB" --network "$NETWORK" \
  -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=testing mysql:8.0 >/dev/null

# Image PHP de test (pdo_mysql, zip et gd pour les exports Excel/PDF), construite une seule fois
if ! docker image inspect "$IMAGE" >/dev/null 2>&1; then
  echo "Construction de l'image PHP de test..."
  docker build -q -t "$IMAGE" - >/dev/null <<'DOCKERFILE'
FROM php:8.4-cli
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*
DOCKERFILE
fi

echo "Attente de MySQL..."
for _ in $(seq 1 60); do
  docker exec "$DB" mysql -uroot -proot -e "SELECT 1" testing >/dev/null 2>&1 && break
  sleep 2
done

# --user : les fichiers créés pendant les tests (vues compilées, cache) restent à l'utilisateur courant
docker run --rm --network "$NETWORK" --user "$(id -u):$(id -g)" -v "$PWD":/app -w /app \
  -e DB_CONNECTION=mysql -e DB_HOST="$DB" -e DB_DATABASE=testing \
  -e DB_USERNAME=root -e DB_PASSWORD=root \
  "$IMAGE" php artisan test "$@"
