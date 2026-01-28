<?php

namespace App\Http\Controllers;

use App\Models\Kiosque;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class KiosqueController extends Controller
{
    /**
     * Afficher la liste des kiosques
     */
    public function index(Request $request)
    {
        $query = Kiosque::with(['agents' => function($q) {
            $q->where('statut', 'actif');
        }]);

        // Filtres
        if ($request->filled('ville')) {
            $query->where('ville', $request->ville);
        }

        if ($request->filled('quartier')) {
            $query->where('quartier', $request->quartier);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('quartier', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%");
            });
        }

        $kiosques = $query->orderBy('ville')->orderBy('quartier')->orderBy('nom')->paginate(20);

        // Listes pour les filtres
        $villes = Kiosque::distinct()->whereNotNull('ville')->pluck('ville')->filter();
        $quartiers = Kiosque::distinct()->whereNotNull('quartier')->pluck('quartier')->filter();

        return view('pages.kiosques.index', compact('kiosques', 'villes', 'quartiers'));
    }

    /**
     * Afficher la carte des kiosques
     */
    public function carte()
    {
        $kiosques = Kiosque::actif()
            ->avecCoordonnees()
            ->with(['agents' => function($q) {
                $q->where('statut', 'actif');
            }])
            ->get();

        return view('pages.kiosques.carte', compact('kiosques'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Générer le prochain code
        $lastCode = Kiosque::where('code', 'like', 'K%')
            ->orderBy('code', 'desc')
            ->first();
        
        $nextNumber = $lastCode ? intval(substr($lastCode->code, 1)) + 1 : 1;
        $suggestedCode = 'K' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('pages.kiosques.create', compact('suggestedCode'));
    }

    /**
     * Enregistrer un nouveau kiosque
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:kiosques,code',
            'nom' => 'required|string|max:150',
            'adresse' => 'nullable|string',
            'quartier' => 'nullable|string|max:100',
            'ville' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'telephone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required|in:fixe,mobile',
            'statut' => 'required|in:actif,inactif,en_travaux',
            'capacite_agents' => 'required|integer|min:1|max:20',
            'horaire_ouverture' => 'nullable|date_format:H:i',
            'horaire_fermeture' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
        ]);

        // Upload de la photo
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos/kiosques', 'public');
            $validated['photo'] = $photoPath;
        }

        $kiosque = Kiosque::create($validated);

        return redirect()->route('kiosques.index')
            ->with('success', 'Kiosque créé avec succès !');
    }

    /**
     * Afficher un kiosque
     */
    public function show(Kiosque $kiosque)
    {
        $kiosque->load(['agents' => function($q) {
            $q->with('utilisateur', 'soldes');
        }]);

        // Statistiques
        $stats = [
            'agents_actifs' => $kiosque->agentsActifs()->count(),
            'places_disponibles' => $kiosque->placesDisponibles(),
            'est_sature' => $kiosque->estSature(),
            'transactions_mois' => DB::table('transactions')
                ->join('agents', 'transactions.agent_id', '=', 'agents.id')
                ->where('agents.kiosque_id', $kiosque->id)
                ->where('transactions.statut', 'valide')
                ->whereMonth('transactions.date', now()->month)
                ->count(),
            'montant_mois' => DB::table('transactions')
                ->join('agents', 'transactions.agent_id', '=', 'agents.id')
                ->where('agents.kiosque_id', $kiosque->id)
                ->where('transactions.statut', 'valide')
                ->whereMonth('transactions.date', now()->month)
                ->sum('transactions.montant'),
        ];

        return view('pages.kiosques.show', compact('kiosque', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Kiosque $kiosque)
    {
        return view('pages.kiosques.edit', compact('kiosque'));
    }

    /**
     * Mettre à jour un kiosque
     */
    public function update(Request $request, Kiosque $kiosque)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:kiosques,code,' . $kiosque->id,
            'nom' => 'required|string|max:150',
            'adresse' => 'nullable|string',
            'quartier' => 'nullable|string|max:100',
            'ville' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'telephone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required|in:fixe,mobile',
            'statut' => 'required|in:actif,inactif,en_travaux',
            'capacite_agents' => 'required|integer|min:1|max:20',
            'horaire_ouverture' => 'nullable|date_format:H:i',
            'horaire_fermeture' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
        ]);

        // Upload de la nouvelle photo
        if ($request->hasFile('photo')) {
            if ($kiosque->photo) {
                Storage::disk('public')->delete($kiosque->photo);
            }
            $photoPath = $request->file('photo')->store('photos/kiosques', 'public');
            $validated['photo'] = $photoPath;
        }

        $kiosque->update($validated);

        return redirect()->route('kiosques.show', $kiosque)
            ->with('success', 'Kiosque mis à jour avec succès !');
    }

    /**
     * Supprimer un kiosque (soft delete)
     */
    public function destroy(Kiosque $kiosque)
    {
        // Vérifier s'il y a des agents actifs
        if ($kiosque->agentsActifs()->exists()) {
            return redirect()->route('kiosques.index')
                ->with('error', 'Impossible de supprimer ce kiosque car il a des agents actifs assignés.');
        }

        $kiosque->delete();

        return redirect()->route('kiosques.index')
            ->with('success', 'Kiosque supprimé avec succès !');
    }

    /**
     * Trouver les kiosques à proximité (API)
     */
    public function proximite(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'rayon' => 'nullable|numeric|min:0.1|max:100', // en km
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $rayon = $request->rayon ?? 10; // 10 km par défaut

        $kiosques = Kiosque::selectRaw("
            *,
            (6371 * ACOS(
                COS(RADIANS(?)) * COS(RADIANS(latitude)) *
                COS(RADIANS(longitude) - RADIANS(?)) +
                SIN(RADIANS(?)) * SIN(RADIANS(latitude))
            )) AS distance_km
        ", [$latitude, $longitude, $latitude])
            ->actif()
            ->avecCoordonnees()
            ->having('distance_km', '<=', $rayon)
            ->orderBy('distance_km')
            ->with(['agentsActifs'])
            ->get();

        return response()->json($kiosques);
    }

    /**
     * Obtenir les données pour la carte (API)
     */
    public function carteData()
    {
        $kiosques = Kiosque::avecCoordonnees()
            ->with(['agentsActifs'])
            ->get()
            ->map(function($kiosque) {
                return [
                    'id' => $kiosque->id,
                    'nom' => $kiosque->nom,
                    'code' => $kiosque->code,
                    'quartier' => $kiosque->quartier,
                    'ville' => $kiosque->ville,
                    'latitude' => (float) $kiosque->latitude,
                    'longitude' => (float) $kiosque->longitude,
                    'telephone' => $kiosque->telephone,
                    'type' => $kiosque->type,
                    'statut' => $kiosque->statut,
                    'agents_count' => $kiosque->agentsActifs->count(),
                    'capacite' => $kiosque->capacite_agents,
                    'places_disponibles' => $kiosque->placesDisponibles(),
                    'est_sature' => $kiosque->estSature(),
                ];
            });

        return response()->json($kiosques);
    }

    /**
     * Assigner un agent à un kiosque
     */
    public function assignerAgent(Request $request, Kiosque $kiosque)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
        ]);

        // Vérifier si le kiosque n'est pas saturé
        if ($kiosque->estSature()) {
            return response()->json([
                'success' => false,
                'message' => 'Le kiosque a atteint sa capacité maximale.'
            ], 400);
        }

        $agent = Agent::find($request->agent_id);
        $agent->update(['kiosque_id' => $kiosque->id]);

        return response()->json([
            'success' => true,
            'message' => 'Agent assigné avec succès !',
            'places_disponibles' => $kiosque->placesDisponibles(),
        ]);
    }

    /**
     * Retirer un agent d'un kiosque
     */
    public function retirerAgent(Kiosque $kiosque, Agent $agent)
    {
        if ($agent->kiosque_id !== $kiosque->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet agent n\'est pas assigné à ce kiosque.'
            ], 400);
        }

        $agent->update(['kiosque_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Agent retiré avec succès !',
            'places_disponibles' => $kiosque->placesDisponibles(),
        ]);
    }
}
