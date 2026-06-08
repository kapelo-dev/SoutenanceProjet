<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Accès bloqué - PDV Connect</title>
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased flex h-full items-center justify-center bg-background p-6">
    <div class="max-w-md w-full kt-card p-8 text-center">
        <div class="flex justify-center mb-4">
            <i class="ki-filled ki-shield-cross text-5xl text-destructive"></i>
        </div>
        <h1 class="text-xl font-semibold text-mono mb-2">Adresse IP bloquée</h1>
        <p class="text-sm text-secondary-foreground mb-4">
            L'accès à cette application est refusé pour votre adresse IP
            <span class="font-mono font-medium text-foreground">{{ $ip }}</span>.
        </p>
        <div class="rounded-lg bg-muted/50 border border-border px-4 py-3 text-sm text-left mb-6">
            <span class="font-medium text-foreground">Motif :</span>
            {{ $reason }}
        </div>
        <p class="text-xs text-secondary-foreground">
            Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur du système.
        </p>
    </div>
</body>
</html>
