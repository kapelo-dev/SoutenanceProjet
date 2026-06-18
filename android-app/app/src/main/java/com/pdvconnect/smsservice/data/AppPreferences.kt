package com.pdvconnect.smsservice.data

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "pdv_connect_settings")

class AppPreferences(private val context: Context) {

    val consentAccepted: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_CONSENT] ?: false
    }

    val serviceEnabled: Flow<Boolean> = context.dataStore.data.map { prefs ->
        prefs[KEY_SERVICE_ENABLED] ?: false
    }

    val apiBaseUrl: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_API_URL]?.takeIf { it.isNotBlank() }
    }

    val apiToken: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_API_TOKEN]?.takeIf { it.isNotBlank() }
    }

    /** Code local (PIN) propre à ce téléphone — différent du code web. */
    val localConfigPin: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_LOCAL_CONFIG_PIN]?.takeIf { it.isNotBlank() }
    }

    /** Liste des filtres SMS (numéros ou noms type FLOOZ), un par ligne. Vide = accepter tous. */
    val filterList: Flow<List<String>> = context.dataStore.data.map { prefs ->
        val listValue = prefs[KEY_FILTER_LIST].orEmpty()
        if (listValue.isNotBlank()) {
            listValue.split(FILTER_DELIMITER).map { it.trim() }.filter { it.isNotBlank() }
        } else {
            prefs[KEY_FILTER_NUMBER_LEGACY]?.trim()?.takeIf { it.isNotBlank() }?.let { listOf(it) } ?: emptyList()
        }
    }

    val agentSessionToken: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_AGENT_TOKEN]?.takeIf { it.isNotBlank() }
    }

    val agentDashboardCache: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_AGENT_DASHBOARD_CACHE]
    }

    suspend fun hasLocalConfigPin(): Boolean = !localConfigPin.first().isNullOrBlank()

    suspend fun setConsent(accepted: Boolean) {
        context.dataStore.edit { it[KEY_CONSENT] = accepted }
    }

    suspend fun setServiceEnabled(enabled: Boolean) {
        context.dataStore.edit { it[KEY_SERVICE_ENABLED] = enabled }
    }

    suspend fun setApiBaseUrl(url: String) {
        context.dataStore.edit { it[KEY_API_URL] = url.trim() }
    }

    suspend fun setApiToken(token: String) {
        context.dataStore.edit { it[KEY_API_TOKEN] = token }
    }

    suspend fun setLocalConfigPin(pin: String?) {
        context.dataStore.edit {
            if (pin.isNullOrBlank()) it.remove(KEY_LOCAL_CONFIG_PIN)
            else it[KEY_LOCAL_CONFIG_PIN] = pin.trim()
        }
    }

    suspend fun setFilterList(list: List<String>) {
        context.dataStore.edit {
            val value = list.map { it.trim() }.filter { it.isNotBlank() }.joinToString(FILTER_DELIMITER)
            if (value.isEmpty()) it.remove(KEY_FILTER_LIST)
            else it[KEY_FILTER_LIST] = value
        }
    }

    suspend fun setAgentSessionToken(token: String?) {
        context.dataStore.edit {
            if (token.isNullOrBlank()) it.remove(KEY_AGENT_TOKEN)
            else it[KEY_AGENT_TOKEN] = token.trim()
        }
    }

    suspend fun setAgentDashboardCache(json: String?) {
        context.dataStore.edit {
            if (json.isNullOrBlank()) it.remove(KEY_AGENT_DASHBOARD_CACHE)
            else it[KEY_AGENT_DASHBOARD_CACHE] = json
        }
    }

    suspend fun saveAll(
        consent: Boolean,
        serviceEnabled: Boolean,
        apiUrl: String,
        apiToken: String,
        filterList: List<String>,
    ) {
        context.dataStore.edit { prefs ->
            prefs[KEY_CONSENT] = consent
            prefs[KEY_SERVICE_ENABLED] = serviceEnabled
            prefs[KEY_API_URL] = apiUrl.trim()
            prefs[KEY_API_TOKEN] = apiToken
            val filterValue = filterList.map { it.trim() }.filter { it.isNotBlank() }.joinToString(FILTER_DELIMITER)
            if (filterValue.isEmpty()) prefs.remove(KEY_FILTER_LIST)
            else prefs[KEY_FILTER_LIST] = filterValue
        }
    }

    companion object {
        private const val FILTER_DELIMITER = "\n"
        private val KEY_CONSENT = booleanPreferencesKey("consent_accepted")
        private val KEY_SERVICE_ENABLED = booleanPreferencesKey("service_enabled")
        private val KEY_API_URL = stringPreferencesKey("api_base_url")
        private val KEY_API_TOKEN = stringPreferencesKey("api_token")
        private val KEY_LOCAL_CONFIG_PIN = stringPreferencesKey("local_config_pin")
        private val KEY_FILTER_LIST = stringPreferencesKey("filter_list")
        private val KEY_FILTER_NUMBER_LEGACY = stringPreferencesKey("filter_number")
        private val KEY_AGENT_TOKEN = stringPreferencesKey("agent_session_token")
        private val KEY_AGENT_DASHBOARD_CACHE = stringPreferencesKey("agent_dashboard_cache")
    }
}
