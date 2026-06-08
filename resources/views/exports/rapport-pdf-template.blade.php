@php use App\Support\PdfBranding; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>{{ $title }}</title>
    @include('exports.partials.pdf-styles')
    <style>
        .pdf-kpi-table.pdf-kpi-portrait { border-spacing: 6px; }
        .pdf-kpi-table.pdf-kpi-portrait td { width: 50%; }
        .pdf-section-title { margin-top: 12px; margin-bottom: 6px; font-size: 9pt; }
    </style>
</head>
<body>
    @include('exports.partials.pdf-header', [
        'title' => $title,
        'subtitle' => 'Rapport d\'activité Mobile Money',
        'metaLines' => [
            'Période : ' . $dateDebut->format('d/m/Y') . ' — ' . $dateFin->format('d/m/Y'),
        ],
        'filtersText' => $filtersText ?? null,
    ])

    <div class="pdf-section-title">Vue d'ensemble</div>
    <table class="pdf-kpi-table pdf-kpi-portrait">
        <tr>
            <td>
                <div class="pdf-kpi-label">Transactions validées</div>
                <div class="pdf-kpi-value">{{ PdfBranding::formatNumber($statsGlobales['total_transactions']) }}</div>
            </td>
            <td>
                <div class="pdf-kpi-label">Montant total</div>
                <div class="pdf-kpi-value">{{ PdfBranding::formatNumber($statsGlobales['montant_total']) }} <span class="pdf-kpi-unit">XOF</span></div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="pdf-kpi-label">Commissions</div>
                <div class="pdf-kpi-value">{{ PdfBranding::formatNumber($statsGlobales['commission_total']) }} <span class="pdf-kpi-unit">XOF</span></div>
            </td>
            <td>
                <div class="pdf-kpi-label">Agents actifs</div>
                <div class="pdf-kpi-value">{{ PdfBranding::formatNumber($statsGlobales['nombre_agents']) }}</div>
            </td>
        </tr>
    </table>

    @if(count($statsOperateurs) > 0)
        <div class="pdf-section-title">Performance par opérateur</div>
        <table class="pdf-table pdf-table-rapport">
            <thead>
                <tr>
                    <th>Opérateur</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-right">Montant total</th>
                    <th class="text-right">Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsOperateurs as $stat)
                    <tr>
                        <td><strong>{{ $stat['operateur']->libelle }}</strong></td>
                        <td class="text-center">{{ PdfBranding::formatNumber($stat['nombre_transactions']) }}</td>
                        <td class="text-right">{{ PdfBranding::formatMoney($stat['montant_total']) }}</td>
                        <td class="text-right">{{ PdfBranding::formatMoney($stat['commission_total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($topAgents) > 0)
        <div class="pdf-section-title">Top 10 agents</div>
        <table class="pdf-table pdf-table-rapport">
            <thead>
                <tr>
                    <th class="text-center" style="width: 28px;">#</th>
                    <th>Agent</th>
                    <th class="text-center">Tx</th>
                    <th class="text-right">Montant</th>
                    <th class="text-right">Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topAgents as $index => $topAgent)
                    <tr>
                        <td class="text-center">
                            @if($index < 3)
                                <span class="pdf-badge pdf-badge-gold">{{ $index + 1 }}</span>
                            @else
                                <span class="pdf-badge pdf-badge-neutral">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td><strong>{{ $topAgent['agent']->nomComplet }}</strong></td>
                        <td class="text-center">{{ PdfBranding::formatNumber($topAgent['nombre_transactions']) }}</td>
                        <td class="text-right">{{ PdfBranding::formatMoney($topAgent['montant_total']) }}</td>
                        <td class="text-right">{{ PdfBranding::formatMoney($topAgent['commission_total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pdf-section-title">Détail des transactions ({{ count($data) }})</div>
    <table class="pdf-table pdf-table-rapport">
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
                    @foreach($row as $cell)
                        <td>
                            @if(is_array($cell) && isset($cell['libelle']))
                                <span class="pdf-op-cell">
                                    @if(!empty($cell['logo']['base64']))
                                        <img src="{{ $cell['logo']['base64'] }}" alt="" class="pdf-op-logo" />
                                    @elseif(!empty($cell['logo']['couleur']))
                                        <span class="pdf-op-badge" style="background-color: {{ $cell['logo']['couleur'] }};">
                                            {{ $cell['logo']['code'] ?? 'OP' }}
                                        </span>
                                    @endif
                                    {{ $cell['libelle'] }}
                                </span>
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="pdf-empty">
                        Aucune transaction pour les filtres sélectionnés
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('exports.partials.pdf-footer', [
        'recordCount' => count($data),
    ])
</body>
</html>
