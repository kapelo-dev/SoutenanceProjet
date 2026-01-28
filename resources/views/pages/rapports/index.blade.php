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
                <button class="kt-btn kt-btn-outline kt-btn-primary" data-kt-drawer-toggle="#filtres_drawer">
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
    
    <!-- Drawer Filtres -->
    <div class="kt-drawer kt-drawer-end card bottom-5 end-5 top-5 hidden w-[450px] max-w-[90%] flex-col rounded-xl border border-border"
        data-kt-drawer="true" data-kt-drawer-container="body" id="filtres_drawer">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between gap-2.5 px-5 py-3.5 text-sm font-semibold text-mono">
                Filtres de rapport
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="border-b border-b-border"></div>
            <form method="GET" action="{{ route('rapports.index') }}" id="form_filtres" class="flex flex-col h-full">
                <div class="kt-scrollable-y-auto grow" data-kt-scrollable="true" data-kt-scrollable-dependencies="#header"
                    data-kt-scrollable-max-height="auto" data-kt-scrollable-offset="230px">
                    <div class="flex flex-col gap-5 p-5">
                        <div class="grid grid-cols-1 gap-5">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Date de début</label>
                                <input type="date" name="date_debut" class="kt-input" value="{{ request('date_debut', $dateDebut->format('Y-m-d')) }}" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Date de fin</label>
                                <input type="date" name="date_fin" class="kt-input" value="{{ request('date_fin', $dateFin->format('Y-m-d')) }}" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5">
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
                        <div class="grid grid-cols-1 gap-5">
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
                <div class="border-t border-t-border p-5 flex items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-ghost" data-kt-drawer-dismiss="true">Annuler</button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-magnifier"></i>
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Drawer Filtres -->
    
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- begin: grid -->
            <div class="grid items-stretch gap-y-5 lg:grid-cols-3 lg:gap-7.5">
                <div class="lg:col-span-1">
                    <div class="grid h-full grid-cols-2 items-stretch gap-5 lg:gap-7.5">
                        <style>
                            .channel-stats-bg {
                                background-image: url('assets/media/images/2600x1600/bg-3.png');
                            }

                            .dark .channel-stats-bg {
                                background-image: url('assets/media/images/2600x1600/bg-3-dark.png');
                            }
                        </style>
                        @forelse($statsOperateurs as $index => $stat)
                        @if($index < 4)
                        <div class="kt-card channel-stats-bg h-full flex-col justify-between gap-6 bg-cover bg-[right_top_-1.7rem] bg-no-repeat rtl:bg-[left_top_-1.7rem]">
                            @if($stat['operateur']->logo)
                            <img alt="{{ $stat['operateur']->libelle }}" class="ms-5 mt-4 w-20 h-20 object-contain" src="{{ asset('storage/' . $stat['operateur']->logo) }}" />
                            @else
                            <div class="ms-5 mt-4 w-10 h-10 rounded flex items-center justify-center text-white font-bold" style="background-color: {{ $stat['operateur']->couleur ?? '#3b82f6' }};">
                                {{ strtoupper(substr($stat['operateur']->libelle, 0, 1)) }}
                            </div>
                            @endif
                            <div class="flex flex-col gap-1 px-5 pb-4">
                                <span class="text-3xl font-semibold text-mono">
                                    {{ number_format($stat['montant_total'] / 1000, 1) }}k
                                </span>
                                <span class="text-sm font-normal text-secondary-foreground">
                                    {{ $stat['operateur']->libelle }}
                                </span>
                                <span class="text-xs font-normal text-secondary-foreground">
                                    {{ $stat['nombre_transactions'] }} transaction{{ $stat['nombre_transactions'] > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @empty
                        @for($i = 0; $i < 4; $i++)
                        <div class="kt-card channel-stats-bg h-full flex-col justify-between gap-6 bg-cover bg-[right_top_-1.7rem] bg-no-repeat rtl:bg-[left_top_-1.7rem]">
                            <div class="ms-5 mt-4 w-7 h-7 rounded bg-gray-300"></div>
                            <div class="flex flex-col gap-1 px-5 pb-4">
                                <span class="text-3xl font-semibold text-mono">0</span>
                                <span class="text-sm font-normal text-secondary-foreground">Aucune donnée</span>
                            </div>
                        </div>
                        @endfor
                        @endforelse
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <style>
                        .entry-callout-bg {
                            background-image: url('assets/media/images/2600x1600/2.png');
                        }

                        .dark .entry-callout-bg {
                            background-image: url('assets/media/images/2600x1600/2-dark.png');
                        }
                    </style>
                    <div class="kt-card h-full">
                        <div class="kt-card-content entry-callout-bg bg-[length:80%] bg-no-repeat p-10 [background-position:175%_25%] rtl:[background-position:-70%_25%]">
                            <div class="flex flex-col justify-center gap-4">
                                <div class="flex -space-x-2">
                                    @forelse($topAgents as $topAgent)
                                    <div class="flex">
                                        @if($topAgent['agent']->utilisateur && $topAgent['agent']->utilisateur->photo_profil)
                                        <img class="hover:z-5 relative size-10 shrink-0 rounded-full ring-1 ring-background object-cover" src="{{ asset('storage/' . $topAgent['agent']->utilisateur->photo_profil) }}" alt="{{ $topAgent['agent']->nomComplet }}" />
                                        @else
                                        <span class="hover:z-5 text-2xs relative inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold leading-none text-primary-inverse ring-1 ring-background">
                                            {{ strtoupper(substr($topAgent['agent']->nom ?? $topAgent['agent']->prenom, 0, 1)) }}
                                        </span>
                                        @endif
                                    </div>
                                    @empty
                                    <div class="flex">
                                        <span class="hover:z-5 text-2xs relative inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-gray-300 text-xs font-semibold leading-none text-gray-600 ring-1 ring-background">
                                            ?
                                        </span>
                                    </div>
                                    @endforelse
                                </div>
                                <h2 class="text-xl font-semibold text-mono">
                                    Top Agents
                                    <br />
                                    <span class="text-base font-normal text-secondary-foreground">
                                        Période: {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}
                                    </span>
                                </h2>
                                <div class="flex flex-col gap-2">
                                    @forelse($topAgents as $topAgent)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-foreground">{{ $topAgent['agent']->nomComplet }}</span>
                                        <span class="text-secondary-foreground">{{ number_format($topAgent['montant_total'], 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    @empty
                                    <p class="text-sm text-secondary-foreground">Aucun agent trouvé pour cette période</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="kt-card-footer justify-center">
                            <a class="kt-link kt-link-underlined kt-link-dashed" href="{{ route('agents.liste-agents') }}">
                                Voir tous les agents
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end: grid -->
            <!-- begin: grid -->
            <div class="grid items-stretch gap-5 lg:grid-cols-3 lg:gap-7.5">
                <div class="lg:col-span-1">
                    <div class="kt-card h-full">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Statistiques</h3>
                        </div>
                        <div class="kt-card-content flex flex-col gap-5 px-5 pt-5 lg:px-7.5">
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-foreground">Montant total</span>
                                <span class="text-2xl font-semibold text-mono">{{ number_format($statsGlobales['montant_total'], 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-foreground">Commission totale</span>
                                <span class="text-2xl font-semibold text-mono">{{ number_format($statsGlobales['commission_total'], 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-foreground">Nombre d'agents</span>
                                <span class="text-2xl font-semibold text-mono">{{ $statsGlobales['nombre_agents'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="kt-card h-full">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Dernières transactions</h3>
                            <a class="kt-btn kt-btn-sm kt-btn-outline" href="{{ route('transactions.index') }}">
                                Voir tout
                            </a>
                        </div>
                        <div class="kt-card-content">
                            <div class="flex flex-col gap-5">
                                @forelse($dernieresTransactions as $transaction)
                                <div class="flex items-center gap-2.5">
                                    <div class="flex items-center justify-center rounded-full bg-gray-100 size-9 dark:bg-gray-900">
                                        @if($transaction->type == 'depot')
                                        <i class="ki-filled ki-arrow-down text-base text-green-600 dark:text-green-400"></i>
                                        @elseif($transaction->type == 'retrait')
                                        <i class="ki-filled ki-arrow-up text-base text-red-600 dark:text-red-400"></i>
                                        @elseif($transaction->type == 'transfert')
                                        <i class="ki-filled ki-arrows-circle text-base text-blue-600 dark:text-blue-400"></i>
                                        @else
                                        <i class="ki-filled ki-wallet text-base text-yellow-600 dark:text-yellow-400"></i>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-0.5 flex-1">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ ucfirst($transaction->type) }} - {{ $transaction->operateur->libelle ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs font-normal text-secondary-foreground">
                                            {{ $transaction->agent->nomComplet ?? 'N/A' }} - {{ $transaction->reference }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1 lg:gap-5">
                                        <span class="text-xs font-normal text-secondary-foreground">
                                            {{ $transaction->date->locale('fr')->isoFormat('D MMM Y, HH:mm') }}
                                        </span>
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ number_format($transaction->montant, 0, ',', ' ') }} FCFA
                                        </span>
                                        <div class="kt-menu" data-kt-menu="true">
                                            <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px"
                                                data-kt-menu-item-placement="bottom-end"
                                                data-kt-menu-item-placement-rtl="bottom-start"
                                                data-kt-menu-item-toggle="dropdown"
                                                data-kt-menu-item-trigger="click">
                                                <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                    <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                </button>
                                                <div class="kt-menu-dropdown kt-menu-default w-full max-w-[200px]" data-kt-menu-dismiss="true">
                                                    <div class="kt-menu-item">
                                                        <a class="kt-menu-link" href="{{ route('transactions.index', ['search' => $transaction->reference]) }}">
                                                            <span class="kt-menu-icon">
                                                                <i class="ki-filled ki-document"></i>
                                                            </span>
                                                            <span class="kt-menu-title">Détails</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="flex items-center justify-center py-10">
                                    <p class="text-sm text-secondary-foreground">Aucune transaction trouvée</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end: grid -->
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
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endsection
