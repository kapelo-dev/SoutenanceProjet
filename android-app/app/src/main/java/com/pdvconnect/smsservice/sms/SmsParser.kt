package com.pdvconnect.smsservice.sms

import java.util.regex.Pattern

/**
 * Parse le corps d'un SMS pour en extraire les champs d'une transaction (Mobile Money).
 * Formats supportés : Mix (Yas), FLOOZ / Moov Money, et formats génériques.
 *
 * Exemples :
 * - Mix retrait : "retrait de 4 900 FCFA effectue par 91069102 ... REF 13990966494"
 * - Mix dépôt   : "Depot de 2 000 FCFA effectue pour 90513298(AHIAKPOR) ..."
 * - FLOOZ retrait : "Montant: 2 000,00 FCFA ... par le client FABIO ,79984409 ... Txn ID 040228895463"
 * - FLOOZ dépôt   : "Depot reussi Montant:2600,00 FCFA beneficiaire : 96096844 ... Txn ID: 040229031027"
 */
object SmsParser {

    // Montant : "4 900 FCFA", "2 000,00 FCFA", "Montant: 2 000,00 FCFA", "Montant:2600,00 FCFA"
    private val AMOUNT_PATTERNS = listOf(
        Pattern.compile("montant[\\s:]+([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*FCFA", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*F\\s*C\\s*F\\s*A", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*Francs", Pattern.CASE_INSENSITIVE),
        Pattern.compile("(\\d[\\d\\s.,]*)\\s*XAF", Pattern.CASE_INSENSITIVE)
    )

    // Type : dépôt / retrait (Mix: "retrait de", "Depot de" ; FLOOZ: "retrait valider", "Depot reussi")
    private val DEPOT_KEYWORDS = listOf(
        "reçu", "recu", "depot", "dépôt", "depôt", "credit", "crédit", "entree", "entrée",
        "depot reussi", "dépôt réussi", "depot réussi", "beneficiaire"
    )
    private val RETRAIT_KEYWORDS = listOf(
        "retrait", "debit", "débit", "sortie", "envoyé", "envoye", "transfert",
        "retrait valider", "retrait validé", "retrait effectue", "veillez remettre l'argent"
    )

    data class ParsedTransaction(
        val montant: Double,
        val type: String, // depot, retrait, transfert, paiement
        val rawBody: String,
        val reference: String? = null,
        val clientTelephone: String? = null,
        val clientNom: String? = null,
        val commission: Double? = null,
        val agentCode: String? = null,
        val operatorName: String? = null, // FLOOZ, Mix, etc.
        val virtualBalanceAfter: Double? = null
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

        val montant = extractMainAmount(normalized) ?: return null
        val type = detectType(normalized)
        val reference = extractReference(normalized)
        val (telephone, nom) = extractClientInfo(normalized)
        val commission = extractCommission(normalized)
        val agentCode = extractCodeAgent(normalized)
        val operatorName = extractNetwork(normalized)
        val virtualBalanceAfter = extractVirtualBalanceAfter(normalized)

        return ParsedTransaction(
            montant = montant,
            type = type,
            rawBody = body.take(500),
            reference = reference,
            clientTelephone = telephone,
            clientNom = nom,
            commission = commission,
            agentCode = agentCode,
            operatorName = operatorName,
            virtualBalanceAfter = virtualBalanceAfter
        )
    }

    /** Montant principal : priorité à "Montant: X FCFA" pour ne pas prendre la commission. */
    private fun extractMainAmount(text: String): Double? {
        val montantExplicit = Pattern.compile("montant\\s*:?\\s*([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE)
        val m = montantExplicit.matcher(text)
        if (m.find()) {
            val raw = m.group(1)?.replace(" ", "")?.replace(",", ".")?.trim() ?: return null
            val value = raw.toDoubleOrNull()
            if (value != null && value > 0) return value
        }
        val otherAmounts = mutableListOf<Double>()
        for (pattern in AMOUNT_PATTERNS) {
            if (pattern.pattern().contains("montant", ignoreCase = true)) continue
            val matcher = pattern.matcher(text)
            while (matcher.find()) {
                val raw = matcher.group(1)?.replace(" ", "")?.replace(",", ".")?.trim() ?: continue
                val value = raw.toDoubleOrNull()
                if (value != null && value > 0) otherAmounts.add(value)
            }
        }
        return otherAmounts.maxOrNull()
    }

    /** Commission : "Commission Net : 24,32 FCFA" ou "commission : 21 FCFA" */
    private fun extractCommission(text: String): Double? {
        val p = Pattern.compile("commission\\s+(?:net\\s*)?:?\\s*([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE)
        val m = p.matcher(text)
        if (m.find()) {
            val raw = m.group(1)?.replace(" ", "")?.replace(",", ".")?.trim() ?: return null
            return raw.toDoubleOrNull()
        }
        return null
    }

    /** Code Agent : "Code Agent: 5150328" */
    private fun extractCodeAgent(text: String): String? {
        val p = Pattern.compile("code\\s+agent\\s*:?\\s*([0-9]+)", Pattern.CASE_INSENSITIVE)
        val m = p.matcher(text)
        return if (m.find()) m.group(1)?.trim()?.take(20) else null
    }

    /** Réseau : "Nouveau solde FLOOZ" -> code FLOOZ, "solde Mix" -> code YAS (Mixx by yas) */
    private fun extractNetwork(text: String): String? {
        // On renvoie directement le "code" utilisé en base pour faciliter le matching côté Laravel
        if (Pattern.compile("(?:solde\\s+)?FLOOZ", Pattern.CASE_INSENSITIVE).matcher(text).find()) return "FLOOZ"
        if (Pattern.compile("(?:solde\\s+)?Mixx?", Pattern.CASE_INSENSITIVE).matcher(text).find()) return "YAS"
        return null
    }

    /** Nouveau solde virtuel : "Nouveau solde FLOOZ : 28 703,00 FCFA" */
    private fun extractVirtualBalanceAfter(text: String): Double? {
        val p = Pattern.compile("(?:nouveau\\s+)?solde\\s*(?:\\s*[A-Za-z]*)?\\s*:?\\s*([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE)
        val m = p.matcher(text)
        if (m.find()) {
            val raw = m.group(1)?.replace(" ", "")?.replace(",", ".")?.trim() ?: return null
            return raw.toDoubleOrNull()
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
        // Txn ID (FLOOZ) : "Txn ID 040228895463" ou "Txn ID: 040229031027"
        val txnIdPattern = Pattern.compile("Txn\\s*ID\\s*:?\\s*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE)
        val txnM = txnIdPattern.matcher(text)
        if (txnM.find()) return txnM.group(1)?.take(50)
        // REF (Mix) : "REF 13990966494"
        val refPattern = Pattern.compile("(?:ref|reference|n°|no|code)[\\s:]*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE)
        val m = refPattern.matcher(text)
        return if (m.find()) m.group(1)?.take(50) else null
    }

    private fun extractClientInfo(text: String): Pair<String?, String?> {
        var phone: String? = null
        var nom: String? = null

        // FLOOZ: "beneficiaire : 96096844"
        val beneficiairePattern = Pattern.compile("beneficiaire\\s*:?\\s*([0-9]{8,10})", Pattern.CASE_INSENSITIVE)
        val benefM = beneficiairePattern.matcher(text)
        if (benefM.find()) phone = benefM.group(1)

        // Mix / FLOOZ: "effectue pour 90513298(AHIAKPOR)" → phone + nom
        val pourPattern = Pattern.compile("(?:effectue\\s+)?pour\\s+([0-9]{8,10})\\s*\\(([^)]+)\\)", Pattern.CASE_INSENSITIVE)
        val pourM = pourPattern.matcher(text)
        if (pourM.find()) {
            phone = phone ?: pourM.group(1)
            nom = pourM.group(2)?.trim()?.take(100)
        }

        // "effectue par 91069102" ou "par 91069102"
        val parPhonePattern = Pattern.compile("(?:effectue\\s+)?par\\s+([0-9]{8,10})\\b", Pattern.CASE_INSENSITIVE)
        val parPhoneM = parPhonePattern.matcher(text)
        if (parPhoneM.find() && phone == null) phone = parPhoneM.group(1)

        // FLOOZ: "par le client FABIO ,79984409" ou "client FABIO ,79984409"
        val clientPattern = Pattern.compile("(?:par\\s+le\\s+)?client\\s+([A-Za-z\\s]{2,30})\\s*,\\s*([0-9]{8,10})", Pattern.CASE_INSENSITIVE)
        val clientM = clientPattern.matcher(text)
        if (clientM.find()) {
            nom = nom ?: clientM.group(1)?.trim()?.take(100)
            phone = phone ?: clientM.group(2)
        }

        // Fallback: téléphone seul (séquence 8–10 chiffres, éventuellement +228)
        if (phone == null) {
            val phonePattern = Pattern.compile("(?:\\+228)?[0-9]{8,10}")
            val phoneMatcher = phonePattern.matcher(text)
            if (phoneMatcher.find()) phone = phoneMatcher.group()
        }
        // Fallback: nom après "par " ou "de " (lettres seulement)
        if (nom == null) {
            val nomPattern = Pattern.compile("(?:par|de)\\s+([A-Za-z\\s]{2,30})(?:\\s|,|\\.|\\()", Pattern.CASE_INSENSITIVE)
            val nomMatcher = nomPattern.matcher(text)
            if (nomMatcher.find()) nom = nomMatcher.group(1)?.trim()?.take(100)
        }
        return Pair(phone, nom)
    }
}
