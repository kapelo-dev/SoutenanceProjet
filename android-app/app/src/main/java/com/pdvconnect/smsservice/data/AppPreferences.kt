package com.pdvconnect.smsservice.data

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
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

    /** Liste des filtres SMS (numéros ou noms type FLOOZ), un par ligne. Vide = accepter tous. */
    val filterList: Flow<List<String>> = context.dataStore.data.map { prefs ->
        val listValue = prefs[KEY_FILTER_LIST].orEmpty()
        if (listValue.isNotBlank()) {
            listValue.split(FILTER_DELIMITER).map { it.trim() }.filter { it.isNotBlank() }
        } else {
            // Migration : ancienne clé filter_number (un seul numéro)
            prefs[KEY_FILTER_NUMBER_LEGACY]?.trim()?.takeIf { it.isNotBlank() }?.let { listOf(it) } ?: emptyList()
        }
    }

    /** Code pour accéder à la page de configuration. Vide = pas de verrouillage. */
    val configAccessCode: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_CONFIG_ACCESS_CODE]?.takeIf { it.isNotBlank() }
    }

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

    suspend fun setFilterList(list: List<String>) {
        context.dataStore.edit {
            val value = list.map { it.trim() }.filter { it.isNotBlank() }.joinToString(FILTER_DELIMITER)
            if (value.isEmpty()) it.remove(KEY_FILTER_LIST)
            else it[KEY_FILTER_LIST] = value
        }
    }

    suspend fun setConfigAccessCode(code: String?) {
        context.dataStore.edit {
            if (code.isNullOrBlank()) it.remove(KEY_CONFIG_ACCESS_CODE)
            else it[KEY_CONFIG_ACCESS_CODE] = code.trim()
        }
    }

    suspend fun saveAll(
        consent: Boolean,
        serviceEnabled: Boolean,
        apiUrl: String,
        apiToken: String,
        filterList: List<String>,
        configAccessCode: String?
    ) {
        context.dataStore.edit { prefs ->
            prefs[KEY_CONSENT] = consent
            prefs[KEY_SERVICE_ENABLED] = serviceEnabled
            prefs[KEY_API_URL] = apiUrl.trim()
            prefs[KEY_API_TOKEN] = apiToken
            val filterValue = filterList.map { it.trim() }.filter { it.isNotBlank() }.joinToString(FILTER_DELIMITER)
            if (filterValue.isEmpty()) prefs.remove(KEY_FILTER_LIST)
            else prefs[KEY_FILTER_LIST] = filterValue
            if (configAccessCode.isNullOrBlank()) prefs.remove(KEY_CONFIG_ACCESS_CODE)
            else prefs[KEY_CONFIG_ACCESS_CODE] = configAccessCode.trim()
        }
    }

    companion object {
        private const val FILTER_DELIMITER = "\n"
        private val KEY_CONSENT = booleanPreferencesKey("consent_accepted")
        private val KEY_SERVICE_ENABLED = booleanPreferencesKey("service_enabled")
        private val KEY_API_URL = stringPreferencesKey("api_base_url")
        private val KEY_API_TOKEN = stringPreferencesKey("api_token")
        private val KEY_FILTER_LIST = stringPreferencesKey("filter_list")
        private val KEY_FILTER_NUMBER_LEGACY = stringPreferencesKey("filter_number")
        private val KEY_CONFIG_ACCESS_CODE = stringPreferencesKey("config_access_code")
    }
}
