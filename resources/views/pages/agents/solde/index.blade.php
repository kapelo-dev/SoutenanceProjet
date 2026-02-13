@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Gestion des Soldes
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Vue d'ensemble des soldes de tous les agents.
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('agents.solde.export', request()->all()) }}" class="kt-btn kt-btn-outline" data-ajax="false" target="_blank">
                <img src="{{ asset('assets/media/app/pdf-icon.svg') }}" alt="PDF" class="w-5 h-5 inline-block mr-2" />
                Exporter en PDF
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
                    <input data-kt-datatable-search="#soldes_table" placeholder="Rechercher un agent" type="text" value=""/>
                </label>
                
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="soldes_table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="w-[50px] text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox"/>
                                </th>
                                <th class="min-w-[200px]" style="width: 22%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Agent
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[140px]" style="width: 14%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Montant Initial
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[130px]" style="width: 13%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Espèce
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[160px]" style="width: 16%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Montant Virtuel
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[130px]" style="width: 13%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Commissions
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[160px]" style="width: 16%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Dernière MAJ
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agents as $agent)
                            @php
                                $soldeEspece = $agent->soldes->where('type', 'espece')->first();
                                $soldesVirtuels = $agent->soldes->where('type', 'virtuel');
                                $totalVirtuel = $soldesVirtuels->sum('montant');
                                $soldeTotal = ($soldeEspece ? $soldeEspece->montant : 0) + $totalVirtuel;
                                $derniereMaj = $agent->soldes->max('date') ?? $agent->updated_at;
                                $commissions = $agent->transactions()->where('statut', 'valide')->sum('commission') ?? 0;
                                
                                // Déterminer le statut de l'agent pour le badge
                                $statutBadge = 'success'; // Actif par défaut
                                if ($agent->statut == 'suspendu') {
                                    $statutBadge = 'destructive';
                                } elseif ($agent->statut == 'en_attente') {
                                    $statutBadge = 'warning';
                                } elseif ($agent->statut == 'inactif') {
                                    $statutBadge = 'secondary';
                                }
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="{{ $agent->id }}"/>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="relative">
                                            <img class="h-9 w-9 rounded-full object-cover" src="{{ $agent->utilisateur && $agent->utilisateur->photo_profil ? asset('storage/' . $agent->utilisateur->photo_profil) : asset('assets/media/avatars/300-3.png') }}"/>
                                            <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5">
                                                @if($agent->statut == 'actif')
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success border border-white"></span>
                                                @elseif($agent->statut == 'en_attente')
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-warning border border-white"></span>
                                                @elseif($agent->statut == 'suspendu')
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-destructive border border-white"></span>
                                                @else
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary border border-white"></span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="javascript:void(0)" onclick="loadAgentDetails({{ $agent->id }})">
                                                {{ $agent->nomComplet }}
                                            </a>
                                            <span class="text-xs text-secondary-foreground font-normal">
                                                {{ $agent->code_agent ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-foreground font-semibold">
                                    {{ number_format($agent->montant_initial_total ?? 0, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="text-warning font-semibold">
                                    {{ number_format($soldeEspece ? $soldeEspece->montant : 0, 0, ',', ' ') }} FCFA
                                </td>
                                <td>
                                    @if($soldesVirtuels->count() > 0)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs text-primary font-medium">{{ number_format($totalVirtuel, 0, ',', ' ') }} FCFA</span>
                                            @if($soldesVirtuels->count() > 1)
                                            <button class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost" onclick="toggleDropdown(this)">
                                                <i class="ki-filled ki-down text-xs transition-transform duration-200"></i>
                                            </button>
                                            @endif
                                        </div>
                                        <div class="hidden dropdown-content mt-1 border-t border-border pt-1">
                                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs">
                                                @foreach($soldesVirtuels as $soldeVirtuel)
                                                <div class="flex items-center gap-1.5 whitespace-nowrap">
                                                    @if($soldeVirtuel->operateur)
                                                        @if($soldeVirtuel->operateur->logo)
                                                            <span class="h-4 w-4 shrink-0 rounded-full overflow-hidden flex items-center justify-center bg-muted">
                                                                <img src="{{ asset('storage/' . $soldeVirtuel->operateur->logo) }}" alt="{{ $soldeVirtuel->operateur->libelle }}" class="h-full w-full object-cover" />
                                                            </span>
                                                        @else
                                                            <span class="h-4 w-4 rounded-full flex items-center justify-center text-[10px] font-semibold text-white shrink-0" style="background-color: {{ $soldeVirtuel->operateur->couleur ?? '#6b7280' }}">
                                                                {{ strtoupper(substr($soldeVirtuel->operateur->libelle ?? '', 0, 1)) }}
                                                            </span>
                                                        @endif
                                                        <span class="text-primary">{{ $soldeVirtuel->operateur->libelle }}</span>
                                                        <span class="font-medium text-foreground">{{ number_format($soldeVirtuel->montant, 0, ',', ' ') }} FCFA</span>
                                                    @else
                                                        <span class="text-primary">N/A</span>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-xs text-primary font-medium">0 FCFA</span>
                                    @endif
                                </td>
                                <td class="text-foreground font-normal">
                                    {{ number_format($commissions, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="text-foreground text-sm font-normal">
                                    {{ $derniereMaj->locale('fr')->isoFormat('D MMM Y, HH:mm') }}
                                </td>
                                
                                 
                                
                            </tr>
                            @empty
                            <tr class="empty-row">
                                <td colspan="8" class="text-center py-20 !border-0" style="width: 100% !important; padding: 5rem 0 !important; border: none !important; border-left: none !important; border-right: none !important;">
                                    <div class="flex flex-col items-center justify-center gap-5 w-full">
                                        <div class="flex items-center justify-center rounded-full bg-gray-100 size-20 dark:bg-gray-900">
                                            <i class="ki-filled ki-wallet text-4xl text-gray-500 dark:text-gray-400"></i>
                                        </div>
                                        <div class="flex flex-col gap-2 items-center text-center max-w-md">
                                            <h3 class="text-lg font-semibold text-foreground">Aucun agent trouvé</h3>
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
    #soldes_table tbody tr.empty-row {
        border: none !important;
        display: table-row !important;
    }
    
    #soldes_table tbody tr.empty-row td {
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
    #soldes_table tbody tr.empty-row td,
    #soldes_table tbody tr.empty-row th {
        border: none !important;
    }
    
    /* Forcer le tableau à prendre toute la largeur pour cette ligne */
    #soldes_table {
        width: 100% !important;
    }
    
    #soldes_table tbody tr.empty-row td[colspan="8"] {
        display: table-cell !important;
        width: 100% !important;
    }
    
    /* Cacher le message par défaut du datatable si présent */
    #soldes_table tbody tr td:only-child:not(.empty-row td) {
        display: none;
    }
</style>
<script>
    function toggleDropdown(button) {
        const dropdown = button.closest('.flex.flex-col').querySelector('.dropdown-content');
        const icon = button.querySelector('i');
        
        if (dropdown) {
            dropdown.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        }
    }
</script>
@endsection
