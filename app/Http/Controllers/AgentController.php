<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Kiosque;
use App\Models\Utilisateur;
use App\Models\Solde;
use App\Models\Operateur;
use App\Models\Profil;
use App\Models\Transaction;
use App\Models\TypeOperation;
use App\Models\AgentKiosqueHistorique;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    use Exportable;
    /**
     * Afficher la liste des agents
     */
    public function index(Request $request)
    {
        try {
            $query = Agent::with(['kiosque', 'utilisateur']);

            // Filtres
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('kiosque_id')) {
                $query->where('kiosque_id', $request->kiosque_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('code_agent', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%");
                });
            }

            $agents = $query->orderBy('nom')->orderBy('prenom')->paginate(20);
            $kiosques = Kiosque::actif()->orderBy('nom')->get();
            $operateurs = Operateur::actif()->get();

            return $this->ajaxView('pages.agents.liste_agents.index', compact('agents', 'kiosques', 'operateurs'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans AgentController@index: ' . $e->getMessage());
            return $this->ajaxView('pages.agents.liste_agents.index', [
                'agents' => \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 20),
                'kiosques' => Kiosque::actif()->orderBy('nom')->get(),
                'operateurs' => Operateur::actif()->get()
            ])->with('error', 'Une erreur est survenue lors du chargement des données.');
        }
    }

    /**
     * Afficher la page des soldes des agents
     */
    public function soldes(Request $request)
    {
        try {
            $query = Agent::with(['kiosque', 'utilisateur', 'soldes.operateur']);

            // Filtrer uniquement les agents avec soldes positifs
            if ($request->has('soldes_positifs') && $request->soldes_positifs == 1) {
                $query->whereHas('soldes', function($q) {
                    $q->where('montant', '>', 0);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('code_agent', 'like', "%{$search}%");
                });
            }

            $agents = $query->actif()->orderBy('nom')->get();
            $operateurs = Operateur::actif()->get();
            
            // Calculer les commissions pour chaque agent (sera fait dans la vue pour éviter N+1)

            return $this->ajaxView('pages.agents.solde.index', compact('agents', 'operateurs'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans AgentController@soldes: ' . $e->getMessage());
            return $this->ajaxView('pages.agents.solde.index', [
                'agents' => collect(),
                'operateurs' => Operateur::actif()->get()
            ])->with('error', 'Une erreur est survenue lors du chargement des données.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Générer le prochain code agent
        $lastCode = Agent::where('code_agent', 'like', 'AG%')
            ->orderBy('code_agent', 'desc')
            ->first();
        
        $nextNumber = $lastCode ? intval(substr($lastCode->code_agent, 2)) + 1 : 1;
        $suggestedCode = 'AG' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $kiosques = Kiosque::actif()->orderBy('nom')->get();
        $utilisateurs = Utilisateur::actif()
            ->whereDoesntHave('agent')
            ->orderBy('nom')
            ->get();

        return view('pages.agents.create', compact('suggestedCode', 'kiosques', 'utilisateurs'));
    }

    /**
     * Enregistrer un nouvel agent
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_agent' => 'nullable|string|max:50|unique:agents,code_agent',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'required|string|max:20|unique:agents,telephone',
            'montant_initial_total' => 'nullable|numeric|min:0',
            'espece_initiale' => 'nullable|numeric|min:0',
            'kiosque_id' => 'nullable|exists:kiosques,id',
            'user_id' => 'nullable|exists:utilisateurs,id',
            'statut' => 'required|in:actif,inactif,suspendu,en_attente',
        ]);

        // Vérifier si le kiosque n'est pas saturé
        if ($request->filled('kiosque_id')) {
            $kiosque = Kiosque::find($request->kiosque_id);
            if ($kiosque && $kiosque->estSature()) {
                return back()->withErrors([
                    'kiosque_id' => 'Le kiosque sélectionné a atteint sa capacité maximale.'
                ])->withInput();
            }
        }

        $agent = Agent::create($validated);

        // Créer les soldes initiaux si spécifiés
        if ($request->filled('espece_initiale') && $request->espece_initiale > 0) {
            Solde::create([
                'agent_id' => $agent->id,
                'operateur_id' => null,
                'montant' => $request->espece_initiale,
                'type' => 'espece',
                'description' => 'Solde initial',
            ]);
        }

        return redirect()->route('agents.show', $agent)
            ->with('success', 'Agent créé avec succès !');
    }

    /**
     * Enregistrer un nouvel agent avec création optionnelle de kiosque
     */
    public function storeWithKiosque(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validation Agent
            $agentRules = [
                'code_agent' => 'required|string|max:50|unique:agents,code_agent',
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'telephone' => 'required|string|max:20|unique:agents,telephone',
                'email' => 'required|email|max:255|unique:utilisateurs,email',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'espece_initiale' => 'nullable|numeric|min:0',
                'kiosque_id' => 'nullable|exists:kiosques,id',
                'statut' => 'required|in:actif,inactif,suspendu,en_attente',
            ];
            
            // Validation des montants virtuels par opérateur
            $operateurs = Operateur::actif()->get();
            foreach ($operateurs as $operateur) {
                $agentRules['montant_virtuel_' . $operateur->id] = 'nullable|numeric|min:0';
            }

            // Validation Kiosque si création
            // Vérifier si creer_kiosque est à '1' ou si kiosque est présent
            $creerKiosque = $request->input('creer_kiosque') === '1' || $request->input('creer_kiosque') === 1 || $request->has('kiosque');
            
            if ($creerKiosque && $request->has('kiosque') && is_array($request->kiosque)) {
                $kiosqueRules = [
                    'kiosque.code' => 'required|string|max:50|unique:kiosques,code',
                    'kiosque.nom' => 'required|string|max:100',
                    'kiosque.adresse' => 'required|string|max:255',
                    'kiosque.ville' => 'required|string|max:100',
                    'kiosque.latitude' => 'required|numeric|between:-90,90',
                    'kiosque.longitude' => 'required|numeric|between:-180,180',
                    'kiosque.quartier' => 'nullable|string|max:100',
                    'kiosque.telephone' => 'nullable|string|max:20',
                    'kiosque.type' => 'nullable|string|in:fixe,mobile',
                    'kiosque.capacite_agents' => 'nullable|integer|min:1',
                    'kiosque.horaire_ouverture' => 'nullable|date_format:H:i',
                    'kiosque.horaire_fermeture' => 'nullable|date_format:H:i',
                    'kiosque.description' => 'nullable|string',
                    'kiosque.statut' => 'nullable|string|in:actif,inactif',
                ];
                
                $validator = \Validator::make($request->all(), array_merge($agentRules, $kiosqueRules));
            } else {
                $validator = \Validator::make($request->all(), $agentRules);
            }

            if ($validator->fails()) {
                \Log::error('Erreur de validation dans AgentController@storeWithKiosque', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['photo', 'password', 'mot_de_passe'])
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kiosqueId = null;

            // Créer le kiosque si demandé
            $creerKiosque = $request->input('creer_kiosque') === '1' || $request->input('creer_kiosque') === 1 || ($request->has('kiosque') && is_array($request->kiosque));
            
            if ($creerKiosque && $request->has('kiosque') && is_array($request->kiosque)) {
                $kiosqueData = $request->kiosque;
                $kiosque = Kiosque::create([
                    'code' => $kiosqueData['code'],
                    'nom' => $kiosqueData['nom'],
                    'adresse' => $kiosqueData['adresse'],
                    'quartier' => $kiosqueData['quartier'] ?? null,
                    'ville' => $kiosqueData['ville'],
                    'telephone' => $kiosqueData['telephone'] ?? null,
                    'latitude' => $kiosqueData['latitude'],
                    'longitude' => $kiosqueData['longitude'],
                    'type' => $kiosqueData['type'] ?? 'fixe',
                    'capacite_agents' => $kiosqueData['capacite_agents'] ?? 5,
                    'horaire_ouverture' => !empty($kiosqueData['horaire_ouverture']) ? $kiosqueData['horaire_ouverture'] : '08:00:00',
                    'horaire_fermeture' => !empty($kiosqueData['horaire_fermeture']) ? $kiosqueData['horaire_fermeture'] : '18:00:00',
                    'description' => $kiosqueData['description'] ?? null,
                    'statut' => $kiosqueData['statut'] ?? 'actif',
                ]);
                $kiosqueId = $kiosque->id;
            } elseif ($request->filled('kiosque_id')) {
                $kiosqueId = $request->kiosque_id;
                // Vérifier si le kiosque n'est pas saturé
                $kiosque = Kiosque::find($kiosqueId);
                if ($kiosque && $kiosque->estSature()) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['kiosque_id' => ['Le kiosque sélectionné a atteint sa capacité maximale.']]
                    ], 422);
                }
            }

            // Créer l'utilisateur automatiquement
            $motDePasse = Str::random(12); // Mot de passe généré aléatoirement
            
            // Upload de la photo de profil si fournie
            $photoProfil = null;
            if ($request->hasFile('photo')) {
                $photoProfil = $request->file('photo')->store('photos/profils', 'public');
            }
            
            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'mot_de_passe' => Hash::make($motDePasse),
                'photo_profil' => $photoProfil,
                'statut' => 'actif',
            ]);

            // Assigner le profil "Agent" par défaut
            $profilAgent = Profil::where('libelle', 'Agent')->first();
            if ($profilAgent) {
                $utilisateur->profils()->attach($profilAgent->id);
            } else {
                // Si le profil Agent n'existe pas, créer un log d'erreur
                \Log::warning('Le profil "Agent" n\'existe pas dans la base de données. L\'utilisateur ' . $utilisateur->id . ' n\'a pas de profil assigné.');
            }

            // Créer l'agent
            $agentData = $request->only([
                'code_agent', 'nom', 'prenom', 'telephone', 'statut'
            ]);
            $agentData['kiosque_id'] = $kiosqueId;
            $agentData['user_id'] = $utilisateur->id;
            
            // Calculer le montant initial total (espèce + montants virtuels)
            $montantTotal = $request->espece_initiale ?? 0;
            $operateurs = Operateur::actif()->get();
            foreach ($operateurs as $operateur) {
                $montantVirtuel = $request->input('montant_virtuel_' . $operateur->id, 0);
                if ($montantVirtuel > 0) {
                    $montantTotal += $montantVirtuel;
                }
            }
            $agentData['montant_initial_total'] = $montantTotal;
            
            $agent = Agent::create($agentData);
            
            // Créer l'historique d'affectation si un kiosque est assigné
            if ($kiosqueId) {
                AgentKiosqueHistorique::create([
                    'agent_id' => $agent->id,
                    'kiosque_id' => $kiosqueId,
                    'date_debut' => now(),
                    'type_mouvement' => 'affectation',
                    'created_by' => auth()->id(),
                ]);
            }

            // Enregistrer les montants initiaux comme des opérations en agence
            $typeApportEspece = TypeOperation::where('code', 'apport_espece')->first();
            $typeApportVirtuel = TypeOperation::where('code', 'apport_virtuel')->first();
            
            if ($typeApportEspece && $request->filled('espece_initiale') && $request->espece_initiale > 0) {
                $transactionEspece = Transaction::create([
                    'agent_id' => $agent->id,
                    'type_operation_id' => $typeApportEspece->id,
                    'operateur_id' => null,
                    'montant' => $request->espece_initiale,
                    'type' => 'depot',
                    'statut' => 'valide',
                    'description' => 'Montant initial en espèces',
                ]);
                
                Solde::create([
                    'agent_id' => $agent->id,
                    'operateur_id' => null,
                    'montant' => $request->espece_initiale,
                    'type' => 'espece',
                    'date' => now(),
                    'description' => "Transaction {$transactionEspece->reference} - {$typeApportEspece->libelle}",
                ]);
            }
            
            if ($typeApportVirtuel) {
                foreach ($operateurs as $operateur) {
                    $montantVirtuel = $request->input('montant_virtuel_' . $operateur->id, 0);
                    if ($montantVirtuel > 0) {
                        $transactionVirtuel = Transaction::create([
                            'agent_id' => $agent->id,
                            'type_operation_id' => $typeApportVirtuel->id,
                            'operateur_id' => $operateur->id,
                            'montant' => $montantVirtuel,
                            'type' => 'depot',
                            'statut' => 'valide',
                            'description' => 'Montant initial virtuel - ' . $operateur->libelle,
                        ]);
                        
                        Solde::create([
                            'agent_id' => $agent->id,
                            'operateur_id' => $operateur->id,
                            'montant' => $montantVirtuel,
                            'type' => 'virtuel',
                            'date' => now(),
                            'description' => "Transaction {$transactionVirtuel->reference} - {$typeApportVirtuel->libelle}",
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Agent créé avec succès' . ($kiosqueId ? ' et kiosque associé' : '') . '. Un utilisateur a été créé automatiquement.',
                'agent' => $agent->load('kiosque', 'utilisateur'),
                'utilisateur' => [
                    'email' => $utilisateur->email,
                    'mot_de_passe' => $motDePasse // À noter : ce mot de passe doit être communiqué à l'agent
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Erreur de validation dans AgentController@storeWithKiosque: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur dans AgentController@storeWithKiosque: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Toujours retourner du JSON même en cas d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    /**
     * Afficher un agent
     */
    public function show(Request $request, Agent $agent)
    {
        try {
            $agent->load([
                'kiosque',
                'utilisateur',
                'transactions' => function($q) {
                    $q->with('operateur')->latest()->limit(20);
                },
                'soldes' => function($q) use ($agent) {
                    $q->whereIn('id', function($subQuery) use ($agent) {
                        $subQuery->selectRaw('MAX(id)')
                            ->from('soldes')
                            ->where('agent_id', $agent->id)
                            ->groupBy(DB::raw('COALESCE(operateur_id, 0)'), 'type');
                    })->with('operateur');
                },
                'historiqueKiosques' => function($q) {
                    $q->with('kiosque')->orderBy('date_debut', 'desc');
                }
            ]);

            // Statistiques
            $stats = [
                'solde_total' => $agent->soldeTotal() ?? 0,
                'transactions_total' => $agent->transactions()->count(),
                'transactions_mois' => $agent->transactions()->duMois()->count(),
                'montant_mois' => $agent->transactions()->duMois()->where('statut', 'valide')->sum('montant') ?? 0,
                'commission_mois' => $agent->transactions()->duMois()->where('statut', 'valide')->sum('commission') ?? 0,
                'transactions_jour' => $agent->transactions()->duJour()->count(),
                'montant_jour' => $agent->transactions()->duJour()->where('statut', 'valide')->sum('montant') ?? 0,
            ];

            if ($request->ajax() || $request->wantsJson()) {
                // Ajouter l'URL complète de la photo si elle existe
                $agentData = $agent->toArray();
                if ($agent->utilisateur && $agent->utilisateur->photo_profil) {
                    $agentData['utilisateur']['photo_profil_url'] = asset('storage/' . $agent->utilisateur->photo_profil);
                }
                
                return response()->json([
                    'success' => true,
                    'agent' => $agentData,
                    'stats' => $stats
                ]);
            }

            // Pour les requêtes non-AJAX, rediriger vers la liste
            return redirect()->route('agents.liste-agents')
                ->with('info', 'Utilisez le bouton "Voir" pour afficher les détails dans un modal.');
        } catch (\Exception $e) {
            \Log::error('Erreur dans AgentController@show: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors du chargement des données: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Une erreur est survenue lors du chargement des données.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Request $request, Agent $agent)
    {
        try {
            $agent->load(['kiosque', 'utilisateur']);
            $kiosques = Kiosque::actif()->orderBy('nom')->get();
            $operateurs = Operateur::actif()->get();
            
            if ($request->ajax() || $request->wantsJson()) {
                // Ajouter l'URL complète de la photo si elle existe
                $agentData = $agent->toArray();
                if ($agent->utilisateur && $agent->utilisateur->photo_profil) {
                    $agentData['utilisateur']['photo_profil_url'] = asset('storage/' . $agent->utilisateur->photo_profil);
                }
                
                return response()->json([
                    'success' => true,
                    'agent' => $agentData,
                    'kiosques' => $kiosques,
                    'operateurs' => $operateurs
                ]);
            }

            $utilisateurs = Utilisateur::actif()
                ->where(function($q) use ($agent) {
                    $q->whereDoesntHave('agent')
                      ->orWhere('id', $agent->user_id);
                })
                ->orderBy('nom')
                ->get();

            return view('pages.agents.edit', compact('agent', 'kiosques', 'utilisateurs'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans AgentController@edit: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors du chargement des données: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Une erreur est survenue lors du chargement des données.');
        }
    }

    /**
     * Mettre à jour un agent
     */
    public function update(Request $request, Agent $agent)
    {
        try {
            $validated = $request->validate([
                'code_agent' => 'nullable|string|max:50|unique:agents,code_agent,' . $agent->id,
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'telephone' => 'required|string|max:20|unique:agents,telephone,' . $agent->id,
                'kiosque_id' => 'nullable|exists:kiosques,id',
                'user_id' => 'nullable|exists:utilisateurs,id',
                'statut' => 'required|in:actif,inactif,suspendu,en_attente',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Vérifier si le nouveau kiosque n'est pas saturé
            if ($request->filled('kiosque_id') && $request->kiosque_id != $agent->kiosque_id) {
                $kiosque = Kiosque::find($request->kiosque_id);
                if ($kiosque && $kiosque->estSature()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'errors' => ['kiosque_id' => ['Le kiosque sélectionné a atteint sa capacité maximale.']]
                        ], 422);
                    }
                    return back()->withErrors([
                        'kiosque_id' => 'Le kiosque sélectionné a atteint sa capacité maximale.'
                    ])->withInput();
                }
            }

            // Upload de la photo de profil si fournie
            if ($request->hasFile('photo') && $agent->utilisateur) {
                // Supprimer l'ancienne photo si elle existe
                if ($agent->utilisateur->photo_profil) {
                    \Storage::disk('public')->delete($agent->utilisateur->photo_profil);
                }
                $photoProfil = $request->file('photo')->store('photos/profils', 'public');
                $agent->utilisateur->update(['photo_profil' => $photoProfil]);
            }

            $agent->update($validated);

            if ($request->ajax()) {
                $agent->load('kiosque', 'utilisateur');
                $agentData = $agent->toArray();
                if ($agent->utilisateur && $agent->utilisateur->photo_profil) {
                    $agentData['utilisateur']['photo_profil_url'] = asset('storage/' . $agent->utilisateur->photo_profil);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Agent mis à jour avec succès !',
                    'agent' => $agentData
                ]);
            }

            return redirect()->route('agents.show', $agent)
                ->with('success', 'Agent mis à jour avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Erreur dans AgentController@update: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Supprimer un agent (soft delete)
     */
    public function destroy(Agent $agent)
    {
        // Vérifier s'il y a des transactions récentes
        $hasRecentTransactions = $agent->transactions()
            ->where('date', '>=', now()->subDays(30))
            ->exists();

        if ($hasRecentTransactions) {
            return redirect()->route('agents.index')
                ->with('error', 'Impossible de supprimer cet agent car il a des transactions récentes.');
        }

        $agent->delete();

        return redirect()->route('agents.index')
            ->with('success', 'Agent supprimé avec succès !');
    }

    /**
     * Mettre à jour le solde d'un agent
     */
    public function updateSolde(Request $request, Agent $agent)
    {
        $request->validate([
            'type' => 'required|in:espece,virtuel',
            'operateur_id' => 'required_if:type,virtuel|nullable|exists:operateurs,id',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        Solde::create([
            'agent_id' => $agent->id,
            'operateur_id' => $request->type === 'virtuel' ? $request->operateur_id : null,
            'montant' => $request->montant,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solde mis à jour avec succès !',
            'solde_total' => $agent->soldeTotal(),
        ]);
    }

    /**
     * Obtenir les soldes d'un agent (API)
     */
    public function getSoldes(Agent $agent)
    {
        $soldesActuels = $agent->soldesActuels();
        
        $soldes = $soldesActuels->map(function($solde) {
            return [
                'type' => $solde->type,
                'operateur' => $solde->operateur ? [
                    'id' => $solde->operateur->id,
                    'code' => $solde->operateur->code,
                    'libelle' => $solde->operateur->libelle,
                    'couleur' => $solde->operateur->couleur,
                ] : null,
                'montant' => $solde->montant,
                'date' => $solde->date->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'soldes' => $soldes,
            'total' => $agent->soldeTotal(),
        ]);
    }

    /**
     * Changer le statut d'un agent
     */
    public function changeStatut(Request $request, Agent $agent)
    {
        $request->validate([
            'statut' => 'required|in:actif,inactif,suspendu,en_attente',
        ]);

        $agent->update(['statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Statut modifié avec succès !',
            'statut' => $agent->statut,
        ]);
    }

    /**
     * Enregistrer les montants initiaux d'un agent comme des opérations en agence
     */
    public function storeMontantsInitiaux(Request $request, $agentId)
    {
        try {
            $agent = Agent::findOrFail($agentId);
            $operateurs = Operateur::actif()->get();
            
            // Validation
            $rules = [
                'espece_initiale' => 'nullable|numeric|min:0',
            ];
            foreach ($operateurs as $operateur) {
                $rules['montant_virtuel_' . $operateur->id] = 'nullable|numeric|min:0';
            }
            
            $validated = $request->validate($rules);
            
            DB::beginTransaction();
            
            // Récupérer les types d'opération
            $typeApportEspece = TypeOperation::where('code', 'apport_espece')->first();
            $typeApportVirtuel = TypeOperation::where('code', 'apport_virtuel')->first();
            
            if (!$typeApportEspece || !$typeApportVirtuel) {
                throw new \Exception('Types d\'opération non trouvés. Veuillez exécuter le seeder TypeOperationSeeder.');
            }
            
            // Créer l'opération pour l'espèce initiale
            if ($request->filled('espece_initiale') && $request->espece_initiale > 0) {
                $transactionEspece = Transaction::create([
                    'agent_id' => $agent->id,
                    'type_operation_id' => $typeApportEspece->id,
                    'operateur_id' => null,
                    'montant' => $request->espece_initiale,
                    'type' => 'depot',
                    'statut' => 'valide',
                    'description' => 'Montant initial en espèces',
                ]);
                
                // Créer le solde
                Solde::create([
                    'agent_id' => $agent->id,
                    'operateur_id' => null,
                    'montant' => $request->espece_initiale,
                    'type' => 'espece',
                    'date' => now(),
                    'description' => "Transaction {$transactionEspece->reference} - {$typeApportEspece->libelle}",
                ]);
            }
            
            // Créer les opérations pour les montants virtuels
            foreach ($operateurs as $operateur) {
                $montantVirtuel = $request->input('montant_virtuel_' . $operateur->id, 0);
                if ($montantVirtuel > 0) {
                    $transactionVirtuel = Transaction::create([
                        'agent_id' => $agent->id,
                        'type_operation_id' => $typeApportVirtuel->id,
                        'operateur_id' => $operateur->id,
                        'montant' => $montantVirtuel,
                        'type' => 'depot',
                        'statut' => 'valide',
                        'description' => 'Montant initial virtuel - ' . $operateur->libelle,
                    ]);
                    
                    // Créer le solde
                    Solde::create([
                        'agent_id' => $agent->id,
                        'operateur_id' => $operateur->id,
                        'montant' => $montantVirtuel,
                        'type' => 'virtuel',
                        'date' => now(),
                        'description' => "Transaction {$transactionVirtuel->reference} - {$typeApportVirtuel->libelle}",
                    ]);
                }
            }
            
            // Mettre à jour le montant initial total de l'agent
            $montantTotal = $request->espece_initiale ?? 0;
            foreach ($operateurs as $operateur) {
                $montantVirtuel = $request->input('montant_virtuel_' . $operateur->id, 0);
                if ($montantVirtuel > 0) {
                    $montantTotal += $montantVirtuel;
                }
            }
            $agent->update(['montant_initial_total' => $montantTotal]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Montants initiaux enregistrés avec succès comme opérations en agence.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l\'enregistrement des montants initiaux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporter la liste des agents
     */
    public function export(Request $request)
    {
        $query = Agent::with(['kiosque', 'utilisateur']);

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('kiosque_id')) {
            $query->where('kiosque_id', $request->kiosque_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('code_agent', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $agents = $query->orderBy('nom')->orderBy('prenom')->get();

        $headers = ['Code Agent', 'Nom', 'Prénom', 'Téléphone', 'Email', 'Kiosque', 'Statut', 'Montant Total Initial'];
        
        $data = $agents->map(function($agent) {
            return [
                $agent->code_agent ?? '-',
                $agent->nom ?? '-',
                $agent->prenom ?? '-',
                $agent->telephone ?? '-',
                ($agent->utilisateur && $agent->utilisateur->email) ? $agent->utilisateur->email : '-',
                ($agent->kiosque && $agent->kiosque->nom) ? $agent->kiosque->nom : 'Aucun kiosque',
                ucfirst($agent->statut ?? 'inactif'),
                number_format((float)($agent->montant_initial_total ?? 0), 0, ',', ' ') . ' XOF',
            ];
        })->toArray();

        $filename = 'agents_' . now()->format('Y-m-d_His') . '.pdf';

        return $this->exportToPdf('Liste des Agents', $headers, $data, $filename);
    }

    /**
     * Exporter les soldes des agents
     */
    public function exportSoldes(Request $request)
    {
        $query = Agent::with(['kiosque', 'utilisateur', 'soldes.operateur']);

        // Filtrer uniquement les agents avec soldes positifs
        if ($request->has('soldes_positifs') && $request->soldes_positifs == 1) {
            $query->whereHas('soldes', function($q) {
                $q->where('montant', '>', 0);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('code_agent', 'like', "%{$search}%");
            });
        }

        $agents = $query->actif()->orderBy('nom')->get();
        $operateurs = Operateur::actif()->get();

        // Préparer les données avec les soldes par opérateur
        $headers = ['Code Agent', 'Nom', 'Prénom', 'Kiosque', 'Statut', 'Montant Initial', 'Solde en Espèce'];
        foreach ($operateurs as $operateur) {
            $headers[] = 'Solde ' . $operateur->libelle;
        }
        $headers[] = 'Solde Total';

        $data = [];
        foreach ($agents as $agent) {
            // Récupérer le solde en espèce
            $soldeEspece = $agent->soldes->where('type', 'espece')->first();
            $montantEspece = $soldeEspece ? $soldeEspece->montant : 0;
            
            // Récupérer les soldes virtuels par opérateur
            $soldesVirtuels = $agent->soldes->where('type', 'virtuel');
            
            $row = [
                $agent->code_agent ?? '-',
                $agent->nom ?? '-',
                $agent->prenom ?? '-',
                ($agent->kiosque && $agent->kiosque->nom) ? $agent->kiosque->nom : 'Aucun kiosque',
                ucfirst($agent->statut ?? 'inactif'),
                number_format($agent->montant_initial_total ?? 0, 0, ',', ' ') . ' XOF',
                number_format($montantEspece, 0, ',', ' ') . ' XOF',
            ];

            $soldeTotalVirtuel = 0;
            foreach ($operateurs as $operateur) {
                $solde = $soldesVirtuels->where('operateur_id', $operateur->id)->first();
                $montant = $solde ? $solde->montant : 0;
                $soldeTotalVirtuel += $montant;
                $row[] = number_format($montant, 0, ',', ' ') . ' XOF';
            }
            // Solde total = espèce + total virtuel
            $soldeTotal = $montantEspece + $soldeTotalVirtuel;
            $row[] = number_format($soldeTotal, 0, ',', ' ') . ' XOF';
            
            $data[] = $row;
        }

        $filename = 'soldes_agents_' . now()->format('Y-m-d_His') . '.pdf';

        return $this->exportToPdf('Soldes des Agents', $headers, $data, $filename);
    }
}
