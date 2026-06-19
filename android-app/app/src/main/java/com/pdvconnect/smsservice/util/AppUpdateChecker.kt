package com.pdvconnect.smsservice.util

import android.content.Context
import com.pdvconnect.smsservice.BuildConfig
import com.pdvconnect.smsservice.api.MobileConfigApiClient

object AppUpdateChecker {

    data class Result(
        val updateRequired: Boolean,
        val currentVersionCode: Int = BuildConfig.VERSION_CODE,
        val currentVersionName: String = BuildConfig.VERSION_NAME,
        val serverVersionCode: Int = 0,
        val serverVersionName: String? = null,
        val downloadUrl: String? = null,
        val apkDirectUrl: String? = null,
    )

    suspend fun check(baseUrl: String): Result {
        val response = MobileConfigApiClient.create(baseUrl).appVersion()
        val current = BuildConfig.VERSION_CODE
        val minRequired = response.minVersionCode.coerceAtLeast(1)
        val latest = response.versionCode.coerceAtLeast(1)
        // Blocage uniquement si en dessous du minimum obligatoire (pas à chaque version « latest »).
        val updateRequired = current < minRequired

        val downloadUrl = when {
            !response.downloadPageUrl.isNullOrBlank() -> response.downloadPageUrl
            !response.apkUrl.isNullOrBlank() -> response.apkUrl
            else -> null
        }

        return Result(
            updateRequired = updateRequired,
            currentVersionCode = current,
            currentVersionName = BuildConfig.VERSION_NAME,
            serverVersionCode = latest,
            serverVersionName = response.versionName,
            downloadUrl = downloadUrl,
            apkDirectUrl = response.apkUrl,
        )
    }

    fun openDownload(context: Context, result: Result) {
        val url = result.apkDirectUrl?.takeIf { it.isNotBlank() }
            ?: result.downloadUrl
            ?: return
        val intent = android.content.Intent(android.content.Intent.ACTION_VIEW, android.net.Uri.parse(url))
        context.startActivity(intent)
    }
}
