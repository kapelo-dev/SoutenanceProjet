package com.pdvconnect.smsservice

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import com.pdvconnect.smsservice.sync.NetworkMonitor
import com.pdvconnect.smsservice.sync.SyncScheduler
import com.pdvconnect.smsservice.sms.ServiceStarter
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch

class PdvConnectApp : Application() {

    override fun onCreate() {
        super.onCreate()
        createNotificationChannels()
        NetworkMonitor.register(this)
        SyncScheduler.schedulePeriodic(this)
        CoroutineScope(SupervisorJob() + Dispatchers.IO).launch {
            ServiceStarter.ensureRunningIfConfigured(this@PdvConnectApp)
        }
    }

    private fun createNotificationChannels() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val manager = getSystemService(NotificationManager::class.java)

            val serviceChannel = NotificationChannel(
                CHANNEL_ID,
                getString(R.string.notification_channel_name),
                NotificationManager.IMPORTANCE_LOW,
            ).apply {
                description = getString(R.string.notification_channel_description)
            }

            val alertsChannel = NotificationChannel(
                CHANNEL_ALERTS_ID,
                getString(R.string.notification_alerts_channel_name),
                NotificationManager.IMPORTANCE_HIGH,
            ).apply {
                description = getString(R.string.notification_alerts_channel_description)
            }

            manager.createNotificationChannel(serviceChannel)
            manager.createNotificationChannel(alertsChannel)
        }
    }

    companion object {
        const val CHANNEL_ID = "pdv_connect_sms_channel"
        const val CHANNEL_ALERTS_ID = "pdv_connect_alerts_channel"
    }
}
