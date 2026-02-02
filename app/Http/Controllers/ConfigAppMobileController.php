<?php

namespace App\Http\Controllers;

use App\Models\ConfigAppMobile;
use Illuminate\Http\Request;

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
}
