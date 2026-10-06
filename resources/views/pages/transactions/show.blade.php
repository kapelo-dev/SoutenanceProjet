@extends('layouts.demo1.base')

@section('content')
@php
    $statuts = [
        'valide' => ['Validée', 'success'],
        'en_attente' => ['En attente', 'warning'],
        'annule' => ['Annulée', 'destructive'],
        'echoue' => ['Échouée', 'secondary'],
    ];
    [$statutLibelle, $statutCouleur] = $statuts[$transaction->statut] ?? [ucfirst($transaction->statut), 'secondary'];
    $types = ['depot' => 'Dépôt', 'retrait' => 'Retrait', 'transfert' => 'Transfert', 'paiement' => 'Paiement'];
    $fcfa = fn ($montant) => $montant !== null ? number_format((float) $montant, 0, ',', ' ') . ' FCFA' : '—';
@endphp

<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono flex items-center gap-3">
                Transaction {{ $transaction->reference }}
                <span class="kt-badge kt-badge-outline kt-badge-{{ $statutCouleur }}">{{ $statutLibelle }}</span>
            </h1>
            <div class="text-sm text-secondary-foreground">
                {{ $transaction->date?->format('d/m/Y à H:i') }}
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('transactions.index') }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i> Liste des transactions
            </a>
            @if($transaction->statut !== 'valide')
                <a href="{{ route('transactions.edit', $transaction) }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-notepad-edit"></i> Modifier
                </a>
            @endif
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="lg:col-span-2 flex flex-col gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Détails de l'opération</h3>
                </div>
                <div class="kt-card-content">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-7.5 gap-y-4 text-sm">
                        <div>
                            <dt class="text-secondary-foreground">Type</dt>
                            <dd class="font-medium text-mono">{{ $types[$transaction->type] ?? ucfirst($transaction->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-secondary-foreground">Montant</dt>
                            <dd class="font-semibold text-mono text-base">{{ $fcfa($transaction->montant) }}</dd>
                        </div>
                        <div>
                            <dt class="text-secondary-foreground">Commission</dt>
                            <dd class="font-medium text-mono">{{ $fcfa($transaction->commission) }}</dd>
                        </div>
                        <div>
                            <dt class="text-secondary-foreground">Opérateur</dt>
                            <dd class="font-medium text-mono">{{ $transaction->operateur?->libelle ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-secondary-foreground">Client</dt>
                            <dd class="font-medium text-mono">{{ $transaction->client_nom ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-secondary-foreground">Téléphone du client</dt>
                            <dd class="font-medium text-mono">{{ $transaction->client_telephone ?: '—' }}</dd>
                        </div>
                        @if($transaction->operator_txn_id)
                            <div>
                                <dt class="text-secondary-foreground">Référence opérateur</dt>
                                <dd class="font-medium text-mono">{{ $transaction->operator_txn_id }}</dd>
                            </div>
                        @endif
                        @if($transaction->virtual_balance_after !== null)
                            <div>
                                <dt class="text-secondary-foreground">Solde virtuel après opération</dt>
                                <dd class="font-medium text-mono">{{ $fcfa($transaction->virtual_balance_after) }}</dd>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <dt class="text-secondary-foreground">Description</dt>
                            <dd class="font-medium text-mono whitespace-pre-line">{{ $transaction->description ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Historique des modifications</h3>
                </div>
                <div class="kt-card-content">
                    @forelse($transaction->audits->sortByDesc('date_modification') as $audit)
                        <div class="flex flex-col gap-1 py-3 border-b border-border last:border-0 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="kt-badge kt-badge-sm kt-badge-outline">{{ ucfirst($audit->type_modification) }}</span>
                                <span class="text-secondary-foreground">
                                    {{ $audit->date_modification?->format('d/m/Y H:i') }}
                                    — {{ $audit->utilisateur?->nom_complet ?? 'Système' }}
                                </span>
                            </div>
                            <div class="text-mono">
                                {{ $fcfa($audit->ancien_montant) }} → {{ $fcfa($audit->nouveau_montant) }}
                            </div>
                            @if($audit->raison)
                                <div class="text-secondary-foreground">Raison : {{ $audit->raison }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-secondary-foreground">Aucune modification enregistrée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Agent</h3>
                </div>
                <div class="kt-card-content text-sm">
                    @if($transaction->agent)
                        <div class="font-medium text-mono">{{ $transaction->agent->nomComplet }}</div>
                        <div class="text-secondary-foreground">{{ $transaction->agent->code_agent ?? '—' }} · {{ $transaction->agent->telephone }}</div>
                        <div class="text-secondary-foreground mt-2">Kiosque : {{ $transaction->agent->kiosque?->nom ?? 'Aucun' }}</div>
                    @else
                        <span class="text-secondary-foreground">Agent introuvable</span>
                    @endif
                </div>
            </div>

            @if($transaction->statut !== 'annule')
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Annuler la transaction</h3>
                    </div>
                    <div class="kt-card-content">
                        <form id="form-annulation-transaction" data-url="{{ route('transactions.annuler', $transaction) }}" class="flex flex-col gap-3">
                            <p class="text-sm text-secondary-foreground">
                                Les soldes de l'agent seront rétablis et l'annulation sera tracée dans l'historique.
                            </p>
                            <textarea class="kt-textarea" name="raison" rows="3" maxlength="500" required
                                      placeholder="Raison de l'annulation"></textarea>
                            <p class="text-sm text-destructive hidden" data-erreur></p>
                            <button type="submit" class="kt-btn kt-btn-destructive">Annuler la transaction</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('form-annulation-transaction');
    if (!form) return;

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const bouton = form.querySelector('button[type="submit"]');
        const erreur = form.querySelector('[data-erreur]');
        erreur.classList.add('hidden');
        bouton.disabled = true;

        try {
            const response = await fetch(form.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ raison: form.raison.value }),
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                window.location.reload();
                return;
            }
            erreur.textContent = data.message || 'Annulation impossible.';
        } catch (e) {
            erreur.textContent = 'Erreur réseau : la transaction n\'a pas été annulée.';
        }
        erreur.classList.remove('hidden');
        bouton.disabled = false;
    });
})();
</script>
@endsection
