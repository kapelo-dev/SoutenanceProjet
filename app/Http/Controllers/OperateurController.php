<?php

namespace App\Http\Controllers;

use App\Models\Operateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OperateurController extends Controller
{
    /**
     * Afficher la liste des opérateurs
     */
    public function index()
    {
        $operateurs = Operateur::orderBy('ordre')->get();
        
        return view('pages.operateurs.index', compact('operateurs'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('pages.operateurs.create');
    }

    /**
     * Enregistrer un nouvel opérateur
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:operateurs,code',
                'libelle' => 'required|string|max:100',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'couleur' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'statut' => 'required|in:actif,inactif',
                'ordre' => 'nullable|integer|min:0',
            ]);

            // Upload du logo
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos/operateurs', 'public');
                $validated['logo'] = $logoPath;
            }

            $operateur = Operateur::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Opérateur créé avec succès !',
                    'operateur' => $operateur
                ]);
            }

            return redirect()->route('operateurs.index')
                ->with('success', 'Opérateur créé avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Afficher un opérateur
     */
    public function show(Request $request, Operateur $operateur)
    {
        $operateur->load(['transactions' => function($query) {
            $query->latest()->limit(10);
        }]);

        // Statistiques
        $stats = [
            'transactions_total' => $operateur->transactions()->count(),
            'montant_total' => $operateur->transactions()->where('statut', 'valide')->sum('montant') ?? 0,
            'commission_total' => $operateur->transactions()->where('statut', 'valide')->sum('commission') ?? 0,
            'transactions_mois' => $operateur->transactions()->duMois()->count(),
            'montant_mois' => $operateur->transactions()->duMois()->where('statut', 'valide')->sum('montant') ?? 0,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'operateur' => $operateur,
                'stats' => $stats
            ]);
        }

        return view('pages.operateurs.show', compact('operateur', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Request $request, Operateur $operateur)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'operateur' => $operateur
            ]);
        }

        return view('pages.operateurs.edit', compact('operateur'));
    }

    /**
     * Mettre à jour un opérateur
     */
    public function update(Request $request, Operateur $operateur)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:operateurs,code,' . $operateur->id,
                'libelle' => 'required|string|max:100',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'couleur' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'statut' => 'required|in:actif,inactif',
                'ordre' => 'nullable|integer|min:0',
            ]);

            // Upload du nouveau logo
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
                if ($operateur->logo) {
                    Storage::disk('public')->delete($operateur->logo);
                }
                $logoPath = $request->file('logo')->store('logos/operateurs', 'public');
                $validated['logo'] = $logoPath;
            }

            $operateur->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Opérateur mis à jour avec succès !',
                    'operateur' => $operateur
                ]);
            }

            return redirect()->route('operateurs.index')
                ->with('success', 'Opérateur mis à jour avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Supprimer un opérateur (soft delete)
     */
    public function destroy(Operateur $operateur)
    {
        // Vérifier s'il y a des transactions liées
        if ($operateur->transactions()->exists()) {
            return redirect()->route('operateurs.index')
                ->with('error', 'Impossible de supprimer cet opérateur car il a des transactions associées.');
        }

        $operateur->delete();

        return redirect()->route('operateurs.index')
            ->with('success', 'Opérateur supprimé avec succès !');
    }

    /**
     * Activer/Désactiver un opérateur
     */
    public function toggleStatus(Operateur $operateur)
    {
        $operateur->update([
            'statut' => $operateur->statut === 'actif' ? 'inactif' : 'actif'
        ]);

        return response()->json([
            'success' => true,
            'statut' => $operateur->statut,
            'message' => 'Statut mis à jour avec succès !'
        ]);
    }

    /**
     * Obtenir les statistiques d'un opérateur (API)
     */
    public function statistiques(Operateur $operateur)
    {
        $stats = [
            'operateur' => $operateur->only(['code', 'libelle', 'couleur']),
            'transactions' => [
                'total' => $operateur->transactions()->valide()->count(),
                'montant_total' => $operateur->transactions()->valide()->sum('montant'),
                'commission_total' => $operateur->transactions()->valide()->sum('commission'),
            ],
            'jour' => [
                'count' => $operateur->transactions()->valide()->duJour()->count(),
                'montant' => $operateur->transactions()->valide()->duJour()->sum('montant'),
            ],
            'mois' => [
                'count' => $operateur->transactions()->valide()->duMois()->count(),
                'montant' => $operateur->transactions()->valide()->duMois()->sum('montant'),
            ],
        ];

        return response()->json($stats);
    }
}
