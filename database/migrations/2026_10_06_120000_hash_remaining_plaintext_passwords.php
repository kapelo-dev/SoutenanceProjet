<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Hache les mots de passe encore stockés en clair (anciens comptes).
     *
     * La connexion acceptait jusqu'ici un mot de passe égal à la valeur stockée, pour migrer ces comptes
     * au fil de l'eau. Ce mécanisme est supprimé : il permettait aussi de se connecter en envoyant le hash
     * lui-même. Les comptes concernés gardent le même mot de passe, désormais haché.
     */
    public function up(): void
    {
        DB::table('utilisateurs')
            ->select(['id', 'mot_de_passe'])
            ->orderBy('id')
            ->each(function ($utilisateur) {
                $valeur = (string) $utilisateur->mot_de_passe;

                // algo null : la valeur n'est pas un hash (bcrypt, argon…) reconnu par PHP
                if ($valeur !== '' && password_get_info($valeur)['algo'] === null) {
                    DB::table('utilisateurs')
                        ->where('id', $utilisateur->id)
                        ->update(['mot_de_passe' => Hash::make($valeur)]);
                }
            });
    }

    public function down(): void
    {
        // Irréversible : un hash ne peut pas être ramené au mot de passe en clair.
    }
};
