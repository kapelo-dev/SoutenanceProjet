package com.pdvconnect.smsservice.sms

import android.content.Context
import android.content.Intent
import android.os.Build
import com.pdvconnect.smsservice.data.AppPreferences
import kotlinx.coroutines.flow.first

object ServiceStarter {

    /** Démarre le service en premier plan si le transfert SMS est activé et configuré. */
    suspend fun ensureRunningIfConfigured(context: Context) {
        val prefs = AppPreferences(context.applicationContext)
        if (!prefs.serviceEnabled.first()) return
        if (prefs.apiBaseUrl.first().isNullOrBlank() || prefs.apiToken.first().isNullOrBlank()) return
        startForegroundService(context.applicationContext)
    }

    fun startForegroundService(context: Context) {
        val intent = Intent(context, SmsForwarderService::class.java)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            context.startForegroundService(intent)
        } else {
            context.startService(intent)
        }
    }
}
