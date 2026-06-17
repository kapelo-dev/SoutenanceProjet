#!/usr/bin/env bash
# Script pour générer l'APK debug de PDV Connect SMS (installation sur téléphone Android).
# Usage : depuis le dossier android-app : ./build-apk.sh

set -e
cd "$(dirname "$0")"

# Utiliser un JDK qui contient jlink (requis par le build Android). Java 21 minimal n'a souvent pas jlink.
if [ -z "$JAVA_HOME" ] || [ ! -x "$JAVA_HOME/bin/jlink" ]; then
  for jdk in /usr/lib/jvm/java-17-openjdk-amd64 /usr/lib/jvm/java-1.17.0-openjdk-amd64 "$HOME/Android/Sdk/jbr"; do
    if [ -d "$jdk" ] && [ -x "$jdk/bin/jlink" ]; then
      export JAVA_HOME="$jdk"
      echo "JAVA_HOME défini: $JAVA_HOME (pour jlink)"
      break
    fi
  done
fi

# Définir ANDROID_HOME si pas déjà défini (emplacements courants)
if [ -z "$ANDROID_HOME" ] && [ -z "$ANDROID_SDK_ROOT" ]; then
  for dir in "$HOME/Android/Sdk" "/opt/android-sdk" "$HOME/Library/Android/sdk"; do
    if [ -d "$dir" ]; then
      export ANDROID_HOME="$dir"
      echo "ANDROID_HOME défini: $ANDROID_HOME"
      break
    fi
  done
fi

if [ -z "$ANDROID_HOME" ] && [ -z "$ANDROID_SDK_ROOT" ]; then
  echo "Erreur: ANDROID_HOME (ou ANDROID_SDK_ROOT) n'est pas défini et aucun SDK trouvé."
  echo "Installez Android Studio ou les command-line tools et définissez ANDROID_HOME."
  echo "Exemple: export ANDROID_HOME=\$HOME/Android/Sdk"
  exit 1
fi

echo "Génération de l'APK debug..."
./gradlew assembleDebug "$@"

APK="app/build/outputs/apk/debug/app-debug.apk"
if [ -f "$APK" ]; then
  bash ../scripts/publish-mobile-apk.sh --force || true
  echo ""
  echo "APK généré : $APK"
  echo "Publié pour le web : ../public/downloads/pdv-connect.apk"
  echo "Page d'installation : /app-mobile"
else
  echo "Erreur: APK non trouvé à $APK"
  exit 1
fi
