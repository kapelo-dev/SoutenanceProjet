#!/bin/bash
set -e

# Render injecte PORT (ex: 10000) — Apache écoute par défaut sur 80
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
  sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
  echo "Apache configuré sur le port ${PORT}."
fi

cd /var/www/html

# Render définit RENDER_EXTERNAL_URL automatiquement
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# APP_KEY Render (generateValue) n'est pas au format Laravel base64:...
if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -q '^base64:'; then
  echo "Génération APP_KEY Laravel..."
  php artisan key:generate --force --no-interaction
fi

# Attendre MySQL avec la base cible (pas seulement le host)
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
  echo "En attente de MySQL ($DB_HOST / $DB_DATABASE)..."
  max_attempts="${DB_WAIT_ATTEMPTS:-60}"
  attempt=0
  until php -r "
    new PDO(
      'mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}',
      '${DB_USERNAME}',
      '${DB_PASSWORD}',
      [PDO::ATTR_TIMEOUT => 5]
    );
  " 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge "$max_attempts" ]; then
      echo "ERREUR: MySQL inaccessible ($DB_HOST / $DB_DATABASE) après ${max_attempts} tentatives."
      echo "Vérifiez: accès distant AlwaysData, DB_PASSWORD sur Render, base $DB_DATABASE existante."
      exit 1
    fi
    sleep 2
  done
  echo "MySQL prêt."
fi

# Migrations obligatoires
php artisan migrate --force --no-interaction

# Nettoyer avant cache (évite config stale)
php artisan config:clear --no-interaction || true
php artisan route:clear --no-interaction || true
php artisan view:clear --no-interaction || true

php artisan config:cache --no-interaction
# route:cache incompatible avec les closures dans routes/web.php
php artisan view:cache --no-interaction || true

php artisan storage:link 2>/dev/null || true

exec "$@"
