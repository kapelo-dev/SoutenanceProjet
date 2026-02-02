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

    /**
     * Profils (rôles) auxquels ce paramètre est destiné. Vide = tous les profils.
     */
    public function profils()
    {
        return $this->belongsToMany(Profil::class, 'parametre_salaire_profil', 'parametre_salaire_id', 'profil_id')
            ->withTimestamps();
    }
}
