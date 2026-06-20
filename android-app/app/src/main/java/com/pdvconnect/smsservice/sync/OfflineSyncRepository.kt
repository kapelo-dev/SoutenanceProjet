package com.pdvconnect.smsservice.sync

import android.content.Context
import android.util.Log
import com.google.gson.JsonParser
import com.pdvconnect.smsservice.api.AgentApiClient
import com.pdvconnect.smsservice.api.AgentCancelRequest
import com.pdvconnect.smsservice.api.ApiClient
import com.pdvconnect.smsservice.api.TransactionFromSmsRequest
import com.pdvconnect.smsservice.data.AgentDashboardCache
import com.pdvconnect.smsservice.data.local.AppDatabase
import com.pdvconnect.smsservice.data.local.PendingAgentActionEntity
import com.pdvconnect.smsservice.data.local.PendingTransactionEntity
import com.pdvconnect.smsservice.sms.SmsParser
import com.pdvconnect.smsservice.util.AgentContextProvider
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
        val lastError: String? = null,
    )

    enum class ItemOutcome {
        SYNCED,
        QUEUED_OFFLINE,
        QUEUED_SERVER_ERROR,
        FAILED,
    }

    data class EnqueueResult(
        val queueId: Long,
        val syncResult: SyncResult? = null,
        val skippedReason: String? = null,
        val itemOutcome: ItemOutcome = ItemOutcome.QUEUED_OFFLINE,
        val itemError: String? = null,
    )

    suspend fun enqueueSmsTransaction(
        parsed: SmsParser.ParsedTransaction,
        sender: String,
        baseUrl: String,
        apiToken: String,
    ): EnqueueResult = withContext(Dispatchers.IO) {
        var queueId: Long

        parsed.reference?.let { ref ->
            val existing = txDao.findByReference(ref)
            if (existing != null) {
                Log.d(TAG, "Réf. $ref déjà en file (#${existing.id}, ${existing.status})")
                queueId = existing.id
                if (existing.status == PendingTransactionEntity.STATUS_SYNCING) {
                    txDao.update(existing.copy(status = PendingTransactionEntity.STATUS_PENDING))
                }
                val syncResult = syncAll(notifyUser = false)
                return@withContext buildEnqueueResult(
                    queueId = queueId,
                    syncResult = syncResult,
                    skippedReason = "Réf. $ref — nouvelle tentative d'envoi",
                )
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
            transactionCategory = parsed.transactionCategory,
            sourceAgentCode = parsed.sourceAgentCode,
            sourceAgentName = parsed.sourceAgentName,
            virtualBalanceAfter = parsed.virtualBalanceAfter,
            sender = sender,
            apiBaseUrl = baseUrl,
            apiToken = apiToken,
        )

        queueId = txDao.insert(entity)
        Log.d(TAG, "Transaction mise en file locale #$queueId")

        val syncResult = if (NetworkUtils.isOnline(context)) {
            syncAll(notifyUser = false)
        } else {
            SyncScheduler.scheduleImmediate(context)
            null
        }

        buildEnqueueResult(queueId = queueId, syncResult = syncResult)
    }

    private suspend fun buildEnqueueResult(
        queueId: Long,
        syncResult: SyncResult?,
        skippedReason: String? = null,
    ): EnqueueResult {
        val item = txDao.findById(queueId)
        val online = NetworkUtils.isOnline(context)

        val outcome = when {
            item == null -> ItemOutcome.SYNCED
            item.status == PendingTransactionEntity.STATUS_FAILED -> ItemOutcome.FAILED
            online -> ItemOutcome.QUEUED_SERVER_ERROR
            else -> ItemOutcome.QUEUED_OFFLINE
        }

        return EnqueueResult(
            queueId = queueId,
            syncResult = syncResult,
            skippedReason = skippedReason,
            itemOutcome = outcome,
            itemError = item?.lastError,
        )
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
                offline = true,
            )
            SyncScheduler.scheduleImmediate(context)
        }

        id
    }

    suspend fun syncAll(notifyUser: Boolean = true): SyncResult = withContext(Dispatchers.IO) {
        txDao.resetSyncingToPending()

        if (!NetworkUtils.isOnline(context)) {
            val pending = txDao.pendingCount() + actionDao.pendingCount()
            if (notifyUser && pending > 0) {
                NotificationHelper.showPendingTransactions(context, pending, offline = true)
            }
            return@withContext SyncResult(stillPending = pending, networkError = true)
        }

        var txSynced = 0
        var actionSynced = 0
        var hadNetworkError = false
        var lastApiError: String? = null

        for (item in txDao.getPending()) {
            txDao.update(item.copy(status = PendingTransactionEntity.STATUS_SYNCING))
            try {
                val api = ApiClient.create(item.apiBaseUrl, item.apiToken)
                val agentContext = AgentContextProvider.resolve(context, item.agentCode)

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
                    agentId = agentContext.agentId,
                    agentCode = agentContext.agentCode,
                    agentTelephone = agentContext.agentTelephone,
                    operatorCode = item.operatorCode,
                    virtualBalanceAfter = item.virtualBalanceAfter,
                    transactionCategory = item.transactionCategory,
                    sourceAgentCode = item.sourceAgentCode,
                    sourceAgentName = item.sourceAgentName,
                )
                val response = api.sendTransactionFromSms(request)
                if (response.isSuccessful) {
                    txDao.deleteById(item.id)
                    txSynced++
                    Log.d(TAG, "Transaction #${item.id} synchronisée")
                } else {
                    val error = parseApiError(response.errorBody()?.string(), response.code())
                    lastApiError = error
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
                lastApiError = e.message ?: "Erreur réseau"
                txDao.update(
                    item.copy(
                        status = PendingTransactionEntity.STATUS_PENDING,
                        attemptCount = item.attemptCount + 1,
                        lastError = lastApiError,
                    ),
                )
                break
            } catch (e: Exception) {
                lastApiError = e.message ?: "Erreur inconnue"
                txDao.update(
                    item.copy(
                        status = PendingTransactionEntity.STATUS_FAILED,
                        attemptCount = item.attemptCount + 1,
                        lastError = lastApiError,
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
                    lastApiError = e.message ?: "Erreur réseau"
                    actionDao.update(
                        action.copy(
                            status = PendingAgentActionEntity.STATUS_PENDING,
                            attemptCount = action.attemptCount + 1,
                            lastError = lastApiError,
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

        if (notifyUser) {
            if (stillPending > 0) {
                if (hadNetworkError) {
                    NotificationHelper.showPendingTransactions(context, stillPending, offline = false)
                    lastApiError?.let { NotificationHelper.showSyncError(context, it) }
                } else {
                    NotificationHelper.showPendingTransactions(context, stillPending, offline = false)
                    lastApiError?.let { NotificationHelper.showSyncError(context, it) }
                }
            } else {
                NotificationHelper.cancel(context, 2001)
            }

            if (txSynced + actionSynced > 0) {
                NotificationHelper.showSyncSuccess(context, txSynced + actionSynced)
            }
        }

        SyncResult(
            transactionsSynced = txSynced,
            actionsSynced = actionSynced,
            stillPending = stillPending,
            networkError = hadNetworkError,
            lastError = lastApiError,
        )
    }

    private fun parseApiError(body: String?, httpCode: Int): String {
        if (!body.isNullOrBlank()) {
            try {
                val json = JsonParser.parseString(body)
                if (json.isJsonObject) {
                    val msg = json.asJsonObject.get("message")?.asString
                    if (!msg.isNullOrBlank()) return msg.take(300)
                }
            } catch (_: Exception) {
            }
            return body.take(200)
        }
        return "HTTP $httpCode"
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
