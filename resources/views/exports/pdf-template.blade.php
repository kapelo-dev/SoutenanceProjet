<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            margin: 15mm;
            color: #000;
        }
        .header {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .logo-container {
            margin-bottom: 10px;
            margin-right: 20px;
        }
        .logo-container img {
            height: 45px;
            max-width: 220px;
        }
        .logo-text {
            font-size: 20pt;
            font-weight: bold;
            color: #FBBF24;
            line-height: 1.2;
        }
        .logo-text .subtitle {
            font-size: 16pt;
            font-weight: 500;
            color: #525252;
        }
        .header-content {
            flex: 1;
        }
        h1 {
            color: #FBBF24;
            border-bottom: 3px solid #FBBF24;
            padding-bottom: 8px;
            margin-bottom: 8px;
            font-size: 16pt;
            margin-top: 0;
        }
        .info {
            margin-bottom: 12px;
            font-size: 8pt;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tbody {
            display: table-row-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th {
            background-color: #FBBF24;
            color: #000;
            font-weight: bold;
            padding: 6px 5px;
            text-align: left;
            border: 1px solid #000;
            font-size: 8pt;
        }
        td {
            padding: 5px;
            border: 1px solid #ccc;
            font-size: 8pt;
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .operateur-cell {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .operateur-logo {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: cover;
        }
        .operateur-badge {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 7pt;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 7pt;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            @php
                // Créer un SVG simplifié compatible avec DomPDF
                // DomPDF supporte mieux les SVG encodés en base64 dans une balise img
                $logoSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 240 40" width="240" height="40">
    <!-- Icon simplifié sans gradients -->
    <g>
        <!-- Main circle -->
        <circle cx="20" cy="20" r="8" fill="#FBBF24"/>
        <circle cx="20" cy="20" r="5.5" fill="none" stroke="white" stroke-width="1.2" opacity="0.7"/>
        <!-- Connection lines -->
        <path d="M28 14 L36 10 M28 26 L36 30" stroke="#FBBF24" stroke-width="2" stroke-linecap="round"/>
        <!-- Connection points -->
        <circle cx="36" cy="10" r="3.5" fill="#FBBF24"/>
        <circle cx="36" cy="30" r="3.5" fill="#FBBF24"/>
        <!-- Central bright point -->
        <circle cx="20" cy="20" r="2" fill="white" opacity="0.8"/>
    </g>
    <!-- PDV text avec police DejaVu Sans -->
    <text x="48" y="26" font-family="DejaVu Sans" font-size="22" font-weight="bold" fill="#FBBF24">PDV</text>
    <!-- CONNECT text -->
    <text x="98" y="26" font-family="DejaVu Sans" font-size="22" font-weight="normal" fill="#374151">CONNECT</text>
    <!-- Decorative line under CONNECT -->
    <line x1="98" y1="30" x2="200" y2="30" stroke="#FBBF24" stroke-width="2" stroke-linecap="round" opacity="0.3"/>
</svg>';
                $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode($logoSvg);
            @endphp
            <img src="{{ $logoBase64 }}" alt="PDV Connect" style="height: 45px; max-width: 220px;" />
        </div>
        <div class="header-content">
            <h1>{{ $title }}</h1>
            <div class="info">
                Date d'export : {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($row as $index => $cell)
                        <td>
                            @if(is_array($cell) && isset($cell['libelle']))
                                {{-- Cellule opérateur avec logo --}}
                                <div class="operateur-cell">
                                    @if(isset($cell['logo']['base64']))
                                        <img src="{{ $cell['logo']['base64'] }}" alt="{{ $cell['libelle'] }}" class="operateur-logo" />
                                    @elseif(isset($cell['logo']['couleur']))
                                        <div class="operateur-badge" style="background-color: {{ $cell['logo']['couleur'] }};">
                                            {{ $cell['logo']['code'] ?? 'OP' }}
                                        </div>
                                    @endif
                                    <span>{{ $cell['libelle'] }}</span>
                                </div>
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 20px;">
                        Aucune donnée à exporter
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Généré par PDV Connect - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
