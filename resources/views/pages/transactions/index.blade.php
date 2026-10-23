@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Gestion des Transactions
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Vue d'ensemble de toutes les transactions effectuées.
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button"
                class="kt-btn kt-btn-outline"
                data-pdf-preview
                data-export-table="#transactions_table"
                data-pdf-url="{{ route('transactions.export', request()->all()) }}"
                data-pdf-title="Liste des transactions">
                <img src="{{ asset('assets/media/app/pdf-icon.svg') }}" alt="PDF" class="w-5 h-5 inline-block mr-2" />
                Exporter en PDF
            </button>
            <a href="{{ route('transactions.export', array_merge(request()->all(), ['format' => 'excel'])) }}"
                class="kt-btn kt-btn-outline" data-ajax="false" data-export-table="#transactions_table">
                <img src="{{ asset('assets/media/file-types/excel.svg') }}" alt="Excel" class="w-5 h-5 inline-block mr-2" />
                Exporter en Excel
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->
<!-- Container -->
<div class="kt-container-fixed">
    <div class="kt-card kt-card-grid min-w-full">
        @php
            $typesTransaction = [
                'depot' => 'Dépôt',
                'retrait' => 'Retrait',
                'transfert' => 'Transfert',
                'paiement' => 'Paiement',
            ];
            $agentFiltre = request('agent_id') ? $agents->firstWhere('id', (int) request('agent_id')) : null;
            $filtresActifs = array_filter([
                'date_debut' => request('date_debut') ? 'Du ' . \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') : null,
                'date_fin' => request('date_fin') ? 'Au ' . \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') : null,
                'agent_id' => $agentFiltre ? 'Agent : ' . $agentFiltre->nomComplet : null,
                'type' => request('type') ? 'Type : ' . ($typesTransaction[request('type')] ?? request('type')) : null,
            ]);
            $puces = $filtresActifs + (request('search') ? ['search' => 'Recherche : « ' . request('search') . ' »'] : []);
            // Paramètres à conserver d'un formulaire à l'autre (filtres, recherche, taille de page)
            $paramsConserves = fn (array $sauf) => \Illuminate\Support\Arr::except(request()->query(), array_merge($sauf, ['page']));
        @endphp
        <div class="kt-card-header py-5 flex-wrap gap-2">
            <div class="flex flex-wrap items-center gap-2">
                @foreach($puces as $champ => $libelle)
                    <span class="kt-badge kt-badge-outline kt-badge-primary gap-1.5">
                        {{ $libelle }}
                        <a href="{{ route('transactions.index', $paramsConserves([$champ])) }}"
                           class="leading-none" title="Retirer ce filtre">
                            <i class="ki-filled ki-cross text-xs"></i>
                        </a>
                    </span>
                @endforeach
                @if(count($puces) > 1)
                    <a href="{{ route('transactions.index') }}" class="kt-link kt-link-underlined kt-link-dashed text-sm">Tout effacer</a>
                @endif
            </div>
            <div class="flex items-center gap-2.5 ml-auto">
                <form method="GET" action="{{ route('transactions.index') }}" id="transactions_recherche_form">
                    @foreach($paramsConserves(['search']) as $cle => $valeur)
                        <input type="hidden" name="{{ $cle }}" value="{{ $valeur }}"/>
                    @endforeach
                    <label class="kt-input">
                        <i class="ki-filled ki-magnifier"></i>
                        <input name="search" placeholder="Référence, client, téléphone…" type="search" value="{{ request('search') }}"/>
                    </label>
                </form>
                <button type="button" class="kt-btn kt-btn-outline relative" data-transactions-filtres-open>
                    <i class="ki-filled ki-filter"></i>
                    Filtres
                    @if(count($filtresActifs))
                        <span class="kt-badge kt-badge-sm kt-badge-primary rounded-full">{{ count($filtresActifs) }}</span>
                    @endif
                </button>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" id="transactions_table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="w-[50px] text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-transactions-check-all type="checkbox"/>
                                </th>
                                <th class="min-w-[160px] text-center" style="width: 14%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            N° Transaction
                                        </span>
                                    </span>
                                </th>
                                <th class="min-w-[180px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Client
                                        </span>
                                    </span>
                                </th>
                                <th class="min-w-[180px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Agent
                                        </span>
                                    </span>
                                </th>
                                <th class="min-w-[120px] text-center" style="width: 12%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Montant
                                        </span>
                                    </span>
                                </th>
                                <th class="min-w-[100px] text-center" style="width: 10%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Commission
                                        </span>
                                    </span>
                                </th>
                                <th class="min-w-[120px] text-center" style="width: 12%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Statut
                                        </span>
                                    </span>
                                </th>
                                <th class="min-w-[160px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Date
                                        </span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td class="text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="{{ $transaction->id }}"/>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-col gap-0.5 items-center">
                                        <span class="leading-none font-medium text-sm text-mono">
                                            {{ $transaction->reference }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground font-normal">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if($transaction->operateur)
                                            @php
                                                $operateurLogo = $transaction->operateur->logo
                                                    ? asset('storage/' . $transaction->operateur->logo)
                                                    : null;
                                            @endphp
                                            <div class="w-8 h-8 shrink-0 flex items-center justify-center">
                                            @if($operateurLogo)
                                                <img class="h-8 w-8 rounded-full object-cover" src="{{ $operateurLogo }}" alt="{{ $transaction->operateur->libelle }}"/>
                                            @else
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-[10px] font-semibold text-white"
                                                     style="background-color: {{ $transaction->operateur->couleur ?? '#6366f1' }};">
                                                    {{ strtoupper(substr($transaction->operateur->code, 0, 2)) }}
                                                </div>
                                            @endif
                                            </div>
                                        @endif
                                        <div class="flex flex-col gap-0.5 items-start min-w-0 flex-1">
                                            <span class="leading-none font-medium text-sm truncate w-full">
                                                @if($transaction->client_nom)
                                                    {{ $transaction->client_nom }}
                                                @elseif($transaction->operateur)
                                                    {{ $transaction->operateur->libelle }}
                                                @else
                                                    —
                                                @endif
                                            </span>
                                            @if($transaction->client_telephone)
                                                <span class="text-xs text-secondary-foreground font-normal truncate w-full">
                                                    {{ $transaction->client_telephone }}
                                                </span>
                                            @else
                                                <span class="text-xs text-muted-foreground">—</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2.5">
                                        @if($transaction->agent)
                                            @php
                                                $agentAvatar = asset('assets/media/avatars/300-3.png');
                                                if ($transaction->agent->utilisateur && $transaction->agent->utilisateur->photo_profil) {
                                                    $agentAvatar = asset('storage/' . $transaction->agent->utilisateur->photo_profil);
                                                }
                                            @endphp
                                            <img class="h-9 w-9 rounded-full object-cover flex-shrink-0" src="{{ $agentAvatar }}" alt=""/>
                                            <div class="flex flex-col gap-0.5 text-left max-w-[180px]">
                                                <span class="leading-none font-medium text-sm truncate w-full">
                                                    @if($transaction->agent->utilisateur)
                                                        {{ $transaction->agent->utilisateur->prenom }} {{ $transaction->agent->utilisateur->nom }}
                                                    @else
                                                        {{ $transaction->agent->nomComplet }}
                                                    @endif
                                                </span>
                                                <span class="text-xs text-secondary-foreground font-normal">
                                                    {{ $transaction->agent->code_agent ?? '—' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted-foreground">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center text-foreground font-semibold">
                                    @if($transaction->type == 'depot')
                                        <span class="text-success">+{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</span>
                                    @elseif($transaction->type == 'retrait')
                                        <span class="text-destructive">-{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</span>
                                    @else
                                        <span class="text-primary">{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</span>
                                    @endif
                                </td>
                                <td class="text-center text-foreground font-medium">
                                    @if($transaction->commission !== null && $transaction->commission > 0)
                                        {{ number_format($transaction->commission, 0, ',', ' ') }} FCFA
                                    @else
                                        <span class="text-muted-foreground">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($transaction->statut == 'valide')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">
                                            Validée
                                        </span>
                                    @elseif($transaction->statut == 'en_attente')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">
                                            En attente
                                        </span>
                                    @elseif($transaction->statut == 'annule')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-destructive">
                                            Annulée
                                        </span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-secondary">
                                            {{ ucfirst($transaction->statut) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center text-foreground font-normal">
                                    {{ $transaction->date->locale('fr')->isoFormat('D MMM Y, HH:mm') }}
                                </td>
                            </tr>
                            @empty
                            <tr class="empty-row">
                                <td colspan="8" class="text-center py-20 !border-0" style="width: 100% !important; padding: 5rem 0 !important; border: none !important; border-left: none !important; border-right: none !important;">
                                    <div class="flex flex-col items-center justify-center gap-5 w-full">
                                        <div class="flex items-center justify-center rounded-full bg-gray-100 size-20 dark:bg-gray-900">
                                            <i class="ki-filled ki-file text-4xl text-gray-500 dark:text-gray-400"></i>
                                        </div>
                                        <div class="flex flex-col gap-2 items-center text-center max-w-md">
                                            <h3 class="text-lg font-semibold text-foreground">Aucune transaction</h3>
                                            <p class="text-sm text-secondary-foreground">
                                                {{ count($filtresActifs) ? 'Aucune transaction ne correspond aux filtres sélectionnés.' : "Il n'y a actuellement aucune transaction dans le système." }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                    <form method="GET" action="{{ route('transactions.index') }}" class="flex items-center gap-2 order-2 md:order-1">
                        @foreach($paramsConserves(['per_page']) as $cle => $valeur)
                            <input type="hidden" name="{{ $cle }}" value="{{ $valeur }}"/>
                        @endforeach
                        Afficher
                        <select class="kt-select w-20" name="per_page" onchange="this.form.requestSubmit()">
                            @foreach([10, 20, 50, 100] as $n)
                                <option value="{{ $n }}" @selected($transactions->perPage() == $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                        par page
                    </form>
                    <div class="flex items-center gap-4 order-1 md:order-2">
                        <span>
                            {{ $transactions->total() ? $transactions->firstItem() . '-' . $transactions->lastItem() . ' sur ' . number_format($transactions->total(), 0, ',', ' ') : '0 résultat' }}
                        </span>
                        {{ $transactions->links('partials.pagination-metronic') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Container -->

<!-- Panneau de filtres (s'ouvre depuis la droite).
     Déplacé sous <body> au chargement : <main> est animé (transform), ce qui casserait position: fixed. -->
<div id="transactions_filtres_portail" data-transactions-filtres-portail>
<style>
    #content #transactions_filtres_portail {
        display: none;
    }
    .transactions-filtres-backdrop {
        position: fixed;
        inset: 0;
        z-index: 99;
        background: rgba(0, 0, 0, .35);
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease, visibility .25s ease;
    }
    .transactions-filtres-drawer {
        position: fixed;
        top: 0;
        bottom: 0;
        right: 0;
        z-index: 100;
        width: 380px;
        max-width: 90%;
        transform: translateX(100%);
        transition: transform .25s ease;
        box-shadow: -8px 0 24px rgba(0, 0, 0, .08);
    }
    body.transactions-filtres-ouvert .transactions-filtres-backdrop {
        opacity: 1;
        visibility: visible;
    }
    body.transactions-filtres-ouvert .transactions-filtres-drawer {
        transform: translateX(0);
    }
    body.transactions-filtres-ouvert {
        overflow: hidden;
    }
</style>
<div class="transactions-filtres-backdrop" data-transactions-filtres-close></div>
<aside class="transactions-filtres-drawer bg-background border-s border-border flex flex-col" id="transactions_filtres_drawer" aria-hidden="true">
    <div class="flex items-center justify-between gap-2.5 px-5 py-4 border-b border-border">
        <div class="flex items-center gap-2 text-base font-semibold text-mono">
            <i class="ki-filled ki-filter text-primary"></i>
            Filtrer les transactions
        </div>
        <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-transactions-filtres-close title="Fermer">
            <i class="ki-filled ki-cross"></i>
        </button>
    </div>
    <form id="transactions_filtres_form" method="GET" action="{{ route('transactions.index') }}" data-ajax="false" class="flex flex-col flex-1 min-h-0">
        @foreach(\Illuminate\Support\Arr::only(request()->query(), ['search', 'per_page']) as $cle => $valeur)
            <input type="hidden" name="{{ $cle }}" value="{{ $valeur }}"/>
        @endforeach
        <div class="flex-1 overflow-y-auto px-5 py-5 flex flex-col gap-6">
            <div class="flex flex-col gap-2.5">
                <span class="text-sm font-medium text-mono">Période</span>
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-secondary-foreground" for="filtre_date_debut">Du</label>
                        <input class="kt-input" type="date" id="filtre_date_debut" name="date_debut" value="{{ request('date_debut') }}"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-secondary-foreground" for="filtre_date_fin">Au</label>
                        <input class="kt-input" type="date" id="filtre_date_fin" name="date_fin" value="{{ request('date_fin') }}"/>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" class="kt-btn kt-btn-xs kt-btn-outline" data-filtre-periode="aujourdhui">Aujourd'hui</button>
                    <button type="button" class="kt-btn kt-btn-xs kt-btn-outline" data-filtre-periode="7j">7 derniers jours</button>
                    <button type="button" class="kt-btn kt-btn-xs kt-btn-outline" data-filtre-periode="mois">Ce mois</button>
                </div>
            </div>

            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-medium text-mono" for="filtre_agent">Agent</label>
                <select class="kt-select" id="filtre_agent" name="agent_id">
                    <option value="">Tous les agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected((string) request('agent_id') === (string) $agent->id)>
                            {{ $agent->nomComplet }}@if($agent->code_agent) ({{ $agent->code_agent }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-2.5">
                <span class="text-sm font-medium text-mono">Type</span>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input class="kt-radio kt-radio-sm" type="radio" name="type" value="" @checked(!request('type'))/>
                        Tous
                    </label>
                    @foreach($typesTransaction as $valeur => $libelle)
                        <label class="flex items-center gap-2 text-sm">
                            <input class="kt-radio kt-radio-sm" type="radio" name="type" value="{{ $valeur }}" @checked(request('type') === $valeur)/>
                            {{ $libelle }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5 px-5 py-4 border-t border-border">
            <button type="button" class="kt-btn kt-btn-outline flex-1 justify-center" data-transactions-filtres-reset>
                Réinitialiser
            </button>
        </div>
    </form>
</aside>
</div>
<style>
    /* S'assurer que le message vide est centré sur toute la largeur */
    #transactions_table tbody tr.empty-row {
        border: none !important;
        display: table-row !important;
    }
    
    #transactions_table tbody tr.empty-row td {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        border: none !important;
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: none !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        position: relative !important;
    }
    
    /* Masquer toutes les bordures des colonnes pour cette ligne */
    #transactions_table tbody tr.empty-row td,
    #transactions_table tbody tr.empty-row th {
        border: none !important;
    }
    
    /* Forcer le tableau à prendre toute la largeur pour cette ligne */
    #transactions_table {
        width: 100% !important;
    }
    
    #transactions_table tbody tr.empty-row td[colspan="8"] {
        display: table-cell !important;
        width: 100% !important;
    }
    
    /* Cacher le message par défaut du datatable si présent */
    #transactions_table tbody tr td:only-child:not(.empty-row td) {
        display: none;
    }
</style>
<script>
window.initTransactionsPage = function() {
    if (!document.getElementById('transactions_table')) return;

    if (window.AjaxNavigation && typeof window.AjaxNavigation.setupEmptyDatatable === 'function') {
        window.AjaxNavigation.setupEmptyDatatable('transactions_table', 8, @json($transactions->isEmpty()));
    }

    // Sortir le panneau de <main> (remplace l'éventuel panneau d'un chargement précédent)
    const portail = document.querySelector('#content [data-transactions-filtres-portail]');
    if (portail) {
        document.querySelectorAll('body > [data-transactions-filtres-portail]').forEach(ancien => ancien.remove());
        document.body.appendChild(portail);
    }
};

// Panneau de filtres : écouteurs délégués, attachés une seule fois (survivent à la navigation AJAX)
if (!window._transactionsFiltresBound) {
    window._transactionsFiltresBound = true;

    const ouvrir = () => document.body.classList.add('transactions-filtres-ouvert');
    const fermer = () => document.body.classList.remove('transactions-filtres-ouvert');
    const isoDate = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

    // Le panneau reste ouvert pendant l'application automatique des filtres
    let garderOuvert = false;
    let minuterie = null;

    // Filtres cumulatifs : seuls les champs renseignés partent dans l'URL
    const appliquer = (form) => {
        const debut = form.querySelector('[name="date_debut"]');
        const fin = form.querySelector('[name="date_fin"]');
        if (debut.value && fin.value && debut.value > fin.value) {
            fin.setCustomValidity('La date de fin doit être postérieure à la date de début.');
            form.reportValidity();
            fin.setCustomValidity('');
            return;
        }
        const url = new URL(form.action, window.location.origin);
        for (const [cle, valeur] of new FormData(form).entries()) {
            if (valeur !== '') url.searchParams.set(cle, valeur);
        }
        garderOuvert = document.body.classList.contains('transactions-filtres-ouvert');
        if (window.AjaxNavigation && typeof window.AjaxNavigation.loadPage === 'function') {
            window.AjaxNavigation.loadPage(url.toString());
        } else {
            window.location.href = url.toString();
        }
    };

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-transactions-filtres-open]')) {
            ouvrir();
            return;
        }
        if (e.target.closest('[data-transactions-filtres-close]')) {
            fermer();
            return;
        }

        const form = document.getElementById('transactions_filtres_form');
        if (!form) return;

        if (e.target.closest('[data-transactions-filtres-reset]')) {
            form.querySelectorAll('input[type="date"], select').forEach(champ => { champ.value = ''; });
            const tous = form.querySelector('input[name="type"][value=""]');
            if (tous) tous.checked = true;
            appliquer(form);
            return;
        }

        const raccourci = e.target.closest('[data-filtre-periode]');
        if (raccourci) {
            const fin = new Date();
            let debut = new Date();
            if (raccourci.dataset.filtrePeriode === '7j') debut.setDate(fin.getDate() - 6);
            if (raccourci.dataset.filtrePeriode === 'mois') debut = new Date(fin.getFullYear(), fin.getMonth(), 1);
            form.querySelector('[name="date_debut"]').value = isoDate(debut);
            form.querySelector('[name="date_fin"]').value = isoDate(fin);
            appliquer(form);
        }
    });

    document.addEventListener('submit', (e) => {
        if (e.target.id !== 'transactions_filtres_form') return;
        e.preventDefault();
        appliquer(e.target);
    });

    // Application automatique dès qu'un filtre change (léger délai pour la saisie des dates)
    document.addEventListener('change', (e) => {
        const form = e.target.closest('#transactions_filtres_form');
        if (!form) return;
        clearTimeout(minuterie);
        minuterie = setTimeout(() => appliquer(form), e.target.type === 'date' ? 500 : 0);
    });

    document.addEventListener('change', (e) => {
        const table = document.getElementById('transactions_table');
        if (!table || !table.contains(e.target)) return;
        const lignes = [...table.querySelectorAll('[data-kt-datatable-row-check]')];
        const toutCocher = table.querySelector('[data-transactions-check-all]');
        if (e.target === toutCocher) {
            lignes.forEach(caseLigne => { caseLigne.checked = toutCocher.checked; });
        } else if (toutCocher && e.target.matches('[data-kt-datatable-row-check]')) {
            const cochees = lignes.filter(caseLigne => caseLigne.checked).length;
            toutCocher.checked = cochees === lignes.length;
            toutCocher.indeterminate = cochees > 0 && cochees < lignes.length;
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') fermer();
    });

    // Changement de page : fermer le panneau et le supprimer si on a quitté les transactions
    document.addEventListener('ajax-content-loaded', () => {
        if (!garderOuvert || !document.getElementById('transactions_table')) fermer();
        garderOuvert = false;
        if (!document.getElementById('transactions_table')) {
            document.querySelectorAll('body > [data-transactions-filtres-portail]').forEach(ancien => ancien.remove());
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.initTransactionsPage());
} else {
    window.initTransactionsPage();
}

document.addEventListener('ajax-content-loaded', () => {
    if (document.getElementById('transactions_table')) {
        window.initTransactionsPage();
    }
});
</script>
@endsection
