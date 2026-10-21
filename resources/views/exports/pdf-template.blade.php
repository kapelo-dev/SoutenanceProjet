<!DOCTYPE html>
<html lang="fr">
<head>
    {{-- Doit précéder tout texte accentué : sinon libxml lit le document en Latin-1 --}}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $title }}</title>
    @include('exports.partials.pdf-styles')
</head>
<body>
    @include('exports.partials.pdf-header', [
        'title' => $title,
        'subtitle' => $subtitle ?? null,
        'metaLines' => $metaLines ?? [],
        'filtersText' => $filtersText ?? null,
    ])

    @include('exports.partials.pdf-data-table', [
        'headers' => $headers,
        'data' => $data,
        'emptyMessage' => $emptyMessage ?? 'Aucune donnée à exporter',
    ])

    @include('exports.partials.pdf-footer', [
        'recordCount' => count($data),
    ])
</body>
</html>
