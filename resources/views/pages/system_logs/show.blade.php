<div class="flex flex-col gap-5">
    <!-- Informations générales -->
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Date et heure</span>
            <span class="font-medium">{{ $systemLog->created_at->format('d/m/Y à H:i:s') }}</span>
        </div>

        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Utilisateur</span>
            @if($systemLog->utilisateur)
                <span class="font-medium">{{ $systemLog->utilisateur->nom }} {{ $systemLog->utilisateur->prenom }}</span>
                <span class="text-xs text-secondary-foreground">{{ $systemLog->utilisateur->email }}</span>
            @else
                <span class="font-medium">Système</span>
            @endif
        </div>

        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Action</span>
            <span class="kt-badge kt-badge-{{ $systemLog->action_color }}">{{ $systemLog->action_label }}</span>
        </div>

        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Entité concernée</span>
            @if($systemLog->model_name)
                <span class="font-medium">{{ $systemLog->model_name }} #{{ $systemLog->model_id }}</span>
            @else
                <span class="text-secondary-foreground">-</span>
            @endif
        </div>

        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Adresse IP</span>
            <span class="font-medium">{{ $systemLog->ip_address ?? '-' }}</span>
        </div>

        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Navigateur</span>
            <span class="text-sm">{{ Str::limit($systemLog->user_agent ?? '-', 50) }}</span>
        </div>
    </div>

    <!-- Description -->
    <div class="flex flex-col gap-2">
        <span class="text-sm text-secondary-foreground">Description</span>
        <div class="kt-card bg-secondary/10">
            <div class="kt-card-content p-4">
                <p class="text-sm">{{ $systemLog->description }}</p>
            </div>
        </div>
    </div>

    <!-- Anciennes valeurs -->
    @if($systemLog->old_values)
        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Anciennes valeurs</span>
            <div class="kt-card bg-destructive/10">
                <div class="kt-card-content p-4">
                    <pre class="text-xs overflow-auto">{{ json_encode($systemLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Nouvelles valeurs -->
    @if($systemLog->new_values)
        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Nouvelles valeurs</span>
            <div class="kt-card bg-success/10">
                <div class="kt-card-content p-4">
                    <pre class="text-xs overflow-auto">{{ json_encode($systemLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Métadonnées -->
    @if($systemLog->metadata)
        <div class="flex flex-col gap-2">
            <span class="text-sm text-secondary-foreground">Métadonnées supplémentaires</span>
            <div class="kt-card bg-info/10">
                <div class="kt-card-content p-4">
                    <pre class="text-xs overflow-auto">{{ json_encode($systemLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    @endif
</div>
