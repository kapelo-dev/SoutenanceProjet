package com.pdvconnect.smsservice.sms

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Telephony
import android.util.Log
import com.pdvconnect.smsservice.data.AppPreferences
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

        scope.launch {
            val prefs = AppPreferences(context)
            val consent = prefs.consentAccepted.first()
            val serviceEnabled = prefs.serviceEnabled.first()
            val apiUrl = prefs.apiBaseUrl.first()
            val apiToken = prefs.apiToken.first()
            val filterList = prefs.filterList.first()

            if (!consent || !serviceEnabled || apiUrl.isNullOrBlank() || apiToken.isNullOrBlank()) {
                Log.d(TAG, "Service disabled or not configured, ignoring SMS")
                return@launch
            }

            val messages = Telephony.Sms.Intents.getMessagesFromIntent(intent) ?: return@launch
            for (sms in messages) {
                val sender = sms.originatingAddress ?: ""
                val body = sms.messageBody ?: ""

                if (filterList.isNotEmpty()) {
                    val normalizedSender = sender.replace(" ", "").replace("+", "")
                    val matches = filterList.any { filter ->
                        val f = filter.trim().replace(" ", "").replace("+", "")
                        when {
                            f.all { c -> c.isDigit() || c == '+' } -> {
                                // Filtre numérique : comparer au sender
                                normalizedSender.contains(f) || f.contains(normalizedSender.takeLast(8))
                            }
                            else -> {
                                // Filtre nom (ex. FLOOZ) : sender ou corps du SMS
                                body.contains(filter, ignoreCase = true) || normalizedSender.contains(filter, ignoreCase = true)
                            }
                        }
                    }
                    if (!matches) {
                        Log.d(TAG, "SMS from $sender ignored (filters: $filterList)")
                        continue
                    }
                }

                val parsed = SmsParser.parse(body, sender) ?: continue
                if (!parsed.isValid()) continue

                withContext(Dispatchers.IO) {
                    SmsForwarderService.enqueueSend(
                        context = context,
                        baseUrl = apiUrl,
                        apiToken = apiToken,
                        parsed = parsed,
                        sender = sender
                    )
                }
            }
        }
    }

    companion object {
        private const val TAG = "PdvConnectSmsReceiver"
    }
}
