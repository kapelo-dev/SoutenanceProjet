@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Gestion des Agents
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Vue d'ensemble de tous les agents.
            </div>
        </div>      
        <div class="flex items-center gap-2.5">
        
            <a class="kt-btn kt-btn-outline" href="#">
                <i class="ki-filled ki-file-down"></i>
                Exporter
            </a>
        
            <button class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouvel_agent">
                <i class="ki-filled ki-plus"></i>
                Nouvel Agent
            </button>
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
                    <input data-kt-datatable-search="#agents_table" placeholder="Rechercher un agent" type="text" value=""/>
                </label>
                
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="agents_table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="w-[50px] text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox"/>
                                </th>
                                <th class="min-w-[220px]" style="width: 28%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Agent
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[140px]" style="width: 15%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Code Agent
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[150px]" style="width: 16%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Téléphone
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[120px]" style="width: 12%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Statut
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[160px]" style="width: 17%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Date d'ajout
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="w-[50px]"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agents as $agent)
                            <tr>
                                <td class="text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="{{ $agent->id }}"/>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="">
                                            <img class="h-9 rounded-full" src="{{ $agent->utilisateur && $agent->utilisateur->photo_profil ? asset('storage/' . $agent->utilisateur->photo_profil) : asset('assets/media/avatars/300-3.png') }}"/>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="javascript:void(0)" onclick="loadAgentDetails({{ $agent->id }})">
                                                {{ $agent->nomComplet }}
                                            </a>
                                            @if($agent->utilisateur)
                                            <span class="text-xs text-secondary-foreground font-normal">
                                                {{ $agent->utilisateur->email }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="leading-none font-medium text-sm text-mono">
                                            {{ $agent->code_agent ?? 'N/A' }}
                                        </span>
                                        @if($agent->kiosque)
                                        <span class="text-xs text-secondary-foreground font-normal">
                                            {{ $agent->kiosque->nom }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-foreground font-normal">
                                    {{ $agent->telephone }}
                                </td>
                                <td>
                                    @if($agent->statut == 'actif')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">
                                            Actif
                                        </span>
                                    @elseif($agent->statut == 'en_attente')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">
                                            En attente
                                        </span>
                                    @elseif($agent->statut == 'suspendu')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-destructive">
                                            Suspendu
                                        </span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-secondary">
                                            Inactif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-foreground font-normal">
                                    {{ $agent->created_at->locale('fr')->isoFormat('D MMM Y') }}
                                </td>
                                <td>
                                    <div class="kt-menu" data-kt-menu="true">
                                        <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px"
                                            data-kt-menu-item-placement="bottom-end"
                                            data-kt-menu-item-placement-rtl="bottom-start"
                                            data-kt-menu-item-toggle="dropdown"
                                            data-kt-menu-item-trigger="click">
                                            <button type="button" class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="kt-menu-dropdown kt-menu-default w-full max-w-[200px]" data-kt-menu-dismiss="true">
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link view-agent" 
                                                        href="javascript:void(0)" 
                                                        data-id="{{ $agent->id }}">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-eye"></i>
                                                        </span>
                                                        <span class="kt-menu-title">
                                                            Voir
                                                        </span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-separator"></div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link edit-agent" 
                                                        href="javascript:void(0)" 
                                                        data-id="{{ $agent->id }}">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-pencil"></i>
                                                        </span>
                                                        <span class="kt-menu-title">
                                                            Modifier
                                                        </span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-separator"></div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="#" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer cet agent ?')) { document.getElementById('delete-form-{{ $agent->id }}').submit(); }">
                                                        <span class="kt-menu-icon">
                                                            <i class="ki-filled ki-trash"></i>
                                                        </span>
                                                        <span class="kt-menu-title">
                                                            Supprimer
                                                        </span>
                                                    </a>
                                                    <form id="delete-form-{{ $agent->id }}" action="{{ route('agents.destroy', $agent->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr class="empty-row">
                                <td colspan="7" class="text-center py-20 !border-0" style="width: 100% !important; padding: 5rem 0 !important; border: none !important; border-left: none !important; border-right: none !important;">
                                    <div class="flex flex-col items-center justify-center gap-5 w-full">
                                        <div class="flex items-center justify-center rounded-full bg-gray-100 size-20 dark:bg-gray-900">
                                            <i class="ki-filled ki-user text-4xl text-gray-500 dark:text-gray-400"></i>
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
    #agents_table tbody tr.empty-row {
        border: none !important;
        display: table-row !important;
    }
    
    #agents_table tbody tr.empty-row td {
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
    #agents_table tbody tr.empty-row td,
    #agents_table tbody tr.empty-row th {
        border: none !important;
    }
    
    /* Forcer le tableau à prendre toute la largeur pour cette ligne */
    #agents_table {
        width: 100% !important;
    }
    
    #agents_table tbody tr.empty-row td[colspan="7"] {
        display: table-cell !important;
        width: 100% !important;
    }
    
    /* Cacher le message par défaut du datatable si présent */
    #agents_table tbody tr td:only-child:not(.empty-row td) {
        display: none;
    }
    
    /* Menu déroulant agents : même style que page Opérateurs */
    #agents_table .kt-menu-dropdown {
        display: none !important;
        position: fixed !important;
        z-index: 99999 !important;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        min-width: 200px;
        padding: 0.5rem 0;
    }
    #agents_table .kt-menu-dropdown.show { display: block !important; }
    #agents_table tbody tr.agents-row-menu-open { position: relative; z-index: 10000 !important; }
    .dark #agents_table .kt-menu-dropdown { background: #1f2937; border-color: #374151; }
    
    /* Conteneurs : ne pas créer de clipping sur le dropdown (il est en fixed) */
    .kt-card,
    .kt-card-content {
        position: relative;
        z-index: auto;
    }
    /* Le dropdown est en position: fixed donc il n'est pas coupé par le scroll */
    
    /* Le modal doit avoir un z-index très élevé pour être au-dessus de tout */
    .kt-modal,
    #modal_view_agent {
        z-index: 100000 !important;
    }
    
    .kt-modal-content {
        z-index: 100001 !important;
    }
    
    /* Mode sombre - uniquement les menus du tableau agents (pas la sidebar) */
    #agents_table .dark .kt-menu-dropdown {
        background: #1f2937;
        border-color: #374151;
    }
    
    #agents_table .kt-menu-item {
        position: relative;
    }
    
    #agents_table .kt-menu-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: #374151;
        text-decoration: none;
        transition: color 0.15s ease;
        cursor: pointer;
    }
    
    #agents_table .dark .kt-menu-link {
        color: #d1d5db;
    }
    
    #agents_table .kt-menu-icon {
        margin-right: 0.75rem;
        display: flex;
        align-items: center;
    }
    
    #agents_table .kt-menu-separator {
        height: 1px;
        background-color: #e5e7eb;
        margin: 0.25rem 0;
    }
    
    #agents_table .dark .kt-menu-separator {
        background-color: #374151;
    }
</style>

<!-- Modal Nouvel Agent avec Kiosque -->
<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_nouvel_agent">
    <div class="kt-modal-content max-w-[900px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                Nouvel Agent
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-0">
            <!-- Onglets -->
            <div class="kt-tabs kt-tabs-line border-b border-border px-5" data-kt-tabs="true">
                <div class="flex items-center gap-5">
                    <button class="kt-tab-toggle py-4 active" data-kt-tab-toggle="#tab_agent">
                        Informations Agent
                    </button>
                    <button class="kt-tab-toggle py-4" data-kt-tab-toggle="#tab_kiosque">
                        Configuration Kiosque
                    </button>
                    <button class="kt-tab-toggle py-4" data-kt-tab-toggle="#tab_montants">
                        Montants Initiaux
                    </button>
                </div>
            </div>
            
            <form id="form_nouvel_agent_kiosque" class="flex flex-col" enctype="multipart/form-data">
                <!-- Onglet Agent -->
                <div id="tab_agent" class="kt-tab-content p-5 active">
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Code Agent <span class="text-destructive">*</span>
                                </label>
                                <input class="kt-input" type="text" name="code_agent" id="agent_code" placeholder="Ex: AG0001" required />
                                <span class="text-xs text-secondary-foreground">Code unique pour identifier l'agent</span>
                                <span class="text-xs text-destructive hidden" id="error_code_agent"></span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Statut <span class="text-destructive">*</span>
                                </label>
                                <select class="kt-select" name="statut" id="agent_statut" data-kt-select="true" required>
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                    <option value="en_attente">En attente</option>
                                    <option value="suspendu">Suspendu</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Nom <span class="text-destructive">*</span>
                                </label>
                                <input class="kt-input" type="text" name="nom" id="agent_nom" placeholder="Ex: Doe" required />
                                <span class="text-xs text-destructive hidden" id="error_nom"></span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Prénom <span class="text-destructive">*</span>
                                </label>
                                <input class="kt-input" type="text" name="prenom" id="agent_prenom" placeholder="Ex: John" required />
                                <span class="text-xs text-destructive hidden" id="error_prenom"></span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Téléphone <span class="text-destructive">*</span>
                            </label>
                            <input class="kt-input" type="text" name="telephone" id="agent_telephone" placeholder="Ex: +228 90 12 34 56" required />
                            <span class="text-xs text-destructive hidden" id="error_telephone"></span>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Email <span class="text-destructive">*</span>
                            </label>
                            <input class="kt-input" type="email" name="email" id="agent_email" placeholder="Ex: agent@example.com" required />
                            <span class="text-xs text-secondary-foreground">Un utilisateur sera créé automatiquement avec cet email</span>
                            <span class="text-xs text-destructive hidden" id="error_email"></span>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Photo de profil
                            </label>
                            <div class="flex items-center gap-5">
                                <div id="agent_photo_preview" class="flex-shrink-0">
                                    <div class="h-20 w-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <i class="ki-filled ki-user text-2xl text-gray-400"></i>
                                    </div>
                                </div>
                                <input class="kt-input flex-1" type="file" name="photo" id="agent_photo" accept="image/*" />
                            </div>
                            <span class="text-xs text-secondary-foreground">Format: JPEG, PNG, JPG (max 2MB)</span>
                            <span class="text-xs text-destructive hidden" id="error_photo"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Onglet Kiosque -->
                <div id="tab_kiosque" class="kt-tab-content p-5 hidden">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2 mb-2">
                            <input class="kt-switch" type="checkbox" name="creer_kiosque" id="creer_kiosque" value="1" />
                            <label class="kt-label" for="creer_kiosque">
                                Créer un nouveau kiosque pour cet agent
                            </label>
                        </div>
                        
                        <div id="kiosque_form" class="hidden flex flex-col gap-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Code Kiosque <span class="text-destructive">*</span>
                                    </label>
                                    <input class="kt-input" type="text" name="kiosque_code" id="kiosque_code" placeholder="Ex: KIO001" />
                                    <span class="text-xs text-destructive hidden" id="error_kiosque_code"></span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Nom du Kiosque <span class="text-destructive">*</span>
                                    </label>
                                    <input class="kt-input" type="text" name="kiosque_nom" id="kiosque_nom" placeholder="Ex: Kiosque Centre-Ville" />
                                    <span class="text-xs text-destructive hidden" id="error_kiosque_nom"></span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Adresse <span class="text-destructive">*</span>
                                    </label>
                                    <input class="kt-input" type="text" name="kiosque_adresse" id="kiosque_adresse" placeholder="Ex: Rue 123" />
                                    <span class="text-xs text-destructive hidden" id="error_kiosque_adresse"></span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Quartier
                                    </label>
                                    <input class="kt-input" type="text" name="kiosque_quartier" id="kiosque_quartier" placeholder="Ex: Quartier Administratif" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Ville <span class="text-destructive">*</span>
                                    </label>
                                    <input class="kt-input" type="text" name="kiosque_ville" id="kiosque_ville" placeholder="Ex: Lomé" />
                                    <span class="text-xs text-destructive hidden" id="error_kiosque_ville"></span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Téléphone
                                    </label>
                                    <input class="kt-input" type="text" name="kiosque_telephone" id="kiosque_telephone" placeholder="Ex: +228 90 12 34 56" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Type
                                    </label>
                                    <select class="kt-select" name="kiosque_type" id="kiosque_type" data-kt-select="true">
                                        <option value="fixe">Fixe</option>
                                        <option value="mobile">Mobile</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Capacité Agents
                                    </label>
                                    <input class="kt-input" type="number" name="kiosque_capacite" id="kiosque_capacite" placeholder="5" min="1" value="1" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Horaire Ouverture
                                    </label>
                                    <input class="kt-input" type="time" name="kiosque_horaire_ouverture" id="kiosque_horaire_ouverture" value="08:00" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label">
                                        Horaire Fermeture
                                    </label>
                                    <input class="kt-input" type="time" name="kiosque_horaire_fermeture" id="kiosque_horaire_fermeture" value="18:00" />
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Description
                                </label>
                                <textarea class="kt-input" rows="3" name="kiosque_description" id="kiosque_description" placeholder="Description du kiosque..."></textarea>
                            </div>
                            
                            <!-- Carte OpenStreetMap -->
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Localisation GPS <span class="text-destructive">*</span>
                                </label>
                                <div class="flex gap-2 mb-2">
                                    <input class="kt-input flex-1" type="number" name="kiosque_latitude" id="kiosque_latitude" placeholder="Latitude" step="any" />
                                    <input class="kt-input flex-1" type="number" name="kiosque_longitude" id="kiosque_longitude" placeholder="Longitude" step="any" />
                                    <button type="button" class="kt-btn kt-btn-outline" onclick="geolocaliser()">
                                        <i class="ki-filled ki-geolocation"></i>
                                        Ma position
                                    </button>
                                </div>
                                <div id="map" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid var(--border);"></div>
                                <span class="text-xs text-secondary-foreground">Cliquez sur la carte ou déplacez le marqueur pour définir l'emplacement. Adresse, quartier et ville seront remplis automatiquement.</span>
                                <span class="text-xs text-destructive hidden" id="error_kiosque_location"></span>
                            </div>
                        </div>
                        
                        <div id="kiosque_existant" class="flex flex-col gap-2">
                            <label class="kt-label">
                                Kiosque existant
                            </label>
                            <select class="kt-select" name="kiosque_id" id="agent_kiosque_id" data-kt-select="true">
                                <option value="">Aucun kiosque</option>
                                @foreach($kiosques ?? [] as $kiosque)
                                    <option value="{{ $kiosque->id }}">{{ $kiosque->nom }} - {{ $kiosque->adresse }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Onglet Montants Initiaux -->
                <div id="tab_montants" class="kt-tab-content p-5 hidden">
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Espèce Initiale
                            </label>
                            <input class="kt-input" type="number" name="espece_initiale" id="agent_espece_initiale" placeholder="0" min="0" step="0.01" />
                            <span class="text-xs text-secondary-foreground">Montant en espèces physiques</span>
                        </div>
                        
                        <!-- Montants virtuels par opérateur -->
                        <div class="flex flex-col gap-3 border-t border-border pt-4">
                            <label class="kt-label font-semibold">
                                Montants Virtuels par Opérateur Mobile Money
                            </label>
                            <span class="text-xs text-secondary-foreground mb-2">Définir le montant virtuel initial pour chaque opérateur</span>
                            @foreach($operateurs ?? [] as $operateur)
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    {{ $operateur->libelle }} ({{ $operateur->code }})
                                </label>
                                <input class="kt-input" type="number" name="montant_virtuel_{{ $operateur->id }}" id="montant_virtuel_{{ $operateur->id }}" placeholder="0" min="0" step="0.01" value="" />
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="kt-modal-footer">
            <button class="kt-btn kt-btn-ghost" data-kt-modal-dismiss="true">
                Annuler
            </button>
            <button class="kt-btn kt-btn-primary" id="btn_save_agent_kiosque" onclick="saveAgentWithKiosque()">
                <i class="ki-filled ki-check"></i>
                <span>Créer l'agent</span>
            </button>
        </div>
    </div>
</div>
<!-- End Modal Nouvel Agent avec Kiosque -->

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let marker;
let isCreatingKiosque = false;

// Exécuter le setup du modal (tabs + toggle kiosque) — appelé au chargement initial OU après navigation AJAX
function setupAgentModalOnce() {
    const modal = document.querySelector('#modal_nouvel_agent');
    if (!modal) return;
    // Éviter de ré-attacher les listeners aux tabs à chaque appel (éviter doublons après AJAX)
    if (modal._agentModalSetupDone) return;
    modal._agentModalSetupDone = true;

    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/26370817-2ad4-48a9-8621-53fe8856d785',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'liste_agents:setupAgentModalOnce',message:'running',data:{readyState:document.readyState},timestamp:Date.now(),sessionId:'debug-session',hypothesisId:'H3'})}).catch(function(){});
    // #endregion

    // Gestion manuelle des onglets Agent / Kiosque / Montants pour éviter que le contenu s'affiche sous le mauvais onglet
    const tabAgentBtn = document.querySelector('[data-kt-tab-toggle="#tab_agent"]');
    const tabKiosqueBtn = document.querySelector('[data-kt-tab-toggle="#tab_kiosque"]');
    const tabMontantsBtn = document.querySelector('[data-kt-tab-toggle="#tab_montants"]');
    const tabAgent = document.getElementById('tab_agent');
    const tabKiosque = document.getElementById('tab_kiosque');
    const tabMontants = document.getElementById('tab_montants');

    if (tabAgentBtn && tabKiosqueBtn && tabMontantsBtn && tabAgent && tabKiosque && tabMontants) {
        // Fonction pour activer un onglet et désactiver les autres
        function activateTab(activeBtn, activeTab) {
            // Désactiver tous les boutons et onglets
            [tabAgentBtn, tabKiosqueBtn, tabMontantsBtn].forEach(btn => btn.classList.remove('active'));
            [tabAgent, tabKiosque, tabMontants].forEach(tab => {
                tab.classList.add('hidden');
                tab.classList.remove('active');
            });
            
            // Activer le bouton et l'onglet sélectionnés
            activeBtn.classList.add('active');
            activeTab.classList.remove('hidden');
            activeTab.classList.add('active');
        }

        // État initial : onglet Agent visible
        activateTab(tabAgentBtn, tabAgent);

        tabAgentBtn.addEventListener('click', function (e) {
            e.preventDefault();
            activateTab(tabAgentBtn, tabAgent);
        });

        tabKiosqueBtn.addEventListener('click', function (e) {
            e.preventDefault();
            activateTab(tabKiosqueBtn, tabKiosque);

            const creerKiosqueCheckbox = document.getElementById('creer_kiosque');
            if (creerKiosqueCheckbox && creerKiosqueCheckbox.checked && !map) {
                setTimeout(function() {
                    initMap();
                }, 200);
            }
        });

        tabMontantsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            activateTab(tabMontantsBtn, tabMontants);
        });
    }

    // Toggle création kiosque — délégation sur document (une seule fois globalement)
    if (!window.__agentCreerKiosqueListenerAttached) {
        window.__agentCreerKiosqueListenerAttached = true;
        document.addEventListener('change', function (e) {
            const target = e.target;
            if (!target || target.id !== 'creer_kiosque') return;

            const kiosqueForm = document.getElementById('kiosque_form');
            const kiosqueExistant = document.getElementById('kiosque_existant');
            if (!kiosqueForm || !kiosqueExistant) return;

            isCreatingKiosque = target.checked;
            if (target.checked) {
                kiosqueForm.classList.remove('hidden');
                kiosqueExistant.classList.add('hidden');
                setTimeout(function () {
                    initMap();
                }, 300);
            } else {
                kiosqueForm.classList.add('hidden');
                kiosqueExistant.classList.remove('hidden');
                if (map) {
                    map.remove();
                    map = null;
                    marker = null;
                }
            }
        }, true);
    }

    // Réinitialiser le formulaire quand le modal se ferme
    modal.addEventListener('kt-modal-dismiss', function() {
        resetAgentModal();
    });
}

// Chargement initial : DOMContentLoaded a déjà pu avoir lieu (navigation AJAX)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAgentModalOnce);
} else {
    setupAgentModalOnce();
}
// Après injection AJAX, le contenu est déjà "loaded" donc on ré-attache au nouvel élément (flag par modal)
document.addEventListener('ajax-content-loaded', function() {
    if (document.querySelector('#modal_nouvel_agent')) {
        setupAgentModalOnce();
    }
});

function initMap() {
    // Vérifier que l'élément map existe et est visible
    const mapElement = document.getElementById('map');
    if (!mapElement || mapElement.offsetParent === null) {
        return; // L'élément n'existe pas ou n'est pas visible
    }
    
    // Détruire la carte existante si elle existe
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
    
    // Coordonnées par défaut (Lomé, Togo)
    const defaultLat = 6.1375;
    const defaultLng = 1.2123;
    
    // Initialiser la carte
    map = L.map('map').setView([defaultLat, defaultLng], 13);
    
    // Ajouter la couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Ajouter un marqueur par défaut
    marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    
    // Mettre à jour les champs latitude/longitude
    updateCoordinates(defaultLat, defaultLng);
    
    // Événement de clic sur la carte
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Déplacer le marqueur
        marker.setLatLng([lat, lng]);
        updateCoordinates(lat, lng);
    });
    
    // Événement de glissement du marqueur
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });
}

function updateKiosqueAddressFields(adresse, quartier, ville) {
    const adresseEl = document.getElementById('kiosque_adresse');
    const quartierEl = document.getElementById('kiosque_quartier');
    const villeEl = document.getElementById('kiosque_ville');
    if (adresseEl) adresseEl.value = adresse || '';
    if (quartierEl) quartierEl.value = quartier || '';
    if (villeEl) villeEl.value = ville || '';
}

function reverseGeocodeKiosque(lat, lng, callback) {
    const url = 'https://nominatim.openstreetmap.org/reverse?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng) + '&format=json&accept-language=fr';
    fetch(url, {
        headers: { 'Accept': 'application/json', 'User-Agent': 'PDVConnect-Agent/1.0' }
    }).then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data || !data.address) {
            if (callback) callback('', '', '');
            return;
        }
        var addr = data.address;
        var adresse = [addr.road, addr.house_number].filter(Boolean).join(' ') || addr.display_name.split(',')[0] || '';
        var quartier = addr.suburb || addr.neighbourhood || addr.quarter || addr.village || addr.district || '';
        var ville = addr.city || addr.town || addr.municipality || addr.state || addr.county || '';
        if (callback) callback(adresse, quartier, ville);
      })
      .catch(function() {
        if (callback) callback('', '', '');
      });
}

function updateCoordinates(lat, lng) {
    const latInput = document.getElementById('kiosque_latitude');
    const lngInput = document.getElementById('kiosque_longitude');
    if (latInput) latInput.value = lat.toFixed(8);
    if (lngInput) lngInput.value = lng.toFixed(8);
    reverseGeocodeKiosque(lat, lng, function(adresse, quartier, ville) {
        updateKiosqueAddressFields(adresse, quartier, ville);
    });
}

// Mettre à jour le marqueur quand les coordonnées sont modifiées manuellement
document.addEventListener('DOMContentLoaded', function() {
    const latInput = document.getElementById('kiosque_latitude');
    const lngInput = document.getElementById('kiosque_longitude');
    
    if (latInput && lngInput) {
        let updateTimeout;
        const updateMarker = function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(function() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    if (map && marker) {
                        marker.setLatLng([lat, lng]);
                        map.setView([lat, lng], map.getZoom());
                    }
                }
            }, 500);
        };
        
        latInput.addEventListener('input', updateMarker);
        lngInput.addEventListener('input', updateMarker);
    }
    
    // Prévisualisation de la photo de profil
    const photoInput = document.getElementById('agent_photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Vérifier la taille du fichier (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux. Taille maximale: 2MB');
                    this.value = '';
                    return;
                }
                
                // Vérifier le type de fichier
                if (!file.type.match('image.*')) {
                    alert('Veuillez sélectionner une image valide');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('agent_photo_preview');
                    if (preview) {
                        preview.innerHTML = `<img class="h-20 w-20 rounded-full object-cover border-2 border-border" src="${e.target.result}" alt="Photo de profil"/>`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

function geolocaliser() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            if (map) {
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);
                updateCoordinates(lat, lng);
            }
        }, function(error) {
            alert('Impossible d\'obtenir votre position. Veuillez sélectionner manuellement sur la carte.');
        });
    } else {
        alert('La géolocalisation n\'est pas supportée par votre navigateur.');
    }
}

window.resetAgentModal = function resetAgentModal() {
    document.getElementById('form_nouvel_agent_kiosque').reset();
    document.getElementById('creer_kiosque').checked = false;
    document.getElementById('kiosque_form').classList.add('hidden');
    document.getElementById('kiosque_existant').classList.remove('hidden');
    isCreatingKiosque = false;
    
    // Réinitialiser la prévisualisation de la photo
    const photoPreview = document.getElementById('agent_photo_preview');
    if (photoPreview) {
        photoPreview.innerHTML = '<div class="h-20 w-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"><i class="ki-filled ki-user text-2xl text-gray-400"></i></div>';
    }
    
    // Détruire la carte si elle existe
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
    
    // Réinitialiser les erreurs
    document.querySelectorAll('.text-destructive').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
}

window.saveAgentWithKiosque = function saveAgentWithKiosque() {
    console.log('saveAgentWithKiosque appelée');
    const form = document.getElementById('form_nouvel_agent_kiosque');
    const formData = new FormData(form);
    
    // Extraire les données pour validation
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'photo') {
            data[key] = value;
        }
    }
    
    // Validation Agent
    if (!data.code_agent || data.code_agent.trim() === '') {
        document.getElementById('error_code_agent').textContent = 'Le code agent est requis.';
        document.getElementById('error_code_agent').classList.remove('hidden');
        return;
    }
    
    if (!data.nom || data.nom.trim() === '') {
        document.getElementById('error_nom').textContent = 'Le nom est requis.';
        document.getElementById('error_nom').classList.remove('hidden');
        return;
    }
    
    if (!data.prenom || data.prenom.trim() === '') {
        document.getElementById('error_prenom').textContent = 'Le prénom est requis.';
        document.getElementById('error_prenom').classList.remove('hidden');
        return;
    }
    
    if (!data.telephone || data.telephone.trim() === '') {
        document.getElementById('error_telephone').textContent = 'Le téléphone est requis.';
        document.getElementById('error_telephone').classList.remove('hidden');
        return;
    }
    
    if (!data.email || data.email.trim() === '') {
        document.getElementById('error_email').textContent = 'L\'email est requis pour créer l\'utilisateur.';
        document.getElementById('error_email').classList.remove('hidden');
        return;
    }
    
    // Valider le format email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(data.email)) {
        document.getElementById('error_email').textContent = 'Veuillez entrer un email valide.';
        document.getElementById('error_email').classList.remove('hidden');
        return;
    }
    
    // Si création de kiosque
    if (data.creer_kiosque === '1') {
        if (!data.kiosque_code || data.kiosque_code.trim() === '') {
            document.getElementById('error_kiosque_code').textContent = 'Le code kiosque est requis.';
            document.getElementById('error_kiosque_code').classList.remove('hidden');
            return;
        }
        
        if (!data.kiosque_nom || data.kiosque_nom.trim() === '') {
            document.getElementById('error_kiosque_nom').textContent = 'Le nom du kiosque est requis.';
            document.getElementById('error_kiosque_nom').classList.remove('hidden');
            return;
        }
        
        if (!data.kiosque_adresse || data.kiosque_adresse.trim() === '') {
            document.getElementById('error_kiosque_adresse').textContent = 'L\'adresse est requise.';
            document.getElementById('error_kiosque_adresse').classList.remove('hidden');
            return;
        }
        
        if (!data.kiosque_ville || data.kiosque_ville.trim() === '') {
            document.getElementById('error_kiosque_ville').textContent = 'La ville est requise.';
            document.getElementById('error_kiosque_ville').classList.remove('hidden');
            return;
        }
        
        if (!data.kiosque_latitude || !data.kiosque_longitude) {
            document.getElementById('error_kiosque_location').textContent = 'Veuillez sélectionner un emplacement sur la carte.';
            document.getElementById('error_kiosque_location').classList.remove('hidden');
            return;
        }
    }
    
    // Nettoyer les données
    if (!data.user_id || data.user_id === '') {
        delete data.user_id;
    }
    
    
    // Préparer les données du kiosque si création
    if (data.creer_kiosque === '1') {
        data.kiosque = {
            code: data.kiosque_code,
            nom: data.kiosque_nom,
            adresse: data.kiosque_adresse,
            quartier: data.kiosque_quartier || null,
            ville: data.kiosque_ville,
            telephone: data.kiosque_telephone || null,
            latitude: parseFloat(data.kiosque_latitude),
            longitude: parseFloat(data.kiosque_longitude),
            type: data.kiosque_type || 'fixe',
            capacite_agents: parseInt(data.kiosque_capacite) || 5,
            horaire_ouverture: data.kiosque_horaire_ouverture || '08:00',
            horaire_fermeture: data.kiosque_horaire_fermeture || '18:00',
            description: data.kiosque_description || null,
            statut: 'actif'
        };
        // Supprimer kiosque_id si on crée un nouveau kiosque
        delete data.kiosque_id;
    } else {
        // Si on n'crée pas de kiosque, utiliser kiosque_id si fourni
        if (!data.kiosque_id || data.kiosque_id === '') {
            delete data.kiosque_id;
        }
    }
    
    // Supprimer les champs kiosque du data principal
    delete data.kiosque_code;
    delete data.kiosque_nom;
    delete data.kiosque_adresse;
    delete data.kiosque_quartier;
    delete data.kiosque_ville;
    delete data.kiosque_telephone;
    delete data.kiosque_latitude;
    delete data.kiosque_longitude;
    delete data.kiosque_type;
    delete data.kiosque_capacite;
    delete data.kiosque_horaire_ouverture;
    delete data.kiosque_horaire_fermeture;
    delete data.kiosque_description;
    
    // Désactiver le bouton
    const submitBtn = document.getElementById('btn_save_agent_kiosque');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Création...';
    
    // Utiliser FormData pour gérer les fichiers et les données
    const finalFormData = new FormData();
    
    // Ajouter tous les champs de base
    finalFormData.append('code_agent', data.code_agent);
    finalFormData.append('nom', data.nom);
    finalFormData.append('prenom', data.prenom);
    finalFormData.append('telephone', data.telephone);
    finalFormData.append('email', data.email);
    finalFormData.append('statut', data.statut);
    
    // Ajouter les montants initiaux si spécifiés
    if (data.espece_initiale && parseFloat(data.espece_initiale) > 0) {
        finalFormData.append('espece_initiale', data.espece_initiale);
    }
    
    // Ajouter les montants virtuels
    @if(isset($operateurs))
    @foreach($operateurs as $operateur)
    if (data['montant_virtuel_{{ $operateur->id }}'] && parseFloat(data['montant_virtuel_{{ $operateur->id }}']) > 0) {
        finalFormData.append('montant_virtuel_{{ $operateur->id }}', data['montant_virtuel_{{ $operateur->id }}']);
    }
    @endforeach
    @endif
    
    // Ajouter la photo si elle existe
    const photoInput = document.getElementById('agent_photo');
    if (photoInput && photoInput.files.length > 0) {
        finalFormData.append('photo', photoInput.files[0]);
    }
    
    // Ajouter les données du kiosque si création
    if (data.creer_kiosque === '1' && data.kiosque) {
        finalFormData.append('kiosque[code]', data.kiosque.code);
        finalFormData.append('kiosque[nom]', data.kiosque.nom);
        finalFormData.append('kiosque[adresse]', data.kiosque.adresse);
        if (data.kiosque.quartier) finalFormData.append('kiosque[quartier]', data.kiosque.quartier);
        finalFormData.append('kiosque[ville]', data.kiosque.ville);
        if (data.kiosque.telephone) finalFormData.append('kiosque[telephone]', data.kiosque.telephone);
        finalFormData.append('kiosque[latitude]', data.kiosque.latitude);
        finalFormData.append('kiosque[longitude]', data.kiosque.longitude);
        finalFormData.append('kiosque[type]', data.kiosque.type);
        finalFormData.append('kiosque[capacite_agents]', data.kiosque.capacite_agents);
        finalFormData.append('kiosque[horaire_ouverture]', data.kiosque.horaire_ouverture);
        finalFormData.append('kiosque[horaire_fermeture]', data.kiosque.horaire_fermeture);
        if (data.kiosque.description) finalFormData.append('kiosque[description]', data.kiosque.description);
        finalFormData.append('kiosque[statut]', data.kiosque.statut);
    } else if (data.kiosque_id) {
        finalFormData.append('kiosque_id', data.kiosque_id);
    }
    
    fetch('{{ route("agents.store-with-kiosque") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: finalFormData
    })
    .then(response => {
        // Vérifier le type de contenu
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Réponse non-JSON reçue:', text);
                throw new Error('Le serveur a renvoyé une réponse non-JSON. Vérifiez les logs du serveur.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Fermer le modal
            const modal = document.querySelector('#modal_nouvel_agent');
            if (modal) {
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                }
            }
            
            resetAgentModal();
            
            // Afficher un message avec les informations de l'utilisateur créé
            if (data.utilisateur) {
                const message = 'Agent créé avec succès!\n\n' +
                    'Informations de connexion:\n' +
                    'Email: ' + data.utilisateur.email + '\n' +
                    'Mot de passe: ' + data.utilisateur.mot_de_passe + '\n\n' +
                    'Veuillez noter ces informations et les communiquer à l\'agent.';
                alert(message);
            }
            
            // Recharger la page
            window.location.reload();
        } else {
            // Afficher les erreurs
            console.error('Erreurs de validation:', data.errors);
            
            if (data.errors) {
                // Réinitialiser toutes les erreurs d'abord
                document.querySelectorAll('.text-destructive').forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });
                
                Object.keys(data.errors).forEach(field => {
                    // Gérer les erreurs de kiosque (kiosque.code, kiosque.nom, etc.)
                    const fieldName = field.startsWith('kiosque.') ? 'kiosque_' + field.replace('kiosque.', '') : field;
                    const errorElement = document.getElementById('error_' + fieldName.replace(/\./g, '_'));
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.classList.remove('hidden');
                    } else {
                        // Si l'élément d'erreur n'existe pas, afficher dans la console et dans une alerte
                        console.warn('Élément d\'erreur introuvable pour le champ:', field, 'Valeur:', data.errors[field][0]);
                    }
                });
                
                // Afficher un message général si des erreurs existent
                if (Object.keys(data.errors).length > 0) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    alert('Erreurs de validation:\n\n' + errorMessages);
                }
            } else if (data.message) {
                alert('Erreur: ' + data.message);
            } else {
                alert('Une erreur est survenue lors de la création de l\'agent.');
            }
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        let errorMessage = 'Une erreur est survenue lors de la création.';
        if (error.message) {
            errorMessage += '\n\n' + error.message;
        }
        alert(errorMessage);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Variable globale pour la carte Leaflet
let viewAgentMap = null;

// Stocker l'ID de l'agent actuel pour le bouton modifier
let currentViewAgentId = null;

// Fonction pour initialiser la carte dans le header du modal
function initViewAgentMap(latitude, longitude, kiosqueNom) {
    const mapHeader = document.getElementById('view_agent_map_header');
    if (!mapHeader) return;
    
    // Détruire la carte existante si elle existe
    if (viewAgentMap) {
        viewAgentMap.remove();
        viewAgentMap = null;
    }
    
    // Créer un conteneur pour la carte
    let mapContainer = mapHeader.querySelector('#view_agent_map_container');
    if (!mapContainer) {
        mapContainer = document.createElement('div');
        mapContainer.id = 'view_agent_map_container';
        mapContainer.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; border-radius: 0.5rem 0.5rem 0 0; overflow: hidden;';
        mapHeader.insertBefore(mapContainer, mapHeader.firstChild);
    } else {
        // Vider le conteneur s'il existe déjà
        mapContainer.innerHTML = '';
    }
    
    // Position par défaut (si pas de kiosque, utiliser une position par défaut)
    const defaultLat = latitude || 14.7167; // Latitude par défaut (Dakar)
    const defaultLng = longitude || -17.4677; // Longitude par défaut (Dakar)
    const zoom = latitude && longitude ? 18 : 10; // Zoom plus élevé (18 au lieu de 15)
    
    // Initialiser la carte Leaflet
    viewAgentMap = L.map(mapContainer, {
        zoomControl: false,
        attributionControl: false
    }).setView([defaultLat, defaultLng], zoom);
    
    // Ajouter la couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(viewAgentMap);
    
    // Ajouter un marqueur si on a les coordonnées du kiosque
    if (latitude && longitude) {
        // Créer un marqueur personnalisé vert pour le kiosque
        const kiosqueIcon = L.divIcon({
            className: 'kiosque-marker',
            html: '<div style="background-color: #10b981; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><i class="ki-filled ki-geolocation text-white" style="font-size: 16px;"></i></div>',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        const marker = L.marker([latitude, longitude], {
            icon: kiosqueIcon
        }).addTo(viewAgentMap);
        
        if (kiosqueNom) {
            marker.bindPopup(`<b>${kiosqueNom}</b>`);
        }
        
        // Ajuster la vue pour positionner le marqueur en haut à droite de l'avatar
        // On décale la vue vers le bas et la gauche pour que le marqueur apparaisse visuellement en haut à droite
        // L'avatar est centré, donc on veut que le marqueur soit visible à droite de l'avatar
        // Avec un zoom de 18, on utilise un décalage plus petit pour un positionnement précis
        // Pour positionner le marqueur en haut à droite, on décale vers le bas (latitude -) et vers la gauche (longitude -)
        const offsetLat = latitude - 0.0003; // Décalage vers le sud (bas) pour que le marqueur soit en haut
        const offsetLng = longitude - 0.0003; // Décalage vers l'ouest (gauche) pour que le marqueur soit à droite
        viewAgentMap.setView([offsetLat, offsetLng], 18);
    }
    
    // Redimensionner la carte plusieurs fois pour s'assurer qu'elle s'affiche correctement
    // Leaflet a besoin que le conteneur soit visible pour calculer correctement les dimensions
    // Utiliser requestAnimationFrame pour s'assurer que le DOM est mis à jour
    requestAnimationFrame(() => {
        setTimeout(() => {
            if (viewAgentMap) {
                viewAgentMap.invalidateSize();
                // Réessayer après un autre court délai
                setTimeout(() => {
                    if (viewAgentMap) {
                        viewAgentMap.invalidateSize();
                        // Une dernière fois pour être sûr
                        setTimeout(() => {
                            if (viewAgentMap) {
                                viewAgentMap.invalidateSize();
                            }
                        }, 200);
                    }
                }, 200);
            }
        }, 100);
    });
}

// Fonction pour charger et afficher les détails d'un agent
window.loadAgentDetails = function(id) {
    currentViewAgentId = id;
    const modal = document.getElementById('modal_view_agent');
    if (!modal) {
        console.error('Modal de visualisation introuvable');
        return;
    }
    
    fetch(`/agents/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!response.ok) {
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(errData => {
                    throw new Error(errData.message || 'Erreur lors du chargement des données');
                });
            }
            throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
        }
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('Le serveur a renvoyé une réponse non-JSON: ' + text.substring(0, 100));
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Erreur lors du chargement des données');
        }
        if (data.success && data.agent) {
            const agent = data.agent;
            
            // Remplir le modal de visualisation
            const nomComplet = `${agent.prenom || ''} ${agent.nom || ''}`.trim() || '-';
            document.getElementById('view_agent_nom_complet').textContent = nomComplet;
            document.getElementById('view_agent_code').textContent = agent.code_agent || '-';
            document.getElementById('view_agent_telephone').textContent = agent.telephone || '-';
            
            const statutEl = document.getElementById('view_agent_statut');
            const statutText = (agent.statut || 'inactif').charAt(0).toUpperCase() + (agent.statut || 'inactif').slice(1);
            statutEl.textContent = statutText;
            statutEl.className = `kt-badge kt-badge-${agent.statut === 'actif' ? 'success' : agent.statut === 'suspendu' ? 'danger' : agent.statut === 'en_attente' ? 'warning' : 'secondary'}`;
            
            // Informations utilisateur
            const email = (agent.utilisateur && agent.utilisateur.email) ? agent.utilisateur.email : '-';
            const emailEl = document.getElementById('view_agent_email');
            if (emailEl) {
                emailEl.textContent = email;
            }
            const emailLink = document.getElementById('view_agent_email_link');
            if (emailLink) {
                emailLink.textContent = email;
                emailLink.href = email !== '-' ? `mailto:${email}` : '#';
            }
            
            // Photo de profil avec bordure verte - parfaitement ronde
            const photoContainer = document.getElementById('view_agent_photo');
            if (photoContainer) {
                if (agent.utilisateur && (agent.utilisateur.photo_profil_url || agent.utilisateur.photo_profil)) {
                    const photoUrl = agent.utilisateur.photo_profil_url || `/storage/${agent.utilisateur.photo_profil}`;
                    photoContainer.innerHTML = `<img class="border-3 border-green-500 object-cover" style="width: 100px; height: 100px; border-radius: 50%; border-width: 3px; border-color: #10b981;" src="${photoUrl}" alt="${nomComplet}"/>`;
                } else {
                    photoContainer.innerHTML = '<div class="border-3 border-green-500 bg-gray-200 dark:bg-gray-700 flex items-center justify-center" style="width: 100px; height: 100px; border-radius: 50%; border-width: 3px; border-color: #10b981;"><i class="ki-filled ki-user text-4xl text-gray-400"></i></div>';
                }
            }
            
            // Informations kiosque
            const kiosqueInfo = document.getElementById('view_agent_kiosque_info');
            const kiosqueCard = document.getElementById('view_agent_kiosque_card');
            let kiosqueLat = null;
            let kiosqueLng = null;
            let kiosqueNom = null;
            
            if (agent.kiosque) {
                document.getElementById('view_agent_kiosque_nom').textContent = agent.kiosque.nom || '-';
                document.getElementById('view_agent_kiosque_nom_detail').textContent = agent.kiosque.nom || '-';
                document.getElementById('view_agent_kiosque_code_detail').textContent = agent.kiosque.code || '-';
                document.getElementById('view_agent_kiosque_type').textContent = agent.kiosque.type || '-';
                document.getElementById('view_agent_kiosque_adresse').textContent = agent.kiosque.adresse || '-';
                if (kiosqueInfo) kiosqueInfo.style.display = 'flex';
                if (kiosqueCard) kiosqueCard.style.display = 'block';
                
                kiosqueLat = agent.kiosque.latitude;
                kiosqueLng = agent.kiosque.longitude;
                kiosqueNom = agent.kiosque.nom;
            } else {
                document.getElementById('view_agent_kiosque_nom').textContent = 'Aucun kiosque';
                if (kiosqueInfo) kiosqueInfo.style.display = 'none';
                if (kiosqueCard) kiosqueCard.style.display = 'none';
            }
            
            // Soldes
            document.getElementById('view_agent_espece_initiale').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(agent.espece_initiale || 0);
            document.getElementById('view_agent_montant_initial_total').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(agent.montant_initial_total || 0);
            
            // Statistiques
            if (data.stats) {
                document.getElementById('view_agent_solde_total').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(data.stats.solde_total || 0);
                document.getElementById('view_agent_transactions_total').textContent = data.stats.transactions_total || 0;
                document.getElementById('view_agent_transactions_mois').textContent = data.stats.transactions_mois || 0;
                document.getElementById('view_agent_montant_mois').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(data.stats.montant_mois || 0);
                document.getElementById('view_agent_transactions_jour').textContent = data.stats.transactions_jour || 0;
                document.getElementById('view_agent_montant_jour').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(data.stats.montant_jour || 0);
            }
            
            // Ouvrir le modal d'abord, puis initialiser la carte APRÈS que le modal soit complètement visible
            if (typeof KTModal !== 'undefined') {
                let modalInstance = KTModal.getInstance(modal);
                if (!modalInstance) {
                    modalInstance = new KTModal(modal);
                }
                
                // Ouvrir le modal
                modalInstance.show();
                
                // Attendre que le modal soit complètement visible avant d'initialiser la carte
                // Utiliser plusieurs tentatives avec requestAnimationFrame pour s'assurer que le modal est prêt
                let attempts = 0;
                const maxAttempts = 20;
                
                function tryInitMap() {
                    attempts++;
                    const mapHeader = document.getElementById('view_agent_map_header');
                    
                    // Vérifier si le modal est visible et a des dimensions
                    if (mapHeader && mapHeader.offsetWidth > 0 && mapHeader.offsetHeight > 0) {
                        // Le modal est visible, initialiser la carte
                        setTimeout(() => {
                            initViewAgentMap(kiosqueLat, kiosqueLng, kiosqueNom);
                        }, 100);
                    } else if (attempts < maxAttempts) {
                        // Réessayer au prochain frame
                        requestAnimationFrame(tryInitMap);
                    } else {
                        // Fallback : initialiser quand même après plusieurs tentatives
                        console.warn('Le modal n\'est pas complètement visible, initialisation de la carte de toute façon...');
                        setTimeout(() => {
                            initViewAgentMap(kiosqueLat, kiosqueLng, kiosqueNom);
                        }, 300);
                    }
                }
                
                // Commencer à essayer après que le modal soit ouvert
                requestAnimationFrame(tryInitMap);
            } else {
                modal.style.display = 'flex';
                modal.classList.add('show');
                
                // Attendre que le modal soit visible avant d'initialiser la carte
                setTimeout(() => {
                    initViewAgentMap(kiosqueLat, kiosqueLng, kiosqueNom);
                }, 300);
            }
        } else {
            throw new Error('Données invalides');
        }
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        console.error('Stack trace:', error.stack);
        let errorMessage = 'Une erreur est survenue lors du chargement des données.';
        if (error.message) {
            errorMessage += '\n\n' + error.message;
        }
        alert(errorMessage);
    });
}

// Nettoyer la carte quand le modal est fermé
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal_view_agent');
    if (modal) {
        modal.addEventListener('hidden', function() {
            if (viewAgentMap) {
                viewAgentMap.remove();
                viewAgentMap = null;
            }
        });
    }
    
    // Gérer le bouton "Modifier" depuis le modal de visualisation
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-agent-from-view');
        if (editBtn) {
            e.preventDefault();
            const modal = document.getElementById('modal_view_agent');
            if (modal && typeof KTModal !== 'undefined') {
                const modalInstance = KTModal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
            // Utiliser l'ID stocké
            if (currentViewAgentId && typeof window.loadAgentEdit === 'function') {
                setTimeout(() => {
                    window.loadAgentEdit(currentViewAgentId);
                }, 300);
            }
        }
    });
});

// Fonction pour charger et afficher le formulaire d'édition
window.loadAgentEdit = function(id) {
    const modal = document.getElementById('modal_edit_agent');
    if (!modal) {
        console.error('Modal d\'édition introuvable');
        return;
    }
    
    fetch(`/agents/${id}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors du chargement des données');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.agent) {
            const agent = data.agent;
            
            // Remplir le formulaire d'édition
            document.getElementById('edit_agent_id').value = agent.id;
            document.getElementById('edit_code_agent').value = agent.code_agent || '';
            document.getElementById('edit_nom').value = agent.nom || '';
            document.getElementById('edit_prenom').value = agent.prenom || '';
            document.getElementById('edit_telephone').value = agent.telephone || '';
            document.getElementById('edit_statut').value = agent.statut || 'actif';
            document.getElementById('edit_kiosque_id').value = agent.kiosque_id || '';
            
            // Photo de profil
            const photoPreview = document.getElementById('edit_agent_photo_preview');
            if (agent.utilisateur && (agent.utilisateur.photo_profil_url || agent.utilisateur.photo_profil)) {
                const photoUrl = agent.utilisateur.photo_profil_url || `/storage/${agent.utilisateur.photo_profil}`;
                photoPreview.innerHTML = `<img class="h-20 w-20 rounded-full object-cover border-2 border-border" src="${photoUrl}" alt="${agent.nom} ${agent.prenom}"/>`;
            } else {
                photoPreview.innerHTML = '<div class="h-20 w-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"><i class="ki-filled ki-user text-2xl text-gray-400"></i></div>';
            }
            
            // Réinitialiser les erreurs
            document.querySelectorAll('#modal_edit_agent .text-destructive').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            
            // Ouvrir le modal après un court délai pour laisser le menu se fermer
            setTimeout(() => {
                if (typeof KTModal !== 'undefined') {
                    let modalInstance = KTModal.getInstance(modal);
                    if (!modalInstance) {
                        modalInstance = new KTModal(modal);
                    }
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                    modal.classList.add('show');
                }
            }, 150);
        } else {
            throw new Error('Données invalides');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors du chargement des données: ' + error.message);
    });
}

// Fonction pour initialiser les event listeners des boutons d'action
function initAgentsPageActions() {
    // Éviter de ré-attacher plusieurs fois les mêmes listeners
    if (document._agentsActionsInited) {
        return;
    }
    document._agentsActionsInited = true;

    // Utiliser la délégation d'événements pour capturer les clics même après le chargement dynamique
    // Voir les détails
    document.addEventListener('click', function(e) {
        const viewLink = e.target.closest('.view-agent');
        if (viewLink) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Fermer le menu déroulant
            const menu = viewLink.closest('.kt-menu-dropdown');
            if (menu) {
                menu.classList.remove('show');
                menu.classList.add('hidden');
                menu.style.display = 'none';
                const row = menu.closest('tr');
                if (row) row.classList.remove('agents-row-menu-open');
                setTimeout(() => {
                    const id = viewLink.getAttribute('data-id');
                    if (id && typeof window.loadAgentDetails === 'function') {
                        window.loadAgentDetails(id);
                    }
                }, 100);
            } else {
                const id = viewLink.getAttribute('data-id');
                if (id && typeof window.loadAgentDetails === 'function') {
                    window.loadAgentDetails(id);
                }
            }
            return false;
        }
        
        // Modifier
        const editLink = e.target.closest('.edit-agent');
        if (editLink) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Fermer le menu déroulant
            const menu = editLink.closest('.kt-menu-dropdown');
            if (menu) {
                menu.classList.remove('show');
                menu.classList.add('hidden');
                menu.style.display = 'none';
                const row = menu.closest('tr');
                if (row) row.classList.remove('agents-row-menu-open');
                setTimeout(() => {
                    const id = editLink.getAttribute('data-id');
                    if (id && typeof window.loadAgentEdit === 'function') {
                        window.loadAgentEdit(id);
                    }
                }, 100);
            } else {
                const id = editLink.getAttribute('data-id');
                if (id && typeof window.loadAgentEdit === 'function') {
                    window.loadAgentEdit(id);
                }
            }
            return false;
        }
    }, true);
}

// Fonction pour initialiser les menus d'actions des agents
function initKTMenus() {
    // Éviter de ré-attacher plusieurs fois les mêmes listeners
    if (document._agentsMenusInited) {
        return;
    }
    document._agentsMenusInited = true;

    console.log('Initialisation des menus KTMenu (agents)...');

    // Laisser Metronic initialiser les menus globaux si disponible
    if (window.MetronicCore && typeof window.MetronicCore.initMenus === 'function') {
        window.MetronicCore.initMenus();
    }

    // Fallback manuel pour gérer les clics sur les trois points
    console.log('Ajout du fallback manuel pour les menus (agents)');

    // Utiliser capture pour intercepter les clics avant les autres handlers
    document.addEventListener('click', function(e) {
        const menuToggle = e.target.closest('.kt-menu-toggle');
        if (menuToggle) {
            console.log('Clic sur menu toggle détecté');
            e.preventDefault();
            e.stopPropagation();
            
            const menuItem = menuToggle.closest('.kt-menu-item');
            const dropdown = menuItem ? menuItem.querySelector('.kt-menu-dropdown') : null;
            
            console.log('Dropdown trouvé:', dropdown ? 'oui' : 'non');
            
            if (dropdown) {
                // Fermer tous les autres dropdowns
                document.querySelectorAll('.kt-menu-dropdown').forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('show');
                        d.style.display = 'none';
                        const r = d.closest('tr');
                        if (r) r.classList.remove('agents-row-menu-open');
                    }
                });
                
                // Toggle le dropdown actuel
                const isOpen = dropdown.classList.contains('show');
                console.log('État actuel du dropdown:', isOpen ? 'ouvert' : 'fermé');
                
                if (isOpen) {
                    dropdown.classList.remove('show');
                    dropdown.style.display = 'none';
                    const openRow = dropdown.closest('tr');
                    if (openRow) openRow.classList.remove('agents-row-menu-open');
                    console.log('Fermeture du dropdown');
                } else {
                    // Retirer la classe des autres lignes
                    document.querySelectorAll('#agents_table tbody tr.agents-row-menu-open').forEach(function(r) {
                        r.classList.remove('agents-row-menu-open');
                    });
                    // Mettre la ligne du menu ouvert au-dessus des autres (z-index) pour que Voir/Modifier soient cliquables
                    const currentRow = menuToggle.closest('tr');
                    if (currentRow) currentRow.classList.add('agents-row-menu-open');

                    // Positionner le dropdown en fixed pour qu'il soit au-dessus de tout
                    const rect = menuToggle.getBoundingClientRect();
                    dropdown.style.position = 'fixed';
                    dropdown.style.top = (rect.bottom + 5) + 'px'; // 5px sous le bouton
                    dropdown.style.right = (window.innerWidth - rect.right) + 'px'; // Aligné à droite du bouton
                    dropdown.style.zIndex = '99999';
                    
                    dropdown.classList.add('show');
                    dropdown.style.display = 'block';
                    console.log('Ouverture du dropdown à position:', dropdown.style.top, dropdown.style.right);
                }
            }
            return false;
        }
        
        // Fermer les dropdowns quand on clique ailleurs
        if (!e.target.closest('.kt-menu')) {
            const openDropdowns = document.querySelectorAll('.kt-menu-dropdown.show');
            if (openDropdowns.length > 0) {
                console.log('Fermeture de', openDropdowns.length, 'dropdown(s) ouverts');
                openDropdowns.forEach(d => {
                    d.classList.remove('show');
                    d.style.display = 'none';
                    const row = d.closest('tr');
                    if (row) row.classList.remove('agents-row-menu-open');
                });
            }
        }
    }, true); // Utiliser capture phase
}

// Exposer une fonction d'initialisation globale pour la page Agents (utilisée par la navigation AJAX)
window.initAgentsPage = function() {
    // S'assurer que le message vide reste centré (colspan, largeur)
    const table = document.getElementById('agents_table');
    if (table) {
        const applyEmptyRowFix = () => {
            const emptyRow = table.querySelector('tbody tr.empty-row');
            if (!emptyRow) return;
            const td = emptyRow.querySelector('td');
            if (td && td.getAttribute('colspan') !== '7') {
                td.setAttribute('colspan', '7');
                td.style.width = '100%';
                td.style.border = 'none';
            }
        };

        applyEmptyRowFix();

        // Observer les changements du DOM pour maintenir le colspan (une seule fois)
        if (!table._agentsEmptyObserver) {
            const observer = new MutationObserver(() => applyEmptyRowFix());
            observer.observe(table, { childList: true, subtree: true });
            table._agentsEmptyObserver = observer;
        }
    }

    // Initialiser menus et actions
    initKTMenus();
    initAgentsPageActions();
};

// Initialisation sur chargement normal
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.initAgentsPage();
    });
} else {
    window.initAgentsPage();
}

// Réinitialisation explicite après navigation AJAX (quand la page Agents est chargée via AJAX)
document.addEventListener('ajax-content-loaded', function() {
    if (document.getElementById('agents_table')) {
        window.initAgentsPage();
    }
});

</script>
@endsection

<!-- Modal Voir Agent -->
<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_view_agent" style="display: none;">
    <div class="kt-modal-content kt-container-fixed p-0" id="modal_view_agent_content">
        <div class="kt-modal-header rounded-t-lg p-0 border-0 relative min-h-80 flex flex-col items-stretch justify-end bg-center bg-cover bg-no-repeat mb-7" id="view_agent_map_header">
            <div class="flex flex-col justify-end border-b-0 grow px-9 bg-gradient-to-t from-light from-3% to-transparent" style="z-index: 1; position: relative;">
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline absolute top-0 end-0 me-5 mt-5 lg:me-10 shadow-default" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
                <div class="flex justify-center mb-5" style="position: relative; z-index: 2;">
                    <div id="view_agent_photo" class="flex-shrink-0"></div>
                </div>
                <div class="grid lg:grid-cols-3 gap-3 w-full items-center">
                    <div></div>
                    <div class="flex items-center flex-col">
                        <div class="flex items-center gap-1.5 mb-2">
                            <a class="text-lg leading-5 font-semibold text-foreground hover:text-primary" href="#" id="view_agent_nom_complet">
                                -
                            </a>
                            <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor"></path>
                            </svg>
                        </div>
                        <div class="flex flex-wrap justify-center gap-1 lg:gap-3 text-sm">
                            <div class="flex gap-1 items-center" id="view_agent_kiosque_info">
                                <i class="ki-filled ki-geolocation text-muted-foreground text-base"></i>
                                <span class="text-secondary-foreground" id="view_agent_kiosque_nom">-</span>
                            </div>
                            <div class="flex gap-1 items-center">
                                <i class="ki-filled ki-sms text-muted-foreground text-base"></i>
                                <a class="text-secondary-foreground hover:text-primary" href="#" id="view_agent_email_link">-</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        
                        <button class="kt-btn kt-btn-icon kt-btn-outline">
                            <i class="ki-filled ki-messages"></i>
                        </button>
                        <div data-kt-dropdown="true" data-kt-dropdown-placement="bottom-end">
                            <button class="kt-btn kt-btn-icon kt-btn-outline" data-kt-dropdown-toggle="true">
                                <i class="ki-filled ki-dots-vertical"></i>
                            </button>
                            <div class="kt-dropdown-menu w-full max-w-[220px]" data-kt-dropdown-menu="true">
                                <ul class="kt-dropdown-menu-sub">
                                    <li>
                                        <button class="kt-dropdown-menu-link edit-agent-from-view" data-kt-dropdown-dismiss="true" data-agent-id="">
                                            <i class="ki-filled ki-pencil"></i>
                                            Modifier
                                        </button>
                                    </li>
                                    <li>
                                        <button class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true">
                                            <i class="ki-filled ki-file-up"></i>
                                            Exporter
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-modal-body kt-scrollable-y py-0 mb-5 ps-6 pr-3 me-3">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
                <div class="col-span-1">
                    <div class="grid gap-5 lg:gap-7.5">
                        <!-- About Card -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">À propos</h3>
                            </div>
                            <div class="kt-card-content pt-4 pb-3">
                                <table class="kt-table-auto">
                                    <tbody>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Code Agent:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_code">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Téléphone:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_telephone">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Email:</td>
                                            <td class="text-sm text-mono pb-3.5">
                                                <a class="text-foreground hover:text-primary" href="#" id="view_agent_email">-</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Statut:</td>
                                            <td class="text-sm text-mono pb-3.5">
                                                <span id="view_agent_statut" class="kt-badge"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Espèce Initiale:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_espece_initiale">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Montant Total:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_montant_initial_total">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Kiosque Card -->
                        <div class="kt-card" id="view_agent_kiosque_card" style="display: none;">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Kiosque</h3>
                            </div>
                            <div class="kt-card-content pt-4 pb-3">
                                <table class="kt-table-auto">
                                    <tbody>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Nom:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_kiosque_nom_detail">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Code:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_kiosque_code_detail">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Type:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_kiosque_type">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm text-secondary-foreground pb-3.5 pe-3">Adresse:</td>
                                            <td class="text-sm text-mono pb-3.5" id="view_agent_kiosque_adresse">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex flex-col gap-5 lg:gap-7.5">
                        <!-- Statistiques Cards -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Solde Total</h3>
                                </div>
                                <div class="kt-card-content">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xl font-semibold text-foreground" id="view_agent_solde_total">0 XOF</span>
                                    </div>
                                </div>
                            </div>
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Transactions Total</h3>
                                </div>
                                <div class="kt-card-content">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xl font-semibold text-foreground" id="view_agent_transactions_total">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Transactions ce Mois</h3>
                                </div>
                                <div class="kt-card-content">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xl font-semibold text-foreground" id="view_agent_transactions_mois">0</span>
                                        <span class="text-sm text-secondary-foreground" id="view_agent_montant_mois">0 XOF</span>
                                    </div>
                                </div>
                            </div>
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Aujourd'hui</h3>
                                </div>
                                <div class="kt-card-content">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xl font-semibold text-foreground" id="view_agent_transactions_jour">0</span>
                                        <span class="text-sm text-secondary-foreground" id="view_agent_montant_jour">0 XOF</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifier Agent -->
<div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_edit_agent" style="display: none;">
    <div class="kt-modal-content max-w-[700px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">
                Modifier l'Agent
            </h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="form_edit_agent" enctype="multipart/form-data">
            <input type="hidden" name="agent_id" id="edit_agent_id" />
            <div class="kt-modal-body">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Photo de profil
                        </label>
                        <div class="flex items-center gap-5">
                            <div id="edit_agent_photo_preview" class="flex-shrink-0"></div>
                            <input class="kt-input flex-1" type="file" name="photo" id="edit_agent_photo" accept="image/*" />
                        </div>
                        <span class="text-xs text-secondary-foreground">Format: JPEG, PNG, JPG (max 2MB)</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Code Agent <span class="text-destructive">*</span>
                            </label>
                            <input class="kt-input" type="text" name="code_agent" id="edit_code_agent" required />
                            <span class="text-xs text-destructive hidden" id="error_edit_code_agent"></span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Statut <span class="text-destructive">*</span>
                            </label>
                            <select class="kt-select" name="statut" id="edit_statut" required>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en_attente">En attente</option>
                                <option value="suspendu">Suspendu</option>
                            </select>
                            <span class="text-xs text-destructive hidden" id="error_edit_statut"></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Nom <span class="text-destructive">*</span>
                            </label>
                            <input class="kt-input" type="text" name="nom" id="edit_nom" required />
                            <span class="text-xs text-destructive hidden" id="error_edit_nom"></span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Prénom <span class="text-destructive">*</span>
                            </label>
                            <input class="kt-input" type="text" name="prenom" id="edit_prenom" required />
                            <span class="text-xs text-destructive hidden" id="error_edit_prenom"></span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Téléphone <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input" type="text" name="telephone" id="edit_telephone" required />
                        <span class="text-xs text-destructive hidden" id="error_edit_telephone"></span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="kt-label">
                            Kiosque
                        </label>
                        <select class="kt-select" name="kiosque_id" id="edit_kiosque_id" data-kt-select="true">
                            <option value="">Aucun kiosque</option>
                            @foreach($kiosques ?? [] as $kiosque)
                                <option value="{{ $kiosque->id }}">{{ $kiosque->nom }} ({{ $kiosque->code }})</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-destructive hidden" id="error_edit_kiosque_id"></span>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-primary" id="btn_update_agent">
                    <i class="ki-filled ki-check"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Prévisualisation de la photo lors de l'édition
document.addEventListener('DOMContentLoaded', function() {
    const editPhotoInput = document.getElementById('edit_agent_photo');
    if (editPhotoInput) {
        editPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux. Taille maximale: 2MB');
                    this.value = '';
                    return;
                }
                
                if (!file.type.match('image.*')) {
                    alert('Veuillez sélectionner une image valide');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('edit_agent_photo_preview');
                    if (preview) {
                        preview.innerHTML = `<img class="h-20 w-20 rounded-full object-cover border-2 border-border" src="${e.target.result}" alt="Photo de profil"/>`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Soumission du formulaire d'édition
    const editForm = document.getElementById('form_edit_agent');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const agentId = document.getElementById('edit_agent_id').value;
            formData.append('_method', 'PUT');
            
            const submitBtn = document.getElementById('btn_update_agent');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Enregistrement...';
            
            // Réinitialiser les erreurs
            document.querySelectorAll('#modal_edit_agent .text-destructive').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            
            fetch(`/agents/${agentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Le serveur a renvoyé une réponse non-JSON');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorEl = document.getElementById('error_edit_' + key);
                            if (errorEl) {
                                errorEl.textContent = data.errors[key][0];
                                errorEl.classList.remove('hidden');
                            }
                        });
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'enregistrement: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>
