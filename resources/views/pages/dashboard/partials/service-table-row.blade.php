@php
    $badge = match($service['status']) {
        'ok' => 'kt-badge-success',
        'warning' => 'kt-badge-warning',
        'error' => 'kt-badge-destructive',
        default => 'kt-badge-outline',
    };
    $label = match($service['status']) {
        'ok' => 'OK',
        'warning' => 'Alerte',
        'error' => 'Erreur',
        default => '—',
    };
@endphp
<tr data-service-row>
    <td class="px-5 py-3">
        <div class="font-medium" data-service-name>{{ $service['name'] }}</div>
        <div class="text-xs text-secondary-foreground truncate max-w-xs" data-service-detail>{{ $service['detail'] }}</div>
    </td>
    <td class="px-5 py-3">
        <span class="kt-badge kt-badge-sm kt-badge-outline {{ $badge }}" data-service-status>{{ $label }}</span>
    </td>
    <td class="px-5 py-3 text-right font-mono font-medium" data-service-value>{{ $service['value'] }}</td>
</tr>
