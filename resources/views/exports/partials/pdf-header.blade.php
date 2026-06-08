@php use App\Support\PdfBranding; @endphp
<table class="pdf-header-bar">
    <tr>
        <td class="pdf-header-brand">
            <img src="{{ PdfBranding::logoBase64() }}" alt="PDV Connect" class="pdf-logo" />
        </td>
        <td class="pdf-header-meta">
            <div class="pdf-header-meta-content">
                <p class="pdf-doc-title">{{ $title }}</p>
                @if(!empty($subtitle))
                    <p class="pdf-doc-subtitle">{{ $subtitle }}</p>
                @endif
                @foreach($metaLines ?? [] as $line)
                    <p class="pdf-meta-line">{{ $line }}</p>
                @endforeach
                <p class="pdf-meta-line">Exporté le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
        </td>
    </tr>
</table>
<div class="pdf-accent-line"></div>
@if(!empty($filtersText))
    <div class="pdf-filters">
        <strong>Filtres appliqués :</strong> {{ $filtersText }}
    </div>
@endif
