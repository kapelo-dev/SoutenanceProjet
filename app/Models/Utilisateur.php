<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class Utilisateur extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

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

    /**
     * Champs sensibles à masquer dans les logs système
     */
    protected $hiddenFromLogs = [
        'mot_de_passe',
        'password',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'dernier_connexion' => 'datetime',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Récupérer le mot de passe pour l'authentification
     * Laravel utilise "password" par défaut, mais nous utilisons "mot_de_passe"
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    /**
     * Accessor pour le champ password (compatibilité Laravel)
     */
    public function getPasswordAttribute()
    {
        return $this->mot_de_passe;
    }

    /**
     * Mutator pour le champ password (compatibilité Laravel)
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['mot_de_passe'] = $value;
    }

    /**
     * Générer automatiquement un UUID lors de la création
     */
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

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    /**
     * Méthodes utiles
     */
    
    // Vérifier si l'utilisateur est un agent
    public function isAgent()
    {
        return $this->agent()->exists();
    }

    // Vérifier si l'utilisateur a un profil spécifique
    public function hasProfil($profilLibelle)
    {
        return $this->profils()->where('libelle', $profilLibelle)->exists();
    }
}
