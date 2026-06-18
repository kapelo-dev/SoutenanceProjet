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
}
