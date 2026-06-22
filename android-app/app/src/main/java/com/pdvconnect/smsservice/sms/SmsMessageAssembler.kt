package com.pdvconnect.smsservice.sms

import android.telephony.SmsMessage

/**
 * Recolle les segments d'un SMS multipart avant parsing.
 * Android livre souvent 2+ morceaux (UCS-2 avec accents) ; l'app Messages les affiche
 * fusionnés, mais getMessagesFromIntent() renvoie un tableau de fragments.
 */
object SmsMessageAssembler {

    data class IncomingSms(
        val sender: String,
        val body: String,
        val partCount: Int,
    )

    fun fromMessages(messages: Array<SmsMessage>?): IncomingSms? {
        if (messages.isNullOrEmpty()) return null

        val body = joinPartBodies(messages.map { it.messageBody })
        if (body.isBlank()) return null

        val sender = messages.firstOrNull()?.originatingAddress?.trim().orEmpty()

        return IncomingSms(
            sender = sender,
            body = body,
            partCount = messages.size,
        )
    }

    /** Recolle les corps de segments (testable sans API Android). */
    fun joinPartBodies(parts: List<String?>): String {
        return parts.joinToString(separator = "") { it.orEmpty() }
    }
}
