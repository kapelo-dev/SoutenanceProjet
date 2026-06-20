package com.pdvconnect.smsservice.util

import android.content.Context
import android.net.ConnectivityManager
import android.net.NetworkCapabilities

object NetworkUtils {

    /**
     * Interface réseau disponible (Wi‑Fi, mobile, Ethernet ou VPN).
     * On n'exige pas NET_CAPABILITY_VALIDATED : sur certaines box / opérateurs
     * Android affiche « connecté » mais la validation Google tarde ou échoue.
     */
    fun isOnline(context: Context): Boolean {
        val cm = context.getSystemService(Context.CONNECTIVITY_SERVICE) as? ConnectivityManager
            ?: return false

        cm.activeNetwork?.let { network ->
            if (hasUsableNetwork(cm.getNetworkCapabilities(network))) return true
        }

        // Secours : activeNetwork peut être null brièvement alors qu'une interface est up.
        for (network in cm.allNetworks) {
            if (hasUsableNetwork(cm.getNetworkCapabilities(network))) return true
        }

        return false
    }

    private fun hasUsableNetwork(caps: NetworkCapabilities?): Boolean {
        if (caps == null) return false
        if (!caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)) return false

        return caps.hasTransport(NetworkCapabilities.TRANSPORT_WIFI) ||
            caps.hasTransport(NetworkCapabilities.TRANSPORT_CELLULAR) ||
            caps.hasTransport(NetworkCapabilities.TRANSPORT_ETHERNET) ||
            caps.hasTransport(NetworkCapabilities.TRANSPORT_VPN)
    }
}
