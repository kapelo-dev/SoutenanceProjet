package com.pdvconnect.smsservice.api

import com.google.gson.annotations.SerializedName
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.Header
import retrofit2.http.POST

/**
 * Payload envoyé vers l'API Laravel POST /api/transactions/from-sms
 */
data class TransactionFromSmsRequest(
    @SerializedName("montant") val montant: Double,
    @SerializedName("type") val type: String,
    @SerializedName("description") val description: String? = null,
    @SerializedName("client_nom") val clientNom: String? = null,
    @SerializedName("client_telephone") val clientTelephone: String? = null,
    @SerializedName("reference") val reference: String? = null,
    @SerializedName("operator_txn_id") val operatorTxnId: String? = null,
    @SerializedName("source") val source: String = "sms",
    @SerializedName("raw_sms") val rawSms: String? = null,
    @SerializedName("commission") val commission: Double? = null,
    @SerializedName("agent_id") val agentId: Long? = null,
    @SerializedName("agent_code") val agentCode: String? = null,
    @SerializedName("agent_telephone") val agentTelephone: String? = null,
    @SerializedName("operator_code") val operatorCode: String? = null,
    @SerializedName("virtual_balance_after") val virtualBalanceAfter: Double? = null
)

data class TransactionFromSmsResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String? = null,
    @SerializedName("transaction") val transaction: TransactionDto? = null,
    @SerializedName("transaction_id") val transactionId: Long? = null
)

data class TransactionDto(
    @SerializedName("id") val id: Long,
    @SerializedName("reference") val reference: String?,
    @SerializedName("montant") val montant: Double,
    @SerializedName("type") val type: String,
    @SerializedName("statut") val statut: String?
)

interface TransactionApi {

    @POST("api/transactions/from-sms")
    suspend fun sendTransactionFromSms(@Body body: TransactionFromSmsRequest): Response<TransactionFromSmsResponse>
}
