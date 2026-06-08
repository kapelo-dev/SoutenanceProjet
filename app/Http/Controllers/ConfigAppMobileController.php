<?php

namespace App\Http\Controllers;

use App\Models\ConfigAppMobile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ConfigAppMobileController extends Controller
{
    /**
     * Affiche la page de configuration de l'application Android.
     */
    public function index()
    {
        $config = ConfigAppMobile::getActive() ?? new ConfigAppMobile(['actif' => true]);

        return view('pages.config_app_mobile.index', compact('config'));
    }

    /**
     * Enregistre ou met à jour la configuration (une seule config active).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'api_base_url' => 'nullable|string|max:500|url',
            'api_token' => 'nullable|string|max:255',
            'filtres_sms' => 'nullable|array',
            'filtres_sms.*' => 'nullable|string|max:100',
            'code_config' => 'nullable|string|max:50',
            'actif' => 'boolean',
        ], [
            'api_base_url.url' => "L'URL de base doit être une URL valide (ex: https://votredomaine.com).",
        ]);

        $validated['actif'] = $request->boolean('actif', true);

        // Normaliser les filtres : trim, supprimer les vides
        if (isset($validated['filtres_sms'])) {
            $validated['filtres_sms'] = array_values(array_filter(array_map(function ($s) {
                return mb_substr(trim((string) $s), 0, 100);
            }, $validated['filtres_sms'])));
        }

        $config = ConfigAppMobile::getActive();

        if ($config) {
            $config->update($validated);
            $message = 'Configuration app mobile mise à jour.';
        } else {
            ConfigAppMobile::create($validated);
            $message = 'Configuration app mobile enregistrée.';
        }

        return redirect()
            ->route('parametres-app-mobile.index')
            ->with('success', $message);
    }

    /**
     * Génère un nouveau token API (pour copier-coller dans l'app mobile).
     */
    public function generateToken(Request $request)
    {
        $token = Str::random(64);

        return response()->json(['token' => $token]);
    }

    /**
     * Teste la connectivité vers l'URL de base de l'API (depuis le serveur Laravel).
     */
    public function pingApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_base_url' => 'required|string|max:500|url',
            'api_token' => 'nullable|string|max:255',
        ], [
            'api_base_url.required' => "L'URL de base est requise pour le test.",
            'api_base_url.url' => "L'URL de base doit être une URL valide (ex: https://votredomaine.com).",
        ]);

        $baseUrl = rtrim($validated['api_base_url'], '/');
        $token = trim((string) ($validated['api_token'] ?? ''));
        $checks = [];
        $allOk = true;

        $checks[] = $server = $this->pingHttpGet($baseUrl.'/up', 'Serveur');
        if (! $server['ok']) {
            $allOk = false;
        }

        $checks[] = $api = $this->pingMobileSmsEndpoint($baseUrl);
        if (! $api['ok']) {
            $allOk = false;
        }

        if ($token !== '') {
            $checks[] = $tokenCheck = $this->pingApiToken($baseUrl, $token);
            if (! $tokenCheck['ok']) {
                $allOk = false;
            }
        }

        return response()->json([
            'success' => $allOk,
            'message' => $allOk
                ? 'Connexion à l\'API réussie.'
                : 'La connexion à l\'API a échoué. Consultez le détail ci-dessous.',
            'checks' => $checks,
        ], $allOk ? 200 : 422);
    }

    protected function pingMobileSmsEndpoint(string $baseUrl): array
    {
        $started = microtime(true);

        try {
            $response = $this->httpClient()->post($baseUrl.'/api/transactions/from-sms', []);
            $latencyMs = (int) round((microtime(true) - $started) * 1000);
            $status = $response->status();

            if (in_array($status, [401, 422, 503], true)) {
                $detail = match ($status) {
                    401 => 'Endpoint SMS accessible (authentification requise)',
                    422 => 'Endpoint SMS accessible',
                    503 => 'Endpoint SMS accessible (token non configuré sur le serveur)',
                    default => 'HTTP '.$status,
                };

                return [
                    'name' => 'API mobile',
                    'ok' => true,
                    'latency_ms' => $latencyMs,
                    'detail' => $detail,
                ];
            }

            if ($status === 404) {
                return [
                    'name' => 'API mobile',
                    'ok' => false,
                    'latency_ms' => $latencyMs,
                    'detail' => 'Route /api/transactions/from-sms introuvable (404)',
                ];
            }

            return [
                'name' => 'API mobile',
                'ok' => $response->successful(),
                'latency_ms' => $latencyMs,
                'detail' => 'HTTP '.$status,
            ];
        } catch (\Throwable $e) {
            return [
                'name' => 'API mobile',
                'ok' => false,
                'detail' => $this->formatHttpError($e),
            ];
        }
    }

    protected function pingHttpGet(string $url, string $name): array
    {
        $started = microtime(true);

        try {
            $response = $this->httpClient()->get($url);

            $latencyMs = (int) round((microtime(true) - $started) * 1000);

            if ($response->successful()) {
                return [
                    'name' => $name,
                    'ok' => true,
                    'latency_ms' => $latencyMs,
                    'detail' => 'HTTP '.$response->status(),
                ];
            }

            return [
                'name' => $name,
                'ok' => false,
                'latency_ms' => $latencyMs,
                'detail' => 'HTTP '.$response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'name' => $name,
                'ok' => false,
                'detail' => $this->formatHttpError($e),
            ];
        }
    }

    protected function httpClient()
    {
        $options = ['timeout' => 10];
        $caBundle = $this->resolveCaBundle();

        if ($caBundle !== null) {
            $options['verify'] = $caBundle;
        }

        return Http::withOptions($options)->acceptJson();
    }

    protected function resolveCaBundle(): ?string
    {
        $candidates = array_filter([
            ini_get('curl.cainfo') ?: null,
            ini_get('openssl.cafile') ?: null,
            '/usr/local/etc/ca-certificates/cert.pem',
            '/opt/homebrew/etc/ca-certificates/cert.pem',
            '/etc/ssl/cert.pem',
        ]);

        foreach ($candidates as $path) {
            if (is_string($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function formatHttpError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'cURL error 77') || str_contains($message, 'trust anchors')) {
            return 'Certificats SSL locaux manquants ou mal configurés (cURL 77). '
                .'Corrigez PHP : openssl.cafile=/usr/local/etc/ca-certificates/cert.pem dans php.ini, '
                .'ou exécutez : brew reinstall ca-certificates openssl@3 php';
        }

        return $message;
    }

    protected function pingApiToken(string $baseUrl, string $token): array
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        if ($baseUrl === $appUrl) {
            $config = ConfigAppMobile::getActive();
            $expected = $config && ! empty($config->api_token)
                ? $config->api_token
                : (string) config('sms_api.token');

            if ($expected === '') {
                return [
                    'name' => 'Token API',
                    'ok' => false,
                    'detail' => 'Aucun token enregistré sur ce serveur — enregistrez la configuration d\'abord.',
                ];
            }

            if (! hash_equals($expected, $token)) {
                return [
                    'name' => 'Token API',
                    'ok' => false,
                    'detail' => 'Token refusé (401). Vérifiez la valeur saisie ou enregistrez un nouveau token.',
                ];
            }

            return [
                'name' => 'Token API',
                'ok' => true,
                'detail' => 'Authentification valide.',
            ];
        }

        try {
            $response = $this->httpClient()
                ->withToken($token)
                ->post($baseUrl.'/api/transactions/from-sms', []);

            if ($response->status() === 401) {
                return [
                    'name' => 'Token API',
                    'ok' => false,
                    'detail' => 'Token refusé (401).',
                ];
            }

            if ($response->status() === 503) {
                return [
                    'name' => 'Token API',
                    'ok' => false,
                    'detail' => 'API SMS non configurée sur le serveur distant.',
                ];
            }

            // 422 = token accepté, payload invalide (attendu pour un ping)
            if ($response->status() === 422) {
                return [
                    'name' => 'Token API',
                    'ok' => true,
                    'detail' => 'Authentification valide.',
                ];
            }

            return [
                'name' => 'Token API',
                'ok' => $response->successful(),
                'detail' => 'HTTP '.$response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'name' => 'Token API',
                'ok' => false,
                'detail' => $this->formatHttpError($e),
            ];
        }
    }
}
