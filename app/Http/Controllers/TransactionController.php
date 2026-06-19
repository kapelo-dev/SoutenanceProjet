<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Transaction;
use App\Support\AgentPhoneResolver;
use App\Models\Operateur;
use App\Models\Solde;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use Exportable;
    /**
     * Afficher la liste des transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::commerciale()->with(['agent.utilisateur', 'operateur']);

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

        return $this->ajaxView('pages.transactions.index', compact('transactions', 'operateurs', 'agents', 'stats'));
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
     * Créer une transaction depuis l'application Android (SMS).
     * Authentification : Bearer token (config sms_api.token).
     */
    public function storeFromSms(Request $request)
    {
        \Log::info('[SMS-API] Appel reçu', [
            'ip' => $request->ip(),
            'montant' => $request->input('montant'),
            'type' => $request->input('type'),
            'reference' => $request->input('reference'),
            'operator_code' => $request->input('operator_code'),
            'agent_code' => $request->input('agent_code'),
            'agent_telephone' => $request->input('agent_telephone'),
            'agent_id' => $request->input('agent_id'),
        ]);

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'type' => 'required|in:depot,retrait,transfert,paiement',
            'description' => 'nullable|string|max:500',
            'client_nom' => 'nullable|string|max:100',
            'client_telephone' => 'nullable|string|max:20',
            'reference' => 'nullable|string|max:50',
            'operator_txn_id' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:20',
            'raw_sms' => 'nullable|string|max:1000',
            'agent_id' => 'nullable|exists:agents,id',
            'agent_code' => 'nullable|string|max:50',
            'agent_telephone' => 'nullable|string|max:20',
            'operateur_id' => 'nullable|exists:operateurs,id',
            'operator_code' => 'nullable|string|max:50',
            'commission' => 'nullable|numeric|min:0',
            'virtual_balance_after' => 'nullable|numeric|min:0',
        ]);

        $agent = null;
        if (!empty($validated['agent_id'])) {
            $agent = Agent::find($validated['agent_id']);
        }
        if (!$agent && !empty($validated['agent_code'])) {
            $agent = Agent::where('code_agent', $validated['agent_code'])->first();
        }
        if (!$agent && !empty($validated['agent_telephone'])) {
            $agent = AgentPhoneResolver::resolve($validated['agent_telephone']);
        }
        if (!$agent) {
            $agent = Agent::find(config('sms_api.default_agent_id'));
        }

        $operateur = null;
        if (!empty($validated['operateur_id'])) {
            $operateur = Operateur::find($validated['operateur_id']);
        }
        if (!$operateur && !empty($validated['operator_code'])) {
            $code = trim($validated['operator_code']);
            $operateur = Operateur::where('code', $code)
                ->orWhere('libelle', 'like', '%' . $code . '%')
                ->first();
        }
        if (!$operateur) {
            $operateur = Operateur::find(config('sms_api.default_operateur_id'));
        }

        if (!$agent || !$operateur) {
            \Log::warning('[SMS-API] Agent ou opérateur introuvable', [
                'agent_id' => $validated['agent_id'] ?? null,
                'agent_code' => $validated['agent_code'] ?? null,
                'agent_telephone' => $validated['agent_telephone'] ?? null,
                'operator_code' => $validated['operator_code'] ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Agent ou opérateur introuvable (vérifiez le téléphone agent/SIM, agent_code, operator_code ou config sms_api).',
            ], 422);
        }

        $data = [
            'montant' => $validated['montant'],
            'type' => $validated['type'],
            'operateur_id' => $operateur->id,
            'agent_id' => $agent->id,
            'statut' => 'valide',
            'description' => $validated['description'] ?? ($validated['raw_sms'] ?? null),
            'client_nom' => $validated['client_nom'] ?? null,
            'client_telephone' => $validated['client_telephone'] ?? null,
            'operator_txn_id' => $validated['operator_txn_id'] ?? null,
            'commission' => $validated['commission'] ?? null,
            'virtual_balance_after' => $validated['virtual_balance_after'] ?? null,
        ];
        if (!empty($validated['reference'])) {
            $data['reference'] = $validated['reference'];
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create($data);
            if ($transaction->statut === 'valide') {
                $this->updateAgentBalance($transaction);
            }
            DB::commit();

            \Log::info('[SMS-API] Transaction créée', ['id' => $transaction->id, 'reference' => $transaction->reference]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction enregistrée.',
                'transaction_id' => $transaction->id,
                'transaction' => [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'montant' => (float) $transaction->montant,
                    'type' => $transaction->type,
                    'statut' => $transaction->statut,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[SMS-API] Erreur enregistrement', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage(),
            ], 500);
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

        $user = auth()->user();
        if ($user?->isAgent()) {
            $agent = $user->agent;
            if (! $agent || $transaction->agent_id !== $agent->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez annuler que vos propres transactions.',
                ], 403);
            }

            if (! \App\Http\Controllers\Api\MobileAgentController::canAgentCancel($transaction)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Annulation impossible : la transaction date de plus de 24 heures.',
                ], 422);
            }
        }

        $request->validate([
            'raison' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Inverser les soldes avant d'annuler
            $this->reverseAgentBalance($transaction);
            
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
                'message' => 'Transaction annulée avec succès ! Les soldes ont été mis à jour.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques des transactions (API)
     */
    public function statistiques(Request $request)
    {
        $periode = $request->get('periode', 'jour'); // jour, semaine, mois, annee

        $query = Transaction::commerciale()->valide();

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
     * Exporter les transactions (Excel ou PDF)
     */
    public function export(Request $request)
    {
        $query = Transaction::commerciale()->with(['agent', 'operateur']);

        // Appliquer les mêmes filtres que l'index
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

        $transactions = $query->latest('date')->get();

        $headers = ['Référence', 'Date', 'Type', 'Montant (XOF)', 'Opérateur', 'Agent', 'Client', 'Téléphone Client', 'Commission (XOF)', 'Statut'];
        
        $data = $transactions->map(function($transaction) {
            // Préparer le logo de l'opérateur pour le PDF
            $operateurCell = '-';
            if ($transaction->operateur) {
                $operateurLibelle = $transaction->operateur->libelle ?? '-';
                $operateurLogo = null;
                
                if ($transaction->operateur->logo) {
                    // Le logo est stocké dans storage/app/public/
                    $logoPath = storage_path('app/public/' . $transaction->operateur->logo);
                    if (file_exists($logoPath)) {
                        // Convertir l'image en base64 pour le PDF
                        $imageData = file_get_contents($logoPath);
                        $imageInfo = getimagesize($logoPath);
                        $mimeType = $imageInfo['mime'] ?? 'image/png';
                        $base64 = base64_encode($imageData);
                        
                        $operateurLogo = [
                            'base64' => 'data:' . $mimeType . ';base64,' . $base64,
                            'libelle' => $operateurLibelle,
                        ];
                    }
                }
                
                // Si pas de logo, utiliser la couleur avec les initiales
                if (!$operateurLogo && $transaction->operateur->couleur) {
                    $operateurLogo = [
                        'couleur' => $transaction->operateur->couleur,
                        'code' => strtoupper(substr($transaction->operateur->code ?? 'OP', 0, 2)),
                        'libelle' => $operateurLibelle,
                    ];
                }
                
                $operateurCell = [
                    'libelle' => $operateurLibelle,
                    'logo' => $operateurLogo,
                ];
            }
            
            return [
                $transaction->reference ?? '-',
                $transaction->date ? $transaction->date->format('d/m/Y H:i') : '-',
                ucfirst($transaction->type ?? '-'),
                number_format($transaction->montant ?? 0, 0, ',', ' ') . ' XOF',
                $operateurCell,
                ($transaction->agent) ? (($transaction->agent->prenom ?? '') . ' ' . ($transaction->agent->nom ?? '')) : '-',
                $transaction->client_nom ?? '-',
                $transaction->client_telephone ?? '-',
                number_format($transaction->commission ?? 0, 0, ',', ' ') . ' XOF',
                ucfirst($transaction->statut ?? '-'),
            ];
        })->toArray();

        $filename = 'transactions_' . now()->format('Y-m-d_His');

        if ($this->wantsExcelExport($request)) {
            return $this->exportToExcel($headers, $data, $this->excelFilename($filename), 'Liste des Transactions', 'Historique des opérations Mobile Money', $this->buildTransactionExportFilters($request));
        }

        return $this->exportToPdf('Liste des Transactions', $headers, $data, $filename . '.pdf', 'portrait', $request, [
            'subtitle' => 'Historique des opérations Mobile Money',
            'filtersText' => $this->buildTransactionExportFilters($request),
        ]);
    }

    private function buildTransactionExportFilters(Request $request): ?string
    {
        $parts = [];
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $parts[] = 'Période : ' . ($request->date_debut ?? '…') . ' — ' . ($request->date_fin ?? '…');
        }
        if ($request->filled('statut')) {
            $parts[] = 'Statut : ' . ucfirst($request->statut);
        }
        if ($request->filled('type')) {
            $parts[] = 'Type : ' . ucfirst($request->type);
        }
        if ($request->filled('search')) {
            $parts[] = 'Recherche : ' . $request->search;
        }

        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * Méthode privée pour inverser le solde de l'agent lors de l'annulation d'une transaction.
     */
    private function reverseAgentBalance(Transaction $transaction)
    {
        $agent = $transaction->agent;
        $operateur = $transaction->operateur;
        $montant = (float) $transaction->montant;

        // --- Inverser le solde virtuel (solde opérateur) ---
        $dernierVirtuel = Solde::where('agent_id', $agent->id)
            ->where('operateur_id', $operateur->id)
            ->where('type', 'virtuel')
            ->latest('date')
            ->first();
        $ancienVirtuel = $dernierVirtuel ? (float) $dernierVirtuel->montant : 0;
        $nouveauVirtuel = max(0, $ancienVirtuel - $montant);

        Solde::create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'montant' => $nouveauVirtuel,
            'type' => 'virtuel',
            'description' => "Annulation transaction {$transaction->reference}",
        ]);

        // --- Inverser le solde espèce (caisse agent) ---
        if (in_array($transaction->type, ['depot', 'retrait'])) {
            $dernierEspece = Solde::where('agent_id', $agent->id)
                ->whereNull('operateur_id')
                ->where('type', 'espece')
                ->latest('date')
                ->first();
            $ancienEspece = $dernierEspece ? (float) $dernierEspece->montant : 0;
            $nouveauEspece = $ancienEspece + $montant;

            Solde::create([
                'agent_id' => $agent->id,
                'operateur_id' => null,
                'montant' => $nouveauEspece,
                'type' => 'espece',
                'description' => "Annulation transaction {$transaction->reference} ({$transaction->type})",
            ]);
        }
    }

    /**
     * Méthode privée pour mettre à jour le solde de l'agent (espèce + virtuel).
     * Retrait : espèce diminue (agent donne du cash), virtuel augmente (opérateur crédite).
     * Dépôt : espèce diminue (agent envoie au réseau), virtuel augmente (solde opérateur).
     */
    private function updateAgentBalance(Transaction $transaction)
    {
        $agent = $transaction->agent;
        $operateur = $transaction->operateur;
        $montant = (float) $transaction->montant;

        // --- Virtuel (solde opérateur) ---
        $dernierVirtuel = Solde::where('agent_id', $agent->id)
            ->where('operateur_id', $operateur->id)
            ->where('type', 'virtuel')
            ->latest('date')
            ->first();
        $ancienVirtuel = $dernierVirtuel ? (float) $dernierVirtuel->montant : 0;

        if ($transaction->virtual_balance_after !== null && $transaction->virtual_balance_after > 0) {
            $nouveauVirtuel = (float) $transaction->virtual_balance_after;
        } else {
            $nouveauVirtuel = $ancienVirtuel + $montant;
        }

        Solde::create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'montant' => $nouveauVirtuel,
            'type' => 'virtuel',
            'description' => "Transaction {$transaction->reference}",
        ]);
        $transaction->update(['virtual_balance_after' => $nouveauVirtuel]);

        // --- Espèce (caisse agent) : retrait et dépôt → espèce diminue ---
        $dernierEspece = Solde::where('agent_id', $agent->id)
            ->whereNull('operateur_id')
            ->where('type', 'espece')
            ->latest('date')
            ->first();
        $ancienEspece = $dernierEspece ? (float) $dernierEspece->montant : 0;

        if (in_array($transaction->type, ['depot', 'retrait'])) {
            $nouveauEspece = max(0, $ancienEspece - $montant);
            Solde::create([
                'agent_id' => $agent->id,
                'operateur_id' => null,
                'montant' => $nouveauEspece,
                'type' => 'espece',
                'description' => "Transaction {$transaction->reference} ({$transaction->type})",
            ]);
        }
    }
}
