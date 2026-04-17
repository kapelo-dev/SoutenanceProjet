<?php

namespace App\Traits;

use App\Models\SystemLog;

trait LogsActivity
{
    /**
     * Boot du trait
     */
    protected static function bootLogsActivity()
    {
        // Log lors de la création
        static::created(function ($model) {
            if (auth()->check() && $model->shouldLogCreation()) {
                SystemLog::logCreate($model);
            }
        });

        // Log lors de la modification
        static::updated(function ($model) {
            if (auth()->check() && $model->shouldLogUpdate()) {
                $oldValues = $model->getOriginal();
                $changes = $model->getChanges();
                
                // Ne logger que si des changements significatifs ont eu lieu
                if (!empty($changes) && count($changes) > 1) { // > 1 car updated_at change toujours
                    // Filtrer les champs sensibles dans les anciennes et nouvelles valeurs
                    $filteredOldValues = $model->filterSensitiveData($oldValues);
                    $filteredNewValues = $model->filterSensitiveData($model->getAttributes());
                    SystemLog::logUpdate($model, $filteredOldValues, null, $filteredNewValues);
                }
            }
        });

        // Log lors de la suppression
        static::deleted(function ($model) {
            if (auth()->check() && $model->shouldLogDeletion()) {
                SystemLog::logDelete($model);
            }
        });
    }

    /**
     * Déterminer si la création doit être loggée
     */
    protected function shouldLogCreation()
    {
        return property_exists($this, 'logCreation') ? $this->logCreation : true;
    }

    /**
     * Déterminer si la modification doit être loggée
     */
    protected function shouldLogUpdate()
    {
        return property_exists($this, 'logUpdate') ? $this->logUpdate : true;
    }

    /**
     * Déterminer si la suppression doit être loggée
     */
    protected function shouldLogDeletion()
    {
        return property_exists($this, 'logDeletion') ? $this->logDeletion : true;
    }

    /**
     * Logger une action personnalisée
     */
    public function logCustomAction($action, $description, $metadata = [])
    {
        return SystemLog::logAction($action, $description, $this, null, null, $metadata);
    }

    /**
     * Filtrer les données sensibles avant de les logger
     */
    protected function filterSensitiveData($data)
    {
        // Liste des champs sensibles à masquer
        $sensitiveFields = property_exists($this, 'hiddenFromLogs') 
            ? $this->hiddenFromLogs 
            : ['password', 'mot_de_passe', 'remember_token', 'api_token'];
        
        if (!is_array($data)) {
            return $data;
        }
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '********';
            }
        }
        
        return $data;
    }
}
