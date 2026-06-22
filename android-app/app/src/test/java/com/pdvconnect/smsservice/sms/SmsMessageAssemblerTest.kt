package com.pdvconnect.smsservice.sms

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertNull
import org.junit.Test

class SmsMessageAssemblerTest {

  private val fullRetrait =
      "Retrait de 4 900 FCFA effectue par 92948264, le 19-06-26 12:19. " +
          "Commision: 21 FCFA. Votre nouveau solde Mixx : 209842FCFA (commission incluse). Ref: 17787919232."

    @Test
    fun joinPartBodies_concatenatesMultipartSegments() {
        val part1 = "Retrait de 4 900 FCFA effectue par 92948264, le 19-06-26 12:19. "
        val part2 = "Commision: 21 FCFA. Votre nouveau solde Mixx : 209842FCFA (commission incluse). Ref: 17787919232."

        val joined = SmsMessageAssembler.joinPartBodies(listOf(part1, part2))
        assertEquals(fullRetrait, joined)
    }

    @Test
    fun parseMultipartRetrait_afterJoin() {
        val part1 = "Retrait de 4 900 FCFA effectue par 92948264, le 19-06-26 12:19. "
        val part2 = "Commision: 21 FCFA. Votre nouveau solde Mixx : 209842FCFA (commission incluse). Ref: 17787919232."
        val body = SmsMessageAssembler.joinPartBodies(listOf(part1, part2))

        val parsed = SmsParser.parse(body)
        assertNotNull(parsed)
        assertEquals(4900.0, parsed!!.montant, 0.01)
        assertEquals("retrait", parsed.type)
        assertEquals("17787919232", parsed.reference)
        assertEquals(21.0, parsed.commission!!, 0.01)
    }

    @Test
    fun parseSecondFragmentAlone_failsAsBefore() {
        val part2Only = "Commision: 21 FCFA. Votre nouveau solde Mixx : 209842FCFA (commission incluse). Ref: 17787919232."
        assertNull(SmsParser.parse(part2Only))
    }

    @Test
    fun joinPartBodies_emptyReturnsEmpty() {
        assertEquals("", SmsMessageAssembler.joinPartBodies(emptyList()))
        assertNull(SmsMessageAssembler.fromMessages(emptyArray()))
    }
}
