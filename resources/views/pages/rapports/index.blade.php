@extends('layouts.demo1.base')

@section('content')
<main class="grow" id="content" role="content">
    <!-- Container -->
    <div class="kt-container-fixed" id="contentContainer">
    </div>
    <!-- End of Container -->
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Rapports
                </h1>
                <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                    Gestion des rapports - {{ $statsGlobales['total_transactions'] }} transaction{{ $statsGlobales['total_transactions'] > 1 ? 's' : '' }}
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button"
                    class="kt-btn kt-btn-outline"
                    data-pdf-preview
                    data-pdf-url="{{ route('rapports.export', request()->all()) }}"
                    data-pdf-title="Rapport des transactions">
                    <img src="{{ asset('assets/media/app/pdf-icon.svg') }}" alt="PDF" class="w-5 h-5 inline-block mr-2" />
                    Exporter en PDF
                </button>
                <a href="{{ route('rapports.export', array_merge(request()->all(), ['format' => 'excel'])) }}"
                    class="kt-btn kt-btn-outline" data-ajax="false">
                    <img src="{{ asset('assets/media/file-types/excel.svg') }}" alt="Excel" class="w-5 h-5 inline-block mr-2" />
                    Exporter en Excel
                </a>
                <button class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_filtres_rapports">
                    <i class="ki-filled ki-setting-4"></i>
                    Filtres
                </button>
                <a href="{{ route('rapports.index', request()->except(['date_debut', 'date_fin', 'agent_id', 'operateur_id', 'type', 'statut', 'kiosque_id'])) }}" class="kt-btn kt-btn-outline" onclick="return confirm('Voulez-vous réinitialiser tous les filtres ?')">
                    <i class="ki-filled ki-arrows-circle"></i>
                    Réinitialiser
                </a>
            </div>
        </div>
    </div>
    <!-- End of Container -->
    
    <!-- Modal Filtres -->
    <div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_filtres_rapports" style="display: none;">
        <div class="kt-modal-content max-w-[800px]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">
                    Filtres de rapport
                </h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('rapports.index') }}" id="form_filtres">
                <div class="kt-modal-body">
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Date de début</label>
                                <input type="date" name="date_debut" class="kt-input" value="{{ request('date_debut', $dateDebut->format('Y-m-d')) }}" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Date de fin</label>
                                <input type="date" name="date_fin" class="kt-input" value="{{ request('date_fin', $dateFin->format('Y-m-d')) }}" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <label class="kt-label">Agent</label>
                                    <button type="button" class="text-xs text-primary hover:underline" onclick="toggleAllAgentsSearch(this)">
                                        <span class="select-all-text" id="agent_select_all_text">Tout sélectionner</span>
                                    </button>
                                </div>
                                <input type="hidden" name="agent_id_all" id="agent_id_all_value" value="" />
                                <div id="rapport_agent_selected_badges" class="flex flex-wrap gap-1.5 min-h-0"></div>
                                <div class="relative">
                                    <input class="kt-input" type="text" id="rapport_agent_search" placeholder="Rechercher agent (nom, prénom, code…)" autocomplete="off" />
                                    <div id="rapport_agent_dropdown" class="hidden absolute left-0 right-0 top-full z-30 mt-1 rounded-lg border border-border bg-background shadow-lg max-h-48 overflow-auto py-1">
                                        <div class="px-3 py-2 text-xs text-muted-foreground border-b border-border flex items-center gap-2">
                                            <input type="checkbox" id="rapport_agent_tous_checkbox" class="checkbox-filter"
                                                {{ in_array('tous', (array)request('agent_id', [])) ? 'checked' : '' }}
                                                onchange="handleAgentTousCheckbox(this)">
                                            <span class="font-semibold">Tous les agents</span>
                                        </div>
                                        <div id="rapport_agent_results"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <label class="kt-label">Opérateur</label>
                                    <button type="button" class="text-xs text-primary hover:underline" onclick="toggleAllCheckboxes('operateur_id', this)">
                                        <span class="select-all-text">Tout sélectionner</span>
                                    </button>
                                </div>
                                <div class="kt-card max-h-48 overflow-y-auto border border-border p-3">
                                    <div class="flex flex-col gap-1">
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-2 transition-colors border-b border-border pb-2 mb-1">
                                            <input type="checkbox" name="operateur_id[]" value="tous" class="checkbox-filter"
                                                {{ in_array('tous', (array)request('operateur_id', [])) ? 'checked' : '' }}
                                                onchange="handleTousCheckbox(this, 'operateur_id')">
                                            <span class="text-sm font-semibold text-foreground">Tous les opérateurs</span>
                                        </label>
                                        @foreach($operateurs as $operateur)
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="operateur_id[]" value="{{ $operateur->id }}" class="checkbox-filter operateur-checkbox"
                                                {{ in_array($operateur->id, (array)request('operateur_id', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'operateur_id')">
                                            <span class="text-sm text-foreground">{{ $operateur->libelle }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <label class="kt-label">Type de transaction</label>
                                    <button type="button" class="text-xs text-primary hover:underline" onclick="toggleAllCheckboxes('type', this)">
                                        <span class="select-all-text">Tout sélectionner</span>
                                    </button>
                                </div>
                                <div class="kt-card max-h-48 overflow-y-auto border border-border p-3">
                                    <div class="flex flex-col gap-1">
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-2 transition-colors border-b border-border pb-2 mb-1">
                                            <input type="checkbox" name="type[]" value="tous" class="checkbox-filter"
                                                {{ in_array('tous', (array)request('type', [])) ? 'checked' : '' }}
                                                onchange="handleTousCheckbox(this, 'type')">
                                            <span class="text-sm font-semibold text-foreground">Tous les types</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="type[]" value="depot" class="checkbox-filter type-checkbox"
                                                {{ in_array('depot', (array)request('type', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'type')">
                                            <span class="text-sm text-foreground">Dépôt</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="type[]" value="retrait" class="checkbox-filter type-checkbox"
                                                {{ in_array('retrait', (array)request('type', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'type')">
                                            <span class="text-sm text-foreground">Retrait</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="type[]" value="transfert" class="checkbox-filter type-checkbox"
                                                {{ in_array('transfert', (array)request('type', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'type')">
                                            <span class="text-sm text-foreground">Transfert</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="type[]" value="paiement" class="checkbox-filter type-checkbox"
                                                {{ in_array('paiement', (array)request('type', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'type')">
                                            <span class="text-sm text-foreground">Paiement</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <label class="kt-label">Statut</label>
                                    <button type="button" class="text-xs text-primary hover:underline" onclick="toggleAllCheckboxes('statut', this)">
                                        <span class="select-all-text">Tout sélectionner</span>
                                    </button>
                                </div>
                                <div class="kt-card max-h-48 overflow-y-auto border border-border p-3">
                                    <div class="flex flex-col gap-1">
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-2 transition-colors border-b border-border pb-2 mb-1">
                                            <input type="checkbox" name="statut[]" value="tous" class="checkbox-filter"
                                                {{ in_array('tous', (array)request('statut', [])) ? 'checked' : '' }}
                                                onchange="handleTousCheckbox(this, 'statut')">
                                            <span class="text-sm font-semibold text-foreground">Tous les statuts</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="statut[]" value="valide" class="checkbox-filter statut-checkbox"
                                                {{ in_array('valide', (array)request('statut', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'statut')">
                                            <span class="text-sm text-foreground">Validé</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="statut[]" value="en_attente" class="checkbox-filter statut-checkbox"
                                                {{ in_array('en_attente', (array)request('statut', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'statut')">
                                            <span class="text-sm text-foreground">En attente</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="statut[]" value="annule" class="checkbox-filter statut-checkbox"
                                                {{ in_array('annule', (array)request('statut', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'statut')">
                                            <span class="text-sm text-foreground">Annulé</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="statut[]" value="echoue" class="checkbox-filter statut-checkbox"
                                                {{ in_array('echoue', (array)request('statut', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'statut')">
                                            <span class="text-sm text-foreground">Échoué</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="kt-label">Kiosque</label>
                                <button type="button" class="text-xs text-primary hover:underline" onclick="toggleAllCheckboxes('kiosque_id', this)">
                                    <span class="select-all-text">Tout sélectionner</span>
                                </button>
                            </div>
                            <div class="kt-card max-h-48 overflow-y-auto border border-border p-3">
                                <div class="flex flex-col gap-1">
                                    <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-2 transition-colors border-b border-border pb-2 mb-1">
                                        <input type="checkbox" name="kiosque_id[]" value="tous" class="checkbox-filter"
                                            {{ in_array('tous', (array)request('kiosque_id', [])) ? 'checked' : '' }}
                                            onchange="handleTousCheckbox(this, 'kiosque_id')">
                                        <span class="text-sm font-semibold text-foreground">Tous les kiosques</span>
                                    </label>
                                    @foreach($kiosques as $kiosque)
                                    <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                        <input type="checkbox" name="kiosque_id[]" value="{{ $kiosque->id }}" class="checkbox-filter kiosque-checkbox"
                                            {{ in_array($kiosque->id, (array)request('kiosque_id', [])) ? 'checked' : '' }}
                                            onchange="handleCheckboxChange(this, 'kiosque_id')">
                                        <span class="text-sm text-foreground">{{ $kiosque->nom }} <span class="text-secondary-foreground">({{ $kiosque->code }})</span></span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-modal-footer">
                    <button type="button" class="kt-btn kt-btn-ghost" data-kt-modal-dismiss="true">Annuler</button>
                    <button type="submit" class="kt-btn kt-btn-primary" id="btn_appliquer_filtres">
                        <i class="ki-filled ki-magnifier"></i>
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Modal Filtres -->
    
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            {{-- Statistiques Globales --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Statistiques Globales</h3>
                    <div class="text-sm text-secondary-foreground">
                        Période: {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="overflow-x-auto">
                        <table class="kt-table kt-table-row-bordered kt-table-row-dashed align-middle">
                            <thead>
                                <tr>
                                    <th class="min-w-150px">Indicateur</th>
                                    <th class="min-w-150px text-end">Valeur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Nombre total de transactions</strong></td>
                                    <td class="text-end"><strong>{{ number_format($statsGlobales['total_transactions'], 0, ',', ' ') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Montant total</strong></td>
                                    <td class="text-end"><strong>{{ number_format($statsGlobales['montant_total'], 0, ',', ' ') }} XOF</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Commission totale</strong></td>
                                    <td class="text-end"><strong>{{ number_format($statsGlobales['commission_total'], 0, ',', ' ') }} XOF</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre d'agents</strong></td>
                                    <td class="text-end"><strong>{{ $statsGlobales['nombre_agents'] }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Statistiques par Opérateur --}}
            @if(count($statsOperateurs) > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Statistiques par Opérateur</h3>
                </div>
                <div class="kt-card-content">
                    <div class="overflow-x-auto">
                        <table class="kt-table kt-table-row-bordered kt-table-row-dashed align-middle">
                            <thead>
                                <tr>
                                    <th class="min-w-150px">Opérateur</th>
                                    <th class="min-w-100px text-end">Nombre de transactions</th>
                                    <th class="min-w-150px text-end">Montant total</th>
                                    <th class="min-w-150px text-end">Commission totale</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statsOperateurs as $stat)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if($stat['operateur']->logo)
                                            <img alt="{{ $stat['operateur']->libelle }}" class="w-8 h-8 object-contain" src="{{ asset('storage/' . $stat['operateur']->logo) }}" />
                                            @else
                                            <div class="w-8 h-8 rounded flex items-center justify-center text-white text-xs font-bold" style="background-color: {{ $stat['operateur']->couleur ?? '#3b82f6' }};">
                                                {{ strtoupper(substr($stat['operateur']->libelle, 0, 1)) }}
                                            </div>
                                            @endif
                                            <span>{{ $stat['operateur']->libelle }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($stat['nombre_transactions'], 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stat['montant_total'], 0, ',', ' ') }} XOF</td>
                                    <td class="text-end">{{ number_format($stat['commission_total'], 0, ',', ' ') }} XOF</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Top 10 Agents --}}
            @if(count($topAgents) > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Top 10 Agents</h3>
                </div>
                <div class="kt-card-content">
                    <div class="overflow-x-auto">
                        <table class="kt-table kt-table-row-bordered kt-table-row-dashed align-middle">
                            <thead>
                                <tr>
                                    <th class="min-w-50px">Rang</th>
                                    <th class="min-w-200px">Agent</th>
                                    <th class="min-w-100px text-end">Nombre de transactions</th>
                                    <th class="min-w-150px text-end">Montant total</th>
                                    <th class="min-w-150px text-end">Commission totale</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topAgents as $index => $topAgent)
                                <tr>
                                    <td><strong>{{ $index + 1 }}</strong></td>
                                    <td>{{ $topAgent['agent']->nomComplet }}</td>
                                    <td class="text-end">{{ number_format($topAgent['nombre_transactions'], 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($topAgent['montant_total'], 0, ',', ' ') }} XOF</td>
                                    <td class="text-end">{{ number_format($topAgent['commission_total'], 0, ',', ' ') }} XOF</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Liste des Transactions --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Détail des Transactions ({{ count($transactions) }} transaction{{ count($transactions) > 1 ? 's' : '' }})</h3>
                </div>
                <div class="kt-card-content">
                    <div class="overflow-x-auto">
                        <table class="kt-table kt-table-row-bordered kt-table-row-dashed align-middle">
                            <thead>
                                <tr>
                                    <th class="min-w-120px">Référence</th>
                                    <th class="min-w-120px">Date</th>
                                    <th class="min-w-100px">Type</th>
                                    <th class="min-w-120px text-end">Montant (XOF)</th>
                                    <th class="min-w-120px">Opérateur</th>
                                    <th class="min-w-150px">Agent</th>
                                    <th class="min-w-120px">Client</th>
                                    <th class="min-w-120px">Téléphone Client</th>
                                    <th class="min-w-120px text-end">Commission (XOF)</th>
                                    <th class="min-w-100px">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->reference ?? '-' }}</td>
                                    <td>{{ $transaction->date ? $transaction->date->format('d/m/Y H:i') : '-' }}</td>
                                    <td><span class="badge badge-light-{{ $transaction->type == 'depot' ? 'success' : ($transaction->type == 'retrait' ? 'danger' : 'primary') }}">{{ ucfirst($transaction->type ?? '-') }}</span></td>
                                    <td class="text-end">{{ number_format($transaction->montant ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td>
                                        @if($transaction->operateur)
                                        <div class="flex items-center gap-2">
                                            @if($transaction->operateur->logo)
                                            <img alt="{{ $transaction->operateur->libelle }}" class="w-6 h-6 object-contain" src="{{ asset('storage/' . $transaction->operateur->logo) }}" />
                                            @else
                                            <div class="w-6 h-6 rounded flex items-center justify-center text-white text-xs font-bold" style="background-color: {{ $transaction->operateur->couleur ?? '#3b82f6' }};">
                                                {{ strtoupper(substr($transaction->operateur->libelle, 0, 1)) }}
                                            </div>
                                            @endif
                                            <span>{{ $transaction->operateur->libelle }}</span>
                                        </div>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>{{ ($transaction->agent) ? (($transaction->agent->prenom ?? '') . ' ' . ($transaction->agent->nom ?? '')) : '-' }}</td>
                                    <td>{{ $transaction->client_nom ?? '-' }}</td>
                                    <td>{{ $transaction->client_telephone ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($transaction->commission ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td>
                                        <span class="badge badge-light-{{ $transaction->statut == 'valide' ? 'success' : ($transaction->statut == 'annule' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($transaction->statut ?? '-') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-10">
                                        <p class="text-sm text-secondary-foreground">Aucune transaction trouvée pour les filtres sélectionnés</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</main>

<style>
/* Style personnalisé pour les checkboxes */
.checkbox-filter {
    width: 1.125rem;
    height: 1.125rem;
    cursor: pointer;
    accent-color: hsl(var(--primary));
    flex-shrink: 0;
    border-radius: 0.25rem;
    border: 2px solid hsl(var(--border));
    transition: all 0.2s ease;
}

.checkbox-filter:hover {
    border-color: hsl(var(--primary));
}

.checkbox-filter:checked {
    background-color: hsl(var(--primary));
    border-color: hsl(var(--primary));
}

.checkbox-filter:focus {
    outline: 2px solid hsl(var(--primary) / 0.3);
    outline-offset: 2px;
}

/* Style pour les conteneurs de checkboxes */
.kt-card.max-h-48 {
    background-color: hsl(var(--card));
    border-radius: 0.5rem;
}

/* Style pour les labels avec hover amélioré */
label[class*="cursor-pointer"] {
    border-radius: 0.375rem;
    transition: background-color 0.15s ease;
}

label[class*="cursor-pointer"]:hover {
    background-color: hsl(var(--accent) / 0.5) !important;
}

/* Style pour le bouton "Tout sélectionner" */
button[onclick*="toggleAllCheckboxes"] {
    transition: color 0.15s ease;
}

button[onclick*="toggleAllCheckboxes"]:hover {
    color: hsl(var(--primary)) !important;
}
</style>

<script>
function handleTousCheckbox(checkbox, fieldName) {
    const allCheckboxes = document.querySelectorAll(`input[name="${fieldName}[]"]:not([value="tous"])`);
    
    if (checkbox.checked) {
        // Si "Tous" est coché, cocher tous les autres visuellement
        allCheckboxes.forEach(cb => {
            cb.checked = true;
        });
    } else {
        // Si "Tous" est décoché, décocher tous les autres
        allCheckboxes.forEach(cb => {
            cb.checked = false;
        });
    }
}

function handleCheckboxChange(checkbox, fieldName) {
    const tousCheckbox = document.querySelector(`input[name="${fieldName}[]"][value="tous"]`);
    const allCheckboxes = document.querySelectorAll(`input[name="${fieldName}[]"]:not([value="tous"])`);
    
    if (!checkbox.checked && tousCheckbox && tousCheckbox.checked) {
        // Si un élément spécifique est décoché et "Tous" est coché, décocher "Tous"
        tousCheckbox.checked = false;
    } else if (checkbox.checked) {
        // Si un élément spécifique est coché, vérifier si tous sont cochés
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        if (allChecked && tousCheckbox) {
            // Si tous les éléments sont cochés, cocher "Tous" aussi
            tousCheckbox.checked = true;
        } else if (tousCheckbox) {
            // Sinon, décocher "Tous"
            tousCheckbox.checked = false;
        }
    }
}

function toggleAllCheckboxes(fieldName, button) {
    const allCheckboxes = document.querySelectorAll(`input[name="${fieldName}[]"]:not([value="tous"])`);
    const tousCheckbox = document.querySelector(`input[name="${fieldName}[]"][value="tous"]`);
    const selectAllText = button.querySelector('.select-all-text');
    
    // Vérifier si tous sont déjà sélectionnés
    const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
    
    if (allChecked) {
        // Tout désélectionner
        allCheckboxes.forEach(cb => cb.checked = false);
        if (tousCheckbox) tousCheckbox.checked = false;
        if (selectAllText) selectAllText.textContent = 'Tout sélectionner';
    } else {
        // Tout sélectionner
        allCheckboxes.forEach(cb => cb.checked = true);
        if (tousCheckbox) tousCheckbox.checked = true;
        if (selectAllText) selectAllText.textContent = 'Tout désélectionner';
    }
}

// ===== Recherche multi-select Agent =====
var rapportAgentsData = @json($agentsJson ?? []);
var rapportSelectedAgents = {};
// Initialiser les agents sélectionnés depuis la requête
(function() {
    var preselected = @json((array)request('agent_id', []));
    preselected.forEach(function(id) {
        if (id === 'tous' || id === '') return;
        var found = rapportAgentsData.find(function(a) { return String(a.id) === String(id); });
        if (found) {
            rapportSelectedAgents[found.id] = found;
        }
    });
})();

function renderAgentBadges() {
    var container = document.getElementById('rapport_agent_selected_badges');
    if (!container) return;
    var ids = Object.keys(rapportSelectedAgents);
    if (ids.length === 0) {
        container.innerHTML = '';
        return;
    }
    container.innerHTML = ids.map(function(id) {
        var a = rapportSelectedAgents[id];
        var label = (a.libelle || 'Agent #' + a.id) + (a.code_agent ? ' (' + a.code_agent + ')' : '');
        return '<span class="inline-flex items-center gap-1 rounded-md bg-primary/10 text-primary text-xs font-medium px-2 py-1">' +
            '<span>' + label.replace(/</g, '&lt;') + '</span>' +
            '<button type="button" class="ml-0.5 hover:text-destructive" onclick="removeAgentFromSelection(' + id + ')">&times;</button>' +
            '</span>';
    }).join('');
}

function removeAgentFromSelection(agentId) {
    delete rapportSelectedAgents[agentId];
    renderAgentBadges();
    updateAgentSelectAllText();
    // Décocher "Tous" si on retire un agent
    var tousCb = document.getElementById('rapport_agent_tous_checkbox');
    if (tousCb) tousCb.checked = false;
}

function handleAgentTousCheckbox(checkbox) {
    if (checkbox.checked) {
        // Sélectionner tous les agents
        rapportAgentsData.forEach(function(a) {
            rapportSelectedAgents[a.id] = a;
        });
    } else {
        // Désélectionner tous les agents
        rapportSelectedAgents = {};
    }
    renderAgentBadges();
    updateAgentSelectAllText();
}

function toggleAllAgentsSearch(button) {
    var ids = Object.keys(rapportSelectedAgents);
    var tousCb = document.getElementById('rapport_agent_tous_checkbox');
    
    if (ids.length === rapportAgentsData.length) {
        // Tout désélectionner
        rapportSelectedAgents = {};
        if (tousCb) tousCb.checked = false;
    } else {
        // Tout sélectionner
        rapportAgentsData.forEach(function(a) {
            rapportSelectedAgents[a.id] = a;
        });
        if (tousCb) tousCb.checked = true;
    }
    renderAgentBadges();
    updateAgentSelectAllText();
}

function updateAgentSelectAllText() {
    var textEl = document.getElementById('agent_select_all_text');
    var ids = Object.keys(rapportSelectedAgents);
    if (textEl) {
        textEl.textContent = (ids.length === rapportAgentsData.length && ids.length > 0) ? 'Tout désélectionner' : 'Tout sélectionner';
    }
}

function initAgentSearch() {
    var searchEl = document.getElementById('rapport_agent_search');
    var dropdownEl = document.getElementById('rapport_agent_dropdown');
    var resultsEl = document.getElementById('rapport_agent_results');
    
    if (!searchEl || !dropdownEl || !resultsEl) return;
    
    // Afficher les badges initiaux
    renderAgentBadges();
    updateAgentSelectAllText();
    
    searchEl.addEventListener('focus', function() {
        showAgentDropdown('');
    });
    
    searchEl.addEventListener('input', function() {
        var q = (this.value || '').toLowerCase().trim();
        showAgentDropdown(q);
    });
    
    function showAgentDropdown(q) {
        var filtered = rapportAgentsData;
        if (q.length >= 1) {
            filtered = rapportAgentsData.filter(function(a) {
                return (a.libelle && a.libelle.toLowerCase().indexOf(q) !== -1) ||
                    (a.nom && a.nom.toLowerCase().indexOf(q) !== -1) ||
                    (a.prenom && a.prenom.toLowerCase().indexOf(q) !== -1) ||
                    (a.code_agent && a.code_agent.toLowerCase().indexOf(q) !== -1);
            });
        }
        
        if (filtered.length === 0 && q.length >= 1) {
            resultsEl.innerHTML = '<div class="px-3 py-2 text-xs text-muted-foreground">Aucun agent trouvé</div>';
        } else {
            resultsEl.innerHTML = filtered.slice(0, 20).map(function(a) {
                var isSelected = rapportSelectedAgents[a.id];
                var label = (a.libelle || 'Agent #' + a.id) + (a.code_agent ? ' <span class="text-secondary-foreground">(' + a.code_agent + ')</span>' : '');
                return '<label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">' +
                    '<input type="checkbox" class="checkbox-filter rapport-agent-cb" data-agent-id="' + a.id + '" ' + (isSelected ? 'checked' : '') + ' onchange="toggleAgentFromDropdown(this, ' + a.id + ')">' +
                    '<span class="text-sm text-foreground">' + label + '</span>' +
                    '</label>';
            }).join('');
        }
        dropdownEl.classList.remove('hidden');
    }
    
    // Fermer en cliquant dehors
    document.addEventListener('click', function(e) {
        if (!searchEl.contains(e.target) && !dropdownEl.contains(e.target)) {
            dropdownEl.classList.add('hidden');
        }
    });
}

function toggleAgentFromDropdown(checkbox, agentId) {
    if (checkbox.checked) {
        var found = rapportAgentsData.find(function(a) { return a.id === agentId; });
        if (found) rapportSelectedAgents[agentId] = found;
    } else {
        delete rapportSelectedAgents[agentId];
    }
    renderAgentBadges();
    updateAgentSelectAllText();
    // Mettre à jour "Tous" checkbox
    var tousCb = document.getElementById('rapport_agent_tous_checkbox');
    if (tousCb) {
        tousCb.checked = Object.keys(rapportSelectedAgents).length === rapportAgentsData.length;
    }
}

// Injecter les agent_id sélectionnés dans le formulaire avant soumission
function setupAgentFormSubmit() {
    var form = document.getElementById('form_filtres');
    if (!form) return;
    
    var origSubmit = form.onsubmit;
    form.addEventListener('submit', function(e) {
        // Supprimer les anciens hidden inputs agent_id
        form.querySelectorAll('input[name="agent_id[]"]').forEach(function(el) { el.remove(); });
        
        var ids = Object.keys(rapportSelectedAgents);
        var tousCb = document.getElementById('rapport_agent_tous_checkbox');
        
        if (tousCb && tousCb.checked) {
            // Ajouter "tous"
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'agent_id[]';
            inp.value = 'tous';
            form.appendChild(inp);
        } else if (ids.length > 0) {
            ids.forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'agent_id[]';
                inp.value = id;
                form.appendChild(inp);
            });
        }
    });
}

// Mettre à jour le texte du bouton selon l'état initial
function updateToggleButtonsText() {
    const toggleButtons = document.querySelectorAll('[onclick*="toggleAllCheckboxes"]');
    toggleButtons.forEach(button => {
        const fieldName = button.getAttribute('onclick').match(/'([^']+)'/)[1];
        const allCheckboxes = document.querySelectorAll(`input[name="${fieldName}[]"]:not([value="tous"])`);
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        const selectAllText = button.querySelector('.select-all-text');
        
        if (selectAllText) {
            selectAllText.textContent = allChecked ? 'Tout désélectionner' : 'Tout sélectionner';
        }
    });
}

// Exposer une fonction d'initialisation globale pour la page Rapports (utilisée par la navigation AJAX)
window.initRapportsPage = function() {
    // Réinitialiser les selects Metronic (KTSelect) avec un délai pour s'assurer que le DOM est prêt
    setTimeout(() => {
        const selects = document.querySelectorAll('.kt-select[data-kt-select="true"]');
        selects.forEach(select => {
            if (window.KTSelect && typeof window.KTSelect.getInstance === 'function') {
                const instance = window.KTSelect.getInstance(select);
                if (instance) {
                    instance.destroy();
                }
                new window.KTSelect(select);
            }
        });
    }, 200);
    
    // Mettre à jour les textes des boutons toggle
    updateToggleButtonsText();
    
    // Initialiser la recherche d'agents
    initAgentSearch();
    setupAgentFormSubmit();
    
    // Configurer la fermeture du modal après soumission du formulaire
    setupFiltresFormClose();
};

// Initialisation sur chargement normal
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.initRapportsPage();
    });
} else {
    window.initRapportsPage();
}

// Réinitialisation explicite après navigation AJAX (quand la page Rapports est chargée via AJAX)
document.addEventListener('ajax-content-loaded', function() {
    // Attendre un peu pour s'assurer que le DOM est complètement chargé
    setTimeout(function() {
        if (document.getElementById('modal_filtres_rapports')) {
            window.initRapportsPage();
        }
    }, 100);
});

// Fonction pour fermer le modal après soumission du formulaire
function setupFiltresFormClose() {
    const formFiltres = document.getElementById('form_filtres');
    const modal = document.getElementById('modal_filtres_rapports');
    
    if (!formFiltres || !modal) {
        return;
    }
    
    // Retirer l'ancien listener s'il existe (pour éviter les doublons après AJAX)
    if (formFiltres._submitListenerAttached && formFiltres._submitHandler) {
        formFiltres.removeEventListener('submit', formFiltres._submitHandler);
        formFiltres._submitListenerAttached = false;
    }
    
    // Créer un nouveau handler
    formFiltres._submitHandler = function(e) {
        // Fermer le modal après un court délai pour laisser le temps à la soumission
        setTimeout(function() {
            const currentModal = document.getElementById('modal_filtres_rapports');
            if (currentModal) {
                currentModal.classList.add('hidden');
                currentModal.classList.remove('flex');
                currentModal.style.display = 'none';
                currentModal.classList.remove('show');
            }
        }, 150);
    };
    
    // Attacher le nouveau listener
    formFiltres.addEventListener('submit', formFiltres._submitHandler);
    formFiltres._submitListenerAttached = true;
}
</script>
@endsection
