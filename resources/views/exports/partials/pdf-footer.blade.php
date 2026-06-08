<div class="pdf-footer">
    <p>
        <strong>PDV Connect</strong> — Plateforme de gestion Mobile Money
        &nbsp;|&nbsp; Document généré le {{ now()->format('d/m/Y H:i:s') }}
        @if(!empty($recordCount))
            &nbsp;|&nbsp; {{ $recordCount }} enregistrement{{ ($recordCount ?? 0) > 1 ? 's' : '' }}
        @endif
    </p>
</div>
