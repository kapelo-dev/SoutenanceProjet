package com.pdvconnect.smsservice.sms

/**
 * Expéditeurs autorisés : seuls leurs SMS sont transformés en transactions.
 *
 * La liste vient de la page web « Configuration app mobile » (GET /api/mobile/sms-config).
 * Tant qu'aucune liste n'a été définie sur le web, des noms génériques Mobile Money sont utilisés.
 */
object SmsFilter {

    /**
     * Utilisés quand la liste du serveur est vide (ou jamais reçue).
     * « MIX » en plus de « MIXX » : le SMS de dépôt Mix écrit « nouveau solde mix ».
     */
    val DEFAULT_FILTERS = listOf("FLOOZ", "MOOV", "MIX", "MIXX", "YAS")

    fun effectiveFilters(serverFilters: List<String>): List<String> {
        val cleaned = serverFilters.map { it.trim() }.filter { it.isNotBlank() }
        return cleaned.ifEmpty { DEFAULT_FILTERS }
    }

    /**
     * Numéro (ex. 8282, +22890123456) : comparé à l'expéditeur.
     * Nom (ex. FLOOZ) : cherché comme mot entier dans l'expéditeur ou le texte du SMS,
     * car certains téléphones affichent un numéro court à la place du nom de l'opérateur.
     */
    fun isAllowed(sender: String?, body: String?, filters: List<String>): Boolean {
        val normalizedSender = sender.orEmpty().replace(" ", "").replace("+", "")
        val text = body.orEmpty()

        return filters.any { raw ->
            val filter = raw.trim()
            if (filter.isEmpty()) return@any false

            val digits = filter.replace(" ", "").replace("+", "")
            if (digits.all { it.isDigit() }) {
                if (normalizedSender.isEmpty() || !normalizedSender.all { it.isDigit() }) return@any false
                // Même numéro, avec ou sans indicatif (+228) : comparaison sur les 8 derniers chiffres
                normalizedSender == digits ||
                    normalizedSender.takeLast(8) == digits.takeLast(8) && minOf(normalizedSender.length, digits.length) >= 8
            } else {
                val word = Regex("(?<![\\p{L}\\d])" + Regex.escape(filter) + "(?![\\p{L}\\d])", RegexOption.IGNORE_CASE)
                word.containsMatchIn(sender.orEmpty()) || word.containsMatchIn(text)
            }
        }
    }
}
