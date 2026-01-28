<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Solde;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Afficher la liste des transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['agent', 'operateur']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('operateur_id')) {
            $query->where('operateur_id', $request->operateur_id);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date', '<=', $request->date_fin);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('client_nom', 'like', "%{$search}%")
                  ->orWhere('client_telephone', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('date')->paginate(20);
        
        $operateurs = Operateur::actif()->get();
        $agents = Agent::actif()->orderBy('nom')->get();

        // Statistiques pour la période affichée
        $stats = [
            'total' => $query->valide()->sum('montant'),
            'count' => $query->valide()->count(),
            'commission' => $query->valide()->sum('commission'),
        ];

        return view('pages.transactions.index', compact('transactions', 'operateurs', 'agents', 'stats'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $operateurs = Operateur::actif()->get();
        $agents = Agent::actif()->with('kiosque')->orderBy('nom')->get();

        return view('pages.transactions.create', compact('operateurs', 'agents'));
    }

    /**
     * Enregistrer une nouvelle transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'type' => 'required|in:depot,retrait,transfert,paiement',
            'operateur_id' => 'required|exists:operateurs,id',
            'agent_id' => 'required|exists:agents,id',
            'statut' => 'required|in:valide,en_attente,annule,echoue',
            'description' => 'nullable|string|max:500',
            'commission' => 'nullable|numeric|min:0',
            'client_nom' => 'nullable|string|max:100',
            'client_telephone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Créer la transaction
            $transaction = Transaction::create($validated);

            // Mettre à jour le solde de l'agent si la transaction est validée
            if ($transaction->statut === 'valide') {
                $this->updateAgentBalance($transaction);
            }

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction créée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création de la transaction.'])->withInput();
        }
    }

    /**
     * Afficher une transaction
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['agent.kiosque', 'operateur', 'audits.utilisateur']);

        return view('pages.transactions.show', compact('transaction'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Transaction $transaction)
    {
        // Seules les transactions en attente peuvent être éditées
        if ($transaction->statut === 'valide') {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Les transactions validées ne peuvent pas être modifiées.');
        }

        $operateurs = Operateur::actif()->get();
        $agents = Agent::actif()->with('kiosque')->orderBy('nom')->get();

        return view('pages.transactions.edit', compact('transaction', 'operateurs', 'agents'));
    }

    /**
     * Mettre à jour une transaction
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Vérifier le statut
        if ($transaction->statut === 'valide') {
            return back()->with('error', 'Les transactions validées ne peuvent pas être modifiées.');
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'type' => 'required|in:depot,retrait,transfert,paiement',
            'operateur_id' => 'required|exists:operateurs,id',
            'agent_id' => 'required|exists:agents,id',
            'statut' => 'required|in:valide,en_attente,annule,echoue',
            'description' => 'nullable|string|max:500',
            'commission' => 'nullable|numeric|min:0',
            'client_nom' => 'nullable|string|max:100',
            'client_telephone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $oldStatut = $transaction->statut;
            $transaction->update($validated);

            // Si le statut passe à "validé", mettre à jour le solde
            if ($transaction->statut === 'valide' && $oldStatut !== 'valide') {
                $this->updateAgentBalance($transaction);
            }

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction mise à jour avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour.'])->withInput();
        }
    }

    /**
     * Annuler une transaction
     */
    public function annuler(Request $request, Transaction $transaction)
    {
        if ($transaction->statut === 'annule') {
            return response()->json([
                'success' => false,
                'message' => 'Cette transaction est déjà annulée.'
            ], 400);
        }

        $request->validate([
            'raison' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $transaction->update(['statut' => 'annule']);

            // Créer un audit
            $transaction->audits()->create([
                'ancien_montant' => $transaction->montant,
                'nouveau_montant' => 0,
                'operateur_id' => $transaction->operateur_id,
                'user_id' => auth()->id(),
                'raison' => $request->raison,
                'type_modification' => 'annulation',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction annulée avec succès !'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation.'
            ], 500);
        }
    }

    /**
     * Statistiques des transactions (API)
     */
    public function statistiques(Request $request)
    {
        $periode = $request->get('periode', 'jour'); // jour, semaine, mois, annee

        $query = Transaction::valide();

        switch ($periode) {
            case 'jour':
                $query->duJour();
                break;
            case 'semaine':
                $query->where('date', '>=', now()->startOfWeek());
                break;
            case 'mois':
                $query->duMois();
                break;
            case 'annee':
                $query->whereYear('date', now()->year);
                break;
        }

        $stats = [
            'total_transactions' => $query->count(),
            'montant_total' => $query->sum('montant'),
            'commission_total' => $query->sum('commission'),
            'par_type' => [
                'depot' => [
                    'count' => (clone $query)->depot()->count(),
                    'montant' => (clone $query)->depot()->sum('montant'),
                ],
                'retrait' => [
                    'count' => (clone $query)->retrait()->count(),
                    'montant' => (clone $query)->retrait()->sum('montant'),
                ],
                'transfert' => [
                    'count' => (clone $query)->where('type', 'transfert')->count(),
                    'montant' => (clone $query)->where('type', 'transfert')->sum('montant'),
                ],
                'paiement' => [
                    'count' => (clone $query)->where('type', 'paiement')->count(),
                    'montant' => (clone $query)->where('type', 'paiement')->sum('montant'),
                ],
            ],
            'par_operateur' => Operateur::actif()->get()->map(function($operateur) use ($query) {
                $opQuery = clone $query;
                return [
                    'operateur' => $operateur->only(['id', 'code', 'libelle', 'couleur']),
                    'count' => $opQuery->where('operateur_id', $operateur->id)->count(),
                    'montant' => $opQuery->where('operateur_id', $operateur->id)->sum('montant'),
                ];
            }),
        ];

        return response()->json($stats);
    }

    /**
     * Exporter les transactions (CSV)
     */
    public function export(Request $request)
    {
        $query = Transaction::with(['agent', 'operateur']);

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('operateur_id')) $query->where('operateur_id', $request->operateur_id);
        if ($request->filled('date_debut')) $query->whereDate('date', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->whereDate('date', '<=', $request->date_fin);

        $transactions = $query->latest('date')->get();

        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Référence', 'Date', 'Type', 'Montant', 'Opérateur', 
                'Agent', 'Client', 'Téléphone Client', 'Commission', 'Statut'
            ]);

            // Données
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->reference,
                    $transaction->date->format('Y-m-d H:i:s'),
                    $transaction->type,
                    $transaction->montant,
                    $transaction->operateur->libelle,
                    $transaction->agent->nomComplet,
                    $transaction->client_nom,
                    $transaction->client_telephone,
                    $transaction->commission,
                    $transaction->statut,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Méthode privée pour mettre à jour le solde de l'agent
     */
    private function updateAgentBalance(Transaction $transaction)
    {
        $agent = $transaction->agent;
        $operateur = $transaction->operateur;

        // Récupérer le dernier solde virtuel pour cet opérateur
        $dernierSolde = Solde::where('agent_id', $agent->id)
            ->where('operateur_id', $operateur->id)
            ->where('type', 'virtuel')
            ->latest('date')
            ->first();

        $ancienMontant = $dernierSolde ? $dernierSolde->montant : 0;

        // Calculer le nouveau montant selon le type de transaction
        switch ($transaction->type) {
            case 'depot':
                $nouveauMontant = $ancienMontant + $transaction->montant;
                break;
            case 'retrait':
                $nouveauMontant = $ancienMontant - $transaction->montant;
                break;
            case 'transfert':
            case 'paiement':
                $nouveauMontant = $ancienMontant - $transaction->montant;
                break;
            default:
                $nouveauMontant = $ancienMontant;
        }

        // Créer un nouveau solde
        Solde::create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'montant' => $nouveauMontant,
            'type' => 'virtuel',
            'description' => "Transaction {$transaction->reference}",
        ]);

        // Mettre à jour le champ virtual_balance_after
        $transaction->update(['virtual_balance_after' => $nouveauMontant]);
    }
}
