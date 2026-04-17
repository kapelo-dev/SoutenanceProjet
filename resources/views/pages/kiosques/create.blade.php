@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Créer un kiosque
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Renseignez les informations du nouveau kiosque
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('kiosques.index') }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i>
                Annuler
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->

<!-- Container -->
<div class="kt-container-fixed">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Informations du kiosque</h3>
        </div>
        <form action="{{ route('kiosques.store') }}" method="POST" enctype="multipart/form-data" class="kt-card-content" data-ajax="false">
            @csrf

            @if($errors->any())
                <div class="kt-alert kt-alert-danger mb-5">
                    <div class="kt-alert-content">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="flex flex-col gap-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Nom <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input @error('nom') kt-input-error @enderror" type="text" name="nom" value="{{ old('nom') }}" required />
                        @error('nom')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Code <span class="text-muted-foreground text-xs">(généré automatiquement)</span>
                        </label>
                        <input class="kt-input bg-muted cursor-not-allowed @error('code') kt-input-error @enderror" type="text" value="{{ old('code', $suggestedCode ?? '') }}" placeholder="Ex: K001" disabled readonly />
                        <input type="hidden" name="code" value="{{ old('code', $suggestedCode ?? '') }}" />
                        @error('code')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Type <span class="text-destructive">*</span>
                        </label>
                        <select class="kt-select @error('type') kt-input-error @enderror" name="type" data-kt-select="true" required>
                            <option value="fixe" {{ old('type', 'fixe') == 'fixe' ? 'selected' : '' }}>Fixe</option>
                            <option value="mobile" {{ old('type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                        </select>
                        @error('type')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Statut <span class="text-destructive">*</span>
                        </label>
                        <select class="kt-select @error('statut') kt-input-error @enderror" name="statut" data-kt-select="true" required>
                            <option value="actif" {{ old('statut', 'actif') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="en_travaux" {{ old('statut') == 'en_travaux' ? 'selected' : '' }}>En travaux</option>
                        </select>
                        @error('statut')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Capacité d'agents <span class="text-destructive">*</span>
                        </label>
                        <input class="kt-input @error('capacite_agents') kt-input-error @enderror" type="number" name="capacite_agents" value="{{ old('capacite_agents', 1) }}" min="1" max="20" required />
                        @error('capacite_agents')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Téléphone
                        </label>
                        <input class="kt-input @error('telephone') kt-input-error @enderror" type="text" name="telephone" value="{{ old('telephone') }}" />
                        @error('telephone')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">
                        Adresse
                    </label>
                    <input id="adresse" class="kt-input @error('adresse') kt-input-error @enderror" type="text" name="adresse" value="{{ old('adresse') }}" />
                    @error('adresse')
                        <span class="text-xs text-destructive">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Quartier
                        </label>
                        <input id="quartier" class="kt-input @error('quartier') kt-input-error @enderror" type="text" name="quartier" value="{{ old('quartier') }}" />
                        @error('quartier')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Ville
                        </label>
                        <select id="ville" class="kt-select @error('ville') kt-input-error @enderror" name="ville" data-kt-select="true">
                            <option value="">Sélectionner une ville</option>
                            <option value="Lomé" {{ old('ville') == 'Lomé' ? 'selected' : '' }}>Lomé</option>
                            <option value="Kara" {{ old('ville') == 'Kara' ? 'selected' : '' }}>Kara</option>
                            <option value="Sokodé" {{ old('ville') == 'Sokodé' ? 'selected' : '' }}>Sokodé</option>
                            <option value="Atakpamé" {{ old('ville') == 'Atakpamé' ? 'selected' : '' }}>Atakpamé</option>
                            <option value="Kpalimé" {{ old('ville') == 'Kpalimé' ? 'selected' : '' }}>Kpalimé</option>
                            <option value="Bassar" {{ old('ville') == 'Bassar' ? 'selected' : '' }}>Bassar</option>
                            <option value="Tsévié" {{ old('ville') == 'Tsévié' ? 'selected' : '' }}>Tsévié</option>
                            <option value="Aného" {{ old('ville') == 'Aného' ? 'selected' : '' }}>Aného</option>
                            <option value="Dapaong" {{ old('ville') == 'Dapaong' ? 'selected' : '' }}>Dapaong</option>
                            <option value="Mango" {{ old('ville') == 'Mango' ? 'selected' : '' }}>Mango</option>
                            <option value="Niamtougou" {{ old('ville') == 'Niamtougou' ? 'selected' : '' }}>Niamtougou</option>
                            <option value="Tabligbo" {{ old('ville') == 'Tabligbo' ? 'selected' : '' }}>Tabligbo</option>
                            <option value="Notsé" {{ old('ville') == 'Notsé' ? 'selected' : '' }}>Notsé</option>
                            <option value="Vogan" {{ old('ville') == 'Vogan' ? 'selected' : '' }}>Vogan</option>
                            <option value="Badou" {{ old('ville') == 'Badou' ? 'selected' : '' }}>Badou</option>
                            <option value="Kandé" {{ old('ville') == 'Kandé' ? 'selected' : '' }}>Kandé</option>
                            <option value="Tchamba" {{ old('ville') == 'Tchamba' ? 'selected' : '' }}>Tchamba</option>
                            <option value="Bafilo" {{ old('ville') == 'Bafilo' ? 'selected' : '' }}>Bafilo</option>
                        </select>
                        @error('ville')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Position sur la carte</label>
                    <p class="text-xs text-muted-foreground mb-2">Cliquez sur la carte ou déplacez le marqueur pour définir l'emplacement. Adresse, quartier, ville et coordonnées seront remplis automatiquement.</p>
                    <div id="kiosque_create_map" style="height: 400px; width: 100%; border-radius: 8px; overflow: hidden; border: 1px solid var(--border);"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Latitude
                        </label>
                        <input id="latitude" class="kt-input @error('latitude') kt-input-error @enderror" type="number" name="latitude" value="{{ old('latitude') }}" step="0.00000001" min="-90" max="90" />
                        @error('latitude')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Longitude
                        </label>
                        <input id="longitude" class="kt-input @error('longitude') kt-input-error @enderror" type="number" name="longitude" value="{{ old('longitude') }}" step="0.00000001" min="-180" max="180" />
                        @error('longitude')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Horaire d'ouverture
                        </label>
                        <input class="kt-input @error('horaire_ouverture') kt-input-error @enderror" type="time" name="horaire_ouverture" value="{{ old('horaire_ouverture', '08:00') }}" />
                        @error('horaire_ouverture')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Horaire de fermeture
                        </label>
                        <input class="kt-input @error('horaire_fermeture') kt-input-error @enderror" type="time" name="horaire_fermeture" value="{{ old('horaire_fermeture', '18:00') }}" />
                        @error('horaire_fermeture')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">
                        Description
                    </label>
                    <textarea class="kt-input @error('description') kt-input-error @enderror" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-xs text-destructive">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">
                        Photo
                    </label>
                    <input class="kt-input @error('photo') kt-input-error @enderror" type="file" name="photo" accept="image/jpeg,image/png,image/jpg" />
                    <span class="text-xs text-secondary-foreground">Format: JPEG, PNG, JPG (max 2MB)</span>
                    @error('photo')
                        <span class="text-xs text-destructive">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="kt-card-footer justify-end gap-2.5 mt-5">
                <a href="{{ route('kiosques.index') }}" class="kt-btn kt-btn-outline">
                    Annuler
                </a>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i>
                    Créer le kiosque
                </button>
            </div>
        </form>
    </div>
</div>
<!-- End of Container -->

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    // Lomé, Togo
    const defaultLat = 6.1375;
    const defaultLng = 1.2123;
    let mapCreate = null;
    let markerCreate = null;

    function updateCoords(lat, lng) {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        if (latInput) latInput.value = lat.toFixed(8);
        if (lngInput) lngInput.value = lng.toFixed(8);
    }

    function updateAddressFields(adresse, quartier, ville) {
        const adresseEl = document.getElementById('adresse');
        const quartierEl = document.getElementById('quartier');
        const villeEl = document.getElementById('ville');
        if (adresseEl) adresseEl.value = adresse || '';
        if (quartierEl) quartierEl.value = quartier || '';
        if (villeEl) villeEl.value = ville || '';
    }

    function reverseGeocode(lat, lng, callback) {
        const url = 'https://nominatim.openstreetmap.org/reverse?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng) + '&format=json&accept-language=fr';
        fetch(url, {
            headers: { 'Accept': 'application/json', 'User-Agent': 'PDVConnect-Kiosque/1.0' }
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

    function onPositionChange(lat, lng) {
        updateCoords(lat, lng);
        reverseGeocode(lat, lng, function(adresse, quartier, ville) {
            updateAddressFields(adresse, quartier, ville);
        });
    }

    function initKiosqueCreateMap() {
        const mapEl = document.getElementById('kiosque_create_map');
        if (!mapEl || typeof L === 'undefined') return;

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        let initialLat = defaultLat;
        let initialLng = defaultLng;
        if (latInput && lngInput && latInput.value && lngInput.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                initialLat = lat;
                initialLng = lng;
            }
        }

        if (mapCreate) {
            mapCreate.remove();
            mapCreate = null;
            markerCreate = null;
        }

        mapCreate = L.map('kiosque_create_map').setView([initialLat, initialLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(mapCreate);

        markerCreate = L.marker([initialLat, initialLng], { draggable: true }).addTo(mapCreate);
        onPositionChange(initialLat, initialLng);

        mapCreate.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            markerCreate.setLatLng([lat, lng]);
            onPositionChange(lat, lng);
        });

        markerCreate.on('dragend', function() {
            const pos = markerCreate.getLatLng();
            onPositionChange(pos.lat, pos.lng);
        });

        if (latInput && lngInput) {
            let timeout;
            function syncMarkerFromInputs() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && mapCreate && markerCreate) {
                        markerCreate.setLatLng([lat, lng]);
                        mapCreate.setView([lat, lng], mapCreate.getZoom());
                    }
                }, 500);
            }
            latInput.addEventListener('input', syncMarkerFromInputs);
            lngInput.addEventListener('input', syncMarkerFromInputs);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initKiosqueCreateMap, 100);
        });
    } else {
        setTimeout(initKiosqueCreateMap, 100);
    }

    document.addEventListener('ajax-content-loaded', function() {
        if (document.getElementById('kiosque_create_map')) {
            setTimeout(initKiosqueCreateMap, 200);
        }
    });
})();
</script>
@endsection
