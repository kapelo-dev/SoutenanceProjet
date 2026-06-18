@extends('layouts.demo9.base')

@section('content')
<div class="kt-container-fixed py-10">
    <div class="kt-card mb-7.5">
        <div class="kt-card-content p-10 lg:p-15 text-center">
            <img src="{{ asset('assets/media/app/pdv-connect-logo.svg') }}" alt="PDV Connect" class="h-12 w-auto max-w-[240px] object-contain mx-auto mb-6" />
            <h1 class="text-3xl lg:text-4xl font-bold text-mono mb-4">Application mobile PDV Connect</h1>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                Installez l'application Android pour transférer automatiquement les SMS de transaction
                et accéder à l'espace agent (transactions, commissions, annulations).
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 max-w-4xl mx-auto">
        <div class="kt-card">
            <div class="kt-card-content p-8 flex flex-col gap-5">
                <h2 class="text-xl font-semibold text-mono">Téléchargement</h2>

                @if($apkAvailable)
                    <div class="rounded-lg border border-border bg-muted/30 p-4 text-sm space-y-2">
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Version</span>
                            <span class="font-medium text-mono">{{ $appVersion }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Taille</span>
                            <span class="font-medium text-mono">{{ number_format($apkSize / 1048576, 1, ',', ' ') }} Mo</span>
                        </div>
                        @if($apkUpdatedAt)
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Mise à jour</span>
                            <span class="font-medium text-mono">{{ $apkUpdatedAt->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    </div>

                    <a href="{{ $apkUrl }}" class="kt-btn kt-btn-primary kt-btn-lg justify-center" download="pdv-connect.apk">
                        <i class="ki-filled ki-cloud-download me-2"></i>
                        Télécharger l'APK
                    </a>

                    <p class="text-xs text-muted-foreground">
                        Lien direct : <a href="{{ $apkUrl }}" class="text-primary hover:underline break-all">{{ $apkUrl }}</a>
                    </p>
                @else
                    <div class="kt-alert kt-alert-warning">
                        <div class="kt-alert-content">
                            L'APK n'est pas encore disponible sur ce serveur.
                            Placez le fichier dans <code>public/downloads/pdv-connect.apk</code>.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content p-8 flex flex-col gap-4">
                <h2 class="text-xl font-semibold text-mono">Installation</h2>
                <ol class="list-decimal list-inside space-y-3 text-sm text-muted-foreground">
                    <li>Téléchargez le fichier APK sur votre téléphone Android.</li>
                    <li>Ouvrez le fichier et autorisez l'installation depuis cette source si Android le demande.</li>
                    <li>Accordez les permissions SMS et notifications.</li>
                    <li>Dans l'onglet <strong>Espace agent</strong>, connectez-vous avec votre <strong>code agent</strong> et mot de passe.</li>
                    <li>L'onglet <strong>Service SMS</strong> (réservé admin) demande le code web et le code local du téléphone.</li>
                </ol>
                <a href="{{ route('login') }}" class="kt-btn kt-btn-outline justify-center mt-2">
                    Accéder au portail web
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
