<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;

        // Rechercher l'utilisateur par email
        $utilisateur = Utilisateur::where('email', $email)->first();

        if (!$utilisateur) {
            return back()->withErrors([
                'email' => 'Les identifiants fournis sont incorrects.',
            ])->withInput($request->only('email'));
        }

        // Vérifier le statut de l'utilisateur
        if ($utilisateur->statut !== 'actif') {
            return back()->withErrors([
                'email' => 'Votre compte est désactivé ou suspendu.',
            ])->withInput($request->only('email'));
        }

        // Vérifier le mot de passe
        // Si le mot de passe n'est pas hashé, le hasher et le mettre à jour
        if (!Hash::check($password, $utilisateur->mot_de_passe)) {
            // Vérifier si c'est un mot de passe en clair (pour migration)
            if ($utilisateur->mot_de_passe === $password) {
                // Hasher le mot de passe et le sauvegarder
                $utilisateur->mot_de_passe = Hash::make($password);
                $utilisateur->save();
            } else {
                return back()->withErrors([
                    'email' => 'Les identifiants fournis sont incorrects.',
                ])->withInput($request->only('email'));
            }
        }

        // Authentifier l'utilisateur
        Auth::login($utilisateur, $request->boolean('remember'));

        // Régénérer la session pour éviter la fixation de session
        $request->session()->regenerate();

        // Vérifier si c'est la première connexion (dernier_connexion est null)
        if (is_null($utilisateur->dernier_connexion)) {
            // Rediriger vers la page de changement de mot de passe
            return redirect()->route('password.change');
        }

        // Mettre à jour la date de dernière connexion
        $utilisateur->dernier_connexion = now();
        $utilisateur->save();

        // Rediriger vers la page demandée ou le dashboard
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showChangePasswordForm()
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Vérifier que c'est bien la première connexion
        if (!is_null(Auth::user()->dernier_connexion)) {
            return redirect()->route('dashboard');
        }

        return view('auth.change-password');
    }

    /**
     * Traiter le changement de mot de passe
     */
    public function changePassword(Request $request)
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Vérifier que c'est bien la première connexion
        if (!is_null(Auth::user()->dernier_connexion)) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $utilisateur = Auth::user();

        // Mettre à jour le mot de passe
        $utilisateur->mot_de_passe = Hash::make($request->password);
        
        // Mettre à jour la date de dernière connexion pour permettre l'accès normal
        $utilisateur->dernier_connexion = now();
        
        $utilisateur->save();

        // Rafraîchir l'utilisateur dans la session pour que le middleware voie la nouvelle valeur
        Auth::setUser($utilisateur->fresh());

        // Régénérer la session
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Votre mot de passe a été changé avec succès. Bienvenue !');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
