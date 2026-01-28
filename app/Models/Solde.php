<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Solde extends Model
{
    use HasFactory;

    protected $table = 'soldes';

    protected $fillable = [
        'uid',
        'agent_id',
        'operateur_id',
        'montant',
        'type',
        'date',
        'description',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Générer automatiquement un UUID lors de la création
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relations
     */
    
    // Un solde appartient à un agent
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    // Un solde peut appartenir à un opérateur (NULL si espèce)
    public function operateur()
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }

    /**
     * Scopes
     */
    
    public function scopeEspece($query)
    {
        return $query->where('type', 'espece');
    }

    public function scopeVirtuel($query)
    {
        return $query->where('type', 'virtuel');
    }

    public function scopeParOperateur($query, $operateurId)
    {
        return $query->where('operateur_id', $operateurId);
    }

    public function scopeDernier($query)
    {
        return $query->latest('date');
    }
}
