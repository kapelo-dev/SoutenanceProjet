package com.pdvconnect.smsservice.util

import android.content.Context
import com.pdvconnect.smsservice.data.AppPreferences
import kotlinx.coroutines.flow.first

object AgentContextProvider {

    data class AgentContext(
        val agentId: Long?,
        val agentCode: String?,
        val agentTelephone: String?,
    )

    suspend fun resolve(context: Context, smsAgentCode: String?): AgentContext {
        val prefs = AppPreferences(context)
        val boundId = prefs.boundAgentId.first()
        val boundCode = prefs.boundAgentCode.first()
        val boundPhone = prefs.boundAgentTelephone.first()
        val simPhone = SimUtils.getSimPhoneNumber(context)

        return AgentContext(
            agentId = boundId,
            agentCode = smsAgentCode?.takeIf { it.isNotBlank() } ?: boundCode,
            agentTelephone = boundPhone ?: simPhone,
        )
    }
}
