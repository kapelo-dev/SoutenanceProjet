@extends('layouts.demo1.base')

@section('content')
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Dashboard Agent
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                {{ $agent->nom }} {{ $agent->prenom }} @if($agent->kiosque) — {{ $agent->kiosque->nom }} @endif
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-outline" href="{{ route('transactions.index') }}">
                <i class="ki-filled ki-eye"></i>
                Voir toutes les transactions
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm text-secondary-foreground">Aujourd'hui</div>
                        <div class="text-2xl font-semibold text-mono">{{ number_format($stats['today_total'] ?? 0, 0, ',', ' ') }} F</div>
                        <div class="text-xs text-muted-foreground">{{ $stats['today_count'] ?? 0 }} transaction(s) validée(s)</div>
                    </div>
                    <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <i class="ki-filled ki-calendar text-primary text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm text-secondary-foreground">Ce mois</div>
                        <div class="text-2xl font-semibold text-mono">{{ number_format($stats['month_total'] ?? 0, 0, ',', ' ') }} F</div>
                        <div class="text-xs text-muted-foreground">{{ $stats['month_count'] ?? 0 }} transaction(s) validée(s)</div>
                    </div>
                    <div class="size-12 rounded-full bg-success/10 flex items-center justify-center">
                        <i class="ki-filled ki-chart-line text-success text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm text-secondary-foreground">Commission (mois)</div>
                        <div class="text-2xl font-semibold text-mono">{{ number_format($stats['month_commission'] ?? 0, 0, ',', ' ') }} F</div>
                        <div class="text-xs text-muted-foreground">Total validé</div>
                    </div>
                    <div class="size-12 rounded-full bg-warning/10 flex items-center justify-center">
                        <i class="ki-filled ki-dollar text-warning text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card mt-7.5">
        <div class="kt-card-header py-5">
            <h3 class="kt-card-title">Dernières transactions</h3>
        </div>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th class="min-w-[180px] text-center" style="width: 22%;">Référence</th>
                            <th class="min-w-[140px] text-center" style="width: 15%;">Type</th>
                            <th class="min-w-[160px] text-center" style="width: 18%;">Opérateur</th>
                            <th class="min-w-[160px] text-center" style="width: 18%;">Montant</th>
                            <th class="min-w-[160px] text-center" style="width: 15%;">Date</th>
                            <th class="min-w-[120px] text-center" style="width: 12%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td class="text-center">
                                    <a class="font-medium text-sm text-mono hover:text-primary" href="{{ route('transactions.show', $t->id) }}">
                                        {{ $t->reference }}
                                    </a>
                                    <div class="text-xs text-secondary-foreground">{{ ucfirst($t->statut) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">{{ ucfirst($t->type) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm">{{ $t->operateur?->libelle ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-semibold">{{ number_format($t->montant ?? 0, 0, ',', ' ') }} F</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm text-secondary-foreground">{{ $t->date?->format('d/m/Y H:i') ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($t->statut === 'valide' && \App\Http\Controllers\Api\MobileAgentController::canAgentCancel($t))
                                        <button class="kt-btn kt-btn-sm kt-btn-outline kt-btn-danger annuler-transaction" 
                                                data-transaction-id="{{ $t->id }}"
                                                data-transaction-ref="{{ $t->reference }}">
                                            <i class="ki-filled ki-cross-circle"></i>
                                            Annuler
                                        </button>
                                    @elseif($t->statut === 'valide')
                                        <span class="text-xs text-muted-foreground" title="Annulation possible sous 48 h">+ 48 h</span>
                                    @else
                                        <span class="text-xs text-muted-foreground">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-muted-foreground">
                                    Aucune transaction pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'annulation de transaction
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.annuler-transaction');
        if (!btn) return;

        const transactionId = btn.dataset.transactionId;
        const transactionRef = btn.dataset.transactionRef;

        // Demander confirmation
        if (!confirm(`Êtes-vous sûr de vouloir annuler la transaction ${transactionRef} ?\n\nCette action inversera automatiquement les soldes de l'agent.`)) {
            return;
        }

        // Demander la raison de l'annulation
        const raison = prompt('Veuillez indiquer la raison de l\'annulation :');
        if (!raison || raison.trim() === '') {
            AppToast.warning('La raison de l\'annulation est obligatoire.');
            return;
        }

        // Désactiver le bouton pendant le traitement
        btn.disabled = true;
        btn.innerHTML = '<i class="ki-filled ki-loading"></i> Annulation...';

        // Envoyer la requête d'annulation
        fetch(`/transactions/${transactionId}/annuler`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ raison: raison })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                AppToast.success(data.message || 'Transaction annulée avec succès.');
                // Recharger la page pour afficher les soldes mis à jour
                window.location.reload();
            } else {
                AppToast.error(data.message || 'Erreur lors de l\'annulation de la transaction.');
                btn.disabled = false;
                btn.innerHTML = '<i class="ki-filled ki-cross-circle"></i> Annuler';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            AppToast.error('Une erreur est survenue lors de l\'annulation.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ki-filled ki-cross-circle"></i> Annuler';
        });
    });
});
</script>
@endpush

