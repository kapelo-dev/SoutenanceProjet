#!/bin/bash
set -e

# Render injecte PORT (ex: 10000) — Apache écoute par défaut sur 80
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
  sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
  echo "Apache configuré sur le port ${PORT}."
fi

# Attendre que MySQL soit prêt (docker-compose / Render)
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
  echo "En attente de MySQL sur $DB_HOST..."
  max_attempts="${DB_WAIT_ATTEMPTS:-60}"
  attempt=0
  until php -r "new PDO('mysql:host=$DB_HOST;port=${DB_PORT:-3306}', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge "$max_attempts" ]; then
      echo "MySQL indisponible après ${max_attempts} tentatives."
      exit 1
    fi
    sleep 2
  done
  echo "MySQL prêt."
fi

cd /var/www/html

# Render définit RENDER_EXTERNAL_URL automatiquement
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Générer la clé si absente
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
  php artisan key:generate --force
fi

# Migrations (sans échouer si déjà à jour)
php artisan migrate --force --no-interaction || true

# Cache config et routes en production
php artisan config:cache --no-interaction || true
php artisan route:cache --no-interaction || true
php artisan view:cache --no-interaction || true

# Lien symbolique storage -> public si pas fait
php artisan storage:link 2>/dev/null || true

exec "$@"
