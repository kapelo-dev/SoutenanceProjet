@extends('layouts.demo1.base')

@section('content')
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Dashboard
                </h1>
                <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                    Vue d'ensemble de l'activité en temps réel
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <a class="kt-btn kt-btn-outline" href="{{ route('transactions.index') }}">
                    Voir Transactions
                </a>
            </div>
        </div>
    </div>
    <!-- End of Container -->

    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- begin: grid -->
            <div class="grid items-stretch gap-y-5 lg:grid-cols-3 lg:gap-7.5">
                <div class="lg:col-span-1">
                    <div class="grid h-full grid-cols-2 items-stretch gap-5 lg:gap-7.5">
                        <style>
                            .channel-stats-bg {
                                background-image: url('{{ asset("assets/media/images/2600x1600/bg-3.png") }}');
                            }

                            .dark .channel-stats-bg {
                                background-image: url('{{ asset("assets/media/images/2600x1600/bg-3-dark.png") }}');
                            }
                        </style>
                        <div
                            class="kt-card channel-stats-bg h-full flex-col justify-between gap-6 rounded-2xl border border-white/40 bg-white/30 backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.08)] bg-cover bg-[right_top_-1.7rem] bg-no-repeat rtl:bg-[left_top_-1.7rem] dark:border-white/10 dark:bg-white/5">
                            <i class="ki-filled ki-chart-line ms-5 mt-4 text-3xl text-primary/70"></i>
                            <div class="flex flex-col gap-1 px-5 pb-4">
                                <span class="text-3xl font-semibold text-mono">
                                    {{ number_format($stats['transactions_jour']) }}
                                </span>
                                <span class="text-sm font-normal text-secondary-foreground">
                                    Transactions/Jour
                                </span>
                            </div>
                        </div>
                        <div
                            class="kt-card channel-stats-bg h-full flex-col justify-between gap-6 rounded-2xl border border-emerald-200/50 bg-emerald-50/40 backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.06)] bg-cover bg-[right_top_-1.7rem] bg-no-repeat rtl:bg-[left_top_-1.7rem] dark:border-emerald-300/25 dark:bg-emerald-400/10">
                            <i class="ki-filled ki-dollar ms-5 mt-4 text-3xl text-emerald-500/80"></i>
                            <div class="flex flex-col gap-1 px-5 pb-4">
                                <span class="text-3xl font-semibold text-mono">
                                    {{ number_format($stats['montant_jour'] / 1000000, 1) }}M
                                </span>
                                <span class="text-sm font-normal text-secondary-foreground">
                                    FCFA Aujourd'hui
                                </span>
                            </div>
                        </div>
                        <div
                            class="kt-card channel-stats-bg h-full flex-col justify-between gap-6 rounded-2xl border border-amber-200/50 bg-amber-50/40 backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.06)] bg-cover bg-[right_top_-1.7rem] bg-no-repeat rtl:bg-[left_top_-1.7rem] dark:border-amber-300/25 dark:bg-amber-400/10">
                            <i class="ki-filled ki-people ms-5 mt-4 text-3xl text-amber-500/80"></i>
                            <div class="flex flex-col gap-1 px-5 pb-4">
                                <span class="text-3xl font-semibold text-mono">
                                    {{ $stats['agents_actifs'] }}
                                </span>
                                <span class="text-sm font-normal text-secondary-foreground">
                                    Agents Actifs
                                </span>
                            </div>
                        </div>
                        <div
                            class="kt-card channel-stats-bg h-full flex-col justify-between gap-6 rounded-2xl border border-sky-200/50 bg-sky-50/40 backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.06)] bg-cover bg-[right_top_-1.7rem] bg-no-repeat rtl:bg-[left_top_-1.7rem] dark:border-sky-300/25 dark:bg-sky-400/10">
                            <i class="ki-filled ki-shop ms-5 mt-4 text-3xl text-sky-500/80"></i>
                            <div class="flex flex-col gap-1 px-5 pb-4">
                                <span class="text-3xl font-semibold text-mono">
                                    {{ $stats['kiosques_actifs'] }}
                                </span>
                                <span class="text-sm font-normal text-secondary-foreground">
                                    Kiosques Actifs
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="kt-card h-full">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Performance du Mois
                            </h3>
                            <div class="flex flex-col items-end gap-0.5 text-right">
                                <span class="text-xs text-secondary-foreground">
                                    Montant total : 
                                    <span class="font-semibold text-success">
                                        {{ number_format($stats['montant_mois'], 0, ',', ' ') }} FCFA
                                    </span>
                                </span>
                                <span class="text-xs text-secondary-foreground">
                                    {{ number_format($stats['transactions_mois']) }} transactions • 
                                    Commission : {{ number_format($stats['commission_mois'], 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                        <div class="kt-card-content">
                            <div id="dashboard_month_map" class="rounded-lg overflow-hidden" style="height: 260px; width: 100%;"></div>
                            <p class="mt-3 text-xs text-secondary-foreground">
                                Le cercle le plus large indique la zone avec le plus fort chiffre d'affaires du mois.
                            </p>
                        </div>
                        <div class="kt-card-footer justify-center">
                            <a class="kt-link kt-link-underlined kt-link-dashed"
                                href="{{ route('transactions.index') }}">
                                Détail des Transactions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end: grid -->

            <!-- begin: Évolution -->
            <div class="kt-card">
                <div class="kt-card-header flex-wrap gap-3">
                    <div class="flex flex-col gap-1">
                        <h3 class="kt-card-title">Évolution de l'activité</h3>
                        <span class="text-xs text-secondary-foreground">Montant et volume des transactions validées</span>
                    </div>
                    <div class="flex items-center gap-2 ms-auto">
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" data-dashboard-evolution-period="7jours">7 jours</button>
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" data-dashboard-evolution-period="30jours">30 jours</button>
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" data-dashboard-evolution-period="12mois">12 mois</button>
                    </div>
                </div>
                <div class="kt-card-content px-2 pb-2 lg:px-5">
                    <div id="dashboard_evolution_chart"
                        data-initial='@json($evolutionTransactions->values())'></div>
                </div>
            </div>
            <!-- end: Évolution -->

            <!-- begin: grid -->
            <div class="grid items-stretch gap-5 lg:grid-cols-3 lg:gap-7.5">
                <div class="lg:col-span-1">
                    <div class="kt-card h-full">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Transactions par Type
                            </h3>
                            <div class="kt-menu" data-kt-menu="true">
                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px"
                                    data-kt-menu-item-placement="bottom-start" data-kt-menu-item-toggle="dropdown"
                                    data-kt-menu-item-trigger="click">
                                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                        <i class="ki-filled ki-dots-vertical text-lg">
                                        </i>
                                    </button>
                                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[200px]"
                                        data-kt-menu-dismiss="true">
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link" href="{{ route('transactions.index') }}">
                                                <span class="kt-menu-icon">
                                                    <i class="ki-filled ki-cloud-change">
                                                    </i>
                                                </span>
                                                <span class="kt-menu-title">
                                                    Toutes les transactions
                                                </span>
                                            </a>
                                        </div>
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link" href="#">
                                                <span class="kt-menu-icon">
                                                    <i class="ki-filled ki-setting-3">
                                                    </i>
                                                </span>
                                                <span class="kt-menu-title">
                                                    Paramètres
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-card-content flex flex-col gap-5 px-5 pt-5 lg:px-7.5">
                            <div class="flex items-center gap-2.5">
                                <div class="flex items-center justify-center size-9 rounded-full bg-success-light">
                                    <i class="ki-filled ki-arrow-up text-base text-success"></i>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-medium leading-none text-foreground">
                                        Dépôts
                                    </span>
                                    <span class="text-xs font-normal text-secondary-foreground">
                                        {{ number_format($transactionsParType['depot'], 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="flex items-center justify-center size-9 rounded-full bg-destructive-light">
                                    <i class="ki-filled ki-arrow-down text-base text-destructive"></i>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-medium leading-none text-foreground">
                                        Retraits
                                    </span>
                                    <span class="text-xs font-normal text-secondary-foreground">
                                        {{ number_format($transactionsParType['retrait'], 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="flex items-center justify-center size-9 rounded-full bg-primary-light">
                                    <i class="ki-filled ki-arrows-circle text-base text-primary"></i>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-medium leading-none text-foreground">
                                        Transferts
                                    </span>
                                    <span class="text-xs font-normal text-secondary-foreground">
                                        {{ number_format($transactionsParType['transfert'], 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="kt-card-footer justify-center">
                            <a class="kt-link kt-link-underlined kt-link-dashed"
                                href="{{ route('transactions.index') }}">
                                Toutes les Transactions
                            </a>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="kt-card h-full">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Opérateurs Actifs
                            </h3>
                            <a class="kt-btn kt-btn-sm kt-btn-outline" href="{{ route('operateurs.index') }}">
                                Gérer
                            </a>
                        </div>
                        <div class="kt-card-content">
                            <div class="flex flex-col gap-5">
                                @foreach($operateurs as $item)
                                <div class="flex items-center gap-3.5 w-full">
                                    <div class="flex items-center justify-center rounded-full bg-gray-100 size-9 shrink-0 dark:bg-gray-900">
                                        @if($item['operateur'] && $item['operateur']->logo)
                                            <img class="h-6 w-6 rounded object-contain" src="{{ asset('storage/' . $item['operateur']->logo) }}" alt="{{ $item['operateur']->libelle ?? 'N/A' }}"/>
                                        @else
                                            <i class="ki-filled ki-abstract-39 text-base text-gray-600 dark:text-gray-300">
                                            </i>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                                        <span class="text-sm font-medium text-foreground whitespace-nowrap overflow-hidden text-ellipsis">
                                            {{ $item['operateur']->libelle ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs font-normal text-secondary-foreground whitespace-nowrap">
                                            {{ number_format($item['transactions']) }} transactions ce mois
                                        </span>
                                    </div>
                                    <div class="flex flex-col items-end gap-0.5 shrink-0 ms-auto">
                                        <span class="text-sm font-semibold text-success whitespace-nowrap">
                                            {{ number_format($item['montant'], 0, ',', ' ') }} FCFA
                                        </span>
                                        <span class="text-xs font-normal text-secondary-foreground">
                                            {{ number_format($item['transactions']) }} trans.
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end: grid -->

            <!-- begin: Dernières Transactions -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        Dernières Transactions
                    </h3>
                    <a class="kt-btn kt-btn-sm kt-btn-outline" href="{{ route('transactions.index') }}">
                        Voir Tout
                    </a>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-5">
                        @foreach($dernieresTransactions->take(5) as $transaction)
                        <div class="flex items-center gap-2.5">
                            <div class="flex items-center justify-center rounded-full {{ $transaction->type == 'depot' ? 'bg-success-light' : 'bg-destructive-light' }} size-9">
                                <i class="ki-filled {{ $transaction->type == 'depot' ? 'ki-arrow-up' : 'ki-arrow-down' }} text-base {{ $transaction->type == 'depot' ? 'text-success' : 'text-destructive' }}"></i>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-medium text-foreground">
                                    {{ $transaction->agent->nom }} {{ $transaction->agent->prenom }}
                                </span>
                                <span class="text-xs font-normal text-secondary-foreground">
                                    {{ ucfirst($transaction->type) }} - {{ $transaction->operateur?->libelle ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="flex -space-x-2 ms-auto">
                                @if($transaction->operateur && $transaction->operateur->logo)
                                <img class="h-7 w-7 rounded" src="{{ asset('storage/' . $transaction->operateur->logo) }}" alt="{{ $transaction->operateur->libelle ?? 'N/A' }}"/>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 lg:gap-5">
                                <span class="text-sm font-semibold {{ $transaction->type == 'depot' ? 'text-success' : 'text-destructive' }}">
                                    {{ $transaction->type == 'depot' ? '+' : '-' }}{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA
                                </span>
                                <div class="kt-menu" data-kt-menu="true">
                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px"
                                        data-kt-menu-item-placement="bottom-end"
                                        data-kt-menu-item-placement-rtl="bottom-start"
                                        data-kt-menu-item-toggle="dropdown"
                                        data-kt-menu-item-trigger="click">
                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                            <i class="ki-filled ki-dots-vertical text-lg">
                                            </i>
                                        </button>
                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[200px]"
                                            data-kt-menu-dismiss="true">
                                            <div class="kt-menu-item">
                                                <a class="kt-menu-link" href="{{ route('transactions.show', $transaction->id) }}">
                                                    <span class="kt-menu-icon">
                                                        <i class="ki-filled ki-document">
                                                        </i>
                                                    </span>
                                                    <span class="kt-menu-title">
                                                        Détails
                                                    </span>
                                                </a>
                                            </div>
                                            <div class="kt-menu-item">
                                                <a class="kt-menu-link" href="{{ route('transactions.edit', $transaction->id) }}">
                                                    <span class="kt-menu-icon">
                                                        <i class="ki-filled ki-pencil">
                                                        </i>
                                                    </span>
                                                    <span class="kt-menu-title">
                                                        Modifier
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- end: Dernières Transactions -->

        </div>
    </div>
    <!-- End of Container -->

@endsection
