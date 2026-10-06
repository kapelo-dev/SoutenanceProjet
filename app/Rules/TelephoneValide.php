<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Numéro de téléphone : 8 à 15 chiffres (8 = numéro local togolais, 15 = maximum international E.164),
 * avec éventuellement un « + » initial et des espaces, points ou tirets comme séparateurs.
 *
 * Minimum 8 chiffres : un agent est reconnu par les 8 derniers chiffres de son numéro
 * (SMS, application mobile — voir AgentPhoneResolver).
 */
class TelephoneValide implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valeur = trim((string) $value);
        $chiffres = preg_replace('/\D/', '', $valeur);

        if (! preg_match('/^\+?[0-9][0-9 .\-]*$/', $valeur) || strlen($chiffres) < 8 || strlen($chiffres) > 15) {
            $fail('Le numéro de téléphone doit contenir entre 8 et 15 chiffres (ex. 90123456 ou +228 90 12 34 56).');
        }
    }
}
