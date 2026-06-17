package com.pdvconnect.smsservice.data

import android.content.Context
import com.google.gson.Gson
import com.pdvconnect.smsservice.api.AgentDashboard
import com.pdvconnect.smsservice.api.AgentInfo
import com.pdvconnect.smsservice.api.AgentLoginResponse
import kotlinx.coroutines.flow.first

class AgentDashboardCache(private val context: Context) {

    private val prefs = AppPreferences(context)
    private val gson = Gson()

    data class CachedDashboard(
        val agent: AgentInfo?,
        val dashboard: AgentDashboard?,
        val cachedAt: Long = System.currentTimeMillis(),
    )

    suspend fun save(response: AgentLoginResponse) {
        val payload = CachedDashboard(
            agent = response.agent,
            dashboard = response.dashboard,
            cachedAt = System.currentTimeMillis(),
        )
        prefs.setAgentDashboardCache(gson.toJson(payload))
    }

    suspend fun load(): CachedDashboard? {
        val json = prefs.agentDashboardCache.first() ?: return null
        return try {
            gson.fromJson(json, CachedDashboard::class.java)
        } catch (_: Exception) {
            null
        }
    }

    suspend fun clear() {
        prefs.setAgentDashboardCache(null)
    }
}
