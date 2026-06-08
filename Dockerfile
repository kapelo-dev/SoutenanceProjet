# ========== Stage 1 : build des assets (Vite / Tailwind) ==========
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY tailwind.config.js vite.config.js ./
COPY resources ./resources
COPY public ./public

ENV NODE_ENV=production
RUN npm run build \
    && test -f public/build/manifest.json \
    || (echo "Échec build Vite — manifest.json introuvable" && exit 1)

# ========== Stage 2 : application Laravel ==========
FROM php:8.3-apache

# Extensions PHP nécessaires pour Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache : DocumentRoot vers public/ et AllowOverride pour Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers remoteip

COPY docker/apache-remoteip.conf /etc/apache2/conf-available/render-remoteip.conf
RUN a2enconf render-remoteip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Dépendances PHP (sans dev pour production)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
COPY --from=frontend /app/public/build ./public/build

# Assets Metronic (styles.css, core.bundle.js…) — fallback si absents du repo
RUN if [ ! -f public/assets/css/styles.css ]; then \
      apt-get update && apt-get install -y --no-install-recommends git \
      && rm -rf /var/lib/apt/lists/* \
      && bash scripts/setup-assets.sh; \
    fi \
    && rm -f public/hot

RUN composer dump-autoload --optimize

# Droits pour storage et bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Point d'entrée : migrations + démarrage Apache
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]

# Render injecte $PORT au runtime (entrypoint reconfigure Apache)
EXPOSE 80
