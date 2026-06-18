package com.pdvconnect.smsservice.api

import com.google.gson.annotations.SerializedName
import retrofit2.http.Body
import retrofit2.http.POST

data class VerifyConfigCodeRequest(
    val code: String,
)

data class VerifyConfigCodeResponse(
    val valid: Boolean = false,
    val message: String? = null,
)

interface MobileConfigApi {
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
