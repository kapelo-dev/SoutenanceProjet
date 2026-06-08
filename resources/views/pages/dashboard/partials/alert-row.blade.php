@php
    $severityClass = match($alert['severity']) {
        'critical' => 'bg-destructive/10 text-destructive border-destructive/20',
        default => 'bg-[#fef9e7] text-[#1a3a6e] border-[#f5c400]/30 dark:bg-[#f5c400]/10 dark:text-[#f5c400]',
    };
    $severityLabel = $alert['severity'] === 'critical' ? 'Critique' : 'Alerte';
@endphp
<div class="flex gap-4 p-5" data-alert-row>
    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg font-bold text-sm {{ $severityClass }} border">
        {{ $index }}
    </div>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="font-semibold text-foreground" data-alert-title>{{ $alert['title'] }}</span>
            <span class="kt-badge kt-badge-sm kt-badge-outline {{ $alert['severity'] === 'critical' ? 'kt-badge-destructive' : 'kt-badge-warning' }}">{{ $severityLabel }}</span>
        </div>
        <p class="text-sm text-secondary-foreground mb-2" data-alert-problem>{{ $alert['problem'] }}</p>
        <div class="rounded-lg bg-muted/50 border border-border px-3 py-2.5 text-sm">
            <span class="font-medium text-foreground">Action : </span>
            <span data-alert-action>{{ $alert['action'] }}</span>
        </div>
    </div>
</div>
