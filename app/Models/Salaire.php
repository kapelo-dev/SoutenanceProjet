<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'parametre_salaire_id',
        'periode',
        'date_debut',
        'date_fin',
        'montant_fixe',
        'montant_commission',
        'montant_bonus',
        'montant_deduction',
        'montant_total',
        'details_calcul',
        'statut',
        'date_paiement',
        'notes',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_paiement' => 'date',
        'montant_fixe' => 'decimal:2',
        'montant_commission' => 'decimal:2',
        'montant_bonus' => 'decimal:2',
        'montant_deduction' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'details_calcul' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function parametreSalaire()
    {
        return $this->belongsTo(ParametreSalaire::class);
    }

    public function mouvementsTresorerie()
    {
        return $this->hasMany(MouvementTresorerie::class);
    }
}
