package com.pdvconnect.smsservice.sync

import android.content.Context
import android.util.Log
import com.pdvconnect.smsservice.api.AgentApiClient
import com.pdvconnect.smsservice.api.AgentCancelRequest
import com.pdvconnect.smsservice.api.ApiClient
import com.pdvconnect.smsservice.api.TransactionFromSmsRequest
import com.pdvconnect.smsservice.data.AgentDashboardCache
import com.pdvconnect.smsservice.data.local.AppDatabase
import com.pdvconnect.smsservice.data.local.PendingAgentActionEntity
import com.pdvconnect.smsservice.data.local.PendingTransactionEntity
import com.pdvconnect.smsservice.sms.SmsParser
import com.pdvconnect.smsservice.util.NetworkUtils
import com.pdvconnect.smsservice.util.NotificationHelper
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import java.io.IOException

class OfflineSyncRepository(private val context: Context) {

    private val db = AppDatabase.get(context)
    private val txDao = db.pendingTransactionDao()
    private val actionDao = db.pendingAgentActionDao()
    private val dashboardCache = AgentDashboardCache(context)

    data class SyncResult(
        val transactionsSynced: Int = 0,
        val actionsSynced: Int = 0,
        val stillPending: Int = 0,
        val networkError: Boolean = false,
    )

    suspend fun enqueueSmsTransaction(
        parsed: SmsParser.ParsedTransaction,
        sender: String,
        baseUrl: String,
        apiToken: String,
    ): Long = withContext(Dispatchers.IO) {
        parsed.reference?.let { ref ->
            if (txDao.findByReference(ref) != null) {
                Log.d(TAG, "Transaction déjà en file (réf. $ref)")
                return@withContext -1L
            }
        }

        val entity = PendingTransactionEntity(
            montant = parsed.montant,
            type = parsed.type,
            description = parsed.rawBody.take(500),
            clientNom = parsed.clientNom,
            clientTelephone = parsed.clientTelephone,
            reference = parsed.reference,
            operatorTxnId = sender.take(50),
            rawSms = parsed.rawBody.take(500),
            commission = parsed.commission,
            agentCode = parsed.agentCode,
            operatorCode = parsed.operatorName,
            virtualBalanceAfter = parsed.virtualBalanceAfter,
            sender = sender,
            apiBaseUrl = baseUrl,
            apiToken = apiToken,
        )

        val id = txDao.insert(entity)
        Log.d(TAG, "Transaction mise en file locale #$id")

        if (NetworkUtils.isOnline(context)) {
            syncAll()
        } else {
            val pending = txDao.pendingCount()
            NotificationHelper.showPendingTransactions(context, pending)
            SyncScheduler.scheduleImmediate(context)
        }

        id
    }

    suspend fun enqueueCancelTransaction(
        transactionId: Long,
        raison: String,
        agentToken: String,
        apiBaseUrl: String,
    ): Long = withContext(Dispatchers.IO) {
        val id = actionDao.insert(
            PendingAgentActionEntity(
                actionType = PendingAgentActionEntity.TYPE_CANCEL_TRANSACTION,
                transactionId = transactionId,
                raison = raison,
                agentToken = agentToken,
                apiBaseUrl = apiBaseUrl,
            ),
        )

        if (NetworkUtils.isOnline(context)) {
            syncAll()
        } else {
            NotificationHelper.showPendingTransactions(
                context,
                txDao.pendingCount() + actionDao.pendingCount(),
            )
            SyncScheduler.scheduleImmediate(context)
        }

        id
    }

    suspend fun syncAll(): SyncResult = withContext(Dispatchers.IO) {
        if (!NetworkUtils.isOnline(context)) {
            val pending = txDao.pendingCount() + actionDao.pendingCount()
            NotificationHelper.showPendingTransactions(context, pending)
            return@withContext SyncResult(stillPending = pending, networkError = true)
        }

        var txSynced = 0
        var actionSynced = 0
        var hadNetworkError = false

        for (item in txDao.getPending()) {
            txDao.update(item.copy(status = PendingTransactionEntity.STATUS_SYNCING))
            try {
                val api = ApiClient.create(item.apiBaseUrl, item.apiToken)
                val request = TransactionFromSmsRequest(
                    montant = item.montant,
                    type = item.type,
                    description = item.description,
                    clientNom = item.clientNom,
                    clientTelephone = item.clientTelephone,
                    reference = item.reference,
                    operatorTxnId = item.operatorTxnId,
                    source = "sms",
                    rawSms = item.rawSms,
                    commission = item.commission,
                    agentCode = item.agentCode,
                    operatorCode = item.operatorCode,
                    virtualBalanceAfter = item.virtualBalanceAfter,
                )
                val response = api.sendTransactionFromSms(request)
                if (response.isSuccessful) {
                    txDao.deleteById(item.id)
                    txSynced++
                    Log.d(TAG, "Transaction #${item.id} synchronisée")
                } else {
                    val error = response.errorBody()?.string()?.take(200) ?: "HTTP ${response.code()}"
                    txDao.update(
                        item.copy(
                            status = PendingTransactionEntity.STATUS_FAILED,
                            attemptCount = item.attemptCount + 1,
                            lastError = error,
                        ),
                    )
                }
            } catch (e: IOException) {
                hadNetworkError = true
                txDao.update(
                    item.copy(
                        status = PendingTransactionEntity.STATUS_PENDING,
                        attemptCount = item.attemptCount + 1,
                        lastError = e.message,
                    ),
                )
                break
            } catch (e: Exception) {
                txDao.update(
                    item.copy(
                        status = PendingTransactionEntity.STATUS_FAILED,
                        attemptCount = item.attemptCount + 1,
                        lastError = e.message,
                    ),
                )
            }
        }

        if (!hadNetworkError) {
            for (action in actionDao.getPending()) {
                actionDao.update(action.copy(status = PendingAgentActionEntity.STATUS_SYNCING))
                try {
                    val api = AgentApiClient.create(action.apiBaseUrl)
                    val response = api.cancelTransaction(
                        "Bearer ${action.agentToken}",
                        action.transactionId,
                        AgentCancelRequest(action.raison),
                    )
                    if (response.success) {
                        actionDao.deleteById(action.id)
                        actionSynced++
                        response.dashboard?.let {
                            dashboardCache.save(
                                com.pdvconnect.smsservice.api.AgentLoginResponse(
                                    success = true,
                                    agent = response.agent,
                                    dashboard = it,
                                ),
                            )
                        }
                    } else {
                        actionDao.update(
                            action.copy(
                                status = PendingAgentActionEntity.STATUS_FAILED,
                                attemptCount = action.attemptCount + 1,
                                lastError = response.message,
                            ),
                        )
                    }
                } catch (e: IOException) {
                    hadNetworkError = true
                    actionDao.update(
                        action.copy(
                            status = PendingAgentActionEntity.STATUS_PENDING,
                            attemptCount = action.attemptCount + 1,
                            lastError = e.message,
                        ),
                    )
                    break
                } catch (e: Exception) {
                    actionDao.update(
                        action.copy(
                            status = PendingAgentActionEntity.STATUS_FAILED,
                            attemptCount = action.attemptCount + 1,
                            lastError = e.message,
                        ),
                    )
                }
            }
        }

        val stillPending = txDao.pendingCount() + actionDao.pendingCount()

        if (stillPending > 0) {
            NotificationHelper.showPendingTransactions(context, stillPending)
        } else {
            NotificationHelper.cancel(context, 2001)
        }

        if (txSynced + actionSynced > 0) {
            NotificationHelper.showSyncSuccess(context, txSynced + actionSynced)
        }

        SyncResult(
            transactionsSynced = txSynced,
            actionsSynced = actionSynced,
            stillPending = stillPending,
            networkError = hadNetworkError,
        )
    }

    fun observePendingTransactionCount() = txDao.observePendingCount()

    suspend fun pendingTotalCount(): Int = txDao.pendingCount() + actionDao.pendingCount()

    companion object {
        private const val TAG = "PdvConnectOfflineSync"

        @Volatile
        private var instance: OfflineSyncRepository? = null

        fun get(context: Context): OfflineSyncRepository {
            return instance ?: synchronized(this) {
                instance ?: OfflineSyncRepository(context.applicationContext).also { instance = it }
            }
        }
    }
}
