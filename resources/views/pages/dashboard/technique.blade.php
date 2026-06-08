@extends('layouts.demo1.base')

@section('content')
@php
    $health = $metrics['health'];
    $alerts = $metrics['alerts'];
    $criticalAlerts = collect($alerts)->where('severity', 'critical');
    $warningAlerts = collect($alerts)->where('severity', 'warning');
    $infoAlerts = collect($alerts)->where('severity', 'info');
    $statusConfig = [
        'ok' => ['border' => 'border-[#1a3a6e]/20', 'bg' => 'bg-[#1a3a6e]', 'text' => 'text-white', 'icon' => 'ki-shield-tick', 'badge' => 'kt-badge-success'],
        'warning' => ['border' => 'border-[#f5c400]/40', 'bg' => 'bg-[#fef9e7] dark:bg-[#f5c400]/10', 'text' => 'text-[#1a3a6e] dark:text-[#f5c400]', 'icon' => 'ki-information-2', 'badge' => 'kt-badge-warning'],
        'error' => ['border' => 'border-destructive/30', 'bg' => 'bg-destructive/5', 'text' => 'text-destructive', 'icon' => 'ki-information-3', 'badge' => 'kt-badge-destructive'],
    ];
    $cfg = $statusConfig[$health['status']] ?? $statusConfig['warning'];
    $gaugeBar = ['ok' => 'bg-[#1a3a6e]', 'warning' => 'bg-[#f5c400]', 'error' => 'bg-destructive', 'info' => 'bg-muted-foreground/40'];
@endphp

<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5">
        <div>
            <h1 class="text-xl font-semibold text-mono">Dashboard Technique</h1>
            <p class="text-sm text-secondary-foreground mt-1">Supervision serveur et diagnostic applicatif</p>
        </div>
        <div class="flex items-center gap-3">
            <span id="tech_updated" class="text-xs text-secondary-foreground"></span>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" id="tech_refresh">
                <i class="ki-filled ki-arrows-circle"></i> Actualiser
            </button>
        </div>
    </div>
</div>

<div class="kt-container-fixed pb-8" id="tech_dashboard_root"
    data-metrics-url="{{ route('dashboard.technique.metrics') }}"
    data-initial='@json($metrics)'>

    {{-- Bandeau statut --}}
    <div class="rounded-2xl border p-5 mb-6 {{ $cfg['border'] }} {{ $cfg['bg'] }} {{ $cfg['text'] }}" id="tech_health_banner">
        <div class="flex flex-wrap items-start gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/15">
                <i class="ki-filled {{ $cfg['icon'] }} text-2xl" id="tech_health_icon"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-lg font-semibold" id="tech_health_label">{{ $health['label'] }}</span>
                    <span class="kt-badge kt-badge-sm {{ $cfg['badge'] }}" id="tech_health_badge">
                        {{ $health['status'] === 'ok' ? 'Opérationnel' : ($health['status'] === 'warning' ? 'Surveillance' : 'Critique') }}
                    </span>
                </div>
                <p class="text-sm opacity-90" id="tech_health_summary">{{ $health['summary'] }}</p>
            </div>
            <div class="flex gap-4 text-center" id="tech_health_counts">
                <div><div class="text-2xl font-bold" data-count="critical">{{ $health['critical'] ?? 0 }}</div><div class="text-xs opacity-75">Critiques</div></div>
                <div><div class="text-2xl font-bold" data-count="warnings">{{ $health['warnings'] ?? 0 }}</div><div class="text-xs opacity-75">Alertes</div></div>
            </div>
        </div>
    </div>

    {{-- Actions à faire --}}
    <div class="mb-6" id="tech_alerts_panel">
        @if($criticalAlerts->isNotEmpty() || $warningAlerts->isNotEmpty())
            <div class="kt-card border border-border">
                <div class="kt-card-header border-b border-border bg-muted/30">
                    <h3 class="kt-card-title flex items-center gap-2">
                        <i class="ki-filled ki-wrench text-primary"></i>
                        Que faire ?
                    </h3>
                    <span class="text-xs text-secondary-foreground">Actions recommandées par priorité</span>
                </div>
                <div class="kt-card-content p-0 divide-y divide-border" id="tech_alerts_list">
                    @foreach($criticalAlerts->merge($warningAlerts) as $i => $alert)
                        @include('pages.dashboard.partials.alert-row', ['alert' => $alert, 'index' => $i + 1])
                    @endforeach
                </div>
            </div>
        @else
            <div class="kt-card border border-[#1a3a6e]/15 bg-[#fef9e7]/30 dark:bg-[#1a3a6e]/5" id="tech_alerts_ok">
                <div class="kt-card-content flex items-center gap-4 p-5">
                    <i class="ki-filled ki-check-circle text-3xl text-success"></i>
                    <div>
                        <div class="font-semibold text-foreground">Aucune action requise</div>
                        <div class="text-sm text-secondary-foreground">Tous les services critiques fonctionnent. Les métriques non disponibles (RAM, CPU) sont normales en local ou hébergement mutualisé.</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Jauges --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6" id="tech_gauges">
        @foreach($metrics['gauges'] as $key => $gauge)
            <div class="kt-card overflow-hidden" data-gauge="{{ $key }}">
                <div class="h-1 {{ $gaugeBar[$gauge['status']] ?? 'bg-primary' }}" data-gauge-accent></div>
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 text-sm font-medium text-secondary-foreground">
                            <i class="ki-filled {{ $gauge['icon'] ?? 'ki-chart' }} text-primary"></i>
                            <span data-gauge-label>{{ $gauge['label'] }}</span>
                        </div>
                        @if($gauge['status'] !== 'info')
                            <span class="text-xl font-bold text-mono" data-gauge-percent>{{ $gauge['percent'] }}%</span>
                        @else
                            <span class="text-xs kt-badge kt-badge-outline">N/A</span>
                        @endif
                    </div>
                    @if($gauge['status'] !== 'info')
                        <div class="h-2 rounded-full bg-muted mb-3 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700 {{ $gaugeBar[$gauge['status']] ?? 'bg-primary' }}" data-gauge-bar style="width:{{ min(100, $gauge['percent']) }}%"></div>
                        </div>
                    @endif
                    <div class="text-sm font-semibold" data-gauge-value>{{ $gauge['value'] }}</div>
                    <div class="text-xs text-secondary-foreground mt-1" data-gauge-detail>{{ $gauge['detail'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-5">
        <div class="lg:col-span-3 kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Services applicatifs</h3>
            </div>
            <div class="kt-card-content p-0" id="tech_services">
                <table class="w-full text-sm">
                    <thead class="bg-muted/40 text-secondary-foreground">
                        <tr>
                            <th class="text-left font-medium px-5 py-3">Service</th>
                            <th class="text-left font-medium px-5 py-3">État</th>
                            <th class="text-right font-medium px-5 py-3">Valeur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border" id="tech_services_body">
                        @foreach($metrics['services'] as $service)
                            @include('pages.dashboard.partials.service-table-row', ['service' => $service])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="lg:col-span-2 kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Environnement</h3>
            </div>
            <div class="kt-card-content p-5 pt-0" id="tech_system">
                <dl class="divide-y divide-border">
                    @foreach($metrics['system'] as $row)
                        <div class="flex justify-between gap-4 py-2.5 text-sm">
                            <dt class="text-secondary-foreground">{{ $row['label'] }}</dt>
                            <dd class="font-mono font-medium text-right">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>

    @if($infoAlerts->isNotEmpty())
        <div class="mt-5 text-xs text-secondary-foreground flex flex-wrap gap-x-4 gap-y-1" id="tech_info_notes">
            @foreach($infoAlerts as $info)
                <span><i class="ki-filled ki-information-2"></i> {{ $info['title'] }} : {{ $info['action'] }}</span>
            @endforeach
        </div>
    @endif
</div>
@endsection
