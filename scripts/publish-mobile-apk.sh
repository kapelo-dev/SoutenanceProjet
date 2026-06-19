#!/usr/bin/env bash
# Copie l'APK Android vers public/downloads/pdv-connect.apk (URL publique /app-mobile)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEST="public/downloads/pdv-connect.apk"
VERSION_JSON="public/downloads/pdv-connect.version.json"
mkdir -p public/downloads

# Release en priorité — le debug peut rester obsolète après un bump de version.
SOURCES=(
  "android-app/app/build/outputs/apk/release/app-release.apk"
  "android-app/app/build/outputs/apk/release/app-release-unsigned.apk"
  "android-app/app/build/outputs/apk/debug/app-debug.apk"
)

read_apk_version() {
  local apk_path="$1"
  local meta_dir meta_file
  meta_dir="$(dirname "$apk_path")"
  meta_file="$meta_dir/output-metadata.json"

  if [[ -f "$meta_file" ]]; then
  python3 - "$meta_file" <<'PY'
import json, sys
data = json.load(open(sys.argv[1]))
el = data.get("elements", [{}])[0]
print(f"{el.get('versionCode', '')}\n{el.get('versionName', '')}")
PY
    return
  fi

  if command -v aapt >/dev/null 2>&1; then
    aapt dump badging "$apk_path" | awk -F"'" '/versionCode=/{vc=$2} /versionName=/{vn=$2} END{print vc; print vn}'
    return
  fi

  echo ""
}

if [[ -f "$DEST" && "${1:-}" != "--force" ]]; then
  echo "APK déjà présent : $DEST ($(du -h "$DEST" | cut -f1))"
  exit 0
fi

for src in "${SOURCES[@]}"; do
  if [[ -f "$src" ]]; then
    mapfile -t VERSION_INFO < <(read_apk_version "$src")
    VC="${VERSION_INFO[0]:-}"
    VN="${VERSION_INFO[1]:-}"

    if [[ -z "$VC" ]]; then
      echo "Impossible de lire versionCode depuis $src" >&2
      continue
    fi

    cp "$src" "$DEST"
    touch "$DEST"
    cat > "$VERSION_JSON" <<EOF
{"version_code":$VC,"version_name":"${VN:-$VC}"}
EOF

    echo "APK publié : $DEST ← $src ($(du -h "$DEST" | cut -f1))"
        echo "Version : $VN (code $VC) → $VERSION_JSON"
        echo ""
        echo "→ Commit + push pour déployer sur Render (version auto, sans MAJ .env) :"
        echo "   git add public/downloads/pdv-connect.apk public/downloads/pdv-connect.version.json"
        echo "   git commit -m \"chore(mobile): APK v${VN:-$VC}\""
        echo "   git push"
    exit 0
  fi
done

echo "Aucun APK trouvé. Générez-le d'abord :" >&2
echo "  cd android-app && ./gradlew assembleRelease" >&2
exit 1
