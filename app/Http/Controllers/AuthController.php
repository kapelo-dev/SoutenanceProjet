<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\Utilisateur;
use App\Services\IpBlockService;
use App\Support\AuthIdentifier;
use App\Support\UserHomeRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected IpBlockService $ipBlockService
    ) {}

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->to(UserHomeRedirect::pathFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string|max:100',
            'password' => 'required|string',
        ], [
            'identifiant.required' => 'L\'identifiant est requis.',
        ]);

        $identifiant = trim($request->identifiant);
        $password = $request->password;

        $utilisateur = AuthIdentifier::resolveUtilisateur($identifiant);

        if (! $utilisateur) {
            $this->logLoginFailed(
                $request,
                "Tentative de connexion échouée pour l'identifiant : {$identifiant}",
                null,
                ['identifiant' => $identifiant]
            );

            return back()->withErrors([
                'identifiant' => 'Les identifiants fournis sont incorrects.',
            ])->withInput($request->only('identifiant'));
        }

        if ($utilisateur->statut !== 'actif') {
            $this->logLoginFailed(
                $request,
                "Tentative de connexion sur compte {$utilisateur->statut} : {$utilisateur->nom} {$utilisateur->prenom}",
                $utilisateur->id,
                ['statut' => $utilisateur->statut, 'identifiant' => $identifiant]
            );

            return back()->withErrors([
                'identifiant' => 'Votre compte est désactivé ou suspendu.',
            ])->withInput($request->only('identifiant'));
        }

        if (! Hash::check($password, $utilisateur->mot_de_passe)) {
            if ($utilisateur->mot_de_passe === $password) {
                $utilisateur->mot_de_passe = Hash::make($password);
                $utilisateur->save();
            } else {
                $this->logLoginFailed(
                    $request,
                    "Tentative de connexion échouée (mot de passe incorrect) : {$utilisateur->nom} {$utilisateur->prenom}",
                    $utilisateur->id,
                    ['raison' => 'mot_de_passe_incorrect', 'identifiant' => $identifiant]
                );

                return back()->withErrors([
                    'identifiant' => 'Les identifiants fournis sont incorrects.',
                ])->withInput($request->only('identifiant'));
            }
        }

        Auth::login($utilisateur, $request->boolean('remember'));
        $request->session()->regenerate();

        if (is_null($utilisateur->dernier_connexion)) {
            return redirect()->route('password.change');
        }

        $utilisateur->dernier_connexion = now();
        $utilisateur->save();

        SystemLog::logLogin($utilisateur, true);

        return redirect()->intended(UserHomeRedirect::pathFor($utilisateur));
    }

    public function showChangePasswordForm()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! is_null(Auth::user()->dernier_connexion)) {
            return redirect()->to(UserHomeRedirect::pathFor(Auth::user()));
        }

        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! is_null(Auth::user()->dernier_connexion)) {
            return redirect()->to(UserHomeRedirect::pathFor(Auth::user()));
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $utilisateur = Auth::user();
        $utilisateur->mot_de_passe = Hash::make($request->password);
        $utilisateur->dernier_connexion = now();
        $utilisateur->save();

        Auth::setUser($utilisateur->fresh());
        $request->session()->regenerate();

        return redirect()
            ->to(UserHomeRedirect::pathFor($utilisateur))
            ->with('status', 'Votre mot de passe a été changé avec succès. Bienvenue !');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            SystemLog::logLogout($user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function logLoginFailed(Request $request, string $description, ?int $userId = null, array $metadata = []): void
    {
        if (config('security.audit_logging_enabled', true)) {
            SystemLog::create([
                'user_id' => $userId,
                'action' => 'login_failed',
                'description' => $description,
                'ip_address' => $request->clientIp(),
                'user_agent' => $request->userAgent(),
                'metadata' => $metadata,
            ]);
        }

        $this->ipBlockService->recordLoginFailure($request, $userId);
    }
}
