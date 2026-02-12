@extends('layouts.demo1.base')

@section('content')
<main class="grow" id="content" role="content">
    <!-- Container -->
    <div class="kt-container-fixed" id="contentContainer">
    </div>
    <!-- End of Container -->
    <!-- Titre + bouton Ajouter -->
    <div class="kt-container-fixed">
     <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
      <div class="flex flex-col justify-center gap-2">
       <h1 class="text-2xl font-semibold leading-none text-mono">
        Utilisateurs
       </h1>
       <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
        Affichage de {{ $utilisateurs->total() }} utilisateur{{ $utilisateurs->total() > 1 ? 's' : '' }}.
       </div>
      </div>
      <div class="flex items-center gap-2.5">
       <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouvel_utilisateur">
        <i class="ki-filled ki-plus"></i>
        Ajouter
       </button>
      </div>
     </div>
    </div>
    <!-- Filtres -->
    <div class="kt-container-fixed">
     <div class="flex flex-col items-stretch gap-5 lg:gap-7.5">
      <div class="flex flex-wrap items-center gap-5 justify-end">
       <div class="flex items-center flex-wrap gap-5">
        <form method="GET" action="{{ route('utilisateurs.index') }}" class="flex items-center gap-2.5" id="utilisateurs-filter-form">
         <select name="statut" id="filter-statut" class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Sélectionner un statut">
          <option value="">Tous les statuts</option>
          <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>
           Actif
          </option>
          <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>
           Inactif
          </option>
          <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>
           Suspendu
          </option>
         </select>
         <select name="profil_id" id="filter-profil" class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Sélectionner un profil">
          <option value="">Tous les profils</option>
          @foreach($profils ?? [] as $profil)
          <option value="{{ $profil->id }}" {{ request('profil_id') == $profil->id ? 'selected' : '' }}>
           {{ $profil->libelle }}
          </option>
          @endforeach
         </select>
         <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
        <form method="GET" action="{{ route('utilisateurs.index') }}" class="flex" id="utilisateurs-search-form">
         <label class="kt-input">
          <i class="ki-filled ki-magnifier">
          </i>
          <input placeholder="Rechercher par nom, prénom, email" type="text" name="search" id="filter-search" value="{{ request('search') }}"/>
          @if(request('statut'))
          <input type="hidden" name="statut" value="{{ request('statut') }}">
          @endif
          @if(request('profil_id'))
          <input type="hidden" name="profil_id" value="{{ request('profil_id') }}">
          @endif
         </label>
        </form>
        <div class="kt-toggle-group kt-toggle-group-sm" data-kt-tabs="true">
         <a class="kt-btn kt-btn-icon active" data-kt-tab-toggle="#team_crew_card" href="#">
          <i class="ki-filled ki-category">
          </i>
         </a>
         <a class="kt-btn kt-btn-icon" data-kt-tab-toggle="#team_crew_list" href="#">
          <i class="ki-filled ki-row-horizontal">
          </i>
         </a>
        </div>
       </div>
      </div>
      <div class="flex flex-col gap-5 lg:gap-7.5" id="team_crew_card">
       <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
        @forelse($utilisateurs as $utilisateur)
        <div class="kt-card flex flex-col">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5 flex-1">
           <div class="flex justify-center mb-2.5">
           <div class="size-20 relative">
            @if($utilisateur->photo_profil)
            <img class="rounded-full w-20 h-20 object-cover" src="{{ asset('storage/' . $utilisateur->photo_profil) }}" alt="{{ $utilisateur->prenom }} {{ $utilisateur->nom }}"/>
            @else
            <div class="flex items-center justify-center relative text-2xl {{ $utilisateur->statut == 'actif' ? 'text-green-500 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30' : ($utilisateur->statut == 'suspendu' ? 'text-destructive ring-destructive/20 bg-destructive/5' : 'text-muted-foreground ring-muted bg-muted/5') }} size-20 ring-1 rounded-full">
             {{ strtoupper(substr($utilisateur->prenom ?? $utilisateur->nom, 0, 1)) }}
            </div>
            @endif
            <div class="flex size-2.5 {{ $utilisateur->statut == 'actif' ? 'bg-green-500' : ($utilisateur->statut == 'suspendu' ? 'bg-red-500' : 'bg-gray-500') }} rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" href="javascript:void(0)" onclick="loadUserProfile({{ $utilisateur->id }})">
            {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           @if($utilisateur->profils->count() > 0)
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            {{ $utilisateur->profils->pluck('libelle')->implode(', ') }}
           </div>
           @endif
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="mailto:{{ $utilisateur->email }}">
             {{ $utilisateur->email }}
            </a>
           </div>
          </div>
          @if($utilisateur->telephone)
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-phone me-1 text-muted-foreground">
            </i>
            {{ $utilisateur->telephone }}
           </div>
          </div>
          @endif
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $utilisateur->profils->count() }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Profil{{ $utilisateur->profils->count() > 1 ? 's' : '' }}
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             {{ ucfirst($utilisateur->statut) }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Statut
            </span>
           </div>
           @if($utilisateur->dernier_connexion)
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $utilisateur->dernier_connexion->format('d/m/Y') }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Dernière connexion
            </span>
           </div>
           @endif
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-sm kt-btn-outline {{ $utilisateur->statut == 'actif' ? 'kt-btn-primary' : '' }}">
           <i class="ki-filled ki-check-circle">
           </i>
           {{ $utilisateur->statut == 'actif' ? 'Actif' : ($utilisateur->statut == 'suspendu' ? 'Suspendu' : 'Inactif') }}
          </a>
         </div>
        </div>
        @empty
        <div class="col-span-2">
         <div class="kt-card">
          <div class="kt-card-content text-center py-10">
           <p class="text-secondary-foreground">Aucun utilisateur trouvé</p>
          </div>
         </div>
        </div>
        @endforelse
       </div>
       @if($utilisateurs->hasPages())
       <div class="flex grow justify-center pt-5 lg:pt-7.5">
        {{ $utilisateurs->links() }}
       </div>
       @endif
      </div>
      <div class="hidden" id="team_crew_list">
       <div class="kt-card p-0">
        <div class="divide-y divide-border">
        @forelse($utilisateurs as $utilisateur)
        <div class="p-7.5">
         <div class="grid items-center gap-4 lg:gap-8 grid-cols-[minmax(0,1fr)_auto_auto]">
          <!-- Colonne 1 : avatar + infos de base -->
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            @if($utilisateur->photo_profil)
            <div class="size-20 relative">
             <img class="rounded-full w-20 h-20 object-cover" src="{{ asset('storage/' . $utilisateur->photo_profil) }}" alt="{{ $utilisateur->prenom }} {{ $utilisateur->nom }}"/>
             <div class="flex size-2.5 {{ $utilisateur->statut == 'actif' ? 'bg-green-500' : ($utilisateur->statut == 'suspendu' ? 'bg-red-500' : 'bg-gray-500') }} rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
            @else
            <div class="flex items-center justify-center relative text-2xl {{ $utilisateur->statut == 'actif' ? 'text-green-500 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30' : ($utilisateur->statut == 'suspendu' ? 'text-destructive ring-destructive/20 bg-destructive/5' : 'text-muted-foreground ring-muted bg-muted/5') }} size-20 ring-1 rounded-full">
             {{ strtoupper(substr($utilisateur->prenom ?? $utilisateur->nom, 0, 1)) }}
             <div class="flex size-2.5 {{ $utilisateur->statut == 'actif' ? 'bg-green-500' : ($utilisateur->statut == 'suspendu' ? 'bg-red-500' : 'bg-gray-500') }} rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
            @endif
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" href="javascript:void(0)" onclick="loadUserProfile({{ $utilisateur->id }})">
              {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
             </path>
            </svg>
           </div>
           <div class="flex items-center flex-wrap gap-x-4">
            @if($utilisateur->profils->count() > 0)
            <div class="flex items-center text-sm text-secondary-foreground">
             <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
             </i>
             {{ $utilisateur->profils->pluck('libelle')->implode(', ') }}
            </div>
            @endif
            <div class="flex items-center text-sm">
             <i class="ki-filled ki-sms me-1 text-muted-foreground">
             </i>
             <a class="text-secondary-foreground hover:text-primary" href="mailto:{{ $utilisateur->email }}">
              {{ $utilisateur->email }}
             </a>
            </div>
           </div>
          </div>
          </div>

          <!-- Colonne 2 : stats alignées (profils / statut / dernière connexion) -->
          <div class="flex items-center flex-wrap gap-2 lg:gap-4">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2 min-w-[80px]">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $utilisateur->profils->count() }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Profil{{ $utilisateur->profils->count() > 1 ? 's' : '' }}
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2 min-w-[80px]">
            <span class="text-mono text-sm leading-none font-medium">
             {{ ucfirst($utilisateur->statut) }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Statut
            </span>
           </div>
           @if($utilisateur->dernier_connexion)
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2 min-w-[110px]">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $utilisateur->dernier_connexion->format('d/m/Y') }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Dernière connexion
            </span>
           </div>
           @endif
          </div>

          <!-- Colonne 3 : bouton statut -->
          <div class="text-right w-28">
           <a class="kt-btn kt-btn-sm kt-btn-outline {{ $utilisateur->statut == 'actif' ? 'kt-btn-primary' : '' }}">
            <i class="ki-filled ki-check-circle">
            </i>
            {{ $utilisateur->statut == 'actif' ? 'Actif' : ($utilisateur->statut == 'suspendu' ? 'Suspendu' : 'Inactif') }}
           </a>
          </div>
         </div>
        </div>
        @empty
        <div class="p-7.5 text-center py-10">
          <p class="text-secondary-foreground">Aucun utilisateur trouvé</p>
        </div>
        @endforelse
        </div>
       </div>
       @if($utilisateurs->hasPages())
       <div class="flex grow justify-center pt-5 lg:pt-7.5">
        {{ $utilisateurs->links() }}
       </div>
       @endif
      </div>
     </div>
    </div>
    <!-- End of Container -->

   <!-- Modal Nouvel Utilisateur -->
   <div class="kt-modal" data-kt-modal="true" id="modal_nouvel_utilisateur" style="display: none;">
    <div class="kt-modal-content max-w-2xl">
     <div class="kt-modal-header">
      <h3 class="kt-modal-title">Créer un utilisateur</h3>
      <button type="button" class="kt-modal-close" data-kt-modal-dismiss="true">
       <i class="ki-filled ki-cross"></i>
      </button>
     </div>
     <form action="{{ route('utilisateurs.store') }}" method="POST" enctype="multipart/form-data" id="form_nouvel_utilisateur">
      @csrf
      <div class="kt-modal-body flex flex-col gap-5">
       @if($errors->any())
        <div class="kt-alert kt-alert-danger">
         <i class="ki-filled ki-information-2"></i>
         <ul class="list-disc list-inside text-sm">
          @foreach($errors->all() as $err)
           <li>{{ $err }}</li>
          @endforeach
         </ul>
        </div>
       @endif
       <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-2">
         <label class="kt-label" for="create_nom">Nom <span class="text-destructive">*</span></label>
         <input type="text" name="nom" id="create_nom" class="kt-input" value="{{ old('nom') }}" required maxlength="100" placeholder="Nom de famille">
        </div>
        <div class="flex flex-col gap-2">
         <label class="kt-label" for="create_prenom">Prénom <span class="text-destructive">*</span></label>
         <input type="text" name="prenom" id="create_prenom" class="kt-input" value="{{ old('prenom') }}" required maxlength="100" placeholder="Prénom">
        </div>
       </div>
       <div class="flex flex-col gap-2">
        <label class="kt-label" for="create_email">Email <span class="text-destructive">*</span></label>
        <input type="email" name="email" id="create_email" class="kt-input" value="{{ old('email') }}" required maxlength="100" placeholder="email@exemple.com">
       </div>
       <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-2">
         <label class="kt-label" for="create_mot_de_passe">Mot de passe <span class="text-destructive">*</span></label>
         <input type="password" name="mot_de_passe" id="create_mot_de_passe" class="kt-input" required minlength="8" placeholder="Min. 8 caractères">
        </div>
        <div class="flex flex-col gap-2">
         <label class="kt-label" for="create_mot_de_passe_confirmation">Confirmer le mot de passe <span class="text-destructive">*</span></label>
         <input type="password" name="mot_de_passe_confirmation" id="create_mot_de_passe_confirmation" class="kt-input" required minlength="8" placeholder="Répéter le mot de passe">
        </div>
       </div>
       <div class="flex flex-col gap-2">
        <label class="kt-label" for="create_telephone">Téléphone</label>
        <input type="text" name="telephone" id="create_telephone" class="kt-input" value="{{ old('telephone') }}" maxlength="20" placeholder="Optionnel">
       </div>
       <div class="flex flex-col gap-2">
        <label class="kt-label" for="create_statut">Statut <span class="text-destructive">*</span></label>
        <select name="statut" id="create_statut" class="kt-select" data-kt-select="true" required>
         <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
         <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
         <option value="suspendu" {{ old('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
        </select>
       </div>
       <div class="flex flex-col gap-2">
        <label class="kt-label" for="create_profils">Profils <span class="text-destructive">*</span></label>
        <select name="profils[]" id="create_profils" class="kt-select" data-kt-select="true" multiple required>
         @foreach($profils ?? [] as $profil)
          <option value="{{ $profil->id }}" {{ in_array($profil->id, old('profils', [])) ? 'selected' : '' }}>{{ $profil->libelle }}</option>
         @endforeach
        </select>
        <span class="text-xs text-muted-foreground">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs profils.</span>
       </div>
       <div class="flex flex-col gap-2">
        <label class="kt-label" for="create_photo_profil">Photo de profil</label>
        <input type="file" name="photo_profil" id="create_photo_profil" class="kt-input" accept="image/jpeg,image/png,image/jpg">
        <span class="text-xs text-muted-foreground">JPEG, PNG ou JPG, max. 2 Mo.</span>
       </div>
      </div>
      <div class="kt-modal-footer">
       <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Annuler</button>
       <button type="submit" class="kt-btn kt-btn-primary">
        <i class="ki-filled ki-check me-1"></i>
        Créer l'utilisateur
       </button>
      </div>
     </form>
    </div>
   </div>

   <!-- Modal de Profil Utilisateur -->
   <div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_profile" style="display: none;">
      <div class="kt-modal-content kt-container-fixed p-0" id="modal_profile_content">
       <div class="kt-modal-header rounded-t-lg p-0 border-0 relative flex flex-col items-center justify-center bg-center bg-cover bg-no-repeat mb-7 modal-bg" style="min-height: 200px; padding: 2rem 1rem;">
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline absolute top-4 end-4 shadow-default" data-kt-modal-dismiss="true">
          <i class="ki-filled ki-cross"></i>
         </button>
         <div class="flex flex-col items-center justify-center w-full">
          <div class="flex justify-center mb-4">
           <div class="relative">
            <img id="modal_profile_avatar" class="rounded-full border-3 border-green-500 w-[100px] h-[100px] object-cover" src="" alt="Avatar" style="display: none;">
           </div>
          </div>
          <div class="flex items-center flex-col">
           <div class="flex items-center gap-2 mb-2">
            <span id="modal_profile_name" class="text-xl leading-6 font-semibold text-foreground"></span>
            <svg class="text-primary" fill="none" height="18" viewBox="0 0 15 16" width="18" xmlns="http://www.w3.org/2000/svg">
             <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor"></path>
            </svg>
           </div>
           <div class="flex flex-wrap justify-center gap-3 lg:gap-4 text-sm">
            <div class="flex gap-2 items-center" id="modal_profile_profils_container" style="display: none;">
             <span class="kt-badge kt-badge-primary kt-badge-outline">
              <i class="ki-filled ki-abstract-41 me-1"></i>
              <span id="modal_profile_profils" class="font-medium"></span>
             </span>
            </div>
            <div class="flex gap-2 items-center">
             <i class="ki-filled ki-sms text-primary text-lg"></i>
             <a id="modal_profile_email" class="text-secondary-foreground hover:text-primary font-medium" href="#"></a>
            </div>
           </div>
          </div>
         </div>
       </div>
       <div class="kt-modal-body kt-scrollable-y py-5 mb-5 px-6">
        <div class="flex justify-center">
         <div class="w-full max-w-2xl">
          <div class="kt-card shadow-sm">
           <div class="kt-card-header bg-primary/5 border-b border-primary/20">
            <h3 class="kt-card-title text-primary flex items-center gap-2">
             <i class="ki-filled ki-information-circle text-xl"></i>
             Informations détaillées
            </h3>
           </div>
           <div class="kt-card-content pt-5 pb-6">
            <table class="kt-table-auto w-full">
             <tbody id="modal_profile_about">
              <!-- Les informations seront chargées dynamiquement -->
             </tbody>
            </table>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
   <!-- End Modal de Profil Utilisateur -->
   </main>

<script>
// La fonction loadUserProfile est définie dans resources/js/page-init.js
// et est disponible globalement via window.loadUserProfile


// Fonction d'initialisation pour la page utilisateurs (appelée après navigation AJAX)
window.initUtilisateursPage = function() {
    // Réinitialiser les modals Metronic
    setTimeout(() => {
        if (window.MetronicCore && typeof window.MetronicCore.initModals === 'function') {
            window.MetronicCore.initModals();
        }
        
        // Réinitialiser les selects Metronic pour le modal de création
        const createProfilsSelect = document.getElementById('create_profils');
        const createStatutSelect = document.getElementById('create_statut');
        
        if (createProfilsSelect) {
            createProfilsSelect.setAttribute('data-kt-select', 'true');
            if (typeof KTSelect !== 'undefined') {
                try {
                    const existingInstance = KTSelect.getInstance(createProfilsSelect);
                    if (existingInstance) {
                        existingInstance.destroy();
                    }
                    new KTSelect(createProfilsSelect);
                } catch (error) {
                    console.warn('Erreur initialisation select profils:', error);
                }
            }
        }
        
        if (createStatutSelect) {
            createStatutSelect.setAttribute('data-kt-select', 'true');
            if (typeof KTSelect !== 'undefined') {
                try {
                    const existingInstance = KTSelect.getInstance(createStatutSelect);
                    if (existingInstance) {
                        existingInstance.destroy();
                    }
                    new KTSelect(createStatutSelect);
                } catch (error) {
                    console.warn('Erreur initialisation select statut:', error);
                }
            }
        }
    }, 200);
};

// Initialisation sur chargement normal
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.initUtilisateursPage();
        
        // Réouvrir le modal de création si des erreurs de validation (après soumission)
        @if($errors->any())
        setTimeout(() => {
            var modal = document.getElementById('modal_nouvel_utilisateur');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('show');
                modal.classList.remove('hidden');
            }
        }, 300);
        @endif
    });
} else {
    window.initUtilisateursPage();
    
    // Réouvrir le modal de création si des erreurs de validation (après soumission)
    @if($errors->any())
    setTimeout(() => {
        var modal = document.getElementById('modal_nouvel_utilisateur');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
            modal.classList.remove('hidden');
        }
    }, 300);
    @endif
}

// Réinitialisation explicite après navigation AJAX (quand la page Utilisateurs est chargée via AJAX)
document.addEventListener('ajax-content-loaded', function() {
    if (document.getElementById('modal_nouvel_utilisateur')) {
        window.initUtilisateursPage();
    }
});

// Filtrage en temps réel via AJAX
(function() {
    let searchTimeout;
    
    // Fonction pour charger les utilisateurs via AJAX
    function loadUtilisateurs() {
        // Récupérer les valeurs actuelles des filtres à chaque appel
        const filterStatut = document.getElementById('filter-statut');
        const filterProfil = document.getElementById('filter-profil');
        const filterSearch = document.getElementById('filter-search');
        
        if (!filterStatut || !filterProfil || !filterSearch) {
            console.error('Éléments de filtre non trouvés');
            return;
        }
        
        const statut = filterStatut.value;
        const profilId = filterProfil.value;
        const search = filterSearch.value;
        
        console.log('Chargement avec filtres:', { statut, profilId, search });
        
        // Construire l'URL avec les paramètres
        const params = new URLSearchParams();
        if (statut) params.append('statut', statut);
        if (profilId) params.append('profil_id', profilId);
        if (search) params.append('search', search);
        
        const url = '{{ route("utilisateurs.index") }}' + (params.toString() ? '?' + params.toString() : '');
        
        // Récupérer seulement la section des résultats (pas les filtres)
        const resultsContainer = document.querySelector('#team_crew_card, #team_crew_list').parentElement;
        const countElement = document.querySelector('h3.text-base.text-mono');
        
        if (resultsContainer) {
            resultsContainer.style.opacity = '0.6';
            resultsContainer.style.pointerEvents = 'none';
        }
        
        // Faire la requête AJAX
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur de chargement');
            return response.text();
        })
        .then(html => {
            // Parser le HTML reçu
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Extraire seulement la section des résultats (pas les filtres)
            const newResults = doc.querySelector('#team_crew_card, #team_crew_list').parentElement;
            const newCount = doc.querySelector('h3.text-base.text-mono');
            
            if (newResults && resultsContainer) {
                // Remplacer seulement les résultats
                resultsContainer.innerHTML = newResults.innerHTML;
                
                // Mettre à jour le compteur
                if (newCount && countElement) {
                    countElement.textContent = newCount.textContent;
                }
                
                // Restaurer l'opacité
                resultsContainer.style.opacity = '1';
                resultsContainer.style.pointerEvents = 'auto';
                
                // Mettre à jour l'URL sans recharger la page
                window.history.pushState({}, '', url);
                
                console.log('Utilisateurs rechargés via AJAX');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des utilisateurs:', error);
            if (resultsContainer) {
                resultsContainer.style.opacity = '1';
                resultsContainer.style.pointerEvents = 'auto';
            }
        });
    }
    
    // Utiliser la délégation d'événements sur le document pour capturer les changements
    document.addEventListener('change', function(e) {
        if (e.target.id === 'filter-statut' || e.target.id === 'filter-profil') {
            console.log('Filtre changé:', e.target.id, '=', e.target.value);
            loadUtilisateurs();
        }
    });
    
    // Pour le champ de recherche, utiliser input avec debounce
    document.addEventListener('input', function(e) {
        if (e.target.id === 'filter-search') {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                console.log('Recherche:', e.target.value);
                loadUtilisateurs();
            }, 500);
        }
    });
    
    // Support de la touche Entrée pour recherche immédiate
    document.addEventListener('keydown', function(e) {
        if (e.target.id === 'filter-search' && e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            loadUtilisateurs();
        }
    });
    
    console.log('Filtrage en temps réel activé pour les utilisateurs (avec délégation d\'événements)');
})();
</script>
@endsection