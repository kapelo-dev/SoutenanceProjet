<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    protected $fillable = [
        'filename',
        'disk',
        'path',
        'database_name',
        'size_bytes',
        'checksum',
        'status',
        'error_message',
        'duration_ms',
        'trigger',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'duration_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
