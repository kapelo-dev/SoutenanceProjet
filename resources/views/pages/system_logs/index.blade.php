@extends('layouts.demo1.base')

@section('content')
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-xl font-medium leading-none text-mono">
                Logs Système
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Historique des actions effectuées sur le système
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-sm kt-btn-outline" href="{{ route('system-logs.export.excel', request()->query()) }}">
                <i class="ki-filled ki-file-down"></i>
                Excel
            </a>
            <a class="kt-btn kt-btn-sm kt-btn-outline" href="{{ route('system-logs.export.pdf', request()->query()) }}">
                <i class="ki-filled ki-file-down"></i>
                PDF
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistiques -->
        <div class="grid grid-cols-2 gap-5 lg:grid-cols-4 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-2xl font-semibold text-mono">{{ number_format($stats['total']) }}</span>
                    <span class="text-sm text-secondary-foreground">Total des logs</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-2xl font-semibold text-mono text-primary">{{ number_format($stats['today']) }}</span>
                    <span class="text-sm text-secondary-foreground">Aujourd'hui</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-2xl font-semibold text-mono text-success">{{ number_format($stats['this_week']) }}</span>
                    <span class="text-sm text-secondary-foreground">Cette semaine</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-2xl font-semibold text-mono text-info">{{ number_format($stats['this_month']) }}</span>
                    <span class="text-sm text-secondary-foreground">Ce mois</span>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filtres</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('system-logs.index') }}" class="grid gap-4 lg:grid-cols-4" data-ajax="false">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Utilisateur</label>
                        <select name="user_id" class="kt-select" data-kt-select="true">
                            <option value="">Tous</option>
                            @foreach($utilisateurs as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->nom }} {{ $user->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Action</label>
                        <select name="action" class="kt-select" data-kt-select="true">
                            <option value="">Toutes</option>
                            @foreach($actions as $key => $label)
                                <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Type d'entité</label>
                        <select name="model_type" class="kt-select" data-kt-select="true">
                            <option value="">Tous</option>
                            @foreach($modelTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('model_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Recherche</label>
                        <input type="text" name="search" class="kt-input" placeholder="Description..." value="{{ request('search') }}">
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Date début</label>
                        <input type="date" name="date_debut" class="kt-input" value="{{ request('date_debut') }}">
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Date fin</label>
                        <input type="date" name="date_fin" class="kt-input" value="{{ request('date_fin') }}">
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-filter"></i>
                            Filtrer
                        </button>
                        <a href="{{ route('system-logs.index') }}" class="kt-btn kt-btn-outline">
                            <i class="ki-filled ki-arrows-circle"></i>
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des logs -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    Liste des logs ({{ $logs->total() }})
                </h3>
            </div>
            <div class="kt-card-content p-0">
                <div class="overflow-x-auto">
                    <table class="kt-table">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Entité</th>
                                <th>Description</th>
                                <th>IP</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ $log->created_at->format('d/m/Y') }}</span>
                                            <span class="text-xs text-secondary-foreground">{{ $log->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->utilisateur)
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center justify-center size-9 rounded-full bg-primary-light">
                                                    <span class="text-sm font-semibold text-primary">
                                                        {{ strtoupper(substr($log->utilisateur->prenom, 0, 1)) }}{{ strtoupper(substr($log->utilisateur->nom, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ $log->utilisateur->nom }} {{ $log->utilisateur->prenom }}</span>
                                                    <span class="text-xs text-secondary-foreground">{{ $log->utilisateur->email }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-secondary-foreground">Système</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="kt-badge kt-badge-{{ $log->action_color }}">
                                            {{ $log->action_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->model_name)
                                            <span class="font-medium">{{ $log->model_name }}</span>
                                            @if($log->model_id)
                                                <span class="text-xs text-secondary-foreground">#{{ $log->model_id }}</span>
                                            @endif
                                        @else
                                            <span class="text-secondary-foreground">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ Str::limit($log->description, 80) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs text-secondary-foreground">{{ $log->ip_address ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon" onclick="viewLogDetails({{ $log->id }})">
                                            <i class="ki-filled ki-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="ki-filled ki-information-2 text-4xl text-secondary-foreground"></i>
                                            <span class="text-secondary-foreground">Aucun log trouvé</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="kt-card-footer justify-center">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal détails log -->
<div id="modal_log_details" class="kt-modal" data-kt-modal="true" data-kt-modal-backdrop-static="true">
    <div class="kt-modal-content max-w-3xl">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Détails du log</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body" id="log_details_content">
            <div class="flex items-center justify-center py-8">
                <div class="kt-spinner kt-spinner-primary"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewLogDetails(logId) {
    const modal = document.getElementById('modal_log_details');
    const content = document.getElementById('log_details_content');
    
    // Afficher le modal
    KTModal.getInstance(modal).show();
    
    // Charger les détails
    content.innerHTML = '<div class="flex items-center justify-center py-8"><div class="kt-spinner kt-spinner-primary"></div></div>';
    
    fetch(`/system-logs/${logId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="text-center text-destructive py-8">Erreur lors du chargement des détails</div>';
        });
}
</script>
@endpush
@endsection
