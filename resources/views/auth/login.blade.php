<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="fr">
<head>
    <base href="{{ url('/') }}">
    <title>Connexion - PDV Connect</title>
    <meta charset="utf-8"/>
    <meta content="follow, index" name="robots"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta content="Page de connexion" name="description"/>
    <link href="{{ asset('assets/media/app/favicon.svg') }}" rel="icon" type="image/svg+xml" />
    <link href="{{ asset('assets/media/app/apple-touch-icon.png') }}" rel="apple-touch-icon" sizes="180x180" />
    <link href="{{ asset('assets/media/app/favicon-32x32.png') }}" rel="icon" sizes="32x32" type="image/png" />
    <link href="{{ asset('assets/media/app/favicon-16x16.png') }}" rel="icon" sizes="16x16" type="image/png" />
    <link href="{{ asset('assets/media/app/favicon.ico') }}" rel="shortcut icon" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Geist+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <!-- Theme Mode -->
    <script>
        const defaultThemeMode = 'light';
        let themeMode;

        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }

            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.classList.add(themeMode);
        }
    </script>
    <!-- End of Theme Mode -->
    
    <!-- Page -->
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
        .login-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.55) 0%, transparent 85%);
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
                <form action="{{ route('login') }}" method="POST" id="sign_in_form" class="flex flex-col gap-6">
                    @csrf

                    <!-- Header -->
                    <div class="flex flex-col gap-2 mb-2">
                        <h3 class="text-2xl font-bold text-foreground leading-none" style="font-family: 'Geist Sans', system-ui, sans-serif;">
                            Connexion
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Entrez vos identifiants pour accéder à votre espace.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-information-2 text-red-500 text-lg shrink-0 mt-0.5"></i>
                                <div class="flex flex-col gap-1 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <span>{{ $error }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Identifiant -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">
                            Identifiant
                        </label>
                        <div class="kt-input @error('identifiant') kt-input-error @enderror">
                            <i class="ki-filled ki-user text-muted-foreground text-sm"></i>
                            <input
                                name="identifiant"
                                type="text"
                                placeholder="email@exemple.com ou AG0001"
                                value="{{ old('identifiant', old('email')) }}"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <span class="text-xs text-muted-foreground">
                            Personnel : votre email · Agent : votre code agent
                        </span>
                        @error('identifiant')
                            <span class="text-xs text-destructive flex items-center gap-1">
                                <i class="ki-filled ki-information-2 text-xs"></i>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-foreground">
                                Mot de passe
                            </label>
                            <a class="text-xs font-medium text-[#314e6c] hover:text-[#263d56] transition-colors" href="#">
                                Mot de passe oublié?
                            </a>
                        </div>
                        <div class="kt-input @error('password') kt-input-error @enderror" data-kt-toggle-password="true">
                            <i class="ki-filled ki-lock text-muted-foreground text-sm"></i>
                            <input
                                name="password"
                                placeholder="Entrez votre mot de passe"
                                type="password"
                                required
                            />
                            <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                                <span class="kt-toggle-password-active:hidden">
                                    <i class="ki-filled ki-eye text-muted-foreground"></i>
                                </span>
                                <span class="hidden kt-toggle-password-active:block">
                                    <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                                </span>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-xs text-destructive flex items-center gap-1">
                                <i class="ki-filled ki-information-2 text-xs"></i>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Remember + Submit -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
                            <span class="text-sm text-muted-foreground">
                                Se souvenir de moi
                            </span>
                        </label>
                    </div>

                    <button class="kt-btn kt-btn-primary flex justify-center grow h-11 text-sm font-semibold" type="submit">
                        Se connecter
                    </button>

                    <!-- Divider -->
                    <div class="flex items-center gap-3">
                        <div class="h-px grow bg-border"></div>
                        <span class="text-xs text-muted-foreground shrink-0">ou</span>
                        <div class="h-px grow bg-border"></div>
                    </div>

                    <!-- Bottom link -->
                    <div class="text-center">
                        <span class="text-sm text-muted-foreground">Pas encore de compte?</span>
                        <a class="text-sm font-semibold text-[#314e6c] hover:text-[#263d56] transition-colors ms-1" href="#">
                            Contactez l'administrateur
                        </a>
                    </div>
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
                        Pilotez vos points de vente<br/>
                        <span class="text-[#f5c400]">mobile money</span> en temps réel
                    </h1>
                    <p class="max-w-md text-base leading-relaxed text-white/65">
                        Centralisez la gestion de vos kiosques, agents et transactions Flooz &amp; Mixx by Yas depuis une plateforme unique, sécurisée et pensée pour le terrain.
                    </p>
                </div>

                <ul class="flex flex-col gap-3 border-t border-white/10 pt-6">
                    <li class="flex items-center gap-3 text-sm text-white/80">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/8 ring-1 ring-white/10">
                            <i class="ki-filled ki-chart-line-up text-[#f5c400] text-sm"></i>
                        </span>
                        Suivi des transactions et soldes en direct
                    </li>
                    <li class="flex items-center gap-3 text-sm text-white/80">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/8 ring-1 ring-white/10">
                            <i class="ki-filled ki-shop text-[#f5c400] text-sm"></i>
                        </span>
                        Gestion des kiosques et réseau d'agents
                    </li>
                    <li class="flex items-center gap-3 text-sm text-white/80">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/8 ring-1 ring-white/10">
                            <i class="ki-filled ki-document text-[#f5c400] text-sm"></i>
                        </span>
                        Rapports et pilotage financier consolidés
                    </li>
                </ul>
            </div>

            <p class="relative z-10 mt-auto hidden pt-10 text-xs uppercase tracking-[0.22em] text-white/35 lg:block">
                PDV Connect · Plateforme de gestion Mobile Money
            </p>
        </div>
    </div>
    <!-- End of Page -->
    
    @include('layouts.partials.flash-messages')

    <!-- Scripts -->
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    @vite(['resources/js/app.js'])
    <!-- End of Scripts -->
</body>
</html>
