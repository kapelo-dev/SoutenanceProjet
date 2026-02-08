<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Kiosque extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kiosques';

    protected $fillable = [
        'uid',
        'code',
        'nom',
        'adresse',
        'quartier',
        'ville',
        'latitude',
        'longitude',
        'telephone',
        'photo',
        'type',
        'statut',
        'capacite_agents',
        'horaire_ouverture',
        'horaire_fermeture',
        'description',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'capacite_agents' => 'integer',
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
    
    // Un kiosque peut avoir plusieurs agents
    public function agents()
    {
        return $this->hasMany(Agent::class, 'kiosque_id');
    }

    // Agents actifs du kiosque
    public function agentsActifs()
    {
        return $this->hasMany(Agent::class, 'kiosque_id')->where('statut', 'actif');
    }

    /**
     * Scopes
     */
    
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeAvecCoordonnees($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeParVille($query, $ville)
    {
        return $query->where('ville', $ville);
    }

    public function scopeParQuartier($query, $quartier)
    {
        return $query->where('quartier', $quartier);
    }

    /**
     * Méthodes utiles
     */
    
    // Calculer la distance avec un autre kiosque ou des coordonnées
    public function distanceVers($latitude, $longitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        // Formule de Haversine
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    // Vérifier si le kiosque est saturé (tous les agents assignés comptent, pas seulement actifs)
    public function estSature()
    {
        return $this->agents()->count() >= $this->capacite_agents;
    }

    // Nombre de places disponibles (tous les agents assignés occupent une place)
    public function placesDisponibles()
    {
        return max(0, $this->capacite_agents - $this->agents()->count());
    }

    /**
     * Accesseurs
     */
    
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset($this->photo) : null;
    }

    public function getCoordonneesGpsAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }
        return null;
    }
}
