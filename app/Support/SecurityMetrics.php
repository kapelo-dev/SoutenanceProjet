<?php

namespace App\Support;

use App\Models\SecurityAlertResolution;
use App\Models\SystemLog;
use App\Services\IpBlockService;
use Illuminate\Support\Facades\DB;

class SecurityMetrics
{
    public static function collect(): array
    {
        $ipBlockService = app(IpBlockService::class);
        $since24h = now()->subDay();
        $since7d = now()->subDays(7);

        $failed24h = SystemLog::where('action', 'login_failed')->where('created_at', '>=', $since24h)->count();
        $success24h = SystemLog::where('action', 'login')->where('created_at', '>=', $since24h)->count();
        $failed7d = SystemLog::where('action', 'login_failed')->where('created_at', '>=', $since7d)->count();

        $suspiciousIps = self::suspiciousIps($since24h, 3, $ipBlockService);
        $blockedIps = $ipBlockService->listActive();
        $topIps = self::topFailedIps($since7d, 8);
        $timeline = self::timeline($since24h);
        $recentEvents = self::recentEvents(15);
        $sensitive24h = SystemLog::whereIn('action', ['delete', 'export'])
            ->where('created_at', '>=', $since24h)
            ->count();

        $compromiseSignals = self::compromiseSignals($since24h);

        $stats = [
            'login_failed_24h' => $failed24h,
            'login_success_24h' => $success24h,
            'login_failed_7d' => $failed7d,
            'suspicious_ips_count' => $suspiciousIps->count(),
            'blocked_ips_count' => $blockedIps->count(),
            'blocked_attempts_24h' => $failed24h,
            'sensitive_actions_24h' => $sensitive24h,
            'compromise_signals' => $compromiseSignals->count(),
        ];

        $alerts = self::buildAlerts($stats, $suspiciousIps, $compromiseSignals);
        $health = self::health($alerts);

        return [
            'generated_at' => now()->toIso8601String(),
            'health' => $health,
            'alerts' => $alerts,
            'stats' => $stats,
            'gauges' => [
                'auth_threat' => self::gaugeAuthThreat($failed24h, $success24h),
                'failed_logins' => self::gaugeCount('Échecs connexion (24h)', $failed24h, 10, 'ki-cross-circle'),
                'suspicious_ips' => self::gaugeCount('IPs suspectes', $suspiciousIps->count(), 5, 'ki-geolocation'),
                'sensitive_ops' => self::gaugeCount('Actions sensibles (24h)', $sensitive24h, 20, 'ki-shield-cross'),
            ],
            'timeline' => $timeline,
            'top_ips' => $topIps,
            'suspicious_ips' => $suspiciousIps,
            'blocked_ips' => $blockedIps,
            'auto_block_threshold' => config('security.auto_block_threshold', 5),
            'recent_events' => $recentEvents,
            'monitoring_tools' => self::monitoringTools(),
        ];
    }

    protected static function suspiciousIps($since, int $minFailures, IpBlockService $ipBlockService)
    {
        return SystemLog::query()
            ->select('ip_address', DB::raw('COUNT(*) as failures'), DB::raw('MAX(created_at) as last_attempt'))
            ->where('action', 'login_failed')
            ->where('created_at', '>=', $since)
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->having('failures', '>=', $minFailures)
            ->orderByDesc('failures')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'ip' => $row->ip_address,
                'failures' => (int) $row->failures,
                'last_attempt' => $row->last_attempt,
                'is_blocked' => $ipBlockService->isBlocked($row->ip_address),
            ]);
    }

    protected static function topFailedIps($since, int $limit)
    {
        return SystemLog::query()
            ->select('ip_address', DB::raw('COUNT(*) as failures'))
            ->where('action', 'login_failed')
            ->where('created_at', '>=', $since)
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderByDesc('failures')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'ip' => $row->ip_address,
                'failures' => (int) $row->failures,
            ]);
    }

    protected static function compromiseSignals($since)
    {
        $ipsWithFailures = SystemLog::where('action', 'login_failed')
            ->where('created_at', '>=', $since)
            ->whereNotNull('ip_address')
            ->distinct()
            ->pluck('ip_address');

        if ($ipsWithFailures->isEmpty()) {
            return collect();
        }

        return SystemLog::with('utilisateur')
            ->where('action', 'login')
            ->where('created_at', '>=', $since)
            ->whereIn('ip_address', $ipsWithFailures)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'ip' => $log->ip_address,
                'user' => $log->utilisateur?->nom_complet ?? '—',
                'at' => $log->created_at->toIso8601String(),
                'description' => $log->description,
            ]);
    }

    protected static function timeline($since): array
    {
        $hours = collect(range(23, 0))->map(function ($h) use ($since) {
            $start = now()->subHours($h)->startOfHour();
            $end = $start->copy()->endOfHour();

            return [
                'label' => $start->format('H\h'),
                'failed' => SystemLog::where('action', 'login_failed')
                    ->whereBetween('created_at', [$start, $end])->count(),
                'success' => SystemLog::where('action', 'login')
                    ->whereBetween('created_at', [$start, $end])->count(),
            ];
        });

        return $hours->values()->all();
    }

    protected static function recentEvents(int $limit): array
    {
        return SystemLog::with('utilisateur')
            ->whereIn('action', ['login_failed', 'login', 'logout', 'delete', 'export'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'action_label' => self::actionLabel($log->action),
                'severity' => self::actionSeverity($log->action),
                'description' => $log->description,
                'ip' => $log->ip_address,
                'user' => $log->utilisateur?->nom_complet,
                'at' => $log->created_at->toIso8601String(),
            ])
            ->all();
    }

    protected static function actionLabel(string $action): string
    {
        return match ($action) {
            'login_failed' => 'Échec connexion',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'delete' => 'Suppression',
            'export' => 'Export',
            default => ucfirst($action),
        };
    }

    protected static function actionSeverity(string $action): string
    {
        return match ($action) {
            'login_failed' => 'warning',
            'delete' => 'critical',
            'export' => 'info',
            default => 'ok',
        };
    }

    protected static function gaugeAuthThreat(int $failed, int $success): array
    {
        $total = max(1, $failed + $success);
        $riskPct = (int) round(($failed / $total) * 100);
        $status = $failed >= 10 ? 'error' : ($failed >= 3 ? 'warning' : 'ok');

        return [
            'label' => 'Niveau de menace auth.',
            'value' => $failed . ' échec(s) / ' . $success . ' succès',
            'detail' => $failed >= 10 ? 'Activité suspecte élevée' : ($failed > 0 ? 'Surveillance recommandée' : 'Aucune tentative suspecte'),
            'percent' => min(100, $riskPct),
            'status' => $status,
            'icon' => 'ki-shield-search',
        ];
    }

    protected static function gaugeCount(string $label, int $count, int $warningAt, string $icon): array
    {
        $status = $count >= $warningAt * 2 ? 'error' : ($count >= $warningAt ? 'warning' : 'ok');
        $percent = min(100, (int) round(($count / max(1, $warningAt * 2)) * 100));

        return [
            'label' => $label,
            'value' => (string) $count,
            'detail' => $count === 0 ? 'Rien à signaler' : 'Seuil alerte : ' . $warningAt,
            'percent' => $percent,
            'status' => $status,
            'icon' => $icon,
        ];
    }

    protected static function buildAlerts(array $stats, $suspiciousIps, $compromiseSignals): array
    {
        $alerts = [];

        $massiveAuth = $stats['login_failed_24h'] >= 10;
        $unusualAuth = ! $massiveAuth && $stats['login_failed_24h'] >= 3;
        $hasSuspiciousIps = $suspiciousIps->isNotEmpty();
        $hasCompromise = $compromiseSignals->isNotEmpty();

        SecurityAlertResolution::clearIfConditionEnded('auth.failed_massive', $massiveAuth);
        SecurityAlertResolution::clearIfConditionEnded('auth.failed_unusual', $unusualAuth);
        SecurityAlertResolution::clearIfConditionEnded('network.suspicious_ips', $hasSuspiciousIps);
        SecurityAlertResolution::clearIfConditionEnded('compromise.same_ip_login', $hasCompromise);

        if ($massiveAuth && ! SecurityAlertResolution::isSuppressed('auth.failed_massive')) {
            $alerts[] = self::alertPayload(
                'auth.failed_massive',
                'critical',
                'Tentatives de connexion massives',
                $stats['login_failed_24h'] . ' échecs de connexion en 24h.',
                'Vérifiez les IPs suspectes, bloquez-les si besoin, renforcez les mots de passe admin.',
                'auth',
            );
        } elseif ($unusualAuth && ! SecurityAlertResolution::isSuppressed('auth.failed_unusual')) {
            $alerts[] = self::alertPayload(
                'auth.failed_unusual',
                'warning',
                'Tentatives de connexion inhabituelles',
                $stats['login_failed_24h'] . ' échec(s) en 24h.',
                'Consultez les logs et surveillez les IPs listées ci-dessous.',
                'auth',
            );
        }

        if ($hasSuspiciousIps && ! SecurityAlertResolution::isSuppressed('network.suspicious_ips')) {
            $blockedCount = $suspiciousIps->where('is_blocked', true)->count();
            $alerts[] = self::alertPayload(
                'network.suspicious_ips',
                'warning',
                $blockedCount > 0 ? 'IPs bloquées activement' : 'IPs à surveiller',
                $blockedCount > 0
                    ? $blockedCount . ' IP(s) suspecte(s) sont bloquées par l\'application.'
                    : $suspiciousIps->count() . ' adresse(s) IP avec plusieurs échecs de connexion.',
                $blockedCount > 0
                    ? 'Les IPs bloquées ne peuvent plus accéder au site. Marquez comme résolue une fois la menace traitée.'
                    : 'Surveillez ces IPs. Blocage automatique après ' . config('security.auto_block_threshold', 5) . ' échecs en 24h.',
                'network',
            );
        }

        if ($hasCompromise && ! SecurityAlertResolution::isSuppressed('compromise.same_ip_login')) {
            $alerts[] = self::alertPayload(
                'compromise.same_ip_login',
                'critical',
                'Connexions réussies après échecs (même IP)',
                $compromiseSignals->count() . ' connexion(s) depuis une IP ayant échoué récemment.',
                'Vérifiez ces comptes, forcez un changement de mot de passe et auditez les actions récentes.',
                'compromise',
            );
        }

        if ($stats['sensitive_actions_24h'] >= 15 && ! SecurityAlertResolution::isSuppressed('audit.sensitive_volume')) {
            SecurityAlertResolution::clearIfConditionEnded('audit.sensitive_volume', true);
            $alerts[] = self::alertPayload(
                'audit.sensitive_volume',
                'warning',
                'Volume élevé d\'actions sensibles',
                $stats['sensitive_actions_24h'] . ' suppressions/exports en 24h.',
                'Confirmez que ces opérations sont légitimes dans Logs Système.',
                'audit',
            );
        } else {
            SecurityAlertResolution::clearIfConditionEnded('audit.sensitive_volume', $stats['sensitive_actions_24h'] >= 15);
        }

        $actionable = collect($alerts)->whereIn('severity', ['critical', 'warning']);

        if ($actionable->isEmpty()) {
            $alerts[] = self::alertPayload(
                'status.ok',
                'info',
                'Aucune menace détectée',
                'Pas d\'anomalie significative sur les dernières 24h.',
                'Continuez la surveillance via ce tableau de bord et les Logs Système.',
                'status',
            );
        }

        return $alerts;
    }

    protected static function alertPayload(
        string $key,
        string $severity,
        string $title,
        string $problem,
        string $action,
        string $component,
    ): array {
        return [
            'key' => $key,
            'severity' => $severity,
            'title' => $title,
            'problem' => $problem,
            'action' => $action,
            'component' => $component,
        ];
    }

    public static function validAlertKeys(): array
    {
        return [
            'auth.failed_massive',
            'auth.failed_unusual',
            'network.suspicious_ips',
            'compromise.same_ip_login',
            'audit.sensitive_volume',
        ];
    }

    protected static function health(array $alerts): array
    {
        $critical = collect($alerts)->where('severity', 'critical')->count();
        $warnings = collect($alerts)->where('severity', 'warning')->count();

        if ($critical > 0) {
            return [
                'status' => 'error',
                'label' => 'Menace potentielle — action requise',
                'summary' => $critical . ' alerte(s) critique(s). Des tentatives d\'intrusion ou des signaux de compromission ont été détectés.',
                'critical' => $critical,
                'warnings' => $warnings,
            ];
        }

        if ($warnings > 0) {
            return [
                'status' => 'warning',
                'label' => 'Surveillance active — activité suspecte',
                'summary' => 'L\'application n\'a pas été compromise confirmée, mais des tentatives ont été enregistrées.',
                'critical' => 0,
                'warnings' => $warnings,
            ];
        }

        return [
            'status' => 'ok',
            'label' => 'Situation normale',
            'summary' => 'Aucune attaque réussie détectée sur la période analysée.',
            'critical' => 0,
            'warnings' => 0,
        ];
    }

    protected static function monitoringTools(): array
    {
        return [
            ['name' => 'Logs Système (PDV Connect)', 'role' => 'Audit applicatif', 'status' => 'actif', 'scope' => 'Connexions, CRUD, exports'],
            ['name' => 'Cloudflare', 'role' => 'WAF / anti-DDoS', 'status' => 'recommandé', 'scope' => 'Trafic HTTP avant l\'app'],
            ['name' => 'Fail2ban', 'role' => 'Blocage IP', 'status' => 'recommandé', 'scope' => 'Serveur / SSH / login'],
            ['name' => 'Sentry', 'role' => 'Erreurs & anomalies', 'status' => 'optionnel', 'scope' => 'Exceptions PHP'],
            ['name' => 'Blocage IP (PDV Connect)', 'role' => 'Liste noire applicative', 'status' => 'actif', 'scope' => 'Auto après ' . config('security.auto_block_threshold', 5) . ' échecs / déblocage manuel'],
            ['name' => 'Rate limiting Laravel', 'role' => 'Anti brute-force', 'status' => 'actif', 'scope' => 'Route /login (10/min)'],
        ];
    }
}
