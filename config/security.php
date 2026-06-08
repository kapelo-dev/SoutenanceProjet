<?php

return [
    /*
    | Nombre d'échecs de connexion (24h) avant blocage automatique de l'IP.
    */
    'auto_block_threshold' => (int) env('SECURITY_AUTO_BLOCK_THRESHOLD', 5),

    /*
    | Durée du blocage auto en heures. Null = jusqu'au déblocage manuel.
    */
    'auto_block_hours' => env('SECURITY_AUTO_BLOCK_HOURS') !== null
        ? (int) env('SECURITY_AUTO_BLOCK_HOURS')
        : null,
];
