<?php

namespace App\Support;

/**
 * Version APK mobile : source de vérité = public/downloads/pdv-connect.version.json
 * généré par scripts/publish-mobile-apk.sh (commit + push = déploiement auto sur Render).
 */
class MobileApkVersion
{
    public static function resolve(): array
    {
        $apkPath = public_path('downloads/pdv-connect.apk');
        $fromFile = self::fromVersionJson();

        if ($fromFile !== null) {
            $versionCode = $fromFile['version_code'];
            $minOverride = env('MOBILE_APK_MIN_VERSION_CODE');

            return [
                'version_code' => $versionCode,
                'version_name' => $fromFile['version_name'],
                'min_version_code' => ($minOverride !== null && $minOverride !== '')
                    ? min((int) $minOverride, $versionCode)
                    : $versionCode,
                'apk_available' => is_file($apkPath),
                'updated_at' => is_file($apkPath) ? filemtime($apkPath) : null,
            ];
        }

        $versionCode = (int) config('app.mobile_apk_version_code', 1);

        return [
            'version_code' => $versionCode,
            'version_name' => (string) config('app.mobile_apk_version', '1.0'),
            'min_version_code' => (int) config('app.mobile_apk_min_version_code', $versionCode),
            'apk_available' => is_file($apkPath),
            'updated_at' => is_file($apkPath) ? filemtime($apkPath) : null,
        ];
    }

    private static function fromVersionJson(): ?array
    {
        $jsonPath = public_path('downloads/pdv-connect.version.json');
        if (! is_file($jsonPath)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data) || ! isset($data['version_code'])) {
            return null;
        }

        $versionCode = (int) $data['version_code'];

        return [
            'version_code' => $versionCode,
            'version_name' => (string) ($data['version_name'] ?? $versionCode),
        ];
    }
}
