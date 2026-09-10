package com.pdvconnect.smsservice.util

import android.content.Context
object AgentContextProvider {

    data class AgentContext(
        val agentId: Long?,
        val agentCode: String?,
        val agentTelephone: String?,
    )

    /**
     * Rattache la transaction à l'agent dont la SIM a reçu le SMS,
     * pas à l'agent connecté dans l'espace agent.
     */
    suspend fun resolveForSmsTransaction(context: Context, smsAgentCode: String?): AgentContext {
        val simPhone = SimUtils.getSimPhoneNumber(context)

        return smsAgentContext(
            simPhone = simPhone,
            smsAgentCode = smsAgentCode,
        )
    }

    /** Logique pure : pas d'agent_id session — résolution serveur via téléphone SIM. */
    internal fun smsAgentContext(simPhone: String?, smsAgentCode: String?): AgentContext {
        return AgentContext(
            agentId = null,
            agentCode = smsAgentCode?.takeIf { it.isNotBlank() },
            agentTelephone = simPhone?.takeIf { it.isNotBlank() },
        )
    }
}
