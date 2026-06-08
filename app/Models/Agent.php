<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class Agent extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'agents';

    protected $fillable = [
        'uid',
        'code_agent',
        'nom',
        'prenom',
        'telephone',
        'montant_initial_total',
        'espece_initiale',
        'kiosque_id',
        'statut',
        'user_id',
    ];

    protected $casts = [
        'montant_initial_total' => 'decimal:2',
        'espece_initiale' => 'decimal:2',
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
    
    // Un agent appartient à un utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }

    // Un agent appartient à un kiosque
    public function kiosque()
    {
        return $this->belongsTo(Kiosque::class, 'kiosque_id');
    }

    // Un agent peut effectuer plusieurs transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'agent_id');
    }

    // Un agent a plusieurs enregistrements de solde
    public function soldes()
    {
        return $this->hasMany(Solde::class, 'agent_id');
    }

    // Un agent a plusieurs salaires
    public function salaires()
    {
        return $this->hasMany(Salaire::class, 'agent_id');
    }

    // Un agent a un historique d'affectations dans les kiosques
    public function historiqueKiosques()
    {
        return $this->hasMany(AgentKiosqueHistorique::class, 'agent_id')->orderBy('date_debut', 'desc');
    }

    // Affectation actuelle dans un kiosque
    public function affectationActuelle()
    {
        return $this->hasOne(AgentKiosqueHistorique::class, 'agent_id')->enCours()->latest('date_debut');
    }

    /**
     * Scopes
     */
    
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeSansKiosque($query)
    {
        return $query->whereNull('kiosque_id');
    }

    public function scopeAvecKiosque($query)
    {
        return $query->whereNotNull('kiosque_id');
    }

    /**
     * Accesseurs
     */
    
    public function getNomCompletAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Méthodes utiles
     */
    
    // Obtenir le solde actuel par type et opérateur
    public function soldeActuel($type = null, $operateur_id = null)
    {
        $query = $this->soldes()->latest('date');

        if ($type) {
            $query->where('type', $type);
        }

        if ($operateur_id) {
            $query->where('operateur_id', $operateur_id);
        }

        return $query->first();
    }

    // Obtenir tous les soldes actuels (1 espèce + 1 virtuel par opérateur)
    public function soldesActuels(array $with = [])
    {
        $query = $this->soldes()
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('soldes')
                    ->where('agent_id', $this->id)
                    ->groupBy('operateur_id', 'type');
            });

        if ($with !== []) {
            $query->with($with);
        }

        return $query->get();
    }

    // Calculer le solde total
    public function soldeTotal()
    {
        return $this->soldesActuels()->sum('montant');
    }
}
