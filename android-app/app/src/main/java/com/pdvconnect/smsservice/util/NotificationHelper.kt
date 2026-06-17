package com.pdvconnect.smsservice.util

import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import androidx.core.app.NotificationCompat
import com.pdvconnect.smsservice.PdvConnectApp
import com.pdvconnect.smsservice.R
import com.pdvconnect.smsservice.ui.MainActivity

object NotificationHelper {

    private const val NOTIFICATION_PENDING_TX = 2001
    private const val NOTIFICATION_SYNC_OK = 2002

    fun showPendingTransactions(context: Context, pendingCount: Int) {
        if (pendingCount <= 0) {
            cancel(context, NOTIFICATION_PENDING_TX)
            return
        }

        val open = PendingIntent.getActivity(
            context,
            0,
            Intent(context, MainActivity::class.java),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
        )

        val notification = NotificationCompat.Builder(context, PdvConnectApp.CHANNEL_ALERTS_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_alert)
            .setContentTitle(context.getString(R.string.notif_pending_title))
            .setContentText(
                context.resources.getQuantityString(
                    R.plurals.notif_pending_message,
                    pendingCount,
                    pendingCount,
                ),
            )
            .setStyle(
                NotificationCompat.BigTextStyle().bigText(
                    context.resources.getQuantityString(
                        R.plurals.notif_pending_message,
                        pendingCount,
                        pendingCount,
                    ),
                ),
            )
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setContentIntent(open)
            .setAutoCancel(true)
            .build()

        context.getSystemService(NotificationManager::class.java)
            ?.notify(NOTIFICATION_PENDING_TX, notification)
    }

    fun showSyncSuccess(context: Context, syncedCount: Int) {
        if (syncedCount <= 0) return

        val notification = NotificationCompat.Builder(context, PdvConnectApp.CHANNEL_ALERTS_ID)
            .setSmallIcon(android.R.drawable.ic_menu_upload)
            .setContentTitle(context.getString(R.string.notif_sync_ok_title))
            .setContentText(
                context.resources.getQuantityString(
                    R.plurals.notif_sync_ok_message,
                    syncedCount,
                    syncedCount,
                ),
            )
            .setPriority(NotificationCompat.PRIORITY_DEFAULT)
            .setAutoCancel(true)
            .build()

        context.getSystemService(NotificationManager::class.java)
            ?.notify(NOTIFICATION_SYNC_OK, notification)
    }

    fun cancel(context: Context, id: Int) {
        context.getSystemService(NotificationManager::class.java)?.cancel(id)
    }
}
