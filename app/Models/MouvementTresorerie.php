<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementTresorerie extends Model
{
    use HasFactory;

    protected $table = 'mouvements_tresorerie';

    protected $fillable = [
        'type',
        'categorie',
        'montant',
        'date_mouvement',
        'reference',
        'agent_id',
        'salaire_id',
        'transaction_id',
        'description',
        'mode_paiement',
        'utilisateur_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_mouvement' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function salaire()
    {
        return $this->belongsTo(Salaire::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
