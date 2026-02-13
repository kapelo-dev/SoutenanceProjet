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
                <a href="{{ route('rapports.export', request()->all()) }}" class="kt-btn kt-btn-outline" data-ajax="false" target="_blank">
                    <img src="{{ asset('assets/media/app/pdf-icon.svg') }}" alt="PDF" class="w-5 h-5 inline-block mr-2" />
                    Exporter en PDF
                </a>
                <button class="kt-btn kt-btn-outline kt-btn-primary" data-kt-modal-toggle="#modal_filtres_rapports">
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
                                    <button type="button" class="text-xs text-primary hover:underline" onclick="toggleAllCheckboxes('agent_id', this)">
                                        <span class="select-all-text">Tout sélectionner</span>
                                    </button>
                                </div>
                                <div class="kt-card max-h-48 overflow-y-auto border border-border p-3">
                                    <div class="flex flex-col gap-1">
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-2 transition-colors border-b border-border pb-2 mb-1">
                                            <input type="checkbox" name="agent_id[]" value="tous" class="checkbox-filter" 
                                                {{ in_array('tous', (array)request('agent_id', [])) ? 'checked' : '' }}
                                                onchange="handleTousCheckbox(this, 'agent_id')">
                                            <span class="text-sm font-semibold text-foreground">Tous les agents</span>
                                        </label>
                                        @foreach($agents as $agent)
                                        <label class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md px-3 py-1.5 transition-colors">
                                            <input type="checkbox" name="agent_id[]" value="{{ $agent->id }}" class="checkbox-filter agent-checkbox"
                                                {{ in_array($agent->id, (array)request('agent_id', [])) ? 'checked' : '' }}
                                                onchange="handleCheckboxChange(this, 'agent_id')">
                                            <span class="text-sm text-foreground">{{ $agent->nomComplet }} <span class="text-secondary-foreground">({{ $agent->code_agent }})</span></span>
                                        </label>
                                        @endforeach
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
                                <label class="kt-label">Statut</label>
                                <select name="statut[]" multiple class="kt-select" data-kt-select="true" data-kt-select-placeholder="Sélectionner des statuts">
                                    <option value="valide" {{ in_array('valide', (array)request('statut', [])) ? 'selected' : '' }}>Validé</option>
                                    <option value="en_attente" {{ in_array('en_attente', (array)request('statut', [])) ? 'selected' : '' }}>En attente</option>
                                    <option value="annule" {{ in_array('annule', (array)request('statut', [])) ? 'selected' : '' }}>Annulé</option>
                                    <option value="echoue" {{ in_array('echoue', (array)request('statut', [])) ? 'selected' : '' }}>Échoué</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">Kiosque</label>
                            <select name="kiosque_id[]" multiple class="kt-select" data-kt-select="true" data-kt-select-placeholder="Sélectionner des kiosques">
                                @foreach($kiosques as $kiosque)
                                <option value="{{ $kiosque->id }}" {{ in_array($kiosque->id, (array)request('kiosque_id', [])) ? 'selected' : '' }}>
                                    {{ $kiosque->nom }} ({{ $kiosque->code }})
                                </option>
                                @endforeach
                            </select>
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
