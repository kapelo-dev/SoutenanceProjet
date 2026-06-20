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
}
