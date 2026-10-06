package com.pdvconnect.smsservice.sms

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

class SmsFilterTest {

    private val floozRetrait = "Txn ID 040228895463 retrait valider par le client FABIO ,79984409 Montant: 2 000,00 FCFA Nouveau solde FLOOZ : 28 703,00 FCFA."
    private val mixDepot = "Depot de 2 000 FCFA effectue pour 90513298(AHIAKPOR) nouveau solde mix : 275 784 (commission incluse) REF 13990436494"

    @Test
    fun listeVideDuServeurUtiliseLesExpediteursParDefaut() {
        assertEquals(SmsFilter.DEFAULT_FILTERS, SmsFilter.effectiveFilters(emptyList()))
        assertEquals(SmsFilter.DEFAULT_FILTERS, SmsFilter.effectiveFilters(listOf(" ", "")))
        assertEquals(listOf("8282"), SmsFilter.effectiveFilters(listOf(" 8282 ")))
    }

    @Test
    fun parDefautLesVraisSmsMobileMoneyPassent() {
        val filtres = SmsFilter.effectiveFilters(emptyList())
        assertTrue(SmsFilter.isAllowed("8282", floozRetrait, filtres)) // « FLOOZ » dans le texte
        assertTrue(SmsFilter.isAllowed("MIXX by YAS", mixDepot, filtres)) // nom de l'expéditeur
        assertTrue(SmsFilter.isAllowed("1234", mixDepot, filtres)) // « nouveau solde mix » dans le texte
    }

    @Test
    fun parDefautUnSmsPersonnelEstIgnore() {
        val filtres = SmsFilter.effectiveFilters(emptyList())
        assertFalse(SmsFilter.isAllowed("+22890123456", "Salut, tu peux m'envoyer 2 000 FCFA ce soir ?", filtres))
    }

    @Test
    fun nomCompareCommeMotEntier() {
        // « YAS » ne doit pas correspondre à « payas » ou « Yasmine »
        assertFalse(SmsFilter.isAllowed("Yasmine", "Rendez-vous chez payas demain", listOf("YAS")))
        assertTrue(SmsFilter.isAllowed("YAS", "message", listOf("yas")))
    }

    @Test
    fun numeroCourtExact() {
        assertTrue(SmsFilter.isAllowed("8282", "texte", listOf("8282")))
        assertFalse(SmsFilter.isAllowed("82820", "texte", listOf("8282")))
        assertFalse(SmsFilter.isAllowed("FLOOZ", "texte", listOf("8282")))
    }

    @Test
    fun numeroAvecOuSansIndicatif() {
        assertTrue(SmsFilter.isAllowed("+22890123456", "texte", listOf("90123456")))
        assertTrue(SmsFilter.isAllowed("90123456", "texte", listOf("+228 90 12 34 56")))
        assertFalse(SmsFilter.isAllowed("+22899999999", "texte", listOf("90123456")))
    }

    @Test
    fun expediteurVideNeCorrespondPasAUnNumero() {
        assertFalse(SmsFilter.isAllowed("", "texte", listOf("8282")))
    }
}
