<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Évaluation sûre (sans eval) des formules de salaire personnalisées.
 *
 * Grammaire : nombres, variables autorisées, + - * /, parenthèses, signe unaire.
 *   expression := terme (('+' | '-') terme)*
 *   terme      := facteur (('*' | '/') facteur)*
 *   facteur    := ('+' | '-') facteur | nombre | variable | '(' expression ')'
 */
class FormuleSalaire
{
    public const VARIABLES = [
        'montant_transactions',
        'nb_transactions',
        'commissions',
        'montant_fixe',
        'taux_commission',
        'solde_final',
        'objectif_atteint',
    ];

    private array $tokens = [];

    private int $pos = 0;

    private array $variables = [];

    /**
     * @throws InvalidArgumentException si la formule est invalide
     */
    public static function evaluer(string $formule, array $variables): float
    {
        return (new self)->run($formule, $variables);
    }

    /**
     * Message d'erreur si la formule est invalide, null sinon (test avec des valeurs fictives).
     */
    public static function erreur(string $formule): ?string
    {
        try {
            self::evaluer($formule, array_fill_keys(self::VARIABLES, 1));

            return null;
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * La formule fait-elle référence à cette variable ? (évite de calculer une valeur coûteuse inutilement)
     */
    public static function utilise(string $formule, string $variable): bool
    {
        return preg_match('/(?<![a-z_])' . preg_quote($variable, '/') . '(?![a-z_])/i', $formule) === 1;
    }

    private function run(string $formule, array $variables): float
    {
        $this->variables = $variables;
        $this->tokens = $this->tokenize(str_replace(['×', '−', ','], ['*', '-', '.'], $formule));
        $this->pos = 0;

        if ($this->tokens === []) {
            throw new InvalidArgumentException('La formule est vide.');
        }

        $result = $this->expression();

        if ($this->pos < count($this->tokens)) {
            throw new InvalidArgumentException("Élément inattendu « {$this->tokens[$this->pos]} ».");
        }

        return $result;
    }

    private function tokenize(string $formule): array
    {
        preg_match_all('/\s*(\d+(?:\.\d+)?|[a-z_]+|[-+*\/()]|\S)/i', $formule, $matches);

        return $matches[1];
    }

    private function expression(): float
    {
        $value = $this->terme();

        while (in_array($this->peek(), ['+', '-'], true)) {
            $value = $this->next() === '+' ? $value + $this->terme() : $value - $this->terme();
        }

        return $value;
    }

    private function terme(): float
    {
        $value = $this->facteur();

        while (in_array($this->peek(), ['*', '/'], true)) {
            if ($this->next() === '*') {
                $value *= $this->facteur();
            } else {
                $diviseur = $this->facteur();
                // Division par zéro (ex. nb_transactions = 0) : résultat 0 plutôt qu'une erreur
                $value = $diviseur == 0.0 ? 0.0 : $value / $diviseur;
            }
        }

        return $value;
    }

    private function facteur(): float
    {
        $token = $this->next();

        if ($token === null) {
            throw new InvalidArgumentException('La formule est incomplète.');
        }
        if ($token === '-') {
            return -$this->facteur();
        }
        if ($token === '+') {
            return $this->facteur();
        }
        if ($token === '(') {
            $value = $this->expression();
            if ($this->next() !== ')') {
                throw new InvalidArgumentException('Parenthèse fermante manquante.');
            }

            return $value;
        }
        if (is_numeric($token)) {
            return (float) $token;
        }
        if (in_array($token, self::VARIABLES, true)) {
            return (float) ($this->variables[$token] ?? 0);
        }

        throw new InvalidArgumentException(
            preg_match('/^[a-z_]+$/i', $token)
                ? "Variable inconnue « {$token} »."
                : "Caractère non autorisé « {$token} »."
        );
    }

    private function peek(): ?string
    {
        return $this->tokens[$this->pos] ?? null;
    }

    private function next(): ?string
    {
        return $this->tokens[$this->pos++] ?? null;
    }
}
