package com.pdvconnect.smsservice.sms

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertNull
import org.junit.Test

class SmsParserTest {

    @Test
    fun parseDepotMix() {
        val sms = "Dépôt de 5 000 FCFA effectue pour 91316317(DOSSEH SYLVESTRE), le 19-06-26 20:24. Commission: 14 FCFA. Nouveau solde Mixx : 144 FCFA (commission incluse). Ref: 17915718791."
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(5000.0, p!!.montant, 0.01)
        assertEquals("depot", p.type)
        assertEquals(SmsParser.CATEGORY_COMMERCIAL, p.transactionCategory)
    }

    @Test
    fun parseRetraitMix() {
        val sms = "Retrait de 3 000 FCFA effectue par 93036603, le 19-06-26 20:31. Commision: 21 FCFA. Votre nouveau solde Mixx : 3 165 FCFA (commission incluse). Ref: 17915847509."
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(3000.0, p!!.montant, 0.01)
        assertEquals("retrait", p.type)
    }

    @Test
    fun parseEnvoiMix() {
        val sms = "Envoi de 20 000 FCFA au 90769121(TCHA JACQUES), 19-06-26 09:47. Frais: 0 FCFA. Nouveau solde Mixx: 2 386 FCFA. Ref: 17903074495."
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(20000.0, p!!.montant, 0.01)
        assertEquals("depot", p.type)
    }

    @Test
    fun parseApportVirtuel() {
        val sms = "L'agent 10019 (SPT NYEKONAKPOE) vous a envoyé 50 000 FCFA, le 13-05-26 10:45. Votre nouveau solde Mixx : 55 637 FCFA. Ref: 17254672518"
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(50000.0, p!!.montant, 0.01)
        assertEquals(SmsParser.CATEGORY_APPORT_VIRTUEL, p.transactionCategory)
        assertEquals("10019", p.sourceAgentCode)
    }

    @Test
    fun parseApportVirtuelTypographicApostrophe() {
        val sms = "L\u2019agent 10019 (SPT NYEKONAKPOE) vous a envoyé 50 000 FCFA, le 13-05-26 10:45. Ref: 17254672518"
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(SmsParser.CATEGORY_APPORT_VIRTUEL, p!!.transactionCategory)
    }

    @Test
    fun parseOldMixRefWithoutColon() {
        val sms = "retrait de 4 900 FCFA effectue par 91069102 le 01-11-25 12:36 commission : 21 FCFA votre nouveau solde Mixx : 287734 FCFA (commission incluse) REF 13990966494"
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(4900.0, p!!.montant, 0.01)
        assertEquals("13990966494", p.reference)
    }

    @Test
    fun parseDepotWithCommissionAndRef() {
        val sms = "Dépôt de 1 000 FCFA effectue pour 90828645(H Latevi DJODJI), le 17-06-26 23:38. Commission: 14 FCFA. Nouveau solde Mixx : 208 856 FCFA (commission incluse). Ref: 17881049384."
        val p = SmsParser.parse(sms)
        assertNotNull(p)
        assertEquals(1000.0, p!!.montant, 0.01)
        assertEquals("depot", p.type)
        assertEquals("17881049384", p.reference)
        assertEquals(14.0, p.commission!!, 0.01)
        assertEquals("90828645", p.clientTelephone)
    }

    @Test
    fun parseNullWhenNoAmount() {
        assertNull(SmsParser.parse("Bonjour, votre solde a été mis à jour."))
    }

    // --- SMS réels (message type.txt) ---------------------------------------------------------

    @Test
    fun parseVraiRetraitMix() {
        val sms = "retrait de 4 900 FCFA effectue par 91069102 le 01-11-25 12:36\ncommission : 21 FCFA votre nouveau solde Mixx : 287734 FCFA (commission incluse) REF 13990966494"
        val p = SmsParser.parse(sms)!!
        assertEquals("retrait", p.type)
        assertEquals(4900.0, p.montant, 0.01)
        assertEquals(21.0, p.commission!!, 0.01)
        assertEquals(287734.0, p.virtualBalanceAfter!!, 0.01)
        assertEquals("91069102", p.clientTelephone)
        assertEquals("13990966494", p.reference)
        assertEquals("YAS", p.operatorName)
    }

    @Test
    fun parseVraiDepotMixSoldeSansFcfa() {
        val sms = "Depot de 2 000 FCFA effectue pour 90513298(AHIAKPOR) le 01-11-25 12:45 \nCommission: 14 FCFA nouveau solde mix : 275 784 (commission incluse) REF 13990436494"
        val p = SmsParser.parse(sms)!!
        assertEquals("depot", p.type)
        assertEquals(2000.0, p.montant, 0.01)
        assertEquals(14.0, p.commission!!, 0.01)
        assertEquals(275784.0, p.virtualBalanceAfter!!, 0.01) // pas de « FCFA » après ce solde
        assertEquals("90513298", p.clientTelephone)
        assertEquals("AHIAKPOR", p.clientNom)
    }

    @Test
    fun parseVraiRetraitFlooz() {
        val sms = "Txn ID 040228895463 07/02/2025\nretrait valider par le client FABIO ,79984409\nVeillez remettre l'argent au client \nMontant: 2 000,00 FCFA Commission Net : 24,32 FCFA \nCode Agent: 5150328\nDate:07/02/2025 12:47:22. Nouveau solde FLOOZ : 28 703,00 FCFA.\nTxn ID 040228895463\nFacilitez vos transactions en telechargeant l'appli Moov money sur: https://onelink.to/dgg3cs"
        val p = SmsParser.parse(sms)!!
        assertEquals("retrait", p.type)
        assertEquals(2000.0, p.montant, 0.01)
        assertEquals(24.32, p.commission!!, 0.001) // « Commission Net »
        assertEquals(28703.0, p.virtualBalanceAfter!!, 0.01)
        assertEquals("79984409", p.clientTelephone)
        assertEquals("FABIO", p.clientNom)
        assertEquals("040228895463", p.reference)
        assertEquals("5150328", p.agentCode)
        assertEquals("FLOOZ", p.operatorName)
    }

    @Test
    fun parseVraiDepotFlooz() {
        val sms = "Depot reussi Montant:2600,00 FCFA beneficiaire : 96096844 Date : 01/11/2025 14:13:04 \nCommission Net : 13,44 FCFA Nouveau solde : 26 103,00 FCFA Txn ID: 040229031027\nFacilitez vos transactions en telechargeant l'appli Moov money sur: https://onelink.to/dgg3cs"
        val p = SmsParser.parse(sms)!!
        assertEquals("depot", p.type)
        assertEquals(2600.0, p.montant, 0.01)
        assertEquals(13.44, p.commission!!, 0.001)
        assertEquals(26103.0, p.virtualBalanceAfter!!, 0.01)
        assertEquals("96096844", p.clientTelephone)
        assertEquals("040229031027", p.reference)
    }

    // --- Montants --------------------------------------------------------------------------------

    @Test
    fun parseMontantsAvecSeparateurs() {
        assertEquals(5000.0, SmsParser.parseAmount("5.000")!!, 0.001)
        assertEquals(1500.0, SmsParser.parseAmount("1,500")!!, 0.001)
        assertEquals(2000.0, SmsParser.parseAmount("2 000,00")!!, 0.001)
        assertEquals(2000.0, SmsParser.parseAmount("2.000,00")!!, 0.001)
        assertEquals(1234567.0, SmsParser.parseAmount("1 234 567")!!, 0.001)
        assertEquals(24.32, SmsParser.parseAmount("24,32")!!, 0.001)
        assertEquals(2600.5, SmsParser.parseAmount("2600.5")!!, 0.001)
        assertNull(SmsParser.parseAmount("0"))
    }

    @Test
    fun parseDepotMontantAvecPointDesMilliers() {
        val p = SmsParser.parse("Dépôt de 5.000 FCFA effectue pour 91316317(DOSSEH). Ref: 17915718791.")!!
        assertEquals(5000.0, p.montant, 0.01)
    }

    @Test
    fun parseNeConfondPasLeTelephoneAvecLaReference() {
        val sms = "Txn ID 040228895463 Paiement reussi Montant: 1 500 FCFA. Moov money"
        val p = SmsParser.parse(sms)!!
        assertEquals("paiement", p.type)
        assertNull(p.clientTelephone) // et non « 0402288954 », extrait de la référence
    }

    // --- SMS refusés -----------------------------------------------------------------------------

    @Test
    fun refuseSmsPublicitaire() {
        assertNull(SmsParser.parse("MIXX by YAS : profitez de 500 FCFA de bonus sur votre prochain depot ! Offre valable jusqu'au 31/10."))
    }

    @Test
    fun refuseSmsPersonnelAvecMontant() {
        assertNull(SmsParser.parse("Salut, tu peux m'envoyer 2 000 FCFA ce soir ? Merci"))
    }

    @Test
    fun refuseSmsSansTypeReconnu() {
        // Référence présente mais aucun mot-clé d'opération : plus de « transfert » par défaut
        assertNull(SmsParser.parse("Votre facture n° : 88213 de 12 500 FCFA est disponible."))
    }

    @Test
    fun refuseFloozSansReference() {
        assertNull(SmsParser.parse("Moov money : depot de bonus 1 000 FCFA offert, composez *155#"))
    }

    @Test
    fun typeDuPremierMotCle() {
        // « retrait » apparaît avant « reçu » : c'est un retrait
        val sms = "Txn ID 040228895999 retrait valider par le client KOFI ,90112233 montant reçu : Montant: 3 000,00 FCFA Nouveau solde FLOOZ : 10 000,00 FCFA."
        assertEquals("retrait", SmsParser.parse(sms)!!.type)
    }

    @Test
    fun operateurDepuisExpediteur() {
        val sms = "Retrait de 3 000 FCFA effectue par 93036603, le 19-06-26 20:31. Ref: 17915847509."
        assertEquals("FLOOZ", SmsParser.parse(sms, sender = "FLOOZ")!!.operatorName)
    }

    @Test
    fun refuseEchecDOperation() {
        assertNull(SmsParser.parse("Mixx : echec du retrait de 5 000 FCFA, solde insuffisant. Ref: 17915847999"))
        assertNull(SmsParser.parse("Txn ID 040228895777 Le depot de 2 000,00 FCFA a échoué. Moov money"))
    }

    @Test
    fun refuseCreditDeCommunication() {
        assertNull(SmsParser.parse("Mixx by Yas : vous avez recu 500 FCFA de credit de communication. Ref: 17915850000"))
    }
}
