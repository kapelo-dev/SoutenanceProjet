#!/usr/bin/env bash
# Copie l'APK Android vers public/downloads/pdv-connect.apk (URL publique /app-mobile)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEST="public/downloads/pdv-connect.apk"
mkdir -p public/downloads

SOURCES=(
  "android-app/app/build/outputs/apk/debug/app-debug.apk"
  "android-app/app/build/outputs/apk/release/app-release.apk"
  "android-app/app/build/outputs/apk/release/app-release-unsigned.apk"
)

if [[ -f "$DEST" && "${1:-}" != "--force" ]]; then
  echo "APK déjà présent : $DEST ($(du -h "$DEST" | cut -f1))"
  exit 0
fi

for src in "${SOURCES[@]}"; do
  if [[ -f "$src" ]]; then
    cp "$src" "$DEST"
    echo "APK publié : $DEST ← $src ($(du -h "$DEST" | cut -f1))"
    exit 0
  fi
done

echo "Aucun APK trouvé. Générez-le d'abord :" >&2
echo "  cd android-app && ./build-apk.sh" >&2
exit 1
