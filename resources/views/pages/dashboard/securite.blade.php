@extends('layouts.demo1.base')

@section('content')
@php
    $health = $metrics['health'];
    $alerts = $metrics['alerts'];
    $actionableAlerts = collect($alerts)->whereIn('severity', ['critical', 'warning']);
    $gaugeBar = ['ok' => 'bg-emerald-600', 'warning' => 'bg-amber-500', 'error' => 'bg-destructive', 'info' => 'bg-muted-foreground/40'];
    $statusConfig = [
        'ok' => ['border' => 'border-emerald-200/50', 'bg' => 'bg-emerald-50/80 dark:bg-emerald-950/20', 'text' => 'text-emerald-900 dark:text-emerald-100', 'icon' => 'ki-shield-tick', 'badge' => 'kt-badge-success', 'badgeLabel' => 'Normal'],
        'warning' => ['border' => 'border-amber-200/50', 'bg' => 'bg-amber-50/80 dark:bg-amber-950/20', 'text' => 'text-amber-950 dark:text-amber-100', 'icon' => 'ki-shield-search', 'badge' => 'kt-badge-warning', 'badgeLabel' => 'Surveillance'],
        'error' => ['border' => 'border-destructive/30', 'bg' => 'bg-destructive/5', 'text' => 'text-destructive', 'icon' => 'ki-shield-cross', 'badge' => 'kt-badge-destructive', 'badgeLabel' => 'Alerte'],
    ];
    $cfg = $statusConfig[$health['status']] ?? $statusConfig['warning'];
    $maxTimeline = max(1, collect($metrics['timeline'])->max(fn($t) => max($t['failed'], $t['success'])));
@endphp

<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5">
        <div>
            <h1 class="text-xl font-semibold text-mono">Dashboard Sécurité</h1>
            <p class="text-sm text-secondary-foreground mt-1">Tentatives d'intrusion, connexions et surveillance applicative</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('system-logs.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">Logs Système</a>
            <span id="sec_updated" class="text-xs text-secondary-foreground"></span>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" id="sec_refresh">
                <i class="ki-filled ki-arrows-circle"></i> Actualiser
            </button>
        </div>
    </div>
</div>

<div class="kt-container-fixed pb-8" id="sec_dashboard_root"
    data-metrics-url="{{ route('dashboard.securite.metrics') }}"
    data-resolve-alert-url="{{ route('dashboard.securite.alerts.resolve') }}"
    data-block-url="{{ route('blocked-ips.store') }}"
    data-unblock-url="{{ url('/api/blocked-ips') }}"
    data-initial='@json($metrics)'>

    <div class="rounded-2xl border p-5 mb-6 {{ $cfg['border'] }} {{ $cfg['bg'] }} {{ $cfg['text'] }}" id="sec_health_banner">
        <div class="flex flex-wrap items-start gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                <i class="ki-filled {{ $cfg['icon'] }} text-2xl" id="sec_health_icon"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-lg font-semibold" id="sec_health_label">{{ $health['label'] }}</span>
                    <span class="kt-badge kt-badge-sm {{ $cfg['badge'] }}" id="sec_health_badge">{{ $cfg['badgeLabel'] }}</span>
                </div>
                <p class="text-sm opacity-90" id="sec_health_summary">{{ $health['summary'] }}</p>
            </div>
            <div class="flex gap-6 text-center text-sm" id="sec_health_counts">
                <div>
                    <div class="text-2xl font-bold" data-count="failed">{{ $metrics['stats']['login_failed_24h'] }}</div>
                    <div class="text-xs opacity-75">Échecs (24h)</div>
                </div>
                <div>
                    <div class="text-2xl font-bold" data-count="success">{{ $metrics['stats']['login_success_24h'] }}</div>
                    <div class="text-xs opacity-75">Connexions OK</div>
                </div>
                <div>
                    <div class="text-2xl font-bold" data-count="ips">{{ $metrics['stats']['suspicious_ips_count'] }}</div>
                    <div class="text-xs opacity-75">IPs suspectes</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6" id="sec_alerts_panel">
        @if($actionableAlerts->isNotEmpty())
            <div class="kt-card border border-border">
                <div class="kt-card-header border-b border-border bg-muted/30">
                    <h3 class="kt-card-title flex items-center gap-2">
                        <i class="ki-filled ki-information-3 text-destructive"></i>
                        Alertes sécurité
                    </h3>
                </div>
                <div class="kt-card-content p-0 divide-y divide-border" id="sec_alerts_list">
                    @foreach($actionableAlerts as $i => $alert)
                        @include('pages.dashboard.partials.alert-row', ['alert' => $alert, 'index' => $i + 1])
                    @endforeach
                </div>
            </div>
        @else
            <div class="kt-card border border-emerald-200/40 bg-emerald-50/30 dark:bg-emerald-950/10" id="sec_alerts_ok">
                <div class="kt-card-content flex items-center gap-4 p-5">
                    <i class="ki-filled ki-shield-tick text-3xl text-emerald-600"></i>
                    <div>
                        <div class="font-semibold text-foreground">Aucune menace active détectée</div>
                        <div class="text-sm text-secondary-foreground">Les tentatives d'intrusion enregistrées n'ont pas abouti sur les dernières 24h.</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6" id="sec_gauges">
        @foreach($metrics['gauges'] as $key => $gauge)
            <div class="kt-card overflow-hidden" data-gauge="{{ $key }}">
                <div class="h-1 {{ $gaugeBar[$gauge['status']] ?? 'bg-primary' }}" data-gauge-accent></div>
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2 text-sm font-medium text-secondary-foreground">
                            <i class="ki-filled {{ $gauge['icon'] ?? 'ki-shield' }} text-primary"></i>
                            <span data-gauge-label>{{ $gauge['label'] }}</span>
                        </div>
                        <span class="text-xl font-bold text-mono" data-gauge-percent>{{ $gauge['percent'] }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-muted mb-3 overflow-hidden">
                        <div class="h-full rounded-full {{ $gaugeBar[$gauge['status']] ?? 'bg-primary' }}" data-gauge-bar style="width:{{ min(100, $gauge['percent']) }}%"></div>
                    </div>
                    <div class="text-sm font-semibold" data-gauge-value>{{ $gauge['value'] }}</div>
                    <div class="text-xs text-secondary-foreground mt-1" data-gauge-detail>{{ $gauge['detail'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-5 mb-6">
        <div class="lg:col-span-3 kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Activité authentification (24h)</h3>
                <span class="text-xs text-secondary-foreground">Échecs vs connexions réussies</span>
            </div>
            <div class="kt-card-content px-5 pb-5" id="sec_timeline">
                <div class="flex items-end gap-1 h-32">
                    @foreach($metrics['timeline'] as $point)
                        @php
                            $fh = round(($point['failed'] / $maxTimeline) * 100);
                            $sh = round(($point['success'] / $maxTimeline) * 100);
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-0.5 group" title="{{ $point['label'] }}: {{ $point['failed'] }} échec(s), {{ $point['success'] }} OK">
                            <div class="w-full flex items-end justify-center gap-px h-24">
                                <div class="w-[45%] rounded-t bg-destructive/70" style="height:{{ max(2, $fh) }}%"></div>
                                <div class="w-[45%] rounded-t bg-emerald-600/70" style="height:{{ max(2, $sh) }}%"></div>
                            </div>
                            <span class="text-[9px] text-secondary-foreground">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-4 mt-3 text-xs text-secondary-foreground">
                    <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-destructive/70"></span> Échecs</span>
                    <span class="inline-flex items-center gap-1"><span class="size-2 rounded bg-emerald-600/70"></span> Succès</span>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2 kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Top IPs (échecs — 7j)</h3>
            </div>
            <div class="kt-card-content p-0" id="sec_top_ips">
                <table class="w-full text-sm">
                    <thead class="bg-muted/40 text-secondary-foreground">
                        <tr>
                            <th class="text-left font-medium px-5 py-2">IP</th>
                            <th class="text-right font-medium px-5 py-2">Échecs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border" id="sec_top_ips_body">
                        @forelse($metrics['top_ips'] as $row)
                            <tr>
                                <td class="px-5 py-2 font-mono text-xs">{{ $row['ip'] }}</td>
                                <td class="px-5 py-2 text-right font-semibold text-destructive">{{ $row['failures'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-6 text-center text-secondary-foreground">Aucune tentative</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="kt-card mb-6">
        <div class="kt-card-header flex-wrap gap-3">
            <div>
                <h3 class="kt-card-title">IPs bloquées</h3>
                <span class="text-xs text-secondary-foreground">Blocage auto après {{ $metrics['auto_block_threshold'] }} échecs / 24h — déblocage manuel possible</span>
            </div>
            <form id="sec_block_ip_form" class="flex flex-wrap items-end gap-2 ms-auto">
                @csrf
                <div>
                    <input type="text" name="ip_address" class="kt-input kt-input-sm w-40 font-mono" placeholder="192.168.1.1" required pattern="^[\d\.:]+$" />
                </div>
                <div>
                    <input type="text" name="reason" class="kt-input kt-input-sm w-48" placeholder="Motif (optionnel)" />
                </div>
                <button type="submit" class="kt-btn kt-btn-sm kt-btn-destructive">Bloquer</button>
            </form>
        </div>
        <div class="kt-card-content p-0" id="sec_blocked_ips">
            @forelse($metrics['blocked_ips'] as $block)
                <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-border last:border-0" data-block-id="{{ $block['id'] }}">
                    <div class="min-w-0">
                        <div class="font-mono text-sm font-medium">{{ $block['ip'] }}</div>
                        <div class="text-xs text-secondary-foreground truncate">{{ $block['reason'] }}</div>
                        <div class="text-[11px] text-secondary-foreground mt-0.5">
                            {{ $block['source_label'] }}
                            @if($block['failed_attempts'] > 0)
                                · {{ $block['failed_attempts'] }} échec(s)
                            @endif
                            · {{ \Carbon\Carbon::parse($block['blocked_at'])->diffForHumans() }}
                        </div>
                    </div>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-outline shrink-0 sec-unblock-btn" data-block-id="{{ $block['id'] }}">
                        Débloquer
                    </button>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-secondary-foreground">Aucune IP bloquée actuellement.</p>
            @endforelse
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2 mb-6">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">IPs suspectes</h3>
                <span class="text-xs text-secondary-foreground">≥ 3 échecs / 24h — connexion refusée, IP non bannie</span>
            </div>
            <div class="kt-card-content p-0" id="sec_suspicious_ips">
                @forelse($metrics['suspicious_ips'] as $ip)
                    <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-border last:border-0">
                        <div>
                            <div class="font-mono text-sm">{{ $ip['ip'] }}</div>
                            <div class="text-xs text-secondary-foreground">{{ $ip['failures'] }} tentative(s) — dernière : {{ \Carbon\Carbon::parse($ip['last_attempt'])->diffForHumans() }}</div>
                        </div>
                        @if($ip['is_blocked'] ?? false)
                            <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Bloquée</span>
                        @else
                            <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Surveillée</span>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-secondary-foreground">Aucune IP suspecte sur les dernières 24h.</p>
                @endforelse
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Outils de surveillance</h3>
            </div>
            <div class="kt-card-content p-0" id="sec_tools">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-border">
                        @foreach($metrics['monitoring_tools'] as $tool)
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="font-medium">{{ $tool['name'] }}</div>
                                    <div class="text-xs text-secondary-foreground">{{ $tool['role'] }} — {{ $tool['scope'] }}</div>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @php
                                        $badge = match($tool['status']) {
                                            'actif' => 'kt-badge-success',
                                            'recommandé' => 'kt-badge-warning',
                                            default => 'kt-badge-outline',
                                        };
                                    @endphp
                                    <span class="kt-badge kt-badge-sm {{ $badge }}">{{ ucfirst($tool['status']) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Événements récents</h3>
            <a href="{{ route('system-logs.index') }}" class="kt-link kt-link-sm">Voir tout</a>
        </div>
        <div class="kt-card-content p-0" id="sec_recent_events">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-secondary-foreground">
                    <tr>
                        <th class="text-left font-medium px-5 py-2">Date</th>
                        <th class="text-left font-medium px-5 py-2">Type</th>
                        <th class="text-left font-medium px-5 py-2">Description</th>
                        <th class="text-left font-medium px-5 py-2">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border" id="sec_recent_body">
                    @foreach($metrics['recent_events'] as $event)
                        <tr>
                            <td class="px-5 py-2.5 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($event['at'])->locale('fr')->isoFormat('D MMM HH:mm') }}</td>
                            <td class="px-5 py-2.5">
                                @php
                                    $evBadge = match($event['severity']) {
                                        'critical' => 'kt-badge-destructive',
                                        'warning' => 'kt-badge-warning',
                                        default => 'kt-badge-success',
                                    };
                                @endphp
                                <span class="kt-badge kt-badge-sm kt-badge-outline {{ $evBadge }}">{{ $event['action_label'] }}</span>
                            </td>
                            <td class="px-5 py-2.5 text-secondary-foreground truncate max-w-xs">{{ $event['description'] }}</td>
                            <td class="px-5 py-2.5 font-mono text-xs">{{ $event['ip'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
