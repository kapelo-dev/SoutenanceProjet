<?php

namespace App\Support;

class PdfBranding
{
    /** PNG généré depuis pdv-connect-logo-pdf.svg (DomPDF ne rend pas les masques SVG) */
    public const LOGO_PNG_PATH = 'assets/media/app/pdv-connect-logo-pdf.png';

    public const LOGO_SVG_PATH = 'assets/media/app/pdv-connect-logo-pdf.svg';

    public const COLORS = [
        'primary' => '#1a3a6e',
        'primaryDark' => '#122a52',
        'accent' => '#f5c400',
        'accentSoft' => '#fef9e7',
        'text' => '#1f2937',
        'muted' => '#6b7280',
        'border' => '#e2e8f0',
        'rowAlt' => '#f8fafc',
        'white' => '#ffffff',
    ];

    public static function logoBase64(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $pngPath = public_path(self::LOGO_PNG_PATH);
        if (is_readable($pngPath)) {
            $cached = 'data:image/png;base64,' . base64_encode((string) file_get_contents($pngPath));

            return $cached;
        }

        $svgPath = public_path(self::LOGO_SVG_PATH);
        if (is_readable($svgPath)) {
            $cached = 'data:image/svg+xml;base64,' . base64_encode((string) file_get_contents($svgPath));

            return $cached;
        }

        throw new \RuntimeException('Logo PDF introuvable (png ou svg).');
    }

    public static function formatMoney(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ') . ' XOF';
    }

    public static function formatNumber(float|int|string|null $value): string
    {
        return number_format((float) $value, 0, ',', ' ');
    }
}
