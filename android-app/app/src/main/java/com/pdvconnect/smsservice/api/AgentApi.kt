package com.pdvconnect.smsservice.api

import com.google.gson.annotations.SerializedName
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST
import retrofit2.http.Path

data class AgentLoginRequest(
    val identifiant: String,
    val password: String,
)

data class AgentChangePasswordRequest(
    @SerializedName("current_password") val currentPassword: String,
    val password: String,
    @SerializedName("password_confirmation") val passwordConfirmation: String,
)

data class AgentLoginResponse(
    val success: Boolean,
    val message: String? = null,
    val token: String? = null,
    val agent: AgentInfo? = null,
    val dashboard: AgentDashboard? = null,
)

data class AgentInfo(
    val id: Long,
    @SerializedName("code_agent") val codeAgent: String?,
    val nom: String?,
    val prenom: String?,
    val telephone: String?,
    val kiosque: String?,
)

data class AgentDashboard(
    val balances: AgentBalances? = null,
    val stats: AgentStats?,
    val transactions: List<AgentTransaction>?,
)

data class AgentBalances(
    val espece: Double = 0.0,
    val virtuels: List<AgentVirtuelBalance>? = null,
)

data class AgentVirtuelBalance(
    val code: String?,
    val libelle: String?,
    val montant: Double = 0.0,
    @SerializedName("logo_url") val logoUrl: String? = null,
)

data class AgentStats(
    @SerializedName("today_count") val todayCount: Int = 0,
    @SerializedName("today_total") val todayTotal: Double = 0.0,
    @SerializedName("today_commission") val todayCommission: Double = 0.0,
    @SerializedName("month_count") val monthCount: Int = 0,
    @SerializedName("month_total") val monthTotal: Double = 0.0,
    @SerializedName("month_commission") val monthCommission: Double = 0.0,
    @SerializedName("today_by_operateur") val todayByOperateur: List<OperateurStats>? = null,
    @SerializedName("month_by_operateur") val monthByOperateur: List<OperateurStats>? = null,
)

data class OperateurStats(
    val code: String?,
    val libelle: String?,
    val count: Int = 0,
    val total: Double = 0.0,
    val commission: Double = 0.0,
    @SerializedName("logo_url") val logoUrl: String? = null,
)

data class AgentTransaction(
    val id: Long,
    val reference: String?,
    val type: String?,
    val statut: String?,
    val montant: Double = 0.0,
    val commission: Double = 0.0,
    val operateur: String?,
    @SerializedName("operateur_code") val operateurCode: String? = null,
    @SerializedName("operateur_logo_url") val operateurLogoUrl: String? = null,
    val date: String?,
    @SerializedName("can_cancel") val canCancel: Boolean = false,
)

data class AgentCancelRequest(
    val raison: String,
)

interface AgentApi {
    @POST("api/mobile/agent/login")
    suspend fun login(@Body body: AgentLoginRequest): AgentLoginResponse

    @GET("api/mobile/agent/dashboard")
    suspend fun dashboard(@Header("Authorization") authorization: String): AgentLoginResponse

    @POST("api/mobile/agent/transactions/{id}/annuler")
    suspend fun cancelTransaction(
        @Header("Authorization") authorization: String,
        @Path("id") id: Long,
        @Body body: AgentCancelRequest,
    ): AgentLoginResponse

    @POST("api/mobile/agent/logout")
    suspend fun logout(@Header("Authorization") authorization: String): AgentLoginResponse

    @POST("api/mobile/agent/change-password")
    suspend fun changePassword(
        @Header("Authorization") authorization: String,
        @Body body: AgentChangePasswordRequest,
    ): AgentLoginResponse
}

object AgentApiClient {
    fun create(baseUrl: String): AgentApi {
        val base = baseUrl.trimEnd('/')
        val client = ApiClient.baseOkHttpClient()

        return retrofit2.Retrofit.Builder()
            .baseUrl("$base/")
            .client(client)
            .addConverterFactory(retrofit2.converter.gson.GsonConverterFactory.create())
            .build()
            .create(AgentApi::class.java)
    }
}
