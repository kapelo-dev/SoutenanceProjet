<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ServerMetrics
{
    public static function collect(): array
    {
        $cpu = self::cpu();
        $ram = self::ram();
        $disk = self::disk();
        $phpMemory = self::phpMemory();
        $services = self::services();
        $alerts = self::buildAlerts($cpu, $ram, $disk, $phpMemory, $services);
        $health = self::health($alerts);

        return [
            'generated_at' => now()->toIso8601String(),
            'health' => $health,
            'alerts' => $alerts,
            'gauges' => [
                'cpu' => $cpu,
                'ram' => $ram,
                'disk' => $disk,
                'php_memory' => $phpMemory,
            ],
            'services' => $services,
            'system' => self::systemInfo(),
        ];
    }

    protected static function health(array $alerts): array
    {
        $critical = collect($alerts)->where('severity', 'critical')->count();
        $warnings = collect($alerts)->where('severity', 'warning')->count();
        $infos = collect($alerts)->where('severity', 'info')->count();

        if ($critical > 0) {
            return [
                'status' => 'error',
                'label' => $critical . ' problème' . ($critical > 1 ? 's' : '') . ' critique' . ($critical > 1 ? 's' : '') . ' à corriger',
                'summary' => 'Consultez la liste ci-dessous pour savoir quoi faire.',
                'critical' => $critical,
                'warnings' => $warnings,
                'infos' => $infos,
            ];
        }

        if ($warnings > 0) {
            return [
                'status' => 'warning',
                'label' => 'Application fonctionnelle — ' . $warnings . ' point' . ($warnings > 1 ? 's' : '') . ' à surveiller',
                'summary' => 'Aucun blocage immédiat, mais des optimisations sont recommandées.',
                'critical' => 0,
                'warnings' => $warnings,
                'infos' => $infos,
            ];
        }

        return [
            'status' => 'ok',
            'label' => 'Tous les services essentiels sont opérationnels',
            'summary' => 'L\'application fonctionne normalement.',
            'critical' => 0,
            'warnings' => 0,
            'infos' => $infos,
        ];
    }

    protected static function buildAlerts(array $cpu, array $ram, array $disk, array $phpMemory, array $services): array
    {
        $alerts = [];

        $add = function (string $severity, string $title, string $problem, string $action, ?string $component = null) use (&$alerts) {
            $alerts[] = compact('severity', 'title', 'problem', 'action', 'component');
        };

        if ($cpu['status'] === 'error') {
            $add('critical', 'CPU surchargé', 'La charge processeur dépasse 90 %.', 'Réduisez la charge (exports massifs, cron…) ou contactez votre hébergeur pour augmenter les ressources.', 'cpu');
        } elseif ($cpu['status'] === 'warning') {
            $add('warning', 'CPU élevé', 'La charge processeur est entre 70 et 90 %.', 'Surveillez les tâches lourdes. Si cela persiste, planifiez une montée en charge.', 'cpu');
        } elseif ($cpu['status'] === 'info') {
            $add('info', 'CPU non mesurable', 'Métrique indisponible sur cet hébergement.', 'Aucune action requise — normal en environnement mutualisé.', 'cpu');
        }

        if ($ram['status'] === 'error') {
            $add('critical', 'RAM saturée', 'Plus de 90 % de la mémoire serveur est utilisée.', 'Redémarrez les services, libérez de la mémoire ou augmentez la RAM chez l\'hébergeur.', 'ram');
        } elseif ($ram['status'] === 'warning') {
            $add('warning', 'RAM élevée', 'Plus de 75 % de la RAM est utilisée.', 'Surveillez la consommation. Envisagez d\'optimiser les requêtes ou d\'augmenter la RAM.', 'ram');
        } elseif ($ram['status'] === 'info') {
            $add('info', 'RAM non mesurable', 'Métrique indisponible sur cet hébergement.', 'Aucune action requise — normal en environnement mutualisé.', 'ram');
        }

        if ($disk['status'] === 'error') {
            $add('critical', 'Disque plein', 'Plus de 90 % de l\'espace disque est utilisé.', 'Supprimez les fichiers inutiles (logs dans storage/logs/, exports temporaires) ou contactez l\'hébergeur.', 'disk');
        } elseif ($disk['status'] === 'warning') {
            $add('warning', 'Disque presque plein', 'Plus de 80 % de l\'espace disque est utilisé.', 'Nettoyez storage/logs/ et vérifiez les sauvegardes. Commande : php artisan log:clear (si disponible) ou suppression manuelle des vieux logs.', 'disk');
        } elseif ($disk['status'] === 'info') {
            $add('info', 'Disque non mesurable', 'Impossible de lire l\'espace disque.', 'Aucune action requise si l\'application fonctionne.', 'disk');
        }

        if ($phpMemory['status'] === 'warning') {
            $add('warning', 'Mémoire PHP élevée', 'Le processus PHP utilise plus de 85 % de sa limite.', 'Augmentez memory_limit dans php.ini ou optimisez les exports / requêtes lourdes.', 'php_memory');
        }

        foreach ($services as $service) {
            match ($service['key'] ?? '') {
                'database' => match ($service['status']) {
                    'error' => $add(
                        'critical',
                        'Base de données inaccessible',
                        $service['detail'],
                        '1) Vérifiez que MySQL/MariaDB est démarré. 2) Contrôlez le fichier .env : DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD. 3) Testez : php artisan db:show',
                        'database'
                    ),
                    'warning' => $add(
                        'warning',
                        'Base de données lente',
                        'Latence de ' . $service['value'] . ' (> 500 ms).',
                        'Optimisez les requêtes lentes, ajoutez des index ou vérifiez la charge du serveur MySQL.',
                        'database'
                    ),
                    default => null,
                },
                'cache' => $service['status'] === 'error'
                    ? $add('critical', 'Cache défaillant', $service['detail'], 'Vérifiez CACHE_DRIVER dans .env. Si file : chmod -R 775 storage/framework/cache. Puis : php artisan cache:clear', 'cache')
                    : null,
                'storage' => $service['status'] === 'error'
                    ? $add('critical', 'Stockage non accessible', $service['detail'], 'Exécutez : chmod -R 775 storage bootstrap/cache && php artisan storage:link', 'storage')
                    : null,
                'public_disk' => $service['status'] === 'error'
                    ? $add('critical', 'Disque public inaccessible', $service['detail'], 'Exécutez : php artisan storage:link && vérifiez les permissions de storage/app/public', 'public_disk')
                    : null,
                'queue' => $service['status'] === 'warning'
                    ? $add('warning', 'Jobs en échec', $service['value'] . ' job(s) échoué(s).', 'Consultez la table failed_jobs ou exécutez : php artisan queue:failed', 'queue')
                    : null,
                'opcache' => $service['status'] === 'warning'
                    ? $add('info', 'OPcache inactif', 'Le cache opcode PHP n\'est pas activé.', 'Recommandé en production pour de meilleures performances. Contactez l\'hébergeur si besoin.', 'opcache')
                    : null,
                default => null,
            };
        }

        if (config('app.debug') && config('app.env') === 'production') {
            $add('warning', 'Mode debug activé en production', 'APP_DEBUG=true expose des informations sensibles.', 'Dans .env : APP_DEBUG=false puis php artisan config:clear', 'app');
        }

        return $alerts;
    }

    protected static function cpu(): array
    {
        $cores = self::cpuCores();
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

        if (! $load) {
            return self::gauge('CPU', 0, 'info', 'Non disponible', 'Hébergement mutualisé', 'ki-cpu');
        }

        $load1 = round($load[0], 2);
        $percent = $cores > 0 ? min(100, round(($load1 / $cores) * 100, 1)) : min(100, round($load1 * 25, 1));
        $status = $percent >= 90 ? 'error' : ($percent >= 70 ? 'warning' : 'ok');

        return self::gauge('CPU', $percent, $status, $load1 . ' load', $cores . ' cœurs · avg ' . round($load[1], 2) . ' / ' . round($load[2], 2), 'ki-cpu');
    }

    protected static function ram(): array
    {
        $mem = self::readSystemMemory();

        if (! $mem) {
            return self::gauge('RAM', 0, 'info', 'Non disponible', 'Hébergement mutualisé', 'ki-chart');
        }

        $percent = $mem['total'] > 0 ? round(($mem['used'] / $mem['total']) * 100, 1) : 0;
        $status = $percent >= 90 ? 'error' : ($percent >= 75 ? 'warning' : 'ok');

        return self::gauge('RAM', $percent, $status, self::formatBytes($mem['used']), self::formatBytes($mem['available']) . ' libres / ' . self::formatBytes($mem['total']), 'ki-chart');
    }

    protected static function disk(): array
    {
        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());

        if ($free === false || $total === false || $total <= 0) {
            return self::gauge('Disque', 0, 'info', 'Non disponible', 'Mesure impossible', 'ki-folder');
        }

        $used = $total - $free;
        $percent = round(($used / $total) * 100, 1);
        $status = $percent >= 90 ? 'error' : ($percent >= 80 ? 'warning' : 'ok');

        return self::gauge('Disque', $percent, $status, self::formatBytes($used), self::formatBytes($free) . ' libres', 'ki-folder');
    }

    protected static function phpMemory(): array
    {
        $limit = ini_get('memory_limit');
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limitBytes = self::parseMemoryLimit($limit);

        if ($limitBytes <= 0) {
            return self::gauge('PHP', 0, 'ok', self::formatBytes($usage), 'Pic ' . self::formatBytes($peak) . ' · illimité', 'ki-code');
        }

        $percent = round(($usage / $limitBytes) * 100, 1);
        $status = $percent >= 85 ? 'warning' : 'ok';

        return self::gauge('PHP', $percent, $status, self::formatBytes($usage), 'Pic ' . self::formatBytes($peak) . ' · max ' . $limit, 'ki-code');
    }

    protected static function services(): array
    {
        $services = [];

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 1);
            $services[] = self::service('database', 'Base de données', $latency > 500 ? 'warning' : 'ok', $latency . ' ms', config('database.default') . ' · connectée');
        } catch (\Throwable $e) {
            $services[] = self::service('database', 'Base de données', 'error', 'Hors ligne', $e->getMessage());
        }

        try {
            $key = 'health_' . uniqid();
            Cache::put($key, 1, 10);
            $ok = Cache::get($key) === 1;
            Cache::forget($key);
            $services[] = self::service('cache', 'Cache', $ok ? 'ok' : 'error', $ok ? 'OK' : 'Erreur', config('cache.default'));
        } catch (\Throwable $e) {
            $services[] = self::service('cache', 'Cache', 'error', 'Erreur', $e->getMessage());
        }

        $storageOk = is_writable(storage_path()) && is_writable(storage_path('logs'));
        $services[] = self::service('storage', 'Stockage', $storageOk ? 'ok' : 'error', $storageOk ? 'OK' : 'Erreur', $storageOk ? 'storage/ en écriture' : 'Permissions insuffisantes');

        if (Schema::hasTable('failed_jobs')) {
            try {
                $failed = (int) DB::table('failed_jobs')->count();
                $services[] = self::service('queue', 'File d\'attente', $failed > 0 ? 'warning' : 'ok', (string) $failed . ' échec(s)', config('queue.default'));
            } catch (\Throwable) {
            }
        }

        $opcache = function_exists('opcache_get_status') && @opcache_get_status(false);
        $services[] = self::service('opcache', 'OPcache', $opcache ? 'ok' : 'warning', $opcache ? 'Actif' : 'Inactif', $opcache ? 'Performance optimisée' : 'Optionnel en dev');

        try {
            Storage::disk('public')->directories();
            $services[] = self::service('public_disk', 'Disque public', 'ok', 'OK', 'storage/app/public');
        } catch (\Throwable $e) {
            $services[] = self::service('public_disk', 'Disque public', 'error', 'Erreur', $e->getMessage());
        }

        return $services;
    }

    protected static function systemInfo(): array
    {
        $info = [
            ['label' => 'Système', 'value' => php_uname('s') . ' ' . php_uname('r')],
            ['label' => 'PHP', 'value' => PHP_VERSION],
            ['label' => 'Laravel', 'value' => app()->version()],
            ['label' => 'Serveur web', 'value' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI'],
            ['label' => 'Environnement', 'value' => config('app.env')],
            ['label' => 'Debug', 'value' => config('app.debug') ? 'Activé' : 'Désactivé'],
            ['label' => 'Fuseau horaire', 'value' => config('app.timezone')],
        ];

        if ($uptime = self::readUptime()) {
            $info[] = ['label' => 'Uptime serveur', 'value' => $uptime];
        }

        return $info;
    }

    protected static function service(string $key, string $name, string $status, string $value, string $detail): array
    {
        return compact('key', 'name', 'status', 'value', 'detail');
    }

    protected static function gauge(string $label, float $percent, string $status, string $value, string $detail, string $icon = 'ki-chart'): array
    {
        return compact('label', 'percent', 'status', 'value', 'detail', 'icon');
    }

    protected static function readSystemMemory(): ?array
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/meminfo')) {
            $content = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $content, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $content, $available);
            if (empty($total[1])) {
                return null;
            }
            $totalBytes = (int) $total[1] * 1024;
            $availBytes = (int) ($available[1] ?? 0) * 1024;

            return ['total' => $totalBytes, 'available' => $availBytes, 'used' => $totalBytes - $availBytes];
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            $hwMem = trim((string) shell_exec('sysctl -n hw.memsize 2>/dev/null'));
            if (is_numeric($hwMem) && (int) $hwMem > 0) {
                $total = (int) $hwMem;
                $vm = (string) shell_exec('vm_stat 2>/dev/null');
                if ($vm) {
                    preg_match('/Pages free:\s+(\d+)/', $vm, $free);
                    preg_match('/Pages inactive:\s+(\d+)/', $vm, $inactive);
                    $available = ((int) ($free[1] ?? 0) + (int) ($inactive[1] ?? 0)) * 4096;

                    return ['total' => $total, 'available' => min($available, $total), 'used' => $total - min($available, $total)];
                }
            }
        }

        return null;
    }

    protected static function cpuCores(): int
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/cpuinfo')) {
            return max(1, preg_match_all('/^processor\s/m', (string) file_get_contents('/proc/cpuinfo')));
        }
        if (PHP_OS_FAMILY === 'Darwin') {
            return max(1, (int) trim((string) shell_exec('sysctl -n hw.ncpu 2>/dev/null')));
        }

        return 1;
    }

    protected static function readUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/uptime')) {
            return self::formatUptime((int) explode(' ', (string) file_get_contents('/proc/uptime'))[0]);
        }

        return null;
    }

    protected static function formatUptime(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $mins = intdiv($seconds % 3600, 60);

        return $days > 0 ? "{$days}j {$hours}h" : ($hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m");
    }

    protected static function formatBytes(int|float $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    protected static function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        if ($limit === '-1') {
            return 0;
        }
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
