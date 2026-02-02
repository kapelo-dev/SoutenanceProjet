<!-- Actions et Filtres -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouveau_mouvement">
        <i class="ki-filled ki-plus me-2"></i>
        Nouveau mouvement
    </button>

    <form method="GET" action="{{ route('gestion-entreprise.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="hidden" name="onglet" value="tresorerie">
        <input type="date" name="date_debut" class="kt-input w-40" value="{{ $dateDebut }}">
        <span class="text-muted-foreground">à</span>
        <input type="date" name="date_fin" class="kt-input w-40" value="{{ $dateFin }}">
        <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline">
            <i class="ki-filled ki-filter me-1"></i>
            Filtrer
        </button>
    </form>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-7.5">
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                    <i class="ki-filled ki-arrow-down text-2xl text-success"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm text-muted-foreground">Entrées</div>
                    <div class="text-xl font-bold text-mono text-success">
                        +{{ number_format($stats['entrees'], 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full bg-danger/10">
                    <i class="ki-filled ki-arrow-up text-2xl text-danger"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm text-muted-foreground">Sorties</div>
                    <div class="text-xl font-bold text-mono text-danger">
                        -{{ number_format($stats['sorties'], 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card {{ $stats['solde'] >= 0 ? 'bg-success/5' : 'bg-danger/5' }}">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-12 rounded-full {{ $stats['solde'] >= 0 ? 'bg-success/10' : 'bg-danger/10' }}">
                    <i class="ki-filled ki-wallet text-2xl {{ $stats['solde'] >= 0 ? 'text-success' : 'text-danger' }}"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm text-muted-foreground">Solde Net</div>
                    <div class="text-xl font-bold text-mono {{ $stats['solde'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $stats['solde'] >= 0 ? '+' : '' }}{{ number_format($stats['solde'], 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphique (placeholder pour visualisation future) -->
<div class="kt-card mb-5">
    <div class="kt-card-content p-5">
        <h3 class="text-base font-semibold text-mono mb-3">Évolution de la trésorerie</h3>
        <div class="h-48 flex items-center justify-center bg-muted/10 rounded-lg">
            <span class="text-muted-foreground">
                <i class="ki-filled ki-chart-line text-3xl"></i>
                <p class="text-sm mt-2">Graphique à venir</p>
            </span>
        </div>
    </div>
</div>

<!-- Table des mouvements -->
<div class="kt-card">
    <div class="kt-card-content p-0">
        <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Catégorie</th>
                        <th>Description</th>
                        <th>Référence</th>
                        <th>Mode</th>
                        <th>Montant</th>
                        <th>Par</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mouvements as $mouvement)
                        <tr>
                            <td>{{ $mouvement->date_mouvement->format('d/m/Y') }}</td>
                            <td>
                                @if($mouvement->type === 'entree')
                                    <span class="kt-badge kt-badge-success">
                                        <i class="ki-filled ki-arrow-down me-1"></i>
                                        Entrée
                                    </span>
                                @else
                                    <span class="kt-badge kt-badge-danger">
                                        <i class="ki-filled ki-arrow-up me-1"></i>
                                        Sortie
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="kt-badge kt-badge-outline">
                                    {{ ucfirst($mouvement->categorie) }}
                                </span>
                            </td>
                            <td class="max-w-xs truncate">{{ $mouvement->description }}</td>
                            <td>{{ $mouvement->reference ?? '-' }}</td>
                            <td>
                                @if($mouvement->mode_paiement)
                                    <span class="text-xs">{{ ucfirst($mouvement->mode_paiement) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="font-bold {{ $mouvement->type === 'entree' ? 'text-success' : 'text-danger' }}">
                                    {{ $mouvement->type === 'entree' ? '+' : '-' }}{{ number_format($mouvement->montant, 0, ',', ' ') }} FCFA
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($mouvement->utilisateur->photo_profil)
                                        <img src="{{ asset('storage/' . $mouvement->utilisateur->photo_profil) }}" 
                                             class="size-6 rounded-full object-cover" 
                                             alt="{{ $mouvement->utilisateur->nom_complet }}">
                                    @else
                                        <div class="size-6 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-xs font-semibold text-primary">
                                                {{ strtoupper(substr($mouvement->utilisateur->prenom ?? $mouvement->utilisateur->nom, 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                    <span class="text-xs">{{ $mouvement->utilisateur->prenom }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted-foreground py-10">
                                Aucun mouvement trouvé pour cette période
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($mouvements->hasPages())
    <div class="flex justify-center mt-5">
        {{ $mouvements->appends(['onglet' => 'tresorerie', 'date_debut' => $dateDebut, 'date_fin' => $dateFin])->links() }}
    </div>
@endif

<!-- Modal: Nouveau Mouvement -->
<div class="kt-modal" data-kt-modal="true" id="modal_nouveau_mouvement">
    <div class="kt-modal-content max-w-2xl">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Nouveau mouvement de trésorerie</h3>
            <button class="kt-modal-close" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>

        <form action="{{ route('gestion-entreprise.mouvements.store') }}" method="POST">
            @csrf
            <div class="kt-modal-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Type
                            <span class="text-danger">*</span>
                        </label>
                        <select name="type" class="kt-select" data-kt-select="true" required>
                            <option value="entree">Entrée</option>
                            <option value="sortie">Sortie</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Catégorie
                            <span class="text-danger">*</span>
                        </label>
                        <select name="categorie" class="kt-select" data-kt-select="true" required>
                            <option value="salaire">Salaire</option>
                            <option value="commission">Commission</option>
                            <option value="fourniture">Fourniture</option>
                            <option value="loyer">Loyer</option>
                            <option value="facture">Facture</option>
                            <option value="equipement">Équipement</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Montant (FCFA)
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="montant" class="kt-input" min="0" step="1" required>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Date
                            <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_mouvement" class="kt-input" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Mode de paiement</label>
                        <select name="mode_paiement" class="kt-select" data-kt-select="true">
                            <option value="">Non spécifié</option>
                            <option value="espece">Espèce</option>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Référence</label>
                        <input type="text" name="reference" class="kt-input" placeholder="N° facture, reçu...">
                    </div>

                    <div class="flex flex-col gap-2 lg:col-span-2">
                        <label class="text-sm font-medium">
                            Description
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="description" class="kt-textarea" rows="3" required></textarea>
                    </div>
                </div>
            </div>

            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check me-1"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
