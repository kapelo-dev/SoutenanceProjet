@extends('layouts.demo1.base')

@section('content')
<main class="grow" id="content" role="content">
    <!-- Container -->
    <div class="kt-container-fixed" id="contentContainer">
    </div>
    <!-- End of Container -->
    <!-- Titre + boutons en haut (comme les autres pages) -->
    <div class="kt-container-fixed">
     <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
      <div class="flex flex-col justify-center gap-2">
       <h1 class="text-2xl font-semibold leading-none text-mono">
        Liste des kiosques
       </h1>
       <div id="kiosques-count" class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
        Affichage de {{ $kiosques->total() }} kiosque{{ $kiosques->total() > 1 ? 's' : '' }}.
       </div>
      </div>
      <div class="flex items-center gap-2.5">
       <a href="{{ route('kiosques.create') }}" class="kt-btn kt-btn-primary">
        <i class="ki-filled ki-plus"></i>
        Créer un kiosque
       </a>
       <a href="{{ route('kiosques.carte') }}" class="kt-btn kt-btn-outline">
        <i class="ki-filled ki-geolocation"></i>
        Carte des kiosques
       </a>
      </div>
     </div>
    </div>
    <!-- Filtres -->
    <div class="kt-container-fixed">
     <div class="flex flex-col items-stretch gap-5 lg:gap-7.5">
      <div class="flex flex-wrap items-center gap-5 justify-end">
       <div class="flex items-center flex-wrap gap-5">
        <form method="GET" action="{{ route('kiosques.index') }}" class="flex items-center gap-2.5" id="form-filters-kiosques">
         <select name="statut" id="filter-kiosque-statut" class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Sélectionner un statut">
          <option value="">Tous les statuts</option>
          <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
          <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
          <option value="en_travaux" {{ request('statut') == 'en_travaux' ? 'selected' : '' }}>En travaux</option>
         </select>
         <select name="type" id="filter-kiosque-type" class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Sélectionner un type">
          <option value="">Tous les types</option>
          <option value="fixe" {{ request('type') == 'fixe' ? 'selected' : '' }}>Fixe</option>
          <option value="mobile" {{ request('type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
         </select>
         @if($villes && $villes->count() > 0)
         <select name="ville" id="filter-kiosque-ville" class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Sélectionner une ville">
          <option value="">Toutes les villes</option>
          @foreach($villes as $ville)
          <option value="{{ $ville }}" {{ request('ville') == $ville ? 'selected' : '' }}>{{ $ville }}</option>
          @endforeach
         </select>
         @endif
         <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
        <form method="GET" action="{{ route('kiosques.index') }}" class="flex">
         <label class="kt-input">
          <i class="ki-filled ki-magnifier"></i>
          <input placeholder="Rechercher par nom, code, quartier" type="text" name="search" id="filter-kiosque-search" value="{{ request('search') }}"/>
          @if(request('statut'))<input type="hidden" name="statut" value="{{ request('statut') }}">@endif
          @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
          @if(request('ville'))<input type="hidden" name="ville" value="{{ request('ville') }}">@endif
         </label>
        </form>
        <div class="kt-toggle-group kt-toggle-group-sm" data-kt-tabs="true">
         <a class="kt-btn kt-btn-icon" data-kt-tab-toggle="#kiosques_card" href="#"><i class="ki-filled ki-category"></i></a>
         <a class="kt-btn kt-btn-icon active" data-kt-tab-toggle="#kiosques_list" href="#"><i class="ki-filled ki-row-horizontal"></i></a>
        </div>
       </div>
      </div>
      <div class="hidden flex flex-col gap-5 lg:gap-7.5" id="kiosques_card">
       <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
        @forelse($kiosques as $kiosque)
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="size-20 relative">
            @if($kiosque->photo)
            <img class="rounded-full w-20 h-20 object-cover" src="{{ asset('storage/' . $kiosque->photo) }}" alt="{{ $kiosque->nom }}"/>
            @else
            <div class="flex items-center justify-center relative text-2xl {{ $kiosque->statut == 'actif' ? 'text-green-500 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30' : ($kiosque->statut == 'en_travaux' ? 'text-yellow-500 ring-yellow-200 dark:ring-yellow-950 bg-yellow-50 dark:bg-yellow-950/30' : 'text-muted-foreground ring-muted bg-muted/5') }} size-20 ring-1 rounded-full">
             {{ strtoupper(substr($kiosque->nom, 0, 1)) }}
            </div>
            @endif
            <div class="flex size-2.5 {{ $kiosque->statut == 'actif' ? 'bg-green-500' : ($kiosque->statut == 'en_travaux' ? 'bg-yellow-500' : 'bg-gray-500') }} rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" href="{{ route('kiosques.show', $kiosque) }}">
            {{ $kiosque->nom }}
           </a>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           @if($kiosque->code)
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            {{ $kiosque->code }}
           </div>
           @endif
           @if($kiosque->quartier)
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-geolocation me-1 text-muted-foreground">
            </i>
            {{ $kiosque->quartier }}
            @if($kiosque->ville)
            , {{ $kiosque->ville }}
            @endif
           </div>
           @endif
          </div>
          @if($kiosque->telephone)
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-phone me-1 text-muted-foreground">
            </i>
            {{ $kiosque->telephone }}
           </div>
          </div>
          @endif
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $kiosque->agents->count() }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Agent{{ $kiosque->agents->count() > 1 ? 's' : '' }}
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $kiosque->placesDisponibles() }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Place{{ $kiosque->placesDisponibles() > 1 ? 's' : '' }} disponible{{ $kiosque->placesDisponibles() > 1 ? 's' : '' }}
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             {{ ucfirst($kiosque->type) }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Type
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center gap-2">
          <a class="kt-btn kt-btn-sm {{ $kiosque->statut == 'actif' ? 'kt-btn-primary' : ($kiosque->statut == 'en_travaux' ? 'kt-btn-warning' : 'kt-btn-outline') }}">
           <i class="ki-filled ki-check-circle">
           </i>
           {{ ucfirst(str_replace('_', ' ', $kiosque->statut)) }}
          </a>
          <a href="{{ route('kiosques.edit', $kiosque) }}" class="kt-btn kt-btn-sm kt-btn-outline">
           <i class="ki-filled ki-pencil"></i>
           Modifier
          </a>
         </div>
        </div>
        @empty
        <div class="col-span-2">
         <div class="kt-card">
          <div class="kt-card-content text-center py-10">
           <p class="text-secondary-foreground">Aucun kiosque trouvé</p>
          </div>
         </div>
        </div>
        @endforelse
       </div>
      </div>
      <div id="kiosques_list">
       <div class="grid grid-cols-1 gap-5 lg:gap-7.5">
        @forelse($kiosques as $kiosque)
        <div class="kt-card p-7.5 border-l-4 {{ $kiosque->statut == 'actif' ? 'border-l-green-500' : ($kiosque->statut == 'en_travaux' ? 'border-l-yellow-500' : 'border-l-gray-400') }}">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            @if($kiosque->photo)
            <div class="size-20 relative">
             <img class="rounded-full w-20 h-20 object-cover" src="{{ asset('storage/' . $kiosque->photo) }}" alt="{{ $kiosque->nom }}"/>
             <div class="flex size-2.5 {{ $kiosque->statut == 'actif' ? 'bg-green-500' : ($kiosque->statut == 'en_travaux' ? 'bg-yellow-500' : 'bg-gray-500') }} rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
            @else
            <div class="flex items-center justify-center relative text-2xl {{ $kiosque->statut == 'actif' ? 'text-green-500 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30' : ($kiosque->statut == 'en_travaux' ? 'text-yellow-500 ring-yellow-200 dark:ring-yellow-950 bg-yellow-50 dark:bg-yellow-950/30' : 'text-muted-foreground ring-muted bg-muted/5') }} size-20 ring-1 rounded-full">
             {{ strtoupper(substr($kiosque->nom, 0, 1)) }}
             <div class="flex size-2.5 {{ $kiosque->statut == 'actif' ? 'bg-green-500' : ($kiosque->statut == 'en_travaux' ? 'bg-yellow-500' : 'bg-gray-500') }} rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
            @endif
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" href="{{ route('kiosques.show', $kiosque) }}">
              {{ $kiosque->nom }}
             </a>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             @if($kiosque->code)
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              {{ $kiosque->code }}
             </div>
             @endif
             @if($kiosque->quartier)
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-geolocation me-1 text-muted-foreground">
              </i>
              {{ $kiosque->quartier }}
              @if($kiosque->ville)
              , {{ $kiosque->ville }}
              @endif
             </div>
             @endif
             @if($kiosque->telephone)
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-phone me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="tel:{{ $kiosque->telephone }}">
               {{ $kiosque->telephone }}
              </a>
             </div>
             @endif
            </div>
           </div>
          </div>
         </div>
         <div class="flex items-center flex-wrap gap-5 lg:gap-11">
          <div class="flex items-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
            <span class="text-mono text-sm leading-none font-medium">
             {{ $kiosque->agents->count() }}/{{ $kiosque->capacite_agents }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Agents
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
            <span class="text-mono text-sm leading-none font-medium">
             {{ ucfirst($kiosque->type) }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Type
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
            <span class="text-mono text-sm leading-none font-medium">
             {{ ucfirst(str_replace('_', ' ', $kiosque->statut)) }}
            </span>
            <span class="text-secondary-foreground text-xs">
             Statut
            </span>
           </div>
          </div>
          <div class="text-right flex items-center gap-2">
           <a class="kt-btn kt-btn-sm {{ $kiosque->statut == 'actif' ? 'kt-btn-primary' : ($kiosque->statut == 'en_travaux' ? 'kt-btn-warning' : 'kt-btn-outline') }}">
            <i class="ki-filled ki-check-circle">
            </i>
            {{ ucfirst(str_replace('_', ' ', $kiosque->statut)) }}
           </a>
           <a href="{{ route('kiosques.edit', $kiosque) }}" class="kt-btn kt-btn-sm kt-btn-outline">
            <i class="ki-filled ki-pencil"></i>
            Modifier
           </a>
          </div>
         </div>
        </div>
        @empty
        <div class="kt-card">
         <div class="kt-card-content text-center py-10">
          <p class="text-secondary-foreground">Aucun kiosque trouvé</p>
         </div>
        </div>
        @endforelse
       </div>
       @if($kiosques->hasPages())
       <div class="flex grow justify-center pt-5 lg:pt-7.5">
        {{ $kiosques->links() }}
       </div>
       @endif
      </div>
     </div>
    </div>
   </div>
    <!-- End of Container -->
   </main>

<script>
// Filtrage en temps réel via AJAX pour les kiosques
(function() {
    let searchTimeout;
    
    // Fonction pour charger les kiosques via AJAX
    function loadKiosques() {
        const filterStatut = document.getElementById('filter-kiosque-statut');
        const filterType = document.getElementById('filter-kiosque-type');
        const filterVille = document.getElementById('filter-kiosque-ville');
        const filterSearch = document.getElementById('filter-kiosque-search');
        
        if (!filterStatut || !filterType || !filterSearch) {
            console.error('Éléments de filtre non trouvés');
            return;
        }
        
        const statut = filterStatut.value;
        const type = filterType.value;
        const ville = filterVille ? filterVille.value : '';
        const search = filterSearch.value;
        
        console.log('Chargement avec filtres:', { statut, type, ville, search });
        
        // Construire l'URL avec les paramètres
        const params = new URLSearchParams();
        if (statut) params.append('statut', statut);
        if (type) params.append('type', type);
        if (ville) params.append('ville', ville);
        if (search) params.append('search', search);
        
        const url = '{{ route("kiosques.index") }}' + (params.toString() ? '?' + params.toString() : '');
        
        // Récupérer la section des résultats
        const resultsContainer = document.querySelector('#kiosques_card, #kiosques_list').parentElement;
        const countElement = document.getElementById('kiosques-count');
        
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
            
            // Extraire seulement la section des résultats
            const newResults = doc.querySelector('#kiosques_card, #kiosques_list').parentElement;
            const newCount = doc.getElementById('kiosques-count');
            
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
                
                console.log('Kiosques rechargés via AJAX');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des kiosques:', error);
            if (resultsContainer) {
                resultsContainer.style.opacity = '1';
                resultsContainer.style.pointerEvents = 'auto';
            }
        });
    }
    
    // Utiliser la délégation d'événements sur le document
    document.addEventListener('change', function(e) {
        if (e.target.id === 'filter-kiosque-statut' || 
            e.target.id === 'filter-kiosque-type' || 
            e.target.id === 'filter-kiosque-ville') {
            console.log('Filtre changé:', e.target.id, '=', e.target.value);
            loadKiosques();
        }
    });
    
    // Pour le champ de recherche, utiliser input avec debounce
    document.addEventListener('input', function(e) {
        if (e.target.id === 'filter-kiosque-search') {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                console.log('Recherche:', e.target.value);
                loadKiosques();
            }, 500);
        }
    });
    
    // Support de la touche Entrée pour recherche immédiate
    document.addEventListener('keydown', function(e) {
        if (e.target.id === 'filter-kiosque-search' && e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            loadKiosques();
        }
    });
    
    console.log('Filtrage en temps réel activé pour les kiosques');
})();
</script>
@endsection
