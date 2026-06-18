package com.pdvconnect.smsservice.api

import com.google.gson.annotations.SerializedName
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

data class VerifyConfigCodeRequest(
    val code: String,
)

data class VerifyConfigCodeResponse(
    val valid: Boolean = false,
    val message: String? = null,
)

data class AppVersionResponse(
    @SerializedName("version_code") val versionCode: Int = 1,
    @SerializedName("version_name") val versionName: String? = null,
    @SerializedName("min_version_code") val minVersionCode: Int = 1,
    @SerializedName("apk_available") val apkAvailable: Boolean = false,
    @SerializedName("download_page_url") val downloadPageUrl: String? = null,
    @SerializedName("apk_url") val apkUrl: String? = null,
    @SerializedName("updated_at") val updatedAt: String? = null,
)

interface MobileConfigApi {
    @GET("api/mobile/app-version")
    suspend fun appVersion(): AppVersionResponse

    @POST("api/mobile/verify-config-code")
    suspend fun verifyConfigCode(@Body body: VerifyConfigCodeRequest): VerifyConfigCodeResponse
}

object MobileConfigApiClient {
    fun create(baseUrl: String): MobileConfigApi {
        val base = baseUrl.trimEnd('/')
        return retrofit2.Retrofit.Builder()
            .baseUrl("$base/")
            .client(ApiClient.baseOkHttpClient())
            .addConverterFactory(retrofit2.converter.gson.GsonConverterFactory.create())
            .build()
            .create(MobileConfigApi::class.java)
    }
}
