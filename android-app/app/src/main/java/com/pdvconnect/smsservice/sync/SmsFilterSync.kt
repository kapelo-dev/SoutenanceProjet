package com.pdvconnect.smsservice.sync

import android.content.Context
import android.util.Log
import com.pdvconnect.smsservice.api.ApiClient
import com.pdvconnect.smsservice.data.AppPreferences
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.withContext

/**
 * Récupère les expéditeurs SMS autorisés définis sur le web (GET /api/mobile/sms-config)
 * et les enregistre localement. En cas d'échec (réseau, serveur), la dernière liste reçue est conservée.
 */
object SmsFilterSync {

    sealed class Result {
        data class Ok(val count: Int) : Result()
        data class Error(val message: String) : Result()
    }

    suspend fun refresh(context: Context): Result = withContext(Dispatchers.IO) {
        val prefs = AppPreferences(context)
        val apiUrl = prefs.apiBaseUrl.first()
        val apiToken = prefs.apiToken.first()

        if (apiUrl.isNullOrBlank() || apiToken.isNullOrBlank()) {
            return@withContext Result.Error("URL ou token non configuré : expéditeurs SMS non récupérés.")
        }

        try {
            val response = ApiClient.create(apiUrl, apiToken).smsConfig()
            if (!response.isSuccessful) {
                Log.w(TAG, "Expéditeurs SMS non récupérés (HTTP ${response.code()})")
                return@withContext Result.Error(
                    if (response.code() == 401) {
                        "Token refusé : expéditeurs SMS non récupérés."
                    } else {
                        "Expéditeurs SMS non récupérés (erreur ${response.code()})."
                    },
                )
            }

            val filtres = response.body()?.filtresSms.orEmpty().map { it.trim() }.filter { it.isNotBlank() }
            prefs.setFilterList(filtres)
            Log.d(TAG, "Expéditeurs SMS autorisés : $filtres")
            Result.Ok(filtres.size)
        } catch (e: Exception) {
            Log.w(TAG, "Expéditeurs SMS non récupérés", e)
            Result.Error("Serveur injoignable : la dernière liste d'expéditeurs SMS est conservée.")
        }
    }

    private const val TAG = "PdvConnectSmsFilters"
}
