<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            margin: 10mm;
            color: #000;
        }
        .header {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #FBBF24;
            padding-bottom: 10px;
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
            margin-bottom: 5px;
            font-size: 16pt;
            margin-top: 0;
        }
        .info {
            margin-bottom: 8px;
            font-size: 8pt;
            color: #666;
        }
        .stats-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            padding: 6px 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            font-size: 8pt;
        }
        .stats-cell-label {
            font-weight: bold;
            background-color: #FBBF24;
            color: #000;
            width: 40%;
        }
        .stats-cell-value {
            text-align: right;
            font-weight: bold;
        }
        .operateurs-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .operateurs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .operateurs-table th {
            background-color: #FBBF24;
            color: #000;
            font-weight: bold;
            padding: 6px 5px;
            text-align: left;
            border: 1px solid #000;
        }
        .operateurs-table td {
            padding: 5px;
            border: 1px solid #ccc;
        }
        .operateurs-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .top-agents-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .top-agents-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .top-agents-table th {
            background-color: #FBBF24;
            color: #000;
            font-weight: bold;
            padding: 6px 5px;
            text-align: left;
            border: 1px solid #000;
        }
        .top-agents-table td {
            padding: 5px;
            border: 1px solid #ccc;
        }
        .top-agents-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7pt;
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
            padding: 5px 4px;
            text-align: left;
            border: 1px solid #000;
            font-size: 7pt;
        }
        td {
            padding: 4px;
            border: 1px solid #ccc;
            font-size: 7pt;
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .operateur-cell {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .operateur-logo {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            object-fit: cover;
        }
        .operateur-badge {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 6pt;
            font-weight: bold;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #FBBF24;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 2px solid #FBBF24;
            padding-bottom: 3px;
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
                Période: {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}<br>
                Date d'export : {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>
    
    {{-- Statistiques globales --}}
    <div class="stats-section">
        <div class="section-title">Statistiques Globales</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell stats-cell-label">Nombre total de transactions</div>
                <div class="stats-cell stats-cell-value">{{ number_format($statsGlobales['total_transactions'], 0, ',', ' ') }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell stats-cell-label">Montant total</div>
                <div class="stats-cell stats-cell-value">{{ number_format($statsGlobales['montant_total'], 0, ',', ' ') }} XOF</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell stats-cell-label">Commission totale</div>
                <div class="stats-cell stats-cell-value">{{ number_format($statsGlobales['commission_total'], 0, ',', ' ') }} XOF</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell stats-cell-label">Nombre d'agents</div>
                <div class="stats-cell stats-cell-value">{{ $statsGlobales['nombre_agents'] }}</div>
            </div>
        </div>
    </div>

    {{-- Statistiques par opérateur --}}
    @if(count($statsOperateurs) > 0)
    <div class="operateurs-section">
        <div class="section-title">Statistiques par Opérateur</div>
        <table class="operateurs-table">
            <thead>
                <tr>
                    <th>Opérateur</th>
                    <th>Nombre de transactions</th>
                    <th>Montant total</th>
                    <th>Commission totale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsOperateurs as $stat)
                <tr>
                    <td>{{ $stat['operateur']->libelle }}</td>
                    <td>{{ number_format($stat['nombre_transactions'], 0, ',', ' ') }}</td>
                    <td>{{ number_format($stat['montant_total'], 0, ',', ' ') }} XOF</td>
                    <td>{{ number_format($stat['commission_total'], 0, ',', ' ') }} XOF</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Top agents --}}
    @if(count($topAgents) > 0)
    <div class="top-agents-section">
        <div class="section-title">Top 10 Agents</div>
        <table class="top-agents-table">
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Agent</th>
                    <th>Nombre de transactions</th>
                    <th>Montant total</th>
                    <th>Commission totale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topAgents as $index => $topAgent)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $topAgent['agent']->nomComplet }}</td>
                    <td>{{ number_format($topAgent['nombre_transactions'], 0, ',', ' ') }}</td>
                    <td>{{ number_format($topAgent['montant_total'], 0, ',', ' ') }} XOF</td>
                    <td>{{ number_format($topAgent['commission_total'], 0, ',', ' ') }} XOF</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Liste des transactions --}}
    <div class="section-title">Détail des Transactions ({{ count($data) }} transaction{{ count($data) > 1 ? 's' : '' }})</div>
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
                        Aucune transaction trouvée pour les filtres sélectionnés
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
