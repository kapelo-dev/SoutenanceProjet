<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Support\PermissionCache;
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

    /**
     * Mot de passe temporaire (création du compte ou réinitialisation par un administrateur) :
     * il doit être changé avant d'utiliser l'application, sur le web comme sur le mobile.
     */
    public function doitChangerMotDePasse(): bool
    {
        return $this->dernier_connexion === null;
    }

    public function canAccessRoute(string $routeName): bool
    {
        $acces = $this->accesPermissions();

        if (in_array($routeName, $acces['routes'], true)) {
            return true;
        }

        if ($routeName === 'gestion-entreprise.index') {
            return $this->hasPermissionOnGestionEntrepriseUrl($acces['urls']);
        }

        return false;
    }

    public function canAccessGestionEntrepriseOnglet(?string $onglet = 'salaires'): bool
    {
        $acces = $this->accesPermissions();

        if (in_array('gestion-entreprise.index', $acces['routes'], true)) {
            return true;
        }

        return $this->hasPermissionOnGestionEntrepriseUrl($acces['urls'], $onglet ?: 'salaires');
    }

    /**
     * Équivalent des anciens LIKE '/gestion-entreprise%' et '%onglet=xxx%' (insensibles à la casse en MySQL).
     */
    private function hasPermissionOnGestionEntrepriseUrl(array $urls, ?string $onglet = null): bool
    {
        foreach ($urls as $url) {
            $url = mb_strtolower($url);

            if (str_starts_with($url, '/gestion-entreprise')
                && ($onglet === null || str_contains($url, 'onglet=' . mb_strtolower($onglet)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Profils assignés + ancêtres pour l'héritage des permissions.
     */
    public function effectiveProfilIds(): array
    {
        return $this->accesPermissions()['profils'];
    }

    /**
     * Profils effectifs et liens autorisés (routes + URLs) de l'utilisateur, en cache.
     * Invalidé par PermissionCache::flush() à chaque modification des rôles, permissions, liens ou profils.
     *
     * @return array{profils: int[], routes: string[], urls: string[]}
     */
    private function accesPermissions(): array
    {
        return PermissionCache::remember("acces:{$this->id}", function () {
            $profilIds = $this->calculerProfilIdsEffectifs();

            if ($profilIds === []) {
                return ['profils' => [], 'routes' => [], 'urls' => []];
            }

            $liens = DB::table('profil_liens')
                ->join('liens', 'profil_liens.lien_id', '=', 'liens.id')
                ->whereIn('profil_liens.profil_id', $profilIds)
                ->whereNull('profil_liens.deleted_at')
                ->whereNull('liens.deleted_at')
                ->where('liens.visible', true)
                ->select('liens.route', 'liens.url')
                ->distinct()
                ->get();

            return [
                'profils' => $profilIds,
                'routes' => $liens->pluck('route')->filter()->unique()->values()->all(),
                'urls' => $liens->pluck('url')->filter()->unique()->values()->all(),
            ];
        });
    }

    private function calculerProfilIdsEffectifs(): array
    {
        $directs = $this->profils()
            ->whereNull('user_profils.deleted_at')
            ->pluck('profils.id')
            ->all();

        if ($directs === []) {
            return [];
        }

        // Hiérarchie complète chargée en une requête, puis remontée des parents en PHP
        $parents = Profil::query()->pluck('parent_id', 'id')->all();

        $ids = [];
        foreach ($directs as $id) {
            while ($id && ! in_array($id, $ids, true)) {
                $ids[] = $id;
                $parent = $parents[$id] ?? null;
                // Un parent supprimé (soft delete) n'est pas dans $parents : l'héritage s'arrête là
                $id = $parent !== null && array_key_exists($parent, $parents) ? $parent : null;
            }
        }

        return $ids;
    }
}
