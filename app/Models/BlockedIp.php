<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedIp extends Model
{
    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip_address',
        'reason',
        'source',
        'failed_attempts',
        'blocked_by',
        'unblocked_by',
        'unblocked_at',
        'expires_at',
    ];

    protected $casts = [
        'failed_attempts' => 'integer',
        'unblocked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function bloquePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'blocked_by');
    }

    public function debloquePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'unblocked_by');
    }

    public function scopeActive($query)
    {
        return $query
            ->whereNull('unblocked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        if ($this->unblocked_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
