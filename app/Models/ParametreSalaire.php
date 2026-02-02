<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametreSalaire extends Model
{
    use HasFactory;

    protected $table = 'parametres_salaire';

    protected $fillable = [
        'nom',
        'type',
        'montant_fixe',
        'taux_commission',
        'base_calcul',
        'formule',
        'conditions',
        'actif',
    ];

    protected $casts = [
        'montant_fixe' => 'decimal:2',
        'taux_commission' => 'decimal:2',
        'conditions' => 'array',
        'actif' => 'boolean',
    ];

    public function salaires()
    {
        return $this->hasMany(Salaire::class);
    }
}
