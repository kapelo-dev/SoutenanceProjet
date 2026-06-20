package com.pdvconnect.smsservice.data.local

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.Query
import androidx.room.Update
import kotlinx.coroutines.flow.Flow

@Dao
interface PendingTransactionDao {

    @Insert
    suspend fun insert(entity: PendingTransactionEntity): Long

    @Update
    suspend fun update(entity: PendingTransactionEntity)

    @Query("DELETE FROM pending_transactions WHERE id = :id")
    suspend fun deleteById(id: Long)

    @Query("SELECT * FROM pending_transactions WHERE id = :id LIMIT 1")
    suspend fun findById(id: Long): PendingTransactionEntity?

    @Query(
        """
        UPDATE pending_transactions SET status = 'pending'
        WHERE status = 'syncing'
        """
    )
    suspend fun resetSyncingToPending()

    @Query(
        """
        SELECT * FROM pending_transactions
        WHERE status IN ('pending', 'failed', 'syncing')
        ORDER BY createdAt ASC
        """
    )
    suspend fun getPending(): List<PendingTransactionEntity>

    @Query("SELECT COUNT(*) FROM pending_transactions WHERE status IN ('pending', 'failed', 'syncing')")
    fun observePendingCount(): Flow<Int>

    @Query(
        """
        SELECT COUNT(*) FROM pending_transactions
        WHERE status IN ('pending', 'failed', 'syncing')
        """
    )
    suspend fun pendingCount(): Int

    @Query(
        """
        SELECT * FROM pending_transactions
        WHERE reference IS NOT NULL AND reference = :reference
        LIMIT 1
        """
    )
    suspend fun findByReference(reference: String): PendingTransactionEntity?
}

@Dao
interface PendingAgentActionDao {

    @Insert
    suspend fun insert(entity: PendingAgentActionEntity): Long

    @Update
    suspend fun update(entity: PendingAgentActionEntity)

    @Query("DELETE FROM pending_agent_actions WHERE id = :id")
    suspend fun deleteById(id: Long)

    @Query(
        """
        SELECT * FROM pending_agent_actions
        WHERE status IN ('pending', 'failed')
        ORDER BY createdAt ASC
        """
    )
    suspend fun getPending(): List<PendingAgentActionEntity>

    @Query("SELECT COUNT(*) FROM pending_agent_actions WHERE status IN ('pending', 'failed')")
    suspend fun pendingCount(): Int
}
