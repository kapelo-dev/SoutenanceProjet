package com.pdvconnect.smsservice.sms

import java.util.regex.Pattern

/**
 * Parse les SMS Mobile Money (Mix/Yas, FLOOZ) en transactions structurées.
 *
 * Formats Mix supportés :
 * 1. Dépôt client : « Dépôt de 5 000 FCFA effectue pour 91316317(NOM)... »
 * 2. Retrait client : « Retrait de 3 000 FCFA effectue par 93036603... »
 * 3. Envoi (= dépôt) : « Envoi de 20 000 FCFA au 90769121(NOM)... »
 * 4. Apport virtuel agence : « L'agent 10019 (NOM) vous a envoyé 50 000 FCFA... »
 */
object SmsParser {

    const val CATEGORY_COMMERCIAL = "commercial"
    const val CATEGORY_APPORT_VIRTUEL = "apport_virtuel"

    private val DEPOT_KEYWORDS = listOf(
        "reçu", "recu", "depot", "dépôt", "depôt", "credit", "crédit", "entree", "entrée",
        "depot reussi", "dépôt réussi", "depot réussi", "beneficiaire", "envoi de", "envoyé de",
    )
    private val RETRAIT_KEYWORDS = listOf(
        "retrait", "debit", "débit", "sortie", "veillez remettre l'argent",
        "retrait valider", "retrait validé", "retrait effectue",
    )

    data class ParsedTransaction(
        val montant: Double,
        val type: String,
        val rawBody: String,
        val transactionCategory: String = CATEGORY_COMMERCIAL,
        val reference: String? = null,
        val clientTelephone: String? = null,
        val clientNom: String? = null,
        val commission: Double? = null,
        val agentCode: String? = null,
        val sourceAgentCode: String? = null,
        val sourceAgentName: String? = null,
        val operatorName: String? = null,
        val virtualBalanceAfter: Double? = null,
    ) {
        fun isValid(): Boolean = montant > 0 && type.isNotBlank()
    }

    fun parse(body: String?, sender: String? = null): ParsedTransaction? {
        if (body.isNullOrBlank()) return null
        val text = body.replace("\n", " ").trim()

        // Ordre : formats les plus spécifiques en premier
        parseApportVirtuel(text)?.let { return it }
        parseEnvoiDepot(text)?.let { return it }
        parseDepotRetraitMix(text)?.let { return it }
        parseFloozGeneric(text)?.let { return it }

        return parseGenericFallback(text)
    }

    /** Format 4 — Apport virtuel agence : crédit float Mix sans mouvement espèce caisse. */
    private fun parseApportVirtuel(text: String): ParsedTransaction? {
        val p = Pattern.compile(
            "L'agent\\s+(\\d+)\\s*\\(([^)]+)\\)\\s+vous\\s+a\\s+envoy[ée]\\s+([\\d\\s.,]+)\\s*FCFA",
            Pattern.CASE_INSENSITIVE,
        )
        val m = p.matcher(text)
        if (!m.find()) return null

        val montant = parseAmount(m.group(3)) ?: return null

        return buildParsed(
            text = text,
            montant = montant,
            type = "depot",
            category = CATEGORY_APPORT_VIRTUEL,
            sourceAgentCode = m.group(1)?.trim(),
            sourceAgentName = m.group(2)?.trim()?.take(100),
            clientNom = m.group(2)?.trim()?.take(100),
        )
    }

    /** Format 3 — Envoi vers un client = dépôt commercial. */
    private fun parseEnvoiDepot(text: String): ParsedTransaction? {
        val p = Pattern.compile(
            "Envoi\\s+de\\s+([\\d\\s.,]+)\\s*FCFA\\s+au\\s+(\\d{8,10})\\s*\\(([^)]+)\\)",
            Pattern.CASE_INSENSITIVE,
        )
        val m = p.matcher(text)
        if (!m.find()) return null

        val montant = parseAmount(m.group(1)) ?: return null

        return buildParsed(
            text = text,
            montant = montant,
            type = "depot",
            category = CATEGORY_COMMERCIAL,
            clientTelephone = m.group(2),
            clientNom = m.group(3)?.trim()?.take(100),
        )
    }

    /** Formats 1 & 2 — Dépôt / retrait Mix classiques. */
    private fun parseDepotRetraitMix(text: String): ParsedTransaction? {
        val p = Pattern.compile(
            "(Depot|Dépôt|Depôt|depot|dépôt|depôt|Retrait|retrait)\\s+de\\s+([\\d\\s.,]+)\\s*FCFA",
            Pattern.CASE_INSENSITIVE,
        )
        val m = p.matcher(text)
        if (!m.find()) return null

        val keyword = m.group(1)?.lowercase() ?: return null
        val montant = parseAmount(m.group(2)) ?: return null
        val type = if (keyword.startsWith("r")) "retrait" else "depot"

        val (phone, nom) = extractClientInfo(text)

        return buildParsed(
            text = text,
            montant = montant,
            type = type,
            category = CATEGORY_COMMERCIAL,
            clientTelephone = phone,
            clientNom = nom,
            agentCode = extractCodeAgent(text),
        )
    }

    /** FLOOZ : Montant explicite + mots-clés Flooz. */
    private fun parseFloozGeneric(text: String): ParsedTransaction? {
        val lower = text.lowercase()
        if (!lower.contains("flooz") && !lower.contains("moov money")) return null

        val montant = extractMainAmount(text) ?: return null
        val type = detectType(text)
        val (phone, nom) = extractClientInfo(text)

        return buildParsed(
            text = text,
            montant = montant,
            type = type,
            category = CATEGORY_COMMERCIAL,
            clientTelephone = phone,
            clientNom = nom,
            agentCode = extractCodeAgent(text),
            operatorName = "FLOOZ",
        )
    }

    private fun parseGenericFallback(text: String): ParsedTransaction? {
        val montant = extractMainAmount(text) ?: return null
        val type = detectType(text)
        val (phone, nom) = extractClientInfo(text)

        return buildParsed(
            text = text,
            montant = montant,
            type = type,
            category = CATEGORY_COMMERCIAL,
            clientTelephone = phone,
            clientNom = nom,
            agentCode = extractCodeAgent(text),
        )
    }

    private fun buildParsed(
        text: String,
        montant: Double,
        type: String,
        category: String,
        clientTelephone: String? = null,
        clientNom: String? = null,
        agentCode: String? = null,
        sourceAgentCode: String? = null,
        sourceAgentName: String? = null,
        operatorName: String? = null,
    ): ParsedTransaction {
        return ParsedTransaction(
            montant = montant,
            type = type,
            rawBody = text.take(500),
            transactionCategory = category,
            reference = extractReference(text),
            clientTelephone = clientTelephone,
            clientNom = clientNom,
            commission = extractCommissionOrFrais(text),
            agentCode = agentCode,
            sourceAgentCode = sourceAgentCode,
            sourceAgentName = sourceAgentName,
            operatorName = operatorName ?: extractNetwork(text),
            virtualBalanceAfter = extractVirtualBalanceAfter(text),
        )
    }

    private fun extractMainAmount(text: String): Double? {
        val patterns = listOf(
            Pattern.compile("(?:retrait|depot|dépôt|depôt|envoi)\\s+de\\s+([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("montant\\s*:?\\s*([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("envoy[ée]\\s+([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("(\\d[\\d\\s.,]*)\\s*FCFA", Pattern.CASE_INSENSITIVE),
        )

        for (pattern in patterns) {
            val m = pattern.matcher(text)
            if (m.find()) {
                val contextStart = maxOf(0, m.start() - 40)
                val context = text.substring(contextStart, m.start()).lowercase()
                if (context.contains("commission") || context.contains("commision") ||
                    context.contains("frais") || context.contains("solde")
                ) {
                    continue
                }
                parseAmount(m.group(1))?.let { return it }
            }
        }
        return null
    }

    private fun parseAmount(raw: String?): Double? {
        if (raw.isNullOrBlank()) return null
        val normalized = raw.replace(" ", "").replace(",", ".").trim()
        val value = normalized.toDoubleOrNull()
        return if (value != null && value > 0) value else null
    }

    private fun extractCommissionOrFrais(text: String): Double? {
        val patterns = listOf(
            Pattern.compile("commis(?:sion|ion)\\s*:?\\s*([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("frais\\s*:?\\s*([\\d\\s.,]+)\\s*FCFA", Pattern.CASE_INSENSITIVE),
        )
        for (p in patterns) {
            val m = p.matcher(text)
            if (m.find()) return parseAmount(m.group(1))
        }
        return null
    }

    private fun extractCodeAgent(text: String): String? {
        val p = Pattern.compile("code\\s+agent\\s*:?\\s*([0-9]+)", Pattern.CASE_INSENSITIVE)
        val m = p.matcher(text)
        return if (m.find()) m.group(1)?.trim()?.take(20) else null
    }

    private fun extractNetwork(text: String): String? {
        val lower = text.lowercase()
        if (lower.contains("flooz") || lower.contains("moov money")) return "FLOOZ"
        if (lower.contains("mixx") || lower.contains("mix by") || Regex("\\bmix\\b").containsMatchIn(lower)) return "YAS"
        return null
    }

    private fun extractVirtualBalanceAfter(text: String): Double? {
        val p = Pattern.compile(
            "(?:votre\\s+)?(?:nouveau\\s+)?solde\\s*(?:\\s*[A-Za-z]*)?\\s*:?\\s*([\\d\\s.,]+)\\s*FCFA",
            Pattern.CASE_INSENSITIVE,
        )
        val m = p.matcher(text)
        if (m.find()) return parseAmount(m.group(1))
        return null
    }

    private fun detectType(text: String): String {
        val lower = text.lowercase()
        if (DEPOT_KEYWORDS.any { lower.contains(it) }) return "depot"
        if (RETRAIT_KEYWORDS.any { lower.contains(it) }) return "retrait"
        if (lower.contains("transfert")) return "transfert"
        if (lower.contains("paiement") || lower.contains("pay")) return "paiement"
        return "transfert"
    }

    private fun extractReference(text: String): String? {
        val txnId = Pattern.compile("Txn\\s*ID\\s*:?\\s*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE)
        txnId.matcher(text).let { if (it.find()) return it.group(1)?.take(50) }

        val ref = Pattern.compile("(?:ref|reference|n°|no)\\s*:?\\s*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE)
        ref.matcher(text).let { if (it.find()) return it.group(1)?.take(50) }

        return null
    }

    private fun extractClientInfo(text: String): Pair<String?, String?> {
        var phone: String? = null
        var nom: String? = null

        Pattern.compile("beneficiaire\\s*:?\\s*([0-9]{8,10})", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find()) phone = it.group(1)
        }

        Pattern.compile("(?:effectue\\s+)?pour\\s+([0-9]{8,10})\\s*\\(([^)]+)\\)", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find()) {
                phone = phone ?: it.group(1)
                nom = it.group(2)?.trim()?.take(100)
            }
        }

        Pattern.compile("(?:effectue\\s+)?par\\s+([0-9]{8,10})\\b", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find() && phone == null) phone = it.group(1)
        }

        Pattern.compile("(?:par\\s+le\\s+)?client\\s+([A-Za-z\\s]{2,30})\\s*,\\s*([0-9]{8,10})", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find()) {
                nom = nom ?: it.group(1)?.trim()?.take(100)
                phone = phone ?: it.group(2)
            }
        }

        if (phone == null) {
            Pattern.compile("(?:\\+228)?[0-9]{8,10}").matcher(text).let {
                if (it.find()) phone = it.group()
            }
        }

        return Pair(phone, nom)
    }
}
