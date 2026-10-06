<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;

    protected static ?string $motDePasse = null;

    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'mot_de_passe' => static::$motDePasse ??= Hash::make('password'),
            'statut' => 'actif',
            // Déjà connecté une fois : pas de redirection vers le changement de mot de passe
            'dernier_connexion' => now(),
        ];
    }

    /**
     * Première connexion : l'utilisateur doit changer son mot de passe.
     */
    public function premiereConnexion(): static
    {
        return $this->state(fn () => ['dernier_connexion' => null]);
    }
}
