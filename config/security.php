<?php

return [
    /*
    | Nombre d'échecs de connexion (24h) avant blocage automatique de l'IP.
    | Mettre 0 pour désactiver le blocage automatique.
    */
    'auto_block_threshold' => (int) env('SECURITY_AUTO_BLOCK_THRESHOLD', 5),

    /*
    | Durée du blocage auto en heures. Null = jusqu'au déblocage manuel.
    */
    'auto_block_hours' => env('SECURITY_AUTO_BLOCK_HOURS') !== null
        ? (int) env('SECURITY_AUTO_BLOCK_HOURS')
        : null,

    /*
    | Journalisation audit (connexions, CRUD, exports) dans system_logs.
    */
    'audit_logging_enabled' => filter_var(env('SECURITY_AUDIT_LOGGING_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    | Middleware blocage IP + liste noire applicative.
    */
    'ip_blocking_enabled' => filter_var(env('SECURITY_IP_BLOCKING_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    | Rate limiting sur POST /login (format Laravel : tentatives,minutes).
    */
    'login_rate_limit_enabled' => filter_var(env('SECURITY_LOGIN_RATE_LIMIT_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'login_rate_limit' => env('SECURITY_LOGIN_RATE_LIMIT', '10,1'),
];
