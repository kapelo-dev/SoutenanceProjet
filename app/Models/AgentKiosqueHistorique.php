<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentKiosqueHistorique extends Model
{
    protected $table = 'agent_kiosque_historique';

    protected $fillable = [
        'agent_id',
        'kiosque_id',
        'date_debut',
        'date_fin',
        'type_mouvement',
        'commentaire',
        'created_by',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function kiosque()
    {
        return $this->belongsTo(Kiosque::class, 'kiosque_id');
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'created_by');
    }

    /**
     * Scopes
     */
    
    public function scopeEnCours($query)
    {
        return $query->whereNull('date_fin');
    }

    public function scopeTermine($query)
    {
        return $query->whereNotNull('date_fin');
    }

    /**
     * Méthodes utiles
     */
    
    public function duree()
    {
        $debut = $this->date_debut;
        $fin = $this->date_fin ?? now();
        
        return $debut->diffInDays($fin);
    }

    public function estEnCours()
    {
        return is_null($this->date_fin);
    }
}
