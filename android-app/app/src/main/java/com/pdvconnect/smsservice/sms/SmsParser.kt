package com.pdvconnect.smsservice.sms

import java.util.regex.Pattern

/**
 * Parse le corps d'un SMS pour en extraire les champs d'une transaction (Mobile Money).
 * Formats courants : "Vous avez reçu 5000 FCFA de...", "Retrait de 10000 FCFA...", etc.
 */
object SmsParser {

    // Montant avec séparateurs possibles (ex: 5 000, 5.000, 5000)
    private val AMOUNT_PATTERNS = listOf(
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*FCFA", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*F\\s*C\\s*F\\s*A", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*Francs", Pattern.CASE_INSENSITIVE),
        Pattern.compile("montant[\\s:]+(\\d[\\d\\s.,]*)", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*XAF", Pattern.CASE_INSENSITIVE)
    )

    // Type approximatif selon mots-clés
    private val DEPOT_KEYWORDS = listOf("reçu", "recu", "depot", "dépôt", "credit", "crédit", "entree", "entrée")
    private val RETRAIT_KEYWORDS = listOf("retrait", "debit", "débit", "sortie", "envoyé", "envoye", "transfert")

    data class ParsedTransaction(
        val montant: Double,
        val type: String, // depot, retrait, transfert, paiement
        val rawBody: String,
        val reference: String? = null,
        val clientTelephone: String? = null,
        val clientNom: String? = null
    ) {
        fun isValid(): Boolean = montant > 0 && type.isNotBlank()
    }

    /**
     * Tente d'extraire une transaction du corps du SMS.
     * @return ParsedTransaction si le SMS semble être une notification de transaction, null sinon.
     */
    fun parse(body: String?, sender: String? = null): ParsedTransaction? {
        if (body.isNullOrBlank()) return null
        val normalized = body.replace("\n", " ").trim()

        val montant = extractAmount(normalized) ?: return null
        val type = detectType(normalized)
        val reference = extractReference(normalized)
        val (telephone, nom) = extractClientInfo(normalized)

        return ParsedTransaction(
            montant = montant,
            type = type,
            rawBody = body.take(500),
            reference = reference,
            clientTelephone = telephone,
            clientNom = nom
        )
    }

    private fun extractAmount(text: String): Double? {
        for (pattern in AMOUNT_PATTERNS) {
            val matcher = pattern.matcher(text)
            if (matcher.find()) {
                val raw = matcher.group(1)?.replace(" ", "")?.replace(",", ".") ?: continue
                return raw.replace(" ", "").toDoubleOrNull()
            }
        }
        return null
    }

    private fun detectType(text: String): String {
        val lower = text.lowercase()
        if (DEPOT_KEYWORDS.any { lower.contains(it) }) return "depot"
        if (RETRAIT_KEYWORDS.any { lower.contains(it) }) return "retrait"
        // Par défaut : transfert ou paiement selon contexte
        if (lower.contains("paiement") || lower.contains("pay")) return "paiement"
        return "transfert"
    }

    private fun extractReference(text: String): String? {
        // Référence type REF123, N° 123, code 123
        val refPattern = Pattern.compile("(?:ref|reference|n°|no|code)[\\s:]*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE)
        val m = refPattern.matcher(text)
        return if (m.find()) m.group(1)?.take(50) else null
    }

    private fun extractClientInfo(text: String): Pair<String?, String?> {
        // Téléphone: séquence de chiffres avec + ou 8/9 chiffres
        val phonePattern = Pattern.compile("(?:\\+228)?[0-9]{8,10}")
        val phoneMatcher = phonePattern.matcher(text)
        val phone = if (phoneMatcher.find()) phoneMatcher.group() else null
        // Nom: après "de X" ou "par X" (simplifié)
        val nomPattern = Pattern.compile("(?:de|par)\\s+([A-Za-z\\s]{2,30?})(?:\\s|,|\\.)", Pattern.CASE_INSENSITIVE)
        val nomMatcher = nomPattern.matcher(text)
        val nom = if (nomMatcher.find()) nomMatcher.group(1)?.trim()?.take(100) else null
        return Pair(phone, nom)
    }
}
