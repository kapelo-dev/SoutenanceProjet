package com.pdvconnect.smsservice.data.local

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase

@Database(
    entities = [PendingTransactionEntity::class, PendingAgentActionEntity::class],
    version = 2,
    exportSchema = false,
)
abstract class AppDatabase : RoomDatabase() {

    abstract fun pendingTransactionDao(): PendingTransactionDao

    abstract fun pendingAgentActionDao(): PendingAgentActionDao

    companion object {
        @Volatile
        private var instance: AppDatabase? = null

        private val MIGRATION_1_2 = object : androidx.room.migration.Migration(1, 2) {
            override fun migrate(db: androidx.sqlite.db.SupportSQLiteDatabase) {
                db.execSQL(
                    "ALTER TABLE pending_transactions ADD COLUMN transactionCategory TEXT NOT NULL DEFAULT 'commercial'",
                )
                db.execSQL("ALTER TABLE pending_transactions ADD COLUMN sourceAgentCode TEXT")
                db.execSQL("ALTER TABLE pending_transactions ADD COLUMN sourceAgentName TEXT")
            }
        }

        fun get(context: Context): AppDatabase {
            return instance ?: synchronized(this) {
                instance ?: Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "pdv_connect_offline.db",
                )
                    .addMigrations(MIGRATION_1_2)
                    .build().also { instance = it }
            }
        }
    }
}
