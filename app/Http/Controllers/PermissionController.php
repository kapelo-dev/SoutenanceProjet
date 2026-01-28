<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use App\Models\Lien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Afficher la matrice des permissions
     */
    public function index()
    {
        try {
            // Récupérer tous les rôles (profils) triés par niveau
            $roles = Profil::ordreParNiveau()->get();
            
            // Récupérer tous les liens/routes triés par ordre
            $liens = Lien::orderBy('ordre')->orderBy('libelle')->get();
            
            // Récupérer toutes les permissions existantes (profil_id, lien_id)
            $permissions = DB::table('profil_liens')
                ->whereNull('deleted_at')
                ->select('profil_id', 'lien_id')
                ->get()
                ->map(function($item) {
                    return $item->profil_id . '_' . $item->lien_id;
                })
                ->toArray();
            
            return $this->ajaxView('pages.roles_et_permissions.gestion_permissions.index', compact('roles', 'liens', 'permissions'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans PermissionController@index: ' . $e->getMessage());
            return $this->ajaxView('pages.roles_et_permissions.gestion_permissions.index', [
                'roles' => collect([]),
                'liens' => collect([]),
                'permissions' => []
            ]);
        }
    }

    /**
     * Toggle une permission (activer/désactiver)
     */
    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'profil_id' => 'required|exists:profils,id',
                'lien_id' => 'required|exists:liens,id',
            ]);

            $profilId = $request->profil_id;
            $lienId = $request->lien_id;

            // Vérifier si la permission existe
            $permission = DB::table('profil_liens')
                ->where('profil_id', $profilId)
                ->where('lien_id', $lienId)
                ->whereNull('deleted_at')
                ->first();

            if ($permission) {
                // Désactiver la permission (soft delete)
                DB::table('profil_liens')
                    ->where('profil_id', $profilId)
                    ->where('lien_id', $lienId)
                    ->update(['deleted_at' => now()]);
                
                $action = 'removed';
            } else {
                // Activer la permission
                // Vérifier si elle existe avec deleted_at
                $existing = DB::table('profil_liens')
                    ->where('profil_id', $profilId)
                    ->where('lien_id', $lienId)
                    ->first();

                if ($existing) {
                    // Restaurer
                    DB::table('profil_liens')
                        ->where('profil_id', $profilId)
                        ->where('lien_id', $lienId)
                        ->update(['deleted_at' => null, 'updated_at' => now()]);
                } else {
                    // Créer
                    DB::table('profil_liens')->insert([
                        'profil_id' => $profilId,
                        'lien_id' => $lienId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                $action = 'added';
            }

            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => $action === 'added' ? 'Permission accordée' : 'Permission retirée'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans PermissionController@toggle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification de la permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sauvegarder toutes les permissions en masse
     */
    public function saveAll(Request $request)
    {
        try {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*.profil_id' => 'required|exists:profils,id',
                'permissions.*.lien_id' => 'required|exists:liens,id',
                'permissions.*.granted' => 'required|boolean',
            ]);

            DB::beginTransaction();

            foreach ($request->permissions as $permission) {
                $profilId = $permission['profil_id'];
                $lienId = $permission['lien_id'];
                $granted = $permission['granted'];

                if ($granted) {
                    // Vérifier si la permission existe déjà
                    $existing = DB::table('profil_liens')
                        ->where('profil_id', $profilId)
                        ->where('lien_id', $lienId)
                        ->first();

                    if ($existing) {
                        // Restaurer si supprimée
                        if ($existing->deleted_at) {
                            DB::table('profil_liens')
                                ->where('profil_id', $profilId)
                                ->where('lien_id', $lienId)
                                ->update(['deleted_at' => null, 'updated_at' => now()]);
                        }
                    } else {
                        // Créer
                        DB::table('profil_liens')->insert([
                            'profil_id' => $profilId,
                            'lien_id' => $lienId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } else {
                    // Supprimer (soft delete)
                    DB::table('profil_liens')
                        ->where('profil_id', $profilId)
                        ->where('lien_id', $lienId)
                        ->update(['deleted_at' => now()]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions sauvegardées avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur dans PermissionController@saveAll: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ], 500);
        }
    }
}
