#!/bin/bash
set -e

# APP_KEY valide = base64: + 16 ou 32 octets décodés (AES-128/256)
ensure_app_key() {
  # Render generateValue : souvent base64 brut sans préfixe « base64: »
  if [ -n "${APP_KEY:-}" ] && ! echo "$APP_KEY" | grep -q '^base64:'; then
    export APP_KEY="base64:${APP_KEY}"
  fi
  if ! php -r '
$key = getenv("APP_KEY") ?: "";
if (!str_starts_with($key, "base64:")) exit(1);
$raw = base64_decode(substr($key, 7), true);
if ($raw === false || !in_array(strlen($raw), [16, 32], true)) exit(1);
exit(0);
'; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY généré pour cette instance."
  fi
}

# Render Cron Job : php artisan schedule:run (sans Apache ni migrations)
if [ "$1" = "php" ] && [ "$2" = "artisan" ]; then
  cd /var/www/html
  ensure_app_key
  exec "$@"
fi

# VPS scheduler (docker-compose.vps.yml)
if [ "${RUN_MODE:-}" = "scheduler" ]; then
  cd /var/www/html
  ensure_app_key
  if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    php docker/wait-for-db.php || exit 1
  fi
  echo "Scheduler actif (schedule:run toutes les 60 s)..."
  while true; do
    php artisan schedule:run --no-interaction
    sleep 60
  done
fi

# Render injecte PORT (ex: 10000)
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
  sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
  echo "Apache configuré sur le port ${PORT}."
fi

cd /var/www/html

# Ne jamais laisser public/hot actif en prod (force le dev server Vite → CSS absent)
rm -f public/hot

# .env requis par certains artisan (key:generate écrit dedans)
if [ ! -f .env ]; then
  touch .env
  chown www-data:www-data .env
fi

if [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ ! -f public/build/manifest.json ]; then
  echo "ERREUR: public/build/manifest.json absent — le build Vite npm n'a pas été copié dans l'image."
  echo "Vérifiez le stage Docker frontend (npm run build) et redéployez avec « Clear build cache »."
  exit 1
fi

# APP_KEY : ne pas utiliser key:generate (écrit .env, échoue souvent sur Render)
ensure_app_key

DB_READY=true
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
  echo "Connexion MySQL ($DB_HOST / $DB_DATABASE)..."
  if ! php docker/wait-for-db.php; then
    DB_READY=false
    echo "WARN: base inaccessible — démarrage sans migrations."
  fi
fi

if [ "$DB_READY" = true ] && [ "$SKIP_MIGRATIONS" != "true" ]; then
  if php artisan migrate --force --no-interaction; then
    echo "Migrations OK."
  else
    echo "WARN: migrations échouées — vérifiez les logs ci-dessus."
    php artisan migrate:status 2>/dev/null || true
  fi
fi

# Seed (profils, liens, admin…) — idempotent, sûr à relancer à chaque démarrage
if [ "$DB_READY" = true ] && [ "$SKIP_SEEDING" != "true" ]; then
  if php artisan db:seed --force --no-interaction; then
    echo "Seed OK."
  else
    echo "WARN: seed échoué — lancez: php artisan db:seed --force"
  fi
fi

php artisan config:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

php artisan config:cache --no-interaction 2>/dev/null || echo "WARN: config:cache ignoré"
php artisan view:cache --no-interaction 2>/dev/null || true
php artisan storage:link 2>/dev/null || true

exec "$@"
