# Tester la réception des SMS et l’envoi vers l’API

Pour vérifier que l’app mobile reçoit bien un SMS (Mix / FLOOZ) et envoie la transaction à l’endpoint Laravel, vous pouvez utiliser l’un des outils suivants.

---

## 1. Émulateur Android (recommandé pour le dev)

### Avec l’interface (Extended Controls)

1. Lancez l’émulateur depuis Android Studio (AVD).
2. Cliquez sur les **trois points** (⋯) à droite de la barre de l’émulateur.
3. Onglet **Phone**.
4. Dans **From**, mettez un numéro d’expéditeur (ex. `8282` pour FLOOZ, ou `91069102`).
5. Dans le champ **Message**, collez un message type (voir exemples ci‑dessous).
6. Cliquez sur **Send Message**.

L’émulateur reçoit le SMS ; si l’app PDV Connect SMS est installée, le receiver le traite et envoie la requête vers votre API (à condition que l’URL et le token soient configurés dans l’app).

### En ligne de commande (ADB)

Connectez l’émulateur puis exécutez :

```bash
# Un seul SMS (échappez les guillemets si besoin)
adb -e emu sms send 8282 "Txn ID 040228895463 01/11/2025
retrait valider par le client FABIO ,79984409
Veillez remettre l'argent au client
Montant: 2 000,00 FCFA Commission Net : 24,32 FCFA
Code Agent: 5150328
Date:01/11/2025 12:47:22. Nouveau solde FLOOZ : 28 703,00 FCFA."
```

- `-e` cible l’émulateur (si un seul appareil).
- Remplacez `8282` par le numéro que vous voulez comme expéditeur.
- Le texte entre guillemets est le corps du SMS (format FLOOZ retrait).

Le script `scripts/send-test-sms.sh` (voir plus bas) envoie des exemples Mix et FLOOZ.

---

## 2. Téléphone réel avec un deuxième téléphone

1. Installez l’APK sur le téléphone qui doit recevoir les SMS (celui qui “joue” l’agent).
2. Configurez l’app : URL de l’API, token, code d’accès, activez le service.
3. Depuis **un autre téléphone**, envoyez un **vrai SMS** au numéro du premier.
4. Contenu du SMS : copiez-collez un des messages types (Mix ou FLOOZ) depuis `message type.txt` (à la racine du projet Laravel).

L’app reçoit le SMS, le parse et envoie la transaction à l’API.  
Si vous utilisez des **filtres** dans l’app (ex. numéro ou “FLOOZ”), soit vous envoyez depuis un numéro autorisé, soit le corps du SMS doit contenir le filtre (ex. le mot “FLOOZ”). Pour tester sans se soucier des filtres, laissez la liste des filtres vide (accepter tous les SMS).

---

## 3. Script d’envoi de SMS de test (émulateur + ADB)

Dans le dossier `android-app/scripts/` vous trouverez un script qui envoie des SMS de test (Mix et FLOOZ) à l’émulateur. Utilisation :

```bash
cd android-app
./scripts/send-test-sms.sh
# ou pour un type précis :
./scripts/send-test-sms.sh flooz-retrait
./scripts/send-test-sms.sh mix-depot
```

---

## Exemples de messages à envoyer

### FLOOZ – Retrait

```
Txn ID 040228895463 01/11/2025
retrait valider par le client FABIO ,79984409
Veillez remettre l'argent au client
Montant: 2 000,00 FCFA Commission Net : 24,32 FCFA
Code Agent: 5150328
Date:01/11/2025 12:47:22. Nouveau solde FLOOZ : 28 703,00 FCFA.
```

### FLOOZ – Dépôt

```
Depot reussi Montant:2600,00 FCFA beneficiaire : 96096844 Date : 01/11/2025 14:13:04
Commission Net : 13,44 FCFA Nouveau solde : 26 103,00 FCFA Txn ID: 040229031027
```

### Mix – Retrait

```
retrait de 4 900 FCFA effectue par 91069102 le 01-11-25 12:36
commission : 21 FCFA votre nouveau solde Mixx : 287734 FCFA (commission incluse) REF 13990966494
```

### Mix – Dépôt

```
Depot de 2 000 FCFA effectue pour 90513298(AHIAKPOR) le 01-11-25 12:45
Commission: 14 FCFA nouveau solde mix : 275 784 (commission incluse) REF 13990436494
```

---

## Vérifier que la transaction arrive bien à l’API

1. **Laravel** : assurez-vous que la route `POST /api/transactions/from-sms` est protégée par le middleware qui vérifie le token (Bearer) et que `Config App Mobile` (ou `.env`) contient le même token que dans l’app.
2. **Logs Laravel** : `storage/logs/laravel.log` après envoi du SMS.
3. **Base de données** : table `transactions` — une nouvelle ligne avec `source = 'sms'` (ou le champ que vous utilisez) après le SMS.
4. **App Android** : dans Logcat, filtrez par `PdvConnectSmsReceiver` ou `PdvConnectSmsForwarder` pour voir si le SMS est reçu et si l’appel API est fait.

En résumé : pour tester vous-même l’envoi de messages, utilisez **l’émulateur** (Extended Controls ou `adb emu sms send`) ou **un deuxième téléphone** qui envoie un vrai SMS ; le corps du message doit être un des exemples ci‑dessus (ou ceux de `message type.txt`) pour que le parseur et l’endpoint enregistrent correctement la transaction.
