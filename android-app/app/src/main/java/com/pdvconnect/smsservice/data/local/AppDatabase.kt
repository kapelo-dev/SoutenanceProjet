package com.pdvconnect.smsservice.data.local

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase

@Database(
    entities = [PendingTransactionEntity::class, PendingAgentActionEntity::class],
    version = 1,
    exportSchema = false,
)
abstract class AppDatabase : RoomDatabase() {

    abstract fun pendingTransactionDao(): PendingTransactionDao

    abstract fun pendingAgentActionDao(): PendingAgentActionDao

    companion object {
        @Volatile
        private var instance: AppDatabase? = null

        fun get(context: Context): AppDatabase {
            return instance ?: synchronized(this) {
                instance ?: Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "pdv_connect_offline.db",
                ).build().also { instance = it }
            }
        }
    }
}
