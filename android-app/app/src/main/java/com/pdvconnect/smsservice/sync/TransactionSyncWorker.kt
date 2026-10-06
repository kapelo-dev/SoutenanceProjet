package com.pdvconnect.smsservice.sync

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.pdvconnect.smsservice.util.NotificationHelper

class TransactionSyncWorker(
    context: Context,
    params: WorkerParameters,
) : CoroutineWorker(context, params) {

    override suspend fun doWork(): Result {
        // Toutes les 15 min : prend en compte les expéditeurs modifiés sur le web (sans bloquer la synchro)
        SmsFilterSync.refresh(applicationContext)

        val repo = OfflineSyncRepository.get(applicationContext)
        val result = repo.syncAll()

        return when {
            result.stillPending == 0 -> Result.success()
            result.networkError -> Result.retry()
            else -> Result.success()
        }
    }
}
