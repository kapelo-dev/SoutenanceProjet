<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operateur extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operateurs';

    protected $fillable = [
        'code',
        'libelle',
        'logo',
        'couleur',
        'statut',
        'ordre',
    ];

    protected $casts = [
        'ordre' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    
    // Un opérateur peut avoir plusieurs transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'operateur_id');
    }

    // Un opérateur peut avoir plusieurs soldes
    public function soldes()
    {
        return $this->hasMany(Solde::class, 'operateur_id');
    }

    // Un opérateur peut avoir plusieurs audits
    public function audits()
    {
        return $this->hasMany(Audit::class, 'operateur_id');
    }

    /**
     * Scopes
     */
    
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif')->orderBy('ordre');
    }

    /**
     * Accesseurs
     */
    
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset($this->logo) : null;
    }
}
