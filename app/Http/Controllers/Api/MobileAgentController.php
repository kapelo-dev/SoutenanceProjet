<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Transaction;
use App\Support\AuthIdentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileAgentController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string|max:100',
            'password' => 'required|string',
        ]);

        $utilisateur = AuthIdentifier::resolveUtilisateur(trim($request->identifiant));

        if (! $utilisateur || $utilisateur->statut !== 'actif' || ! $utilisateur->agent) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects ou compte non autorisé.',
            ], 401);
        }

        if (! Hash::check($request->password, $utilisateur->mot_de_passe)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects ou compte non autorisé.',
            ], 401);
        }

        $agent = $utilisateur->agent;
        $token = Str::random(64);

        Cache::put($this->cacheKey($token), [
            'agent_id' => $agent->id,
            'user_id' => $utilisateur->id,
        ], now()->addDays(30));

        return response()->json([
            'success' => true,
            'token' => $token,
            'agent' => $this->formatAgent($agent),
            'dashboard' => $this->buildDashboardPayload($agent),
        ]);
    }

    public function dashboard(Request $request)
    {
        $agent = $this->resolveAgent($request);

        if (! $agent) {
            return response()->json(['success' => false, 'message' => 'Session expirée.'], 401);
        }

        return response()->json([
            'success' => true,
            'agent' => $this->formatAgent($agent),
            'dashboard' => $this->buildDashboardPayload($agent),
        ]);
    }

    public function cancelTransaction(Request $request, Transaction $transaction)
    {
        $token = $this->extractToken($request);
        $payload = $token ? Cache::get($this->cacheKey($token)) : null;

        if (! is_array($payload) || empty($payload['user_id'])) {
            return response()->json(['success' => false, 'message' => 'Session expirée.'], 401);
        }

        $agent = Agent::find($payload['agent_id'] ?? 0);

        if (! $agent || $transaction->agent_id !== $agent->id) {
            return response()->json(['success' => false, 'message' => 'Transaction non autorisée.'], 403);
        }

        if (! $this->canAgentCancel($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'Annulation impossible : la transaction date de plus de 24 heures.',
            ], 422);
        }

        \Illuminate\Support\Facades\Auth::loginUsingId($payload['user_id']);

        $response = app(\App\Http\Controllers\TransactionController::class)->annuler($request, $transaction);
        $data = $response->getData(true);

        if (($data['success'] ?? false) && $agent) {
            $data['dashboard'] = $this->buildDashboardPayload($agent->fresh());
            return response()->json($data, $response->getStatusCode());
        }

        return $response;
    }

    public function logout(Request $request)
    {
        $token = $this->extractToken($request);

        if ($token) {
            Cache::forget($this->cacheKey($token));
        }

        return response()->json(['success' => true, 'message' => 'Déconnexion réussie.']);
    }

    public function changePassword(Request $request)
    {
        $agent = $this->resolveAgent($request);

        if (! $agent) {
            return response()->json(['success' => false, 'message' => 'Session expirée.'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $utilisateur = $agent->utilisateur;

        if (! $utilisateur) {
            return response()->json(['success' => false, 'message' => 'Compte utilisateur introuvable.'], 422);
        }

        if (! Hash::check($request->current_password, $utilisateur->mot_de_passe)) {
            return response()->json(['success' => false, 'message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $utilisateur->mot_de_passe = Hash::make($request->password);
        $utilisateur->save();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }

    public static function cacheKey(string $token): string
    {
        return 'mobile_agent:'.$token;
    }

    public static function canAgentCancel(Transaction $transaction): bool
    {
        if ($transaction->statut !== 'valide' || $transaction->isOperationAgence()) {
            return false;
        }

        $date = $transaction->date ?? $transaction->created_at;

        return $date && $date->gte(now()->subHours(24));
    }

    private function resolveAgent(Request $request): ?Agent
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return null;
        }

        $payload = Cache::get($this->cacheKey($token));

        if (! is_array($payload) || empty($payload['agent_id'])) {
            return null;
        }

        return Agent::with(['kiosque', 'utilisateur'])->find($payload['agent_id']);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        return $request->input('token');
    }

    private function formatAgent(Agent $agent): array
    {
        $agent->loadMissing(['kiosque', 'utilisateur']);

        return [
            'id' => $agent->id,
            'code_agent' => $agent->code_agent,
            'nom' => $agent->nom,
            'prenom' => $agent->prenom,
            'telephone' => $agent->telephone,
            'kiosque' => $agent->kiosque?->nom,
        ];
    }

    private function buildDashboardPayload(Agent $agent): array
    {
        $baseQuery = Transaction::commerciale()->with('operateur')->where('agent_id', $agent->id);

        $todayQuery = (clone $baseQuery)->duJour()->valide();
        $monthQuery = (clone $baseQuery)->duMois()->valide();

        $transactions = (clone $baseQuery)
            ->duMois()
            ->latest('date')
            ->get()
            ->map(fn (Transaction $t) => $this->formatTransactionRow($t));

        return [
            'balances' => $this->buildBalances($agent),
            'stats' => [
                'today_count' => $todayQuery->count(),
                'today_total' => (float) $todayQuery->sum('montant'),
                'today_commission' => (float) $todayQuery->sum('commission'),
                'month_count' => $monthQuery->count(),
                'month_total' => (float) $monthQuery->sum('montant'),
                'month_commission' => (float) $monthQuery->sum('commission'),
                'today_by_operateur' => $this->statsByOperateur((clone $baseQuery)->duJour()->valide()),
                'month_by_operateur' => $this->statsByOperateur((clone $baseQuery)->duMois()->valide()),
            ],
            'transactions' => $transactions,
        ];
    }

    private function buildBalances(Agent $agent): array
    {
        $soldes = $agent->soldesActuels(['operateur']);
        $espece = 0.0;
        $virtuels = [];

        foreach ($soldes as $solde) {
            if ($solde->type === 'espece') {
                $espece = (float) $solde->montant;
            } elseif ($solde->type === 'virtuel' && $solde->operateur) {
                $virtuels[] = [
                    'code' => $solde->operateur->code,
                    'libelle' => $solde->operateur->libelle,
                    'montant' => (float) $solde->montant,
                ];
            }
        }

        return [
            'espece' => $espece,
            'virtuels' => $virtuels,
        ];
    }

    private function formatTransactionRow(Transaction $t): array
    {
        return [
            'id' => $t->id,
            'reference' => $t->reference,
            'type' => $t->type,
            'statut' => $t->statut,
            'montant' => (float) $t->montant,
            'commission' => (float) ($t->commission ?? 0),
            'operateur' => $t->operateur?->libelle,
            'operateur_code' => $t->operateur?->code,
            'date' => $t->date?->format('d/m/Y H:i'),
            'can_cancel' => self::canAgentCancel($t),
        ];
    }

    /**
     * @return list<array{code: string, libelle: string, count: int, total: float, commission: float}>
     */
    private function statsByOperateur($query): array
    {
        $definitions = [
            'YAS' => 'Mixx by yas',
            'FLOOZ' => 'Flooz MONEY',
        ];

        $rows = [];
        foreach ($definitions as $code => $defaultLabel) {
            $operateurQuery = (clone $query)->whereHas('operateur', fn ($q) => $q->where('code', $code));
            $rows[] = [
                'code' => $code,
                'libelle' => $defaultLabel,
                'count' => $operateurQuery->count(),
                'total' => (float) $operateurQuery->sum('montant'),
                'commission' => (float) $operateurQuery->sum('commission'),
            ];
        }

        return $rows;
    }
}
