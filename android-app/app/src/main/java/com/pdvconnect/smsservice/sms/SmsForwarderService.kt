package com.pdvconnect.smsservice.sms

import android.app.Notification
import android.app.PendingIntent
import android.app.Service
import android.content.Context
import android.content.Intent
import android.os.Build
import android.os.IBinder
import android.util.Log
import androidx.core.app.NotificationCompat
import com.pdvconnect.smsservice.PdvConnectApp
import com.pdvconnect.smsservice.R
import com.pdvconnect.smsservice.api.ApiClient
import com.pdvconnect.smsservice.api.TransactionFromSmsRequest
import com.pdvconnect.smsservice.ui.MainActivity
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

/**
 * Service optionnel : peut être démarré pour garder l'app "vivante" et améliorer
 * la fiabilité de réception des SMS. Envoie les transactions parsées vers l'API.
 */
class SmsForwarderService : Service() {

    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onCreate() {
        super.onCreate()
        startForeground(NOTIFICATION_ID, createNotification())
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        return START_STICKY
    }

    private fun createNotification(): Notification {
        val open = PendingIntent.getActivity(
            this,
            0,
            Intent(this, MainActivity::class.java),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        return NotificationCompat.Builder(this, PdvConnectApp.CHANNEL_ID)
            .setContentTitle(getString(R.string.app_name))
            .setContentText(getString(R.string.notification_channel_description))
            .setSmallIcon(android.R.drawable.ic_menu_send)
            .setContentIntent(open)
            .setOngoing(true)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .build()
    }

    companion object {
        private const val TAG = "PdvConnectSmsForwarder"
        private const val NOTIFICATION_ID = 1001

        /**
         * Envoie une transaction parsée vers l'API (appelé depuis SmsReceiver ou depuis le service).
         */
        fun enqueueSend(
            context: Context,
            baseUrl: String,
            apiToken: String,
            parsed: SmsParser.ParsedTransaction,
            sender: String
        ) {
            val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)
            scope.launch {
                try {
                    val api = ApiClient.create(baseUrl, apiToken)
                    val request = TransactionFromSmsRequest(
                        montant = parsed.montant,
                        type = parsed.type,
                        description = parsed.rawBody.take(500),
                        clientNom = parsed.clientNom,
                        clientTelephone = parsed.clientTelephone,
                        reference = parsed.reference,
                        operatorTxnId = sender.take(50),
                        source = "sms",
                        rawSms = parsed.rawBody.take(500)
                    )
                    val response = api.sendTransactionFromSms(request)
                    withContext(Dispatchers.Main) {
                        if (response.isSuccessful) {
                            Log.d(TAG, "Transaction envoyée: ${response.body()?.transactionId}")
                        } else {
                            Log.e(TAG, "API error: ${response.code()} ${response.errorBody()?.string()}")
                        }
                    }
                } catch (e: Exception) {
                    Log.e(TAG, "Envoi API failed", e)
                }
            }
        }
    }
}
