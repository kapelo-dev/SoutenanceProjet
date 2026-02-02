@extends('layouts.demo1.base')

@section('content')
<div class="kt-container-fixed">
    <div class="flex items-center justify-between mb-7.5">
        <div>
            <h1 class="text-2xl font-bold text-mono">Configuration App Mobile</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Paramètres pour l'application Android : endpoint API, token et filtres SMS (numéros ou noms de discussions).
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="kt-alert kt-alert-success mb-5">
            <i class="ki-filled ki-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-alert kt-alert-danger mb-5">
            <i class="ki-filled ki-information-2"></i>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Paramètres à saisir dans l'application Android</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form action="{{ route('parametres-app-mobile.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" for="api_base_url">
                        URL de base de l'API
                    </label>
                    <input type="url"
                           name="api_base_url"
                           id="api_base_url"
                           class="kt-input"
                           value="{{ old('api_base_url', $config->api_base_url) }}"
                           placeholder="https://votredomaine.com">
                    <span class="text-xs text-muted-foreground">
                        L'application Android utilisera cette URL pour envoyer les transactions (ex: https://votredomaine.com).
                    </span>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" for="api_token">
                        Token API (Bearer)
                    </label>
                    <input type="text"
                           name="api_token"
                           id="api_token"
                           class="kt-input"
                           value="{{ old('api_token', $config->api_token) }}"
                           placeholder="Clé secrète partagée avec l'app">
                    <span class="text-xs text-muted-foreground">
                        Ce token doit être saisi dans l'app Android pour s'authentifier auprès de l'API.
                    </span>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Filtres SMS (numéros ou noms de discussions)</label>
                    <div id="filtres_sms_container" class="space-y-2">
                        @php
                            $filtres = old('filtres_sms', $config->filtres_sms ?? []);
                            if (empty($filtres)) {
                                $filtres = [''];
                            }
                        @endphp
                        @foreach($filtres as $index => $valeur)
                        <div class="filtre-sms-row flex items-center gap-2">
                            <input type="text"
                                   name="filtres_sms[]"
                                   class="kt-input flex-1"
                                   value="{{ old('filtres_sms.'.$index, $valeur) }}"
                                   placeholder="Ex: FLOOZ, +22507123456, 1234">
                            <button type="button" class="kt-btn kt-btn-icon kt-btn-outline kt-btn-sm filtre-sms-remove" title="Supprimer" aria-label="Supprimer">
                                <i class="ki-filled ki-trash"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="filtre_sms_plus" class="kt-btn kt-btn-outline kt-btn-sm mt-1">
                        <i class="ki-filled ki-plus me-1"></i>
                        Ajouter une ligne
                    </button>
                    <span class="text-xs text-muted-foreground">
                        Numéro de téléphone (ex: +22507123456) ou nom de discussion (ex: FLOOZ). L'app ne traitera que les SMS provenant de ces numéros ou discussions.
                    </span>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" for="code_config">
                        Code d'accès à la page de configuration
                    </label>
                    <input type="text"
                           name="code_config"
                           id="code_config"
                           class="kt-input max-w-xs"
                           value="{{ old('code_config', $config->code_config ?? '') }}"
                           placeholder="Ex: 1234">
                    <span class="text-xs text-muted-foreground">
                        Code à saisir dans l'application mobile pour accéder à la page de configuration (paramètres, confidentialité).
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox"
                           name="actif"
                           id="actif"
                           value="1"
                           class="kt-checkbox"
                           {{ old('actif', $config->actif) ? 'checked' : '' }}>
                    <label class="text-sm font-medium" for="actif">Configuration active (utilisée par l'API)</label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check me-2"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($config->exists && ($config->api_base_url || $config->api_token))
    <div class="kt-card mt-5">
        <div class="kt-card-header">
            <h3 class="kt-card-title">À copier dans l'application Android</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <p class="text-sm text-muted-foreground mb-4">
                Saisissez ces valeurs dans les paramètres de l'app Android (écran de configuration ou confidentialité).
            </p>
            <div class="space-y-3 font-mono text-sm">
                @if($config->api_base_url)
                    <div>
                        <span class="text-muted-foreground">URL :</span>
                        <code class="ml-2 bg-muted/30 px-2 py-1 rounded break-all">{{ rtrim($config->api_base_url, '/') }}</code>
                    </div>
                @endif
                @if($config->api_token)
                    <div>
                        <span class="text-muted-foreground">Token :</span>
                        <code class="ml-2 bg-muted/30 px-2 py-1 rounded">{{ $config->api_token }}</code>
                    </div>
                @endif
                @if($config->filtres_sms && count($config->filtres_sms) > 0)
                    <div>
                        <span class="text-muted-foreground">Filtres SMS (numéros / discussions) :</span>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            @foreach($config->filtres_sms as $f)
                                <li><code class="bg-muted/30 px-2 py-0.5 rounded">{{ $f }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($config->code_config)
                    <div>
                        <span class="text-muted-foreground">Code d'accès à la configuration :</span>
                        <code class="ml-2 bg-muted/30 px-2 py-1 rounded">{{ $config->code_config }}</code>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<script>
(function() {
    var container = document.getElementById('filtres_sms_container');
    var btnPlus = document.getElementById('filtre_sms_plus');
    if (!container || !btnPlus) return;

    btnPlus.addEventListener('click', function() {
        var row = document.createElement('div');
        row.className = 'filtre-sms-row flex items-center gap-2';
        row.innerHTML = '<input type="text" name="filtres_sms[]" class="kt-input flex-1" placeholder="Ex: FLOOZ, +22507123456">' +
            '<button type="button" class="kt-btn kt-btn-icon kt-btn-outline kt-btn-sm filtre-sms-remove" title="Supprimer" aria-label="Supprimer">' +
            '<i class="ki-filled ki-trash"></i></button>';
        container.appendChild(row);
        row.querySelector('.filtre-sms-remove').addEventListener('click', function() {
            row.remove();
        });
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.filtre-sms-remove')) {
            var row = e.target.closest('.filtre-sms-row');
            if (row && container.querySelectorAll('.filtre-sms-row').length > 1) {
                row.remove();
            }
        }
    });
})();
</script>
@endsection
