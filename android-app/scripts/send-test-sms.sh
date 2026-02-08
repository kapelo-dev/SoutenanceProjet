#!/usr/bin/env bash
# Envoie un SMS de test à l'émulateur Android (adb) pour tester le flux
# PDV Connect SMS -> API. Usage: ./send-test-sms.sh [flooz-retrait|flooz-depot|mix-retrait|mix-depot]
# Prérequis : émulateur lancé, adb dispo.

set -e
ADB="${ADB:-adb}"

# Cible l'émulateur si plusieurs appareils
if [ "$($ADB devices | grep -c emulator)" -ge 1 ]; then
  ADB="$ADB -e"
fi

case "${1:-all}" in
  flooz-retrait)
    SENDER="8282"
    BODY="Txn ID 040228895463 01/11/2025 retrait valider par le client FABIO ,79984409 Veillez remettre l'argent au client Montant: 2 000,00 FCFA Commission Net : 24,32 FCFA Code Agent: 5150328 Date:01/11/2025 12:47:22. Nouveau solde FLOOZ : 28 703,00 FCFA."
    $ADB emu sms send "$SENDER" "$BODY"
    echo "SMS FLOOZ retrait envoyé (expéditeur: $SENDER)"
    ;;
  flooz-depot)
    SENDER="8282"
    BODY="Depot reussi Montant:2600,00 FCFA beneficiaire : 96096844 Date : 01/11/2025 14:13:04 Commission Net : 13,44 FCFA Nouveau solde : 26 103,00 FCFA Txn ID: 040229031027"
    $ADB emu sms send "$SENDER" "$BODY"
    echo "SMS FLOOZ dépôt envoyé (expéditeur: $SENDER)"
    ;;
  mix-retrait)
    SENDER="8282"
    BODY="retrait de 4 900 FCFA effectue par 91069102 le 01-11-25 12:36 commission : 21 FCFA votre nouveau solde Mixx : 287734 FCFA (commission incluse) REF 13990966494"
    $ADB emu sms send "$SENDER" "$BODY"
    echo "SMS Mix retrait envoyé (expéditeur: $SENDER)"
    ;;
  mix-depot)
    SENDER="8282"
    BODY="Depot de 2 000 FCFA effectue pour 90513298(AHIAKPOR) le 01-11-25 12:45 Commission: 14 FCFA nouveau solde mix : 275 784 (commission incluse) REF 13990436494"
    $ADB emu sms send "$SENDER" "$BODY"
    echo "SMS Mix dépôt envoyé (expéditeur: $SENDER)"
    ;;
  all)
    echo "Envoi de 4 SMS de test (FLOOZ retrait/dépôt, Mix retrait/dépôt)..."
    "$0" flooz-retrait
    sleep 1
    "$0" flooz-depot
    sleep 1
    "$0" mix-retrait
    sleep 1
    "$0" mix-depot
    echo "Terminé. Vérifiez l'app et l'API."
    ;;
  *)
    echo "Usage: $0 [flooz-retrait|flooz-depot|mix-retrait|mix-depot|all]"
    echo "  Envoie un SMS de test à l'émulateur (adb). Lancez l'émulateur avant."
    exit 1
    ;;
esac
