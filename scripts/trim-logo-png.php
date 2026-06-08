<?php

$path = __DIR__ . '/../public/assets/media/app/pdv-connect-logo-pdf.png';
$im = imagecreatefrompng($path);
imagesavealpha($im, true);
$w = imagesx($im);
$h = imagesy($im);
$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgba = imagecolorat($im, $x, $y);
        $a = ($rgba >> 24) & 0x7F;
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;
        $isEmpty = ($a >= 120) || ($r > 245 && $g > 245 && $b > 245);
        if ($isEmpty) {
            continue;
        }
        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
    }
}

$pad = 6;
$minX = max(0, $minX - $pad);
$minY = max(0, $minY - $pad);
$maxX = min($w - 1, $maxX + $pad);
$maxY = min($h - 1, $maxY + $pad);
$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;
$out = imagecreatetruecolor($cw, $ch);
imagesavealpha($out, true);
$trans = imagecolorallocatealpha($out, 0, 0, 0, 127);
imagefill($out, 0, 0, $trans);
imagecopy($out, $im, 0, 0, $minX, $minY, $cw, $ch);
imagepng($out, $path);
echo "trimmed to {$cw}x{$ch}\n";
