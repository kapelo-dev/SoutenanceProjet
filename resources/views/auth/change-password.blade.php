<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="fr">
<head>
    <base href="{{ url('/') }}">
    <title>Changer le mot de passe - PDV Connect</title>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <link href="{{ asset('assets/media/app/favicon.svg') }}" rel="icon" type="image/svg+xml" />
    <link href="{{ asset('assets/media/app/favicon.ico') }}" rel="shortcut icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Geist+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <script>
        const defaultThemeMode = 'light';
        let themeMode = localStorage.getItem('kt-theme') || defaultThemeMode;
        if (themeMode === 'system') {
            themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.classList.add(themeMode);
    </script>

    <style>
        .login-hero {
            background: linear-gradient(145deg, #0f1f3d 0%, #1a3a6e 52%, #122847 100%);
            position: relative;
            overflow: hidden;
        }
        .login-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 85% 15%, rgba(245, 196, 0, 0.14) 0%, transparent 42%),
                radial-gradient(circle at 10% 90%, rgba(255, 255, 255, 0.06) 0%, transparent 38%);
            pointer-events: none;
        }
        .login-hero-logo {
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
        }
        .login-hero-accent {
            background: linear-gradient(90deg, #f5c400 0%, rgba(245, 196, 0, 0.15) 100%);
        }
    </style>

    <div class="grid lg:grid-cols-2 grow">
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <div class="max-w-[420px] w-full">
                <div class="mb-8 lg:hidden flex justify-center">
                    <a href="{{ url('/') }}" class="login-hero-logo inline-flex rounded-xl px-5 py-3.5">
                        <img src="{{ asset('assets/media/app/pdv-connect-logo.svg') }}" alt="PDV Connect" class="h-10 w-auto max-w-[220px] object-contain" />
                    </a>
                </div>

                <form action="{{ route('password.change') }}" class="flex flex-col gap-6" method="POST">
                    @csrf

                    <div class="flex flex-col gap-2">
                        <h3 class="text-2xl font-bold text-foreground leading-none" style="font-family: 'Geist Sans', system-ui, sans-serif;">
                            Changer le mot de passe
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Définissez un nouveau mot de passe pour accéder à PDV Connect.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="kt-alert kt-alert-danger">
                            <div class="kt-alert-content">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">Nouveau mot de passe</label>
                        <label class="kt-input @error('password') kt-input-error @enderror" data-kt-toggle-password="true">
                            <i class="ki-filled ki-lock text-muted-foreground text-sm"></i>
                            <input name="password" placeholder="Min. 8 caractères" type="password" required minlength="8" />
                            <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                                <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                                <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                            </button>
                        </label>
                        @error('password')<span class="text-xs text-destructive">{{ $message }}</span>@enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">Confirmer le mot de passe</label>
                        <label class="kt-input @error('password_confirmation') kt-input-error @enderror" data-kt-toggle-password="true">
                            <i class="ki-filled ki-lock text-muted-foreground text-sm"></i>
                            <input name="password_confirmation" placeholder="Répéter le mot de passe" type="password" required minlength="8" />
                            <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                                <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                                <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                            </button>
                        </label>
                        @error('password_confirmation')<span class="text-xs text-destructive">{{ $message }}</span>@enderror
                    </div>

                    <button class="kt-btn kt-btn-primary flex justify-center grow h-11 text-sm font-semibold" type="submit">
                        Enregistrer et continuer
                    </button>
                </form>
            </div>
        </div>

        <div class="order-1 lg:order-2 login-hero flex min-h-[280px] lg:min-h-full flex-col justify-start p-8 lg:p-12 xl:p-16">
            <div class="relative z-10 flex w-full max-w-lg flex-col gap-8">
                <a href="{{ url('/') }}" class="login-hero-logo inline-flex w-fit rounded-xl px-5 py-3.5 transition-transform hover:scale-[1.01]">
                    <img src="{{ asset('assets/media/app/pdv-connect-logo.svg') }}" alt="PDV Connect" class="h-11 w-auto max-w-[240px] object-contain" />
                </a>
                <div class="flex flex-col gap-4">
                    <div class="login-hero-accent h-1 w-14 rounded-full"></div>
                    <h1 class="text-3xl font-bold leading-tight text-white lg:text-[2.35rem] lg:leading-[1.15]" style="font-family: 'Geist Sans', system-ui, sans-serif;">
                        Sécurisez votre<br/>
                        <span class="text-[#f5c400]">accès agent</span>
                    </h1>
                    <p class="max-w-md text-base leading-relaxed text-white/65">
                        Pour votre première connexion, choisissez un mot de passe personnel. Il protège l'accès à vos transactions et à votre espace PDV Connect.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    @vite(['resources/js/app.js'])
</body>
</html>
