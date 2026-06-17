package com.pdvconnect.smsservice.sync

import android.content.Context
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.NetworkRequest
import android.util.Log

object NetworkMonitor {

    private const val TAG = "PdvConnectNetwork"
    private var registered = false
    private var callback: ConnectivityManager.NetworkCallback? = null

    fun register(context: Context) {
        if (registered) return

        val appContext = context.applicationContext
        val cm = appContext.getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager

        val networkCallback = object : ConnectivityManager.NetworkCallback() {
            override fun onAvailable(network: Network) {
                Log.d(TAG, "Réseau disponible — synchronisation planifiée")
                SyncScheduler.scheduleImmediate(appContext)
            }

            override fun onCapabilitiesChanged(network: Network, caps: NetworkCapabilities) {
                if (caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET) &&
                    caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_VALIDATED)
                ) {
                    SyncScheduler.scheduleImmediate(appContext)
                }
            }
        }

        val request = NetworkRequest.Builder()
            .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
            .build()

        cm.registerNetworkCallback(request, networkCallback)
        callback = networkCallback
        registered = true
    }
}
