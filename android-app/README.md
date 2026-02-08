# PDV Connect SMS — Application Android

Service Android sans interface métier : lecture des SMS de transaction (Mobile Money) et envoi vers l’API de la plateforme PDV Connect.

## Prérequis

- **Android Studio** (Hedgehog 2023.1.1 ou plus récent) ou ligne de commande avec JDK 17+
- **Gradle 8.2** (le wrapper est configuré dans `gradle/wrapper/gradle-wrapper.properties`)

## Ouverture du projet

1. Ouvrir **Android Studio** → **Open** → sélectionner le dossier `android-app`.
2. Laisser Android Studio synchroniser le projet (téléchargement des dépendances et du wrapper si besoin).
3. Si le wrapper n’est pas présent : **File → Settings → Build → Gradle** → utiliser **Gradle wrapper** ; ou en ligne de commande : `./gradlew wrapper` (depuis `android-app`).

## Configuration côté serveur (Laravel)

1. Dans le fichier **`.env`** du projet Laravel, ajouter :
   ```env
   SMS_API_TOKEN=votre-token-secret-long-et-imprevisible
   SMS_DEFAULT_AGENT_ID=1
   SMS_DEFAULT_OPERATEUR_ID=1
   ```
2. L’endpoint utilisé par l’app est : **`POST /api/transactions/from-sms`** avec l’en-tête **`Authorization: Bearer <SMS_API_TOKEN>`**.

## Utilisation de l’application

1. **Installer** l’APK sur un appareil (ou émulateur) qui recevra les SMS de transaction.
2. **Lancer** l’application → un seul écran : **Confidentialité et paramètres**.
3. **Accepter** la case « J’accepte que l’application lise les SMS de transaction… ».
4. **Activer** « Service de transfert SMS activé ».
5. **Renseigner** :
   - **URL de l’API** : ex. `https://votredomaine.com` (sans `/api` à la fin).
   - **Token API** : la valeur fournie par l’admin (Configuration App Mobile dans Laravel).
   - **Filtres SMS** : plusieurs lignes possibles via « + Ajouter une ligne ». Chaque ligne = un numéro (ex. `+22507123456`, `8282`) ou un nom de discussion (ex. `FLOOZ`). L’app ne traite que les SMS dont l’expéditeur ou le contenu correspond à l’un de ces filtres. Vide = accepter tous les SMS (déconseillé en production).
   - **Code d’accès à la configuration** : code fourni par l’admin ; une fois enregistré, l’ouverture de l’app demandera ce code pour accéder aux paramètres.
6. **Enregistrer** → le service démarre. Les SMS entrants (correspondant aux filtres) sont parsés et envoyés à l’API.

## Permissions

- **RECEIVE_SMS** / **READ_SMS** : lecture des SMS pour extraire les transactions.
- **INTERNET** : envoi des données vers l’API.
- **POST_NOTIFICATIONS** (Android 13+) : notification du service en premier plan.
- **FOREGROUND_SERVICE** : exécution du service en arrière-plan.

## Format des SMS pris en charge

Le parseur extrait notamment :
- **Montant** : séquences du type `5 000 FCFA`, `5000 FCFA`, `montant: 10000`, etc.
- **Type** : dépôt / retrait / transfert / paiement selon les mots-clés (reçu, retrait, transfert, paiement, etc.).
- **Référence**, **téléphone client**, **nom client** si présents dans le texte.

Les formats réels dépendent des opérateurs Mobile Money. Vous pouvez adapter les regex dans `SmsParser.kt` selon les SMS reçus.

## Tester l’envoi de SMS (émulateur ou téléphone)

Voir **[TEST_SMS.md](TEST_SMS.md)** pour les outils et la procédure : émulateur (Extended Controls ou `adb emu sms send`), deuxième téléphone pour SMS réel, et script `scripts/send-test-sms.sh` qui envoie des SMS type Mix/FLOOZ à l’émulateur.

## Build

- **Debug** : `./gradlew assembleDebug` → APK dans `app/build/outputs/apk/debug/`.
- **Release** : configurer la signature dans `app/build.gradle.kts` puis `./gradlew assembleRelease`.

### Générer l’APK pour installation sur téléphone Android

1. **Prérequis sur la machine de build**
   - **JDK 17** (ou plus) installé.
   - **Android SDK** : soit via [Android Studio](https://developer.android.com/studio) (recommandé), soit [outils en ligne de commande](https://developer.android.com/studio#command-tools).  
   - Variable d’environnement **`ANDROID_HOME`** (ou `ANDROID_SDK_ROOT`) pointant vers le SDK :
     - Linux/macOS (Android Studio) : souvent `$HOME/Android/Sdk`
     - Exemple : `export ANDROID_HOME=$HOME/Android/Sdk`

2. **Lancer la génération de l’APK**
   - Depuis le dossier **`android-app`** :
     ```bash
     ./gradlew assembleDebug
     ```
   - Ou utiliser le script fourni :
     ```bash
     ./build-apk.sh
     ```

3. **Récupérer l’APK**
   - Fichier généré : **`app/build/outputs/apk/debug/app-debug.apk`**
   - Copier ce fichier sur le téléphone (USB, cloud, etc.) et l’installer (autoriser l’installation depuis « sources inconnues » si demandé).

4. **Version Release (optionnel, pour distribution)**
   - Configurer la signature dans `app/build.gradle.kts` (keystore, mots de passe).
   - Puis : `./gradlew assembleRelease`  
   - APK : `app/build/outputs/apk/release/app-release.apk`

## Structure du projet

- `app/src/main/java/com/pdvconnect/smsservice/`
  - **api/** : client Retrofit, interface `TransactionApi`, DTO.
  - **data/** : `AppPreferences` (DataStore).
  - **sms/** : `SmsReceiver`, `SmsParser`, `SmsForwarderService`.
  - **ui/** : `MainActivity` (écran unique de paramètres / confidentialité).
- `app/src/main/AndroidManifest.xml` : permissions, receiver SMS, service, activité.

## Sécurité

- Communiquer avec l’API **uniquement en HTTPS**.
- Ne pas partager le token API (`SMS_API_TOKEN`).
- En production, restreindre les filtres SMS (numéros ou noms) pour ne traiter que les SMS attendus.
- Le code d’accès à la configuration protège l’écran des paramètres ; il est défini dans Laravel (Configuration App Mobile).
