<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlertResolution extends Model
{
    protected $fillable = [
        'alert_key',
        'resolved_by',
        'note',
        'metadata',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'resolved_by');
    }

    public static function isSuppressed(string $alertKey): bool
    {
        return static::where('alert_key', $alertKey)->exists();
    }

    public static function clearIfConditionEnded(string $alertKey, bool $conditionApplies): void
    {
        if (! $conditionApplies) {
            static::where('alert_key', $alertKey)->delete();
        }
    }

    public static function resolve(string $alertKey, int $userId, ?string $note = null, ?array $metadata = null): self
    {
        return static::updateOrCreate(
            ['alert_key' => $alertKey],
            [
                'resolved_by' => $userId,
                'note' => $note,
                'metadata' => $metadata,
                'resolved_at' => now(),
            ]
        );
    }
}
