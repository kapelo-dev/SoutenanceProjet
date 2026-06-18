#!/usr/bin/env bash
# Copie l'APK Android vers public/downloads/pdv-connect.apk (URL publique /app-mobile)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEST="public/downloads/pdv-connect.apk"
GRADLE_FILE="android-app/app/build.gradle.kts"
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
    touch "$DEST"
    echo "APK publié : $DEST ← $src ($(du -h "$DEST" | cut -f1))"
    if [[ -f "$GRADLE_FILE" ]]; then
      VC=$(grep -E 'versionCode\s*=' "$GRADLE_FILE" | head -1 | grep -oE '[0-9]+' || true)
      VN=$(grep -E 'versionName\s*=' "$GRADLE_FILE" | head -1 | sed -E 's/.*"([^"]+)".*/\1/' || true)
      if [[ -n "$VC" ]]; then
        echo ""
        echo "→ Mettez à jour le serveur (Render / .env) :"
        echo "   MOBILE_APK_VERSION_CODE=$VC"
        echo "   MOBILE_APK_MIN_VERSION_CODE=$VC"
        [[ -n "$VN" ]] && echo "   MOBILE_APK_VERSION=$VN"
      fi
    fi
    exit 0
  fi
done

echo "Aucun APK trouvé. Générez-le d'abord :" >&2
echo "  cd android-app && ./build-apk.sh" >&2
exit 1
