<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Les comptes d'agents créés avant cette version avaient tous le mot de passe « changeMe ».
     * Ceux qui l'ont encore devront le changer à leur prochaine connexion (web ou mobile).
     */
    public function up(): void
    {
        // Liste chargée d'abord (une ligne par agent) : on modifie le champ filtré pendant le parcours,
        // ce qu'un parcours par lots (offset) ne supporte pas.
        DB::table('utilisateurs')
            ->join('agents', 'agents.user_id', '=', 'utilisateurs.id')
            ->whereNotNull('utilisateurs.dernier_connexion')
            ->select(['utilisateurs.id', 'utilisateurs.mot_de_passe'])
            ->get()
            ->each(function ($utilisateur) {
                $motDePasseParDefaut = rescue(
                    fn () => Hash::check('changeMe', (string) $utilisateur->mot_de_passe),
                    false,
                    false
                );

                if ($motDePasseParDefaut) {
                    DB::table('utilisateurs')
                        ->where('id', $utilisateur->id)
                        ->update(['dernier_connexion' => null]);
                }
            });
    }

    public function down(): void
    {
        // Rien à annuler : seul le marqueur « mot de passe à changer » a été posé.
    }
};
