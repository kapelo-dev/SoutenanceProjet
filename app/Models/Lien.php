<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lien extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'liens';

    protected $fillable = [
        'libelle',
        'route',
        'url',
        'icone',
        'parent_id',
        'ordre',
        'visible',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'ordre' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    
    // Un lien peut avoir un parent
    public function parent()
    {
        return $this->belongsTo(Lien::class, 'parent_id');
    }

    // Un lien peut avoir plusieurs enfants (sous-menus)
    public function enfants()
    {
        return $this->hasMany(Lien::class, 'parent_id')->orderBy('ordre');
    }

    // Un lien peut être accessible par plusieurs profils
    public function profils()
    {
        return $this->belongsToMany(Profil::class, 'profil_liens', 'lien_id', 'profil_id')
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

    /**
     * Scopes
     */
    
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    public function scopeMenuPrincipal($query)
    {
        return $query->whereNull('parent_id')->orderBy('ordre');
    }

    public function scopeSousMenus($query)
    {
        return $query->whereNotNull('parent_id')->orderBy('ordre');
    }
}
