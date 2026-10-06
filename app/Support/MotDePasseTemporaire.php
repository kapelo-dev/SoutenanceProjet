<?php

namespace App\Support;

/**
 * Mot de passe temporaire remis à un agent à la création de son compte.
 * Il doit être changé à la première connexion (web ou mobile).
 */
class MotDePasseTemporaire
{
    // Sans caractères ambigus à la lecture ou à la dictée (0/O, 1/l/I)
    private const LETTRES = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

    private const CHIFFRES = '23456789';

    public static function generer(int $longueur = 10): string
    {
        $alphabet = self::LETTRES . self::CHIFFRES;

        // Au moins une lettre et un chiffre, le reste au hasard, puis mélange (random_int : aléa cryptographique)
        $caracteres = [
            self::LETTRES[random_int(0, strlen(self::LETTRES) - 1)],
            self::CHIFFRES[random_int(0, strlen(self::CHIFFRES) - 1)],
        ];
        while (count($caracteres) < $longueur) {
            $caracteres[] = $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        for ($i = count($caracteres) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        return implode('', $caracteres);
    }
}
