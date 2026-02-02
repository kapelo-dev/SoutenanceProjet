<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token API pour l'application Android (ingestion SMS)
    |--------------------------------------------------------------------------
    | Définir une clé secrète dans .env : SMS_API_TOKEN=your-secret-token
    */
    'token' => env('SMS_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Agent et opérateur par défaut pour les transactions issues des SMS
    |--------------------------------------------------------------------------
    | IDs à utiliser si l'app Android n'envoie pas agent_id / operateur_id.
    */
    'default_agent_id' => env('SMS_DEFAULT_AGENT_ID', 1),
    'default_operateur_id' => env('SMS_DEFAULT_OPERATEUR_ID', 1),

];
