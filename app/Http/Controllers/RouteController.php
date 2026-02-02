<?php

namespace App\Http\Controllers;

use App\Models\Lien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Affiche la liste de toutes les routes/liens
     */
    public function index()
    {
        // Récupérer tous les liens (visibles et non visibles), triés par ordre, avec la relation parent
        $liens = Lien::with('parent')
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->get();
        
        // Récupérer les menus principaux pour le select du modal
        $menusParents = Lien::whereNull('parent_id')
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->get();

        return $this->ajaxView('pages.roles_et_permissions.gestion_routes.index', compact('liens', 'menusParents'));
    }

    /**
     * Enregistre une nouvelle route/lien
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:100',
            'route' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'icone' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:liens,id',
            'ordre' => 'nullable|integer|min:0',
            'visible' => 'nullable|boolean',
        ], [
            'libelle.required' => 'Le libellé est requis.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 100 caractères.',
            'route.max' => 'La route ne peut pas dépasser 100 caractères.',
            'url.max' => 'L\'URL ne peut pas dépasser 255 caractères.',
            'icone.max' => 'L\'icône ne peut pas dépasser 50 caractères.',
            'parent_id.exists' => 'Le menu parent sélectionné n\'existe pas.',
            'ordre.integer' => 'L\'ordre doit être un nombre entier.',
            'ordre.min' => 'L\'ordre doit être supérieur ou égal à 0.',
        ]);

        // Vérifier qu'au moins route ou url est fourni
        $customValidator = Validator::make($request->all(), []);
        if (empty($request->route) && empty($request->url)) {
            $customValidator->errors()->add('route', 'Vous devez fournir soit une route Laravel, soit une URL.');
        }

        if ($validator->fails() || $customValidator->fails()) {
            $errors = $validator->errors()->merge($customValidator->errors());
            return response()->json([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        try {
            // Créer le lien
            $lien = Lien::create([
                'libelle' => $request->libelle,
                'route' => !empty($request->route) ? $request->route : null,
                'url' => !empty($request->url) ? $request->url : null,
                'icone' => !empty($request->icone) ? $request->icone : null,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : null,
                'ordre' => $request->ordre ?? 0,
                'visible' => $request->visible == 1 || $request->visible === true || $request->visible === '1',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Route créée avec succès.',
                'lien' => $lien
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour la visibilité des routes/liens
     */
    public function updateVisibility(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'routes' => 'required|array',
            'routes.*.id' => 'required|exists:liens,id',
            'routes.*.visible' => 'required|boolean',
        ], [
            'routes.required' => 'Les données des routes sont requises.',
            'routes.array' => 'Les routes doivent être un tableau.',
            'routes.*.id.required' => 'L\'ID de la route est requis.',
            'routes.*.id.exists' => 'Une ou plusieurs routes n\'existent pas.',
            'routes.*.visible.required' => 'L\'état de visibilité est requis.',
            'routes.*.visible.boolean' => 'L\'état de visibilité doit être un booléen.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updated = 0;
            foreach ($request->routes as $routeData) {
                $lien = Lien::find($routeData['id']);
                if ($lien) {
                    $lien->visible = $routeData['visible'];
                    $lien->save();
                    $updated++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $updated . ' route(s) mise(s) à jour avec succès.',
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }
}
