package com.pdvconnect.smsservice.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "pending_agent_actions")
data class PendingAgentActionEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val actionType: String,
    val transactionId: Long,
    val raison: String,
    val agentToken: String,
    val apiBaseUrl: String,
    val status: String = STATUS_PENDING,
    val attemptCount: Int = 0,
    val lastError: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
) {
    companion object {
        const val TYPE_CANCEL_TRANSACTION = "cancel_transaction"
        const val STATUS_PENDING = "pending"
        const val STATUS_SYNCING = "syncing"
        const val STATUS_FAILED = "failed"
    }
}
