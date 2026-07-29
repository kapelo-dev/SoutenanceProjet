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
        'parent_id',
        'niveau',
    ];

    protected $casts = [
        'parent_id' => 'integer',
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

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * IDs du profil et de tous ses ancêtres (héritage permissions).
     */
    public function ancestorIdsIncludingSelf(): array
    {
        $ids = [];
        $current = $this;

        while ($current) {
            if (in_array($current->id, $ids, true)) {
                break;
            }
            $ids[] = $current->id;
            $current = $current->parent;
        }

        return $ids;
    }

    public function wouldCreateParentCycle(?int $parentId): bool
    {
        if (! $parentId) {
            return false;
        }

        if ($this->exists && $parentId === $this->id) {
            return true;
        }

        $current = self::find($parentId);

        while ($current) {
            if ($this->exists && $current->id === $this->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Scopes
     */

    public function scopeOrdreAffichage($query)
    {
        return $query->orderBy('libelle');
    }

    public function scopeOrdreParNiveau($query)
    {
        return $query->orderBy('libelle');
    }
}
