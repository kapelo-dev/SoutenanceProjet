<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Utilisateur extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'uid',
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'telephone',
        'photo_profil',
        'statut',
        'dernier_connexion',
        'email_verified_at',
        'remember_token',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected $casts = [
        'dernier_connexion' => 'datetime',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
    
    // Un utilisateur peut avoir plusieurs profils
    public function profils()
    {
        return $this->belongsToMany(Profil::class, 'user_profils', 'user_id', 'profil_id')
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

    // Un utilisateur peut être lié à un agent
    public function agent()
    {
        return $this->hasOne(Agent::class, 'user_id');
    }

    // Un utilisateur peut effectuer plusieurs audits
    public function audits()
    {
        return $this->hasMany(Audit::class, 'user_id');
    }

    /**
     * Scopes
     */
    
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeSuspendu($query)
    {
        return $query->where('statut', 'suspendu');
    }
}
