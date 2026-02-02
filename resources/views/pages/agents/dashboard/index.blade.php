@extends('layouts.demo1.base')

@section('content')
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Dashboard Agent
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                {{ $agent->nom }} {{ $agent->prenom }} @if($agent->kiosque) — {{ $agent->kiosque->nom }} @endif
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-outline" href="{{ route('transactions.index') }}">
                <i class="ki-filled ki-eye"></i>
                Voir toutes les transactions
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm text-secondary-foreground">Aujourd'hui</div>
                        <div class="text-2xl font-semibold text-mono">{{ number_format($stats['today_total'] ?? 0, 0, ',', ' ') }} F</div>
                        <div class="text-xs text-muted-foreground">{{ $stats['today_count'] ?? 0 }} transaction(s) validée(s)</div>
                    </div>
                    <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <i class="ki-filled ki-calendar text-primary text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm text-secondary-foreground">Ce mois</div>
                        <div class="text-2xl font-semibold text-mono">{{ number_format($stats['month_total'] ?? 0, 0, ',', ' ') }} F</div>
                        <div class="text-xs text-muted-foreground">{{ $stats['month_count'] ?? 0 }} transaction(s) validée(s)</div>
                    </div>
                    <div class="size-12 rounded-full bg-success/10 flex items-center justify-center">
                        <i class="ki-filled ki-chart-line text-success text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content p-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm text-secondary-foreground">Commission (mois)</div>
                        <div class="text-2xl font-semibold text-mono">{{ number_format($stats['month_commission'] ?? 0, 0, ',', ' ') }} F</div>
                        <div class="text-xs text-muted-foreground">Total validé</div>
                    </div>
                    <div class="size-12 rounded-full bg-warning/10 flex items-center justify-center">
                        <i class="ki-filled ki-dollar text-warning text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card mt-7.5">
        <div class="kt-card-header py-5">
            <h3 class="kt-card-title">Dernières transactions</h3>
        </div>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th class="min-w-[180px] text-center" style="width: 25%;">Référence</th>
                            <th class="min-w-[140px] text-center" style="width: 18%;">Type</th>
                            <th class="min-w-[160px] text-center" style="width: 20%;">Opérateur</th>
                            <th class="min-w-[160px] text-center" style="width: 20%;">Montant</th>
                            <th class="min-w-[160px] text-center" style="width: 17%;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td class="text-center">
                                    <a class="font-medium text-sm text-mono hover:text-primary" href="{{ route('transactions.show', $t->id) }}">
                                        {{ $t->reference }}
                                    </a>
                                    <div class="text-xs text-secondary-foreground">{{ ucfirst($t->statut) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">{{ ucfirst($t->type) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm">{{ $t->operateur?->libelle ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-semibold">{{ number_format($t->montant ?? 0, 0, ',', ' ') }} F</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm text-secondary-foreground">{{ $t->date?->format('d/m/Y H:i') ?? '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-muted-foreground">
                                    Aucune transaction pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

