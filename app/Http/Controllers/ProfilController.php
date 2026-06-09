<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    /**
     * Affiche la page de profil de l'utilisateur connecté.
     */
    public function index()
    {
        $user = auth()->user();
        $user->load(['profils', 'agent']);

        return $this->ajaxView('pages.profil.index', compact('user'));
    }

    /**
     * Met à jour le profil de l'utilisateur connecté.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $emailRules = $user->isAgent()
            ? 'nullable|email|max:100|unique:utilisateurs,email,' . $user->id
            : 'required|email|max:100|unique:utilisateurs,email,' . $user->id;

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => $emailRules,
            'telephone' => 'nullable|string|max:20',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.unique' => "Cette adresse email est déjà utilisée.",
        ]);

        if ($request->hasFile('photo_profil')) {
            if ($user->photo_profil) {
                Storage::disk('public')->delete($user->photo_profil);
            }
            $validated['photo_profil'] = $request->file('photo_profil')->store('photos/utilisateurs', 'public');
        }

        $user->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès.',
                'redirect' => route('profil.index'),
            ]);
        }

        return redirect()->route('profil.index')->with('success', 'Profil mis à jour avec succès.');
    }
}
