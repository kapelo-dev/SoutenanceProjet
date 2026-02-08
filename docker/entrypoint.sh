#!/bin/bash
set -e

# Attendre que MySQL soit prêt (quand utilisé avec docker-compose)
if [ -n "$DB_HOST" ]; then
  echo "En attente de MySQL sur $DB_HOST..."
  while ! php -r "new PDO('mysql:host=$DB_HOST;port=${DB_PORT:-3306}', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
    sleep 2
  done
  echo "MySQL prêt."
fi

cd /var/www/html

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
