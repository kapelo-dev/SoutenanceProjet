<?php

namespace App\Services;

use App\Models\BlockedIp;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IpBlockService
{
    private const CACHE_KEY = 'blocked-ips:actifs';

    // Filet de sécurité si la table est modifiée hors de ce service (block/unblock vident le cache)
    private const CACHE_TTL_SECONDES = 600;

    public function isBlocked(?string $ip): bool
    {
        return $this->activeBlockReason($ip) !== null;
    }

    /**
     * Raison du blocage actif de cette IP, null si elle n'est pas bloquée.
     * Lu depuis le cache : le middleware est exécuté sur chaque requête web.
     */
    public function activeBlockReason(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        $block = $this->activeBlocks()[$ip] ?? null;

        if (! $block) {
            return null;
        }

        // L'expiration est vérifiée ici : une entrée en cache peut avoir expiré depuis
        if ($block['expires_at'] !== null && $block['expires_at'] <= now()->getTimestamp()) {
            return null;
        }

        return $block['reason'];
    }

    /**
     * @return array<string, array{reason: string, expires_at: int|null}>
     */
    private function activeBlocks(): array
    {
        return Cache::memo()->remember(self::CACHE_KEY, self::CACHE_TTL_SECONDES, fn () => BlockedIp::active()
            ->get(['ip_address', 'reason', 'expires_at'])
            ->mapWithKeys(fn (BlockedIp $b) => [$b->ip_address => [
                'reason' => $b->reason,
                'expires_at' => $b->expires_at?->getTimestamp(),
            ]])
            ->all());
    }

    private function flushCache(): void
    {
        Cache::memo()->forget(self::CACHE_KEY);
    }

    public function recordLoginFailure(Request $request, ?int $userId = null): void
    {
        if (! config('security.ip_blocking_enabled', true)) {
            return;
        }

        $ip = $request->clientIp();
        if (! $ip || $this->isBlocked($ip)) {
            return;
        }

        $since = now()->subDay();
        $failures = SystemLog::where('action', 'login_failed')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', $since)
            ->count();

        $threshold = config('security.auto_block_threshold', 5);

        if ($failures >= $threshold) {
            $this->block(
                $ip,
                "Blocage automatique après {$failures} échec(s) de connexion en 24h",
                'auto',
                null,
                $failures,
                $this->autoBlockExpiresAt()
            );

            SystemLog::create([
                'user_id' => $userId,
                'action' => 'other',
                'description' => "IP {$ip} bloquée automatiquement ({$failures} échecs / 24h)",
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'metadata' => ['event' => 'ip_blocked', 'source' => 'auto', 'failures' => $failures],
            ]);
        }
    }

    public function block(
        string $ip,
        string $reason,
        string $source = 'manual',
        ?int $blockedBy = null,
        int $failedAttempts = 0,
        ?\DateTimeInterface $expiresAt = null
    ): BlockedIp {
        $blocked = BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'source' => $source,
                'failed_attempts' => $failedAttempts,
                'blocked_by' => $blockedBy,
                'unblocked_by' => null,
                'unblocked_at' => null,
                'expires_at' => $expiresAt,
            ]
        );
        $this->flushCache();

        if ($source === 'manual') {
            SystemLog::create([
                'user_id' => $blockedBy,
                'action' => 'other',
                'description' => "IP {$ip} bloquée manuellement : {$reason}",
                'ip_address' => $ip,
                'metadata' => ['event' => 'ip_blocked', 'source' => 'manual'],
            ]);
        }

        return $blocked;
    }

    public function unblock(string $ip, ?int $unblockedBy = null): bool
    {
        $row = BlockedIp::where('ip_address', $ip)->active()->first();

        if (! $row) {
            return false;
        }

        $row->update([
            'unblocked_at' => now(),
            'unblocked_by' => $unblockedBy,
        ]);
        $this->flushCache();

        SystemLog::create([
            'user_id' => $unblockedBy,
            'action' => 'other',
            'description' => "IP {$ip} débloquée",
            'ip_address' => $ip,
            'metadata' => ['event' => 'ip_unblocked'],
        ]);

        return true;
    }

    public function listActive(): \Illuminate\Support\Collection
    {
        return BlockedIp::with(['bloquePar', 'debloquePar'])
            ->active()
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (BlockedIp $b) => [
                'id' => $b->id,
                'ip' => $b->ip_address,
                'reason' => $b->reason,
                'source' => $b->source,
                'source_label' => $b->source === 'auto' ? 'Automatique' : 'Manuel',
                'failed_attempts' => $b->failed_attempts,
                'blocked_at' => $b->updated_at->toIso8601String(),
                'expires_at' => $b->expires_at?->toIso8601String(),
                'blocked_by' => $b->bloquePar?->nom_complet,
            ]);
    }

    protected function autoBlockExpiresAt(): ?\DateTimeInterface
    {
        $hours = config('security.auto_block_hours');

        return $hours ? now()->addHours((int) $hours) : null;
    }
}
