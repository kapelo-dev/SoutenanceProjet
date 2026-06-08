<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentKiosqueHistorique;
use App\Models\Kiosque;
use App\Models\Operateur;
use App\Models\Profil;
use App\Models\Solde;
use App\Models\Transaction;
use App\Models\TypeOperation;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const DEMO_AGENT_CODES = ['AG001', 'AG002', 'AG003', 'AG004', 'AG005'];

    public function run(): void
    {
        $this->ensureKiosques();
        $this->ensureBaseData();

        $admin = Utilisateur::where('email', 'admin@pdvconnect.com')->first();
        $profilAgent = Profil::where('libelle', 'Agent')->first();
        $kiosques = Kiosque::where('statut', 'actif')->get();
        $operateurs = Operateur::actif()->orderBy('ordre')->get();

        if ($kiosques->isEmpty() || $operateurs->isEmpty()) {
            $this->command->error('Kiosques ou opérateurs manquants. Lancez d\'abord les seeders de base.');
            return;
        }

        $agentsDef = [
            ['code' => 'AG001', 'nom' => 'Koffi', 'prenom' => 'Amévi', 'telephone' => '+22890111222', 'email' => 'amevi.koffi@pdvconnect.com', 'espece' => 500000, 'virtuels' => ['YAS' => 350000, 'FLOOZ' => 280000]],
            ['code' => 'AG002', 'nom' => 'Mensah', 'prenom' => 'Afi', 'telephone' => '+22890222333', 'email' => 'afi.mensah@pdvconnect.com', 'espece' => 420000, 'virtuels' => ['YAS' => 300000, 'FLOOZ' => 250000]],
            ['code' => 'AG003', 'nom' => 'Agbeko', 'prenom' => 'Kodjo', 'telephone' => '+22890333444', 'email' => 'kodjo.agbeko@pdvconnect.com', 'espece' => 380000, 'virtuels' => ['YAS' => 400000, 'FLOOZ' => 200000]],
            ['code' => 'AG004', 'nom' => 'Dossou', 'prenom' => 'Essi', 'telephone' => '+22890444555', 'email' => 'essi.dossou@pdvconnect.com', 'espece' => 450000, 'virtuels' => ['YAS' => 320000, 'FLOOZ' => 310000]],
            ['code' => 'AG005', 'nom' => 'Tchalla', 'prenom' => 'Yao', 'telephone' => '+22890555666', 'email' => 'yao.tchalla@pdvconnect.com', 'espece' => 360000, 'virtuels' => ['YAS' => 280000, 'FLOOZ' => 290000]],
        ];

        DB::beginTransaction();
        try {
            foreach ($agentsDef as $index => $def) {
                if (Agent::where('code_agent', $def['code'])->exists()) {
                    $this->command->info("Agent {$def['code']} déjà présent, ignoré.");
                    continue;
                }

                $kiosque = $kiosques[$index % $kiosques->count()];
                $agent = $this->createAgentWithUser($def, $kiosque, $profilAgent, $operateurs, $admin?->id);
                $this->command->info("✅ Agent {$agent->code_agent} — {$agent->nom_complet} ({$kiosque->nom})");
            }

            $allAgents = Agent::where('statut', 'actif')->get();
            foreach ($allAgents as $agent) {
                $created = $this->seedCommercialTransactions($agent, $operateurs);
                $this->command->info("  → {$created} transactions MM pour {$agent->code_agent}");
            }

            DB::commit();
            $this->command->info('✅ Données de démonstration créées avec succès !');
            $this->command->info('Mot de passe agents : password123');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function ensureKiosques(): void
    {
        $defaults = [
            ['code' => 'K003', 'nom' => 'Kiosque Agoè Centre', 'adresse' => 'Avenue de la Paix, près du marché', 'quartier' => 'Agoè', 'ville' => 'Lomé', 'latitude' => 6.1667, 'longitude' => 1.2167, 'telephone' => '+22890123456', 'type' => 'fixe', 'statut' => 'actif', 'capacite_agents' => 3, 'horaire_ouverture' => '08:00:00', 'horaire_fermeture' => '18:00:00'],
            ['code' => 'K004', 'nom' => 'Kiosque Tokoin', 'adresse' => 'Carrefour Tokoin Casablanca', 'quartier' => 'Tokoin', 'ville' => 'Lomé', 'latitude' => 6.1733, 'longitude' => 1.2309, 'telephone' => '+22890234567', 'type' => 'fixe', 'statut' => 'actif', 'capacite_agents' => 5, 'horaire_ouverture' => '07:30:00', 'horaire_fermeture' => '19:00:00'],
            ['code' => 'K005', 'nom' => 'Kiosque Bè-Kpota', 'adresse' => 'Marché de Bè-Kpota', 'quartier' => 'Bè', 'ville' => 'Lomé', 'latitude' => 6.1289, 'longitude' => 1.2158, 'telephone' => '+22890345678', 'type' => 'fixe', 'statut' => 'actif', 'capacite_agents' => 2, 'horaire_ouverture' => '08:00:00', 'horaire_fermeture' => '17:00:00'],
        ];

        foreach ($defaults as $data) {
            Kiosque::firstOrCreate(['code' => $data['code']], $data);
        }
    }

    private function ensureBaseData(): void
    {
        $this->call([
            TypeOperationSeeder::class,
            OperateurSeeder::class,
        ]);
    }

    private function createAgentWithUser(array $def, Kiosque $kiosque, ?Profil $profilAgent, $operateurs, ?int $adminId): Agent
    {
        $montantTotal = $def['espece'];
        foreach ($def['virtuels'] as $montant) {
            $montantTotal += $montant;
        }

        $utilisateur = Utilisateur::create([
            'nom' => $def['nom'],
            'prenom' => $def['prenom'],
            'email' => $def['email'],
            'telephone' => $def['telephone'],
            'mot_de_passe' => Hash::make('password123'),
            'statut' => 'actif',
            'email_verified_at' => now(),
        ]);

        if ($profilAgent) {
            DB::table('user_profils')->updateOrInsert(
                ['user_id' => $utilisateur->id, 'profil_id' => $profilAgent->id],
                ['deleted_at' => null, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $agent = Agent::create([
            'code_agent' => $def['code'],
            'nom' => $def['nom'],
            'prenom' => $def['prenom'],
            'telephone' => $def['telephone'],
            'montant_initial_total' => $montantTotal,
            'espece_initiale' => $def['espece'],
            'kiosque_id' => $kiosque->id,
            'statut' => 'actif',
            'user_id' => $utilisateur->id,
        ]);

        AgentKiosqueHistorique::create([
            'agent_id' => $agent->id,
            'kiosque_id' => $kiosque->id,
            'date_debut' => now()->subMonths(3)->toDateString(),
            'type_mouvement' => 'affectation',
            'commentaire' => 'Affectation initiale (données démo)',
            'created_by' => $adminId,
        ]);

        $this->seedInitialBalances($agent, $def, $operateurs);

        return $agent;
    }

    private function seedInitialBalances(Agent $agent, array $def, $operateurs): void
    {
        $typeApportEspece = TypeOperation::where('code', 'apport_espece')->first();
        $typeApportVirtuel = TypeOperation::where('code', 'apport_virtuel')->first();
        $dateInit = now()->subMonths(3);

        if ($typeApportEspece && $def['espece'] > 0) {
            $txn = Transaction::create([
                'agent_id' => $agent->id,
                'type_operation_id' => $typeApportEspece->id,
                'operateur_id' => null,
                'montant' => $def['espece'],
                'type' => 'depot',
                'statut' => 'valide',
                'description' => 'Montant initial en espèces',
                'date' => $dateInit,
            ]);

            Solde::create([
                'agent_id' => $agent->id,
                'operateur_id' => null,
                'montant' => $def['espece'],
                'type' => 'espece',
                'date' => $dateInit,
                'description' => "Transaction {$txn->reference} - {$typeApportEspece->libelle}",
            ]);
        }

        if ($typeApportVirtuel) {
            foreach ($operateurs as $operateur) {
                $montant = $def['virtuels'][$operateur->code] ?? 0;
                if ($montant <= 0) {
                    continue;
                }

                $txn = Transaction::create([
                    'agent_id' => $agent->id,
                    'type_operation_id' => $typeApportVirtuel->id,
                    'operateur_id' => $operateur->id,
                    'montant' => $montant,
                    'type' => 'depot',
                    'statut' => 'valide',
                    'description' => 'Montant initial virtuel - ' . $operateur->libelle,
                    'date' => $dateInit,
                ]);

                Solde::create([
                    'agent_id' => $agent->id,
                    'operateur_id' => $operateur->id,
                    'montant' => $montant,
                    'type' => 'virtuel',
                    'date' => $dateInit,
                    'description' => "Transaction {$txn->reference} - {$typeApportVirtuel->libelle}",
                ]);
            }
        }
    }

    private function seedCommercialTransactions(Agent $agent, $operateurs): int
    {
        $existing = Transaction::where('agent_id', $agent->id)
            ->whereNull('type_operation_id')
            ->count();

        if ($existing >= 15) {
            return 0;
        }

        $clients = [
            ['nom' => 'Akakpo', 'prenom' => 'Komlan', 'tel' => '+22897123456'],
            ['nom' => 'Abalo', 'prenom' => 'Mawuli', 'tel' => '+22897234567'],
            ['nom' => 'Soglo', 'prenom' => 'Edem', 'tel' => '+22897345678'],
            ['nom' => 'Gbedey', 'prenom' => 'Abla', 'tel' => '+22897456789'],
            ['nom' => 'Tetteh', 'prenom' => 'Kossi', 'tel' => '+22897567890'],
            ['nom' => 'Amouzou', 'prenom' => 'Fiovi', 'tel' => '+22897678901'],
            ['nom' => 'Blewussi', 'prenom' => 'Kafui', 'tel' => '+22897789012'],
            ['nom' => 'Dzidzinyo', 'prenom' => 'Sena', 'tel' => '+22897890123'],
        ];

        $types = ['depot', 'depot', 'depot', 'retrait', 'retrait', 'retrait', 'paiement', 'transfert'];
        $montants = [5000, 10000, 15000, 20000, 25000, 30000, 50000, 75000, 100000, 150000];
        $count = 0;
        $toCreate = 20 - $existing;

        for ($i = 0; $i < $toCreate; $i++) {
            $operateur = $operateurs->random();
            $client = $clients[array_rand($clients)];
            $type = $types[array_rand($types)];
            $montant = $montants[array_rand($montants)];
            $commission = $this->commissionFor($montant, $type);
            $date = Carbon::now()->subDays(rand(0, 55))->setTime(rand(7, 19), rand(0, 59));

            $statut = rand(1, 100) <= 92 ? 'valide' : (rand(0, 1) ? 'en_attente' : 'annule');

            $txn = Transaction::create([
                'agent_id' => $agent->id,
                'operateur_id' => $operateur->id,
                'montant' => $montant,
                'type' => $type,
                'statut' => $statut,
                'commission' => $statut === 'valide' ? $commission : null,
                'client_nom' => trim($client['prenom'] . ' ' . $client['nom']),
                'client_telephone' => $client['tel'],
                'operator_txn_id' => strtoupper(Str::random(12)),
                'description' => ucfirst($type) . ' ' . $operateur->libelle . ' — client ' . $client['prenom'],
                'date' => $date,
            ]);

            if ($statut === 'valide') {
                $this->applyBalance($agent, $operateur, $txn);
            }

            $count++;
        }

        return $count;
    }

    private function commissionFor(float $montant, string $type): int
    {
        if (in_array($type, ['transfert', 'paiement'])) {
            return (int) round($montant * 0.01);
        }

        return (int) round($montant * 0.015);
    }

    private function applyBalance(Agent $agent, Operateur $operateur, Transaction $transaction): void
    {
        $montant = (float) $transaction->montant;

        $dernierVirtuel = Solde::where('agent_id', $agent->id)
            ->where('operateur_id', $operateur->id)
            ->where('type', 'virtuel')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        $nouveauVirtuel = ($dernierVirtuel ? (float) $dernierVirtuel->montant : 0) + $montant;

        Solde::create([
            'agent_id' => $agent->id,
            'operateur_id' => $operateur->id,
            'montant' => $nouveauVirtuel,
            'type' => 'virtuel',
            'date' => $transaction->date,
            'description' => "Transaction {$transaction->reference}",
        ]);

        $transaction->update(['virtual_balance_after' => $nouveauVirtuel]);

        if (in_array($transaction->type, ['depot', 'retrait'])) {
            $dernierEspece = Solde::where('agent_id', $agent->id)
                ->whereNull('operateur_id')
                ->where('type', 'espece')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->first();

            $nouveauEspece = max(0, ($dernierEspece ? (float) $dernierEspece->montant : 0) - $montant);

            Solde::create([
                'agent_id' => $agent->id,
                'operateur_id' => null,
                'montant' => $nouveauEspece,
                'type' => 'espece',
                'date' => $transaction->date,
                'description' => "Transaction {$transaction->reference} ({$transaction->type})",
            ]);
        }
    }
}
