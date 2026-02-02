<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigAppMobile extends Model
{
    protected $table = 'config_app_mobile';

    protected $fillable = [
        'api_base_url',
        'api_token',
        'filtres_sms',
        'code_config',
        'actif',
    ];

    protected $casts = [
        'filtres_sms' => 'array',
        'actif' => 'boolean',
    ];

    /**
     * Récupère la configuration active (utilisée par l'API et le middleware).
     */
    public static function getActive(): ?self
    {
        return static::where('actif', true)->first();
    }
}
