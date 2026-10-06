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
 *
 * FLOOZ : « Depot reussi Montant:2600,00 FCFA... » / « retrait valider par le client... Montant: 2 000,00 FCFA ».
 *
 * Tout autre SMS n'est accepté que s'il contient une référence d'opération et un type reconnu :
 * un SMS publicitaire ou personnel mentionnant un montant ne devient pas une transaction.
 * Les échecs d'opération et les crédits de communication sont toujours ignorés.
 */
object SmsParser {

    const val CATEGORY_COMMERCIAL = "commercial"
    const val CATEGORY_APPORT_VIRTUEL = "apport_virtuel"

    /**
     * Montant : « 2 000 », « 2.000 », « 1,500 » (séparateurs de milliers par groupes de 3 chiffres),
     * avec éventuellement 1 ou 2 décimales après une virgule ou un point (« 2 000,00 », « 24,32 »).
     */
    private const val AMOUNT = "(\\d{1,3}(?:[ .,]\\d{3})+(?:[.,]\\d{1,2})?|\\d+(?:[.,]\\d{1,2})?)(?!\\d)"

    private val DEPOT_KEYWORDS = listOf(
        "reçu", "recu", "depot", "dépôt", "depôt", "credit", "crédit", "entree", "entrée",
        "beneficiaire", "bénéficiaire", "envoi de", "envoyé de",
    )
    private val RETRAIT_KEYWORDS = listOf(
        "retrait", "debit", "débit", "sortie", "veillez remettre l'argent",
    )
    /** SMS qui citent un montant sans être une opération réussie : jamais transformés en transaction. */
    private val EXCLUSION_KEYWORDS = listOf(
        "echec", "échec", "echoue", "échoué", "echouee", "échouée", "solde insuffisant",
        "credit de communication", "crédit de communication",
    )
    private val TRANSFERT_KEYWORDS = listOf("transfert")
    private val PAIEMENT_KEYWORDS = listOf("paiement")

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
        val text = normalizeSmsText(body)

        val lower = text.lowercase()
        if (EXCLUSION_KEYWORDS.any { lower.contains(it) }) return null

        // Ordre : formats les plus spécifiques en premier
        val parsed = parseApportVirtuel(text)
            ?: parseEnvoiDepot(text)
            ?: parseDepotRetraitMix(text)
            ?: parseFloozGeneric(text)
            ?: parseGenericFallback(text)
            ?: return null

        // L'expéditeur complète l'opérateur quand le texte ne le nomme pas (ex. expéditeur « FLOOZ »)
        return if (parsed.operatorName == null) parsed.copy(operatorName = extractNetwork(sender.orEmpty())) else parsed
    }

    /** Nettoie espaces insécables, apostrophes typographiques, etc. */
    private fun normalizeSmsText(body: String): String {
        return body
            .replace(' ', ' ')
            .replace(' ', ' ')
            .replace(Regex("[\\u2018\\u2019\\u02BC\\u0060]"), "'")
            .replace("\n", " ")
            .replace(Regex("\\s+"), " ")
            .trim()
    }

    /** Format 4 — Apport virtuel agence : crédit float Mix sans mouvement espèce caisse. */
    private fun parseApportVirtuel(text: String): ParsedTransaction? {
        val p = Pattern.compile(
            "L'?\\s*agent\\s+(\\d+)\\s*\\(([^)]+)\\)\\s+vous\\s+a\\s+envoy[ée]\\s+$AMOUNT\\s*FCFA",
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
        val withName = Pattern.compile(
            "Envoi\\s+de\\s+$AMOUNT\\s*FCFA\\s+(?:au|à|a)\\s+(\\d{8,10})(?!\\d)\\s*\\(([^)]+)\\)",
            Pattern.CASE_INSENSITIVE,
        )
        withName.matcher(text).let { m ->
            if (m.find()) {
                val montant = parseAmount(m.group(1)) ?: return@let
                return buildParsed(
                    text = text,
                    montant = montant,
                    type = "depot",
                    category = CATEGORY_COMMERCIAL,
                    clientTelephone = m.group(2),
                    clientNom = m.group(3)?.trim()?.take(100),
                )
            }
        }

        val phoneOnly = Pattern.compile(
            "Envoi\\s+de\\s+$AMOUNT\\s*FCFA\\s+(?:au|à|a)\\s+(\\d{8,10})(?!\\d)",
            Pattern.CASE_INSENSITIVE,
        )
        val m = phoneOnly.matcher(text)
        if (!m.find()) return null

        val montant = parseAmount(m.group(1)) ?: return null

        return buildParsed(
            text = text,
            montant = montant,
            type = "depot",
            category = CATEGORY_COMMERCIAL,
            clientTelephone = m.group(2),
        )
    }

    /** Formats 1 & 2 — Dépôt / retrait Mix classiques. */
    private fun parseDepotRetraitMix(text: String): ParsedTransaction? {
        val p = Pattern.compile(
            "(D[eé]p[oô]t|Retrait)\\s+de\\s+$AMOUNT\\s*FCFA",
            Pattern.CASE_INSENSITIVE or Pattern.UNICODE_CASE,
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

    /** FLOOZ / Moov Money : montant explicite, type et référence (Txn ID) obligatoires. */
    private fun parseFloozGeneric(text: String): ParsedTransaction? {
        val lower = text.lowercase()
        if (!lower.contains("flooz") && !lower.contains("moov money")) return null
        if (extractReference(text) == null) return null

        val montant = extractMainAmount(text) ?: return null
        val type = detectType(text) ?: return null
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

    /**
     * Format non répertorié : accepté seulement avec une référence d'opération et un type reconnu
     * (jamais de type « transfert » par défaut).
     */
    private fun parseGenericFallback(text: String): ParsedTransaction? {
        if (extractReference(text) == null) return null

        val montant = extractMainAmount(text) ?: return null
        val type = detectType(text) ?: return null
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
            Pattern.compile("(?:retrait|d[eé]p[oô]t|envoi)\\s+de\\s+$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE or Pattern.UNICODE_CASE),
            Pattern.compile("montant\\s*:?\\s*$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("envoy[ée]\\s+$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("(?<![\\d,.])$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE),
        )

        for (pattern in patterns) {
            val m = pattern.matcher(text)
            while (m.find()) {
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

    /**
     * « 2 000 » → 2000, « 5.000 » → 5000, « 1,500 » → 1500, « 2 000,00 » → 2000, « 24,32 » → 24.32.
     * Un point ou une virgule suivi de 1 ou 2 chiffres en fin de nombre est une décimale ;
     * suivi de 3 chiffres, c'est un séparateur de milliers.
     */
    internal fun parseAmount(raw: String?): Double? {
        if (raw.isNullOrBlank()) return null
        val compact = raw.replace(" ", "").replace(" ", "").trim()
        if (compact.isEmpty() || !compact.first().isDigit()) return null

        val decimal = Regex("[.,](\\d{1,2})$").find(compact)
        val integerPart = (if (decimal != null) compact.substring(0, decimal.range.first) else compact)
            .replace(".", "")
            .replace(",", "")
        if (integerPart.isEmpty() || !integerPart.all { it.isDigit() }) return null

        val value = (integerPart + (decimal?.let { "." + it.groupValues[1] } ?: "")).toDoubleOrNull()
        return if (value != null && value > 0) value else null
    }

    /** « Commission: 14 FCFA », « Commision: 21 FCFA », « Commission Net : 24,32 FCFA », « Frais: 0 FCFA ». */
    private fun extractCommissionOrFrais(text: String): Double? {
        val patterns = listOf(
            Pattern.compile("commis+ion(?:\\s+net)?\\s*:?\\s*$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE),
            Pattern.compile("frais(?:\\s+net)?\\s*:?\\s*$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE),
        )
        for (p in patterns) {
            val m = p.matcher(text)
            if (m.find()) return parseAmount(m.group(1)) ?: 0.0
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
        if (lower.contains("flooz") || lower.contains("moov")) return "FLOOZ"
        if (lower.contains("mixx") || lower.contains("mix by") || Regex("\\b(mix|yas)\\b").containsMatchIn(lower)) return "YAS"
        return null
    }

    /**
     * Solde après l'opération : « nouveau solde » en priorité (le SMS peut aussi citer l'ancien solde).
     * Le SMS de dépôt Mix n'indique pas « FCFA » après ce solde (« nouveau solde mix : 275 784 (commission incluse) »).
     */
    private fun extractVirtualBalanceAfter(text: String): Double? {
        val patterns = listOf(
            Pattern.compile("nouveau\\s+solde[^:\\d]{0,20}:?\\s*$AMOUNT", Pattern.CASE_INSENSITIVE),
            Pattern.compile("(?<!ancien\\s)solde[^:\\d]{0,20}:\\s*$AMOUNT\\s*FCFA", Pattern.CASE_INSENSITIVE),
        )
        for (p in patterns) {
            val m = p.matcher(text)
            if (m.find()) return parseAmount(m.group(1))
        }
        return null
    }

    /**
     * Type d'après le premier mot-clé rencontré dans le texte (« retrait valider ... reçu » reste un retrait).
     * null si aucun mot-clé : le SMS n'est pas une opération reconnue.
     */
    private fun detectType(text: String): String? {
        val lower = text.lowercase()
        return listOf(
            "depot" to DEPOT_KEYWORDS,
            "retrait" to RETRAIT_KEYWORDS,
            "transfert" to TRANSFERT_KEYWORDS,
            "paiement" to PAIEMENT_KEYWORDS,
        )
            .mapNotNull { (type, keywords) ->
                keywords.map { lower.indexOf(it) }.filter { it >= 0 }.minOrNull()?.let { type to it }
            }
            .minByOrNull { it.second }
            ?.first
    }

    private fun extractReference(text: String): String? {
        val patterns = listOf(
            Pattern.compile("Txn\\s*ID\\s*:?\\s*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE),
            Pattern.compile("\\b(?:ref(?:erence|érence)?|n°|no)\\s*:\\s*([A-Za-z0-9-]+)", Pattern.CASE_INSENSITIVE),
            Pattern.compile("\\bref(?:erence|érence)?\\s+([0-9]{6,})", Pattern.CASE_INSENSITIVE),
        )
        for (p in patterns) {
            val m = p.matcher(text)
            if (m.find()) return m.group(1)?.take(50)
        }
        return null
    }

    private fun extractClientInfo(text: String): Pair<String?, String?> {
        var phone: String? = null
        var nom: String? = null

        Pattern.compile("b[ée]n[ée]ficiaire\\s*:?\\s*([0-9]{8,10})(?!\\d)", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find()) phone = it.group(1)
        }

        Pattern.compile("(?:effectue\\s+)?pour\\s+([0-9]{8,10})(?!\\d)\\s*\\(([^)]+)\\)", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find()) {
                phone = phone ?: it.group(1)
                nom = it.group(2)?.trim()?.take(100)
            }
        }

        Pattern.compile("(?:effectue\\s+)?par\\s+([0-9]{8,10})(?!\\d)", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find() && phone == null) phone = it.group(1)
        }

        Pattern.compile("(?:par\\s+le\\s+)?client\\s+([A-Za-z\\s]{2,30})\\s*,\\s*([0-9]{8,10})(?!\\d)", Pattern.CASE_INSENSITIVE).matcher(text).let {
            if (it.find()) {
                nom = nom ?: it.group(1)?.trim()?.take(100)
                phone = phone ?: it.group(2)
            }
        }

        // Dernier recours : un numéro togolais isolé (8 chiffres, avec ou sans +228),
        // jamais un morceau d'une référence plus longue (Txn ID 040228895463)
        if (phone == null) {
            Pattern.compile("(?<![\\d+])(?:\\+228\\s?)?\\d{8}(?!\\d)").matcher(text).let {
                if (it.find()) phone = it.group().replace(" ", "")
            }
        }

        return Pair(phone, nom)
    }
}
