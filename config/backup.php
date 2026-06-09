<?php

return [
    'enabled' => filter_var(env('BACKUP_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'disk' => env('BACKUP_DISK', 'backups'),

    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 7),

    'schedule_time' => env('BACKUP_SCHEDULE_TIME', '02:00'),

    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

    'prefix' => env('BACKUP_PREFIX', 'pdvconnect'),

    'stale_hours' => (int) env('BACKUP_STALE_HOURS', 48),
];
