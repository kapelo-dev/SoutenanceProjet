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
            <a class="kt-btn kt-btn-outline" href="#">
                <i class="ki-filled ki-file-down"></i>
                Exporter
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->
<!-- Container -->
<div class="kt-container-fixed">
    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header py-5 flex-wrap gap-2">
            <div class="flex items-center gap-5 ml-auto">
                <label class="kt-input">
                    <i class="ki-filled ki-magnifier"></i>
                    <input data-kt-datatable-search="#transactions_table" placeholder="Rechercher une transaction" type="text" value=""/>
                </label>
                
            </div>

        </div>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="transactions_table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="w-[50px] text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox"/>
                                </th>
                                <th class="min-w-[160px] text-center" style="width: 14%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            N° Transaction
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[180px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Client
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[180px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Agent
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[120px] text-center" style="width: 12%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Montant
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[100px] text-center" style="width: 10%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Commission
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[120px] text-center" style="width: 12%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Statut
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[160px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Date
                                        </span>
                                        <span class="kt-table-col-sort"></span>
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
                                        <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="{{ route('transactions.show', $transaction->id) }}">
                                            {{ $transaction->reference }}
                                        </a>
                                        <span class="text-xs text-secondary-foreground font-normal">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2.5">
                                        @if($transaction->operateur)
                                            @php
                                                $operateurLogo = $transaction->operateur->logo
                                                    ? asset('storage/' . $transaction->operateur->logo)
                                                    : null;
                                            @endphp
                                            @if($operateurLogo)
                                                <img class="h-8 w-8 rounded-full object-cover flex-shrink-0" src="{{ $operateurLogo }}" alt="{{ $transaction->operateur->libelle }}"/>
                                            @else
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-[10px] font-semibold text-white flex-shrink-0"
                                                     style="background-color: {{ $transaction->operateur->couleur ?? '#6366f1' }};">
                                                    {{ strtoupper(substr($transaction->operateur->code, 0, 2)) }}
                                                </div>
                                            @endif
                                        @endif
                                        <div class="flex flex-col gap-0.5 items-start max-w-[180px]">
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
                                            <p class="text-sm text-secondary-foreground">Il n'y a actuellement aucune transaction dans le système.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                    <div class="flex items-center gap-2 order-2 md:order-1">
                        Afficher
                        <select class="kt-select w-16" data-kt-datatable-size="true" data-kt-select="" name="perpage">
                        </select>
                        par page
                    </div>
                    <div class="flex items-center gap-4 order-1 md:order-2">
                        <span data-kt-datatable-info="true">
                        </span>
                        <div class="kt-datatable-pagination" data-kt-datatable-pagination="true">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Container -->
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
    // S'assurer que le message vide reste centré après l'initialisation du datatable
    document.addEventListener('DOMContentLoaded', function() {
        const emptyRow = document.querySelector('#transactions_table tbody tr.empty-row');
        if (emptyRow) {
            const td = emptyRow.querySelector('td');
            if (td && td.getAttribute('colspan') !== '8') {
                td.setAttribute('colspan', '8');
                td.style.width = '100%';
                td.style.border = 'none';
            }
            // S'assurer que la ligne vide est visible
            emptyRow.style.display = 'table-row';
        }
        
        // Observer les changements du DOM pour maintenir le colspan
        const observer = new MutationObserver(function(mutations) {
            const emptyRow = document.querySelector('#transactions_table tbody tr.empty-row');
            if (emptyRow) {
                const td = emptyRow.querySelector('td');
                if (td && td.getAttribute('colspan') !== '8') {
                    td.setAttribute('colspan', '8');
                    td.style.width = '100%';
                    td.style.border = 'none';
                }
                // S'assurer que la ligne vide reste visible
                emptyRow.style.display = 'table-row';
            }
        });
        
        const table = document.getElementById('transactions_table');
        if (table) {
            observer.observe(table, { childList: true, subtree: true });
        }
        
        // Désactiver le datatable si aucune transaction
        @if($transactions->isEmpty())
        // Si pas de transactions, ne pas initialiser le datatable
        const datatableElement = document.querySelector('[data-kt-datatable="true"]');
        if (datatableElement) {
            // Empêcher l'initialisation du datatable
            datatableElement.removeAttribute('data-kt-datatable');
        }
        @endif
    });
</script>
@endsection
