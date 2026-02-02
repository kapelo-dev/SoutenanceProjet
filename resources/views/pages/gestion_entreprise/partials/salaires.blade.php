<!-- Actions -->
<div class="flex flex-wrap items-center gap-3 mb-5">
    <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_generer_salaires">
        <i class="ki-filled ki-flash-circle me-2"></i>
        Générer les salaires
    </button>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-7.5">
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                    <i class="ki-filled ki-wallet text-2xl text-primary"></i>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Total Salaires</div>
                    <div class="text-xl font-bold text-mono">{{ number_format($salaires->sum('montant_total'), 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                    <i class="ki-filled ki-check-circle text-2xl text-success"></i>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Payés</div>
                    <div class="text-xl font-bold text-mono">{{ $salaires->where('statut', 'paye')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                    <i class="ki-filled ki-timer text-2xl text-warning"></i>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">En Attente</div>
                    <div class="text-xl font-bold text-mono">{{ $salaires->where('statut', 'en_attente')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                    <i class="ki-filled ki-chart-line-up text-2xl text-info"></i>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Moyenne</div>
                    <div class="text-xl font-bold text-mono">
                        {{ $salaires->count() > 0 ? number_format($salaires->avg('montant_total'), 0, ',', ' ') : '0' }} FCFA
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table des salaires -->
<div class="kt-scrollable-x-auto">
    <table class="kt-table kt-table-border">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Période</th>
                <th>Fixe</th>
                <th>Commission</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Date Paiement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salaires as $salaire)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            @if($salaire->agent->utilisateur->photo_profil)
                                <img src="{{ asset('storage/' . $salaire->agent->utilisateur->photo_profil) }}" 
                                     class="size-8 rounded-full object-cover" 
                                     alt="{{ $salaire->agent->utilisateur->nom_complet }}">
                            @else
                                <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-primary">
                                        {{ strtoupper(substr($salaire->agent->utilisateur->prenom ?? $salaire->agent->utilisateur->nom, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                            <span class="font-medium">{{ $salaire->agent->utilisateur->nom_complet }}</span>
                        </div>
                    </td>
                    <td>{{ $salaire->periode }}</td>
                    <td>{{ number_format($salaire->montant_fixe, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($salaire->montant_commission, 0, ',', ' ') }} FCFA</td>
                    <td class="font-bold">{{ number_format($salaire->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @if($salaire->statut === 'paye')
                            <span class="kt-badge kt-badge-success">Payé</span>
                        @elseif($salaire->statut === 'en_attente')
                            <span class="kt-badge kt-badge-warning">En attente</span>
                        @else
                            <span class="kt-badge kt-badge-secondary">{{ $salaire->statut }}</span>
                        @endif
                    </td>
                    <td>{{ $salaire->date_paiement ? $salaire->date_paiement->format('d/m/Y') : '-' }}</td>
                    <td>
                        @if($salaire->statut === 'en_attente')
                            <button type="button" 
                                    class="kt-btn kt-btn-xs kt-btn-success"
                                    onclick="openPayerModal({{ $salaire->id }})">
                                <i class="ki-filled ki-check-circle me-1"></i>
                                Payer
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted-foreground py-10">
                        Aucun salaire trouvé
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($salaires->hasPages())
    <div class="flex justify-center mt-5">
        {{ $salaires->links() }}
    </div>
@endif

<!-- Modal: Générer Salaires -->
<div class="kt-modal" data-kt-modal="true" id="modal_generer_salaires">
    <div class="kt-modal-content max-w-2xl">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Générer les salaires</h3>
            <button class="kt-modal-close" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>

        <form action="{{ route('gestion-entreprise.generer-salaires') }}" method="POST">
            @csrf
            <div class="kt-modal-body">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Période</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="date" name="date_debut" class="kt-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                            <input type="date" name="date_fin" class="kt-input" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Agents (laissez vide pour tous les agents actifs)</label>
                        <select name="agent_ids[]" class="kt-select" data-kt-select="true" multiple>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->utilisateur->nom_complet }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kt-alert kt-alert-info">
                        <i class="ki-filled ki-information-2"></i>
                        <span>Les salaires seront calculés automatiquement selon les paramètres définis. Les commissions seront basées sur les transactions de la période.</span>
                    </div>
                </div>
            </div>

            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-flash-circle me-1"></i>
                    Générer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Payer Salaire -->
<div class="kt-modal" data-kt-modal="true" id="modal_payer_salaire">
    <div class="kt-modal-content max-w-xl">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Payer le salaire</h3>
            <button class="kt-modal-close" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>

        <form id="form_payer_salaire" method="POST">
            @csrf
            <div class="kt-modal-body">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Date de paiement</label>
                        <input type="date" name="date_paiement" class="kt-input" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Mode de paiement</label>
                        <select name="mode_paiement" class="kt-select" data-kt-select="true" required>
                            <option value="espece">Espèce</option>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Notes (optionnel)</label>
                        <textarea name="notes" class="kt-textarea" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check-circle me-1"></i>
                    Confirmer le paiement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayerModal(salaireId) {
    const modal = document.getElementById('modal_payer_salaire');
    const form = document.getElementById('form_payer_salaire');
    form.action = `/gestion-entreprise/salaires/${salaireId}/payer`;
    
    if (window.KTModal) {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    }
}
</script>
