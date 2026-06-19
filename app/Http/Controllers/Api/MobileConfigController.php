<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfigAppMobile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileConfigController extends Controller
{
    /**
     * Vérifie le code d'accès configuration défini côté web (sans le renvoyer).
     */
    public function verifyConfigCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $config = ConfigAppMobile::getActive();
        $expected = $config?->code_config;

        if (empty($expected)) {
            return response()->json([
                'valid' => false,
                'message' => 'Aucun code de configuration défini sur le serveur.',
            ]);
        }

        $valid = hash_equals((string) $expected, (string) $validated['code']);

        return response()->json([
            'valid' => $valid,
            'message' => $valid ? 'Code valide.' : 'Code incorrect.',
        ]);
    }

    /**
     * Version de l'APK publiée — l'app compare versionCode locale et bloque si obsolète.
     */
    public function appVersion(): JsonResponse
    {
        $apkPath = public_path('downloads/pdv-connect.apk');
        $apkAvailable = is_file($apkPath);
        $updatedAt = $apkAvailable ? filemtime($apkPath) : null;

        $published = $this->publishedApkVersion();
        $versionCode = $published['version_code'];
        $versionName = $published['version_name'];
        $minVersionCode = min(
            (int) config('app.mobile_apk_min_version_code', $versionCode),
            $versionCode
        );

        $apkUrl = $apkAvailable
            ? asset('downloads/pdv-connect.apk').($updatedAt ? '?v='.$updatedAt : '')
            : null;

        return response()->json([
            'version_code' => $versionCode,
            'version_name' => $versionName,
            'min_version_code' => $minVersionCode,
            'apk_available' => $apkAvailable,
            'download_page_url' => route('public.mobile-app'),
            'apk_url' => $apkUrl,
            'updated_at' => $updatedAt ? date('c', $updatedAt) : null,
        ]);
    }

    /**
     * Version réelle de l'APK publié (sidecar JSON), sinon config .env.
     */
    private function publishedApkVersion(): array
    {
        $jsonPath = public_path('downloads/pdv-connect.version.json');
        if (is_file($jsonPath)) {
            $data = json_decode((string) file_get_contents($jsonPath), true);
            if (is_array($data) && isset($data['version_code'])) {
                return [
                    'version_code' => (int) $data['version_code'],
                    'version_name' => (string) ($data['version_name'] ?? config('app.mobile_apk_version', '1.0')),
                ];
            }
        }

        return [
            'version_code' => (int) config('app.mobile_apk_version_code', 1),
            'version_name' => (string) config('app.mobile_apk_version', '1.0'),
        ];
    }
}
