@extends('layouts.demo1.base')

@section('content')
    <div class="kt-container-fixed">
    <div class="flex items-center justify-between mb-7.5">
        <div>
            <h1 class="text-2xl font-bold text-mono">Configuration App Mobile</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Générez le token ici, puis dans l'app Android saisissez uniquement l'URL de l'API, collez le token et le code d'accès.
            </p>
        </div>
    </div>

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
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="url"
                               name="api_base_url"
                               id="api_base_url"
                               class="kt-input flex-1 min-w-[200px]"
                               value="{{ old('api_base_url', $config->api_base_url) }}"
                               placeholder="https://votredomaine.com">
                        <button type="button" id="btn_ping_api" class="kt-btn kt-btn-outline kt-btn-sm whitespace-nowrap">
                            <i class="ki-filled ki-wifi me-1"></i>
                            Tester
                        </button>
                    </div>
                    <div id="ping_api_result" class="hidden kt-alert text-sm"></div>
                    <span class="text-xs text-muted-foreground">
                        L'application Android utilisera cette URL pour envoyer les transactions (ex: https://votredomaine.com).
                    </span>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" for="api_token">
                        Token API (Bearer)
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text"
                               name="api_token"
                               id="api_token"
                               class="kt-input flex-1 min-w-[200px]"
                               value="{{ old('api_token', $config->api_token) }}"
                               placeholder="Générez un token puis copiez-le dans l'app">
                        <button type="button" id="btn_generate_token" class="kt-btn kt-btn-outline kt-btn-sm whitespace-nowrap">
                            <i class="ki-filled ki-key me-1"></i>
                            Générer un token
                        </button>
                        <button type="button" id="btn_copy_token" class="kt-btn kt-btn-outline kt-btn-sm whitespace-nowrap" title="Copier le token">
                            <i class="ki-filled ki-copy me-1"></i>
                            Copier
                        </button>
                    </div>
                    <span class="text-xs text-muted-foreground">
                        Générez un token ici, enregistrez la configuration, puis copiez-collez ce token dans l'application Android.
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
                        Code web : demandé à l'accès de l'onglet Service SMS dans l'app. Code local : défini une fois sur chaque téléphone (différent du code web).
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
                Dans l'app mobile, configurez uniquement : <strong>URL de l'API</strong>, <strong>Token</strong> (collez celui ci-dessous) et <strong>Code d'accès</strong>.
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
    var btnPing = document.getElementById('btn_ping_api');
    var inputUrl = document.getElementById('api_base_url');
    var inputToken = document.getElementById('api_token');
    var pingResult = document.getElementById('ping_api_result');

    function showPingResult(success, message, checks) {
        if (!pingResult) return;
        pingResult.classList.remove('hidden', 'kt-alert-success', 'kt-alert-danger');
        pingResult.classList.add(success ? 'kt-alert-success' : 'kt-alert-danger');

        var html = '<i class="ki-filled ' + (success ? 'ki-check-circle' : 'ki-information-2') + '"></i>';
        html += '<div><div class="font-medium mb-1">' + message + '</div>';
        if (checks && checks.length) {
            html += '<ul class="space-y-0.5 text-xs opacity-90 list-none m-0 p-0">';
            checks.forEach(function(c) {
                var icon = c.ok ? '✓' : '✗';
                var latency = c.latency_ms != null ? ' (' + c.latency_ms + ' ms)' : '';
                html += '<li>' + icon + ' <strong>' + c.name + '</strong> — ' + c.detail + latency + '</li>';
            });
            html += '</ul>';
        }
        html += '</div>';
        pingResult.innerHTML = html;
    }

    if (btnPing && inputUrl) {
        btnPing.addEventListener('click', function() {
            var url = inputUrl.value.trim();
            if (!url) {
                inputUrl.focus();
                showPingResult(false, 'Saisissez une URL de base avant de tester.', []);
                return;
            }

            btnPing.disabled = true;
            var icon = btnPing.querySelector('i');
            icon?.classList.add('animate-spin');
            if (pingResult) pingResult.classList.add('hidden');

            fetch('{{ route("parametres-app-mobile.ping-api") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    api_base_url: url,
                    api_token: inputToken ? inputToken.value.trim() : ''
                })
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                showPingResult(res.data.success, res.data.message || (res.ok ? 'Connexion réussie.' : 'Échec du test.'), res.data.checks || []);
            })
            .catch(function() {
                showPingResult(false, 'Impossible de contacter le serveur pour le test.', []);
            })
            .finally(function() {
                btnPing.disabled = false;
                icon?.classList.remove('animate-spin');
            });
        });
    }

    // Générer un token via l'API et le mettre dans le champ + copier dans le presse-papier
    var btnGenerate = document.getElementById('btn_generate_token');
    var btnCopy = document.getElementById('btn_copy_token');
    if (!inputToken) inputToken = document.getElementById('api_token');
    if (btnGenerate && inputToken) {
        btnGenerate.addEventListener('click', function() {
            btnGenerate.disabled = true;
            btnGenerate.querySelector('i')?.classList.add('animate-spin');
            fetch('{{ route("parametres-app-mobile.generate-token") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.token) {
                    inputToken.value = data.token;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(data.token).then(function() {
                            if (btnCopy) btnCopy.focus();
                        });
                    }
                }
            })
            .finally(function() {
                btnGenerate.disabled = false;
                btnGenerate.querySelector('i')?.classList.remove('animate-spin');
            });
        });
    }
    if (btnCopy && inputToken) {
        btnCopy.addEventListener('click', function() {
            var token = inputToken.value;
            if (!token) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(token).then(function() {
                    var label = btnCopy.querySelector('span') || btnCopy;
                    var orig = btnCopy.innerHTML;
                    btnCopy.innerHTML = '<i class="ki-filled ki-check me-1"></i> Copié !';
                    setTimeout(function() { btnCopy.innerHTML = orig; }, 1500);
                });
            }
        });
    }

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
