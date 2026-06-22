<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Operateur;
use App\Models\Transaction;
use App\Models\TypeOperation;
use App\Models\Solde;
use App\Support\ExportSelection;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsAgenceController extends Controller
{
    use Exportable;

    /**
     * Afficher la page des opérations en agence avec les vraies données.
     * Types d'opération et opérateurs sont chargés dynamiquement depuis la base.
     */
    public function index(Request $request)
    {
        // Transactions les plus récentes avec les relations nécessaires
        $transactions = Transaction::operationAgence()->with(['agent.utilisateur', 'operateur', 'typeOperation'])
            ->latest('date')
            ->paginate(20);

        // Types d'opération (versements, ajouts espèces, etc.) — dynamique depuis la base
        $typesOperation = TypeOperation::actif()->get();

        // Opérateurs (T-Money, Flooz, etc.) — dynamique depuis la base
        $operateurs = Operateur::actif()->get();
        $operateursJson = $operateurs->map(function ($o) {
            return [
                'id' => $o->id,
                'libelle' => $o->libelle,
                'logo' => $o->logo ? asset('storage/' . $o->logo) : null,
                'couleur' => $o->couleur,
            ];
        })->values()->toArray();

        // Agents pour la recherche dans le modal (format pour le JSON en vue)
        $agents = Agent::with('utilisateur')->whereHas('utilisateur')->get();
        $agentsJson = $agents->map(function ($a) {
            $p = $a->utilisateur->prenom ?? '';
            $n = $a->utilisateur->nom ?? '';
            return [
                'id' => $a->id,
                'nom' => $n,
                'prenom' => $p,
                'libelle' => trim($p . ' ' . $n),
            ];
        })->values()->toArray();

        return $this->ajaxView('pages.operation_agence.index', compact('transactions', 'typesOperation', 'operateurs', 'operateursJson', 'agents', 'agentsJson'));
    }

    /**
     * Enregistrer une nouvelle opération en agence (transaction).
     */
    public function store(Request $request)
    {
        $typeOperation = TypeOperation::find($request->input('type_operation_id'));
        $requiertOperateur = $typeOperation && $typeOperation->requiert_operateur;

        $rules = [
            'agent_id' => 'required|exists:agents,id',
            'type_operation_id' => 'required|exists:type_operations,id',
            'montant' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
            'operateur_id' => 'nullable|exists:operateurs,id',
        ];
        if ($requiertOperateur) {
            $rules['operateur_id'] = 'required|exists:operateurs,id';
        }

        $validated = $request->validate($rules);

        $typeOp = TypeOperation::find($validated['type_operation_id']);
        $code = $typeOp ? strtolower($typeOp->code ?? '') : '';
        if (str_contains($code, 'apport')) {
            $type = 'depot';
        } elseif (str_contains($code, 'retrait')) {
            $type = 'retrait';
        } else {
            $type = 'paiement';
        }

        // Utiliser une transaction DB pour garantir la cohérence
        DB::beginTransaction();
        
        try {
            // Créer la transaction
            $transaction = Transaction::create([
                'agent_id' => $validated['agent_id'],
                'type_operation_id' => $validated['type_operation_id'],
                'operateur_id' => $validated['operateur_id'] ?? null,
                'montant' => $validated['montant'],
                'type' => $type,
                'statut' => 'valide',
                'description' => $validated['note'] ?? null,
            ]);

            // Mettre à jour le solde de l'agent
            $agent = Agent::findOrFail($validated['agent_id']);
            $operateurId = $validated['operateur_id'] ?? null;
            
            // Déterminer le type de solde (espèce ou virtuel)
            $typeSolde = $operateurId ? 'virtuel' : 'espece';
            
            // Récupérer le dernier solde pour cet agent et cet opérateur (ou espèce)
            $dernierSolde = Solde::where('agent_id', $agent->id)
                ->where('type', $typeSolde)
                ->when($operateurId, function($q) use ($operateurId) {
                    $q->where('operateur_id', $operateurId);
                }, function($q) {
                    $q->whereNull('operateur_id');
                })
                ->latest('date')
                ->latest('id')
                ->first();
            
            $ancienMontant = $dernierSolde ? $dernierSolde->montant : 0;
            
            // Calculer le nouveau montant selon le type d'opération
            if ($type === 'depot') {
                // Dépôt/Apport : augmente le solde
                $nouveauMontant = $ancienMontant + $validated['montant'];
            } elseif ($type === 'retrait') {
                // Retrait : diminue le solde
                $nouveauMontant = $ancienMontant - $validated['montant'];
            } else {
                // Paiement : diminue le solde (comme un retrait)
                $nouveauMontant = $ancienMontant - $validated['montant'];
            }
            
            // Créer le nouvel enregistrement de solde
            Solde::create([
                'agent_id' => $agent->id,
                'operateur_id' => $operateurId,
                'montant' => $nouveauMontant,
                'type' => $typeSolde,
                'date' => now(),
                'description' => "Transaction {$transaction->reference} - {$typeOp->libelle}",
            ]);
            
            DB::commit();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Opération enregistrée avec succès et solde mis à jour.',
                    'transaction_id' => $transaction->id,
                    'nouveau_solde' => number_format($nouveauMontant, 0, ',', ' ') . ' FCFA',
                ], 201);
            }

            return redirect()->route('operations-agence.index')
                ->with('success', 'Opération enregistrée avec succès et solde mis à jour.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l\'enregistrement de l\'opération: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    /**
     * Exporter les opérations en agence (PDF ou Excel).
     */
    public function export(Request $request)
    {
        $query = Transaction::operationAgence()
            ->with(['agent.utilisateur', 'operateur', 'typeOperation']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($aq) use ($search) {
                        $aq->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('code_agent', 'like', "%{$search}%")
                            ->orWhere('telephone', 'like', "%{$search}%");
                    });
            });
        }

        ExportSelection::apply($query, $request);

        $transactions = $query->latest('date')->get();

        $headers = [
            'Référence',
            'Date',
            'Opération',
            'Type',
            'Agent',
            'Téléphone',
            'Opérateur',
            'Montant (XOF)',
            'Statut',
            'Note',
        ];

        $data = $transactions->map(function (Transaction $transaction) {
            $agent = $transaction->agent;

            return [
                $transaction->reference ?? '-',
                $transaction->date ? $transaction->date->format('d/m/Y H:i') : '-',
                $transaction->typeOperation?->libelle ?? '-',
                ucfirst($transaction->type ?? '-'),
                $agent ? trim(($agent->prenom ?? '').' '.($agent->nom ?? '')) : '-',
                $agent?->telephone ?? '-',
                $transaction->operateur?->libelle ?? '-',
                number_format((float) ($transaction->montant ?? 0), 0, ',', ' ').' XOF',
                ucfirst(str_replace('_', ' ', $transaction->statut ?? '-')),
                $transaction->description ?? '-',
            ];
        })->toArray();

        $filename = 'operations_agence_'.now()->format('Y-m-d_His');
        $filters = $this->buildExportFilters($request);

        if ($this->wantsExcelExport($request)) {
            return $this->exportToExcel(
                $headers,
                $data,
                $this->excelFilename($filename),
                'Opérations en agence',
                'Historique des opérations effectuées en agence',
                $filters,
            );
        }

        return $this->exportToPdf(
            'Opérations en agence',
            $headers,
            $data,
            $filename.'.pdf',
            'landscape',
            $request,
            [
                'subtitle' => 'Historique des opérations effectuées en agence',
                'filtersText' => $filters,
            ],
        );
    }

    private function buildExportFilters(Request $request): ?string
    {
        $parts = [];
        if ($request->filled('statut')) {
            $parts[] = 'Statut : '.ucfirst($request->statut);
        }
        if ($request->filled('type')) {
            $parts[] = 'Type : '.ucfirst($request->type);
        }
        if ($request->filled('search')) {
            $parts[] = 'Recherche : '.$request->search;
        }
        if (ExportSelection::ids($request) !== []) {
            $parts[] = 'Sélection : '.count(ExportSelection::ids($request)).' élément(s)';
        }

        return $parts ? implode(' · ', $parts) : null;
    }
}

