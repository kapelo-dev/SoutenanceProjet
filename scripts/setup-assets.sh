#!/bin/bash
# Restaure les assets Metronic (CSS/JS/images) requis par layouts/partials/head.blade.php
set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$ROOT/.tmp-metronic"
REPO="https://github.com/keenthemes/metronic-tailwind-html-integration.git"

if [ ! -d "$TMP/metronic-tailwind-django/static/css" ]; then
  echo "Clonage Metronic integration..."
  git clone --depth 1 "$REPO" "$TMP"
fi

mkdir -p "$ROOT/public/assets"
cp -r "$TMP/metronic-tailwind-django/static/css" "$ROOT/public/assets/"
cp -r "$TMP/metronic-tailwind-django/static/js" "$ROOT/public/assets/"
cp -r "$TMP/metronic-tailwind-django/static/vendors" "$ROOT/public/assets/"
cp -r "$TMP/metronic-tailwind-angular/public/assets/media" "$ROOT/public/assets/"

rm -f "$ROOT/public/hot"
echo "Assets Metronic installés dans public/assets/"
