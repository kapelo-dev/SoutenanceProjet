<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseBackupService
{
    public function isEnabled(): bool
    {
        return (bool) config('backup.enabled', true);
    }

    public function minioConfigured(): bool
    {
        return filled(config('filesystems.disks.backups.key'))
            && filled(config('filesystems.disks.backups.secret'))
            && filled(config('filesystems.disks.backups.bucket'))
            && filled(config('filesystems.disks.backups.endpoint'));
    }

    public function minioReachable(): bool
    {
        if (! $this->minioConfigured()) {
            return false;
        }

        try {
            Storage::disk(config('backup.disk', 'backups'))->files('', true);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function run(string $trigger = 'manual'): DatabaseBackup
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Les sauvegardes sont désactivées (BACKUP_ENABLED=false).');
        }

        if (! $this->minioConfigured()) {
            throw new RuntimeException('MinIO non configuré. Vérifiez MINIO_* dans .env.');
        }

        if (config('database.default') !== 'mysql') {
            throw new RuntimeException('Seul MySQL est supporté pour les sauvegardes automatiques.');
        }

        $startedAt = microtime(true);
        $connection = config('database.connections.mysql');
        $database = $connection['database'] ?? '';
        $disk = config('backup.disk', 'backups');
        $filename = sprintf(
            '%s_%s_%s.sql.gz',
            config('backup.prefix', 'pdvconnect'),
            $database,
            now()->format('Y-m-d_His')
        );
        $remotePath = 'database/' . now()->format('Y/m') . '/' . $filename;
        $localPath = storage_path('app/backups/' . $filename);

        if (! is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0755, true);
        }

        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'disk' => $disk,
            'path' => $remotePath,
            'database_name' => $database,
            'status' => 'failed',
            'trigger' => $trigger,
        ]);

        try {
            $this->dumpDatabase($connection, $localPath);

            $size = filesize($localPath) ?: 0;
            $checksum = md5_file($localPath) ?: null;

            $this->ensureBackupBucket();
            Storage::disk($disk)->put($remotePath, fopen($localPath, 'r'));

            if (! Storage::disk($disk)->exists($remotePath)) {
                throw new RuntimeException('Échec de l\'upload vers MinIO.');
            }

            $backup->update([
                'status' => 'success',
                'size_bytes' => $size,
                'checksum' => $checksum,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            $this->pruneOldBackups();

            return $backup->fresh();
        } catch (\Throwable $e) {
            $backup->update([
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            throw $e;
        } finally {
            if (is_file($localPath)) {
                @unlink($localPath);
            }
        }
    }

    protected function dumpDatabase(array $connection, string $outputPath): void
    {
        $mysqldump = config('backup.mysqldump_path', 'mysqldump');

        if (! $this->commandExists($mysqldump)) {
            throw new RuntimeException("mysqldump introuvable ({$mysqldump}). Installez MySQL client ou définissez BACKUP_MYSQLDUMP_PATH.");
        }

        $host = $connection['host'] ?? '127.0.0.1';
        $port = (string) ($connection['port'] ?? '3306');
        $database = $connection['database'] ?? '';
        $username = $connection['username'] ?? '';
        $password = $connection['password'] ?? '';

        $dumpFile = $outputPath . '.tmp.sql';

        $process = Process::env([
            'MYSQL_PWD' => $password,
        ])->timeout(600)->run([
            $mysqldump,
            '-h', $host,
            '-P', $port,
            '-u', $username,
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--routines',
            '--triggers',
            $database,
        ]);

        if (! $process->successful()) {
            throw new RuntimeException(trim($process->errorOutput() ?: $process->output() ?: 'mysqldump a échoué.'));
        }

        file_put_contents($dumpFile, $process->output());

        if (! is_file($dumpFile) || filesize($dumpFile) === 0) {
            @unlink($dumpFile);
            throw new RuntimeException('Le dump SQL est vide.');
        }

        $gz = gzopen($outputPath, 'wb9');
        if (! $gz) {
            @unlink($dumpFile);
            throw new RuntimeException('Impossible de compresser le dump.');
        }

        gzwrite($gz, (string) file_get_contents($dumpFile));
        gzclose($gz);
        @unlink($dumpFile);
    }

    protected function commandExists(string $command): bool
    {
        if (str_contains($command, '/')) {
            return is_executable($command);
        }

        $result = Process::run(['which', $command]);

        return $result->successful();
    }

    public function pruneOldBackups(): int
    {
        if (! Schema::hasTable('database_backups')) {
            return 0;
        }

        $retentionDays = max(1, (int) config('backup.retention_days', 7));
        $cutoff = now()->subDays($retentionDays);
        $deleted = 0;

        DatabaseBackup::query()
            ->where('status', 'success')
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->each(function (DatabaseBackup $backup) use (&$deleted) {
                try {
                    if (Storage::disk($backup->disk)->exists($backup->path)) {
                        Storage::disk($backup->disk)->delete($backup->path);
                    }
                } catch (\Throwable) {
                    // ignore storage errors during prune
                }

                $backup->delete();
                $deleted++;
            });

        return $deleted;
    }

    public function metrics(): array
    {
        if (! Schema::hasTable('database_backups')) {
            return $this->emptyMetrics('Table database_backups absente — exécutez php artisan migrate.');
        }

        $lastSuccess = DatabaseBackup::query()
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        $lastFailed = DatabaseBackup::query()
            ->where('status', 'failed')
            ->latest('created_at')
            ->first();

        $totalSuccess = DatabaseBackup::query()->where('status', 'success')->count();
        $totalSize = (int) DatabaseBackup::query()->where('status', 'success')->sum('size_bytes');

        $minioConfigured = $this->minioConfigured();
        $minioReachable = $minioConfigured && $this->minioReachable();

        $staleHours = (int) config('backup.stale_hours', 48);
        $isStale = $lastSuccess
            ? $lastSuccess->created_at->lt(now()->subHours($staleHours))
            : true;

        $status = 'error';
        if (! $this->isEnabled()) {
            $status = 'info';
        } elseif (! $minioConfigured) {
            $status = 'error';
        } elseif (! $minioReachable) {
            $status = 'error';
        } elseif (! $lastSuccess) {
            $status = 'warning';
        } elseif ($isStale) {
            $status = 'warning';
        } else {
            $status = 'ok';
        }

        $recent = DatabaseBackup::query()
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (DatabaseBackup $b) => [
                'id' => $b->id,
                'filename' => $b->filename,
                'status' => $b->status,
                'size' => $b->human_size,
                'size_bytes' => $b->size_bytes,
                'duration_ms' => $b->duration_ms,
                'trigger' => $b->trigger,
                'database' => $b->database_name,
                'path' => $b->path,
                'checksum' => $b->checksum,
                'error' => $b->error_message,
                'created_at' => $b->created_at?->toIso8601String(),
                'created_at_human' => $b->created_at?->diffForHumans(),
            ])
            ->values()
            ->all();

        return [
            'enabled' => $this->isEnabled(),
            'status' => $status,
            'minio' => [
                'configured' => $minioConfigured,
                'reachable' => $minioReachable,
                'bucket' => config('filesystems.disks.backups.bucket'),
                'endpoint' => config('filesystems.disks.backups.endpoint'),
            ],
            'retention_days' => (int) config('backup.retention_days', 7),
            'schedule_time' => config('backup.schedule_time', '02:00'),
            'stale_hours' => $staleHours,
            'is_stale' => $isStale,
            'last_success' => $lastSuccess ? [
                'filename' => $lastSuccess->filename,
                'size' => $lastSuccess->human_size,
                'duration_ms' => $lastSuccess->duration_ms,
                'created_at' => $lastSuccess->created_at?->toIso8601String(),
                'created_at_human' => $lastSuccess->created_at?->diffForHumans(),
                'path' => $lastSuccess->path,
                'checksum' => $lastSuccess->checksum,
            ] : null,
            'last_failed' => $lastFailed ? [
                'filename' => $lastFailed->filename,
                'error' => $lastFailed->error_message,
                'created_at_human' => $lastFailed->created_at?->diffForHumans(),
            ] : null,
            'total_success' => $totalSuccess,
            'total_size' => $this->formatBytes($totalSize),
            'total_size_bytes' => $totalSize,
            'recent' => $recent,
        ];
    }

    protected function emptyMetrics(string $message): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'status' => 'warning',
            'minio' => [
                'configured' => $this->minioConfigured(),
                'reachable' => false,
                'bucket' => config('filesystems.disks.backups.bucket'),
                'endpoint' => config('filesystems.disks.backups.endpoint'),
            ],
            'retention_days' => (int) config('backup.retention_days', 7),
            'schedule_time' => config('backup.schedule_time', '02:00'),
            'stale_hours' => (int) config('backup.stale_hours', 48),
            'is_stale' => true,
            'last_success' => null,
            'last_failed' => null,
            'total_success' => 0,
            'total_size' => '0 o',
            'total_size_bytes' => 0,
            'recent' => [],
            'message' => $message,
        ];
    }

    protected function ensureBackupBucket(): void
    {
        $config = config('filesystems.disks.backups');
        $bucket = $config['bucket'] ?? null;

        if (! filled($bucket)) {
            return;
        }

        $client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $config['region'] ?? 'us-east-1',
            'endpoint' => $config['endpoint'] ?? null,
            'use_path_style_endpoint' => (bool) ($config['use_path_style_endpoint'] ?? true),
            'credentials' => [
                'key' => $config['key'] ?? '',
                'secret' => $config['secret'] ?? '',
            ],
        ]);

        if (! $client->doesBucketExist($bucket)) {
            $client->createBucket(['Bucket' => $bucket]);
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, 1) . ' ' . $units[$i];
    }
}
