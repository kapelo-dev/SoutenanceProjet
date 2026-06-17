package com.pdvconnect.smsservice.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "pending_transactions")
data class PendingTransactionEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val montant: Double,
    val type: String,
    val description: String? = null,
    val clientNom: String? = null,
    val clientTelephone: String? = null,
    val reference: String? = null,
    val operatorTxnId: String? = null,
    val rawSms: String? = null,
    val commission: Double? = null,
    val agentCode: String? = null,
    val operatorCode: String? = null,
    val virtualBalanceAfter: Double? = null,
    val sender: String? = null,
    val apiBaseUrl: String,
    val apiToken: String,
    val status: String = STATUS_PENDING,
    val attemptCount: Int = 0,
    val lastError: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
) {
    companion object {
        const val STATUS_PENDING = "pending"
        const val STATUS_SYNCING = "syncing"
        const val STATUS_FAILED = "failed"
    }
}
