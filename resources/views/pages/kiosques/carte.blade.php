@extends('layouts.demo1.base')

@section('content')
<main class="grow" id="content" role="content">
    <!-- Container -->
    <div class="kt-container-fixed" id="contentContainer">
    </div>
    <!-- End of Container -->
    <!-- Container -->
    <div class="kt-container-fixed">
     <div class="flex flex-col items-stretch gap-5 lg:gap-7.5">
      <div class="flex flex-wrap items-center gap-5 justify-between">
       <h3 class="text-base text-mono font-medium">
        Carte des kiosques ({{ $kiosques->count() }} kiosque{{ $kiosques->count() > 1 ? 's' : '' }})
       </h3>
       <div class="flex items-center flex-wrap gap-5">
        <a href="{{ route('kiosques.index') }}" class="kt-btn kt-btn-outline">
         <i class="ki-filled ki-row-horizontal"></i>
         Liste des kiosques
        </a>
       </div>
      </div>
      <div class="kt-card">
       <div class="kt-card-content p-0">
        <div id="kiosques_map" style="height: 600px; width: 100%; border-radius: 8px; overflow: hidden;"></div>
       </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
       @forelse($kiosques as $kiosque)
       <div class="kt-card">
        <div class="kt-card-content pt-5 pb-5">
         <div class="flex items-center gap-3 mb-3">
          @if($kiosque->photo)
          <img class="rounded-full w-12 h-12 object-cover" src="{{ asset('storage/' . $kiosque->photo) }}" alt="{{ $kiosque->nom }}"/>
          @else
          <div class="flex items-center justify-center text-lg {{ $kiosque->statut == 'actif' ? 'text-green-500 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30' : ($kiosque->statut == 'en_travaux' ? 'text-yellow-500 ring-yellow-200 dark:ring-yellow-950 bg-yellow-50 dark:bg-yellow-950/30' : 'text-muted-foreground ring-muted bg-muted/5') }} w-12 h-12 ring-1 rounded-full">
           {{ strtoupper(substr($kiosque->nom, 0, 1)) }}
          </div>
          @endif
          <div class="flex-1">
           <h4 class="text-base font-semibold text-foreground mb-1">
            {{ $kiosque->nom }}
           </h4>
           @if($kiosque->code)
           <p class="text-sm text-secondary-foreground">
            {{ $kiosque->code }}
           </p>
           @endif
          </div>
          <div class="flex size-2.5 {{ $kiosque->statut == 'actif' ? 'bg-green-500' : ($kiosque->statut == 'en_travaux' ? 'bg-yellow-500' : 'bg-gray-500') }} rounded-full">
          </div>
         </div>
         <div class="flex flex-col gap-2 text-sm">
          @if($kiosque->quartier || $kiosque->ville)
          <div class="flex items-center text-secondary-foreground">
           <i class="ki-filled ki-geolocation me-2 text-muted-foreground">
           </i>
           @if($kiosque->quartier)
           {{ $kiosque->quartier }}
           @endif
           @if($kiosque->quartier && $kiosque->ville)
           , 
           @endif
           @if($kiosque->ville)
           {{ $kiosque->ville }}
           @endif
          </div>
          @endif
          @if($kiosque->telephone)
          <div class="flex items-center text-secondary-foreground">
           <i class="ki-filled ki-phone me-2 text-muted-foreground">
           </i>
           <a href="tel:{{ $kiosque->telephone }}" class="hover:text-primary">
            {{ $kiosque->telephone }}
           </a>
          </div>
          @endif
          <div class="flex items-center justify-between pt-2 border-t border-input">
           <span class="text-secondary-foreground">
            Agents:
           </span>
           <span class="font-medium text-foreground">
            {{ $kiosque->agents->count() }}/{{ $kiosque->capacite_agents }}
           </span>
          </div>
          <div class="flex items-center justify-between">
           <span class="text-secondary-foreground">
            Type:
           </span>
           <span class="font-medium text-foreground">
            {{ ucfirst($kiosque->type) }}
           </span>
          </div>
          <div class="flex items-center justify-between">
           <span class="text-secondary-foreground">
            Statut:
           </span>
           <span class="font-medium {{ $kiosque->statut == 'actif' ? 'text-green-500' : ($kiosque->statut == 'en_travaux' ? 'text-yellow-500' : 'text-gray-500') }}">
            {{ ucfirst(str_replace('_', ' ', $kiosque->statut)) }}
           </span>
          </div>
         </div>
        </div>
        <div class="kt-card-footer justify-center">
         <a href="{{ route('kiosques.show', $kiosque) }}" class="kt-btn kt-btn-outline kt-btn-sm">
          <i class="ki-filled ki-eye">
          </i>
          Voir détails
         </a>
        </div>
       </div>
       @empty
       <div class="col-span-3">
        <div class="kt-card">
         <div class="kt-card-content text-center py-10">
          <p class="text-secondary-foreground">Aucun kiosque avec coordonnées GPS trouvé</p>
         </div>
        </div>
       </div>
       @endforelse
      </div>
     </div>
    </div>
    <!-- End of Container -->
   </main>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let kiosquesMap;
let kiosquesMarkers = [];

document.addEventListener('DOMContentLoaded', function() {
    initKiosquesMap();
});

function initKiosquesMap() {
    const mapElement = document.getElementById('kiosques_map');
    if (!mapElement) {
        return;
    }
    
    // Coordonnées par défaut (Lomé, Togo)
    const defaultLat = 6.1375;
    const defaultLng = 1.2123;
    
    // Initialiser la carte
    kiosquesMap = L.map('kiosques_map').setView([defaultLat, defaultLng], 12);
    
    // Ajouter la couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(kiosquesMap);
    
    // Charger les données des kiosques
    fetch('/api/kiosques/carte-data')
        .then(response => response.json())
        .then(kiosques => {
            if (kiosques.length === 0) {
                return;
            }
            
            // Ajuster la vue pour afficher tous les kiosques
            if (kiosques.length === 1) {
                kiosquesMap.setView([kiosques[0].latitude, kiosques[0].longitude], 15);
            } else {
                const bounds = L.latLngBounds(kiosques.map(k => [k.latitude, k.longitude]));
                kiosquesMap.fitBounds(bounds, { padding: [50, 50] });
            }
            
            // Ajouter les marqueurs
            kiosques.forEach(kiosque => {
                // Couleur du marqueur selon le statut
                let markerColor = '#10b981'; // Vert par défaut (actif)
                if (kiosque.est_sature) {
                    markerColor = '#ef4444'; // Rouge si saturé
                } else if (kiosque.statut === 'en_travaux') {
                    markerColor = '#f59e0b'; // Jaune si en travaux
                } else if (kiosque.statut === 'inactif') {
                    markerColor = '#6b7280'; // Gris si inactif
                }
                
                // Créer un marqueur personnalisé
                const kiosqueIcon = L.divIcon({
                    className: 'kiosque-marker',
                    html: `<div style="background-color: ${markerColor}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><i class="ki-filled ki-geolocation text-white" style="font-size: 16px;"></i></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                
                const marker = L.marker([kiosque.latitude, kiosque.longitude], {
                    icon: kiosqueIcon
                }).addTo(kiosquesMap);
                
                // Popup avec les informations du kiosque
                const popupContent = `
                    <div style="min-width: 200px;">
                        <h4 style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">${kiosque.nom}</h4>
                        ${kiosque.code ? `<p style="margin: 4px 0; font-size: 12px; color: #6b7280;">${kiosque.code}</p>` : ''}
                        ${kiosque.quartier ? `<p style="margin: 4px 0; font-size: 12px; color: #6b7280;">📍 ${kiosque.quartier}${kiosque.ville ? ', ' + kiosque.ville : ''}</p>` : ''}
                        <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Agents: ${kiosque.agents_count}/${kiosque.capacite}</p>
                        <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Type: ${kiosque.type === 'fixe' ? 'Fixe' : 'Mobile'}</p>
                        <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">Statut: ${kiosque.statut === 'actif' ? 'Actif' : (kiosque.statut === 'en_travaux' ? 'En travaux' : 'Inactif')}</p>
                        ${kiosque.telephone ? `<p style="margin: 4px 0; font-size: 12px;"><a href="tel:${kiosque.telephone}" style="color: #3b82f6;">${kiosque.telephone}</a></p>` : ''}
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                kiosquesMarkers.push(marker);
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des kiosques:', error);
        });
}
</script>
@endsection
