package com.pdvconnect.smsservice.util

import android.Manifest
import android.content.Context
import android.content.pm.PackageManager
import android.os.Build
import android.telephony.SubscriptionManager
import android.telephony.TelephonyManager
import androidx.core.content.ContextCompat

object SimUtils {

    fun getSimPhoneNumber(context: Context): String? {
        if (!hasPhonePermission(context)) return null

        val tm = context.getSystemService(Context.TELEPHONY_SERVICE) as? TelephonyManager ?: return null
        tm.line1Number?.takeIf { isValidPhone(it) }?.let { return normalize(it) }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
            val sm = context.getSystemService(Context.TELEPHONY_SUBSCRIPTION_SERVICE) as? SubscriptionManager
            sm?.activeSubscriptionInfoList?.forEach { info ->
                info.number?.takeIf { isValidPhone(it) }?.let { return normalize(it) }
            }
        }

        return null
    }

    private fun hasPhonePermission(context: Context): Boolean {
        val readPhone = ContextCompat.checkSelfPermission(
            context,
            Manifest.permission.READ_PHONE_STATE,
        ) == PackageManager.PERMISSION_GRANTED

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val readNumbers = ContextCompat.checkSelfPermission(
                context,
                Manifest.permission.READ_PHONE_NUMBERS,
            ) == PackageManager.PERMISSION_GRANTED
            return readPhone || readNumbers
        }

        return readPhone
    }

    private fun isValidPhone(value: String): Boolean {
        return value.replace(Regex("\\D"), "").length >= 8
    }

    private fun normalize(value: String): String {
        return value.replace(Regex("[^0-9+]"), "").trim()
    }
}
