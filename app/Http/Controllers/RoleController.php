<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Profil::query()
                ->when($this->hasParentColumn(), fn ($q) => $q->with('parent'))
                ->ordreAffichage()
                ->get();

            foreach ($roles as $role) {
                $role->users_count = $role->utilisateurs()->count();
            }

            $hasParentColumn = $this->hasParentColumn();

            return $this->ajaxView('pages.roles_et_permissions.gestion_roles.index', compact('roles', 'hasParentColumn'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@index: ' . $e->getMessage());

            return $this->ajaxView('pages.roles_et_permissions.gestion_roles.index', [
                'roles' => collect([]),
                'hasParentColumn' => false,
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = $this->makeValidator($request);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $attributes = [
                'libelle' => $request->libelle,
                'description' => $request->description ?? '',
            ];

            if ($this->hasParentColumn()) {
                $parentId = $this->resolveParentId($request);
                $role = new Profil($attributes);
                if ($role->wouldCreateParentCycle($parentId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreurs de validation',
                        'errors' => ['parent_id' => ['Le rôle parent choisi créerait une boucle d\'héritage.']],
                    ], 422);
                }
                $attributes['parent_id'] = $parentId;
            }

            $role = Profil::create($attributes);

            return response()->json([
                'success' => true,
                'message' => 'Rôle créé avec succès',
                'role' => $this->hasParentColumn() ? $role->load('parent') : $role,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $role = Profil::findOrFail($id);
            $validator = $this->makeValidator($request, $id);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $parentId = $this->hasParentColumn() ? $this->resolveParentId($request) : null;

            if ($this->hasParentColumn() && $role->wouldCreateParentCycle($parentId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => ['parent_id' => ['Le rôle parent choisi créerait une boucle d\'héritage.']],
                ], 422);
            }

            $payload = [
                'libelle' => $request->libelle,
                'description' => $request->description ?? '',
            ];

            if ($this->hasParentColumn()) {
                $payload['parent_id'] = $parentId;
            }

            $role->update($payload);

            return response()->json([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès',
                'role' => $this->hasParentColumn() ? $role->fresh(['parent']) : $role->fresh(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Profil::findOrFail($id);

            $usersCount = $role->utilisateurs()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer ce rôle car il est assigné à {$usersCount} utilisateur(s).",
                ], 422);
            }

            if ($this->hasParentColumn() && $role->enfants()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce rôle car d\'autres rôles en héritent.',
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rôle supprimé avec succès',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $role = Profil::query()
                ->when($this->hasParentColumn(), fn ($q) => $q->with(['parent', 'utilisateurs']))
                ->when(! $this->hasParentColumn(), fn ($q) => $q->with('utilisateurs'))
                ->findOrFail($id);
            $role->users_count = $role->utilisateurs()->count();

            return response()->json([
                'success' => true,
                'role' => $role,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans RoleController@show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rôle',
            ], 500);
        }
    }

    private function makeValidator(Request $request, ?int $roleId = null)
    {
        $rules = [
            'libelle' => 'required|string|max:100|unique:profils,libelle' . ($roleId ? ',' . $roleId : ''),
            'description' => 'nullable|string',
        ];

        if ($this->hasParentColumn()) {
            $parentRule = 'nullable|exists:profils,id';
            if ($roleId) {
                $parentRule .= '|not_in:' . $roleId;
            }
            $rules['parent_id'] = $parentRule;
        }

        return Validator::make($request->all(), $rules, [
            'libelle.required' => 'Le nom du rôle est requis.',
            'libelle.unique' => 'Ce nom de rôle existe déjà.',
            'parent_id.exists' => 'Le rôle parent sélectionné n\'existe pas.',
            'parent_id.not_in' => 'Un rôle ne peut pas être son propre parent.',
        ]);
    }

    private function hasParentColumn(): bool
    {
        return Schema::hasColumn('profils', 'parent_id');
    }

    private function resolveParentId(Request $request): ?int
    {
        if (! $request->filled('parent_id')) {
            return null;
        }

        return (int) $request->parent_id;
    }
}
