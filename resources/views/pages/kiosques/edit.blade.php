@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Modifier le Kiosque
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                {{ $kiosque->nom }}
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('kiosques.show', $kiosque) }}" class="kt-btn kt-btn-outline">
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
        <form action="{{ route('kiosques.update', $kiosque) }}" method="POST" enctype="multipart/form-data" class="kt-card-content" data-ajax="false">
            @csrf
            @method('PUT')

            @if(session('success'))
                <div class="kt-alert kt-alert-success mb-5">
                    <div class="kt-alert-content">{{ session('success') }}</div>
                </div>
            @endif

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
                        <input class="kt-input @error('nom') kt-input-error @enderror" type="text" name="nom" value="{{ old('nom', $kiosque->nom) }}" required />
                        @error('nom')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Code
                        </label>
                        <input class="kt-input @error('code') kt-input-error @enderror" type="text" name="code" value="{{ old('code', $kiosque->code) }}" />
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
                            <option value="fixe" {{ old('type', $kiosque->type) == 'fixe' ? 'selected' : '' }}>Fixe</option>
                            <option value="mobile" {{ old('type', $kiosque->type) == 'mobile' ? 'selected' : '' }}>Mobile</option>
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
                            <option value="actif" {{ old('statut', $kiosque->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut', $kiosque->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="en_travaux" {{ old('statut', $kiosque->statut) == 'en_travaux' ? 'selected' : '' }}>En travaux</option>
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
                        <input class="kt-input @error('capacite_agents') kt-input-error @enderror" type="number" name="capacite_agents" value="{{ old('capacite_agents', $kiosque->capacite_agents) }}" min="1" max="20" required />
                        @error('capacite_agents')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Téléphone
                        </label>
                        <input class="kt-input @error('telephone') kt-input-error @enderror" type="text" name="telephone" value="{{ old('telephone', $kiosque->telephone) }}" />
                        @error('telephone')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">
                        Adresse
                    </label>
                    <input class="kt-input @error('adresse') kt-input-error @enderror" type="text" name="adresse" value="{{ old('adresse', $kiosque->adresse) }}" />
                    @error('adresse')
                        <span class="text-xs text-destructive">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Quartier
                        </label>
                        <input class="kt-input @error('quartier') kt-input-error @enderror" type="text" name="quartier" value="{{ old('quartier', $kiosque->quartier) }}" />
                        @error('quartier')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Ville
                        </label>
                        <input class="kt-input @error('ville') kt-input-error @enderror" type="text" name="ville" value="{{ old('ville', $kiosque->ville) }}" />
                        @error('ville')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Latitude
                        </label>
                        <input class="kt-input @error('latitude') kt-input-error @enderror" type="number" name="latitude" value="{{ old('latitude', $kiosque->latitude) }}" step="0.00000001" min="-90" max="90" />
                        @error('latitude')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Longitude
                        </label>
                        <input class="kt-input @error('longitude') kt-input-error @enderror" type="number" name="longitude" value="{{ old('longitude', $kiosque->longitude) }}" step="0.00000001" min="-180" max="180" />
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
                        <input class="kt-input @error('horaire_ouverture') kt-input-error @enderror" type="time" name="horaire_ouverture" value="{{ old('horaire_ouverture', $kiosque->horaire_ouverture) }}" />
                        @error('horaire_ouverture')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">
                            Horaire de fermeture
                        </label>
                        <input class="kt-input @error('horaire_fermeture') kt-input-error @enderror" type="time" name="horaire_fermeture" value="{{ old('horaire_fermeture', $kiosque->horaire_fermeture) }}" />
                        @error('horaire_fermeture')
                            <span class="text-xs text-destructive">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">
                        Description
                    </label>
                    <textarea class="kt-input @error('description') kt-input-error @enderror" name="description" rows="4">{{ old('description', $kiosque->description) }}</textarea>
                    @error('description')
                        <span class="text-xs text-destructive">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">
                        Photo
                    </label>
                    @if($kiosque->photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $kiosque->photo) }}" alt="{{ $kiosque->nom }}" class="w-32 h-32 rounded-full object-cover" />
                        </div>
                    @endif
                    <input class="kt-input @error('photo') kt-input-error @enderror" type="file" name="photo" accept="image/jpeg,image/png,image/jpg" />
                    <span class="text-xs text-secondary-foreground">Format: JPEG, PNG, JPG (max 2MB)</span>
                    @error('photo')
                        <span class="text-xs text-destructive">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="kt-card-footer justify-end gap-2.5 mt-5">
                <a href="{{ route('kiosques.show', $kiosque) }}" class="kt-btn kt-btn-outline">
                    Annuler
                </a>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
<!-- End of Container -->
@endsection
