<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'uid',
        'reference',
        'date',
        'montant',
        'type',
        'type_operation_id',
        'operateur_id',
        'agent_id',
        'statut',
        'description',
        'commission',
        'virtual_balance_after',
        'operator_txn_id',
        'client_nom',
        'client_telephone',
    ];

    protected $casts = [
        'date' => 'datetime',
        'montant' => 'decimal:2',
        'commission' => 'decimal:2',
        'virtual_balance_after' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Générer automatiquement un UUID et une référence lors de la création
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
            if (empty($model->reference)) {
                $model->reference = 'TXN-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Relations
     */
    
    // Une transaction appartient à un agent
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    // Une transaction appartient à un type d'opération (opérations en agence)
    public function typeOperation()
    {
        return $this->belongsTo(TypeOperation::class, 'type_operation_id');
    }

    // Une transaction appartient à un opérateur
    public function operateur()
    {
        return $this->belongsTo(Operateur::class, 'operateur_id');
    }

    // Une transaction peut avoir plusieurs audits
    public function audits()
    {
        return $this->hasMany(Audit::class, 'transaction_id');
    }

    /**
     * Scopes
     */
    
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeDepot($query)
    {
        return $query->where('type', 'depot');
    }

    public function scopeRetrait($query)
    {
        return $query->where('type', 'retrait');
    }

    public function scopePeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date', [$dateDebut, $dateFin]);
    }

    public function scopeDuJour($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeDuMois($query)
    {
        return $query->whereYear('date', now()->year)
                     ->whereMonth('date', now()->month);
    }
}
