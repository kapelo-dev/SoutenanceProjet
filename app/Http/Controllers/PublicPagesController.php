<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicPagesController extends Controller
{
    /**
     * Page de documentation
     */
    public function documentation()
    {
        return view('public.documentation');
    }

    /**
     * Page FAQ
     */
    public function faq()
    {
        return view('public.faq');
    }

    /**
     * Page de support
     */
    public function support()
    {
        return view('public.support');
    }

    /**
     * Page de licence
     */
    public function license()
    {
        return view('public.license');
    }

    /**
     * Page publique de téléchargement de l'application mobile Android
     */
    public function mobileApp()
    {
        $apkPath = public_path('downloads/pdv-connect.apk');
        $apkAvailable = is_file($apkPath);
        $apkUrl = $apkAvailable ? asset('downloads/pdv-connect.apk') : null;
        $apkSize = $apkAvailable ? (int) filesize($apkPath) : 0;
        $apkUpdatedAt = $apkAvailable ? \Carbon\Carbon::createFromTimestamp(filemtime($apkPath)) : null;
        $apkUrlWithVersion = $apkAvailable
            ? asset('downloads/pdv-connect.apk').'?v='.$apkUpdatedAt->timestamp
            : null;

        return view('public.mobile-app', [
            'apkAvailable' => $apkAvailable,
            'apkUrl' => $apkUrlWithVersion,
            'apkSize' => $apkSize,
            'apkUpdatedAt' => $apkUpdatedAt,
            'appVersion' => config('app.mobile_apk_version', '1.0'),
        ]);
    }
}
