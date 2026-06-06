<?php

/**
 * Attendre MySQL et afficher l'erreur PDO réelle (logs Render).
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: '';
$username = getenv('DB_USERNAME') ?: '';
$password = getenv('DB_PASSWORD') ?: '';
$maxAttempts = (int) (getenv('DB_WAIT_ATTEMPTS') ?: 30);

if ($database === '' || $username === '') {
    fwrite(STDERR, "DB_DATABASE ou DB_USERNAME manquant.\n");
    exit(1);
}

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    try {
        new PDO($dsn, $username, $password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "MySQL prêt ({$host}/{$database}).\n";
        exit(0);
    } catch (PDOException $e) {
        fwrite(STDERR, "[{$attempt}/{$maxAttempts}] MySQL: {$e->getMessage()}\n");
        if ($attempt < $maxAttempts) {
            sleep(2);
        }
    }
}

fwrite(STDERR, "ERREUR: MySQL inaccessible après {$maxAttempts} tentatives.\n");
fwrite(STDERR, "Vérifiez AlwaysData: accès distant activé, base '{$database}' existante, DB_PASSWORD sur Render.\n");
exit(1);
