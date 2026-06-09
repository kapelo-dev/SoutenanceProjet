<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database {--trigger=scheduled : manual|scheduled|api}';

    protected $description = 'Sauvegarde la base MySQL et envoie le dump vers MinIO';

    public function handle(DatabaseBackupService $service): int
    {
        if (! $service->isEnabled()) {
            $this->warn('Sauvegarde désactivée (BACKUP_ENABLED=false).');

            return self::SUCCESS;
        }

        $trigger = $this->option('trigger') ?: 'scheduled';

        $this->info('Démarrage de la sauvegarde…');

        try {
            $backup = $service->run($trigger);
            $this->info("Sauvegarde réussie : {$backup->filename} ({$backup->human_size}) → {$backup->path}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Échec : ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
