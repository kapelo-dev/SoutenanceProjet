@php use App\Support\PdfBranding; $c = PdfBranding::COLORS; @endphp
<meta charset="UTF-8">
<style>
    @page {
        margin: 12mm 14mm 16mm 14mm;
    }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 8.5pt;
        color: {{ $c['text'] }};
        margin: 0;
        padding: 0;
        line-height: 1.45;
    }
    .pdf-header-bar {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
    }
    .pdf-header-bar td { vertical-align: middle; padding: 0; }
    .pdf-header-brand {
        background-color: {{ $c['white'] }};
        height: 56px;
        overflow: hidden;
        padding: 4px 14px 4px 16px;
        border-radius: 6px 0 0 6px;
        width: 48%;
        border: 1px solid {{ $c['border'] }};
        border-right: none;
        line-height: 0;
    }
    .pdf-header-meta {
        background-color: {{ $c['primaryDark'] }};
        height: 56px;
        padding: 6px 16px 6px 24px;
        border-radius: 0 6px 6px 0;
        color: {{ $c['white'] }};
        overflow: hidden;
    }
    .pdf-header-meta-content {
        padding-left: 6px;
        line-height: 1.15;
    }
    .pdf-logo {
        height: 48px;
        width: auto;
        max-width: 100%;
        display: block;
        margin: 0;
    }
    .pdf-doc-title {
        font-size: 9pt;
        font-weight: bold;
        color: {{ $c['white'] }};
        margin: 0 0 1px 0;
        line-height: 1.15;
    }
    .pdf-doc-subtitle {
        font-size: 6.5pt;
        color: #cbd5e1;
        margin: 0;
        line-height: 1.15;
    }
    .pdf-meta-line {
        font-size: 6pt;
        color: #e2e8f0;
        margin: 0;
        line-height: 1.15;
    }
    .pdf-accent-line {
        height: 2px;
        background-color: {{ $c['accent'] }};
        margin-bottom: 8px;
        border-radius: 2px;
    }
    .pdf-filters {
        background-color: {{ $c['accentSoft'] }};
        border-left: 4px solid {{ $c['accent'] }};
        padding: 6px 10px;
        margin-bottom: 10px;
        font-size: 7.5pt;
        color: {{ $c['text'] }};
    }
    .pdf-filters strong { color: {{ $c['primary'] }}; }
    .pdf-section-title {
        font-size: 10pt;
        font-weight: bold;
        color: {{ $c['primary'] }};
        margin: 16px 0 8px 0;
        padding-bottom: 4px;
        border-bottom: 2px solid {{ $c['accent'] }};
    }
    .pdf-kpi-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px 0;
        margin-bottom: 6px;
    }
    .pdf-kpi-table td {
        width: 25%;
        background-color: {{ $c['rowAlt'] }};
        border: 1px solid {{ $c['border'] }};
        border-top: 3px solid {{ $c['accent'] }};
        border-radius: 4px;
        padding: 10px 12px;
        vertical-align: top;
    }
    .pdf-kpi-label {
        font-size: 7pt;
        color: {{ $c['muted'] }};
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 4px;
    }
    .pdf-kpi-value {
        font-size: 12pt;
        font-weight: bold;
        color: {{ $c['primary'] }};
    }
    .pdf-kpi-unit {
        font-size: 7pt;
        color: {{ $c['muted'] }};
        font-weight: normal;
    }
    .pdf-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        font-size: 6.5pt;
        page-break-inside: auto;
    }
    .pdf-table thead { display: table-header-group; }
    .pdf-table tbody { display: table-row-group; }
    .pdf-table tr { page-break-inside: avoid; }
    .pdf-table th {
        background-color: {{ $c['primary'] }};
        color: {{ $c['white'] }};
        font-weight: bold;
        padding: 5px 4px;
        text-align: left;
        border: 1px solid {{ $c['primaryDark'] }};
        font-size: 6.5pt;
    }
    .pdf-table td {
        padding: 4px 3px;
        border: 1px solid {{ $c['border'] }};
        vertical-align: middle;
        font-size: 6.5pt;
    }
    .pdf-table tbody tr:nth-child(even) td {
        background-color: {{ $c['rowAlt'] }};
    }
    .pdf-table .text-right { text-align: right; }
    .pdf-table .text-center { text-align: center; }
    .pdf-empty {
        text-align: center;
        padding: 24px;
        color: {{ $c['muted'] }};
        font-style: italic;
    }
    .pdf-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 7pt;
        font-weight: bold;
        color: {{ $c['white'] }};
        background-color: {{ $c['primary'] }};
    }
    .pdf-badge-gold {
        background-color: {{ $c['accent'] }};
        color: {{ $c['primaryDark'] }};
    }
    .pdf-badge-neutral {
        background-color: #94a3b8;
    }
    .pdf-op-cell { white-space: nowrap; }
    .pdf-op-logo {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        vertical-align: middle;
        margin-right: 4px;
    }
    .pdf-op-badge {
        display: inline-block;
        width: 16px;
        height: 16px;
        line-height: 16px;
        border-radius: 50%;
        text-align: center;
        color: white;
        font-size: 6pt;
        font-weight: bold;
        vertical-align: middle;
        margin-right: 4px;
    }
    .pdf-footer {
        margin-top: 18px;
        padding-top: 8px;
        border-top: 1px solid {{ $c['border'] }};
        text-align: center;
        font-size: 7pt;
        color: {{ $c['muted'] }};
    }
    .pdf-footer strong { color: {{ $c['primary'] }}; }
</style>
@stack('pdf-extra-styles')
