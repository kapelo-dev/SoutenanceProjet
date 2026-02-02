<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profil extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profils';

    protected $fillable = [
        'libelle',
        'description',
        'niveau',
    ];

    protected $casts = [
        'niveau' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    
    // Un profil peut être assigné à plusieurs utilisateurs
    public function utilisateurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'user_profils', 'profil_id', 'user_id')
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

    // Un profil peut avoir accès à plusieurs liens
    public function liens()
    {
        return $this->belongsToMany(Lien::class, 'profil_liens', 'profil_id', 'lien_id')
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

    // Paramètres de salaire destinés à ce profil
    public function parametresSalaire()
    {
        return $this->belongsToMany(ParametreSalaire::class, 'parametre_salaire_profil', 'profil_id', 'parametre_salaire_id')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    
    public function scopeOrdreParNiveau($query)
    {
        return $query->orderBy('niveau', 'asc');
    }
}
