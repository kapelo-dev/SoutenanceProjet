package com.pdvconnect.smsservice.util

import android.Manifest
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.core.content.ContextCompat
import com.pdvconnect.smsservice.PdvConnectApp
import com.pdvconnect.smsservice.R
import com.pdvconnect.smsservice.ui.MainActivity

object NotificationHelper {

    private const val TAG = "PdvConnectNotify"
    private const val NOTIFICATION_PENDING_TX = 2001
    private const val NOTIFICATION_SYNC_OK = 2002
    private const val NOTIFICATION_SMS_SKIPPED = 2003
    private const val NOTIFICATION_SYNC_ERROR = 2004

    private fun canNotify(context: Context): Boolean {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU) return true
        return ContextCompat.checkSelfPermission(
            context,
            Manifest.permission.POST_NOTIFICATIONS,
        ) == PackageManager.PERMISSION_GRANTED
    }

    private fun notify(context: Context, id: Int, notification: android.app.Notification) {
        if (!canNotify(context)) {
            Log.w(TAG, "Permission notifications refusée — alerte non affichée (id=$id)")
            return
        }
        context.getSystemService(NotificationManager::class.java)?.notify(id, notification)
    }

    fun showPendingTransactions(context: Context, pendingCount: Int, offline: Boolean = false) {
        if (pendingCount <= 0) {
            cancel(context, NOTIFICATION_PENDING_TX)
            return
        }

        val pluralRes = if (offline) R.plurals.notif_pending_offline_message else R.plurals.notif_pending_message
        val message = context.resources.getQuantityString(pluralRes, pendingCount, pendingCount)

        val open = PendingIntent.getActivity(
            context,
            0,
            Intent(context, MainActivity::class.java),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
        )

        val notification = NotificationCompat.Builder(context, PdvConnectApp.CHANNEL_ALERTS_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_alert)
            .setContentTitle(context.getString(R.string.notif_pending_title))
            .setContentText(message)
            .setStyle(NotificationCompat.BigTextStyle().bigText(message))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setContentIntent(open)
            .setAutoCancel(true)
            .build()

        notify(context, NOTIFICATION_PENDING_TX, notification)
    }

    fun showSyncError(context: Context, detail: String) {
        val notification = NotificationCompat.Builder(context, PdvConnectApp.CHANNEL_ALERTS_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_alert)
            .setContentTitle(context.getString(R.string.notif_sync_error_title))
            .setContentText(detail.take(120))
            .setStyle(NotificationCompat.BigTextStyle().bigText(detail.take(300)))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()

        notify(context, NOTIFICATION_SYNC_ERROR, notification)
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

        notify(context, NOTIFICATION_SYNC_OK, notification)
    }

    fun showSmsSkipped(context: Context, reason: String) {
        val notification = NotificationCompat.Builder(context, PdvConnectApp.CHANNEL_ALERTS_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setContentTitle(context.getString(R.string.notif_sms_skipped_title))
            .setContentText(reason)
            .setStyle(NotificationCompat.BigTextStyle().bigText(reason))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()

        notify(context, NOTIFICATION_SMS_SKIPPED, notification)
    }

    fun showSmsProcessed(context: Context, reference: String?, success: Boolean, detail: String? = null) {
        val refLabel = reference?.takeIf { it.isNotBlank() } ?: "—"
        val title = if (success) {
            context.getString(R.string.notif_sms_ok_title)
        } else {
            context.getString(R.string.notif_sync_error_title)
        }
        val text = if (success) {
            context.getString(R.string.notif_sms_ok_message, refLabel)
        } else {
            detail ?: context.getString(R.string.notif_sms_failed_generic)
        }

        val notification = NotificationCompat.Builder(context, PdvConnectApp.CHANNEL_ALERTS_ID)
            .setSmallIcon(if (success) android.R.drawable.ic_menu_upload else android.R.drawable.ic_dialog_alert)
            .setContentTitle(title)
            .setContentText(text)
            .setStyle(NotificationCompat.BigTextStyle().bigText(text))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .build()

        notify(context, if (success) NOTIFICATION_SYNC_OK else NOTIFICATION_SYNC_ERROR, notification)
    }

    fun cancel(context: Context, id: Int) {
        context.getSystemService(NotificationManager::class.java)?.cancel(id)
    }
}
