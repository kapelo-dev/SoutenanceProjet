<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOperation extends Model
{
    use HasFactory;

    protected $table = 'type_operations';

    protected $fillable = [
        'code',
        'libelle',
        'ordre',
        'actif',
        'requiert_operateur',
    ];

    protected $casts = [
        'ordre' => 'integer',
        'actif' => 'boolean',
        'requiert_operateur' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }
}
