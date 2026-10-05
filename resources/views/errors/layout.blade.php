@php
    $code = trim($__env->yieldContent('code'));
    // Seul le message d'un abort() explicite est affiché : les exceptions internes converties
    // en HttpException (SQL, CSRF, modèle introuvable...) ont un "previous" et ne doivent pas fuiter.
    $isExplicitAbort = isset($exception)
        && get_class($exception) === \Symfony\Component\HttpKernel\Exception\HttpException::class
        && $exception->getPrevious() === null;
    $exceptionMessage = $isExplicitAbort ? trim($exception->getMessage()) : '';
    // yieldContent() renvoie déjà du HTML échappé : on le décode pour éviter un double échappement par {{ }}
    $message = $exceptionMessage !== ''
        ? $exceptionMessage
        : html_entity_decode(trim($__env->yieldContent('message')), ENT_QUOTES);
@endphp
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex"/>
    <title>@yield('title') - PDV Connect</title>
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet"/>
    @php
        // Une page d'erreur ne doit jamais planter à cause d'un build Vite absent
        try {
            echo app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css']);
        } catch (\Throwable $e) {
        }
    @endphp
</head>
<body class="antialiased flex h-full items-center justify-center bg-background p-6">
    <div class="max-w-md w-full kt-card p-8 text-center">
        <div class="flex justify-center mb-4">
            <i class="ki-filled @yield('icon', 'ki-information-2') text-5xl @yield('color', 'text-destructive')"></i>
        </div>
        <div class="text-4xl font-bold text-mono mb-2">{{ $code }}</div>
        <h1 class="text-xl font-semibold text-mono mb-2">@yield('title')</h1>
        <p class="text-sm text-secondary-foreground mb-6">{{ $message }}</p>

        <div class="flex flex-wrap justify-center gap-2.5">
            @hasSection('actions')
                @yield('actions')
            @else
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="kt-btn kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Retour
                </a>
                <a href="{{ url('/') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-home"></i> Accueil
                </a>
            @endif
        </div>

        @hasSection('hint')
            <p class="text-xs text-secondary-foreground mt-6">@yield('hint')</p>
        @endif
    </div>
</body>
</html>
