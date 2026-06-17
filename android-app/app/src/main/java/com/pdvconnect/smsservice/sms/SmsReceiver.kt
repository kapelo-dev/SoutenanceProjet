package com.pdvconnect.smsservice.sms

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Telephony
import android.util.Log
import com.pdvconnect.smsservice.data.AppPreferences
import com.pdvconnect.smsservice.sync.OfflineSyncRepository
import com.pdvconnect.smsservice.util.NotificationHelper
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class SmsReceiver : BroadcastReceiver() {

    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.Default)

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != Telephony.Sms.Intents.SMS_RECEIVED_ACTION) return

        val appContext = context.applicationContext
        // goAsync() : sans cela Android peut tuer le processus avant la fin du traitement.
        val pendingResult = goAsync()

        scope.launch {
            try {
                processIncomingSms(appContext, intent)
            } catch (e: Exception) {
                Log.e(TAG, "Erreur traitement SMS", e)
            } finally {
                pendingResult.finish()
            }
        }
    }

    private suspend fun processIncomingSms(context: Context, intent: Intent) {
        val prefs = AppPreferences(context)
        val consent = prefs.consentAccepted.first()
        val serviceEnabled = prefs.serviceEnabled.first()
        val apiUrl = prefs.apiBaseUrl.first()
        val apiToken = prefs.apiToken.first()
        val filterList = prefs.filterList.first()

        if (!consent || !serviceEnabled || apiUrl.isNullOrBlank() || apiToken.isNullOrBlank()) {
            Log.w(TAG, "Service désactivé ou non configuré — SMS ignoré")
            NotificationHelper.showSmsSkipped(context, "Service SMS inactif ou URL/token manquant")
            return
        }

        ServiceStarter.startForegroundService(context)

        val messages = Telephony.Sms.Intents.getMessagesFromIntent(intent) ?: return
        var processed = 0

        for (sms in messages) {
            val sender = sms.originatingAddress ?: ""
            val body = sms.messageBody ?: ""

            if (filterList.isNotEmpty()) {
                val normalizedSender = sender.replace(" ", "").replace("+", "")
                val matches = filterList.any { filter ->
                    val f = filter.trim().replace(" ", "").replace("+", "")
                    when {
                        f.all { c -> c.isDigit() || c == '+' } -> {
                            normalizedSender.contains(f) || f.contains(normalizedSender.takeLast(8))
                        }
                        else -> {
                            body.contains(filter, ignoreCase = true) ||
                                normalizedSender.contains(filter, ignoreCase = true)
                        }
                    }
                }
                if (!matches) {
                    Log.d(TAG, "SMS de $sender ignoré (filtres: $filterList)")
                    continue
                }
            }

            val parsed = SmsParser.parse(body, sender)
            if (parsed == null) {
                Log.w(TAG, "SMS non reconnu comme transaction: ${body.take(80)}")
                NotificationHelper.showSmsSkipped(context, "SMS reçu mais format non reconnu")
                continue
            }
            if (!parsed.isValid()) {
                Log.w(TAG, "SMS parsé invalide: montant=${parsed.montant}, type=${parsed.type}")
                continue
            }

            withContext(Dispatchers.IO) {
                val id = OfflineSyncRepository.get(context).enqueueSmsTransaction(
                    parsed = parsed,
                    sender = sender,
                    baseUrl = apiUrl,
                    apiToken = apiToken,
                )
                if (id > 0) {
                    processed++
                    Log.i(TAG, "Transaction mise en file #$id (ref=${parsed.reference})")
                }
            }
        }

        if (processed > 0) {
            Log.i(TAG, "$processed transaction(s) traitée(s)")
        }
    }

    companion object {
        private const val TAG = "PdvConnectSmsReceiver"
    }
}
