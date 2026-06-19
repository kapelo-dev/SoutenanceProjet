<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfigAppMobile;
use App\Support\MobileApkVersion;
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
     * Version de l'APK publiée — lue depuis pdv-connect.version.json (automatique au push).
     */
    public function appVersion(): JsonResponse
    {
        $version = MobileApkVersion::resolve();
        $updatedAt = $version['updated_at'];

        $apkUrl = $version['apk_available']
            ? asset('downloads/pdv-connect.apk').($updatedAt ? '?v='.$updatedAt : '')
            : null;

        return response()->json([
            'version_code' => $version['version_code'],
            'version_name' => $version['version_name'],
            'min_version_code' => $version['min_version_code'],
            'apk_available' => $version['apk_available'],
            'download_page_url' => route('public.mobile-app'),
            'apk_url' => $apkUrl,
            'updated_at' => $updatedAt ? date('c', $updatedAt) : null,
        ]);
    }
}
