<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';

    protected $fillable = [
        'uid',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot du modèle
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
    
    // Un log appartient à un utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }

    // Relation polymorphique vers l'entité concernée
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Méthodes statiques pour créer des logs
     */
    
    public static function logAction($action, $description, $model = null, $oldValues = null, $newValues = null, $metadata = [])
    {
        if (! config('security.audit_logging_enabled', true)) {
            return null;
        }

        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->clientIp(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    public static function logLogin($user, $success = true)
    {
        if (! config('security.audit_logging_enabled', true)) {
            return null;
        }

        return self::create([
            'user_id' => $success ? $user->id : null,
            'action' => $success ? 'login' : 'login_failed',
            'description' => $success 
                ? "Connexion réussie de {$user->nom} {$user->prenom}"
                : "Tentative de connexion échouée pour {$user->email}",
            'ip_address' => request()->clientIp(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'email' => $user->email ?? null,
            ],
        ]);
    }

    public static function logLogout($user)
    {
        if (! config('security.audit_logging_enabled', true)) {
            return null;
        }

        return self::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => "Déconnexion de {$user->nom} {$user->prenom}",
            'ip_address' => request()->clientIp(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logCreate($model, $description = null)
    {
        $modelName = class_basename($model);
        
        return self::logAction(
            'create',
            $description ?? "Création de {$modelName} #{$model->id}",
            $model,
            null,
            $model->getAttributes()
        );
    }

    public static function logUpdate($model, $oldValues, $description = null, $newValues = null)
    {
        $modelName = class_basename($model);
        
        return self::logAction(
            'update',
            $description ?? "Modification de {$modelName} #{$model->id}",
            $model,
            $oldValues,
            $newValues ?? $model->getAttributes()
        );
    }

    public static function logDelete($model, $description = null)
    {
        $modelName = class_basename($model);
        
        return self::logAction(
            'delete',
            $description ?? "Suppression de {$modelName} #{$model->id}",
            $model,
            $model->getAttributes(),
            null
        );
    }

    /**
     * Accesseurs
     */
    
    public function getActionLabelAttribute()
    {
        $labels = [
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'login_failed' => 'Connexion échouée',
            'assign' => 'Affectation',
            'unassign' => 'Retrait',
            'validate' => 'Validation',
            'cancel' => 'Annulation',
            'export' => 'Export',
            'import' => 'Import',
            'other' => 'Autre',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    public function getActionColorAttribute()
    {
        $colors = [
            'create' => 'success',
            'update' => 'primary',
            'delete' => 'danger',
            'login' => 'info',
            'logout' => 'secondary',
            'login_failed' => 'warning',
            'assign' => 'success',
            'unassign' => 'warning',
            'validate' => 'success',
            'cancel' => 'danger',
            'export' => 'info',
            'import' => 'info',
            'other' => 'secondary',
        ];

        return $colors[$this->action] ?? 'secondary';
    }

    public function getModelNameAttribute()
    {
        if (!$this->model_type) {
            return null;
        }

        $modelNames = [
            'App\Models\Agent' => 'Agent',
            'App\Models\Kiosque' => 'Kiosque',
            'App\Models\Transaction' => 'Transaction',
            'App\Models\Utilisateur' => 'Utilisateur',
            'App\Models\Operateur' => 'Opérateur',
            'App\Models\AgentKiosqueHistorique' => 'Affectation',
        ];

        return $modelNames[$this->model_type] ?? class_basename($this->model_type);
    }
}
