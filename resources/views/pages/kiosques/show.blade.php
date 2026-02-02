@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Détails du Kiosque
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                {{ $kiosque->nom }}
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('kiosques.index') }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i>
                Retour
            </a>
            <a href="{{ route('kiosques.edit', $kiosque) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-pencil"></i>
                Modifier
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->

<!-- Container -->
<div class="kt-container-fixed">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
        <!-- Informations principales -->
        <div class="xl:col-span-2">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Informations générales</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Nom</label>
                            <div class="text-base font-medium">{{ $kiosque->nom }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Code</label>
                            <div class="text-base font-medium">{{ $kiosque->code ?? 'N/A' }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Type</label>
                            <div class="text-base font-medium">{{ ucfirst($kiosque->type) }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Statut</label>
                            <div>
                                <span class="kt-badge kt-badge-sm {{ $kiosque->statut == 'actif' ? 'kt-badge-success' : ($kiosque->statut == 'en_travaux' ? 'kt-badge-warning' : 'kt-badge-secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $kiosque->statut)) }}
                                </span>
                            </div>
                        </div>
                        @if($kiosque->adresse)
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label class="kt-form-label text-muted-foreground">Adresse</label>
                            <div class="text-base font-medium">{{ $kiosque->adresse }}</div>
                        </div>
                        @endif
                        @if($kiosque->quartier || $kiosque->ville)
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Quartier</label>
                            <div class="text-base font-medium">{{ $kiosque->quartier ?? 'N/A' }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Ville</label>
                            <div class="text-base font-medium">{{ $kiosque->ville ?? 'N/A' }}</div>
                        </div>
                        @endif
                        @if($kiosque->telephone)
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Téléphone</label>
                            <div class="text-base font-medium">{{ $kiosque->telephone }}</div>
                        </div>
                        @endif
                        @if($kiosque->latitude && $kiosque->longitude)
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Coordonnées GPS</label>
                            <div class="text-base font-medium">{{ $kiosque->latitude }}, {{ $kiosque->longitude }}</div>
                        </div>
                        @endif
                        @if($kiosque->horaire_ouverture || $kiosque->horaire_fermeture)
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Horaires</label>
                            <div class="text-base font-medium">
                                {{ $kiosque->horaire_ouverture ?? 'N/A' }} - {{ $kiosque->horaire_fermeture ?? 'N/A' }}
                            </div>
                        </div>
                        @endif
                        @if($kiosque->description)
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label class="kt-form-label text-muted-foreground">Description</label>
                            <div class="text-base font-medium">{{ $kiosque->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="kt-card mt-5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Statistiques</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Agents actifs</label>
                            <div class="text-2xl font-semibold">{{ $stats['agents_actifs'] }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Places disponibles</label>
                            <div class="text-2xl font-semibold">{{ $stats['places_disponibles'] }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Transactions ce mois</label>
                            <div class="text-2xl font-semibold">{{ $stats['transactions_mois'] }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label text-muted-foreground">Montant ce mois</label>
                            <div class="text-2xl font-semibold">{{ number_format($stats['montant_mois'] ?? 0, 0, ',', ' ') }} FCFA</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo et actions -->
        <div class="xl:col-span-1">
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex flex-col items-center gap-5">
                        @if($kiosque->photo)
                            <img class="w-32 h-32 rounded-full object-cover" src="{{ asset('storage/' . $kiosque->photo) }}" alt="{{ $kiosque->nom }}"/>
                        @else
                            <div class="flex items-center justify-center text-4xl {{ $kiosque->statut == 'actif' ? 'text-green-500 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30' : ($kiosque->statut == 'en_travaux' ? 'text-yellow-500 ring-yellow-200 dark:ring-yellow-950 bg-yellow-50 dark:bg-yellow-950/30' : 'text-muted-foreground ring-muted bg-muted/5') }} w-32 h-32 ring-1 rounded-full">
                                {{ strtoupper(substr($kiosque->nom, 0, 1)) }}
                            </div>
                        @endif
                        <div class="text-center">
                            <div class="text-lg font-semibold">{{ $kiosque->nom }}</div>
                            <div class="text-sm text-secondary-foreground">{{ $kiosque->code ?? 'N/A' }}</div>
                        </div>
                        <div class="flex flex-col gap-2.5 w-full">
                            <a href="{{ route('kiosques.edit', $kiosque) }}" class="kt-btn kt-btn-primary w-full">
                                <i class="ki-filled ki-pencil"></i>
                                Modifier
                            </a>
                            <form action="{{ route('kiosques.destroy', $kiosque) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce kiosque ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="kt-btn kt-btn-outline kt-btn-destructive w-full">
                                    <i class="ki-filled ki-trash"></i>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Container -->
@endsection
