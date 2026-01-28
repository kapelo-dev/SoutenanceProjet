<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Audit extends Model
{
    use HasFactory;

    protected $table = 'audits';

    protected $fillable = [
        'uid',
        'transaction_id',
        'ancien_montant',
        'nouveau_montant',
        'operateur_id',
        'user_id',
        'date_modification',
        'raison',
        'type_modification',
    ];

    protected $casts = [
        'ancien_montant' => 'decimal:2',
        'nouveau_montant' => 'decimal:2',
        'date_modification' => 'datetime',
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
    
    // Un audit peut être lié à une transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    // Un audit peut être lié à un opérateur
    public function operateur()
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }

    // Un audit appartient à un utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }

    /**
     * Scopes
     */
    
    public function scopeCorrection($query)
    {
        return $query->where('type_modification', 'correction');
    }

    public function scopeAnnulation($query)
    {
        return $query->where('type_modification', 'annulation');
    }

    public function scopeAjustement($query)
    {
        return $query->where('type_modification', 'ajustement');
    }

    public function scopeRecent($query)
    {
        return $query->latest('date_modification');
    }

    /**
     * Accesseurs
     */
    
    public function getDifferenceAttribute()
    {
        if ($this->ancien_montant !== null && $this->nouveau_montant !== null) {
            return $this->nouveau_montant - $this->ancien_montant;
        }
        return null;
    }
}
