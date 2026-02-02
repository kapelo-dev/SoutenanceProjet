<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class UtilisateurController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = Utilisateur::with('profils');

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('profil_id')) {
            $query->whereHas('profils', function($q) use ($request) {
                $q->where('profil_id', $request->profil_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $utilisateurs = $query->orderBy('nom')->orderBy('prenom')->paginate(20);
        $profils = Profil::ordreParNiveau()->get();

        return $this->ajaxView('pages.utilisateurs.index', compact('utilisateurs', 'profils'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $profils = Profil::ordreParNiveau()->get();
        
        return view('pages.utilisateurs.create', compact('profils'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:utilisateurs,email',
            'mot_de_passe' => 'required|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'statut' => 'required|in:actif,inactif,suspendu',
            'profils' => 'required|array|min:1',
            'profils.*' => 'exists:profils,id',
        ]);

        // Hasher le mot de passe
        $validated['mot_de_passe'] = Hash::make($validated['mot_de_passe']);

        // Upload de la photo
        if ($request->hasFile('photo_profil')) {
            $photoPath = $request->file('photo_profil')->store('photos/utilisateurs', 'public');
            $validated['photo_profil'] = $photoPath;
        }

        // Créer l'utilisateur
        $profils = $validated['profils'];
        unset($validated['profils']);
        
        $utilisateur = Utilisateur::create($validated);
        
        // Attacher les profils
        $utilisateur->profils()->attach($profils);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès !');
    }

    /**
     * Afficher un utilisateur
     * - Si requête AJAX/API : retourne du JSON pour le modal
     * - Sinon : redirige vers la liste (car il n'y a pas de page dédiée)
     */
    public function show(Request $request, Utilisateur $utilisateur)
    {
        $utilisateur->load(['profils', 'agent', 'audits' => function($q) {
            $q->with('transaction')->latest()->limit(10);
        }]);

        // Si c'est une requête AJAX ou API, retourner du JSON pour le modal
        if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'utilisateur' => [
                    'id' => $utilisateur->id,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'email' => $utilisateur->email,
                    'telephone' => $utilisateur->telephone,
                    'statut' => $utilisateur->statut,
                    'photo_profil' => $utilisateur->photo_profil,
                    'photo_profil_url' => $utilisateur->photo_profil ? asset('storage/' . $utilisateur->photo_profil) : null,
                    'dernier_connexion' => $utilisateur->dernier_connexion ? $utilisateur->dernier_connexion->format('d/m/Y H:i') : null,
                    'profils' => $utilisateur->profils->map(function($profil) {
                        return [
                            'id' => $profil->id,
                            'libelle' => $profil->libelle,
                        ];
                    }),
                    'agent' => $utilisateur->agent ? [
                        'id' => $utilisateur->agent->id,
                        'nom' => $utilisateur->agent->nom,
                    ] : null,
                    'created_at' => $utilisateur->created_at->format('d/m/Y'),
                ]
            ]);
        }

        // Sinon, rediriger vers la liste (il n'y a pas de page dédiée, seulement un modal)
        return redirect()->route('utilisateurs.index')
            ->with('info', 'Utilisez le modal pour consulter les détails des utilisateurs.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Utilisateur $utilisateur)
    {
        $utilisateur->load('profils');
        $profils = Profil::ordreParNiveau()->get();
        
        return view('pages.utilisateurs.edit', compact('utilisateur', 'profils'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, Utilisateur $utilisateur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:utilisateurs,email,' . $utilisateur->id,
            'mot_de_passe' => 'nullable|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'statut' => 'required|in:actif,inactif,suspendu',
            'profils' => 'required|array|min:1',
            'profils.*' => 'exists:profils,id',
        ]);

        // Hasher le mot de passe si fourni
        if ($request->filled('mot_de_passe')) {
            $validated['mot_de_passe'] = Hash::make($validated['mot_de_passe']);
        } else {
            unset($validated['mot_de_passe']);
        }

        // Upload de la nouvelle photo
        if ($request->hasFile('photo_profil')) {
            if ($utilisateur->photo_profil) {
                Storage::disk('public')->delete($utilisateur->photo_profil);
            }
            $photoPath = $request->file('photo_profil')->store('photos/utilisateurs', 'public');
            $validated['photo_profil'] = $photoPath;
        }

        // Mettre à jour l'utilisateur
        $profils = $validated['profils'];
        unset($validated['profils']);
        
        $utilisateur->update($validated);
        
        // Synchroniser les profils
        $utilisateur->profils()->sync($profils);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur mis à jour avec succès !');
    }

    /**
     * Supprimer un utilisateur (soft delete)
     */
    public function destroy(Utilisateur $utilisateur)
    {
        // Vérifier s'il y a des audits liés
        if ($utilisateur->audits()->exists()) {
            return redirect()->route('utilisateurs.index')
                ->with('error', 'Impossible de supprimer cet utilisateur car il a effectué des modifications.');
        }

        $utilisateur->delete();

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé avec succès !');
    }

    /**
     * Changer le statut d'un utilisateur
     */
    public function changeStatut(Request $request, Utilisateur $utilisateur)
    {
        $request->validate([
            'statut' => 'required|in:actif,inactif,suspendu',
        ]);

        $utilisateur->update(['statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Statut modifié avec succès !',
            'statut' => $utilisateur->statut,
        ]);
    }

    /**
     * Obtenir les permissions de l'utilisateur connecté (API)
     */
    public function getMyPermissions()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer les profils de l'utilisateur
            $profils = $user->profils()->whereNull('user_profils.deleted_at')->get();
            
            if ($profils->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'profils' => [],
                    'routes' => [],
                    'permissions' => []
                ]);
            }

            // Récupérer toutes les routes/liens accessibles par les profils de l'utilisateur
            $profilIds = $profils->pluck('id')->toArray();
            
            $liens = DB::table('profil_liens')
                ->join('liens', 'profil_liens.lien_id', '=', 'liens.id')
                ->whereIn('profil_liens.profil_id', $profilIds)
                ->whereNull('profil_liens.deleted_at')
                ->whereNull('liens.deleted_at')
                ->where('liens.visible', true)
                ->select('liens.route', 'liens.url', 'liens.id as lien_id')
                ->distinct()
                ->get();

            // IMPORTANT:
            // Le sidebar utilise des href du type "/rapports" (URL), alors que la table "liens" stocke surtout le nom de route (ex: "rapports.index").
            // On renvoie donc une liste d'URLs autorisées (pathnames) + on garde aussi les route names.
            $allowedUrls = [];
            $allowedRouteNames = [];
            $permissionsByLien = [];

            foreach ($liens as $lien) {
                $permissionsByLien[$lien->lien_id] = $lien->route ?: $lien->url;

                if (!empty($lien->url)) {
                    // Normaliser l'URL pour qu'elle commence par /
                    $url = $lien->url;
                    if (!str_starts_with($url, '/')) {
                        $url = '/' . $url;
                    }
                    // Enlever le slash final sauf pour la racine
                    if (strlen($url) > 1 && str_ends_with($url, '/')) {
                        $url = rtrim($url, '/');
                    }
                    $allowedUrls[] = $url;
                }

                if (!empty($lien->route)) {
                    $allowedRouteNames[] = $lien->route;

                    if (Route::has($lien->route)) {
                        // route() retourne une URL complète; on ne garde que le path pour matcher le sidebar.
                        $full = route($lien->route);
                        $path = parse_url($full, PHP_URL_PATH);
                        if (!empty($path)) {
                            // Normaliser le path pour qu'il corresponde exactement au format du sidebar
                            // Le path devrait déjà commencer par / grâce à parse_url
                            // Enlever le slash final sauf pour la racine
                            if (strlen($path) > 1 && str_ends_with($path, '/')) {
                                $path = rtrim($path, '/');
                            }
                            $allowedUrls[] = $path;
                        }
                    }
                }
            }

            $allowedUrls = array_values(array_unique(array_filter($allowedUrls)));
            $allowedRouteNames = array_values(array_unique(array_filter($allowedRouteNames)));

            return response()->json([
                'success' => true,
                'profils' => $profils->map(function($profil) {
                    return [
                        'id' => $profil->id,
                        'libelle' => $profil->libelle,
                        'niveau' => $profil->niveau
                    ];
                }),
                // URLs utilisables directement côté front pour matcher href du sidebar
                'routes' => $allowedUrls,
                // Optionnel: noms de routes (debug / usage futur)
                'route_names' => $allowedRouteNames,
                // Map lien_id -> route/url (debug)
                'permissions' => $permissionsByLien
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans UtilisateurController@getMyPermissions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permissions'
            ], 500);
        }
    }

    /**
     * Obtenir les liens accessibles par un utilisateur (API)
     */
    public function liensAccessibles(Utilisateur $utilisateur)
    {
        $liens = \App\Models\Lien::whereHas('profils', function($query) use ($utilisateur) {
            $query->whereIn('profil_id', $utilisateur->profils->pluck('id'));
        })
        ->visible()
        ->whereNull('parent_id')
        ->with(['enfants' => function($q) use ($utilisateur) {
            $q->whereHas('profils', function($query) use ($utilisateur) {
                $query->whereIn('profil_id', $utilisateur->profils->pluck('id'));
            })->visible()->orderBy('ordre');
        }])
        ->orderBy('ordre')
        ->get();

        return response()->json($liens);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request, Utilisateur $utilisateur)
    {
        $request->validate([
            'nouveau_mot_de_passe' => 'required|string|min:8|confirmed',
        ]);

        $utilisateur->update([
            'mot_de_passe' => Hash::make($request->nouveau_mot_de_passe)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès !'
        ]);
    }
}
