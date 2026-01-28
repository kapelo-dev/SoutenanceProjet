<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Afficher la liste des rôles
     */
    public function index()
    {
        try {
            $roles = Profil::ordreParNiveau()->get();
            
            // Compter le nombre d'utilisateurs par rôle
            foreach ($roles as $role) {
                $role->users_count = $role->utilisateurs()->count();
            }

            return $this->ajaxView('pages.roles_et_permissions.gestion_roles.index', compact('roles'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@index: ' . $e->getMessage());
            return $this->ajaxView('pages.roles_et_permissions.gestion_roles.index', [
                'roles' => collect([])
            ]);
        }
    }

    /**
     * Créer un nouveau rôle
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'libelle' => 'required|string|max:100|unique:profils,libelle',
                'description' => 'nullable|string',
                'niveau' => 'required|integer|min:0|max:10',
            ], [
                'libelle.required' => 'Le nom du rôle est requis.',
                'libelle.unique' => 'Ce nom de rôle existe déjà.',
                'niveau.required' => 'Le niveau est requis.',
                'niveau.integer' => 'Le niveau doit être un nombre entier.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Profil::create([
                'libelle' => $request->libelle,
                'description' => $request->description ?? '',
                'niveau' => $request->niveau ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rôle créé avec succès',
                'role' => $role
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rôle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Profil::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'libelle' => 'required|string|max:100|unique:profils,libelle,' . $id,
                'description' => 'nullable|string',
                'niveau' => 'required|integer|min:0|max:10',
            ], [
                'libelle.required' => 'Le nom du rôle est requis.',
                'libelle.unique' => 'Ce nom de rôle existe déjà.',
                'niveau.required' => 'Le niveau est requis.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role->update([
                'libelle' => $request->libelle,
                'description' => $request->description ?? '',
                'niveau' => $request->niveau ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès',
                'role' => $role->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un rôle
     */
    public function destroy($id)
    {
        try {
            $role = Profil::findOrFail($id);
            
            // Vérifier si le rôle est utilisé
            $usersCount = $role->utilisateurs()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer ce rôle car il est assigné à {$usersCount} utilisateur(s)."
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rôle supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rôle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'un rôle
     */
    public function show($id)
    {
        try {
            $role = Profil::with('utilisateurs')->findOrFail($id);
            $role->users_count = $role->utilisateurs()->count();
            
            return response()->json([
                'success' => true,
                'role' => $role
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rôle'
            ], 500);
        }
    }
}
